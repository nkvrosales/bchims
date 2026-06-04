<?php

namespace App\Controllers;

use App\Models\AuditModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    protected $auditModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->auditModel = new AuditModel();

        // Secure all dashboard actions: Redirect to login if user session is not active
        if (!session()->get('logged_in')) {
            // Note: In initController, we can't easily return a redirect.
            // But we can throw an exception or handle it in the methods.
        }
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
     * Main dashboard dashboard view.
     */
    public function index()
    {
        if ($res = $this->checkAuth()) return $res;

        $db = \Config\Database::connect();
        $requestModel = new \App\Models\SupplyRequestModel();

        $role = session()->get('role');

        $data['title'] = 'Dashboard';
        $data['recent_logs'] = $this->auditModel->get_recent_logs(5);
        $data['total_users'] = $db->table('user')->countAll();

        // Always fetch the real department_id from the DB (session may be stale / not set for old logins)
        $userModel = new \App\Models\UserModel();
        $currentUser = $userModel->get_user_by_id(session()->get('user_id'));
        $deptId = $currentUser['department_id'] ?? null;

        if ($role === 'admin') {
            $data['total_inventory'] = $db->table('central_supply')->countAll();
            $data['total_low_stock'] = $db->table('central_supply')->where('quantity <=', 10)->where('quantity >', 0)->countAllResults();
            $data['total_no_stock'] = $db->table('central_supply')->where('quantity', 0)->countAllResults();
            $data['total_requests'] = $db->table('request')->countAll();
        } else {
            // Staff: count department_supply rows for this department (each row = one supply item allocation)
            $data['total_inventory'] = $db->table('department_supply')
                ->where('department_id', $deptId)
                ->countAllResults();

            $data['total_low_stock'] = $db->table('department_supply')
                ->where('department_id', $deptId)
                ->where('quantity_on_hand <=', 10)
                ->where('quantity_on_hand >', 0)
                ->countAllResults();

            $data['total_no_stock'] = $db->table('department_supply')
                ->where('department_id', $deptId)
                ->where('quantity_on_hand', 0)
                ->countAllResults();

            $data['total_requests'] = $db->table('request')
                ->join('department_supply', 'department_supply.department_supply_id = request.department_supply_id')
                ->where('department_supply.department_id', $deptId)
                ->countAllResults();
        }

        // Fetch recent requests: if staff, filter by department
        if ($role === 'admin') {
            $data['recent_requests'] = array_slice($requestModel->get_requests(), 0, 5);
        } else {
            $data['recent_requests'] = array_slice($requestModel->get_requests(null, $deptId), 0, 5);
        }

        return view('templates/header', $data)
             . view('dashboard/index', $data)
             . view('templates/footer');
    }

    /**
     * Audit Trail log viewer.
     */
    public function audit_trail()
    {
        if ($res = $this->checkAuth()) return $res;

        $data['title'] = 'Audit Trail';
        $data['logs']  = $this->auditModel->get_audit_logs();

        return view('templates/header', $data)
             . view('dashboard/audit_trail', $data)
             . view('templates/footer');
    }

    /**
     * Log client-side actions (Export CSV or Print History) to the Audit Trail via AJAX.
     */
    public function log_action()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Direct access not allowed']);
        }

        if (!session()->get('logged_in')) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $action = $this->request->getPost('action');
        $module = $this->request->getPost('module') ?? 'Audit Trail';
        $description = $this->request->getPost('description');

        if (empty($action) || empty($description)) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Missing parameters']);
        }

        $this->auditModel->log_activity($action, $module, $description);

        return $this->response->setJSON(['status' => 'success']);
    }

    /**
     * User profile settings page.
     */
    public function profile()
    {
        if ($res = $this->checkAuth()) return $res;

        $userModel = new UserModel();
        $departmentModel = new \App\Models\DepartmentModel();

        $userId = session()->get('user_id');
        $user = $userModel->get_user_by_id($userId);

        if (empty($user)) {
            session()->setFlashdata('error', 'User not found.');
            return redirect()->to('dashboard');
        }

        $data['title'] = 'Profile Settings';
        $data['user'] = $user;
        $data['departments'] = $departmentModel->get_departments();

        $isAdmin = (session()->get('role') === 'admin');

        $rules = [
            'username'   => 'required|alpha_numeric_punct|min_length[4]|max_length[30]',
            'last_name'  => 'required|max_length[50]',
            'first_name' => 'required|max_length[50]',
            'password'   => 'permit_empty|min_length[4]|max_length[50]',
        ];

        if ($isAdmin) {
            $rules['role'] = 'required|in_list[admin,staff]';
            $rules['department_id'] = 'permit_empty|numeric';
        }

        if (strcasecmp($this->request->getMethod(), 'post') === 0 && $this->validate($rules)) {
            $username = $this->request->getPost('username');
            $update_data = [
                'username'   => $username,
                'last_name'  => $this->request->getPost('last_name'),
                'first_name' => $this->request->getPost('first_name'),
            ];

            if ($isAdmin) {
                $role = $this->request->getPost('role');
                $update_data['role_id'] = ($role === 'admin') ? 1 : 2;
                $dept_id = $this->request->getPost('department_id');
                $update_data['department_id'] = !empty($dept_id) ? (int)$dept_id : NULL;
            }

            if ($username !== $user['username'] && $userModel->get_user_by_username($username)) {
                $data['error'] = 'That username is already taken. Please choose another one.';
            } else {
                $password = $this->request->getPost('password');
                if (!empty($password)) {
                    $update_data['password'] = $password;
                }

                if ($userModel->update_user($userId, $update_data)) {
                    // Update active session name fields
                    $combined_name = "{$update_data['first_name']} {$update_data['last_name']}";
                    session()->set('last_name', $update_data['last_name']);
                    session()->set('first_name', $update_data['first_name']);
                    session()->set('full_name', $combined_name);
                    session()->set('username', $username);
                    if ($isAdmin) {
                        session()->set('role', $role);
                    }

                    // Log activity
                    $auditDesc = "Updated own profile details.";
                    if (!empty($password)) {
                        $auditDesc .= " Changed name and password.";
                    } else {
                        $auditDesc .= " Changed name.";
                    }
                    $this->auditModel->log_activity('UPDATE_PROFILE', 'Auth', $auditDesc);

                    session()->setFlashdata('success', 'Profile details updated successfully!');
                    return redirect()->to('dashboard/profile');
                }

                $data['error'] = 'An error occurred while updating your profile. Please try again.';
            }
        }

        return view('templates/header', $data)
             . view('dashboard/profile', $data)
             . view('templates/footer');
    }

    /**
     * System settings page.
     */
    public function settings()
    {
        if ($res = $this->checkAuth()) return $res;

        // Settings are only accessible by administrator
        if (session()->get('role') !== 'admin') {
            session()->setFlashdata('error', 'You do not have permission to access the System Settings page.');
            return redirect()->to('dashboard');
        }

        $data['title'] = 'System Settings';

        return view('templates/header', $data)
             . view('dashboard/settings', $data)
             . view('templates/footer');
    }
}
