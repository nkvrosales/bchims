<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'user_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['first_name', 'last_name', 'username', 'email', 'password', 'role_id', 'department_id', 'account_status'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Verify user credentials and return user details if valid.
     */
    public function verify_user($username, $password)
    {
        $user = $this->select("users.*, CONCAT(users.first_name, ' ', users.last_name) AS full_name, departments.department_name AS department_name, roles.role_name AS role")
                     ->join('departments', 'departments.department_id = users.department_id', 'left')
                     ->join('roles', 'roles.role_id = users.role_id', 'inner')
                     ->where('users.username', $username)
                     ->where('users.account_status', 'Active')
                     ->first();

        if ($user) {
            if (password_verify($password, $user['password'])) {
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
        return $this->select("users.user_id AS id, users.username, users.last_name, users.first_name, CONCAT(users.first_name, ' ', users.last_name) AS full_name, roles.role_name AS role, users.account_status, users.created_at, users.department_id, departments.department_name AS department_name, departments.department_name AS department_code, (users.account_status = 'Active') AS is_active, (SELECT MAX(action_date) FROM audit_logs WHERE audit_logs.user_id = users.user_id AND audit_logs.action_type = 'LOGIN') AS last_login")
                    ->join('departments', 'departments.department_id = users.department_id', 'left')
                    ->join('roles', 'roles.role_id = users.role_id', 'inner')
                    ->orderBy('users.last_name', 'ASC')
                    ->orderBy('users.first_name', 'ASC')
                    ->findAll();
    }

    /**
     * Fetch user by their specific ID.
     */
    public function get_user_by_id($id)
    {
        return $this->select("users.*, users.user_id AS id, CONCAT(users.first_name, ' ', users.last_name) AS full_name, departments.department_name AS department_name, departments.department_name AS department_code, roles.role_name AS role_name, roles.role_name AS role, (users.account_status = 'Active') AS is_active")
                    ->join('departments', 'departments.department_id = users.department_id', 'left')
                    ->join('roles', 'roles.role_id = users.role_id', 'inner')
                    ->where('users.user_id', $id)
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
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        return $this->insert($data);
    }

    /**
     * Update an existing user. Hashes the password if provided.
     */
    public function update_user($id, $data)
    {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
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
