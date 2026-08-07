<!-- Page Title Section -->
<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">Dashboard</h1>
        </div>
        <div class="d-flex gap-2">
           
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 rounded-3" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-exclamation fs-5"></i>
            <span><?php echo session()->getFlashdata('error'); ?></span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- KPI Stats Section -->
<div class="row g-4 mb-4 fade-in-up" style="animation-delay: 0.05s;">

    <!-- Pending & Partially Served Requests -->
    <div class="col-lg-6 col-xl-3 col-12">
        <div class="kpi-card h-100 d-flex justify-content-between align-items-center" onclick="window.location='<?php echo base_url('requests'); ?>'" style="cursor:pointer; transition: all 0.2s ease; border-radius: 8px; border-left: 6px solid var(--primary); border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 1.25rem;">
            <div>
                <div class="kpi-label text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em; color: #64748b !important;">Requests</div>
                <h3 class="kpi-value text-dark fw-bold mt-1"><?php echo $total_inventory; ?></h3>
                <div class="mt-2" style="font-size: 0.75rem; color: #10b981; font-weight: 500;">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Near Expiry Count -->
    <div class="col-lg-6 col-xl-3 col-12">
        <div class="kpi-card h-100 d-flex justify-content-between align-items-center" onclick="window.location='<?php echo base_url('inventory?stock_status=near_expiry'); ?>'" style="cursor:pointer; transition: all 0.2s ease; border-radius: 8px; border-left: 6px solid #f59e0b; border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 1.25rem;">
            <div>
                <div class="kpi-label text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em; color: #64748b !important;">Near Expiry</div>
                <h3 class="kpi-value text-dark fw-bold mt-1"><?php echo $total_near_expiry; ?></h3>
            </div>
        </div>
    </div>

    <!-- Expired Count -->
    <div class="col-lg-6 col-xl-3 col-12">
        <div class="kpi-card h-100 d-flex justify-content-between align-items-center" onclick="window.location='<?php echo base_url('inventory?stock_status=expired'); ?>'" style="cursor:pointer; transition: all 0.2s ease; border-radius: 8px; border-left: 6px solid var(--danger); border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 1.25rem;">
            <div>
                <div class="kpi-label text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em; color: #64748b !important;">Expired Count</div>
                <h3 class="kpi-value text-dark fw-bold mt-1"><?php echo $total_expired; ?></h3>
            </div>
        </div>
    </div>

    <!-- No Stock Count -->
    <div class="col-lg-6 col-xl-3 col-12">
        <div class="kpi-card h-100 d-flex justify-content-between align-items-center" onclick="window.location='<?php echo base_url('inventory?stock_status=out_of_stock'); ?>'" style="cursor:pointer; transition: all 0.2s ease; border-radius: 8px; border-left: 6px solid var(--danger); border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 1.25rem;">
            <div>
                <div class="kpi-label text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em; color: #64748b !important;">No Stock Count</div>
                <h3 class="kpi-value text-dark fw-bold mt-1"><?php echo $total_no_stock; ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Main Row Content (Stacked Tables layout) -->
<div class="row g-4 fade-in-up" style="animation-delay: 0.1s;">
    <!-- 1. Requests Panel -->
    <div class="col-12">
        <div class="standard-card">
            <div class="card-header-styled">
                <h5 class="card-title-styled">
                    <span>Pending Requests</span>
                </h5>
                <a href="<?php echo base_url('requests'); ?>" class="btn btn-outline-primary d-flex align-items-center gap-2">
                    <span>View All</span>
                </a>
            </div>
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Request Date</th>
                            <th style="width: 15%;">Reference ID</th>
                            <th style="width: 20%;">Requester</th>
                            <th style="width: 20%;">Item</th>
                            <th style="width: 10%;">Quantity</th>
                            <th style="width: 10%;">Unit</th>
                            <th style="width: 10%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_requests)): ?>
                            <?php foreach ($recent_requests as $req): ?>
                                <tr>
                                    <td class="text-center"><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($req['reference_no'] ?? ('#' . $req['request_id'])); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($req['requester_name']); ?></td>
                                    <td class="text-start"><?php echo htmlspecialchars($req['item_name']); ?></td>
                                    <td class="text-center"><?php echo number_format((int)$req['quantity_requested']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($req['unit'] ?? 'N/A'); ?></td>
                                    <td class="text-center">
                                        <?php $s = (int)$req['request_status']; ?>
                                        <span class="badge badge-action rounded-pill <?php echo $s === 1 ? 'bg-warning-subtle text-dark border border-warning-subtle' : 'bg-info-subtle text-dark border border-info-subtle'; ?>">
                                            <?php echo $s === 1 ? 'Pending' : 'Partial'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <span class="fw-medium">No pending requests.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 2. Near Expiry Items Panel -->
    <div class="col-12 mt-4">
        <div class="standard-card">
            <div class="card-header-styled">
                <h5 class="card-title-styled">
                    <span>Near Expiry Items</span>
                </h5>
                <a href="<?php echo base_url('inventory?stock_status=near_expiry'); ?>" class="btn btn-outline-primary d-flex align-items-center gap-2" id="nearExpiryQuickActionBtn">
                    <span>View All</span>
                </a>
            </div>
            
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Item Name</th>
                            <th style="width: 12%;">Item Code</th>
                            <th style="width: 18%;">Inventory Code</th>
                            <th style="width: 10%;">Stock</th>
                            <th style="width: 10%;">Unit</th>
                            <th style="width: 13%;">Expiry</th>
                            <th style="width: 12%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($near_expiry_items)): ?>
                            <?php foreach ($near_expiry_items as $item): ?>
                                <tr>
                                    <td class="text-start">
                                        <?php echo htmlspecialchars($item['item_name']); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo htmlspecialchars($item['item_code']); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo htmlspecialchars($item['inventory_code'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo (int)$item['quantity_on_hand']; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo htmlspecialchars($item['unit'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo !empty($item['expiration_date']) ? date('M d, Y', strtotime($item['expiration_date'])) : 'N/A'; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-action rounded-pill bg-danger-subtle text-dark border border-danger-subtle">
                                            Near Expiry
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class=" d-block fs-3 mb-2 text-secondary"></i>
                                    <span class="fw-medium">No items near expiry.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. Expired Items Panel -->
    <div class="col-12 mt-4">
        <div class="standard-card">
            <div class="card-header-styled">
                <h5 class="card-title-styled">
                    <span>Expired Items</span>
                </h5>
                <a href="<?php echo base_url('inventory?stock_status=expired'); ?>" class="btn btn-outline-primary d-flex align-items-center gap-2">
                    <span>View All</span>
                </a>
            </div>
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Item Name</th>
                            <th style="width: 12%;">Item Code</th>
                            <th style="width: 18%;">Inventory Code</th>
                            <th style="width: 10%;">Stock</th>
                            <th style="width: 10%;">Unit</th>
                            <th style="width: 13%;">Expiry</th>
                            <th style="width: 12%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($expired_items)): ?>
                            <?php foreach ($expired_items as $item): ?>
                                <tr>
                                    <td class="text-start"><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['item_code']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['inventory_code'] ?? 'N/A'); ?></td>
                                    <td class="text-center"><?php echo number_format((int)$item['quantity_on_hand']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['unit'] ?? 'N/A'); ?></td>
                                    <td class="text-center"><?php echo !empty($item['expiration_date']) ? date('M d, Y', strtotime($item['expiration_date'])) : 'N/A'; ?></td>
                                    <td class="text-center">
                                        <span class="badge badge-action rounded-pill bg-dark-subtle text-dark border border-dark-subtle">
                                            Expired
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <span class="fw-medium">No expired items.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 4. No Stock Items Panel -->
    <div class="col-12 mt-4">
        <div class="standard-card">
            <div class="card-header-styled">
                <h5 class="card-title-styled">
                    <span>Out of Stock Items</span>
                </h5>
                <a href="<?php echo base_url('inventory?stock_status=out_of_stock'); ?>" class="btn btn-outline-primary d-flex align-items-center gap-2">
                    <span>View All</span>
                </a>
            </div>
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Item Name</th>
                            <th style="width: 12%;">Item Code</th>
                            <th style="width: 18%;">Inventory Code</th>
                            <th style="width: 10%;">Stock</th>
                            <th style="width: 10%;">Unit</th>
                            <th style="width: 13%;">Expiry</th>
                            <th style="width: 12%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($no_stock_items)): ?>
                            <?php foreach ($no_stock_items as $item): ?>
                                <tr>
                                    <td class="text-start"><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['item_code']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['inventory_code'] ?? 'N/A'); ?></td>
                                    <td class="text-center"><?php echo number_format((int)$item['quantity_on_hand']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['unit'] ?? 'N/A'); ?></td>
                                    <td class="text-center"><?php echo !empty($item['expiration_date']) ? date('M d, Y', strtotime($item['expiration_date'])) : 'N/A'; ?></td>
                                    <td class="text-center">
                                        <span class="badge badge-action rounded-pill bg-danger-subtle text-dark border border-danger-subtle">
                                            Out of Stock
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <span class="fw-medium">All items are in stock.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 5. Recent Activities Panel -->
    <div class="col-12 mt-4">
        <div class="standard-card">
            <div class="card-header-styled">
                <h5 class="card-title-styled">
                    <span>Recent Activities</span>
                </h5>
                <a href="<?php echo base_url('audit'); ?>" class="btn btn-outline-primary d-flex align-items-center gap-2" id="kpiQuickActionBtn">
                    <span>View All</span>
                </a>
            </div>
            
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th style="width: 18%;">Date/Time</th>
                            <th style="width: 12%;">Action</th>
                            <th>Description</th>
                            <th style="width: 12%;">User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_logs)): ?>
                            <?php foreach ($recent_logs as $log): ?>
                                <tr>
                                    <td class="text-center">
                                        <?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $badge_class = 'bg-secondary';
                                            if (in_array($log['action'], array('LOGIN', 'CREATE_DEPT', 'CREATE_USER', 'ADD_ITEM', 'COMPLETE_PARTIAL_SUPPLY_REQUEST', 'SERVE_SUPPLY_REQUEST', 'PARTIAL_SUPPLY_REQUEST'))) {
                                                $badge_class = 'bg-success-subtle text-dark border border-success-subtle';
                                            } elseif (in_array($log['action'], array('LOGOUT'))) {
                                                $badge_class = 'bg-secondary-subtle text-dark border border-secondary-subtle';
                                            } elseif (in_array($log['action'], array('LOGIN_FAILED', 'SYSTEM_ERR', 'REJECT_SUPPLY_REQUEST'))) {
                                                $badge_class = 'bg-danger-subtle text-dark border border-danger-subtle';
                                            } elseif (in_array($log['action'], array('UPDATE_DEPT', 'UPDATE_USER', 'UPDATE_ITEM', 'UPDATE_PROFILE'))) {
                                                $badge_class = 'bg-info-subtle text-dark border border-info-subtle';
                                            }
                                        ?>
                                        <span class="badge badge-action rounded-pill <?php echo $badge_class; ?>">
                                            <?php echo $log['action']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="word-break: break-word; white-space: normal;" title="<?php echo htmlspecialchars($log['description']); ?>">
                                            <?php echo htmlspecialchars($log['description']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php echo htmlspecialchars($log['username']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fa-regular fa-folder-open d-block fs-3 mb-2 text-secondary"></i>
                                    <span class="fw-medium">No recent logs found.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Date Filter Bar for Rankings & Analytics -->
<form method="GET" action="<?php echo base_url('dashboard'); ?>" id="dashboardFilterForm" class="fade-in-up mt-4 mb-2" style="animation-delay: 0.07s;">
    <div class="db-search-bar" style="flex-wrap: wrap; gap: 8px;">
        <div class="db-search-field" style="flex: 0 0 200px;">
            <input type="date" id="dash_start_date" name="start_date" class="db-search-input" value="<?php echo htmlspecialchars($start_date ?? date('Y-01-01')); ?>" placeholder=" ">
            <label for="dash_start_date">Start Date</label>
        </div>
        <div class="db-search-field" style="flex: 0 0 200px;">
            <input type="date" id="dash_end_date" name="end_date" class="db-search-input" value="<?php echo htmlspecialchars($end_date ?? date('Y-m-d')); ?>" placeholder=" ">
            <label for="dash_end_date">End Date</label>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="submit" class="btn-db-search">
                Search
            </button>
            <a href="<?php echo base_url('dashboard'); ?>" class="btn-db-clear">
                Clear
            </a>
        </div>
    </div>
</form>

<div class="row g-4 mt-1 fade-in-up" style="animation-delay: 0.08s;">
    <div class="col-12">
        <div class="standard-card">
            <div class="card-header-styled"><h5 class="card-title-styled"><span>Top 10 Requested Items</span></h5></div>
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover mb-0 w-100">
                    <thead><tr><th class="text-center" style="width: 12%;">Rank</th><th class="text-center">Item Name</th><th class="text-center" style="width: 20%;">Item Code</th><th class="text-center" style="width: 14%;">Unit</th><th class="text-center" style="width: 18%;">Total Requested</th></tr></thead>
                    <tbody>
                        <?php if (!empty($top_requested_by_category)): ?>
                            <?php foreach ($top_requested_by_category as $item): ?>
                                <tr><td class="text-center"><?php echo (int) $item['rank']; ?></td><td><?php echo htmlspecialchars($item['item_name'] ?? 'N/A'); ?></td><td class="text-center"><?php echo htmlspecialchars($item['item_code'] ?? 'N/A'); ?></td><td class="text-center"><?php echo htmlspecialchars($item['unit'] ?? ''); ?></td><td class="text-center"><?php echo number_format((int) $item['total_quantity']); ?></td></tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No requested items found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="standard-card">
            <div class="card-header-styled">
                <h5 class="card-title-styled"><span>Top 5 Requesting Departments</span></h5>
            </div>
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:8%;">Rank</th>
                            <th>Department Name</th>
                            <th class="text-center" style="width:14%;">Department Code</th>
                            <th class="text-center" style="width:14%;">Total Requests</th>
                            <th class="text-center" style="width:20%;">Quantity Requested</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($top_requesting_departments)): ?>
                            <?php foreach ($top_requesting_departments as $dept): ?>
                                <tr>
                                    <td class="text-center"><?php echo (int)$dept['rank']; ?></td>
                                    <td><?php echo htmlspecialchars($dept['department_name']); ?></td>
                                    <td class="text-center text-muted"><?php echo htmlspecialchars($dept['department_code'] ?? '—'); ?></td>
                                    <td class="text-center"><?php echo number_format((int)$dept['total_requests']); ?></td>
                                    <td class="text-center"><?php echo number_format((int)$dept['total_requested']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No requesting data available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="standard-card">
            <div class="card-header-styled"><h5 class="card-title-styled"><span>Top 10 Consumed Items per Category</span></h5></div>
            <div class="p-3">
                <?php if (!empty($top_consumed_by_category)): ?>
                    <div class="row g-3">
                        <?php foreach ($top_consumed_by_category as $category): ?>
                            <div class="col-12">
                                <div class="border rounded-3 overflow-hidden">
                                    <div class="px-3 py-2 fw-semibold" style="background: rgba(16, 185, 129, 0.10); color: #047857;"><?php echo htmlspecialchars(trim(($category['category_code'] ?? '') . ' - ' . ($category['category_name'] ?? 'Uncategorized'), ' -')); ?></div>
                                    <div class="table-responsive-custom">
                                        <table class="table table-custom table-hover mb-0 w-100">
                                            <thead><tr><th class="text-center" style="width: 12%;">Rank</th><th class="text-center">Item Name</th><th class="text-center" style="width: 20%;">Item Code</th><th class="text-center" style="width: 14%;">Unit</th><th class="text-center" style="width: 18%;">Total Consumed</th></tr></thead>
                                            <tbody>
                                                <?php foreach ($category['items'] as $item): ?>
                                                    <tr><td class="text-center"><?php echo (int) $item['rank']; ?></td><td><?php echo htmlspecialchars($item['item_name'] ?? 'N/A'); ?></td><td class="text-center"><?php echo htmlspecialchars($item['item_code'] ?? 'N/A'); ?></td><td class="text-center"><?php echo htmlspecialchars($item['unit'] ?? ''); ?></td><td class="text-center"><?php echo number_format((int) $item['total_quantity']); ?></td></tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">No consumed items found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


