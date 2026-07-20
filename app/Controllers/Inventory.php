<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\AuditModel;
use App\Models\UserModel;
use App\Models\UnitModel;

class Inventory extends BaseController
{
    protected $itemModel;
    protected $auditModel;
    protected $userModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->itemModel = new ItemModel();
        $this->auditModel = new AuditModel();
        $this->userModel = new UserModel();
    }

    protected function checkAuth()
    {
        if (!session()->get('logged_in')) {
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

    protected function resolveSupplierId($sourceValue, $sourceName = null)
    {
        $db = \Config\Database::connect();

        switch ($sourceValue) {
            case 'donation':
                $sourceType = 'Donation';
                break;
            case 'others':
                $customName = trim($sourceName ?? '');
                if (!empty($customName)) {
                    $existing = $db->table('supplier')
                        ->where('supplier_name', $customName)
                        ->get()
                        ->getRowArray();

                    if (!empty($existing)) {
                        return (int)$existing['supplier_id'];
                    }

                    $db->table('supplier')->insert([
                        'supplier_type' => 'Others',
                        'supplier_name' => $customName,
                        'status'        => 1,
                    ]);
                    return (int)$db->insertID();
                }
                // fall through to default if no name provided
            default:
                $sourceType = 'Supplier';
        }

        $existing = $db->table('supplier')
            ->where('supplier_type', $sourceType)
            ->get()
            ->getRowArray();

        if (!empty($existing)) {
            return (int)$existing['supplier_id'];
        }

        $db->table('supplier')->insert([
            'supplier_type' => $sourceType,
            'supplier_name' => $sourceType,
        ]);

        return (int)$db->insertID();
    }

    /**
     * Display inventory search and filtering listing dashboard
     */
    public function index()
    {
        if ($res = $this->checkAuth()) return $res;

        $userId = session()->get('user_id');
        $user = $this->userModel->get_user_by_id($userId);

        $role = session()->get('role');
        $isAdmin = in_array(strtolower((string) $role), ['admin', 'administrator', 'dev'], true);
        $deptId = $user['department_id'] ?? 3; // default fallback to Central Supplies

        $data['title'] = $isAdmin ? 'Central Supply Inventory' : ($user['department_name'] ?? 'My') . ' Inventory';

        $search = $this->request->getGet('search');
        $stock_status = $this->request->getGet('stock_status');
        $category_id  = $this->request->getGet('category_id');

        $data['search'] = $search;
        $data['department_context'] = $isAdmin ? 'Central Supply' : ($user['department_name'] ?? 'Central Supplies');
        $data['department_name'] = $isAdmin ? 'Central Supply' : ($user['department_name'] ?? 'Local');
        $data['stock_status'] = $stock_status;
        $data['category_id']  = $category_id;
        $data['show_archived'] = false;

        $data['items'] = $this->itemModel->get_items($search, $isAdmin ? 'admin' : 'staff', $deptId, $stock_status, $category_id);

        // Fetch individual batches for expandable row details
        $itemCodes = array_column($data['items'], 'item_code');
        $batches = $this->itemModel->get_batches_by_item_codes($itemCodes, $isAdmin, $deptId);
        $data['batches_by_code'] = [];
        foreach ($batches as $batch) {
            $data['batches_by_code'][$batch['item_code']][] = $batch;
        }

        $data['categories'] = \Config\Database::connect()
            ->table('category')
            ->where('status', 1)
            ->orderBy('category_code', 'ASC')
            ->get()
            ->getResultArray();

        $data['suppliers'] = \Config\Database::connect()
            ->table('supplier')
            ->where('status', 1)
            ->orderBy('supplier_name', 'ASC')
            ->get()
            ->getResultArray();

        $data['units'] = (new UnitModel())->get_units();

        // Get all existing item codes for the dropdown
        $data['all_item_codes'] = \Config\Database::connect()
            ->table('central_supply')
            ->select('item_code')
            ->distinct()
            ->where('status', 1)
            ->orderBy('item_code', 'ASC')
            ->get()
            ->getResultArray();

        if (!$isAdmin) {
            $data['department_batches'] = [];
        }

        return view('templates/header', $data)
             . view('inventory', $data)
             . view('templates/footer');
    }

    /**
     * Create a new inventory item (POST only)
     */
    public function create()
    {
        if ($res = $this->checkAuth()) return $res;

        if (strcasecmp($this->request->getMethod(), 'post') !== 0) {
            return redirect()->to('inventory');
        }

        $role = session()->get('role');
        if (in_array(strtolower((string) $role), ['viewer', 'encoder'], true)) {
            session()->setFlashdata('error', 'You do not have permission to add inventory items.');
            return redirect()->to('inventory');
        }

        $userId = session()->get('user_id');
        $user   = $this->userModel->get_user_by_id($userId);
        $isAdmin = in_array(strtolower((string) $role), ['admin', 'administrator', 'dev'], true);

        $rules = [
            'item_code'        => 'required|alpha_dash|max_length[50]',
            'name'        => 'required|max_length[150]',
            'category_id' => 'required|integer',
            'supplier_type' => 'required|in_list[supplier,donation,others]',
            'supplier_name' => 'permit_empty|max_length[150]',
            'quantity'    => 'required|integer|greater_than[0]',
            'unit'               => 'required',
            'expiration_date'    => 'required|valid_date',
            'manufacturing_date' => 'permit_empty|valid_date',
        ];

        if ($this->validate($rules)) {
            $expiration = $this->request->getPost('expiration_date');
            if (!empty($expiration) && $expiration < date('Y-m-d')) {
                session()->setFlashdata('modal_mode', 'create');
                session()->setFlashdata('modal_errors', '<li>Expiration date cannot be in the past.</li>');
                return redirect()->to('inventory')->withInput();
            }

            $categoryId = (int)$this->request->getPost('category_id');
            $itemCode = strtoupper($this->request->getPost('item_code'));
            $inventoryCode = $this->itemModel->generate_inventory_code($categoryId);

            if (empty($inventoryCode)) {
                session()->setFlashdata('modal_mode', 'create');
                session()->setFlashdata('modal_errors', '<li>Failed to generate inventory code. Please check the category.</li>');
                return redirect()->to('inventory')->withInput();
            }

            $dbCheck = \Config\Database::connect();
            $existsInCentral = $dbCheck->table('central_supply')->where('inventory_code', $inventoryCode)->get()->getRow();
            $existsInInv     = $dbCheck->table('inventory')->where('inventory_code', $inventoryCode)->get()->getRow();
            if ($existsInCentral || $existsInInv) {
                session()->setFlashdata('modal_mode', 'create');
                session()->setFlashdata('modal_errors', '<li>Could not generate a unique inventory code. Please try again.</li>');
                return redirect()->to('inventory')->withInput();
            }

            $db = \Config\Database::connect();
            $db->transStart();

            $itemName   = ucwords(strtolower($this->request->getPost('name')));
            $quantity   = (int)$this->request->getPost('quantity');
            $sourceId   = $this->resolveSupplierId($this->request->getPost('supplier_type'), $this->request->getPost('supplier_name'));
            $batchNum   = $this->request->getPost('batch_num') ?: null;
            $lotNum     = $this->request->getPost('lot_num')   ?: null;
            $expiration = $this->request->getPost('expiration_date') ?: null;
            $manufacturingDate = $this->request->getPost('manufacturing_date') ?: null;
            $unit       = $this->request->getPost('unit') ?: null;
            $remarks    = $this->request->getPost('remarks') ?: null;

            if ($isAdmin) {
                // Insert into central_supply
                $this->itemModel->set_table('central_supply');
                $insert_data = [
                    'inventory_code'   => $inventoryCode,
                    'item_code'        => $itemCode,
                    'item_name'        => $itemName,
                    'batch_num'        => $batchNum,
                    'lot_num'          => $lotNum,
                    'expiration_date'  => $expiration,
                    'manufacturing_date' => $manufacturingDate,
                    'unit'             => $unit,
                    'quantity'         => $quantity,
                    'quantity_on_hand' => $quantity,
                    'category_id'      => $categoryId,
                'supplier_id' => $sourceId,
                    'remarks'          => $remarks,
                ];
                $this->itemModel->insert($insert_data);
                $itemId = $db->insertID();
            } else {
                // Insert into inventory (department stock)
                $this->itemModel->set_table('inventory');
                $insert_data = [
                    'inventory_code'   => $inventoryCode,
                    'item_code'       => $itemCode,
                    'item_name'       => $itemName,
                    'batch_num'       => $batchNum,
                    'lot_num'         => $lotNum,
                    'expiration_date' => $expiration,
                    'manufacturing_date' => $manufacturingDate,
                    'unit'            => $unit,
                    'quantity'        => $quantity,
                    'category_id'     => $categoryId,
                    'remarks'         => $remarks,
                ];
                $this->itemModel->insert($insert_data);
                $itemId = $db->insertID();

                // Get or create central_supply entry for this batch
                $csItem = $db->table('central_supply')->where('inventory_code', $inventoryCode)->get()->getRowArray();
                if (!$csItem) {
                    $db->table('central_supply')->insert([
                        'inventory_code'   => $inventoryCode,
                        'item_code'        => $itemCode,
                        'item_name'        => $itemName,
                        'batch_num'        => $batchNum,
                        'lot_num'          => $lotNum,
                        'expiration_date'  => $expiration,
                        'manufacturing_date' => $manufacturingDate,
                        'unit'             => $unit,
                        'quantity'         => 0,
                        'quantity_on_hand' => 0,
                        'category_id'      => $categoryId,
                        'supplier_id'      => $sourceId, // supplier tracked in central_supply only
                    ]);
                    $csId = $db->insertID();
                } else {
                    $csId = $csItem['central_supply_id'];
                }

                // Link to department_supply
                $db->table('department_supply')->insert([
                    'department_id'     => $user['department_id'],
                    'quantity_received' => $quantity,
                    'quantity_used'     => 0,
                    'quantity_on_hand'  => $quantity,
                ]);
                $deptSupplyId = $db->insertID();

                // Create a dummy request representing manual addition
                $db->table('request')->insert([
                    'department_supply_id' => $deptSupplyId,
                    'quantity_requested'   => $quantity,
                    'quantity_served'      => $quantity,
                    'request_status'               => 1,
                ]);
                $reqId = $db->insertID();

                // Link via supply table
                $db->table('supply')->insert([
                    'request_id'           => $reqId,
                    'central_supply_id'    => $csId,
                    'department_supply_id' => $deptSupplyId,
                    'inventory_id'         => $itemId,
                    'batch_num'            => $batchNum,
                    'lot_num'              => $lotNum,
                    'expiration_date'      => $expiration,
                    'unit'                 => $unit,
                    'quantity'             => $quantity,
                    'category_id'          => $categoryId,
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                session()->setFlashdata('error', 'An error occurred while creating the item.');
            } else {
                $auditDesc = "Added new item: {$itemName} (Item Code: {$itemCode}, Inventory Code: {$inventoryCode}) with initial quantity of {$quantity}.";
                if ($remarks) {
                    $auditDesc .= " Remarks: {$remarks}";
                }
                $this->auditModel->log_activity(
                    'ADD_ITEM',
                    'Inventory',
                    $auditDesc,
                    $itemId
                );
                session()->setFlashdata('success', 'Item successfully added to inventory!');
            }
        } else {
            session()->setFlashdata('modal_mode', 'create');
            session()->setFlashdata('modal_errors', $this->validator->listErrors());
        }

        return redirect()->to('inventory');
    }


    /**
     * Edit/Update an existing item details
     */
    public function edit($id = NULL)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('inventory');
        }

        if (strcasecmp($this->request->getMethod(), 'post') !== 0) {
            return redirect()->to('inventory');
        }

        $role = session()->get('role');
        if (strtolower((string) $role) === 'viewer') {
            session()->setFlashdata('error', 'You do not have permission to edit inventory items.');
            return redirect()->to('inventory');
        }

        $userId = session()->get('user_id');
        $user   = $this->userModel->get_user_by_id($userId);
        $isAdmin = in_array(strtolower((string) $role), ['admin', 'administrator', 'dev'], true);

        $this->itemModel->set_table($isAdmin ? 'central_supply' : 'inventory');
        $item = $this->itemModel->find($id);

        if (empty($item)) {
            session()->setFlashdata('error', 'Item not found in inventory.');
            return redirect()->to('inventory');
        }

        $rules = [
            'item_code'   => 'required|alpha_dash|max_length[50]',
            'name'        => 'required|max_length[150]',
            'category_id' => 'required|integer',
            'supplier_type' => 'required|in_list[supplier,donation,others]',
            'supplier_name' => 'permit_empty|max_length[150]',
            'quantity'    => 'required|integer|greater_than_equal_to[0]',
            'unit'               => 'required',
            'expiration_date'    => 'required|valid_date',
            'manufacturing_date' => 'permit_empty|valid_date',
        ];
        
        if ($this->validate($rules)) {
            $expiration = $this->request->getPost('expiration_date');
            if (!empty($expiration) && $expiration < date('Y-m-d')) {
                session()->setFlashdata('modal_mode', 'edit');
                session()->setFlashdata('modal_edit_id', $id);
                session()->setFlashdata('modal_errors', '<li>Expiration date cannot be in the past.</li>');
                return redirect()->to('inventory')->withInput();
            }

            $db = \Config\Database::connect();

            // Fetch old item for audit diff
            $oldItem = $this->itemModel->find($id);

            $db->transStart();

            $update_data = [
                'item_code'       => strtoupper($this->request->getPost('item_code')),
                'item_name'       => ucwords(strtolower($this->request->getPost('name'))),
                'category_id'     => (int)$this->request->getPost('category_id'),
                'supplier_id'     => $this->resolveSupplierId($this->request->getPost('supplier_type'), $this->request->getPost('supplier_name')),
                'batch_num'       => $this->request->getPost('batch_num') ?: null,
                'lot_num'         => $this->request->getPost('lot_num')   ?: null,
                'expiration_date' => $this->request->getPost('expiration_date') ?: null,
                'manufacturing_date' => $this->request->getPost('manufacturing_date') ?: null,
                'unit'            => $this->request->getPost('unit') ?: null,
                'quantity'        => (int)$this->request->getPost('quantity'),
                'remarks'         => $this->request->getPost('remarks') ?: null,
            ];

            if ($isAdmin) {
                $update_data['quantity_on_hand'] = (int)$this->request->getPost('quantity');
                $this->itemModel->update($id, $update_data);
                // Also update supply unit for admin if needed
                $db->table('supply')->where('central_supply_id', $id)->update(['unit' => $update_data['unit']]);
            } else {
                // For staff: remove supplier_id from inventory update (not a column in inventory table)
                unset($update_data['supplier_id']);
                $this->itemModel->update($id, $update_data);
                // Sync quantity_on_hand in department_supply
                $dsRow = $db->table('department_supply')
                            ->join('supply', 'supply.department_supply_id = department_supply.department_supply_id')
                            ->where('supply.inventory_id', $id)
                            ->where('department_supply.department_id', $user['department_id'])
                            ->get()->getRowArray();
                if ($dsRow) {
                    $newQty = (int)$this->request->getPost('quantity');
                    $oldQty = (int)$dsRow['quantity_on_hand'];
                    $delta = $newQty - $oldQty;
                    if ($delta > 0) {
                        $db->table('department_supply')
                           ->where('department_supply_id', $dsRow['department_supply_id'])
                           ->set('quantity_received', "quantity_received + {$delta}", false)
                           ->set('quantity_on_hand', "quantity_on_hand + {$delta}", false)
                           ->update();
                    } elseif ($delta < 0) {
                        $db->table('department_supply')
                           ->where('department_supply_id', $dsRow['department_supply_id'])
                           ->set('quantity_on_hand', "quantity_on_hand - " . abs($delta), false)
                           ->set('quantity_used', "quantity_used + " . abs($delta), false)
                           ->update();
                    }
                }
                // Update supply unit
                $db->table('supply')->where('inventory_id', $id)->update(['unit' => $update_data['unit']]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                session()->setFlashdata('error', 'An error occurred while updating the item. Please try again.');
                session()->setFlashdata('modal_mode', 'edit');
                session()->setFlashdata('modal_edit_id', $id);
                session()->setFlashdata('modal_errors', '<li>An error occurred while updating the item. Please try again.</li>');
                return redirect()->to('inventory')->withInput();
            } else {
                $changes = [];
                $fields = ['item_name' => 'Name', 'item_code' => 'Item Code', 'quantity' => 'Quantity', 'unit' => 'Unit', 'batch_num' => 'Batch', 'lot_num' => 'Lot', 'expiration_date' => 'Expiration', 'manufacturing_date' => 'Manufacturing', 'remarks' => 'Remarks'];
                foreach ($fields as $key => $label) {
                    $oldVal = $oldItem[$key] ?? '';
                    $newVal = $update_data[$key] ?? '';
                    if ((string)$oldVal !== (string)$newVal) {
                        $changes[] = "{$label}: '{$oldVal}' → '{$newVal}'";
                    }
                }
                $auditDesc = "Updated item: {$update_data['item_name']} (Item Code: {$update_data['item_code']}).";
                if ($changes) {
                    $auditDesc .= ' Changes: ' . implode(', ', $changes);
                }
                $this->auditModel->log_activity(
                    'UPDATE_ITEM',
                    'Inventory',
                    $auditDesc,
                    $id
                );
                session()->setFlashdata('success', 'Item successfully updated!');
                return redirect()->to('inventory');
            }
        } else {
            session()->setFlashdata('modal_mode', 'edit');
            session()->setFlashdata('modal_edit_id', $id);
            session()->setFlashdata('modal_errors', $this->validator->listErrors());
            return redirect()->to('inventory')->withInput();
        }
    }

    /**
     * Generate next inventory code for a category (AJAX endpoint)
     */
    public function generate_inventory_code()
    {
        // Skip auth check for AJAX to avoid redirect issues
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not logged in']);
        }

        $categoryId = $this->request->getPost('category_id');
        
        if (empty($categoryId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Category ID is required']);
        }

        $itemCode = $this->itemModel->generate_inventory_code((int)$categoryId);
        
        if ($itemCode) {
            return $this->response->setJSON(['success' => true, 'inventory_code' => $itemCode]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to generate inventory code']);
        }
    }

    /**
     * Archive an inventory item (set status = 0)
     */
    public function archive($id = NULL)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('inventory');
        }

        $role = session()->get('role');
        if (strtolower((string) $role) === 'viewer') {
            session()->setFlashdata('error', 'You do not have permission to archive inventory items.');
            return redirect()->to('inventory');
        }

        $userId = session()->get('user_id');
        $user = $this->userModel->get_user_by_id($userId);
        $role = session()->get('role');
        $isAdmin = in_array(strtolower((string) $role), ['admin', 'administrator', 'dev'], true);

        $this->itemModel->set_table($isAdmin ? 'central_supply' : 'inventory');
        $item = $this->itemModel->find($id);

        if (empty($item)) {
            session()->setFlashdata('error', 'Item not found.');
            return redirect()->to('inventory');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->itemModel->update($id, ['status' => 0]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'An error occurred while archiving the item.');
        } else {
            $this->auditModel->log_activity(
                'ARCHIVE_ITEM',
                'Inventory',
                "Archived inventory item: {$item['item_name']} (Item Code: {$item['item_code']}).",
                $id
            );

            session()->setFlashdata('success', 'Item successfully archived!');
        }

        return redirect()->to('inventory');
    }

    /**
     * Restore an archived inventory item (set status = 1)
     */
    public function restore($id = NULL)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('inventory');
        }

        $role = session()->get('role');
        if (strtolower((string) $role) === 'viewer') {
            session()->setFlashdata('error', 'You do not have permission to restore inventory items.');
            return redirect()->to('inventory');
        }

        $userId = session()->get('user_id');
        $user = $this->userModel->get_user_by_id($userId);
        $role = session()->get('role');
        $isAdmin = in_array(strtolower((string) $role), ['admin', 'administrator', 'dev'], true);

        $this->itemModel->set_table($isAdmin ? 'central_supply' : 'inventory');
        $item = $this->itemModel->find($id);

        if (empty($item)) {
            session()->setFlashdata('error', 'Item not found.');
            return redirect()->to('inventory');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->itemModel->update($id, ['status' => 1]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'An error occurred while restoring the item.');
        } else {
            $this->auditModel->log_activity(
                'RESTORE_ITEM',
                'Inventory',
                "Restored inventory item: {$item['item_name']} (Item Code: {$item['item_code']}).",
                $id
            );

            session()->setFlashdata('success', 'Item successfully restored!');
        }

        return redirect()->to('inventory');
    }

    /**
     * Permanently delete an inventory item
     */
    public function delete($id = NULL)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('inventory');
        }

        $role = session()->get('role');
        if (strtolower((string) $role) === 'viewer') {
            session()->setFlashdata('error', 'You do not have permission to delete inventory items.');
            return redirect()->to('inventory');
        }

        $userId = session()->get('user_id');
        $user = $this->userModel->get_user_by_id($userId);
        $role = session()->get('role');
        $isAdmin = in_array(strtolower((string) $role), ['admin', 'administrator', 'dev'], true);

        $this->itemModel->set_table($isAdmin ? 'central_supply' : 'inventory');
        $item = $this->itemModel->find($id);

        if (empty($item)) {
            session()->setFlashdata('error', 'Item not found.');
            return redirect()->to('inventory');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        if ($isAdmin) {
            $this->itemModel->delete($id);
        } else {
            // Delete from supply, request, and department supply first
            $supplies = $db->table('supply')
                           ->join('department_supply', 'department_supply.department_supply_id = supply.department_supply_id')
                           ->where('supply.inventory_id', $id)
                           ->where('department_supply.department_id', $user['department_id'])
                           ->get()->getResultArray();
            foreach ($supplies as $s) {
                $db->table('request')->where('department_supply_id', $s['department_supply_id'])->update(['department_supply_id' => null]);
                $db->table('supply')->where('supply_id', $s['supply_id'])->delete();
                $db->table('department_supply')->where('department_supply_id', $s['department_supply_id'])->delete();
            }
            $this->itemModel->delete($id);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'An error occurred while deleting the item.');
        } else {
            $this->auditModel->log_activity(
                'DELETE_ITEM',
                'Inventory',
                "Deleted inventory item: {$item['item_name']} (Item Code: {$item['item_code']}).",
                $id
            );

            session()->setFlashdata('success', 'Item successfully deleted!');
        }

        return redirect()->to('inventory');
    }

    /**
     * Consume inventory items (decrement quantity_on_hand)
     */
    public function consume()
    {
        if ($res = $this->checkAuth()) return $res;

        if (strcasecmp($this->request->getMethod(), 'post') !== 0) {
            return redirect()->to('inventory');
        }

        $role = session()->get('role');
        if (strtolower((string) $role) !== 'encoder') {
            session()->setFlashdata('error', 'You do not have permission to consume inventory.');
            return redirect()->to('inventory');
        }

        $userId = session()->get('user_id');
        $user = $this->userModel->get_user_by_id($userId);

        $itemName = $this->request->getPost('item_name') ?: '';
        $itemCode = $this->request->getPost('item_code') ?: '';
        $quantity = (int)$this->request->getPost('quantity');
        // Accept the old field name for compatibility with already-open forms.
        $remarks = $this->request->getPost('remarks') ?: ($this->request->getPost('reason') ?: '');

        if ($quantity <= 0) {
            session()->setFlashdata('error', 'Quantity must be greater than zero.');
            return redirect()->to('inventory');
        }

        if (empty($itemName) && empty($itemCode)) {
            session()->setFlashdata('error', 'Item not specified.');
            return redirect()->to('inventory');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Find supply records matching this item for the user's department
        $builder = $db->table('supply')
            ->select('supply.supply_id, supply.department_supply_id, supply.central_supply_id')
            ->select('department_supply.quantity_on_hand AS ds_qty')
            ->join('inventory', 'inventory.inventory_id = supply.inventory_id')
            ->join('department_supply', 'department_supply.department_supply_id = supply.department_supply_id')
            ->where('department_supply.department_id', $user['department_id'])
            ->where('department_supply.quantity_on_hand >', 0)
            ->where('inventory.status', 1);

        if (!empty($itemCode)) {
            $builder->where('inventory.item_code', $itemCode);
        } else {
            $builder->where('inventory.item_name', $itemName);
        }

        $matches = $builder->orderBy('inventory.expiration_date', 'ASC')->get()->getResultArray();

        if (empty($matches)) {
            $db->transRollback();
            session()->setFlashdata('error', 'No available stock found for this item.');
            return redirect()->to('inventory');
        }

        $remaining = $quantity;
        $updatedAny = false;
        $totalAvailable = 0;

        foreach ($matches as $m) {
            $totalAvailable += (int)$m['ds_qty'];
        }

        if ($totalAvailable < $quantity) {
            $db->transRollback();
            session()->setFlashdata('error', "Not enough stock. Only {$totalAvailable} unit(s) available.");
            return redirect()->to('inventory');
        }

        foreach ($matches as $m) {
            if ($remaining <= 0) break;

            $dsQty = (int)$m['ds_qty'];
            $take = min($remaining, $dsQty);

            // Decrement department_supply
            $db->table('department_supply')
               ->where('department_supply_id', $m['department_supply_id'])
               ->set('quantity_on_hand', 'quantity_on_hand - ' . $take, false)
               ->set('quantity_used', 'quantity_used + ' . $take, false)
               ->update();

            // Central stock was deducted when this batch was transferred to the
            // department. Consumption must only reduce the department's stock;
            // deducting central_supply here would count the same units twice.

            $remaining -= $take;
            $updatedAny = true;
        }

        $db->transComplete();

        if (!$updatedAny || $db->transStatus() === false) {
            session()->setFlashdata('error', 'An error occurred while consuming inventory.');
        } else {
            $displayName = $itemName ?: $itemCode;
            $auditDesc = "Consumed {$quantity} unit(s) of {$displayName}.";
            if ($remarks) {
                $auditDesc .= " Remarks: {$remarks}";
            }
            $this->auditModel->log_activity(
                'CONSUME_ITEM',
                'Inventory',
                $auditDesc
            );
            session()->setFlashdata('success', "Successfully consumed {$quantity} unit(s).");
        }

        return redirect()->to('inventory');
    }
}
