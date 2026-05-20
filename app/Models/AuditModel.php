<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditModel extends Model
{
    protected $table      = 'audit_logs';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['user_id', 'username', 'action', 'module', 'description', 'ip_address'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Log a user activity in the database.
     */
    public function log_activity($action, $module, $description)
    {
        $session = \Config\Services::session();
        $request = \Config\Services::request();

        $user_id = $session->get('user_id');
        $username = $session->get('username');

        if (empty($user_id)) {
            $user_id = NULL;
            $username = !empty($username) ? $username : 'Guest';
        }

        $data = array(
            'user_id'     => $user_id,
            'username'    => $username,
            'action'      => strtoupper($action),
            'module'      => ucfirst($module),
            'description' => $description,
            'ip_address'  => $request->getIPAddress()
        );

        return $this->insert($data);
    }

    /**
     * Retrieve audit logs based on various optional filters.
     */
    public function get_audit_logs($filters = array())
    {
        $builder = $this;

        if (!empty($filters['start_date'])) {
            $builder = $builder->where('DATE(created_at) >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $builder = $builder->where('DATE(created_at) <=', $filters['end_date']);
        }

        if (!empty($filters['username'])) {
            $builder = $builder->like('username', $filters['username']);
        }

        if (!empty($filters['action'])) {
            $builder = $builder->where('action', strtoupper($filters['action']));
        }

        if (!empty($filters['module'])) {
            $builder = $builder->where('module', ucfirst($filters['module']));
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Retrieve the most recent N activity log entries.
     */
    public function get_recent_logs($limit = 5)
    {
        return $this->orderBy('created_at', 'DESC')->findAll($limit);
    }
}
