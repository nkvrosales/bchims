<?php

namespace App\Controllers;

use App\Models\AuditModel;
use App\Models\UnitModel;
use App\Models\UserModel;

class Units extends BaseController
{
    protected $unitModel;
    protected $auditModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->unitModel = new UnitModel();
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
            session()->setFlashdata('session_expired', 'Your session has expired due to inactivity. Please log in again.');
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

        $search        = trim((string) $this->request->getGet('search'));
        $status_filter = trim((string) $this->request->getGet('status_filter'));

        $data['title']         = 'Unit';
        $data['units']         = $this->unitModel->search_units($search, $status_filter);
        $data['search']        = $search;
        $data['status_filter'] = $status_filter;

        return view('templates/header', $data)
             . view('unit', $data)
             . view('templates/footer');
    }

    public function create()
    {
        if ($res = $this->checkAuth()) return $res;

        if (strcasecmp($this->request->getMethod(), 'get') === 0) {
            return redirect()->to('unit');
        }

        $rules = [
            'unit_name' => 'required|max_length[100]|is_unique[unit.unit_name]',
            'unit_code' => 'required|alpha_dash|max_length[50]|is_unique[unit.unit_code]',
        ];

        if ($this->validate($rules)) {
            $insertData = [
                'unit_name' => ucwords(strtolower($this->request->getPost('unit_name'))),
                'unit_code' => strtolower($this->request->getPost('unit_code')),
            ];

            if ($this->unitModel->insert($insertData)) {
                $this->auditModel->log_activity(
                    'CREATE_UNIT',
                    'Unit',
                    "Created unit: {$insertData['unit_name']} ({$insertData['unit_code']})."
                );
                session()->setFlashdata('success', 'Unit successfully created!');
                return redirect()->to('unit');
            }

            session()->setFlashdata('modal_mode', 'create');
            session()->setFlashdata('modal_errors', '<li>An error occurred while creating the unit.</li>');
            return redirect()->to('unit')->withInput();
        }

        session()->setFlashdata('modal_mode', 'create');
        session()->setFlashdata('modal_errors', $this->validator->listErrors());
        return redirect()->to('unit')->withInput();
    }

    public function edit($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('unit');
        }

        $unit = $this->unitModel->find($id);
        if (empty($unit)) {
            session()->setFlashdata('error', 'Unit not found.');
            return redirect()->to('unit');
        }

        if (strcasecmp($this->request->getMethod(), 'get') === 0) {
            session()->setFlashdata('modal_mode', 'edit');
            session()->setFlashdata('modal_edit_id', $id);
            return redirect()->to('unit');
        }

        $rules = [
            'unit_name' => "required|max_length[100]|is_unique[unit.unit_name,unit_id,{$id}]",
            'unit_code' => "required|alpha_dash|max_length[50]|is_unique[unit.unit_code,unit_id,{$id}]",
        ];

        if ($this->validate($rules)) {
            $oldUnit = $this->unitModel->find($id);
            $updateData = [
                'unit_name' => ucwords(strtolower($this->request->getPost('unit_name'))),
                'unit_code' => strtolower($this->request->getPost('unit_code')),
            ];

            if ($this->unitModel->update($id, $updateData)) {
                $changes = [];
                if (($oldUnit['unit_name'] ?? '') !== $updateData['unit_name']) {
                    $changes[] = "Name: '{$oldUnit['unit_name']}' to '{$updateData['unit_name']}'";
                }
                if (($oldUnit['unit_code'] ?? '') !== $updateData['unit_code']) {
                    $changes[] = "Code: '{$oldUnit['unit_code']}' to '{$updateData['unit_code']}'";
                }

                $auditDesc = "Updated unit: {$updateData['unit_name']} ({$updateData['unit_code']}).";
                if ($changes) {
                    $auditDesc .= ' Changes: ' . implode(', ', $changes);
                }

                $this->auditModel->log_activity('UPDATE_UNIT', 'Unit', $auditDesc, $id);
                session()->setFlashdata('success', 'Unit successfully updated!');
                return redirect()->to('unit');
            }

            session()->setFlashdata('modal_mode', 'edit');
            session()->setFlashdata('modal_edit_id', $id);
            session()->setFlashdata('modal_errors', '<li>An error occurred while updating the unit.</li>');
            return redirect()->to('unit')->withInput();
        }

        session()->setFlashdata('modal_mode', 'edit');
        session()->setFlashdata('modal_edit_id', $id);
        session()->setFlashdata('modal_errors', $this->validator->listErrors());
        return redirect()->to('unit')->withInput();
    }

    public function archive($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('unit');
        }

        $unit = $this->unitModel->find($id);
        if (empty($unit)) {
            session()->setFlashdata('error', 'Unit not found.');
            return redirect()->to('unit');
        }

        if ($this->unitModel->update($id, ['status' => 0])) {
            $this->auditModel->log_activity('DEACTIVATE_UNIT', 'Unit', "Deactivated unit: {$unit['unit_name']}.", $id);
            session()->setFlashdata('success', 'Unit successfully deactivated.');
        } else {
            session()->setFlashdata('error', 'An error occurred while deactivating the unit.');
        }

        return redirect()->to('unit');
    }

    public function restore($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('unit');
        }

        $unit = $this->unitModel->find($id);
        if (empty($unit)) {
            session()->setFlashdata('error', 'Unit not found.');
            return redirect()->to('unit');
        }

        if ($this->unitModel->update($id, ['status' => 1])) {
            $this->auditModel->log_activity('REACTIVATE_UNIT', 'Unit', "Reactivated unit: {$unit['unit_name']}.", $id);
            session()->setFlashdata('success', 'Unit successfully reactivated.');
        } else {
            session()->setFlashdata('error', 'An error occurred while reactivating the unit.');
        }

        return redirect()->to('unit');
    }
}
