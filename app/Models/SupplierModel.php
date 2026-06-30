<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table      = 'source';
    protected $primaryKey = 'source_id';

    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = ['source_type', 'supplier_name', 'contact_person', 'contact_number', 'address', 'status'];
    protected $useTimestamps = false;

    /**
     * Get suppliers with search, filter, and optional limit (limit skipped when filtering).
     */
    public function search_suppliers($search = '', $type_filter = '', $limit = 1000)
    {
        $builder = $this->orderBy('supplier_name', 'ASC');

        if (empty($search) && empty($type_filter)) {
            $builder = $builder->where('status', 1);
        }

        if (!empty($search)) {
            $builder = $builder->groupStart()
                               ->like('supplier_name', $search)
                               ->orLike('contact_person', $search)
                               ->groupEnd();
        }

        if (!empty($type_filter)) {
            $builder = $builder->where('source_type', $type_filter);
        }

        if ($limit !== null && empty($search) && empty($type_filter)) {
            $builder = $builder->limit($limit);
        }

        return $builder->findAll();
    }
}
