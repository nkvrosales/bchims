<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['username', 'password', 'full_name', 'role', 'department_id', 'is_active'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Verify user credentials and return user details if valid.
     */
    public function verify_user($username, $password)
    {
        $user = $this->select('users.*, departments.name AS department_name, departments.code AS department_code')
                     ->join('departments', 'departments.id = users.department_id', 'left')
                     ->where('users.username', $username)
                     ->where('users.is_active', 1)
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
        return $this->select('users.id, users.username, users.full_name, users.role, users.is_active, users.created_at, users.department_id, departments.name AS department_name, departments.code AS department_code')
                    ->join('departments', 'departments.id = users.department_id', 'left')
                    ->orderBy('users.full_name', 'ASC')
                    ->findAll();
    }

    /**
     * Fetch user by their specific ID.
     */
    public function get_user_by_id($id)
    {
        return $this->select('users.*, departments.name AS department_name, departments.code AS department_code')
                    ->join('departments', 'departments.id = users.department_id', 'left')
                    ->where('users.id', $id)
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
