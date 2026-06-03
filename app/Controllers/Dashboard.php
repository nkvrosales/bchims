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

        $data['title'] = 'Dashboard';
        $data['recent_logs'] = $this->auditModel->get_recent_logs(5);
        $data['total_users'] = $db->table('users')->countAll();
        $data['total_logs'] = $db->table('audit_logs')->countAll();

        return view('templates/header', $data)
             . view('dashboard/index', $data)
             . view('templates/footer');
    }

    /**
     * Advanced Audit Trail log system.
     */
    public function audit_trail()
    {
        if ($res = $this->checkAuth()) return $res;

        $db = \Config\Database::connect();

        $data['title'] = 'Audit Trail';

        $filters = array(
            'start_date' => $this->request->getGet('start_date'),
            'end_date'   => $this->request->getGet('end_date'),
            'username'   => $this->request->getGet('username'),
            'action'     => $this->request->getGet('action'),
            'module'     => $this->request->getGet('module')
        );

        $data['filters'] = $filters;
        $data['logs'] = $this->auditModel->get_audit_logs($filters);

        $query_actions = $db->table('audit_logs')->select('action_type')->distinct()->get();
        $data['unique_actions'] = array_column($query_actions->getResultArray(), 'action_type');

        $data['archives'] = [];
        if (session()->get('role') === 'admin') {
            $archivePath = WRITEPATH . 'uploads/archives/';
            if (is_dir($archivePath)) {
                $files = array_diff(scandir($archivePath), array('.', '..'));
                arsort($files);
                foreach ($files as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'csv') {
                        $filePath = $archivePath . $file;
                        $data['archives'][] = [
                            'filename' => $file,
                            'size'     => filesize($filePath),
                            'date'     => date('Y-m-d H:i:s', filemtime($filePath))
                        ];
                    }
                }
            }
        }

        return view('templates/header', $data)
             . view('dashboard/audit_trail', $data)
             . view('templates/footer');
     }

    /**
     * Archive and purge audit logs (by date range or by specific selected IDs).
     */
    public function archive_logs()
    {
        if ($res = $this->checkAuth()) return $res;

        if (session()->get('role') !== 'admin') {
            session()->setFlashdata('error', 'You do not have permission to archive audit logs.');
            return redirect()->to('dashboard/audit_trail');
        }

        $archiveMode = $this->request->getPost('archive_mode') ?? 'by_date';
        $logsToArchive = [];

        if ($archiveMode === 'by_selection') {
            // --- Mode: Archive specific selected rows ---
            $rawIds = $this->request->getPost('log_ids');
            if (empty($rawIds) || !is_array($rawIds)) {
                session()->setFlashdata('error', 'No log entries were selected. Please select at least one row to archive.');
                return redirect()->to('dashboard/audit_trail');
            }
            // Sanitise: ensure all IDs are positive integers
            $ids = array_filter(array_map('intval', $rawIds), fn($id) => $id > 0);
            if (empty($ids)) {
                session()->setFlashdata('error', 'Invalid log entry selection.');
                return redirect()->to('dashboard/audit_trail');
            }
            $logsToArchive = $this->auditModel->get_logs_by_ids($ids);
        } else {
            // --- Mode: Archive by date cutoff ---
            $archiveDate = $this->request->getPost('archive_date');
            if (empty($archiveDate)) {
                session()->setFlashdata('error', 'Please select a valid date for archiving.');
                return redirect()->to('dashboard/audit_trail');
            }
            $logsToArchive = $this->auditModel->get_logs_before_date($archiveDate);
        }

        $count = count($logsToArchive);
        if ($count === 0) {
            session()->setFlashdata('warning', 'No audit logs matched the archive criteria.');
            return redirect()->to('dashboard/audit_trail');
        }

        // Ensure backup directory exists
        $archiveDir = WRITEPATH . 'uploads/archives/';
        if (!is_dir($archiveDir)) {
            mkdir($archiveDir, 0777, true);
        }

        // Generate unique filename
        $filename = 'audit_logs_archive_' . date('Ymd_His') . '.csv';
        $filepath = $archiveDir . $filename;

        // Write CSV (with UTF-8 BOM for Excel compatibility)
        $fp = fopen($filepath, 'w');
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($fp, ['Log ID', 'Timestamp', 'User ID', 'Username', 'Full Name', 'Action Type', 'Module/Table', 'Record ID', 'Description']);
        foreach ($logsToArchive as $log) {
            fputcsv($fp, [
                $log['log_id'],
                $log['created_at'],
                $log['user_id'],
                $log['username'],
                $log['full_name'],
                $log['action'],
                $log['table_name'],
                $log['record_id'],
                $log['description']
            ]);
        }
        fclose($fp);

        // Purge from database
        if ($archiveMode === 'by_selection') {
            $ids = array_column($logsToArchive, 'log_id');
            $this->auditModel->delete_logs_by_ids($ids);
            $modeLabel = "selected entries";
        } else {
            $archiveDate = $this->request->getPost('archive_date');
            $this->auditModel->delete_logs_before_date($archiveDate);
            $modeLabel = "entries on or before {$archiveDate}";
        }

        // Log the archive action itself
        $this->auditModel->log_activity(
            'ARCHIVE_LOGS',
            'Audit Trail',
            "Archived and purged {$count} audit log {$modeLabel}. Backup saved as: {$filename}."
        );

        session()->setFlashdata('success', "Successfully archived and purged {$count} audit log(s). Backup file '{$filename}' has been saved on the server.");
        return redirect()->to('dashboard/audit_trail');
    }

    /**
     * Download a previously archived logs CSV file.
     */
    public function download_archive($filename)
    {
        if ($res = $this->checkAuth()) return $res;

        if (session()->get('role') !== 'admin') {
            session()->setFlashdata('error', 'You do not have permission to download log archives.');
            return redirect()->to('dashboard/audit_trail');
        }

        // Prevent path traversal
        $filename = basename($filename);
        $filepath = WRITEPATH . 'uploads/archives/' . $filename;

        if (!file_exists($filepath)) {
            session()->setFlashdata('error', 'The requested archive file does not exist.');
            return redirect()->to('dashboard/audit_trail');
        }

        // Trigger file download
        return $this->response->download($filepath, null)->setFileName($filename);
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


