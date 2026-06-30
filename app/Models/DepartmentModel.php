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

    protected $allowedFields = ['department_name', 'department_code', 'status'];

    protected $useTimestamps = false;

    /**
     * Override find to return mapped fields.
     */
    public function find($id = null)
    {
        return $this->select("department_id AS id, department_name AS name, department_code AS code, status, NULL AS created_at")
                    ->where('department_id', $id)
                    ->first();
    }

    /**
     * Retrieve all hospital departments.
     */
    public function get_departments()
    {
        return $this->select("department_id AS id, department_name AS name, department_code AS code, status, NULL AS created_at")
                    ->where('status', 1)
                    ->orderBy('department_name', 'ASC')
                    ->findAll();
    }

    /**
     * Get departments with search, filter, and optional limit (limit skipped when searching/filtering).
     */
    public function search_departments($search = '', $limit = 1000)
    {
        $builder = $this->select("department_id AS id, department_name AS name, department_code AS code, status, NULL AS created_at")
                        ->orderBy('department_name', 'ASC');

        if (empty($search)) {
            $builder = $builder->where('status', 1);
        }

        if (!empty($search)) {
            $builder = $builder->groupStart()
                               ->like('department_name', $search)
                               ->orLike('department_code', $search)
                               ->groupEnd();
        }

        if ($limit !== null && empty($search)) {
            $builder = $builder->limit($limit);
        }

        return $builder->findAll();
    }

    /**
     * Get all departments including inactive (for admin restore view).
     */
    public function get_all_departments()
    {
        return $this->select("department_id AS id, department_name AS name, department_code AS code, status, NULL AS created_at")
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
            'department_name' => $data['name'] ?? $data['department_name'] ?? '',
            'department_code' => $data['code'] ?? $data['department_code'] ?? null
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
        if (isset($data['code'])) {
            $update_data['department_code'] = $data['code'];
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
