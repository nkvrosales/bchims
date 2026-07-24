<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    /** Maximum number of batches displayed in a Manage Item modal. */
    public const MANAGE_BATCH_LIMIT = 1000;

    protected $table      = 'inventory';
    protected $primaryKey = 'inventory_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['inventory_id', 'inventory_code', 'item_code', 'item_name', 'batch_num', 'lot_num', 'expiration_date', 'manufacturing_date', 'unit', 'quantity', 'quantity_on_hand', 'category_id', 'supplier_id', 'status', 'remarks'];

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
                                ->select('(SELECT cs2.inventory_code FROM central_supply cs2 WHERE cs2.item_code = central_supply.item_code ORDER BY cs2.central_supply_id DESC LIMIT 1) AS inventory_code')
                                // An item code represents one item in the summary table.
                                // Use a stable display name so name variations in individual
                                // batches do not create duplicate rows.
                                ->select('MIN(central_supply.item_name) AS item_name')
                                ->select('MAX(central_supply.unit) AS unit')
                                ->select('SUM(CASE WHEN central_supply.expiration_date IS NULL OR central_supply.expiration_date >= CURDATE() THEN central_supply.quantity ELSE 0 END) AS quantity')
                                ->select('SUM(CASE WHEN central_supply.expiration_date IS NULL OR central_supply.expiration_date >= CURDATE() THEN central_supply.quantity ELSE 0 END) AS total_quantity')
                                // Expired batches remain visible in batch details, but never count as available stock.
                                ->select('SUM(CASE WHEN central_supply.expiration_date IS NULL OR central_supply.expiration_date >= CURDATE() THEN central_supply.quantity_on_hand ELSE 0 END) AS quantity_on_hand')
                                // A request may be fulfilled by multiple FEFO batches.
                                // Derive consumption from each batch's own balance so the
                                // whole request is not attributed to the first batch.
                                ->select('SUM(GREATEST(central_supply.quantity - central_supply.quantity_on_hand, 0)) AS quantity_served')
                                ->select('MAX(central_supply.category_id) AS category_id')
                                ->select('MAX(central_supply.supplier_id) AS supplier_id')
                                ->select('MAX(central_supply.expiration_date) AS expiration_date')
                                ->select('MAX(central_supply.manufacturing_date) AS manufacturing_date')
                                ->select('MAX(central_supply.batch_num) AS batch_num')
                                ->select('MAX(central_supply.lot_num) AS lot_num')
                                ->select('MAX(central_supply.remarks) AS remarks')
                                ->select('category.category_code, category.category_name')
                                ->select('supplier.supplier_type, supplier.supplier_name')
                                ->join('category', 'category.category_id = central_supply.category_id', 'left')
                                ->join('supplier', 'supplier.supplier_id = central_supply.supplier_id', 'left');

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

            $builder = $builder->groupBy('central_supply.item_code');

            if (!empty($stock_status)) {
                // Usable stock excludes expired batches (same as the quantity_on_hand select above).
                $usableQoh = 'SUM(CASE WHEN central_supply.expiration_date IS NULL OR central_supply.expiration_date >= CURDATE() THEN central_supply.quantity_on_hand ELSE 0 END)';
                $usableQty = 'SUM(CASE WHEN central_supply.expiration_date IS NULL OR central_supply.expiration_date >= CURDATE() THEN central_supply.quantity ELSE 0 END)';
                $totalQoh  = 'SUM(central_supply.quantity_on_hand)';

                if ($stock_status === 'low_stock') {
                    // Low stock among usable inventory only.
                    $builder = $builder->having("{$usableQoh} <= {$usableQty} * 0.15 AND {$usableQoh} > 0", null, false);
                } elseif ($stock_status === 'out_of_stock') {
                    // Truly empty: no remaining units on any batch (expired leftovers are "Expired", not OOS).
                    $builder = $builder->having("{$totalQoh} <= 0", null, false);
                } elseif ($stock_status === 'in_stock') {
                    $builder = $builder->having("{$usableQoh} > {$usableQty} * 0.15", null, false);
                } elseif ($stock_status === 'expired') {
                    // Has remaining stock, and every batch with stock is past its expiration date.
                    $builder = $builder->having(
                        "{$totalQoh} > 0 AND SUM(CASE WHEN central_supply.quantity_on_hand > 0 AND (central_supply.expiration_date IS NULL OR central_supply.expiration_date >= CURDATE()) THEN 1 ELSE 0 END) = 0",
                        null,
                        false
                    );
                } elseif ($stock_status === 'near_expiry') {
                    // Has usable stock, and every batch with remaining stock expires within 30 days
                    // (no undated stock, no stock beyond the 30-day window). Matches dashboard KPI.
                    $builder = $builder->having(
                        "{$totalQoh} > 0"
                        . " AND SUM(CASE WHEN central_supply.quantity_on_hand > 0 AND (central_supply.expiration_date IS NULL OR central_supply.expiration_date >= CURDATE()) THEN 1 ELSE 0 END) > 0"
                        . " AND SUM(CASE WHEN central_supply.quantity_on_hand > 0 AND central_supply.expiration_date IS NOT NULL AND central_supply.expiration_date > DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) = 0"
                        . " AND SUM(CASE WHEN central_supply.quantity_on_hand > 0 AND central_supply.expiration_date IS NULL THEN 1 ELSE 0 END) = 0",
                        null,
                        false
                    );
                }
            }

            if ($limit !== null && empty($search) && empty($stock_status) && empty($category_id)) {
                $builder = $builder->limit($limit);
            }

            return $builder->orderBy('SUM(CASE WHEN central_supply.expiration_date IS NULL OR central_supply.expiration_date >= CURDATE() THEN central_supply.quantity_on_hand ELSE 0 END) = 0', 'ASC', false)
                           ->orderBy('MIN(central_supply.item_name)', 'ASC', false)->get()->getResultArray();
        } else {
            $builder = $this->db->table('inventory')
                                ->select('MAX(inventory.inventory_id) AS id')
                                ->select('MAX(inventory.inventory_id) AS inventory_id')
                                ->select('inventory.item_code')
                                ->select('(SELECT inv2.inventory_code FROM inventory inv2 WHERE inv2.item_code = inventory.item_code ORDER BY inv2.inventory_id DESC LIMIT 1) AS inventory_code')
                                // Keep one table row for each item code, even when older
                                // inventory batches use a slightly different item name.
                                ->select('MIN(inventory.item_name) AS item_name')
                                ->select('MAX(inventory.unit) AS unit')
                                ->select('SUM(CASE WHEN inventory.expiration_date IS NULL OR inventory.expiration_date >= CURDATE() THEN department_supply.quantity_received ELSE 0 END) AS total_quantity')
                                ->select('SUM(CASE WHEN inventory.expiration_date IS NULL OR inventory.expiration_date >= CURDATE() THEN department_supply.quantity_received ELSE 0 END) AS quantity')
                                // Expired batches remain visible in batch details, but never count as available stock.
                                ->select('SUM(CASE WHEN inventory.expiration_date IS NULL OR inventory.expiration_date >= CURDATE() THEN department_supply.quantity_on_hand ELSE 0 END) AS quantity_on_hand')
                                ->select('(SELECT COALESCE(SUM(r.quantity_served), 0) FROM request r JOIN supply s ON s.department_supply_id = r.department_supply_id JOIN inventory inv2 ON inv2.inventory_id = s.inventory_id WHERE inv2.item_code = inventory.item_code AND r.request_status IN (2, 3)) AS quantity_served')
                                ->select('MAX(inventory.category_id) AS category_id')
                                ->select('MAX(inventory.expiration_date) AS expiration_date')
                                ->select('MAX(inventory.manufacturing_date) AS manufacturing_date')
                                ->select('MAX(inventory.batch_num) AS batch_num')
                                ->select('MAX(inventory.lot_num) AS lot_num')
                                ->select('MAX(inventory.remarks) AS remarks')
                                ->select('category.category_code, category.category_name')
                                ->select('supplier.supplier_type, supplier.supplier_name')
                                ->join('supply', 'supply.inventory_id = inventory.inventory_id', 'inner')
                                ->join('department_supply', 'department_supply.department_supply_id = supply.department_supply_id', 'inner')
                                ->join('category', 'category.category_id = inventory.category_id', 'left')
                                ->join('central_supply', 'central_supply.central_supply_id = supply.central_supply_id', 'left')
                                ->join('supplier', 'supplier.supplier_id = central_supply.supplier_id', 'left')
                                ->where('department_supply.department_id', $department_id)
                                ->where('department_supply.quantity_received >', 0);

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

            $builder = $builder->groupBy('inventory.item_code');

            if (!empty($stock_status)) {
                // Usable stock excludes expired batches (same as the quantity_on_hand select above).
                $usableQoh = 'SUM(CASE WHEN inventory.expiration_date IS NULL OR inventory.expiration_date >= CURDATE() THEN department_supply.quantity_on_hand ELSE 0 END)';
                $usableQty = 'SUM(CASE WHEN inventory.expiration_date IS NULL OR inventory.expiration_date >= CURDATE() THEN department_supply.quantity_received ELSE 0 END)';
                $totalQoh  = 'SUM(department_supply.quantity_on_hand)';

                if ($stock_status === 'low_stock') {
                    // Low stock among usable inventory only.
                    $builder = $builder->having("{$usableQoh} <= {$usableQty} * 0.15 AND {$usableQoh} > 0", null, false);
                } elseif ($stock_status === 'out_of_stock') {
                    // Truly empty: no remaining units on any batch (expired leftovers are "Expired", not OOS).
                    $builder = $builder->having("{$totalQoh} <= 0", null, false);
                } elseif ($stock_status === 'in_stock') {
                    $builder = $builder->having("{$usableQoh} > {$usableQty} * 0.15", null, false);
                } elseif ($stock_status === 'expired') {
                    // Has remaining stock, and every batch with stock is past its expiration date.
                    $builder = $builder->having(
                        "{$totalQoh} > 0 AND SUM(CASE WHEN department_supply.quantity_on_hand > 0 AND (inventory.expiration_date IS NULL OR inventory.expiration_date >= CURDATE()) THEN 1 ELSE 0 END) = 0",
                        null,
                        false
                    );
                } elseif ($stock_status === 'near_expiry') {
                    // Has usable stock, and every batch with remaining stock expires within 30 days
                    // (no undated stock, no stock beyond the 30-day window). Matches dashboard KPI.
                    $builder = $builder->having(
                        "{$totalQoh} > 0"
                        . " AND SUM(CASE WHEN department_supply.quantity_on_hand > 0 AND (inventory.expiration_date IS NULL OR inventory.expiration_date >= CURDATE()) THEN 1 ELSE 0 END) > 0"
                        . " AND SUM(CASE WHEN department_supply.quantity_on_hand > 0 AND inventory.expiration_date IS NOT NULL AND inventory.expiration_date > DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) = 0"
                        . " AND SUM(CASE WHEN department_supply.quantity_on_hand > 0 AND inventory.expiration_date IS NULL THEN 1 ELSE 0 END) = 0",
                        null,
                        false
                    );
                }
            }

            if ($limit !== null && empty($search) && empty($stock_status) && empty($category_id)) {
                $builder = $builder->limit($limit);
            }

            return $builder->orderBy('SUM(CASE WHEN inventory.expiration_date IS NULL OR inventory.expiration_date >= CURDATE() THEN department_supply.quantity_on_hand ELSE 0 END) = 0', 'ASC', false)
                           ->orderBy('MIN(inventory.item_name)', 'ASC', false)->get()->getResultArray();
        }
    }

    /**
     * Fetch individual batches for the given item codes.
     */
    public function get_batches_by_item_codes(array $itemCodes, $isAdmin = true, $department_id = null, $limit = self::MANAGE_BATCH_LIMIT)
    {
        if (empty($itemCodes)) {
            return [];
        }

        if ($isAdmin) {
            $builder = $this->db->table('central_supply')
                            ->select('central_supply_id AS id, central_supply.item_code, central_supply.inventory_code, central_supply.item_name, central_supply.batch_num, central_supply.lot_num, central_supply.expiration_date, central_supply.manufacturing_date, central_supply.unit, central_supply.quantity, central_supply.quantity_on_hand, central_supply.remarks, central_supply.category_id, central_supply.status, supplier.supplier_type, supplier.supplier_name')
                            // This is the amount consumed from this specific batch, not
                            // the total quantity served by a request that may span batches.
                            ->select('GREATEST(central_supply.quantity - central_supply.quantity_on_hand, 0) AS quantity_served')
                            ->join('supplier', 'supplier.supplier_id = central_supply.supplier_id', 'left')
                            ->whereIn('central_supply.item_code', $itemCodes)
                            ->orderBy('central_supply.item_code', 'ASC')
                            ->orderBy('central_supply.expiration_date', 'ASC');

            if ($limit !== null) {
                $builder->limit($limit);
            }

            return $builder->get()->getResultArray();
        } else {
            $builder = $this->db->table('inventory')
                            ->select('inventory.inventory_id AS id, inventory.item_code, inventory.inventory_code, inventory.item_name, inventory.batch_num, inventory.lot_num, inventory.expiration_date, inventory.manufacturing_date, inventory.unit, SUM(department_supply.quantity_received) AS quantity, SUM(department_supply.quantity_on_hand) AS quantity_on_hand, SUM(department_supply.quantity_used) AS quantity_used, inventory.remarks, inventory.category_id, inventory.status, MAX(supplier.supplier_type) AS supplier_type, MAX(supplier.supplier_name) AS supplier_name')
                            ->select('(SELECT COALESCE(SUM(r.quantity_served), 0) FROM request r JOIN supply s ON s.department_supply_id = r.department_supply_id WHERE s.inventory_id = inventory.inventory_id AND r.request_status IN (2, 3)) AS quantity_served')
                            ->join('supply', 'supply.inventory_id = inventory.inventory_id', 'inner')
                            ->join('department_supply', 'department_supply.department_supply_id = supply.department_supply_id', 'inner')
                            ->join('central_supply', 'central_supply.central_supply_id = supply.central_supply_id', 'left')
                            ->join('supplier', 'supplier.supplier_id = central_supply.supplier_id', 'left')
                            ->whereIn('inventory.item_code', $itemCodes)
                            ->where('department_supply.department_id', $department_id)
                            ->groupBy('inventory.inventory_id')
                            ->orderBy('inventory.item_code', 'ASC')
                            ->orderBy('inventory.expiration_date', 'ASC');

            if ($limit !== null) {
                $builder->limit($limit);
            }

            return $builder->get()->getResultArray();
        }
    }
}
