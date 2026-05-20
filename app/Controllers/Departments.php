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
        return null;
    }

    /**
     * Display all departments listing
     */
    public function index()
    {
        if ($res = $this->checkAuth()) return $res;

        $data['title'] = 'Departments';
        $data['departments'] = $this->departmentModel->get_departments();

        return view('templates/header', $data)
             . view('departments/index', $data)
             . view('templates/footer');
    }

    /**
     * Create a new hospital department
     */
    public function create()
    {
        if ($res = $this->checkAuth()) return $res;

        $data['title'] = 'Add Department';

        $rules = [
            'code'        => 'required|alpha_dash|max_length[50]|is_unique[departments.code]',
            'name'        => 'required|max_length[100]|is_unique[departments.name]',
            'description' => 'max_length[500]',
        ];

        if (strcasecmp($this->request->getMethod(), 'post') === 0 && $this->validate($rules)) {
            $insert_data = array(
                'code'        => strtoupper($this->request->getPost('code')),
                'name'        => $this->request->getPost('name'),
                'description' => $this->request->getPost('description')
            );

            if ($this->departmentModel->insert($insert_data)) {
                $this->auditModel->log_activity(
                    'CREATE_DEPT',
                    'Departments',
                    "Created new hospital department: {$insert_data['name']} (Code: {$insert_data['code']})."
                );

                session()->setFlashdata('success', 'Department successfully created!');
                return redirect()->to('departments');
            } else {
                $data['error'] = 'An error occurred while creating the department. Please try again.';
            }
        }

        return view('templates/header', $data)
             . view('departments/create', $data)
             . view('templates/footer');
    }

    /**
     * Edit/Update an existing department details
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

        $data['title'] = 'Edit Department';
        $data['dept'] = $dept;

        $rules = [
            'code'        => "required|alpha_dash|max_length[50]|is_unique[departments.code,id,{$id}]",
            'name'        => "required|max_length[100]|is_unique[departments.name,id,{$id}]",
            'description' => 'max_length[500]',
        ];

        if (strcasecmp($this->request->getMethod(), 'post') === 0 && $this->validate($rules)) {
            $update_data = array(
                'code'        => strtoupper($this->request->getPost('code')),
                'name'        => $this->request->getPost('name'),
                'description' => $this->request->getPost('description')
            );

            if ($this->departmentModel->update($id, $update_data)) {
                $changes = array();
                if ($dept['code'] !== $update_data['code']) {
                    $changes[] = "Code ('{$dept['code']}' -> '{$update_data['code']}')";
                }
                if ($dept['name'] !== $update_data['name']) {
                    $changes[] = "Name ('{$dept['name']}' -> '{$update_data['name']}')";
                }
                if ($dept['description'] !== $update_data['description']) {
                    $changes[] = "Description changed";
                }

                $audit_desc = "Updated department: {$update_data['name']}.";
                if (!empty($changes)) {
                    $audit_desc .= " Changes: " . implode(', ', $changes);
                } else {
                    $audit_desc .= " No values were changed.";
                }

                $this->auditModel->log_activity('UPDATE_DEPT', 'Departments', $audit_desc);

                session()->setFlashdata('success', 'Department details successfully updated!');
                return redirect()->to('departments');
            } else {
                $data['error'] = 'An error occurred while updating the department. Please try again.';
            }
        }

        return view('templates/header', $data)
             . view('departments/edit', $data)
             . view('templates/footer');
    }

    /**
     * Delete department record
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

        if ($this->departmentModel->delete($id)) {
            $this->auditModel->log_activity(
                'DELETE_DEPT',
                'Departments',
                "Deleted hospital department: {$dept['name']} (Code: {$dept['code']})."
            );

            session()->setFlashdata('success', 'Department successfully deleted!');
        } else {
            session()->setFlashdata('error', 'An error occurred while trying to delete the department.');
        }

        return redirect()->to('departments');
    }
}
