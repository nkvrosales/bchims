<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplyRequestModel extends Model
{
    protected $table            = 'requests';
    protected $primaryKey       = 'request_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'central_supply_id', 'department_supply_id', 'quantity_requested', 'quantity_served', 'status', 'notes'];

    protected $useTimestamps = false;

    /**
     * Fetch all requests with item, department, and user details joined.
     */
    public function get_requests($user_id = null, $department_id = null)
    {
        $builder = $this->select("
            requests.*,
            requests.request_id AS id,
            requests.quantity_requested AS quantity,
            requests.quantity_served AS served_quantity,
            requests.created_at AS created_at,
            central_supply.item_name AS item_name,
            central_supply.item_code AS item_code,
            central_supply.quantity_on_hand AS item_current_stock,
            'pcs' AS item_unit,
            users.username AS requester_username,
            CONCAT(users.first_name, ' ', users.last_name) AS requester_full_name,
            departments.department_name AS department_name,
            departments.department_name AS department_code,
            departments.department_id AS department_id
        ")
        ->join('central_supply', 'central_supply.central_supply_id = requests.central_supply_id', 'inner')
        ->join('users', 'users.user_id = requests.user_id', 'inner')
        ->join('departments', 'departments.department_id = users.department_id', 'left');

        if ($user_id !== null) {
            $builder = $builder->where('requests.user_id', $user_id);
        } elseif ($department_id !== null) {
            $builder = $builder->where('users.department_id', $department_id);
        }

        return $builder->orderBy('requests.created_at', 'DESC')->findAll();
    }
}
