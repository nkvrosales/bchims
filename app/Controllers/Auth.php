<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AuditModel;

class Auth extends BaseController
{
    protected $userModel;
    protected $auditModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->auditModel = new AuditModel();
    }

    /**
     * Display the login page or handle credential submission.
     */
    public function login()
    {
        $session = session();

        // If already logged in, redirect straight to dashboard
        if ($session->get('logged_in')) {
            return redirect()->to('dashboard');
        }

        // Set form validation rules
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            // Either initial page load (GET) or validation failed (POST)
            return view('login');
        } else {
            // Validation passed, verify credentials
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');

            $user = $this->userModel->verify_user($username, $password);

            if ($user) {
                // Successful verification, initialize session
                $session_data = [
                    'user_id'       => $user['user_id'],
                    'username'      => $user['username'],
                    'last_name'     => $user['last_name'],
                    'first_name'    => $user['first_name'],
                    'full_name'     => $user['full_name'],  // "LastName, FirstName"
                    'role'          => $user['role'],
                    'department_id' => $user['department_id'] ?? null,
                    'logged_in'     => true
                ];
                $session->set($session_data);

                // Write audit trail log
                $this->auditModel->log_activity('LOGIN', 'Auth', 'User logged in.');

                // Redirect to dashboard
                return redirect()->to('dashboard');
            } else {
                // Set flash data and reload login page
                $session->setFlashdata('error', 'Invalid username or password.');
                return redirect()->to('auth/login');
            }
        }
    }

    /**
     * Clear active session data, write logout action in audit log, and redirect.
     */
    public function logout()
    {
        $session = session();

        if ($session->get('logged_in')) {
            // Log logout action before destroying session
            $this->auditModel->log_activity('LOGOUT', 'Auth', 'User logged out.');
        }

        // Destroy session data
        $session->destroy();

        // Redirect back to login page
        return redirect()->to('auth/login');
    }
}
