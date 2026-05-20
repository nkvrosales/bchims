<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartmentModel extends Model
{
    protected $table      = 'departments';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['name', 'code', 'description'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Fetch all hospital departments.
     */
    public function get_departments()
    {
        return $this->orderBy('name', 'ASC')->findAll();
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
        return $this->insert($data);
    }

    /**
     * Update an existing department.
     */
    public function update_department($id, $data)
    {
        return $this->update($id, $data);
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
