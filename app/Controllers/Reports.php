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

        $from = trim((string) $this->request->getGet('from'));
        $to = trim((string) $this->request->getGet('to'));
        $from = preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) ? $from : date('Y-m-01');
        $to = preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) ? $to : date('Y-m-d');

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        if ($isAdmin) {
            $inventorySql = "
                SELECT
                    COUNT(*) AS batch_count,
                    COUNT(DISTINCT item_code) AS item_count,
                    COALESCE(SUM(quantity), 0) AS total_quantity,
                    COALESCE(SUM(quantity_on_hand), 0) AS quantity_on_hand,
                    SUM(CASE WHEN quantity_on_hand = 0 THEN 1 ELSE 0 END) AS out_of_stock,
                    SUM(CASE WHEN expiration_date < CURDATE() AND quantity_on_hand > 0 THEN 1 ELSE 0 END) AS expired,
                    SUM(CASE WHEN expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND quantity_on_hand > 0 THEN 1 ELSE 0 END) AS near_expiry
                FROM central_supply
                WHERE status = 1
            ";
            $topItemsSql = "
                SELECT item_code, item_name, unit, SUM(quantity_on_hand) AS quantity_on_hand
                FROM central_supply
                WHERE status = 1
                GROUP BY item_code, item_name, unit
                ORDER BY quantity_on_hand DESC, item_name ASC
                LIMIT 10
            ";
            $requestsSql = "
                SELECT request.request_status, COUNT(*) AS total
                FROM request
                WHERE request.status > 0 AND DATE(request.request_date) BETWEEN ? AND ?
                GROUP BY request.request_status
            ";
            $requestParams = [$from, $to];
        } else {
            $inventorySql = "
                SELECT
                    COUNT(*) AS batch_count,
                    COUNT(DISTINCT inventory.item_code) AS item_count,
                    COALESCE(SUM(department_supply.quantity_received), 0) AS total_quantity,
                    COALESCE(SUM(department_supply.quantity_on_hand), 0) AS quantity_on_hand,
                    SUM(CASE WHEN department_supply.quantity_on_hand = 0 THEN 1 ELSE 0 END) AS out_of_stock,
                    SUM(CASE WHEN inventory.expiration_date < CURDATE() AND department_supply.quantity_on_hand > 0 THEN 1 ELSE 0 END) AS expired,
                    SUM(CASE WHEN inventory.expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND department_supply.quantity_on_hand > 0 THEN 1 ELSE 0 END) AS near_expiry
                FROM inventory
                INNER JOIN supply ON supply.inventory_id = inventory.inventory_id
                INNER JOIN department_supply ON department_supply.department_supply_id = supply.department_supply_id
                WHERE inventory.status = 1 AND department_supply.department_id = ?
            ";
            $topItemsSql = "
                SELECT inventory.item_code, inventory.item_name, inventory.unit, SUM(department_supply.quantity_on_hand) AS quantity_on_hand
                FROM inventory
                INNER JOIN supply ON supply.inventory_id = inventory.inventory_id
                INNER JOIN department_supply ON department_supply.department_supply_id = supply.department_supply_id
                WHERE inventory.status = 1 AND department_supply.department_id = ?
                GROUP BY inventory.item_code, inventory.item_name, inventory.unit
                ORDER BY quantity_on_hand DESC, inventory.item_name ASC
                LIMIT 10
            ";
            $requestsSql = "
                SELECT request.request_status, COUNT(*) AS total
                FROM request
                INNER JOIN department_supply ON department_supply.department_supply_id = request.department_supply_id
                WHERE request.status > 0 AND department_supply.department_id = ? AND DATE(request.request_date) BETWEEN ? AND ?
                GROUP BY request.request_status
            ";
            $requestParams = [$deptId, $from, $to];
        }

        $inventoryParams = $isAdmin ? [] : [$deptId];
        $summary = $db->query($inventorySql, $inventoryParams)->getRowArray() ?: [];
        $topItems = $db->query($topItemsSql, $inventoryParams)->getResultArray();
        $requestRows = $db->query($requestsSql, $requestParams)->getResultArray();

        $requestSummary = [
            'pending' => 0,
            'served' => 0,
            'partial' => 0,
            'rejected' => 0,
            'cancelled' => 0,
        ];

        foreach ($requestRows as $row) {
            $status = (int) ($row['request_status'] ?? 0);
            $total = (int) ($row['total'] ?? 0);
            if ($status === 1) $requestSummary['pending'] += $total;
            if ($status === 2) $requestSummary['partial'] += $total;
            if ($status === 3) $requestSummary['served'] += $total;
            if ($status === 4) $requestSummary['rejected'] += $total;
            if ($status === 5) $requestSummary['cancelled'] += $total;
        }

        $data = [
            'title' => 'Reports',
            'from' => $from,
            'to' => $to,
            'summary' => $summary,
            'top_items' => $topItems,
            'request_summary' => $requestSummary,
            'report_scope' => $isAdmin ? 'Central Supply' : ($user['department_name'] ?? 'My Department'),
        ];

        return view('templates/header', $data)
             . view('reports', $data)
             . view('templates/footer');
    }
}
