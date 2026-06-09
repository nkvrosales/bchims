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

    protected $allowedFields = ['inventory_id', 'item_code', 'item_name', 'batch_num', 'lot_num', 'expiration_date', 'manufacturing_date', 'unit', 'quantity', 'quantity_on_hand', 'category_id', 'source_id'];

    protected $useTimestamps = false;

    /**
     * Generate auto item code based on category code and current date
     * Format: CATEGORY-YYYY-MM-NNNN (e.g., MED-2026-06-0001)
     */
    public function generate_item_code($category_id)
    {
        $db = \Config\Database::connect();
        
        // Get category code
        $category = $db->table('category')->where('category_id', $category_id)->get()->getRowArray();
        if (!$category) {
            return null;
        }
        
        $categoryCode = strtoupper($category['category_code']);
        $currentDate = date('Y-m');
        
        // Find the last item code for this category and month across both tables
        $prefix = $categoryCode . '-' . $currentDate . '-';
        $csItem = $db->table('central_supply')
                      ->select('item_code')
                      ->like('item_code', $prefix, 'after')
                      ->orderBy('item_code', 'DESC')
                      ->limit(1)
                      ->get()
                      ->getRowArray();
        $invItem = $db->table('inventory')
                       ->select('item_code')
                       ->like('item_code', $prefix, 'after')
                       ->orderBy('item_code', 'DESC')
                       ->limit(1)
                       ->get()
                       ->getRowArray();

        $lastItem = null;
        if ($csItem && $invItem) {
            $lastItem = $csItem['item_code'] >= $invItem['item_code'] ? $csItem : $invItem;
        } elseif ($csItem) {
            $lastItem = $csItem;
        } elseif ($invItem) {
            $lastItem = $invItem;
        }

        $sequence = 1;
        if ($lastItem) {
            $lastSequence = (int)substr($lastItem['item_code'], strlen($prefix));
            $sequence = $lastSequence + 1;
        }
        
        // Format sequence as 4-digit number with leading zeros
        $sequenceFormatted = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        
        return $categoryCode . '-' . $currentDate . '-' . $sequenceFormatted;
    }

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
        $isAdmin = in_array(strtolower((string) $role), ['admin', 'administrator', 'dev'], true);

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

            return $builder->orderBy('central_supply.quantity = 0', 'ASC', false)
                           ->orderBy('central_supply.item_name', 'ASC')->get()->getResultArray();
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

            return $builder->orderBy('department_supply.quantity_on_hand = 0', 'ASC', false)
                           ->orderBy('inventory.item_name', 'ASC')->get()->getResultArray();
        }
    }
}
