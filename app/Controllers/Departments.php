<?php

namespace App\Controllers;

use App\Models\DepartmentModel;
use App\Models\AuditModel;

class Departments extends BaseController
{
    protected $departmentModel;
    protected $auditModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->departmentModel = new DepartmentModel();
        $this->auditModel = new AuditModel();
    }

    protected function checkAuth()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        // Verify the user actually exists to handle reseeded/stale sessions gracefully
        $userModel = new \App\Models\UserModel();
        $user = $userModel->get_user_by_id(session()->get('user_id'));
        if (empty($user)) {
            session()->destroy();
            return redirect()->to('auth/login');
        }

        return null;
    }

    /**
     * Display all departments listing (with embedded create/edit/delete modals)
     */
    public function index()
    {
        if ($res = $this->checkAuth()) return $res;

        $data['title'] = 'Departments';
        $data['departments'] = $this->departmentModel->get_departments();

        return view('templates/header', $data)
             . view('departments', $data)
             . view('templates/footer');
    }

    /**
     * Create a new hospital department (modal-based, POST only)
     */
    public function create()
    {
        if ($res = $this->checkAuth()) return $res;

        // GET requests: redirect to departments index (create is a modal on index)
        if (strcasecmp($this->request->getMethod(), 'get') === 0) {
            return redirect()->to('departments');
        }

        $rules = [
            'code' => 'required|max_length[50]|is_unique[departments.department_code]',
            'name' => 'required|max_length[100]|is_unique[departments.department_name]',
        ];

        if ($this->validate($rules)) {
            $code = $this->request->getPost('code');
            $name = $this->request->getPost('name');
            $insert_data = ['code' => $code, 'name' => $name];

            if ($this->departmentModel->insert_department($insert_data)) {
                $this->auditModel->log_activity(
                    'CREATE_DEPT',
                    'Departments',
                    "Created new hospital department: {$name} ({$code})."
                );

                session()->setFlashdata('success', 'Department successfully created!');
                return redirect()->to('departments');
            } else {
                session()->setFlashdata('modal_mode', 'create');
                session()->setFlashdata('modal_errors', '<li>An error occurred while creating the department. Please try again.</li>');
                return redirect()->to('departments')->withInput();
            }
        } else {
            // Validation failed — re-open modal with errors
            session()->setFlashdata('modal_mode', 'create');
            session()->setFlashdata('modal_errors', $this->validator->listErrors());
            return redirect()->to('departments')->withInput();
        }
    }

    /**
     * Edit/Update an existing department (modal-based)
     */
    public function edit($id = NULL)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('departments');
        }

        $dept = $this->departmentModel->find($id);
        if (empty($dept)) {
            session()->setFlashdata('error', 'Department not found.');
            return redirect()->to('departments');
        }

        // GET requests: redirect to departments and open this dept's edit modal
        if (strcasecmp($this->request->getMethod(), 'get') === 0) {
            session()->setFlashdata('modal_mode', 'edit');
            session()->setFlashdata('modal_edit_id', $id);
            return redirect()->to('departments');
        }

        $rules = [
            'code' => "required|max_length[50]|is_unique[departments.department_code,department_id,{$id}]",
            'name' => "required|max_length[100]|is_unique[departments.department_name,department_id,{$id}]",
        ];

        if ($this->validate($rules)) {
            $code     = $this->request->getPost('code');
            $name     = $this->request->getPost('name');
            $old_name = $dept['name'];
            $old_code = $dept['code'];
            $update_data = ['code' => $code, 'name' => $name];

            if ($this->departmentModel->update_department($id, $update_data)) {
                $audit_desc = "Updated hospital department: '{$old_name}' ({$old_code})";
                if ($old_name !== $name || $old_code !== $code) {
                    $audit_desc .= " → '{$name}' ({$code}).";
                } else {
                    $audit_desc .= ". No values were changed.";
                }

                $this->auditModel->log_activity('UPDATE_DEPT', 'Departments', $audit_desc);

                session()->setFlashdata('success', 'Department successfully updated!');
                return redirect()->to('departments');
            } else {
                session()->setFlashdata('modal_mode', 'edit');
                session()->setFlashdata('modal_edit_id', $id);
                session()->setFlashdata('modal_errors', '<li>An error occurred while updating the department. Please try again.</li>');
                return redirect()->to('departments')->withInput();
            }
        } else {
            // Validation failed — redirect back & open the correct edit modal with errors
            session()->setFlashdata('modal_mode', 'edit');
            session()->setFlashdata('modal_edit_id', $id);
            session()->setFlashdata('modal_errors', $this->validator->listErrors());
            return redirect()->to('departments')->withInput();
        }
    }

    /**
     * Delete department record
     */
    public function archive($id = NULL)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('departments');
        }

        $dept = $this->departmentModel->find($id);
        if (empty($dept)) {
            session()->setFlashdata('error', 'Department not found.');
            return redirect()->to('departments');
        }

        if ($this->departmentModel->update($id, ['status' => 0])) {
            $this->auditModel->log_activity(
                'ARCHIVE_DEPT',
                'Departments',
                "Archived hospital department: {$dept['name']}."
            );
            session()->setFlashdata('success', 'Department successfully archived.');
        } else {
            session()->setFlashdata('error', 'An error occurred while archiving the department.');
        }

        return redirect()->to('departments');
    }

    public function restore($id = NULL)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('departments');
        }

        $dept = $this->departmentModel->find($id);
        if (empty($dept)) {
            session()->setFlashdata('error', 'Department not found.');
            return redirect()->to('departments');
        }

        if ($this->departmentModel->update($id, ['status' => 1])) {
            $this->auditModel->log_activity(
                'RESTORE_DEPT',
                'Departments',
                "Restored hospital department: {$dept['name']}."
            );
            session()->setFlashdata('success', 'Department successfully restored.');
        } else {
            session()->setFlashdata('error', 'An error occurred while restoring the department.');
        }

        return redirect()->to('departments');
    }

    /**
     * Delete department record (permanent)
     */
    public function delete($id = NULL)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('departments');
        }

        $dept = $this->departmentModel->find($id);
        if (empty($dept)) {
            session()->setFlashdata('error', 'Department not found.');
            return redirect()->to('departments');
        }

        if ($this->departmentModel->delete_department($id)) {
            $this->auditModel->log_activity(
                'DELETE_DEPT',
                'Departments',
                "Deleted hospital department: {$dept['name']}."
            );

            session()->setFlashdata('success', 'Department successfully deleted!');
        } else {
            session()->setFlashdata('error', 'An error occurred while trying to delete the department.');
        }

        return redirect()->to('departments');
    }
}
