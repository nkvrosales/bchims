<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\DepartmentModel;
use App\Models\AuditModel;

class Users extends BaseController
{
    protected $userModel;
    protected $departmentModel;
    protected $auditModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->userModel = new UserModel();
        $this->departmentModel = new DepartmentModel();
        $this->auditModel = new AuditModel();
    }

    protected function checkAdmin()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        // Verify the user actually exists to handle reseeded/stale sessions gracefully
        $user = $this->userModel->get_user_by_id(session()->get('user_id'));
        if (empty($user)) {
            session()->destroy();
            return redirect()->to('auth/login');
        }

        if (session()->get('role') !== 'admin') {
            session()->setFlashdata('error', 'Access Denied: Administrative privileges required.');
            return redirect()->to('dashboard');
        }
        return null;
    }

    /**
     * Display all user accounts in the database
     */
    public function index()
    {
        if ($res = $this->checkAdmin()) return $res;

        $data['title'] = 'User Management';
        $data['users'] = $this->userModel->get_users();
        $data['departments'] = $this->departmentModel->get_departments();

        return view('templates/header', $data)
             . view('users/index', $data)
             . view('templates/footer');
    }

    /**
     * Add/Register a new user account
     */
    public function create()
    {
        if ($res = $this->checkAdmin()) return $res;

        // GET requests: redirect to users list (modal is embedded on index page)
        if (strcasecmp($this->request->getMethod(), 'get') === 0) {
            return redirect()->to('users');
        }

        $rules = [
            'username'      => 'required|alpha_dash|max_length[50]|is_unique[users.username]',
            'last_name'     => 'required|max_length[50]',
            'first_name'    => 'required|max_length[50]',
            'password'      => 'required|min_length[4]|max_length[50]',
            'role'          => 'required|in_list[admin,staff]',
            'department_id' => 'permit_empty|numeric',
            'is_active'     => 'required|in_list[0,1]',
        ];

        if ($this->validate($rules)) {
            $dept_id = $this->request->getPost('department_id');
            $role = $this->request->getPost('role');
            $is_active = (int)$this->request->getPost('is_active');
            $insert_data = array(
                'username'       => strtolower($this->request->getPost('username')),
                'last_name'      => $this->request->getPost('last_name'),
                'first_name'     => $this->request->getPost('first_name'),
                'password'       => $this->request->getPost('password'),
                'role_id'        => ($role === 'admin') ? 1 : 2,
                'department_id'  => !empty($dept_id) ? (int)$dept_id : NULL,
                'account_status' => ($is_active === 1) ? 'Active' : 'Inactive'
            );

            if ($this->userModel->insert_user($insert_data)) {
                $dept_log = 'None';
                if (!empty($insert_data['department_id'])) {
                    $dept_obj = $this->departmentModel->find($insert_data['department_id']);
                    if ($dept_obj) {
                        $dept_log = $dept_obj['name'] ?? $dept_obj['department_name'] ?? 'Unknown';
                    }
                }

                $display_name = "{$insert_data['last_name']}, {$insert_data['first_name']}";
                $this->auditModel->log_activity(
                    'CREATE_USER',
                    'Users',
                    "Created new user account: {$insert_data['username']} ({$display_name}) with role {$role}, department {$dept_log}, and status {$insert_data['account_status']}."
                );

                session()->setFlashdata('success', 'User account successfully created!');
                return redirect()->to('users');
            } else {
                // DB insert error
                session()->setFlashdata('create_modal_open', true);
                session()->setFlashdata('create_validation_errors', '<li>An error occurred while creating the account. Please try again.</li>');
                return redirect()->to('users')->withInput();
            }
        } else {
            // Validation failed — re-open modal with errors
            session()->setFlashdata('create_modal_open', true);
            session()->setFlashdata('create_validation_errors', $this->validator->listErrors());
            return redirect()->to('users')->withInput();
        }
    }

    /**
     * Edit/Update user account details via modal redirect
     */
    public function edit($id = NULL)
    {
        if ($res = $this->checkAdmin()) return $res;

        if (empty($id)) {
            return redirect()->to('users');
        }

        $user = $this->userModel->get_user_by_id($id);
        if (empty($user)) {
            session()->setFlashdata('error', 'User not found.');
            return redirect()->to('users');
        }

        // If not post request, redirect to users and trigger opening the edit modal
        if (strcasecmp($this->request->getMethod(), 'post') !== 0) {
            session()->setFlashdata('edit_modal_open_id', $id);
            return redirect()->to('users');
        }

        $rules = [
            'username'      => "required|alpha_dash|max_length[50]|is_unique[users.username,user_id,{$id}]",
            'last_name'     => 'required|max_length[50]',
            'first_name'    => 'required|max_length[50]',
            'password'      => 'permit_empty|min_length[4]|max_length[50]',
            'role'          => 'required|in_list[admin,staff]',
            'department_id' => 'permit_empty|numeric',
            'is_active'     => 'required|in_list[0,1]',
        ];

        if ($this->validate($rules)) {
            $current_admin_id = session()->get('user_id');
            $dept_id = $this->request->getPost('department_id');
            $role = $this->request->getPost('role');
            $is_active = (int)$this->request->getPost('is_active');

            if ((int)$id === (int)$current_admin_id) {
                $role = 'admin';
                $is_active = 1;
            }

            $update_data = array(
                'username'       => strtolower($this->request->getPost('username')),
                'last_name'      => $this->request->getPost('last_name'),
                'first_name'     => $this->request->getPost('first_name'),
                'role_id'        => ($role === 'admin') ? 1 : 2,
                'department_id'  => !empty($dept_id) ? (int)$dept_id : NULL,
                'account_status' => ($is_active === 1) ? 'Active' : 'Inactive'
            );

            $password = $this->request->getPost('password');
            if (!empty($password)) {
                $update_data['password'] = $password;
            }

            if ($this->userModel->update_user($id, $update_data)) {
                $changes = array();
                if ($user['username'] !== $update_data['username']) {
                    $changes[] = "Username ('{$user['username']}' -> '{$update_data['username']}')";
                }
                if ($user['last_name'] !== $update_data['last_name'] || $user['first_name'] !== $update_data['first_name']) {
                    $old_name = "{$user['last_name']}, {$user['first_name']}";
                    $new_name = "{$update_data['last_name']}, {$update_data['first_name']}";
                    $changes[] = "Name ('{$old_name}' -> '{$new_name}')";
                }
                if ($user['role'] !== $role) {
                    $changes[] = "Role ('{$user['role']}' -> '{$role}')";
                }
                if ((int)$user['department_id'] !== (int)$update_data['department_id']) {
                    $old_dept = 'None';
                    if (!empty($user['department_id'])) {
                        $old_dept_obj = $this->departmentModel->find($user['department_id']);
                        if ($old_dept_obj) {
                            $old_dept = $old_dept_obj['name'] ?? $old_dept_obj['department_name'] ?? 'Unknown';
                        }
                    }
                    $new_dept = 'None';
                    if (!empty($update_data['department_id'])) {
                        $new_dept_obj = $this->departmentModel->find($update_data['department_id']);
                        if ($new_dept_obj) {
                            $new_dept = $new_dept_obj['name'] ?? $new_dept_obj['department_name'] ?? 'Unknown';
                        }
                    }
                    $changes[] = "Department ('{$old_dept}' -> '{$new_dept}')";
                }
                if ((int)$user['is_active'] !== $is_active) {
                    $changes[] = "Status (" . ($user['is_active'] ? 'Active' : 'Inactive') . " -> " . ($is_active ? 'Active' : 'Inactive') . ")";
                }
                if (!empty($password)) {
                    $changes[] = "Password (Changed)";
                }

                $audit_desc = "Updated user account: {$update_data['username']}.";
                if (!empty($changes)) {
                    $audit_desc .= " Changes: " . implode(', ', $changes);
                } else {
                    $audit_desc .= " No values were changed.";
                }

                $this->auditModel->log_activity('UPDATE_USER', 'Users', $audit_desc);

                session()->setFlashdata('success', 'User details successfully updated!');
                return redirect()->to('users');
            } else {
                session()->setFlashdata('edit_modal_open_id', $id);
                session()->setFlashdata('edit_validation_errors', '<li>An error occurred while updating the account. Please try again.</li>');
                return redirect()->to('users')->withInput();
            }
        } else {
            // Validation failed — redirect back to users & open the modal with errors
            session()->setFlashdata('edit_modal_open_id', $id);
            session()->setFlashdata('edit_validation_errors', $this->validator->listErrors());
            return redirect()->to('users')->withInput();
        }
    }

    /**
     * Delete user account from database
     */
    public function delete($id = NULL)
    {
        if ($res = $this->checkAdmin()) return $res;

        if (empty($id)) {
            return redirect()->to('users');
        }

        $current_admin_id = session()->get('user_id');
        if ((int)$id === (int)$current_admin_id) {
            session()->setFlashdata('error', 'Critical Safeguard: You cannot delete your own logged-in admin account.');
            return redirect()->to('users');
        }

        $user = $this->userModel->get_user_by_id($id);
        if (empty($user)) {
            session()->setFlashdata('error', 'User not found.');
            return redirect()->to('users');
        }

        if ($this->userModel->delete($id)) {
            $this->auditModel->log_activity(
                'DELETE_USER',
                'Users',
                "Deleted user account: {$user['username']} ({$user['last_name']}, {$user['first_name']}) with role {$user['role']}."
            );

            session()->setFlashdata('success', 'User account successfully deleted!');
        } else {
            session()->setFlashdata('error', 'An error occurred while deleting the account.');
        }

        return redirect()->to('users');
    }
}
