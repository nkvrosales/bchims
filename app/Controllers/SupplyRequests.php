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

        if ($role === 'admin') {
            $requests = $this->requestModel->get_requests();
            $items    = [];
        } else {
            // Staff sees their own department's requests
            $requests = $this->requestModel->get_requests(null, $user['department_id'] ?? 0);
            // Fetch Central Supply items for the request dropdown
            $items = $this->db->table('central_supply')
                              ->select('central_supply_id AS id, item_code, item_name AS name, quantity_on_hand AS quantity')
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

        $data['title']    = 'Supply Requests';
        $data['requests'] = $requests;
        $data['user']     = $user;
        $data['items']    = $items;
        $data['search']   = $search;

        return view('templates/header', $data)
             . view('supply_requests/index', $data)
             . view('templates/footer');
    }

    /**
     * Submit a new supply request (Staff only).
     */
    public function create()
    {
        if ($res = $this->checkAuth()) return $res;

        if (session()->get('role') !== 'staff') {
            session()->setFlashdata('error', 'Administrators are not permitted to submit supply requests.');
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

            $insertData = [
                'user_id'           => $userId,
                'central_supply_id' => $centralSupplyId,
                'quantity_requested'=> $quantity,
                'quantity_served'   => 0,
                'status'            => 'Pending',
                'notes'             => $notes,
            ];

            if ($this->requestModel->insert($insertData)) {
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

        if (session()->get('role') !== 'admin') {
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

        // Get requester's department
        $requester = $this->userModel->get_user_by_id($request['user_id']);
        $deptId    = $requester['department_id'] ?? null;

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Deduct from central_supply
        $db->table('central_supply')
           ->where('central_supply_id', $csItem['central_supply_id'])
           ->update([
               'quantity'         => $csItem['quantity'] - $qtyRequested,
               'quantity_on_hand' => $csItem['quantity_on_hand'] - $qtyRequested,
           ]);

        // 2. Find or create inventory item for the department
        $invItem = $db->table('inventory')
                      ->where('item_code', $csItem['item_code'])
                      ->get()->getRowArray();

        if (!$invItem) {
            $db->table('inventory')->insert([
                'item_code'   => $csItem['item_code'],
                'item_name'   => $csItem['item_name'],
                'batch_num'   => $csItem['batch_num'],
                'lot_num'     => $csItem['lot_num'],
                'expiration_date' => $csItem['expiration_date'],
                'quantity'    => $qtyRequested,
                'category_id' => $csItem['category_id'],
                'source_id'   => $csItem['source_id'],
            ]);
            $invId = $db->insertID();
        } else {
            $invId = $invItem['inventory_id'];
        }

        // 3. Upsert department_supply
        $deptSupply = $db->table('department_supply')
                         ->where('department_id', $deptId)
                         ->where('inventory_id', $invId)
                         ->get()->getRowArray();

        if ($deptSupply) {
            $newQtyOnHand = $deptSupply['quantity_on_hand'] + $qtyRequested;
            $db->table('department_supply')
               ->where('department_supply_id', $deptSupply['department_supply_id'])
               ->update([
                   'quantity_received' => $deptSupply['quantity_received'] + $qtyRequested,
                   'quantity_on_hand'  => $newQtyOnHand,
                   'central_supply_id' => $csItem['central_supply_id'],
               ]);
            $deptSupplyId = $deptSupply['department_supply_id'];
        } else {
            $db->table('department_supply')->insert([
                'department_id'     => $deptId,
                'inventory_id'      => $invId,
                'central_supply_id' => $csItem['central_supply_id'],
                'quantity_received' => $qtyRequested,
                'quantity_used'     => 0,
                'quantity_on_hand'  => $qtyRequested,
            ]);
            $deptSupplyId = $db->insertID();
        }

        // 4. Update request to Served
        $this->requestModel->update($id, [
            'status'               => 'Served',
            'quantity_served'      => $qtyRequested,
            'department_supply_id' => $deptSupplyId,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'An error occurred while serving the request. Please try again.');
        } else {
            $this->auditModel->log_activity(
                'SERVE_SUPPLY_REQUEST',
                'Supply Requests',
                "Served supply request #{$id} for {$request['requester_full_name']}. Transferred {$qtyRequested} unit(s) of '{$csItem['item_name']}' to department '{$requester['department_name']}'."
            );
            session()->setFlashdata('success', "Request served! {$qtyRequested} unit(s) of '{$csItem['item_name']}' transferred to {$requester['department_name']}.");
        }

        return redirect()->to('supply_requests');
    }

    /**
     * Partially serve a pending supply request.
     */
    public function partial($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (session()->get('role') !== 'admin') {
            session()->setFlashdata('error', 'Only administrators can serve supply requests.');
            return redirect()->to('supply_requests');
        }

        if (empty($id)) {
            return redirect()->to('supply_requests');
        }

        $servedQty = (int)$this->request->getPost('served_quantity');
        $request   = $this->_getRequest($id);

        if (!$request) {
            session()->setFlashdata('error', 'Supply request not found.');
            return redirect()->to('supply_requests');
        }

        if ($request['status'] !== 'Pending') {
            session()->setFlashdata('error', 'This request has already been processed.');
            return redirect()->to('supply_requests');
        }

        $qtyRequested = (int)$request['quantity_requested'];

        if ($servedQty <= 0 || $servedQty >= $qtyRequested) {
            session()->setFlashdata('error', "Invalid partial quantity. Must be between 1 and " . ($qtyRequested - 1) . ".");
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

        $requester = $this->userModel->get_user_by_id($request['user_id']);
        $deptId    = $requester['department_id'] ?? null;

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
                         ->where('department_id', $deptId)
                         ->where('inventory_id', $invId)
                         ->get()->getRowArray();

        if ($deptSupply) {
            $db->table('department_supply')
               ->where('department_supply_id', $deptSupply['department_supply_id'])
               ->update([
                   'quantity_received' => $deptSupply['quantity_received'] + $servedQty,
                   'quantity_on_hand'  => $deptSupply['quantity_on_hand'] + $servedQty,
                   'central_supply_id' => $csItem['central_supply_id'],
               ]);
            $deptSupplyId = $deptSupply['department_supply_id'];
        } else {
            $db->table('department_supply')->insert([
                'department_id'     => $deptId,
                'inventory_id'      => $invId,
                'central_supply_id' => $csItem['central_supply_id'],
                'quantity_received' => $servedQty,
                'quantity_used'     => 0,
                'quantity_on_hand'  => $servedQty,
            ]);
            $deptSupplyId = $db->insertID();
        }

        // 4. Update request to Partially Served
        $this->requestModel->update($id, [
            'status'               => 'Partially Served',
            'quantity_served'      => $servedQty,
            'department_supply_id' => $deptSupplyId,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'An error occurred while partially serving the request.');
        } else {
            $this->auditModel->log_activity(
                'PARTIAL_SERVE_SUPPLY_REQUEST',
                'Supply Requests',
                "Partially served supply request #{$id} for {$request['requester_full_name']}. Served {$servedQty} of {$qtyRequested} unit(s) of '{$csItem['item_name']}'."
            );
            session()->setFlashdata('success', "Partially served! {$servedQty} unit(s) of '{$csItem['item_name']}' transferred to {$requester['department_name']}.");
        }

        return redirect()->to('supply_requests');
    }

    /**
     * Reject a pending supply request (Admin only).
     */
    public function reject($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (session()->get('role') !== 'admin') {
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

        if ($this->requestModel->update($id, ['status' => 'Rejected'])) {
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

        if (session()->get('role') !== 'admin') {
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
        $requester = $this->userModel->get_user_by_id($request['user_id']);
        $deptId    = $requester['department_id'] ?? null;

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
                         ->where('department_id', $deptId)
                         ->where('inventory_id', $invId)
                         ->get()->getRowArray();

        if ($deptSupply) {
            $db->table('department_supply')
               ->where('department_supply_id', $deptSupply['department_supply_id'])
               ->update([
                   'quantity_received' => $deptSupply['quantity_received'] + $remainingQty,
                   'quantity_on_hand'  => $deptSupply['quantity_on_hand'] + $remainingQty,
                   'central_supply_id' => $csItem['central_supply_id'],
               ]);
            $deptSupplyId = $deptSupply['department_supply_id'];
        } else {
            $db->table('department_supply')->insert([
                'department_id'     => $deptId,
                'inventory_id'      => $invId,
                'central_supply_id' => $csItem['central_supply_id'],
                'quantity_received' => $remainingQty,
                'quantity_used'     => 0,
                'quantity_on_hand'  => $remainingQty,
            ]);
            $deptSupplyId = $db->insertID();
        }

        // 4. Update request to Served and set quantity_served to full requested amount
        $this->requestModel->update($id, [
            'status'               => 'Served',
            'quantity_served'      => $qtyRequested,
            'department_supply_id' => $deptSupplyId,
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
            session()->setFlashdata('success', "Request completed! Remaining {$remainingQty} unit(s) of '{$csItem['item_name']}' transferred to {$requester['department_name']}.");
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
