<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartmentModel extends Model
{
    protected $table      = 'departments';
    protected $primaryKey = 'department_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['department_name'];

    protected $useTimestamps = false;

    /**
     * Override find to return mapped fields.
     */
    public function find($id = null)
    {
        return $this->select("department_id AS id, department_name AS name, created_at")
                    ->where('department_id', $id)
                    ->first();
    }

    /**
     * Retrieve all hospital departments.
     */
    public function get_departments()
    {
        return $this->select("department_id AS id, department_name AS name, created_at")
                    ->orderBy('department_name', 'ASC')
                    ->findAll();
    }

    /**
     * Get department by specific ID.
     */
    public function get_department_by_id($id)
    {
        return $this->find($id);
    }

    /**
     * Create/Insert a new department record.
     */
    public function insert_department($data)
    {
        $insert_data = [
            'department_name' => $data['name'] ?? $data['department_name'] ?? ''
        ];
        return $this->insert($insert_data);
    }

    /**
     * Update an existing department.
     */
    public function update_department($id, $data)
    {
        $update_data = [];
        if (isset($data['name'])) {
            $update_data['department_name'] = $data['name'];
        }
        return $this->update($id, $update_data);
    }

    /**
     * Delete a department.
     */
    public function delete_department($id)
    {
        return $this->delete($id);
    }

    /**
     * Get total department count.
     */
    public function get_department_count()
    {
        return $this->countAllResults();
    }
}
