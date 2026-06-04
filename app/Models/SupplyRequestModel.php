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
    protected $allowedFields    = ['user_id', 'central_supply_id', 'department_supply_id', 'quantity_requested', 'quantity_served', 'status', 'notes', 'request_date', 'served_date', 'partial_date', 'cancelled_date', 'closed_date'];

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
            central_supply.item_name AS item_name,
            central_supply.item_code AS item_code,
            central_supply.quantity_on_hand AS item_current_stock,
            'pcs' AS item_unit,
            user.username AS requester_username,
            CONCAT(user.first_name, ' ', user.last_name) AS requester_full_name,
            departments.department_name AS department_name,
            departments.department_name AS department_code,
            departments.department_id AS department_id
        ")
        ->join('central_supply', 'central_supply.central_supply_id = request.central_supply_id', 'inner')
        ->join('user', 'user.user_id = request.user_id', 'inner')
        ->join('departments', 'departments.department_id = user.department_id', 'left');

        if ($user_id !== null) {
            $builder = $builder->where('request.user_id', $user_id);
        } elseif ($department_id !== null) {
            $builder = $builder->where('user.department_id', $department_id);
        }

        return $builder->orderBy('request.request_date', 'DESC')->findAll();
    }
}
