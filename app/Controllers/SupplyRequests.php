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
            $lastUser = $_COOKIE['last_username'] ?? null;
            if ($lastUser) {
                $um = new \App\Models\UserModel();
                $u = $um->get_user_by_username($lastUser);
                $this->auditModel->log_activity('LOGOUT', 'Auth', "User account logged out due to inactivity \"$lastUser\".", null, $u ? $u['user_id'] : null);
                setcookie('last_username', '', time() - 3600, '/');
            }
            session()->setFlashdata('session_expired', 'Your session has expired due to inactivity. Please log in again.');
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
        $search        = trim((string) $this->request->getGet('search'));
        $status_filter = trim((string) $this->request->getGet('status_filter'));
        $dept_filter   = trim((string) $this->request->getGet('dept_filter'));

        $departments = [];
        if (is_admin_role()) {
            $requests = $this->requestModel->get_requests(null, null, $search, $status_filter, $dept_filter);
            $items    = [];
            $categories = [];
            
            $deptModel = new \App\Models\DepartmentModel();
            $departments = $deptModel->get_departments();
        } elseif (strtolower((string) $role) === 'viewer') {
            $requests = $this->requestModel->get_requests(null, $user['department_id'] ?? 0, $search, $status_filter);
            $items    = [];
            $categories = [];

            $deptModel = new \App\Models\DepartmentModel();
            $departments = $deptModel->get_departments();
        } else {
            // Staff sees their own department's requests
            $requests = $this->requestModel->get_requests(null, $user['department_id'] ?? 0, $search, $status_filter);
            // Fetch categories for the filter dropdown
            $categories = $this->db->table('category')
                                   ->where('status', 1)
                                   ->orderBy('category_code', 'ASC')
                                   ->get()->getResultArray();
            // Fetch usable Central Supply items for the request dropdown (one per item code).
            // Batch name variants such as "Aspirin 80mg" and "Aspirin 80mgrams"
            // represent the same requestable item.
            $items = $this->db->table('central_supply')
                              ->select('MIN(central_supply_id) AS id, MIN(item_name) AS name, item_code, MAX(inventory_code) AS inventory_code, SUM(quantity_on_hand) AS quantity, MAX(category_id) AS category_id, MAX(unit) AS unit')
                              ->where('status', 1)
                              ->where('quantity_on_hand >', 0)
                              ->groupStart()
                                  ->where('expiration_date >=', date('Y-m-d'))
                                  ->orWhere('expiration_date', null)
                              ->groupEnd()
                              ->groupBy('item_code')
                              ->orderBy('MIN(item_name)', 'ASC', false)
                              ->get()->getResultArray();
        }

        // Fetch all available central_supply batches for batch selection in serve modals
        $batches = $this->db->table('central_supply')
                            ->select('central_supply_id, item_code, inventory_code, item_name, batch_num, lot_num, expiration_date, quantity_on_hand, unit')
                            ->where('status', 1)
                            ->where('quantity_on_hand >', 0)
                            ->orderBy('item_code, expiration_date', 'ASC')
                            ->get()->getResultArray();
        $batchesByCode = [];
        foreach ($batches as $b) {
            $name = $b['item_name'];
            if (!isset($batchesByCode[$name])) {
                $batchesByCode[$name] = [];
            }
            $batchesByCode[$name][] = $b;
        }

        // Build distinct units per item code so naming variants share one unit list.
        $unitRows = $this->db->table('central_supply')
                             ->select('item_code, unit')
                             ->distinct()
                             ->where('status', 1)
                             ->where('unit IS NOT NULL')
                             ->where("unit != ''")
                             ->orderBy('item_code, unit', 'ASC')
                             ->get()->getResultArray();
        $unitsByCode = [];
        foreach ($unitRows as $ur) {
            $unitsByCode[$ur['item_code']][] = $ur['unit'];
        }

        $data['title']         = is_admin_role() ? 'Central Supply Requests' : (strtolower((string) $role) === 'viewer' ? ($user['department_name'] ?? 'Department') . ' Requests' : ($user['department_name'] ?? 'My') . ' Requests');
        $data['requests']      = $requests;
        $data['user']          = $user;
        $data['items']         = $items;
        $data['categories']    = $categories;
        $data['search']        = $search;
        $data['status_filter'] = $status_filter;
        $data['dept_filter']   = $dept_filter;
        $data['departments']   = $departments;
        $data['batches_by_code'] = $batchesByCode;
        $data['units_by_code']   = $unitsByCode;

        return view('templates/header', $data)
             . view('requests', $data)
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
            return redirect()->to('requests');
        }

        $itemIds = $this->request->getPost('item_id');
        $quantities = $this->request->getPost('quantity');
        $postedUnits = $this->request->getPost('unit');
        $notes = $this->request->getPost('notes');

        if (!is_array($itemIds) || empty($itemIds)) {
            session()->setFlashdata('error', 'You must request at least one item.');
            return redirect()->to('requests');
        }

        // Validate notes length
        if (strlen((string)$notes) > 1000) {
            session()->setFlashdata('error', 'Details cannot exceed 1000 characters.');
            session()->setFlashdata('create_request_modal_open', true);
            return redirect()->to('requests');
        }

        // Basic validation loop
        $errors = [];
        $validItems = [];
        foreach ($itemIds as $index => $itemId) {
            $itemId = (int)$itemId;
            $quantity = isset($quantities[$index]) ? (int)$quantities[$index] : 0;

            if ($itemId <= 0) {
                $errors[] = "Row " . ($index + 1) . ": Invalid item selected.";
                continue;
            }

            if ($quantity <= 0) {
                $errors[] = "Row " . ($index + 1) . ": Quantity must be greater than 0.";
                continue;
            }

            // Verify the item exists in central_supply
            $item = $this->db->table('central_supply')
                             ->where('central_supply_id', $itemId)
                             ->get()->getRowArray();

            if (!$item) {
                $errors[] = "Row " . ($index + 1) . ": The selected central supply item does not exist.";
                continue;
            }

            $validItems[] = [
                'item' => $item,
                'quantity' => $quantity,
                'itemId' => $itemId,
                'unit' => isset($postedUnits[$index]) ? trim((string)$postedUnits[$index]) : $item['unit']
            ];
        }

        if (!empty($errors)) {
            session()->setFlashdata('create_request_modal_open', true);
            session()->setFlashdata('create_request_validation_errors', implode('<br>', $errors));
            return redirect()->to('requests');
        }

        $userId = session()->get('user_id');
        $user   = $this->userModel->get_user_by_id($userId);
        $deptId = $user['department_id'] ?? null;

        $db = \Config\Database::connect();
        $db->transStart();

        $successCount = 0;
        $auditLogs = [];

        foreach ($validItems as $v) {
            $item = $v['item'];
            $quantity = $v['quantity'];
            $centralSupplyId = $v['itemId'];

            // 1. Create a new department_supply row for this request
            $db->table('department_supply')->insert([
                'department_id'     => $deptId,
                'quantity_received' => 0,
                'quantity_used'     => 0,
                'quantity_on_hand'  => 0,
            ]);
            $deptSupplyId = $db->insertID();

            // 2. Create or get inventory item for the department (FK for supply record)
            $invItem = $db->table('inventory')
                          ->where('inventory_code', $item['inventory_code'])
                          ->get()->getRowArray();
            if (!$invItem) {
                $db->table('inventory')->insert([
                    'item_code'        => $item['item_code'],
                    'inventory_code'   => $item['inventory_code'],
                    'item_name'       => $item['item_name'],
                    'batch_num'       => $item['batch_num'],
                    'lot_num'         => $item['lot_num'],
                    'expiration_date' => $item['expiration_date'],
                    'unit'            => $item['unit'] ?? null,
                    'quantity'        => 0,
                    'category_id'     => $item['category_id'],
                ]);
                $invId = $db->insertID();
            } else {
                $invId = $invItem['inventory_id'];
            }

            // 3. Create the request
            $insertData = [
                'department_supply_id' => $deptSupplyId,
                'quantity_requested'   => $quantity,
                'quantity_served'      => 0,
                'request_status'       => 1,
                'user_id'              => $userId,
                'notes'                => $notes ? $notes : null,
            ];

            if ($this->requestModel->insert($insertData)) {
                $requestId = $this->requestModel->insertID();

                // 4. Create a supply record for this request
                $db->table('supply')->insert([
                    'request_id'           => $requestId,
                    'central_supply_id'    => $centralSupplyId,
                    'department_supply_id' => $deptSupplyId,
                    'inventory_id'         => $invId,
                    'batch_num'            => $item['batch_num'],
                    'lot_num'              => $item['lot_num'],
                    'expiration_date'      => $item['expiration_date'],
                    'unit'                 => $v['unit'] ? $v['unit'] : $item['unit'],
                    'quantity'             => 0,
                    'category_id'          => $item['category_id'],
                ]);

                $auditLogs[] = "{$quantity} {$item['unit']} of {$item['item_name']}";
                $successCount++;
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false || $successCount === 0) {
            session()->setFlashdata('create_request_modal_open', true);
            session()->setFlashdata('create_request_validation_errors', 'An error occurred while submitting your requests. Please try again or check your input.');
        } else {
            $logMsg = "{$user['full_name']} submitted a supply request batch for: " . implode(', ', $auditLogs) . " from Central Supply.";
            if ($notes) {
                $logMsg .= " Notes: {$notes}";
            }
            $this->auditModel->log_activity(
                'CREATE_SUPPLY_REQUEST',
                'Supply Requests',
                $logMsg
            );

            session()->setFlashdata('success', 'Supply request(s) submitted successfully!');
        }

        return redirect()->to('requests');
    }

    /**
     * Edit/update a pending supply request.
     */
    public function edit($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('requests');
        }

        if (!in_array(strtolower((string) session()->get('role')), ['admin', 'encoder'], true)) {
            session()->setFlashdata('error', 'You do not have permission to edit requests.');
            return redirect()->to('requests');
        }

        $request = $this->_getRequest($id);
        if (!$request) {
            session()->setFlashdata('error', 'Supply request not found.');
            return redirect()->to('requests');
        }

        if ((int)$request['request_status'] !== 1) {
            session()->setFlashdata('error', 'Only pending requests can be edited.');
            return redirect()->to('requests');
        }

        $itemId   = (int)$this->request->getPost('item_id');
        $quantity = (int)$this->request->getPost('quantity');
        $unit     = trim((string)$this->request->getPost('unit'));
        $notes    = trim((string)$this->request->getPost('notes'));

        if ($quantity <= 0) {
            session()->setFlashdata('error', 'Quantity must be greater than 0.');
            return redirect()->to('requests');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->requestModel->update($id, [
            'quantity_requested' => $quantity,
            'notes'              => $notes ?: null,
        ]);

        // Update unit in supply record
        $db->table('supply')
           ->where('request_id', $id)
           ->update(['unit' => $unit]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'An error occurred while updating the request.');
        } else {
            $this->auditModel->log_activity(
                'UPDATE_SUPPLY_REQUEST',
                'Supply Requests',
                "Updated supply request #{$id}: quantity changed to {$quantity}."
            );
            session()->setFlashdata('success', 'Supply request updated successfully!');
        }

        return redirect()->to('requests');
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
            return redirect()->to('requests');
        }

        if (empty($id)) {
            return redirect()->to('requests');
        }

        $request = $this->_getRequest($id);

        if (!$request) {
            session()->setFlashdata('error', 'Supply request not found.');
            return redirect()->to('requests');
        }

        if ((int)$request['request_status'] !== 1) {
            session()->setFlashdata('error', 'This request has already been processed.');
            return redirect()->to('requests');
        }

        $qtyRequested = (int)$request['quantity_requested'];
        $modalId = 'serveModal_' . $id;
        $db = \Config\Database::connect();

        // Full serving is always allocated using FEFO: batches with the nearest
        // expiry date are used first, and batches with no expiry are used last.
        $batches = $db->table('central_supply')
                      // Item code is the product identity. Names can vary due to
                      // corrections or typos between otherwise identical batches.
                      ->where('item_code', $request['item_code'])
                      ->where('unit', $request['item_unit'])
                      ->where('status', 1)
                      ->where('quantity_on_hand >', 0)
                      ->groupStart()
                          ->where('expiration_date >=', date('Y-m-d'))
                          ->orWhere('expiration_date', null)
                      ->groupEnd()
                      ->orderBy('expiration_date IS NULL', 'ASC', false)
                      ->orderBy('expiration_date', 'ASC')
                      ->orderBy('central_supply_id', 'ASC')
                      ->get()->getResultArray();

        $totalAvailable = array_sum(array_map(static function ($batch) {
            return (int) $batch['quantity_on_hand'];
        }, $batches));

        if ($totalAvailable < $qtyRequested) {
            session()->setFlashdata('open_modal', $modalId);
            session()->setFlashdata(
                'modal_errors',
                "Insufficient stock for {$request['item_name']}."
            );
            return redirect()->to('requests');
        }

        $csIds = [];
        $qties = [];
        $need = $qtyRequested;
        foreach ($batches as $batch) {
            if ($need <= 0) {
                break;
            }

            $take = min((int) $batch['quantity_on_hand'], $need);
            $csIds[] = (int) $batch['central_supply_id'];
            $qties[] = $take;
            $need -= $take;
        }

        $db->transStart();

        $totalServed = 0;
        $firstBatch = null;
        $servedBatchesInfo = [];

        foreach ($csIds as $i => $csId) {
            $csId = (int)$csId;
            $qty = isset($qties[$i]) ? (int)$qties[$i] : 0;
            if ($qty <= 0) continue;

            $csItem = $db->table('central_supply')
                         ->where('central_supply_id', $csId)
                         ->get()->getRowArray();

            if (!$csItem) {
                $db->transRollback();
                session()->setFlashdata('open_modal', $modalId);
                session()->setFlashdata('modal_errors', "Inventory batch #{$csId} not found.");
                return redirect()->to('requests');
            }

            if ($csItem['quantity_on_hand'] < $qty) {
                $db->transRollback();
                session()->setFlashdata('open_modal', $modalId);
                session()->setFlashdata('modal_errors', "Insufficient stock for inventory '{$csItem['inventory_code']}'.");
                return redirect()->to('requests');
            }

            if ($csItem['unit'] !== $request['item_unit']) {
                $db->transRollback();
                session()->setFlashdata('open_modal', $modalId);
                session()->setFlashdata('modal_errors', "Batch '{$csItem['inventory_code']}' unit ({$csItem['unit']}) does not match requested unit ({$request['item_unit']}).");
                return redirect()->to('requests');
            }

            // Keep the deduction conditional so a concurrent serve cannot make
            // a batch go below zero after the availability check above.
            $db->table('central_supply')
               ->where('central_supply_id', $csId)
               ->where('quantity_on_hand >=', $qty)
               ->set('quantity_on_hand', 'quantity_on_hand - ' . $qty, false)
               ->update();

            if ($db->affectedRows() !== 1) {
                $db->transRollback();
                session()->setFlashdata('open_modal', $modalId);
                session()->setFlashdata('modal_errors', "Insufficient stock for inventory '{$csItem['inventory_code']}'. Please try again.");
                return redirect()->to('requests');
            }

            $totalServed += $qty;
            if (!$firstBatch) $firstBatch = $csItem;
            $servedBatchesInfo[] = "{$qty} {$csItem['unit']} from inventory code '{$csItem['inventory_code']}' (Batch: {$csItem['batch_num']}, Exp: " . ($csItem['expiration_date'] ? date('Y-m-d', strtotime($csItem['expiration_date'])) : 'N/A') . ")";
        }

        if ($totalServed !== $qtyRequested) {
            $db->transRollback();
            session()->setFlashdata('open_modal', $modalId);
            session()->setFlashdata('modal_errors', "Quantity does not match the requested quantity.");
            return redirect()->to('requests');
        }

        // Get requester's department from joined request data
        $deptId    = $request['department_id'] ?? null;
        $deptName  = $request['department_name'] ?? 'Department';

        // Find the pre-created supply record for this request
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
                       'quantity_received' => $deptSupply['quantity_received'] + $totalServed,
                       'quantity_on_hand'  => $deptSupply['quantity_on_hand'] + $totalServed,
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
                       'quantity' => $invItem['quantity'] + $totalServed,
                   ]);
            }

            // Update supply record quantity and details (first batch as primary)
            $supplyUpdate = [
                'quantity' => $totalServed,
                'batch_num' => $firstBatch['batch_num'],
                'lot_num' => $firstBatch['lot_num'],
                'expiration_date' => $firstBatch['expiration_date'],
            ];
            if ((int)$supplyRec['central_supply_id'] !== (int)$firstBatch['central_supply_id']) {
                $supplyUpdate['central_supply_id'] = $firstBatch['central_supply_id'];
            }
            $db->table('supply')
               ->where('supply_id', $supplyRec['supply_id'])
               ->update($supplyUpdate);
        } else {
            $deptSupplyId = null;
        }

        $this->requestModel->update($id, [
            'request_status'      => 3,
            'quantity_served'     => $totalServed,
            'department_supply_id' => $deptSupplyId,
            'served_date'         => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'An error occurred while serving the request. Please try again.');
        } else {
            $this->auditModel->log_activity(
                'SERVE_SUPPLY_REQUEST',
                'Supply Requests',
                "Served supply request #{$id} for {$request['requester_full_name']}. Transferred {$totalServed} {$firstBatch['unit']} of '{$firstBatch['item_name']}' to department '{$deptName}'. Inventory used: " . implode(', ', $servedBatchesInfo) . "."
            );
            session()->setFlashdata('success', "Request served! {$totalServed} {$firstBatch['unit']} of '{$firstBatch['item_name']}' transferred to {$deptName}.");
        }

        return redirect()->to('requests');
    }

    /**
     * Partially serve a pending or partially served supply request.
     */
    public function partial($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (!is_admin_role()) {
            session()->setFlashdata('error', 'Only administrators can serve supply requests.');
            return redirect()->to('requests');
        }

        if (empty($id)) {
            return redirect()->to('requests');
        }

        $request = $this->_getRequest($id);

        if (!$request) {
            session()->setFlashdata('error', 'Supply request not found.');
            return redirect()->to('requests');
        }

        if (!in_array($request['request_status'], [1, 2])) {
            session()->setFlashdata('error', 'This request has already been processed.');
            return redirect()->to('requests');
        }

        $qtyRequested = (int)$request['quantity_requested'];
        $qtyServed    = (int)$request['quantity_served'];
        $remainingQty = $qtyRequested - $qtyServed;

        $modalId = 'partialModal_' . $id;

        $totalServed = (int) $this->request->getPost('quantity');

        if ($totalServed <= 0 || $totalServed >= $remainingQty) {
            session()->setFlashdata('open_modal', $modalId);
            session()->setFlashdata('modal_errors', "Quantity must be less than the remaining requested quantity ({$remainingQty}).");
            return redirect()->to('requests');
        }

        $db = \Config\Database::connect();

        // Allocate a partial serve by FEFO. Expiring batches are consumed first;
        // a batch without an expiration date is used only after dated batches.
        $batches = $db->table('central_supply')
                      // Allocate across all batches for this product code, even
                      // when a batch name was entered with a minor variation.
                      ->where('item_code', $request['item_code'])
                      ->where('unit', $request['item_unit'])
                      ->where('status', 1)
                      ->where('quantity_on_hand >', 0)
                      ->groupStart()
                          ->where('expiration_date >=', date('Y-m-d'))
                          ->orWhere('expiration_date', null)
                      ->groupEnd()
                      ->orderBy('expiration_date IS NULL', 'ASC', false)
                      ->orderBy('expiration_date', 'ASC')
                      ->orderBy('central_supply_id', 'ASC')
                      ->get()->getResultArray();

        $totalAvailable = array_sum(array_map(static function ($batch) {
            return (int) $batch['quantity_on_hand'];
        }, $batches));

        if ($totalAvailable < $totalServed) {
            session()->setFlashdata('open_modal', $modalId);
            session()->setFlashdata(
                'modal_errors',
                "Insufficient stock for {$request['item_name']}."
            );
            return redirect()->to('requests');
        }

        $csIds = [];
        $qties = [];
        $need = $totalServed;
        foreach ($batches as $batch) {
            if ($need <= 0) {
                break;
            }

            $take = min((int) $batch['quantity_on_hand'], $need);
            $csIds[] = (int) $batch['central_supply_id'];
            $qties[] = $take;
            $need -= $take;
        }

        $db->transStart();

        $firstBatch = null;
        $servedBatchesInfo = [];

        foreach ($csIds as $i => $csId) {
            $csId = (int)$csId;
            $qty = isset($qties[$i]) ? (int)$qties[$i] : 0;
            if ($qty <= 0) continue;

            $csItem = $db->table('central_supply')
                         ->where('central_supply_id', $csId)
                         ->get()->getRowArray();

            if (!$csItem) {
                $db->transRollback();
                session()->setFlashdata('open_modal', $modalId);
                session()->setFlashdata('modal_errors', "Inventory batch #{$csId} not found.");
                return redirect()->to('requests');
            }

            if ($csItem['quantity_on_hand'] < $qty) {
                $db->transRollback();
                session()->setFlashdata('open_modal', $modalId);
                session()->setFlashdata('modal_errors', "Insufficient stock for inventory '{$csItem['inventory_code']}'.");
                return redirect()->to('requests');
            }

            if ($csItem['unit'] !== $request['item_unit']) {
                $db->transRollback();
                session()->setFlashdata('open_modal', $modalId);
                session()->setFlashdata('modal_errors', "Batch '{$csItem['inventory_code']}' unit ({$csItem['unit']}) does not match requested unit ({$request['item_unit']}).");
                return redirect()->to('requests');
            }

            $db->table('central_supply')
               ->where('central_supply_id', $csId)
               ->where('quantity_on_hand >=', $qty)
               ->set('quantity_on_hand', 'quantity_on_hand - ' . $qty, false)
               ->update();

            if ($db->affectedRows() !== 1) {
                $db->transRollback();
                session()->setFlashdata('open_modal', $modalId);
                session()->setFlashdata('modal_errors', "Insufficient stock for inventory '{$csItem['inventory_code']}'. Please try again.");
                return redirect()->to('requests');
            }

            if (!$firstBatch) $firstBatch = $csItem;
            $servedBatchesInfo[] = "{$qty} {$csItem['unit']} from inventory code '{$csItem['inventory_code']}' (Batch: {$csItem['batch_num']}, Exp: " . ($csItem['expiration_date'] ? date('Y-m-d', strtotime($csItem['expiration_date'])) : 'N/A') . ")";
        }

        // Get requester's department from joined request data
        $deptId    = $request['department_id'] ?? null;
        $deptName  = $request['department_name'] ?? 'Department';

        // Find or create inventory item for the department
        $invItem = $db->table('inventory')
                      ->where('inventory_code', $firstBatch['inventory_code'])
                      ->get()->getRowArray();

        if (!$invItem) {
            $db->table('inventory')->insert([
                'item_code'       => $firstBatch['item_code'],
                'inventory_code'  => $firstBatch['inventory_code'],
                'item_name'       => $firstBatch['item_name'],
                'batch_num'       => $firstBatch['batch_num'],
                'lot_num'         => $firstBatch['lot_num'],
                'expiration_date' => $firstBatch['expiration_date'],
                'unit'            => $firstBatch['unit'] ?? null,
                'quantity'        => $totalServed,
                'category_id'     => $firstBatch['category_id'],
            ]);
            $invId = $db->insertID();
        } else {
            $invId = $invItem['inventory_id'];
        }

        // Upsert department_supply
        $deptSupply = $db->table('department_supply')
                         ->join('supply', 'supply.department_supply_id = department_supply.department_supply_id')
                         ->where('department_supply.department_id', $deptId)
                         ->where('supply.inventory_id', $invId)
                         ->get()->getRowArray();

        if ($deptSupply) {
            $db->table('department_supply')
               ->where('department_supply_id', $deptSupply['department_supply_id'])
               ->update([
                   'quantity_received' => $deptSupply['quantity_received'] + $totalServed,
                   'quantity_on_hand'  => $deptSupply['quantity_on_hand'] + $totalServed,
               ]);
            $deptSupplyId = $deptSupply['department_supply_id'];
        } else {
            $db->table('department_supply')->insert([
                'department_id'     => $deptId,
                'quantity_received' => $totalServed,
                'quantity_used'     => 0,
                'quantity_on_hand'  => $totalServed,
            ]);
            $deptSupplyId = $db->insertID();

            $db->table('supply')->insert([
                'request_id'           => $id,
                'central_supply_id'    => $firstBatch['central_supply_id'],
                'department_supply_id' => $deptSupplyId,
                'inventory_id'         => $invId,
                'batch_num'            => $firstBatch['batch_num'],
                'lot_num'              => $firstBatch['lot_num'],
                'expiration_date'      => $firstBatch['expiration_date'],
                'unit'                 => $firstBatch['unit'],
                'quantity'             => $totalServed,
                'category_id'          => $firstBatch['category_id'],
            ]);
        }

        // Update the supply record for this request
        $existingSupply = $db->table('supply')->where('request_id', $id)->get()->getRowArray();
        if ($existingSupply) {
            $supplyUpdate = [
                'batch_num'       => $firstBatch['batch_num'],
                'lot_num'         => $firstBatch['lot_num'],
                'expiration_date' => $firstBatch['expiration_date'],
                'quantity'        => ($existingSupply['quantity'] ?? 0) + $totalServed,
            ];
            if ((int)$existingSupply['central_supply_id'] !== (int)$firstBatch['central_supply_id']) {
                $supplyUpdate['central_supply_id'] = $firstBatch['central_supply_id'];
            }
            $db->table('supply')->where('supply_id', $existingSupply['supply_id'])->update($supplyUpdate);
        }

        // Update request
        $notes = trim((string) $this->request->getPost('partial_notes'));
        $updateData = [
            'request_status'      => 2,
            'quantity_served'     => $qtyServed + $totalServed,
            'department_supply_id' => $deptSupplyId,
            'partial_date'        => date('Y-m-d H:i:s'),
        ];
        if ($notes !== '') {
            $updateData['notes'] = $notes;
        }
        $this->requestModel->update($id, $updateData);

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'An error occurred while partially serving the request.');
        } else {
            $auditDesc = "Partially served supply request #{$id} for {$request['requester_full_name']}. Served {$totalServed} of {$qtyRequested} {$firstBatch['unit']} of '{$firstBatch['item_name']}'. Inventory used: " . implode(', ', $servedBatchesInfo) . ".";
            if ($notes) {
                $auditDesc .= " Remarks: {$notes}";
            }
            $this->auditModel->log_activity(
                'PARTIAL_SERVE_SUPPLY_REQUEST',
                'Supply Requests',
                $auditDesc
            );
            session()->setFlashdata('success', "Partially served! {$totalServed} {$firstBatch['unit']} of '{$firstBatch['item_name']}' transferred to {$deptName}.");
        }

        return redirect()->to('requests');
    }

    /**
     * Reject a pending supply request (Admin only).
     */
    public function reject($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (!is_admin_role()) {
            session()->setFlashdata('error', 'Only administrators can reject supply requests.');
            return redirect()->to('requests');
        }

        if (empty($id)) {
            return redirect()->to('requests');
        }

        $request = $this->_getRequest($id);

        if (!$request) {
            session()->setFlashdata('error', 'Supply request not found.');
            return redirect()->to('requests');
        }

        if ((int)$request['request_status'] !== 1) {
            session()->setFlashdata('error', 'This request has already been processed.');
            return redirect()->to('requests');
        }

        $notes = trim((string) $this->request->getPost('reject_notes'));

        $updateData = ['request_status' => 5, 'cancelled_date' => date('Y-m-d H:i:s')];
        if ($notes !== '') {
            $updateData['notes'] = $notes;
        }

        if ($this->requestModel->update($id, $updateData)) {
            $auditDesc = "Rejected supply request #{$id} from {$request['requester_full_name']} for {$request['quantity_requested']} {$request['item_unit']} of '{$request['item_name']}'.";
            if ($notes) {
                $auditDesc .= " Remarks: {$notes}";
            }
            $this->auditModel->log_activity(
                'REJECT_SUPPLY_REQUEST',
                'Supply Requests',
                $auditDesc
            );
            session()->setFlashdata('success', 'Supply request rejected successfully.');
        } else {
            session()->setFlashdata('error', 'An error occurred while rejecting the request.');
        }

        return redirect()->to('requests');
    }

    /**
     * Cancel a pending or partially served request (encoder or admin).
     */
    public function cancel($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('requests');
        }

        $request = $this->_getRequest($id);

        if (!$request) {
            session()->setFlashdata('error', 'Supply request not found.');
            return redirect()->to('requests');
        }

        $role = strtolower((string) session()->get('role'));

        // Encoders can cancel their own pending or partially served requests; admins can cancel pending or partially served
        if ($role === 'encoder') {
            if (!in_array((int)$request['request_status'], [1, 2], true)) {
                session()->setFlashdata('error', 'Only pending or partially served requests can be cancelled.');
                return redirect()->to('requests');
            }
        } elseif (is_admin_role()) {
            if (!in_array((int)$request['request_status'], [1, 2], true)) {
                session()->setFlashdata('error', 'Only pending or partially served requests can be cancelled.');
                return redirect()->to('requests');
            }
        } else {
            session()->setFlashdata('error', 'You do not have permission to cancel requests.');
            return redirect()->to('requests');
        }

        $notes = trim((string) $this->request->getPost('cancel_notes'));

        $updateData = ['request_status' => 4, 'cancelled_date' => date('Y-m-d H:i:s')];
        if ($notes !== '') {
            $updateData['notes'] = $notes;
        }

        if ($this->requestModel->update($id, $updateData)) {
            $auditDesc = "Cancelled supply request #{$id} from {$request['requester_full_name']} for {$request['quantity_requested']} {$request['item_unit']} of '{$request['item_name']}'.";
            if ($notes) {
                $auditDesc .= " Reason: {$notes}";
            }
            $this->auditModel->log_activity(
                'CANCEL_SUPPLY_REQUEST',
                'Supply Requests',
                $auditDesc
            );
            session()->setFlashdata('success', 'Supply request cancelled successfully.');
        } else {
            session()->setFlashdata('error', 'An error occurred while cancelling the request.');
        }

        return redirect()->to('requests');
    }

    /**
     * Complete a partially served supply request by serving the remaining quantity.
     */
    public function complete_partial($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (!is_admin_role()) {
            session()->setFlashdata('error', 'Only administrators can complete supply requests.');
            return redirect()->to('requests');
        }

        if (empty($id)) {
            return redirect()->to('requests');
        }

        $request = $this->_getRequest($id);

        if (!$request) {
            session()->setFlashdata('error', 'Supply request not found.');
            return redirect()->to('requests');
        }

        if ((int)$request['request_status'] !== 2) {
            session()->setFlashdata('error', 'Only partially served requests can be completed.');
            return redirect()->to('requests');
        }

        $qtyRequested = (int)$request['quantity_requested'];
        $qtyServed    = (int)$request['quantity_served'];
        $remainingQty = $qtyRequested - $qtyServed;

        if ($remainingQty <= 0) {
            session()->setFlashdata('error', 'This request is already fully served.');
            return redirect()->to('requests');
        }

        $modalId = 'completePartialModal_' . $id;
        $db = \Config\Database::connect();

        // Finish partial requests with the same FEFO allocation as full serves.
        $batches = $db->table('central_supply')
                      ->where('item_code', $request['item_code'])
                      ->where('unit', $request['item_unit'])
                      ->where('status', 1)
                      ->where('quantity_on_hand >', 0)
                      ->groupStart()
                          ->where('expiration_date >=', date('Y-m-d'))
                          ->orWhere('expiration_date', null)
                      ->groupEnd()
                      ->orderBy('expiration_date IS NULL', 'ASC', false)
                      ->orderBy('expiration_date', 'ASC')
                      ->orderBy('central_supply_id', 'ASC')
                      ->get()->getResultArray();

        $totalAvailable = array_sum(array_map(static function ($batch) {
            return (int) $batch['quantity_on_hand'];
        }, $batches));

        if ($totalAvailable < $remainingQty) {
            session()->setFlashdata('open_modal', $modalId);
            session()->setFlashdata(
                'modal_errors',
                "Insufficient stock for {$request['item_name']}."
            );
            return redirect()->to('requests');
        }

        $csIds = [];
        $qties = [];
        $need = $remainingQty;
        foreach ($batches as $batch) {
            if ($need <= 0) {
                break;
            }

            $take = min((int) $batch['quantity_on_hand'], $need);
            $csIds[] = (int) $batch['central_supply_id'];
            $qties[] = $take;
            $need -= $take;
        }

        $db->transStart();
        $totalServed = 0;
        $firstBatch = null;
        $servedBatchesInfo = [];

        foreach ($csIds as $i => $csId) {
            $csId = (int)$csId;
            $qty = isset($qties[$i]) ? (int)$qties[$i] : 0;
            if ($qty <= 0) continue;

            $csItem = $db->table('central_supply')
                         ->where('central_supply_id', $csId)
                         ->get()->getRowArray();

            if (!$csItem) {
                $db->transRollback();
                session()->setFlashdata('open_modal', $modalId);
                session()->setFlashdata('modal_errors', "Inventory batch #{$csId} not found.");
                return redirect()->to('requests');
            }

            if ($csItem['quantity_on_hand'] < $qty) {
                $db->transRollback();
                session()->setFlashdata('open_modal', $modalId);
                session()->setFlashdata('modal_errors', "Insufficient stock for inventory '{$csItem['inventory_code']}'.");
                return redirect()->to('requests');
            }

            if ($csItem['unit'] !== $request['item_unit']) {
                $db->transRollback();
                session()->setFlashdata('open_modal', $modalId);
                session()->setFlashdata('modal_errors', "Batch '{$csItem['inventory_code']}' unit ({$csItem['unit']}) does not match requested unit ({$request['item_unit']}).");
                return redirect()->to('requests');
            }

            $db->table('central_supply')
               ->where('central_supply_id', $csId)
               ->where('quantity_on_hand >=', $qty)
               ->set('quantity_on_hand', 'quantity_on_hand - ' . $qty, false)
               ->update();

            if ($db->affectedRows() !== 1) {
                $db->transRollback();
                session()->setFlashdata('open_modal', $modalId);
                session()->setFlashdata('modal_errors', "Insufficient stock for inventory '{$csItem['inventory_code']}'. Please try again.");
                return redirect()->to('requests');
            }

            $totalServed += $qty;
            if (!$firstBatch) $firstBatch = $csItem;
            $servedBatchesInfo[] = "{$qty} {$csItem['unit']} from inventory code '{$csItem['inventory_code']}' (Batch: {$csItem['batch_num']}, Exp: " . ($csItem['expiration_date'] ? date('Y-m-d', strtotime($csItem['expiration_date'])) : 'N/A') . ")";
        }

        if ($totalServed !== $remainingQty) {
            $db->transRollback();
            session()->setFlashdata('open_modal', $modalId);
            session()->setFlashdata('modal_errors', "Quantity does not match the requested quantity.");
            return redirect()->to('requests');
        }

        // Get requester's department
        $deptId    = $request['department_id'] ?? null;
        $deptName  = $request['department_name'] ?? 'Department';

        // Find or create inventory item for the department
        $invItem = $db->table('inventory')
                      ->where('inventory_code', $firstBatch['inventory_code'])
                      ->get()->getRowArray();

        if (!$invItem) {
            $db->table('inventory')->insert([
                'item_code'       => $firstBatch['item_code'],
                'inventory_code'  => $firstBatch['inventory_code'],
                'item_name'       => $firstBatch['item_name'],
                'batch_num'       => $firstBatch['batch_num'],
                'lot_num'         => $firstBatch['lot_num'],
                'expiration_date' => $firstBatch['expiration_date'],
                'unit'            => $firstBatch['unit'] ?? null,
                'quantity'        => $totalServed,
                'category_id'     => $firstBatch['category_id'],
            ]);
            $invId = $db->insertID();
        } else {
            $invId = $invItem['inventory_id'];
        }

        // Upsert department_supply for the remaining quantity
        $deptSupply = $db->table('department_supply')
                         ->join('supply', 'supply.department_supply_id = department_supply.department_supply_id')
                         ->where('department_supply.department_id', $deptId)
                         ->where('supply.inventory_id', $invId)
                         ->get()->getRowArray();

        if ($deptSupply) {
            $db->table('department_supply')
               ->where('department_supply_id', $deptSupply['department_supply_id'])
               ->update([
                   'quantity_received' => $deptSupply['quantity_received'] + $totalServed,
                   'quantity_on_hand'  => $deptSupply['quantity_on_hand'] + $totalServed,
               ]);
            $deptSupplyId = $deptSupply['department_supply_id'];
        } else {
            $db->table('department_supply')->insert([
                'department_id'     => $deptId,
                'quantity_received' => $totalServed,
                'quantity_used'     => 0,
                'quantity_on_hand'  => $totalServed,
            ]);
            $deptSupplyId = $db->insertID();

            $db->table('supply')->insert([
                'request_id'           => $id,
                'central_supply_id'    => $firstBatch['central_supply_id'],
                'department_supply_id' => $deptSupplyId,
                'inventory_id'         => $invId,
                'batch_num'            => $firstBatch['batch_num'],
                'lot_num'              => $firstBatch['lot_num'],
                'expiration_date'      => $firstBatch['expiration_date'],
                'unit'                 => $firstBatch['unit'],
                'quantity'             => $totalServed,
                'category_id'          => $firstBatch['category_id'],
            ]);
        }

        // Update the supply record for this request
        $existingSupply = $db->table('supply')->where('request_id', $id)->get()->getRowArray();
        if ($existingSupply) {
            $supplyUpdate = [
                'batch_num'       => $firstBatch['batch_num'],
                'lot_num'         => $firstBatch['lot_num'],
                'expiration_date' => $firstBatch['expiration_date'],
                'quantity'        => ($existingSupply['quantity'] ?? 0) + $totalServed,
            ];
            if ((int)$existingSupply['central_supply_id'] !== (int)$firstBatch['central_supply_id']) {
                $supplyUpdate['central_supply_id'] = $firstBatch['central_supply_id'];
            }
            $db->table('supply')->where('supply_id', $existingSupply['supply_id'])->update($supplyUpdate);
        }

        // Update request to Served and clear notes
        $this->requestModel->update($id, [
            'request_status'      => 3,
            'quantity_served'     => $qtyRequested,
            'department_supply_id' => $deptSupplyId,
            'served_date'         => date('Y-m-d H:i:s'),
            'closed_date'         => date('Y-m-d H:i:s'),
            'notes'               => null,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'An error occurred while completing the partial request.');
        } else {
            $this->auditModel->log_activity(
                'COMPLETE_PARTIAL_SUPPLY_REQUEST',
                'Supply Requests',
                "Completed partial supply request #{$id} for {$request['requester_full_name']}. Served remaining {$totalServed} {$firstBatch['unit']} of '{$firstBatch['item_name']}'. Inventory used: " . implode(', ', $servedBatchesInfo) . "."
            );
            session()->setFlashdata('success', "Request completed! Remaining {$totalServed} {$firstBatch['unit']} of '{$firstBatch['item_name']}' transferred to {$deptName}.");
        }

        return redirect()->to('requests');
    }

    /**
     * Delete a supply request (Admin only).
     * Must remove supply rows (FK: supply.request_id -> request.request_id) before
     * deleting the request, and then clean up orphaned department_supply rows.
     */
    public function archive($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('requests');
        }

        $request = $this->_getRequest($id);

        if (!$request) {
            session()->setFlashdata('error', 'Supply request not found.');
            return redirect()->to('requests');
        }

        $this->requestModel->update($id, ['status' => 0]);

        $this->auditModel->log_activity(
            'ARCHIVE_SUPPLY_REQUEST',
            'Supply Requests',
            "Archived supply request #{$id} from {$request['requester_full_name']} for {$request['quantity_requested']} unit(s) of '{$request['item_name']}'"
        );
        session()->setFlashdata('success', 'Supply request archived successfully.');

        return redirect()->to('requests');
    }

    public function restore($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (!is_admin_role()) {
            session()->setFlashdata('error', 'Only administrators can restore supply requests.');
            return redirect()->to('requests');
        }

        if (empty($id)) {
            return redirect()->to('requests');
        }

        $request = $this->requestModel->find($id);

        if (!$request) {
            session()->setFlashdata('error', 'Supply request not found.');
            return redirect()->to('requests');
        }

        $this->requestModel->update($id, ['status' => 1]);

        $this->auditModel->log_activity(
            'RESTORE_SUPPLY_REQUEST',
            'Supply Requests',
            "Restored supply request #{$id}."
        );
        session()->setFlashdata('success', 'Supply request restored successfully.');

        return redirect()->to('requests');
    }

    /**
     * Bulk-archive selected supply requests (Admin only).
     */
    public function archive_selected()
    {
        if ($res = $this->checkAuth()) return $res;

        if (!is_admin_role()) {
            session()->setFlashdata('error', 'Only administrators can archive supply requests.');
            return redirect()->to('requests');
        }

        $ids = $this->request->getPost('request_ids');
        if (empty($ids) || !is_array($ids)) {
            session()->setFlashdata('error', 'No supply requests selected.');
            return redirect()->to('requests');
        }

        $count = 0;
        foreach ($ids as $id) {
            $this->requestModel->update($id, ['status' => 0]);
            $count++;
        }

        $this->auditModel->log_activity(
            'BULK_ARCHIVE_SUPPLY_REQUESTS',
            'Supply Requests',
            "Bulk archived {$count} supply request(s)."
        );
        session()->setFlashdata('success', "Successfully archived {$count} supply request(s).");

        return redirect()->to('requests');
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
