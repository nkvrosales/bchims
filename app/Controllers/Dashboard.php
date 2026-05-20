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

        $data['title'] = 'Audit Trail Log';

        $filters = array(
            'start_date' => $this->request->getGet('start_date'),
            'end_date'   => $this->request->getGet('end_date'),
            'username'   => $this->request->getGet('username'),
            'action'     => $this->request->getGet('action'),
            'module'     => $this->request->getGet('module')
        );

        $data['filters'] = $filters;
        $data['logs'] = $this->auditModel->get_audit_logs($filters);

        $query_actions = $db->table('audit_logs')->select('action')->distinct()->get();
        $data['unique_actions'] = array_column($query_actions->getResultArray(), 'action');

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
}
