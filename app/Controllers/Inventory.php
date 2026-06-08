<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\AuditModel;
use App\Models\UserModel;

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

    protected function resolveSourceId($sourceValue)
    {
        $db = \Config\Database::connect();

        switch ($sourceValue) {
            case 'donation':
                $sourceType = 'Donation';
                break;
            case 'old_stock':
                $sourceType = 'Old Stock';
                break;
            default:
                $sourceType = 'Supplier';
        }

        $existing = $db->table('source')
            ->where('source_type', $sourceType)
            ->get()
            ->getRowArray();

        if (!empty($existing)) {
            return (int)$existing['source_id'];
        }

        $db->table('source')->insert([
            'source_type'   => $sourceType,
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

        $data['title'] = 'Inventory';

        $search = $this->request->getGet('search');
        $stock_status = $this->request->getGet('stock_status');

        $data['search'] = $search;
        $data['department_context'] = $isAdmin ? 'Central Supply' : ($user['department_name'] ?? 'Central Supplies');
        $data['department_name'] = $isAdmin ? 'Central Supply' : ($user['department_name'] ?? 'Local');
        $data['stock_status'] = $stock_status;

        $data['items'] = $this->itemModel->get_items($search, $isAdmin ? 'admin' : 'staff', $deptId, $stock_status);
        $data['categories'] = \Config\Database::connect()
            ->table('category')
            ->orderBy('category_code', 'ASC')
            ->get()
            ->getResultArray();

        return view('templates/header', $data)
             . view('inventory/index', $data)
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
            'item_code'   => 'required|alpha_dash|max_length[50]',
            'name'        => 'required|max_length[150]',
            'category_id' => 'required|integer',
            'source_type' => 'required|in_list[supplier,donation,old_stock]',
            'quantity'    => 'required|integer|greater_than_equal_to[0]',
        ];

        if ($this->validate($rules)) {
            $db = \Config\Database::connect();
            $db->transStart();

            $itemCode   = strtoupper($this->request->getPost('item_code'));
            $itemName   = $this->request->getPost('name');
            $quantity   = (int)$this->request->getPost('quantity');
            $categoryId = (int)$this->request->getPost('category_id');
            $sourceId   = $this->resolveSourceId($this->request->getPost('source_type'));
            $batchNum   = $this->request->getPost('batch_num') ?: null;
            $lotNum     = $this->request->getPost('lot_num')   ?: null;
            $expiration = $this->request->getPost('expiration_date') ?: null;
            $unit       = $this->request->getPost('unit') ?: null;

            if ($isAdmin) {
                // Insert into central_supply
                $this->itemModel->set_table('central_supply');
                $insert_data = [
                    'item_code'        => $itemCode,
                    'item_name'        => $itemName,
                    'batch_num'        => $batchNum,
                    'lot_num'          => $lotNum,
                    'expiration_date'  => $expiration,
                    'unit'             => $unit,
                    'quantity'         => $quantity,
                    'quantity_on_hand' => $quantity,
                    'category_id'      => $categoryId,
                    'source_id'        => $sourceId,
                ];
                $this->itemModel->insert($insert_data);
                $itemId = $db->insertID();
            } else {
                // Insert into inventory (department stock)
                $this->itemModel->set_table('inventory');
                $insert_data = [
                    'item_code'       => $itemCode,
                    'item_name'       => $itemName,
                    'batch_num'       => $batchNum,
                    'lot_num'         => $lotNum,
                    'expiration_date' => $expiration,
                    'unit'            => $unit,
                    'quantity'        => $quantity,
                    'category_id'     => $categoryId,
                ];
                $this->itemModel->insert($insert_data);
                $itemId = $db->insertID();

                // Get or create central_supply entry for the item_code
                $csItem = $db->table('central_supply')->where('item_code', $itemCode)->get()->getRowArray();
                if (!$csItem) {
                    $db->table('central_supply')->insert([
                        'item_code'        => $itemCode,
                        'item_name'        => $itemName,
                        'batch_num'        => $batchNum,
                        'lot_num'          => $lotNum,
                        'expiration_date'  => $expiration,
                        'unit'             => $unit,
                        'quantity'         => 0,
                        'quantity_on_hand' => 0,
                        'category_id'      => $categoryId,
                        'source_id'        => $sourceId, // source tracked in central_supply only
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
                    'status'               => 'Manually Added',
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
                $this->auditModel->log_activity(
                    'ADD_ITEM',
                    'Inventory',
                    "Added new item: {$itemName} (Code: {$itemCode}) with initial quantity of {$quantity}.",
                    $itemId
                );
                session()->setFlashdata('success', 'Item successfully added to inventory!');
            }
        } else {
            session()->setFlashdata('create_item_modal_open', true);
            session()->setFlashdata('create_item_validation_errors', $this->validator->listErrors());
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
            'source_type' => 'required|in_list[supplier,donation,old_stock]',
            'quantity'    => 'required|integer|greater_than_equal_to[0]',
        ];

        if ($this->validate($rules)) {
            $db = \Config\Database::connect();
            $db->transStart();

            $update_data = [
                'item_code'       => strtoupper($this->request->getPost('item_code')),
                'item_name'       => $this->request->getPost('name'),
                'category_id'     => (int)$this->request->getPost('category_id'),
                'source_id'       => $this->resolveSourceId($this->request->getPost('source_type')),
                'batch_num'       => $this->request->getPost('batch_num') ?: null,
                'lot_num'         => $this->request->getPost('lot_num')   ?: null,
                'expiration_date' => $this->request->getPost('expiration_date') ?: null,
                'unit'            => $this->request->getPost('unit') ?: null,
                'quantity'        => (int)$this->request->getPost('quantity'),
            ];

            if ($isAdmin) {
                $update_data['quantity_on_hand'] = (int)$this->request->getPost('quantity');
                $this->itemModel->update($id, $update_data);
                // Also update supply unit for admin if needed
                $db->table('supply')->where('central_supply_id', $id)->update(['unit' => $update_data['unit']]);
            } else {
                // For staff: remove source_id from inventory update (not a column in inventory table)
                unset($update_data['source_id']);
                $this->itemModel->update($id, $update_data);
                // Sync quantity_on_hand in department_supply
                $dsRow = $db->table('department_supply')
                            ->join('supply', 'supply.department_supply_id = department_supply.department_supply_id')
                            ->where('supply.inventory_id', $id)
                            ->where('department_supply.department_id', $user['department_id'])
                            ->get()->getRowArray();
                if ($dsRow) {
                    $db->table('department_supply')
                       ->where('department_supply_id', $dsRow['department_supply_id'])
                       ->update(['quantity_on_hand' => (int)$this->request->getPost('quantity')]);
                }
                // Update supply unit
                $db->table('supply')->where('inventory_id', $id)->update(['unit' => $update_data['unit']]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                session()->setFlashdata('error', 'An error occurred while updating the item. Please try again.');
                session()->setFlashdata('edit_item_modal_open', $id);
                return redirect()->to('inventory')->withInput();
            } else {
                $this->auditModel->log_activity(
                    'UPDATE_ITEM',
                    'Inventory',
                    "Updated item: {$update_data['item_name']} (Code: {$update_data['item_code']}).",
                    $id
                );
                session()->setFlashdata('success', 'Item successfully updated!');
                return redirect()->to('inventory');
            }
        } else {
            session()->setFlashdata('edit_item_modal_open', $id);
            session()->setFlashdata('edit_item_validation_errors', $this->validator->listErrors());
            return redirect()->to('inventory')->withInput();
        }
    }

    /**
     * Delete an inventory item
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
                "Deleted inventory item: {$item['item_name']} (Code: {$item['item_code']}).",
                $id
            );

            session()->setFlashdata('success', 'Item successfully deleted!');
        }

        return redirect()->to('inventory');
    }
}
