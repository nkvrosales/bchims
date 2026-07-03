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

    protected $allowedFields = ['inventory_id', 'inventory_code', 'item_code', 'item_name', 'batch_num', 'lot_num', 'expiration_date', 'manufacturing_date', 'unit', 'quantity', 'quantity_on_hand', 'category_id', 'source_id', 'status', 'remarks'];

    protected $useTimestamps = false;

    /**
     * Generate auto inventory code based on category code and current date
     * Format: CATEGORY-YYYY-MM-NNNN (e.g., MED-2026-06-0001)
     */
    public function generate_inventory_code($category_id)
    {
        $db = \Config\Database::connect();
        
        // Get category code
        $category = $db->table('category')->where('category_id', $category_id)->get()->getRowArray();
        if (!$category) {
            return null;
        }
        
        $categoryCode = strtoupper($category['category_code']);
        $currentDate = date('Y-m');
        
        // Find the last inventory code for this category and month across both tables
        $prefix = $categoryCode . '-' . $currentDate . '-';
        $csItem = $db->table('central_supply')
                      ->select('inventory_code')
                      ->like('inventory_code', $prefix, 'after')
                      ->orderBy('inventory_code', 'DESC')
                      ->limit(1)
                      ->get()
                      ->getRowArray();
        $invItem = $db->table('inventory')
                       ->select('inventory_code')
                       ->like('inventory_code', $prefix, 'after')
                       ->orderBy('inventory_code', 'DESC')
                       ->limit(1)
                       ->get()
                       ->getRowArray();

        $lastItem = null;
        if ($csItem && $invItem) {
            $lastItem = $csItem['inventory_code'] >= $invItem['inventory_code'] ? $csItem : $invItem;
        } elseif ($csItem) {
            $lastItem = $csItem;
        } elseif ($invItem) {
            $lastItem = $invItem;
        }

        $sequence = 1;
        if ($lastItem) {
            $lastSequence = (int)substr($lastItem['inventory_code'], strlen($prefix));
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
     * Fetch list of inventory items based on search query, department_id, role, stock status, and category.
     */
    public function get_items($search = '', $role = 'admin', $department_id = null, $stock_status = '', $category_id = null, $limit = 1000)
    {
        $isAdmin = in_array(strtolower((string) $role), ['admin', 'administrator', 'dev'], true);

        if ($isAdmin) {
            $builder = $this->db->table('central_supply')
                                ->select('MAX(central_supply.central_supply_id) AS id')
                                ->select('MAX(central_supply.central_supply_id) AS central_supply_id')
                                ->select('central_supply.item_code')
                                ->select('central_supply.inventory_code')
                                ->select('central_supply.item_name')
                                ->select('MAX(central_supply.unit) AS unit')
                                ->select('SUM(central_supply.quantity) AS quantity')
                                ->select('SUM(central_supply.quantity) AS total_quantity')
                                ->select('SUM(central_supply.quantity_on_hand) AS quantity_on_hand')
                                ->select('MAX(central_supply.category_id) AS category_id')
                                ->select('MAX(central_supply.source_id) AS source_id')
                                ->select('MAX(central_supply.expiration_date) AS expiration_date')
                                ->select('MAX(central_supply.manufacturing_date) AS manufacturing_date')
                                ->select('MAX(central_supply.batch_num) AS batch_num')
                                ->select('MAX(central_supply.lot_num) AS lot_num')
                                ->select('MAX(central_supply.remarks) AS remarks')
                                ->select('category.category_code, category.category_description')
                                ->select('source.source_type, source.supplier_name')
                                ->join('category', 'category.category_id = central_supply.category_id', 'left')
                                ->join('source', 'source.source_id = central_supply.source_id', 'left');

            if (!empty($search)) {
                $builder = $builder->groupStart()
                                   ->like('central_supply.item_code', $search)
                                   ->orLike('central_supply.item_name', $search)
                                   ->orLike('central_supply.inventory_code', $search)
                                   ->groupEnd();
            }

            if (!empty($category_id)) {
                $builder = $builder->where('central_supply.category_id', (int)$category_id);
            }

            if (empty($search)) {
                $builder = $builder->where('central_supply.status', 1);
            }

            $builder = $builder->groupBy('central_supply.item_code, central_supply.item_name');

            if (!empty($stock_status)) {
                if ($stock_status === 'low_stock') {
                    $builder = $builder->having('SUM(central_supply.quantity_on_hand) <= SUM(central_supply.quantity) * 0.15 AND SUM(central_supply.quantity_on_hand) > 0', null, false);
                } elseif ($stock_status === 'out_of_stock') {
                    $builder = $builder->having('SUM(central_supply.quantity_on_hand) = 0', null, false);
                } elseif ($stock_status === 'in_stock') {
                    $builder = $builder->having('SUM(central_supply.quantity_on_hand) > SUM(central_supply.quantity) * 0.15', null, false);
                } elseif ($stock_status === 'expired') {
                    $builder = $builder->having('MAX(central_supply.expiration_date) < CURDATE() AND SUM(central_supply.quantity_on_hand) > 0', null, false);
                } elseif ($stock_status === 'near_expiry') {
                    $builder = $builder->having('MAX(central_supply.expiration_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND SUM(central_supply.quantity_on_hand) > 0', null, false);
                }
            }

            if ($limit !== null && empty($search) && empty($stock_status) && empty($category_id)) {
                $builder = $builder->limit($limit);
            }

            return $builder->orderBy('SUM(central_supply.quantity_on_hand) = 0', 'ASC', false)
                           ->orderBy('central_supply.item_name', 'ASC')->get()->getResultArray();
        } else {
            $builder = $this->db->table('inventory')
                                ->select('MAX(inventory.inventory_id) AS id')
                                ->select('MAX(inventory.inventory_id) AS inventory_id')
                                ->select('inventory.item_code')
                                ->select('inventory.inventory_code')
                                ->select('inventory.item_name')
                                ->select('MAX(inventory.unit) AS unit')
                                ->select('SUM(department_supply.quantity_received) AS total_quantity')
                                ->select('SUM(department_supply.quantity_received) AS quantity')
                                ->select('SUM(department_supply.quantity_on_hand) AS quantity_on_hand')
                                ->select('MAX(inventory.category_id) AS category_id')
                                ->select('MAX(inventory.expiration_date) AS expiration_date')
                                ->select('MAX(inventory.manufacturing_date) AS manufacturing_date')
                                ->select('MAX(inventory.batch_num) AS batch_num')
                                ->select('MAX(inventory.lot_num) AS lot_num')
                                ->select('MAX(inventory.remarks) AS remarks')
                                ->select('category.category_code, category.category_description')
                                ->select('source.source_type, source.supplier_name')
                                ->join('supply', 'supply.inventory_id = inventory.inventory_id', 'inner')
                                ->join('department_supply', 'department_supply.department_supply_id = supply.department_supply_id', 'inner')
                                ->join('category', 'category.category_id = inventory.category_id', 'left')
                                ->join('central_supply', 'central_supply.central_supply_id = supply.central_supply_id', 'left')
                                ->join('source', 'source.source_id = central_supply.source_id', 'left')
                                ->where('department_supply.department_id', $department_id);

            if (empty($search)) {
                $builder = $builder->where('inventory.status', 1);
            }

            if (!empty($search)) {
                $builder = $builder->groupStart()
                                   ->like('inventory.item_code', $search)
                                   ->orLike('inventory.item_name', $search)
                                   ->orLike('inventory.inventory_code', $search)
                                   ->groupEnd();
            }

            if (!empty($category_id)) {
                $builder = $builder->where('inventory.category_id', (int)$category_id);
            }

            $builder = $builder->groupBy('inventory.item_code, inventory.item_name');

            if (!empty($stock_status)) {
                if ($stock_status === 'low_stock') {
                    $builder = $builder->having('SUM(department_supply.quantity_on_hand) <= SUM(department_supply.quantity_received) * 0.15 AND SUM(department_supply.quantity_on_hand) > 0', null, false);
                } elseif ($stock_status === 'out_of_stock') {
                    $builder = $builder->having('SUM(department_supply.quantity_on_hand) = 0', null, false);
                } elseif ($stock_status === 'in_stock') {
                    $builder = $builder->having('SUM(department_supply.quantity_on_hand) > SUM(department_supply.quantity_received) * 0.15', null, false);
                } elseif ($stock_status === 'expired') {
                    $builder = $builder->having('MAX(inventory.expiration_date) < CURDATE() AND SUM(department_supply.quantity_on_hand) > 0', null, false);
                } elseif ($stock_status === 'near_expiry') {
                    $builder = $builder->having('MAX(inventory.expiration_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND SUM(department_supply.quantity_on_hand) > 0', null, false);
                }
            }

            if ($limit !== null && empty($search) && empty($stock_status) && empty($category_id)) {
                $builder = $builder->limit($limit);
            }

            return $builder->orderBy('SUM(department_supply.quantity_on_hand) = 0', 'ASC', false)
                           ->orderBy('inventory.item_name', 'ASC')->get()->getResultArray();
        }
    }

    /**
     * Fetch individual batches for the given item codes.
     */
    public function get_batches_by_item_codes(array $itemCodes, $isAdmin = true, $department_id = null)
    {
        if (empty($itemCodes)) {
            return [];
        }

        if ($isAdmin) {
            return $this->db->table('central_supply')
                            ->select('central_supply_id AS id, item_code, inventory_code, item_name, batch_num, lot_num, expiration_date, manufacturing_date, unit, quantity, quantity_on_hand, remarks, category_id')
                            ->whereIn('item_code', $itemCodes)
                            ->where('status', 1)
                            ->orderBy('item_code', 'ASC')
                            ->orderBy('expiration_date', 'ASC')
                            ->get()
                            ->getResultArray();
        } else {
            return $this->db->table('inventory')
                            ->select('inventory.inventory_id AS id, inventory.item_code, inventory.inventory_code, inventory.item_name, inventory.batch_num, inventory.lot_num, inventory.expiration_date, inventory.manufacturing_date, inventory.unit, department_supply.quantity_received AS quantity, department_supply.quantity_on_hand, inventory.remarks, inventory.category_id')
                            ->join('supply', 'supply.inventory_id = inventory.inventory_id', 'inner')
                            ->join('department_supply', 'department_supply.department_supply_id = supply.department_supply_id', 'inner')
                            ->whereIn('inventory.item_code', $itemCodes)
                            ->where('inventory.status', 1)
                            ->where('department_supply.department_id', $department_id)
                            ->orderBy('inventory.item_code', 'ASC')
                            ->orderBy('inventory.expiration_date', 'ASC')
                            ->get()
                            ->getResultArray();
        }
    }
}
