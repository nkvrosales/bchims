<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplyRequestModel extends Model
{
    protected $table            = 'request';
    protected $primaryKey       = 'request_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['department_supply_id', 'quantity_requested', 'quantity_served', 'status', 'request_date', 'served_date', 'partial_date', 'cancelled_date', 'closed_date', 'user_id', 'notes'];

    protected $useTimestamps = false;

    /**
     * Fetch all requests with item, department, and user details joined.
     */
    public function get_requests($user_id = null, $department_id = null)
    {
        $builder = $this->select("
            request.*,
            request.request_id AS id,
            request.quantity_requested AS quantity,
            request.quantity_served AS served_quantity,
            request.request_date AS created_at,
            central_supply.central_supply_id AS central_supply_id,
            central_supply.item_name AS item_name,
            central_supply.item_code AS item_code,
            central_supply.quantity_on_hand AS item_current_stock,
            'pcs' AS item_unit,
            user.username AS requester_username,
            CONCAT(user.first_name, ' ', user.last_name) AS requester_full_name,
            departments.department_name AS department_name,
            departments.department_id AS department_id,
            user.user_id AS user_id
        ")
        ->join('department_supply', 'department_supply.department_supply_id = request.department_supply_id', 'left')
        ->join('departments', 'departments.department_id = department_supply.department_id', 'left')
        ->join('user', 'user.user_id = request.user_id', 'left')
        ->join('supply', 'supply.department_supply_id = department_supply.department_supply_id', 'left')
        ->join('central_supply', 'central_supply.central_supply_id = supply.central_supply_id', 'left');

        if ($department_id !== null) {
            $builder = $builder->where('department_supply.department_id', $department_id);
        }

        return $builder->groupBy('request.request_id')->orderBy('request.request_date', 'DESC')->findAll();
    }
}
