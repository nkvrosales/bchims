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

    protected $allowedFields = ['category_code', 'category_description', 'status'];
    protected $useTimestamps = false;

    /**
     * Get categories with search and optional limit (limit skipped when searching).
     */
    public function search_categories($search = '', $limit = 1000)
    {
        $builder = $this->orderBy('category_code', 'ASC');

        if (empty($search)) {
            $builder = $builder->where('status', 1);
        }

        if (!empty($search)) {
            $builder = $builder->groupStart()
                               ->like('category_description', $search)
                               ->orLike('category_code', $search)
                               ->groupEnd();
        }

        if ($limit !== null && empty($search)) {
            $builder = $builder->limit($limit);
        }

        return $builder->findAll();
    }
}
