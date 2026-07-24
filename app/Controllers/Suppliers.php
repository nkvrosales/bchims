<?php

namespace App\Controllers;

use App\Models\AuditModel;
use App\Models\SupplierModel;
use App\Models\UserModel;

class Suppliers extends BaseController
{
    protected $supplierModel;
    protected $auditModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->supplierModel = new SupplierModel();
        $this->auditModel = new AuditModel();
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
            if ($lastUser) session()->setFlashdata('session_expired', 'Your session has expired due to inactivity. Please log in again.');
            return redirect()->to('auth/login');
        }

        $userModel = new UserModel();
        $user = $userModel->get_user_by_id(session()->get('user_id'));
        if (empty($user)) {
            session()->destroy();
            return redirect()->to('auth/login');
        }

        if (!is_admin_role()) {
            return redirect()->to('dashboard');
        }

        return null;
    }

    public function index()
    {
        if ($res = $this->checkAuth()) return $res;

        $search      = trim((string) $this->request->getGet('search'));
        $type_filter = trim((string) $this->request->getGet('type_filter'));
        $status_filter = trim((string) $this->request->getGet('status_filter'));

        $suppliers = $this->supplierModel->search_suppliers($search, $type_filter, $status_filter);

        $data['title']       = 'Suppliers';
        $data['suppliers']   = $suppliers;
        $data['search']      = $search;
        $data['type_filter'] = $type_filter;
        $data['status_filter'] = $status_filter;

        return view('templates/header', $data)
             . view('suppliers', $data)
             . view('templates/footer');
    }

    public function create()
    {
        if ($res = $this->checkAuth()) return $res;

        if (strcasecmp($this->request->getMethod(), 'get') === 0) {
            return redirect()->to('suppliers');
        }

        $rules = [
            'supplier_type'  => 'required|in_list[Supplier,Donation,Others]',
            'supplier_name'  => 'required|max_length[150]|is_unique[supplier.supplier_name]',
            'contact_person' => 'permit_empty|max_length[150]',
            'contact_number' => 'permit_empty|max_length[50]',
            'email'          => 'permit_empty|max_length[150]|valid_email',
            'address'        => 'permit_empty',
        ];

        $customErrors = [
            'supplier_name' => ['is_unique' => 'Supplier already exists. Enter a different name.'],
        ];

        if ($this->validate($rules, $customErrors)) {
            $insertData = [
                'supplier_type'  => $this->request->getPost('supplier_type'),
                'supplier_name'  => ucwords(strtolower($this->request->getPost('supplier_name'))),
                'contact_person' => $this->request->getPost('contact_person') ?: null,
                'contact_number' => $this->request->getPost('contact_number') ?: null,
                'email'          => $this->request->getPost('email') ?: null,
                'address'        => $this->request->getPost('address') ?: null,
            ];

            if ($this->supplierModel->insert($insertData)) {
                $this->auditModel->log_activity(
                    'CREATE_SUPPLIER',
                    'Suppliers',
                    "Created supplier: {$insertData['supplier_name']} ({$insertData['supplier_type']})."
                );

                session()->setFlashdata('success', 'Supplier successfully created.');
                return redirect()->to('suppliers');
            }

            session()->setFlashdata('modal_mode', 'create');
            session()->setFlashdata('modal_errors', '<li>An error occurred while creating the supplier.</li>');
            return redirect()->to('suppliers')->withInput();
        }

        session()->setFlashdata('modal_mode', 'create');
        session()->setFlashdata('modal_errors', $this->validator->listErrors());
        return redirect()->to('suppliers')->withInput();
    }

    public function edit($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('suppliers');
        }

        $supplier = $this->supplierModel->find($id);
        if (empty($supplier)) {
            session()->setFlashdata('error', 'Supplier not found.');
            return redirect()->to('suppliers');
        }

        if (strcasecmp($this->request->getMethod(), 'get') === 0) {
            session()->setFlashdata('modal_mode', 'edit');
            session()->setFlashdata('modal_edit_id', $id);
            return redirect()->to('suppliers');
        }

        $rules = [
            'supplier_type'  => 'required|in_list[Supplier,Donation,Others]',
            'supplier_name'  => "required|max_length[150]|is_unique[supplier.supplier_name,supplier_id,{$id}]",
            'contact_person' => 'permit_empty|max_length[150]',
            'contact_number' => 'permit_empty|max_length[50]',
            'email'          => 'permit_empty|max_length[150]|valid_email',
            'address'        => 'permit_empty',
        ];

        $customErrors = [
            'supplier_name' => ['is_unique' => 'Supplier already exists. Enter a different name.'],
        ];

        if ($this->validate($rules, $customErrors)) {
            $oldSupplier = $this->supplierModel->find($id);

            $updateData = [
                'supplier_type'  => $this->request->getPost('supplier_type'),
                'supplier_name'  => ucwords(strtolower($this->request->getPost('supplier_name'))),
                'contact_person' => $this->request->getPost('contact_person') ?: null,
                'contact_number' => $this->request->getPost('contact_number') ?: null,
                'email'          => $this->request->getPost('email') ?: null,
                'address'        => $this->request->getPost('address') ?: null,
            ];

            if ($this->supplierModel->update($id, $updateData)) {
                $changes = [];
                $supplierFields = ['supplier_name' => 'Name', 'supplier_type' => 'Type', 'contact_person' => 'Contact Person', 'contact_number' => 'Contact Number', 'email' => 'Email', 'address' => 'Address'];
                foreach ($supplierFields as $key => $label) {
                    $oldVal = $oldSupplier[$key] ?? '';
                    $newVal = $updateData[$key] ?? '';
                    if ((string)$oldVal !== (string)$newVal) {
                        $changes[] = "{$label}: '{$oldVal}' → '{$newVal}'";
                    }
                }
                $auditDesc = "Updated supplier: {$updateData['supplier_name']} ({$updateData['supplier_type']}).";
                if ($changes) {
                    $auditDesc .= ' Changes: ' . implode(', ', $changes);
                }
                $this->auditModel->log_activity(
                    'UPDATE_SUPPLIER',
                    'Suppliers',
                    $auditDesc,
                    $id
                );

                session()->setFlashdata('success', 'Supplier successfully updated.');
                return redirect()->to('suppliers');
            }

            session()->setFlashdata('modal_mode', 'edit');
            session()->setFlashdata('modal_edit_id', $id);
            session()->setFlashdata('modal_errors', '<li>An error occurred while updating the supplier.</li>');
            return redirect()->to('suppliers')->withInput();
        }

        session()->setFlashdata('modal_mode', 'edit');
        session()->setFlashdata('modal_edit_id', $id);
        session()->setFlashdata('modal_errors', $this->validator->listErrors());
        return redirect()->to('suppliers')->withInput();
    }

    public function archive($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('suppliers');
        }

        $supplier = $this->supplierModel->find($id);
        if (empty($supplier)) {
            session()->setFlashdata('error', 'Supplier not found.');
            return redirect()->to('suppliers');
        }

        if ($this->supplierModel->update($id, ['status' => 0])) {
            $this->auditModel->log_activity(
                'DEACTIVATE_SUPPLIER',
                'Suppliers',
                "Deactivated supplier: {$supplier['supplier_name']}.",
                $id
            );
            session()->setFlashdata('success', 'Supplier successfully deactivated.');
        } else {
            session()->setFlashdata('error', 'An error occurred while deactivating the supplier.');
        }

        return redirect()->to('suppliers');
    }

    public function restore($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('suppliers');
        }

        $supplier = $this->supplierModel->find($id);
        if (empty($supplier)) {
            session()->setFlashdata('error', 'Supplier not found.');
            return redirect()->to('suppliers');
        }

        if ($this->supplierModel->update($id, ['status' => 1])) {
            $this->auditModel->log_activity(
                'REACTIVATE_SUPPLIER',
                'Suppliers',
                "Reactivated supplier: {$supplier['supplier_name']}.",
                $id
            );
            session()->setFlashdata('success', 'Supplier successfully reactivated.');
        } else {
            session()->setFlashdata('error', 'An error occurred while reactivating the supplier.');
        }

        return redirect()->to('suppliers');
    }

    public function delete($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('suppliers');
        }

        $supplier = $this->supplierModel->find($id);
        if (empty($supplier)) {
            session()->setFlashdata('error', 'Supplier not found.');
            return redirect()->to('suppliers');
        }

        $db = \Config\Database::connect();
        $inUse = $db->table('central_supply')->where('supplier_id', $id)->countAllResults();

        if ($inUse > 0) {
            session()->setFlashdata('error', 'Cannot delete a supplier that is currently used by inventory stock.');
            return redirect()->to('suppliers');
        }

        if ($this->supplierModel->delete($id)) {
            $this->auditModel->log_activity(
                'DELETE_SUPPLIER',
                'Suppliers',
                "Deleted supplier: {$supplier['supplier_name']}.",
                $id
            );
            session()->setFlashdata('success', 'Supplier successfully deleted.');
        } else {
            session()->setFlashdata('error', 'An error occurred while deleting the supplier.');
        }

        return redirect()->to('suppliers');
    }
}
