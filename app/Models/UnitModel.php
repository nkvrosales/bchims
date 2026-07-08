<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitModel extends Model
{
    protected $table      = 'unit';
    protected $primaryKey = 'unit_id';

    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = ['unit_name', 'unit_code', 'status'];
    protected $useTimestamps = false;

    public function search_units($search = '', $status_filter = '', $limit = 1000)
    {
        $builder = $this->orderBy('unit_name', 'ASC');

        if (empty($search) && $status_filter === '') {
            $builder = $builder->where('status', 1);
        }

        if (!empty($search)) {
            $builder = $builder->groupStart()
                               ->like('unit_name', $search)
                               ->orLike('unit_code', $search)
                               ->groupEnd();
        }

        if ($status_filter !== '') {
            $builder = $builder->where('status', (int)$status_filter);
        }

        if ($limit !== null && empty($search) && $status_filter === '') {
            $builder = $builder->limit($limit);
        }

        return $builder->findAll();
    }

    public function get_units()
    {
        return $this->where('status', 1)
                    ->orderBy('unit_name', 'ASC')
                    ->findAll();
    }
}
