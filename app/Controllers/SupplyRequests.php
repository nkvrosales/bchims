<?php

namespace App\Controllers;

use App\Models\SupplyRequestModel;
use App\Models\ItemModel;
use App\Models\UserModel;
use App\Models\AuditModel;

class SupplyRequests extends BaseController
{
    protected $requestModel;
    protected $itemModel;
    protected $userModel;
    protected $auditModel;
    protected $db;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->db           = \Config\Database::connect();
        $this->requestModel = new SupplyRequestModel();
        $this->itemModel    = new ItemModel();
        $this->userModel    = new UserModel();
        $this->auditModel   = new AuditModel();
    }

    protected function checkAuth()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        // Verify the user actually exists to handle reseeded/stale sessions gracefully
        $userModel = new UserModel();
        $user = $userModel->get_user_by_id(session()->get('user_id'));
        if (empty($user)) {
            session()->destroy();
            return redirect()->to('auth/login');
        }

        return null;
    }

    /**
     * View all supply requests (Admin) or user department requests (Staff).
     */
    public function index()
    {
        if ($res = $this->checkAuth()) return $res;

        $userId = session()->get('user_id');
        $user   = $this->userModel->get_user_by_id($userId);
        $role   = session()->get('role');
        $search = trim((string) $this->request->getGet('search'));

        if (is_admin_role()) {
            $requests = $this->requestModel->get_requests();
            $items    = [];
            $categories = [];
        } else {
            // Staff sees their own department's requests
            $requests = $this->requestModel->get_requests(null, $user['department_id'] ?? 0);
            // Fetch categories for the filter dropdown
            $categories = $this->db->table('category')
                                   ->orderBy('category_code', 'ASC')
                                   ->get()->getResultArray();
            // Fetch in-stock Central Supply items for the request dropdown (one per item name)
            $items = $this->db->table('central_supply')
                              ->select('MAX(central_supply_id) AS id, item_name AS name, SUM(quantity_on_hand) AS quantity, MAX(category_id) AS category_id')
                              ->where('quantity_on_hand >', 0)
                              ->groupBy('item_name')
                              ->orderBy('item_name', 'ASC')
                              ->get()->getResultArray();
        }

        if ($search !== '') {
            $requests = array_values(array_filter($requests, static function ($req) use ($search) {
                $needle = mb_strtolower($search);
                $haystacks = [
                    (string)($req['request_id'] ?? ''),
                    (string)($req['requester_full_name'] ?? ''),
                    (string)($req['requester_username'] ?? ''),
                    (string)($req['department_name'] ?? ''),
                    (string)($req['item_name'] ?? ''),
                    (string)($req['item_code'] ?? ''),
                    (string)($req['status'] ?? ''),
                    (string)($req['notes'] ?? ''),
                    (string)($req['quantity_requested'] ?? ''),
                    (string)($req['quantity_served'] ?? ''),
                ];

                foreach ($haystacks as $value) {
                    if (mb_stripos($value, $needle) !== false) {
                        return true;
                    }
                }

                return false;
            }));
        }

        $data['title']      = 'Supply Requests';
        $data['requests']   = $requests;
        $data['user']       = $user;
        $data['items']      = $items;
        $data['categories'] = $categories;
        $data['search']     = $search;

        return view('templates/header', $data)
             . view('supply_requests/index', $data)
             . view('templates/footer');
    }

     /**
      * Submit a new supply request.
      */
    public function create()
    {
        if ($res = $this->checkAuth()) return $res;

        if (session()->get('role') !== 'encoder') {
            session()->setFlashdata('error', 'Only encoders are permitted to submit supply requests.');
            return redirect()->to('supply_requests');
        }

        $rules = [
            'item_id'  => 'required|integer',
            'quantity' => 'required|integer|greater_than[0]',
            'notes'    => 'max_length[1000]',
        ];

        if ($this->validate($rules)) {
            $centralSupplyId = (int)$this->request->getPost('item_id');
            $quantity        = (int)$this->request->getPost('quantity');
            $notes           = $this->request->getPost('notes');

            // Verify the item exists in central_supply
            $item = $this->db->table('central_supply')
                             ->where('central_supply_id', $centralSupplyId)
                             ->get()->getRowArray();

            if (!$item) {
                session()->setFlashdata('error', 'The selected central supply item does not exist.');
                return redirect()->to('supply_requests');
            }

            $userId = session()->get('user_id');
            $user   = $this->userModel->get_user_by_id($userId);
            $deptId = $user['department_id'] ?? null;

            // 1. Get or create inventory item for the department
            $invItem = $this->db->table('inventory')
                                ->where('item_code', $item['item_code'])
                                ->get()->getRowArray();
            if (!$invItem) {
                $this->db->table('inventory')->insert([
                    'item_code'       => $item['item_code'],
                    'item_name'       => $item['item_name'],
                    'batch_num'       => $item['batch_num'],
                    'lot_num'         => $item['lot_num'],
                    'expiration_date' => $item['expiration_date'],
                    'quantity'        => 0,
                    'category_id'     => $item['category_id'],
                ]);
                $invId = $this->db->insertID();
            } else {
                $invId = $invItem['inventory_id'];
            }

            // 2. Create a new department_supply row for this request
            $this->db->table('department_supply')->insert([
                'department_id'     => $deptId,
                'quantity_received' => 0,
                'quantity_used'     => 0,
                'quantity_on_hand'  => 0,
            ]);
            $deptSupplyId = $this->db->insertID();

            // 3. Create the request
            $insertData = [
                'department_supply_id' => $deptSupplyId,
                'quantity_requested'   => $quantity,
                'quantity_served'      => 0,
                'status'               => 'Pending',
                'user_id'              => $userId,
            ];

            if ($this->requestModel->insert($insertData)) {
                $requestId = $this->requestModel->insertID();

                // 4. Create a supply record for this request
                $this->db->table('supply')->insert([
                    'request_id'           => $requestId,
                    'central_supply_id'    => $centralSupplyId,
                    'department_supply_id' => $deptSupplyId,
                    'inventory_id'         => $invId,
                    'batch_num'            => $item['batch_num'],
                    'lot_num'              => $item['lot_num'],
                    'expiration_date'      => $item['expiration_date'],
                    'unit'                 => $item['unit'],
                    'quantity'             => 0,
                    'category_id'          => $item['category_id'],
                ]);

                $this->auditModel->log_activity(
                    'CREATE_SUPPLY_REQUEST',
                    'Supply Requests',
                    "{$user['full_name']} submitted a supply request for {$quantity} unit(s) of {$item['item_name']} (Code: {$item['item_code']}) from Central Supply."
                );

                session()->setFlashdata('success', 'Supply request submitted successfully!');
            } else {
                session()->setFlashdata('error', 'An error occurred while submitting your request.');
            }
        } else {
            session()->setFlashdata('create_request_modal_open', true);
            session()->setFlashdata('create_request_validation_errors', $this->validator->listErrors());
        }

        return redirect()->to('supply_requests');
    }

    /**
     * Fully serve a pending supply request — deduct from Central Supply,
     * transfer to requesting department inventory.
     */
    public function serve($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (!is_admin_role()) {
            session()->setFlashdata('error', 'Only administrators can serve supply requests.');
            return redirect()->to('supply_requests');
        }

        if (empty($id)) {
            return redirect()->to('supply_requests');
        }

        $request = $this->_getRequest($id);

        if (!$request) {
            session()->setFlashdata('error', 'Supply request not found.');
            return redirect()->to('supply_requests');
        }

        if ($request['status'] !== 'Pending') {
            session()->setFlashdata('error', 'This request has already been processed.');
            return redirect()->to('supply_requests');
        }

        // Fetch central supply item
        $csItem = $this->db->table('central_supply')
                           ->where('central_supply_id', $request['central_supply_id'])
                           ->get()->getRowArray();

        if (!$csItem) {
            session()->setFlashdata('error', 'Associated central supply item not found.');
            return redirect()->to('supply_requests');
        }

        $qtyRequested = (int)$request['quantity_requested'];

        if ($csItem['quantity_on_hand'] < $qtyRequested) {
            session()->setFlashdata('error', "Insufficient stock of '{$csItem['item_name']}' in Central Supply. Available: {$csItem['quantity_on_hand']}, Requested: {$qtyRequested}.");
            return redirect()->to('supply_requests');
        }

        // Get requester's department from joined request data
        $deptId    = $request['department_id'] ?? null;
        $deptName  = $request['department_name'] ?? 'Department';

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Deduct from central_supply
        $db->table('central_supply')
           ->where('central_supply_id', $csItem['central_supply_id'])
           ->update([
               'quantity'         => $csItem['quantity'] - $qtyRequested,
               'quantity_on_hand' => $csItem['quantity_on_hand'] - $qtyRequested,
           ]);        // 2. Find the pre-created supply record for this request
        $supplyRec = $db->table('supply')
                        ->where('request_id', $id)
                        ->get()->getRowArray();

        if ($supplyRec) {
            // Update department_supply quantity_on_hand
            $deptSupply = $db->table('department_supply')
                             ->where('department_supply_id', $supplyRec['department_supply_id'])
                             ->get()->getRowArray();
            if ($deptSupply) {
                $db->table('department_supply')
                   ->where('department_supply_id', $deptSupply['department_supply_id'])
                   ->update([
                       'quantity_received' => $deptSupply['quantity_received'] + $qtyRequested,
                       'quantity_on_hand'  => $deptSupply['quantity_on_hand'] + $qtyRequested,
                   ]);
            }
            $deptSupplyId = $supplyRec['department_supply_id'];

            // Update inventory quantity
            $invItem = $db->table('inventory')
                          ->where('inventory_id', $supplyRec['inventory_id'])
                          ->get()->getRowArray();
            if ($invItem) {
                $db->table('inventory')
                   ->where('inventory_id', $invItem['inventory_id'])
                   ->update([
                       'quantity' => $invItem['quantity'] + $qtyRequested,
                   ]);
            }

            // Update supply record quantity and details
            $db->table('supply')
               ->where('supply_id', $supplyRec['supply_id'])
               ->update([
                   'quantity' => $qtyRequested,
                   'batch_num' => $csItem['batch_num'],
                   'lot_num' => $csItem['lot_num'],
                   'expiration_date' => $csItem['expiration_date'],
               ]);
        } else {
            $deptSupplyId = null;
        }

        // 4. Update request to Served
        $this->requestModel->update($id, [
            'status'               => 'Served',
            'quantity_served'      => $qtyRequested,
            'department_supply_id' => $deptSupplyId,
            'served_date'          => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'An error occurred while serving the request. Please try again.');
        } else {
            $this->auditModel->log_activity(
                'SERVE_SUPPLY_REQUEST',
                'Supply Requests',
                "Served supply request #{$id} for {$request['requester_full_name']}. Transferred {$qtyRequested} unit(s) of '{$csItem['item_name']}' to department '{$deptName}'."
            );
            session()->setFlashdata('success', "Request served! {$qtyRequested} unit(s) of '{$csItem['item_name']}' transferred to {$deptName}.");
        }

        return redirect()->to('supply_requests');
    }

    /**
     * Partially serve a pending or partially served supply request.
     */
    public function partial($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (!is_admin_role()) {
            session()->setFlashdata('error', 'Only administrators can serve supply requests.');
            return redirect()->to('supply_requests');
        }

        if (empty($id)) {
            return redirect()->to('supply_requests');
        }

        $servedQty = (int)$this->request->getPost('served_quantity');
        $notes     = trim((string)$this->request->getPost('partial_notes'));
        $request   = $this->_getRequest($id);

        if (!$request) {
            session()->setFlashdata('error', 'Supply request not found.');
            return redirect()->to('supply_requests');
        }

        if (!in_array($request['status'], ['Pending', 'Partially Served'])) {
            session()->setFlashdata('error', 'This request has already been processed.');
            return redirect()->to('supply_requests');
        }

        $qtyRequested = (int)$request['quantity_requested'];
        $qtyServed    = (int)$request['quantity_served'];
        $remainingQty = $qtyRequested - $qtyServed;

        if ($servedQty <= 0 || $servedQty >= $remainingQty) {
            session()->setFlashdata('error', "Invalid partial quantity. Must be between 1 and " . ($remainingQty - 1) . ".");
            return redirect()->to('supply_requests');
        }

        // Fetch central supply item
        $csItem = $this->db->table('central_supply')
                           ->where('central_supply_id', $request['central_supply_id'])
                           ->get()->getRowArray();

        if (!$csItem) {
            session()->setFlashdata('error', 'Associated central supply item not found.');
            return redirect()->to('supply_requests');
        }

        if ($csItem['quantity_on_hand'] < $servedQty) {
            session()->setFlashdata('error', "Insufficient stock. Available: {$csItem['quantity_on_hand']}, Trying to serve: {$servedQty}.");
            return redirect()->to('supply_requests');
        }

        // Get requester's department from joined request data
        $deptId    = $request['department_id'] ?? null;
        $deptName  = $request['department_name'] ?? 'Department';

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Deduct from central_supply
        $db->table('central_supply')
           ->where('central_supply_id', $csItem['central_supply_id'])
           ->update([
               'quantity'         => $csItem['quantity'] - $servedQty,
               'quantity_on_hand' => $csItem['quantity_on_hand'] - $servedQty,
           ]);

        // 2. Find or create inventory item for the department
        $invItem = $db->table('inventory')
                      ->where('item_code', $csItem['item_code'])
                      ->get()->getRowArray();

        if (!$invItem) {
            $db->table('inventory')->insert([
                'item_code'       => $csItem['item_code'],
                'item_name'       => $csItem['item_name'],
                'batch_num'       => $csItem['batch_num'],
                'lot_num'         => $csItem['lot_num'],
                'expiration_date' => $csItem['expiration_date'],
                'quantity'        => $servedQty,
                'category_id'     => $csItem['category_id'],
                'source_id'       => $csItem['source_id'],
            ]);
            $invId = $db->insertID();
        } else {
            $invId = $invItem['inventory_id'];
        }

        // 3. Upsert department_supply
        $deptSupply = $db->table('department_supply')
                         ->join('supply', 'supply.department_supply_id = department_supply.department_supply_id')
                         ->where('department_supply.department_id', $deptId)
                         ->where('supply.inventory_id', $invId)
                         ->get()->getRowArray();

        if ($deptSupply) {
            $db->table('department_supply')
               ->where('department_supply_id', $deptSupply['department_supply_id'])
               ->update([
                   'quantity_received' => $deptSupply['quantity_received'] + $servedQty,
                   'quantity_on_hand'  => $deptSupply['quantity_on_hand'] + $servedQty,
               ]);
            $deptSupplyId = $deptSupply['department_supply_id'];
        } else {
            $db->table('department_supply')->insert([
                'department_id'     => $deptId,
                'quantity_received' => $servedQty,
                'quantity_used'     => 0,
                'quantity_on_hand'  => $servedQty,
            ]);
            $deptSupplyId = $db->insertID();

            // Link via supply table
            $db->table('supply')->insert([
                'request_id'           => $id,
                'central_supply_id'    => $csItem['central_supply_id'],
                'department_supply_id' => $deptSupplyId,
                'inventory_id'         => $invId,
                'batch_num'            => $csItem['batch_num'],
                'lot_num'              => $csItem['lot_num'],
                'expiration_date'      => $csItem['expiration_date'],
                'unit'                 => $csItem['unit'],
                'quantity'             => $servedQty,
                'category_id'          => $csItem['category_id'],
            ]);
        }

        // 4. Update request
        $updateData = [
            'status'               => 'Partially Served',
            'quantity_served'      => $qtyServed + $servedQty,
            'department_supply_id' => $deptSupplyId,
            'partial_date'         => date('Y-m-d H:i:s'),
        ];
        if ($notes !== '') {
            $existingNotes = $request['notes'] ?? '';
            $updateData['notes'] = $existingNotes !== ''
                ? $existingNotes . "\n---\n" . $notes
                : $notes;
        }
        $this->requestModel->update($id, $updateData);

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'An error occurred while partially serving the request.');
        } else {
            $this->auditModel->log_activity(
                'PARTIAL_SERVE_SUPPLY_REQUEST',
                'Supply Requests',
                "Partially served supply request #{$id} for {$request['requester_full_name']}. Served {$servedQty} of {$qtyRequested} unit(s) of '{$csItem['item_name']}'."
            );
            session()->setFlashdata('success', "Partially served! {$servedQty} unit(s) of '{$csItem['item_name']}' transferred to {$deptName}.");
        }

        return redirect()->to('supply_requests');
    }

    /**
     * Reject a pending supply request (Admin only).
     */
    public function reject($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (!is_admin_role()) {
            session()->setFlashdata('error', 'Only administrators can reject supply requests.');
            return redirect()->to('supply_requests');
        }

        if (empty($id)) {
            return redirect()->to('supply_requests');
        }

        $request = $this->_getRequest($id);

        if (!$request) {
            session()->setFlashdata('error', 'Supply request not found.');
            return redirect()->to('supply_requests');
        }

        if ($request['status'] !== 'Pending') {
            session()->setFlashdata('error', 'This request has already been processed.');
            return redirect()->to('supply_requests');
        }

        if ($this->requestModel->update($id, ['status' => 'Rejected', 'cancelled_date' => date('Y-m-d H:i:s')])) {
            $this->auditModel->log_activity(
                'REJECT_SUPPLY_REQUEST',
                'Supply Requests',
                "Rejected supply request #{$id} from {$request['requester_full_name']} for {$request['quantity_requested']} unit(s) of '{$request['item_name']}'."
            );
            session()->setFlashdata('success', 'Supply request rejected successfully.');
        } else {
            session()->setFlashdata('error', 'An error occurred while rejecting the request.');
        }

        return redirect()->to('supply_requests');
    }

    /**
     * Complete a partially served supply request by serving the remaining quantity.
     */
    public function complete_partial($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (!is_admin_role()) {
            session()->setFlashdata('error', 'Only administrators can complete supply requests.');
            return redirect()->to('supply_requests');
        }

        if (empty($id)) {
            return redirect()->to('supply_requests');
        }

        $request = $this->_getRequest($id);

        if (!$request) {
            session()->setFlashdata('error', 'Supply request not found.');
            return redirect()->to('supply_requests');
        }

        if ($request['status'] !== 'Partially Served') {
            session()->setFlashdata('error', 'Only partially served requests can be completed.');
            return redirect()->to('supply_requests');
        }

        $qtyRequested = (int)$request['quantity_requested'];
        $qtyServed    = (int)$request['quantity_served'];
        $remainingQty = $qtyRequested - $qtyServed;

        if ($remainingQty <= 0) {
            session()->setFlashdata('error', 'This request is already fully served.');
            return redirect()->to('supply_requests');
        }

        // Fetch central supply item
        $csItem = $this->db->table('central_supply')
                           ->where('central_supply_id', $request['central_supply_id'])
                           ->get()->getRowArray();

        if (!$csItem) {
            session()->setFlashdata('error', 'Associated central supply item not found.');
            return redirect()->to('supply_requests');
        }

        if ($csItem['quantity_on_hand'] < $remainingQty) {
            session()->setFlashdata('error', "Insufficient stock to complete the request. Available: {$csItem['quantity_on_hand']}, Needed: {$remainingQty}.");
            return redirect()->to('supply_requests');
        }

        // Get requester's department
        // Get requester's department from joined request data
        $deptId    = $request['department_id'] ?? null;
        $deptName  = $request['department_name'] ?? 'Department';

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Deduct remaining quantity from central_supply
        $db->table('central_supply')
           ->where('central_supply_id', $csItem['central_supply_id'])
           ->update([
               'quantity'         => $csItem['quantity'] - $remainingQty,
               'quantity_on_hand' => $csItem['quantity_on_hand'] - $remainingQty,
           ]);

        // 2. Find or create inventory item for the department
        $invItem = $db->table('inventory')
                      ->where('item_code', $csItem['item_code'])
                      ->get()->getRowArray();

        if (!$invItem) {
            $db->table('inventory')->insert([
                'item_code'       => $csItem['item_code'],
                'item_name'       => $csItem['item_name'],
                'batch_num'       => $csItem['batch_num'],
                'lot_num'         => $csItem['lot_num'],
                'expiration_date' => $csItem['expiration_date'],
                'quantity'        => $remainingQty,
                'category_id'     => $csItem['category_id'],
                'source_id'       => $csItem['source_id'],
            ]);
            $invId = $db->insertID();
        } else {
            $invId = $invItem['inventory_id'];
        }

        // 3. Upsert department_supply for the remaining quantity
        $deptSupply = $db->table('department_supply')
                         ->join('supply', 'supply.department_supply_id = department_supply.department_supply_id')
                         ->where('department_supply.department_id', $deptId)
                         ->where('supply.inventory_id', $invId)
                         ->get()->getRowArray();

        if ($deptSupply) {
            $db->table('department_supply')
               ->where('department_supply_id', $deptSupply['department_supply_id'])
               ->update([
                   'quantity_received' => $deptSupply['quantity_received'] + $remainingQty,
                   'quantity_on_hand'  => $deptSupply['quantity_on_hand'] + $remainingQty,
               ]);
            $deptSupplyId = $deptSupply['department_supply_id'];
        } else {
            $db->table('department_supply')->insert([
                'department_id'     => $deptId,
                'quantity_received' => $remainingQty,
                'quantity_used'     => 0,
                'quantity_on_hand'  => $remainingQty,
            ]);
            $deptSupplyId = $db->insertID();

            // Link via supply table
            $db->table('supply')->insert([
                'request_id'           => $id,
                'central_supply_id'    => $csItem['central_supply_id'],
                'department_supply_id' => $deptSupplyId,
                'inventory_id'         => $invId,
                'batch_num'            => $csItem['batch_num'],
                'lot_num'              => $csItem['lot_num'],
                'expiration_date'      => $csItem['expiration_date'],
                'unit'                 => $csItem['unit'],
                'quantity'             => $remainingQty,
                'category_id'          => $csItem['category_id'],
            ]);
        }

        // 4. Update request to Served and clear notes
        $this->requestModel->update($id, [
            'status'               => 'Served',
            'quantity_served'      => $qtyRequested,
            'department_supply_id' => $deptSupplyId,
            'served_date'          => date('Y-m-d H:i:s'),
            'closed_date'          => date('Y-m-d H:i:s'),
            'notes'                => null,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'An error occurred while completing the partial request.');
        } else {
            $this->auditModel->log_activity(
                'COMPLETE_PARTIAL_SUPPLY_REQUEST',
                'Supply Requests',
                "Completed partial supply request #{$id} for {$request['requester_full_name']}. Served remaining {$remainingQty} unit(s) of '{$csItem['item_name']}'."
            );
            session()->setFlashdata('success', "Request completed! Remaining {$remainingQty} unit(s) of '{$csItem['item_name']}' transferred to {$deptName}.");
        }

        return redirect()->to('supply_requests');
    }

    /**
     * Delete a supply request (Admin only).
     * Must remove supply rows (FK: supply.request_id -> request.request_id) before
     * deleting the request, and then clean up orphaned department_supply rows.
     */
    public function delete($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (!is_admin_role()) {
            session()->setFlashdata('error', 'Only administrators can delete supply requests.');
            return redirect()->to('supply_requests');
        }

        if (empty($id)) {
            return redirect()->to('supply_requests');
        }

        $request = $this->_getRequest($id);

        if (!$request) {
            session()->setFlashdata('error', 'Supply request not found.');
            return redirect()->to('supply_requests');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Collect department_supply_id(s) linked via supply rows for this request
        $supplyRows = $db->table('supply')->where('request_id', $id)->get()->getResultArray();
        $deptSupplyIds = array_unique(array_column($supplyRows, 'department_supply_id'));

        // 2. Delete supply rows first (FK: supply.request_id -> request)
        $db->table('supply')->where('request_id', $id)->delete();

        // 3. Delete the request itself
        $this->requestModel->delete($id);

        // 4. Clean up orphaned department_supply rows (ones no longer referenced by any request or supply)
        foreach ($deptSupplyIds as $dsId) {
            $stillReferenced = $db->table('request')->where('department_supply_id', $dsId)->countAllResults();
            $stillInSupply   = $db->table('supply')->where('department_supply_id', $dsId)->countAllResults();
            if ($stillReferenced === 0 && $stillInSupply === 0) {
                $db->table('department_supply')->where('department_supply_id', $dsId)->delete();
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'An error occurred while deleting the request. Please try again.');
        } else {
            $this->auditModel->log_activity(
                'DELETE_SUPPLY_REQUEST',
                'Supply Requests',
                "Deleted supply request #{$id} from {$request['requester_full_name']} for {$request['quantity_requested']} unit(s) of '{$request['item_name']}'"
            );
            session()->setFlashdata('success', 'Supply request deleted successfully.');
        }

        return redirect()->to('supply_requests');
    }

    /**
     * Bulk-delete selected supply requests (Admin only).
     * Deletes supply rows first to satisfy FK constraints, then the requests.
     */
    public function delete_selected()
    {
        if ($res = $this->checkAuth()) return $res;

        if (!is_admin_role()) {
            session()->setFlashdata('error', 'Only administrators can delete supply requests.');
            return redirect()->to('supply_requests');
        }

        $ids = $this->request->getPost('request_ids');
        if (empty($ids) || !is_array($ids)) {
            session()->setFlashdata('error', 'No supply requests selected.');
            return redirect()->to('supply_requests');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $allDeptSupplyIds = [];

        foreach ($ids as $id) {
            // Collect department_supply_ids before deletion
            $supplyRows = $db->table('supply')->where('request_id', $id)->get()->getResultArray();
            foreach ($supplyRows as $row) {
                $allDeptSupplyIds[] = $row['department_supply_id'];
            }
            // Delete supply rows (FK: supply.request_id -> request)
            $db->table('supply')->where('request_id', $id)->delete();
            // Delete the request
            $this->requestModel->delete($id);
        }

        // Clean up orphaned department_supply rows
        foreach (array_unique($allDeptSupplyIds) as $dsId) {
            $stillReferenced = $db->table('request')->where('department_supply_id', $dsId)->countAllResults();
            $stillInSupply   = $db->table('supply')->where('department_supply_id', $dsId)->countAllResults();
            if ($stillReferenced === 0 && $stillInSupply === 0) {
                $db->table('department_supply')->where('department_supply_id', $dsId)->delete();
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'An error occurred while deleting selected requests.');
        } else {
            $count = count($ids);
            $this->auditModel->log_activity(
                'BULK_DELETE_SUPPLY_REQUESTS',
                'Supply Requests',
                "Bulk deleted {$count} supply request(s)."
            );
            session()->setFlashdata('success', "Successfully deleted {$count} supply request(s).");
        }

        return redirect()->to('supply_requests');
    }

    /**
     * Helper: Fetch a single request with all joined data.
     */
    private function _getRequest($id)
    {
        $all = $this->requestModel->get_requests();
        foreach ($all as $r) {
            if ((int)$r['request_id'] === (int)$id) {
                return $r;
            }
        }
        return null;
    }
}
