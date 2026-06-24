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
}
