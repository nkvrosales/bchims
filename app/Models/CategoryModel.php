<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table      = 'category';
    protected $primaryKey = 'category_id';

    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = ['category_code', 'category_name', 'status'];
    protected $useTimestamps = false;

    /**
     * Get categories with search and optional limit (limit skipped when searching).
     */
    public function search_categories($search = '', $status_filter = '', $limit = 1000)
    {
        $builder = $this->orderBy('category_code', 'ASC');

        if (empty($search) && $status_filter === '') {
            $builder = $builder->where('status', 1);
        }

        if (!empty($search)) {
            $builder = $builder->groupStart()
                               ->like('category_name', $search)
                               ->orLike('category_code', $search)
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
}
