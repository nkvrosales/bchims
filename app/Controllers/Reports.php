<?php

namespace App\Controllers;

use App\Models\UserModel;

class Reports extends BaseController
{
    protected function checkAuth()
    {
        if (!session()->get('logged_in')) {
            session()->setFlashdata('session_expired', 'Your session has expired due to inactivity. Please log in again.');
            return redirect()->to('auth/login');
        }

        $userModel = new UserModel();
        $user = $userModel->get_user_by_id(session()->get('user_id'));
        if (empty($user)) {
            session()->destroy();
            return redirect()->to('auth/login');
        }

        return null;
    }

    public function index()
    {
        if ($res = $this->checkAuth()) return $res;

        $db = \Config\Database::connect();
        $userModel = new UserModel();
        $user = $userModel->get_user_by_id(session()->get('user_id'));
        $isAdmin = is_admin_role();
        $deptId = $user['department_id'] ?? null;

        $monthStart = date('Y-m-01');
        $monthEnd   = date('Y-m-t');

        if ($isAdmin) {
            $nearExpirySql = "
                SELECT item_code, item_name, expiration_date, quantity_on_hand
                FROM central_supply
                WHERE status = 1
                  AND quantity_on_hand > 0
                  AND expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                ORDER BY expiration_date ASC
            ";
            $pendingRequestsSql = "
                SELECT DISTINCT r.request_id, r.request_date, r.quantity_requested, r.quantity_served,
                       r.request_status, r.notes,
                       CONCAT(u.first_name, ' ', u.last_name) AS requester_full_name,
                       d.department_name,
                       cs.item_name, s.unit AS item_unit
                FROM request r
                INNER JOIN user u ON u.user_id = r.user_id
                INNER JOIN department_supply ds ON ds.department_supply_id = r.department_supply_id
                INNER JOIN departments d ON d.department_id = ds.department_id
                INNER JOIN supply s ON s.request_id = r.request_id
                INNER JOIN central_supply cs ON cs.central_supply_id = s.central_supply_id
                WHERE r.status > 0 AND r.request_status = 1
                ORDER BY r.request_date DESC
            ";
            $partialRequestsSql = "
                SELECT DISTINCT r.request_id, r.request_date, r.quantity_requested, r.quantity_served,
                       r.request_status, r.notes,
                       CONCAT(u.first_name, ' ', u.last_name) AS requester_full_name,
                       d.department_name,
                       cs.item_name, s.unit AS item_unit
                FROM request r
                INNER JOIN user u ON u.user_id = r.user_id
                INNER JOIN department_supply ds ON ds.department_supply_id = r.department_supply_id
                INNER JOIN departments d ON d.department_id = ds.department_id
                INNER JOIN supply s ON s.request_id = r.request_id
                INNER JOIN central_supply cs ON cs.central_supply_id = s.central_supply_id
                WHERE r.status > 0 AND r.request_status = 2
                ORDER BY r.request_date DESC
            ";
            $arrivedSql = "
                SELECT item_code, item_name, unit, quantity_on_hand, created_at
                FROM central_supply
                WHERE status = 1 AND created_at >= ? AND created_at <= ?
                ORDER BY created_at DESC
            ";
            $arrivedParams = [$monthStart, $monthEnd];
        } else {
            $nearExpirySql = "
                SELECT i.item_code, i.item_name, i.expiration_date, ds.quantity_on_hand
                FROM inventory i
                INNER JOIN supply s ON s.inventory_id = i.inventory_id
                INNER JOIN department_supply ds ON ds.department_supply_id = s.department_supply_id
                WHERE i.status = 1
                  AND ds.department_id = ?
                  AND ds.quantity_on_hand > 0
                  AND i.expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                ORDER BY i.expiration_date ASC
            ";
            $pendingRequestsSql = "
                SELECT DISTINCT r.request_id, r.request_date, r.quantity_requested, r.quantity_served,
                       r.request_status, r.notes,
                       CONCAT(u.first_name, ' ', u.last_name) AS requester_full_name,
                       d.department_name,
                       cs.item_name, s.unit AS item_unit
                FROM request r
                INNER JOIN user u ON u.user_id = r.user_id
                INNER JOIN department_supply ds ON ds.department_supply_id = r.department_supply_id
                INNER JOIN departments d ON d.department_id = ds.department_id
                INNER JOIN supply s ON s.request_id = r.request_id
                INNER JOIN central_supply cs ON cs.central_supply_id = s.central_supply_id
                WHERE r.status > 0 AND r.request_status = 1 AND ds.department_id = ?
                ORDER BY r.request_date DESC
            ";
            $partialRequestsSql = "
                SELECT DISTINCT r.request_id, r.request_date, r.quantity_requested, r.quantity_served,
                       r.request_status, r.notes,
                       CONCAT(u.first_name, ' ', u.last_name) AS requester_full_name,
                       d.department_name,
                       cs.item_name, s.unit AS item_unit
                FROM request r
                INNER JOIN user u ON u.user_id = r.user_id
                INNER JOIN department_supply ds ON ds.department_supply_id = r.department_supply_id
                INNER JOIN departments d ON d.department_id = ds.department_id
                INNER JOIN supply s ON s.request_id = r.request_id
                INNER JOIN central_supply cs ON cs.central_supply_id = s.central_supply_id
                WHERE r.status > 0 AND r.request_status = 2 AND ds.department_id = ?
                ORDER BY r.request_date DESC
            ";
            $arrivedSql = "
                SELECT i.item_code, i.item_name, i.unit, ds.quantity_on_hand, i.created_at
                FROM inventory i
                INNER JOIN supply s ON s.inventory_id = i.inventory_id
                INNER JOIN department_supply ds ON ds.department_supply_id = s.department_supply_id
                WHERE i.status = 1 AND ds.department_id = ? AND i.created_at >= ? AND i.created_at <= ?
                ORDER BY i.created_at DESC
            ";
            $arrivedParams = [$deptId, $monthStart, $monthEnd];
        }

        $nearExpiryParams = $isAdmin ? [] : [$deptId];
        $nearExpiryItems = $db->query($nearExpirySql, $nearExpiryParams)->getResultArray();

        $pendingParams = $isAdmin ? [] : [$deptId];
        $pendingRequests = $db->query($pendingRequestsSql, $pendingParams)->getResultArray();

        $partialParams = $isAdmin ? [] : [$deptId];
        $partialRequests = $db->query($partialRequestsSql, $partialParams)->getResultArray();

        $arrivedItems = $db->query($arrivedSql, $arrivedParams)->getResultArray();

        $data = [
            'title'             => 'Reports',
            'pending_requests'  => $pendingRequests,
            'partial_requests'  => $partialRequests,
            'near_expiry_items' => $nearExpiryItems,
            'arrived_items'     => $arrivedItems,
        ];

        return view('templates/header', $data)
             . view('reports', $data)
             . view('templates/footer');
    }
}
