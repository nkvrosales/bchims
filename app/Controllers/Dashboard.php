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
     * Main dashboard dashboard view.
     */
    public function index()
    {
        if ($res = $this->checkAuth()) return $res;

        $db = \Config\Database::connect();

        $role = session()->get('role');

        $data['title'] = 'Dashboard';
        $data['recent_logs'] = $this->auditModel->get_recent_logs(5);
        $data['total_users'] = $db->table('user')->countAll();

        // Always fetch the real department_id from the DB (session may be stale / not set for old logins)
        $userModel = new \App\Models\UserModel();
        $currentUser = $userModel->get_user_by_id(session()->get('user_id'));
        $deptId = $currentUser['department_id'] ?? null;

        if (is_admin_role()) {
            // Count pending (1) and partially served (2) requests
            $reqCountRow = $db->query(
                "SELECT COUNT(*) AS cnt FROM request WHERE request_status IN (1, 2) AND status > 0"
            )->getRowArray();
            $data['total_inventory'] = (int)($reqCountRow['cnt'] ?? 0);

            $data['total_low_stock'] = (int)$db->query(
                "SELECT COUNT(*) AS cnt FROM (
                    SELECT item_code, item_name, SUM(quantity_on_hand) AS total_qoh
                    FROM central_supply
                    WHERE status = 1
                    GROUP BY item_code, item_name
                    HAVING total_qoh <= 10 AND total_qoh > 0
                ) AS grouped"
            )->getRowArray()['cnt'];

            $data['total_no_stock'] = (int)$db->query(
                "SELECT COUNT(*) AS cnt
                FROM central_supply
                WHERE status = 1 AND quantity_on_hand = 0"
            )->getRowArray()['cnt'];

            $data['total_expired'] = (int)$db->query(
                "SELECT COUNT(*) AS cnt
                FROM central_supply
                WHERE status = 1 AND expiration_date < CURDATE() AND quantity_on_hand > 0"
            )->getRowArray()['cnt'];

            $data['total_near_expiry'] = (int)$db->query(
                "SELECT COUNT(*) AS cnt
                FROM central_supply
                WHERE status = 1
                AND expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                AND quantity_on_hand > 0"
            )->getRowArray()['cnt'];

            $data['near_expiry_items'] = $db->query(
                "SELECT item_code, item_name, quantity_on_hand, expiration_date
                FROM central_supply
                WHERE status = 1
                AND expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                AND quantity_on_hand > 0
                ORDER BY expiration_date ASC
                LIMIT 5"
            )->getResultArray();
        } else {
            // Staff: count pending (1) and partially served (2) requests for this department
            $data['total_inventory'] = (int)$db->query(
                "SELECT COUNT(*) AS cnt FROM request r
                 INNER JOIN department_supply ds ON ds.department_supply_id = r.department_supply_id
                 WHERE ds.department_id = ? AND r.request_status IN (1, 2) AND r.status > 0",
                [$deptId]
            )->getRowArray()['cnt'];

            $data['total_low_stock'] = (int)$db->query(
                "SELECT COUNT(*) AS cnt FROM (
                    SELECT i.item_code, i.item_name, SUM(ds.quantity_on_hand) AS total_qoh
                    FROM inventory i
                    INNER JOIN supply s ON s.inventory_id = i.inventory_id
                    INNER JOIN department_supply ds ON ds.department_supply_id = s.department_supply_id
                    WHERE ds.department_id = ? AND i.status = 1
                    GROUP BY i.item_code, i.item_name
                    HAVING total_qoh <= 10 AND total_qoh > 0
                ) AS grouped",
                [$deptId]
            )->getRowArray()['cnt'];

            $data['total_no_stock'] = (int)$db->query(
                "SELECT COUNT(*) AS cnt
                FROM inventory i
                INNER JOIN supply s ON s.inventory_id = i.inventory_id
                INNER JOIN department_supply ds ON ds.department_supply_id = s.department_supply_id
                WHERE ds.department_id = ? AND i.status = 1 AND ds.quantity_on_hand = 0",
                [$deptId]
            )->getRowArray()['cnt'];

            $data['total_expired'] = (int)$db->query(
                "SELECT COUNT(*) AS cnt
                FROM inventory i
                INNER JOIN supply s ON s.inventory_id = i.inventory_id
                INNER JOIN department_supply ds ON ds.department_supply_id = s.department_supply_id
                WHERE ds.department_id = ? AND i.status = 1
                AND i.expiration_date < CURDATE() AND ds.quantity_on_hand > 0",
                [$deptId]
            )->getRowArray()['cnt'];

            $data['total_near_expiry'] = (int)$db->query(
                "SELECT COUNT(*) AS cnt
                FROM inventory i
                INNER JOIN supply s ON s.inventory_id = i.inventory_id
                INNER JOIN department_supply ds ON ds.department_supply_id = s.department_supply_id
                WHERE ds.department_id = ? AND i.status = 1
                AND i.expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                AND ds.quantity_on_hand > 0",
                [$deptId]
            )->getRowArray()['cnt'];

            $data['near_expiry_items'] = $db->query(
                "SELECT i.item_code, i.item_name, ds.quantity_on_hand, i.expiration_date
                FROM inventory i
                INNER JOIN supply s ON s.inventory_id = i.inventory_id
                INNER JOIN department_supply ds ON ds.department_supply_id = s.department_supply_id
                WHERE ds.department_id = ? AND i.status = 1
                AND i.expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                AND ds.quantity_on_hand > 0
                ORDER BY i.expiration_date ASC
                LIMIT 5",
                [$deptId]
            )->getResultArray();
        }

        return view('templates/header', $data)
             . view('dashboard/dashboard', $data)
             . view('templates/footer');
    }

    public function audit_trail()
    {
        if ($res = $this->checkAuth()) return $res;

        $search = $this->request->getGet('search');
        $action_filter = $this->request->getGet('action_filter');

        $data['title'] = 'Audit Log';
        $data['search'] = $search;
        $data['action_filter'] = $action_filter;

        $auditModel = new \App\Models\AuditModel();
        $filters = [];
        if (!empty($search)) {
            $filters['search'] = $search;
        }
        if (!empty($action_filter)) {
            $filters['action'] = $action_filter;
        }
        $data['logs'] = $auditModel->get_audit_logs($filters);

        return view('templates/header', $data)
             . view('audit', $data)
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
        $module = $this->request->getPost('module') ?? 'Audit Logs';
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

        $rules = [
            'username'   => 'required|alpha_numeric_punct|max_length[30]',
            'last_name'  => 'required|max_length[50]',
            'first_name' => 'required|max_length[50]',
            'email'      => 'permit_empty|valid_email|max_length[100]',
            'password'   => 'permit_empty|min_length[4]|max_length[50]',
            'confirm_password' => 'permit_empty|matches[password]',
            'old_password'     => 'required',
        ];

        $validationMessages = [
            'confirm_password' => [
                'matches' => 'The new passwords do not match.',
            ],
            'old_password' => [
                'required' => 'Current password is required to save changes.',
            ],
        ];

        if (strcasecmp($this->request->getMethod(), 'post') === 0 && $this->validate($rules, $validationMessages)) {
            $username = $this->request->getPost('username');
            $old_password = $this->request->getPost('old_password');
            $password = $this->request->getPost('password');
            $email = $this->request->getPost('email');

            // Verify current password
            if (!password_verify($old_password, $user['password'])) {
                $data['error'] = 'Current password is incorrect.';
            }

            if (!isset($data['error'])) {
            $update_data = [
                'username'   => $username,
                'last_name'  => $this->request->getPost('last_name'),
                'first_name' => $this->request->getPost('first_name'),
                'email'      => !empty($email) ? $email : null,
            ];

            if ($username !== $user['username'] && $userModel->get_user_by_username($username)) {
                $data['error'] = 'That username is already taken. Please choose another one.';
            } else {
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

                    // Log activity
                    $auditDesc = "Updated own profile details.";
                    $changes = [];
                    if (!empty($password)) {
                        $changes[] = "Password";
                    }
                    if ($update_data['first_name'] !== $user['first_name']) {
                        $changes[] = "First Name";
                    }
                    if ($update_data['last_name'] !== $user['last_name']) {
                        $changes[] = "Last Name";
                    }
                    if ($update_data['username'] !== $user['username']) {
                        $changes[] = "Username";
                    }
                    if ($update_data['email'] !== ($user['email'] ?? '')) {
                        $changes[] = "Email";
                    }
                    if (!empty($changes)) {
                        $auditDesc .= " Changed " . implode(', ', $changes) . ".";
                    }
                    $this->auditModel->log_activity('UPDATE_PROFILE', 'Auth', $auditDesc);

                    session()->setFlashdata('success', 'Profile details updated successfully!');
                    return redirect()->to('dashboard/profile');
                }

                $data['error'] = 'An error occurred while updating your profile. Please try again.';
            }
            }
        }

        return view('templates/header', $data)
             . view('dashboard/profile', $data)
             . view('templates/footer');
    }

}

