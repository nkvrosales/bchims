<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditModel extends Model
{
    protected $table      = 'audit_log';
    protected $primaryKey = 'user_log_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['user_id', 'action_type', 'action_description', 'ip_address', 'user_agent'];

    protected $useTimestamps = false;

    /**
     * Log a user activity in the database.
     */
    public function log_activity($action, $module, $description, $record_id = NULL)
    {
        $session = \Config\Services::session();

        $user_id = $session->get('user_id');

        if (empty($user_id)) {
            $user_id = NULL;
        } else {
            // Verify if the user_id exists to prevent FK constraint failures.
            $db = \Config\Database::connect();
            $userExists = $db->table('user')->where('user_id', $user_id)->countAllResults();
            if ($userExists === 0) {
                $user_id = NULL;
            }
        }

        $data = array(
            'user_id'            => $user_id,
            'action_type'        => strtoupper($action),
            'action_description' => $description,
            'ip_address'         => \Config\Services::request()->getIPAddress(),
            'user_agent'         => \Config\Services::request()->getUserAgent()->getAgentString(),
        );

        return $this->insert($data);
    }

    /**
     * Retrieve audit logs based on various optional filters.
     */
    public function get_audit_logs($filters = array())
    {
        $builder = $this->select("audit_log.*, audit_log.user_log_id AS log_id, audit_log.action_type AS action, audit_log.action_description AS description, audit_log.action_date AS created_at, CONCAT(user.first_name, ' ', user.last_name) AS full_name, COALESCE(user.username, 'Guest') AS username, audit_log.ip_address, audit_log.user_agent")
                        ->join('user', 'user.user_id = audit_log.user_id', 'left');

        if (!is_admin_role()) {
            $builder = $builder->where('audit_log.user_id', session()->get('user_id'));
        }

        if (!empty($filters['start_date'])) {
            $builder = $builder->where('DATE(action_date) >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $builder = $builder->where('DATE(action_date) <=', $filters['end_date']);
        }

        if (!empty($filters['username'])) {
            $builder = $builder->like('user.username', $filters['username']);
        }

        if (!empty($filters['action'])) {
            $builder = $builder->where('action_type', strtoupper($filters['action']));
        }

        if (!empty($filters['module'])) {
            $builder = $builder->like('action_description', '[' . ucfirst($filters['module']) . ']');
        }

        return $builder->orderBy('action_date', 'DESC')->findAll();
    }

    /**
     * Retrieve the most recent N activity log entries.
     */
    public function get_recent_logs($limit = 5)
    {
        $builder = $this->select("audit_log.*, audit_log.user_log_id AS log_id, audit_log.action_type AS action, audit_log.action_description AS description, audit_log.action_date AS created_at, CONCAT(user.first_name, ' ', user.last_name) AS full_name, COALESCE(user.username, 'Guest') AS username, audit_log.ip_address, audit_log.user_agent")
                        ->join('user', 'user.user_id = audit_log.user_id', 'left');
                        
        if (!is_admin_role()) {
            $builder = $builder->where('audit_log.user_id', session()->get('user_id'));
        }
        return $builder->orderBy('action_date', 'DESC')->findAll($limit);
    }

}
