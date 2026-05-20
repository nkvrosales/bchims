<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    protected $table      = 'items';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['item_code', 'name', 'description', 'unit', 'department', 'quantity', 'min_stock', 'last_updated_by'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Fetch list of inventory items based on search query, department, and stock status.
     */
    public function get_items($search = '', $department = '', $stock_status = '')
    {
        $builder = $this;

        if (!empty($search)) {
            $builder = $builder->groupStart()
                               ->like('item_code', $search)
                               ->orLike('name', $search)
                               ->orLike('description', $search)
                               ->groupEnd();
        }

        if (!empty($department)) {
            $builder = $builder->where('department', $department);
        }

        if (!empty($stock_status)) {
            if ($stock_status === 'low_stock') {
                $builder = $builder->where('quantity <= min_stock')
                                   ->where('quantity > 0');
            } elseif ($stock_status === 'out_of_stock') {
                $builder = $builder->where('quantity', 0);
            } elseif ($stock_status === 'in_stock') {
                $builder = $builder->where('quantity > min_stock');
            }
        }

        return $builder->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Retrieve a single item by its ID.
     */
    public function get_item_by_id($id)
    {
        return $this->find($id);
    }

    /**
     * Retrieve a single item by its unique Item Code.
     */
    public function get_item_by_code($item_code)
    {
        return $this->where('item_code', $item_code)->first();
    }

    /**
     * Insert a new inventory item.
     */
    public function insert_item($data)
    {
        return $this->insert($data);
    }

    /**
     * Update an existing inventory item.
     */
    public function update_item($id, $data)
    {
        return $this->update($id, $data);
    }

    /**
     * Delete an inventory item.
     */
    public function delete_item($id)
    {
        return $this->delete($id);
    }

    /**
     * Get aggregate statistics for inventory items.
     */
    public function get_inventory_stats()
    {
        $total_items = $this->countAllResults();

        $low_stock = $this->where('quantity <= min_stock')
                          ->where('quantity > 0')
                          ->countAllResults();

        $out_of_stock = $this->where('quantity', 0)
                             ->countAllResults();

        $departments = $this->select('department, COUNT(*) as count, SUM(quantity) as total_qty')
                            ->groupBy('department')
                            ->findAll();

        $dept_stats = array(
            'LAB' => array('count' => 0, 'qty' => 0),
            'PHARMA' => array('count' => 0, 'qty' => 0),
            'SUPPLIES' => array('count' => 0, 'qty' => 0),
            'OR/DR COMPLEX' => array('count' => 0, 'qty' => 0)
        );

        foreach ($departments as $dept) {
            if (isset($dept_stats[$dept['department']])) {
                $dept_stats[$dept['department']] = array(
                    'count' => (int)$dept['count'],
                    'qty'   => (int)$dept['total_qty']
                );
            }
        }

        return array(
            'total_items'  => $total_items,
            'low_stock'    => $low_stock,
            'out_of_stock' => $out_of_stock,
            'departments'  => $dept_stats
        );
    }
}
