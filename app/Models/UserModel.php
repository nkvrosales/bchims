<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'user';
    protected $primaryKey = 'user_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['first_name', 'last_name', 'username', 'email', 'password', 'role_id', 'department_id', 'status'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Verify user credentials and return user details if valid.
     */
    public function verify_user($username, $password)
    {
        $user = $this->select("user.*, CONCAT(user.first_name, ' ', user.last_name) AS full_name, departments.department_name AS department_name, roles.role_name AS role")
                     ->join('departments', 'departments.department_id = user.department_id', 'left')
                     ->join('roles', 'roles.role_id = user.role_id', 'inner')
                     ->groupStart()
                         ->where('user.username', $username)
                         ->orWhere('user.email', $username)
                     ->groupEnd()
                     ->where('user.status', 1)
                     ->first();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                if (password_needs_rehash($user['password'], PASSWORD_ARGON2ID)) {
                    $this->update($user['user_id'], ['password' => password_hash($password, PASSWORD_ARGON2ID)]);
                }
                unset($user['password']);
                return $user;
            }
        }
        return false;
    }

    /**
     * Retrieve all user accounts.
     */
    public function get_users()
    {
        return $this->select("user.user_id AS id, user.username, user.email, user.last_name, user.first_name, CONCAT(user.first_name, ' ', user.last_name) AS full_name, roles.role_name AS role, user.status, user.created_at, user.department_id, departments.department_name AS department_name, departments.department_code AS department_code, (user.status = 1) AS is_active, (SELECT MAX(action_date) FROM audit_log WHERE audit_log.user_id = user.user_id AND audit_log.action_type = 'LOGIN') AS last_login")
                    ->join('departments', 'departments.department_id = user.department_id', 'left')
                    ->join('roles', 'roles.role_id = user.role_id', 'inner')
                    ->orderBy('user.last_name', 'ASC')
                    ->orderBy('user.first_name', 'ASC')
                    ->limit(100)
                    ->find();
                    
    }

    /**
     * Search and retrieve user accounts with filters and limit from database.
     */
    public function search($keyword = null, $role_filter = null, $dept_filter = null, $limit = 3)
    {
        $current_user_id = session()->get('user_id');

        $builder = $this->select("user.user_id AS id, user.username, user.email, user.last_name, user.first_name, CONCAT(user.first_name, ' ', user.last_name) AS full_name, roles.role_name AS role, user.status, user.created_at, user.department_id, departments.department_name AS department_name, departments.department_code AS department_code, (user.status = 1) AS is_active, (SELECT MAX(action_date) FROM audit_log WHERE audit_log.user_id = user.user_id AND audit_log.action_type = 'LOGIN') AS last_login")
                        ->join('departments', 'departments.department_id = user.department_id', 'left')
                        ->join('roles', 'roles.role_id = user.role_id', 'inner');

        // Hide own account and dev accounts (role_id = 0) only if no search filters/keywords are applied
        if (empty($keyword) && empty($role_filter) && empty($dept_filter)) {
            $builder->where('user.user_id !=', $current_user_id)
                    ->where('user.role_id !=', 0);
        }

        if (!empty($keyword)) {
            $builder->groupStart()
                    ->like('user.username', $keyword)
                    ->orLike('user.first_name', $keyword)
                    ->orLike('user.last_name', $keyword)
                    ->orLike("CONCAT(user.first_name, ' ', user.last_name)", $keyword)
                    ->orLike('departments.department_name', $keyword)
                    ->groupEnd();
        }

        if (!empty($role_filter)) {
            $builder->where('roles.role_name', $role_filter);
        }

        if (!empty($dept_filter)) {
            $builder->where('user.department_id', $dept_filter);
        }

        return $builder->orderBy('user.last_name', 'ASC')
                       ->orderBy('user.first_name', 'ASC')
                       ->limit($limit)
                       ->find();
    }

    /**
     * Fetch user by their specific ID.
     */
    public function get_user_by_id($id)
    {
        return $this->select("user.*, user.user_id AS id, CONCAT(user.first_name, ' ', user.last_name) AS full_name, departments.department_name AS department_name, departments.department_code AS department_code, roles.role_name AS role_name, roles.role_name AS role, (user.status = 1) AS is_active")
                    ->join('departments', 'departments.department_id = user.department_id', 'left')
                    ->join('roles', 'roles.role_id = user.role_id', 'inner')
                    ->where('user.user_id', $id)
                    ->first();
    }

    /**
     * Retrieve a user by username for unique validation checks.
     */
    public function get_user_by_username($username)
    {
        return $this->where('username', $username)->first();
    }

    /**
     * Create a new user account with hashed password.
     */
    public function insert_user($data)
    {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_ARGON2ID);
        }
        return $this->insert($data);
    }

    /**
     * Update an existing user. Hashes the password if provided.
     */
    public function update_user($id, $data)
    {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_ARGON2ID);
        } else {
            unset($data['password']);
        }
        
        return $this->update($id, $data);
    }

    /**
     * Delete a user account from database.
     */
    public function delete_user($id)
    {
        return $this->delete($id);
    }
}
