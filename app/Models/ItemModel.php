<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    protected $table      = 'inventory';
    protected $primaryKey = 'inventory_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['inventory_id', 'item_code', 'item_name', 'batch_num', 'lot_num', 'expiration_date', 'unit', 'quantity', 'quantity_on_hand', 'category_id'];

    protected $useTimestamps = false;

    /**
     * Dynamically target either central_supply or inventory table at runtime.
     */
    public function set_table($table_name)
    {
        $this->table = $table_name;
        if ($table_name === 'central_supply') {
            $this->primaryKey = 'central_supply_id';
        } else {
            $this->primaryKey = 'inventory_id';
        }
        return $this;
    }

    /**
     * Fetch list of inventory items based on search query, department_id, role, and stock status.
     */
    public function get_items($search = '', $role = 'admin', $department_id = null, $stock_status = '')
    {
        $isAdmin = in_array(strtolower((string) $role), ['admin', 'administrator'], true);

        if ($isAdmin) {
            $builder = $this->db->table('central_supply')
                                ->select('central_supply.*, central_supply.central_supply_id AS id, central_supply.central_supply_id AS central_supply_id, category.category_code, category.category_description, source.source_type, source.supplier_name')
                                ->join('category', 'category.category_id = central_supply.category_id', 'left')
                                ->join('source', 'source.source_id = central_supply.source_id', 'left');

            if (!empty($search)) {
                $builder = $builder->groupStart()
                                   ->like('central_supply.item_code', $search)
                                   ->orLike('central_supply.item_name', $search)
                                   ->groupEnd();
            }

            if (!empty($stock_status)) {
                if ($stock_status === 'low_stock') {
                    $builder = $builder->where('central_supply.quantity <= 10')
                                       ->where('central_supply.quantity > 0');
                } elseif ($stock_status === 'out_of_stock') {
                    $builder = $builder->where('central_supply.quantity', 0);
                } elseif ($stock_status === 'in_stock') {
                    $builder = $builder->where('central_supply.quantity > 10');
                }
            }

            return $builder->orderBy('central_supply.item_name', 'ASC')->get()->getResultArray();
        } else {
            $builder = $this->db->table('inventory')
                                ->select('inventory.*, inventory.inventory_id AS id, inventory.inventory_id AS inventory_id, department_supply.quantity_on_hand AS quantity, category.category_code, category.category_description, source.source_type, source.supplier_name')
                                ->join('supply', 'supply.inventory_id = inventory.inventory_id', 'inner')
                                ->join('department_supply', 'department_supply.department_supply_id = supply.department_supply_id', 'inner')
                                ->join('category', 'category.category_id = inventory.category_id', 'left')
                                ->join('central_supply', 'central_supply.central_supply_id = supply.central_supply_id', 'left')
                                ->join('source', 'source.source_id = central_supply.source_id', 'left')
                                ->where('department_supply.department_id', $department_id);

            if (!empty($search)) {
                $builder = $builder->groupStart()
                                   ->like('inventory.item_code', $search)
                                   ->orLike('inventory.item_name', $search)
                                   ->groupEnd();
            }

            if (!empty($stock_status)) {
                if ($stock_status === 'low_stock') {
                    $builder = $builder->where('department_supply.quantity_on_hand <= 10')
                                       ->where('department_supply.quantity_on_hand > 0');
                } elseif ($stock_status === 'out_of_stock') {
                    $builder = $builder->where('department_supply.quantity_on_hand', 0);
                } elseif ($stock_status === 'in_stock') {
                    $builder = $builder->where('department_supply.quantity_on_hand > 10');
                }
            }

            return $builder->orderBy('inventory.item_name', 'ASC')->get()->getResultArray();
        }
    }
}
