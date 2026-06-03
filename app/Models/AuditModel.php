<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditModel extends Model
{
    protected $table      = 'audit_logs';
    protected $primaryKey = 'log_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['user_id', 'action_type', 'table_name', 'record_id', 'action_description'];

    protected $useTimestamps = true;
    protected $createdField  = 'action_date';
    protected $updatedField  = '';

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
            // Verify if the user_id exists in the users table to prevent FK constraint failures
            $db = \Config\Database::connect();
            $userExists = $db->table('users')->where('user_id', $user_id)->countAllResults();
            if ($userExists === 0) {
                $user_id = NULL;
            }
        }

        $data = array(
            'user_id'            => $user_id,
            'action_type'        => strtoupper($action),
            'table_name'         => ucfirst($module),
            'record_id'          => $record_id,
            'action_description' => $description
        );

        return $this->insert($data);
    }

    /**
     * Retrieve audit logs based on various optional filters.
     */
    public function get_audit_logs($filters = array())
    {
        $builder = $this->select("audit_logs.*, audit_logs.action_type AS action, audit_logs.action_description AS description, audit_logs.action_date AS created_at, CONCAT(users.first_name, ' ', users.last_name) AS full_name, users.username AS username")
                        ->join('users', 'users.user_id = audit_logs.user_id', 'left');

        if (session()->get('role') !== 'admin') {
            $builder = $builder->where('audit_logs.user_id', session()->get('user_id'));
        }

        if (!empty($filters['start_date'])) {
            $builder = $builder->where('DATE(action_date) >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $builder = $builder->where('DATE(action_date) <=', $filters['end_date']);
        }

        if (!empty($filters['username'])) {
            $builder = $builder->like('users.username', $filters['username']);
        }

        if (!empty($filters['action'])) {
            $builder = $builder->where('action_type', strtoupper($filters['action']));
        }

        if (!empty($filters['module'])) {
            $builder = $builder->where('table_name', ucfirst($filters['module']));
        }

        return $builder->orderBy('action_date', 'DESC')->findAll();
    }

    /**
     * Retrieve the most recent N activity log entries.
     */
    public function get_recent_logs($limit = 5)
    {
        $builder = $this->select("audit_logs.*, audit_logs.action_type AS action, audit_logs.action_description AS description, audit_logs.action_date AS created_at, CONCAT(users.first_name, ' ', users.last_name) AS full_name, users.username AS username")
                        ->join('users', 'users.user_id = audit_logs.user_id', 'left');
                        
        if (session()->get('role') !== 'admin') {
            $builder = $builder->where('audit_logs.user_id', session()->get('user_id'));
        }
        return $builder->orderBy('action_date', 'DESC')->findAll($limit);
    }

    /**
     * Get audit logs up to a specific date.
     */
    public function get_logs_before_date($date)
    {
        return $this->select("audit_logs.*, audit_logs.action_type AS action, audit_logs.action_description AS description, audit_logs.action_date AS created_at, CONCAT(users.first_name, ' ', users.last_name) AS full_name, users.username AS username")
                    ->join('users', 'users.user_id = audit_logs.user_id', 'left')
                    ->where('DATE(action_date) <=', $date)
                    ->orderBy('action_date', 'ASC')
                    ->findAll();
    }

    /**
     * Delete audit logs up to a specific date.
     */
    public function delete_logs_before_date($date)
    {
        return $this->where('DATE(action_date) <=', $date)->delete();
    }

    /**
     * Get specific audit logs by an array of log IDs.
     */
    public function get_logs_by_ids(array $ids)
    {
        return $this->select("audit_logs.*, audit_logs.action_type AS action, audit_logs.action_description AS description, audit_logs.action_date AS created_at, CONCAT(users.first_name, ' ', users.last_name) AS full_name, users.username AS username")
                    ->join('users', 'users.user_id = audit_logs.user_id', 'left')
                    ->whereIn('log_id', $ids)
                    ->orderBy('action_date', 'ASC')
                    ->findAll();
    }

    /**
     * Delete specific audit logs by an array of log IDs.
     */
    public function delete_logs_by_ids(array $ids)
    {
        return $this->whereIn('log_id', $ids)->delete();
    }
}

