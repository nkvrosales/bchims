<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\AuditModel;

class Inventory extends BaseController
{
    protected $itemModel;
    protected $auditModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->itemModel = new ItemModel();
        $this->auditModel = new AuditModel();
    }

    protected function checkAuth()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }
        return null;
    }

    /**
     * Display inventory search and filtering listing dashboard
     */
    public function index()
    {
        if ($res = $this->checkAuth()) return $res;

        $data['title'] = 'Inventory';

        $search = $this->request->getGet('search');
        $department = $this->request->getGet('department');
        $stock_status = $this->request->getGet('stock_status');

        $data['search'] = $search;
        $data['department'] = $department;
        $data['stock_status'] = $stock_status;

        $data['items'] = $this->itemModel->get_items($search, $department, $stock_status);

        return view('templates/header', $data)
             . view('inventory/index', $data)
             . view('templates/footer');
    }

    /**
     * Create a new inventory item
     */
    public function create()
    {
        if ($res = $this->checkAuth()) return $res;

        $data['title'] = 'Add Item';

        $rules = [
            'item_code'   => 'required|is_unique[items.item_code]|alpha_dash|max_length[50]',
            'name'        => 'required|max_length[100]',
            'department'  => 'required|in_list[LAB,PHARMA,SUPPLIES,OR/DR COMPLEX]',
            'quantity'    => 'required|integer|greater_than_equal_to[0]',
            'unit'        => 'required|max_length[50]',
            'min_stock'   => 'required|integer|greater_than_equal_to[0]',
            'description' => 'max_length[500]',
        ];

        if (strcasecmp($this->request->getMethod(), 'post') === 0 && $this->validate($rules)) {
            $insert_data = array(
                'item_code'   => strtoupper($this->request->getPost('item_code')),
                'name'        => $this->request->getPost('name'),
                'department'  => $this->request->getPost('department'),
                'quantity'    => (int)$this->request->getPost('quantity'),
                'unit'        => $this->request->getPost('unit'),
                'min_stock'   => (int)$this->request->getPost('min_stock'),
                'description' => $this->request->getPost('description')
            );

            if ($this->itemModel->insert($insert_data)) {
                $this->auditModel->log_activity(
                    'ADD_ITEM',
                    'Inventory',
                    "Added new item: {$insert_data['name']} (Code: {$insert_data['item_code']}) with initial quantity of {$insert_data['quantity']} {$insert_data['unit']} in department {$insert_data['department']}."
                );

                session()->setFlashdata('success', 'Item successfully added to inventory!');
                return redirect()->to('inventory');
            } else {
                $data['error'] = 'An error occurred while trying to save the item. Please try again.';
            }
        }

        return view('templates/header', $data)
             . view('inventory/create', $data)
             . view('templates/footer');
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

        $item = $this->itemModel->find($id);
        if (empty($item)) {
            session()->setFlashdata('error', 'Item not found in inventory.');
            return redirect()->to('inventory');
        }

        $data['title'] = 'Edit Item';
        $data['item'] = $item;

        $rules = [
            'item_code'   => "required|alpha_dash|max_length[50]|is_unique[items.item_code,id,{$id}]",
            'name'        => 'required|max_length[100]',
            'department'  => 'required|in_list[LAB,PHARMA,SUPPLIES,OR/DR COMPLEX]',
            'quantity'    => 'required|integer|greater_than_equal_to[0]',
            'unit'        => 'required|max_length[50]',
            'min_stock'   => 'required|integer|greater_than_equal_to[0]',
            'description' => 'max_length[500]',
        ];

        if (strcasecmp($this->request->getMethod(), 'post') === 0 && $this->validate($rules)) {
            $update_data = array(
                'item_code'   => strtoupper($this->request->getPost('item_code')),
                'name'        => $this->request->getPost('name'),
                'department'  => $this->request->getPost('department'),
                'quantity'    => (int)$this->request->getPost('quantity'),
                'unit'        => $this->request->getPost('unit'),
                'min_stock'   => (int)$this->request->getPost('min_stock'),
                'description' => $this->request->getPost('description')
            );

            if ($this->itemModel->update($id, $update_data)) {
                $changes = array();
                if ($item['name'] !== $update_data['name']) {
                    $changes[] = "Name ('{$item['name']}' -> '{$update_data['name']}')";
                }
                if ($item['item_code'] !== $update_data['item_code']) {
                    $changes[] = "Code ('{$item['item_code']}' -> '{$update_data['item_code']}')";
                }
                if ($item['department'] !== $update_data['department']) {
                    $changes[] = "Department ('{$item['department']}' -> '{$update_data['department']}')";
                }
                if ((int)$item['quantity'] !== (int)$update_data['quantity']) {
                    $changes[] = "Quantity ({$item['quantity']} -> {$update_data['quantity']} {$update_data['unit']})";
                }
                if ((int)$item['min_stock'] !== (int)$update_data['min_stock']) {
                    $changes[] = "Min Stock ({$item['min_stock']} -> {$update_data['min_stock']})";
                }

                $audit_desc = "Updated item: {$update_data['name']} (Code: {$update_data['item_code']}).";
                if (!empty($changes)) {
                    $audit_desc .= " Changes: " . implode(', ', $changes);
                } else {
                    $audit_desc .= " No values were changed.";
                }

                $this->auditModel->log_activity('UPDATE_ITEM', 'Inventory', $audit_desc);

                session()->setFlashdata('success', 'Item successfully updated!');
                return redirect()->to('inventory');
            } else {
                $data['error'] = 'An error occurred while updating the item. Please try again.';
            }
        }

        return view('templates/header', $data)
             . view('inventory/edit', $data)
             . view('templates/footer');
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

        $item = $this->itemModel->find($id);
        if (empty($item)) {
            session()->setFlashdata('error', 'Item not found in inventory.');
            return redirect()->to('inventory');
        }

        if ($this->itemModel->delete($id)) {
            $this->auditModel->log_activity(
                'DELETE_ITEM',
                'Inventory',
                "Deleted inventory item: {$item['name']} (Code: {$item['item_code']}) which had quantity {$item['quantity']} {$item['unit']} in department {$item['department']}."
            );

            session()->setFlashdata('success', 'Item successfully deleted!');
        } else {
            session()->setFlashdata('error', 'An error occurred while trying to delete the item.');
        }

        return redirect()->to('inventory');
    }
}
