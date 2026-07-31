<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AuditModel;

class Reports extends BaseController
{
    protected $auditModel;

    public function __construct()
    {
        $this->auditModel = new AuditModel();
    }

    protected function checkAuth()
    {
        if (!session()->get('logged_in')) {
            $lastUser = $_COOKIE['last_username'] ?? null;
            if ($lastUser) {
                $um = new \App\Models\UserModel();
                $u = $um->get_user_by_username($lastUser);
                $this->auditModel->log_activity('LOGOUT', 'Auth', "User account logged out due to inactivity \"$lastUser\".", null, $u ? $u['user_id'] : null);
                setcookie('last_username', '', time() - 3600, '/');
            }
            if ($lastUser) session()->setFlashdata('session_expired', 'Your session has expired due to inactivity. Please log in again.');
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

        $reportPeriod = strtolower((string) ($this->request->getGet('report_period') ?: 'today'));
        $today = new \DateTimeImmutable('today');

        switch ($reportPeriod) {
            case 'weekly':
                $startDate = $today->modify('monday this week')->format('Y-m-d');
                $endDate   = $today->modify('sunday this week')->format('Y-m-d');
                break;
            case 'monthly':
                $startDate = $today->modify('first day of this month')->format('Y-m-d');
                $endDate   = $today->modify('last day of this month')->format('Y-m-d');
                break;
            case 'yearly':
                $startDate = $today->modify('first day of january')->format('Y-m-d');
                $endDate   = $today->modify('last day of december')->format('Y-m-d');
                break;
            case 'custom':
                $startDate = $this->request->getGet('start_date');
                $endDate   = $this->request->getGet('end_date');
                break;
            case 'today':
            default:
                $reportPeriod = 'today';
                $startDate = $today->format('Y-m-d');
                $endDate   = $startDate;
                break;
        }

        if ($isAdmin) {
            // Near Expiry
            $nearExpiryWhere = "cs.status = 1 AND cs.quantity_on_hand > 0 AND cs.expiration_date IS NOT NULL";
            $nearExpiryParams = [];
            if (!empty($startDate) && !empty($endDate)) {
                $nearExpiryWhere .= " AND cs.expiration_date BETWEEN ? AND ?";
                $nearExpiryParams = [$startDate, $endDate];
            } elseif (!empty($startDate)) {
                $nearExpiryWhere .= " AND cs.expiration_date >= ?";
                $nearExpiryParams = [$startDate];
            } elseif (!empty($endDate)) {
                $nearExpiryWhere .= " AND cs.expiration_date <= ?";
                $nearExpiryParams = [$endDate];
            } else {
                $nearExpiryWhere .= " AND cs.expiration_date >= CURDATE() AND cs.expiration_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
            }

            $nearExpiryItems = $db->query(
                "SELECT cs.central_supply_id, cs.inventory_code, cs.batch_num, cs.item_code, cs.item_name, cs.unit, cs.quantity_on_hand, cs.expiration_date
                 FROM central_supply cs
                 WHERE {$nearExpiryWhere}
                 ORDER BY cs.expiration_date ASC, cs.item_name ASC",
                $nearExpiryParams
            )->getResultArray();

            // Low Stock
            $lowStockWhere = "cs.status = 1 AND cs.quantity_on_hand <= 10 AND cs.quantity_on_hand > 0";
            $lowStockParams = [];
            if (!empty($startDate) && !empty($endDate)) {
                $lowStockWhere .= " AND (cs.expiration_date IS NULL OR cs.expiration_date BETWEEN ? AND ?)";
                $lowStockParams = [$startDate, $endDate];
            } elseif (!empty($startDate)) {
                $lowStockWhere .= " AND (cs.expiration_date IS NULL OR cs.expiration_date >= ?)";
                $lowStockParams = [$startDate];
            } elseif (!empty($endDate)) {
                $lowStockWhere .= " AND (cs.expiration_date IS NULL OR cs.expiration_date <= ?)";
                $lowStockParams = [$endDate];
            }

            $lowStockItems = $db->query(
                "SELECT cs.central_supply_id, cs.inventory_code, cs.batch_num, cs.item_code, cs.item_name, cs.unit, cs.quantity_on_hand, cs.expiration_date
                 FROM central_supply cs
                 WHERE {$lowStockWhere}
                 ORDER BY cs.quantity_on_hand ASC, cs.item_name ASC",
                $lowStockParams
            )->getResultArray();

            // Expired
            $expiredWhere = "cs.status = 1 AND cs.quantity_on_hand > 0 AND cs.expiration_date IS NOT NULL";
            $expiredParams = [];
            if (!empty($startDate) && !empty($endDate)) {
                $expiredWhere .= " AND cs.expiration_date BETWEEN ? AND ?";
                $expiredParams = [$startDate, $endDate];
            } elseif (!empty($startDate)) {
                $expiredWhere .= " AND cs.expiration_date >= ?";
                $expiredParams = [$startDate];
            } elseif (!empty($endDate)) {
                $expiredWhere .= " AND cs.expiration_date <= ?";
                $expiredParams = [$endDate];
            } else {
                $expiredWhere .= " AND cs.expiration_date < CURDATE()";
            }

            $expiredItems = $db->query(
                "SELECT cs.central_supply_id, cs.inventory_code, cs.batch_num, cs.item_code, cs.item_name, cs.unit, cs.quantity_on_hand, cs.expiration_date
                 FROM central_supply cs
                 WHERE {$expiredWhere}
                 ORDER BY cs.expiration_date DESC, cs.item_name ASC",
                $expiredParams
            )->getResultArray();

            // Out of Stock
            $noStockWhere = "cs.status = 1 AND cs.quantity_on_hand <= 0";
            $noStockParams = [];
            if (!empty($startDate) && !empty($endDate)) {
                $noStockWhere .= " AND (cs.expiration_date IS NULL OR cs.expiration_date BETWEEN ? AND ?)";
                $noStockParams = [$startDate, $endDate];
            } elseif (!empty($startDate)) {
                $noStockWhere .= " AND (cs.expiration_date IS NULL OR cs.expiration_date >= ?)";
                $noStockParams = [$startDate];
            } elseif (!empty($endDate)) {
                $noStockWhere .= " AND (cs.expiration_date IS NULL OR cs.expiration_date <= ?)";
                $noStockParams = [$endDate];
            }

            $noStockItems = $db->query(
                "SELECT cs.central_supply_id, cs.inventory_code, cs.batch_num, cs.item_code, cs.item_name, cs.unit, cs.quantity_on_hand, cs.expiration_date
                 FROM central_supply cs
                 WHERE {$noStockWhere}
                 ORDER BY cs.item_name ASC",
                $noStockParams
            )->getResultArray();
        } else {
            // Staff Department Queries
            $nearExpiryWhereStaff = "ds.department_id = ? AND i.status = 1 AND ds.quantity_on_hand > 0 AND i.expiration_date IS NOT NULL";
            $nearExpiryParamsStaff = [$deptId];
            if (!empty($startDate) && !empty($endDate)) {
                $nearExpiryWhereStaff .= " AND i.expiration_date BETWEEN ? AND ?";
                $nearExpiryParamsStaff[] = $startDate;
                $nearExpiryParamsStaff[] = $endDate;
            } elseif (!empty($startDate)) {
                $nearExpiryWhereStaff .= " AND i.expiration_date >= ?";
                $nearExpiryParamsStaff[] = $startDate;
            } elseif (!empty($endDate)) {
                $nearExpiryWhereStaff .= " AND i.expiration_date <= ?";
                $nearExpiryParamsStaff[] = $endDate;
            } else {
                $nearExpiryWhereStaff .= " AND i.expiration_date >= CURDATE() AND i.expiration_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
            }

            $nearExpiryItems = $db->query(
                "SELECT i.inventory_id, i.inventory_code, i.batch_num, i.item_code, i.item_name, i.unit, SUM(ds.quantity_on_hand) AS quantity_on_hand, i.expiration_date
                 FROM inventory i
                 INNER JOIN supply s ON s.inventory_id = i.inventory_id
                 INNER JOIN department_supply ds ON ds.department_supply_id = s.department_supply_id
                 WHERE {$nearExpiryWhereStaff}
                 GROUP BY i.inventory_id, i.inventory_code, i.batch_num, i.item_code, i.item_name, i.unit, i.expiration_date
                 ORDER BY i.expiration_date ASC, i.item_name ASC",
                $nearExpiryParamsStaff
            )->getResultArray();

            $lowStockWhereStaff = "ds.department_id = ? AND i.status = 1 AND ds.quantity_on_hand <= 10 AND ds.quantity_on_hand > 0";
            $lowStockParamsStaff = [$deptId];
            if (!empty($startDate) && !empty($endDate)) {
                $lowStockWhereStaff .= " AND (i.expiration_date IS NULL OR i.expiration_date BETWEEN ? AND ?)";
                $lowStockParamsStaff[] = $startDate;
                $lowStockParamsStaff[] = $endDate;
            } elseif (!empty($startDate)) {
                $lowStockWhereStaff .= " AND (i.expiration_date IS NULL OR i.expiration_date >= ?)";
                $lowStockParamsStaff[] = $startDate;
            } elseif (!empty($endDate)) {
                $lowStockWhereStaff .= " AND (i.expiration_date IS NULL OR i.expiration_date <= ?)";
                $lowStockParamsStaff[] = $endDate;
            }

            $lowStockItems = $db->query(
                "SELECT i.inventory_id, i.inventory_code, i.batch_num, i.item_code, i.item_name, i.unit, SUM(ds.quantity_on_hand) AS quantity_on_hand, i.expiration_date
                 FROM inventory i
                 INNER JOIN supply s ON s.inventory_id = i.inventory_id
                 INNER JOIN department_supply ds ON ds.department_supply_id = s.department_supply_id
                 WHERE {$lowStockWhereStaff}
                 GROUP BY i.inventory_id, i.inventory_code, i.batch_num, i.item_code, i.item_name, i.unit, i.expiration_date
                 ORDER BY SUM(ds.quantity_on_hand) ASC, i.item_name ASC",
                $lowStockParamsStaff
            )->getResultArray();

            $expiredWhereStaff = "ds.department_id = ? AND i.status = 1 AND ds.quantity_on_hand > 0 AND i.expiration_date IS NOT NULL";
            $expiredParamsStaff = [$deptId];
            if (!empty($startDate) && !empty($endDate)) {
                $expiredWhereStaff .= " AND i.expiration_date BETWEEN ? AND ?";
                $expiredParamsStaff[] = $startDate;
                $expiredParamsStaff[] = $endDate;
            } elseif (!empty($startDate)) {
                $expiredWhereStaff .= " AND i.expiration_date >= ?";
                $expiredParamsStaff[] = $startDate;
            } elseif (!empty($endDate)) {
                $expiredWhereStaff .= " AND i.expiration_date <= ?";
                $expiredParamsStaff[] = $endDate;
            } else {
                $expiredWhereStaff .= " AND i.expiration_date < CURDATE()";
            }

            $expiredItems = $db->query(
                "SELECT i.inventory_id, i.inventory_code, i.batch_num, i.item_code, i.item_name, i.unit, SUM(ds.quantity_on_hand) AS quantity_on_hand, i.expiration_date
                 FROM inventory i
                 INNER JOIN supply s ON s.inventory_id = i.inventory_id
                 INNER JOIN department_supply ds ON ds.department_supply_id = s.department_supply_id
                 WHERE {$expiredWhereStaff}
                 GROUP BY i.inventory_id, i.inventory_code, i.batch_num, i.item_code, i.item_name, i.unit, i.expiration_date
                 ORDER BY i.expiration_date DESC, i.item_name ASC",
                $expiredParamsStaff
            )->getResultArray();

            $noStockWhereStaff = "ds.department_id = ? AND i.status = 1 AND ds.quantity_received > 0";
            $noStockParamsStaff = [$deptId];
            if (!empty($startDate) && !empty($endDate)) {
                $noStockWhereStaff .= " AND (i.expiration_date IS NULL OR i.expiration_date BETWEEN ? AND ?)";
                $noStockParamsStaff[] = $startDate;
                $noStockParamsStaff[] = $endDate;
            } elseif (!empty($startDate)) {
                $noStockWhereStaff .= " AND (i.expiration_date IS NULL OR i.expiration_date >= ?)";
                $noStockParamsStaff[] = $startDate;
            } elseif (!empty($endDate)) {
                $noStockWhereStaff .= " AND (i.expiration_date IS NULL OR i.expiration_date <= ?)";
                $noStockParamsStaff[] = $endDate;
            }

            $noStockItems = $db->query(
                "SELECT i.inventory_id, i.inventory_code, i.batch_num, i.item_code, i.item_name, i.unit, SUM(ds.quantity_on_hand) AS quantity_on_hand, i.expiration_date
                 FROM inventory i
                 INNER JOIN supply s ON s.inventory_id = i.inventory_id
                 INNER JOIN department_supply ds ON ds.department_supply_id = s.department_supply_id
                 WHERE {$noStockWhereStaff}
                 GROUP BY i.inventory_id, i.inventory_code, i.batch_num, i.item_code, i.item_name, i.unit, i.expiration_date
                 HAVING SUM(ds.quantity_on_hand) <= 0
                 ORDER BY i.item_name ASC",
                $noStockParamsStaff
            )->getResultArray();
        }

        $itemRankings = $this->getItemRankings($db, $isAdmin ? null : (int) $deptId);

        $data = [
            'title'             => 'Reports',
            'report_period'     => $reportPeriod,
            'start_date'        => $startDate,
            'end_date'          => $endDate,
            'near_expiry_items' => $nearExpiryItems,
            'low_stock_items'   => $lowStockItems,
            'expired_items'     => $expiredItems,
            'no_stock_items'    => $noStockItems,
            'near_expiry_count' => count($nearExpiryItems),
            'low_stock_count'   => count($lowStockItems),
            'expired_count'     => count($expiredItems),
            'no_stock_count'    => count($noStockItems),
            'top_requested_by_category' => $itemRankings['requested'],
            'top_consumed_by_category'  => $itemRankings['consumed'],
        ];

        return view('templates/header', $data)
             . view('reports', $data)
             . view('templates/footer');
    }

    private function getItemRankings(\CodeIgniter\Database\BaseConnection $db, ?int $departmentId): array
    {
        $requestedParams = [];
        $requestedDepartmentClause = '';
        if ($departmentId !== null) {
            $requestedDepartmentClause = ' AND ds.department_id = ?';
            $requestedParams[] = $departmentId;
        }

        $requested = $db->query(
            "SELECT COALESCE(i.item_code, cs.item_code) AS item_code, COALESCE(i.item_name, cs.item_name) AS item_name,
                    COALESCE(s.unit, i.unit, cs.unit) AS unit, SUM(r.quantity_requested) AS total_quantity
             FROM request r INNER JOIN supply s ON s.request_id = r.request_id
             INNER JOIN department_supply ds ON ds.department_supply_id = s.department_supply_id
             LEFT JOIN inventory i ON i.inventory_id = s.inventory_id LEFT JOIN central_supply cs ON cs.central_supply_id = s.central_supply_id
             WHERE r.status > 0{$requestedDepartmentClause}
             GROUP BY COALESCE(i.item_code, cs.item_code), COALESCE(i.item_name, cs.item_name), COALESCE(s.unit, i.unit, cs.unit)
             ORDER BY total_quantity DESC, item_name ASC LIMIT 10",
            $requestedParams
        )->getResultArray();
        foreach ($requested as $index => &$item) $item['rank'] = $index + 1;
        unset($item);

        $consumedParams = $departmentId === null ? [] : [$departmentId];
        $consumed = $db->query(
            "SELECT s.category_id, c.category_code, c.category_name, i.item_code, i.item_name,
                    COALESCE(i.unit, s.unit) AS unit, SUM(ds.quantity_used) AS total_quantity
             FROM department_supply ds INNER JOIN supply s ON s.department_supply_id = ds.department_supply_id
             INNER JOIN inventory i ON i.inventory_id = s.inventory_id LEFT JOIN category c ON c.category_id = s.category_id
             WHERE ds.quantity_used > 0" . ($departmentId !== null ? ' AND ds.department_id = ?' : '') . "
             GROUP BY s.category_id, c.category_code, c.category_name, i.item_code, i.item_name, COALESCE(i.unit, s.unit)
             ORDER BY c.category_name ASC, total_quantity DESC, i.item_name ASC",
            $consumedParams
        )->getResultArray();

        $byCategory = [];
        foreach ($consumed as $item) {
            $categoryId = (int) ($item['category_id'] ?? 0);
            if (!isset($byCategory[$categoryId])) {
                $byCategory[$categoryId] = ['category_code' => $item['category_code'] ?? '', 'category_name' => $item['category_name'] ?? 'Uncategorized', 'items' => []];
            }
            if (count($byCategory[$categoryId]['items']) < 10) {
                $item['rank'] = count($byCategory[$categoryId]['items']) + 1;
                $byCategory[$categoryId]['items'][] = $item;
            }
        }

        return ['requested' => $requested, 'consumed' => array_values($byCategory)];
    }
}
