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

    <!-- Inventory Count -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card h-100 d-flex justify-content-between align-items-center" onclick="window.location='<?php echo base_url('inventory'); ?>'" style="cursor:pointer; transition: all 0.2s ease; border-radius: 8px; border-left: 6px solid #0d9488; border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 1.25rem;">
            <div>
                <div class="kpi-label text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em; color: #64748b !important;">Inventory Count</div>
                <h3 class="kpi-value text-dark fw-bold mt-1"><?php echo $total_inventory; ?></h3>
                <div class="mt-2" style="font-size: 0.75rem; color: #10b981; font-weight: 500;">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Low Stock Count -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card h-100 d-flex justify-content-between align-items-center" onclick="window.location='<?php echo base_url('inventory?stock_status=low_stock'); ?>'" style="cursor:pointer; transition: all 0.2s ease; border-radius: 8px; border-left: 6px solid #f59e0b; border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 1.25rem;">
            <div>
                <div class="kpi-label text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em; color: #64748b !important;">Low Stock Count</div>
                <h3 class="kpi-value text-dark fw-bold mt-1"><?php echo $total_low_stock; ?></h3>
               
            </div>
           
        </div>
    </div>

    <!-- No Stock Count -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card h-100 d-flex justify-content-between align-items-center" onclick="window.location='<?php echo base_url('inventory?stock_status=out_of_stock'); ?>'" style="cursor:pointer; transition: all 0.2s ease; border-radius: 8px; border-left: 6px solid #ef4444; border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 1.25rem;">
            <div>
                <div class="kpi-label text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em; color: #64748b !important;">No Stock Count</div>
                <h3 class="kpi-value text-dark fw-bold mt-1"><?php echo $total_no_stock; ?></h3>
            </div>
        </div>
    </div>

    <!-- Supply Requests -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card h-100 d-flex justify-content-between align-items-center" onclick="window.location='<?php echo base_url('supply_requests'); ?>'" style="cursor:pointer; transition: all 0.2s ease; border-radius: 8px; border-left: 6px solid #0d9488; border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 1.25rem;">
            <div>
                <div class="kpi-label text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em; color: #64748b !important;">Supply Requests</div>
                <h3 class="kpi-value text-dark fw-bold mt-1"><?php echo $total_requests; ?></h3>
        </div>
    </div>
</div>

<!-- Main Row Content (Stacked Tables layout) -->
<div class="row g-4 fade-in-up" style="animation-delay: 0.1s;">
    <!-- 1. Recent Supply Requests Panel (Top Full-Width Column) -->
    <div class="col-12">
        <div class="standard-card">
            <div class="card-header-styled">
                <h5 class="card-title-styled">
                    <i class="bi bi-file-earmark-text text-primary"></i>
                    <span>Recent Supply Requests</span>
                </h5>
                <a href="<?php echo base_url('supply_requests'); ?>" class="btn btn-primary d-flex align-items-center gap-2" id="supplyQuickActionBtn">
                    <span>View All</span>
                </a>
            </div>
            
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th style="width: 8%">ID</th>
                            <th style="width: 20%">Date</th>
                            <th style="width: 22%">Requester / Department</th>
                            <th>Item Requested</th>
                            <th style="width: 15%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_requests)): ?>
                            <?php foreach ($recent_requests as $req): ?>
                                <tr>
                                    <td class="font-monospace fw-bold" style="font-size: 0.85rem; color: var(--text-secondary);">
                                        #<?php echo $req['request_id']; ?>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-medium" style="font-size: 0.88rem;">
                                            <?php echo date('M d, Y h:i A', strtotime($req['request_date'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark" style="font-size: 0.88rem;">
                                            <?php echo htmlspecialchars($req['department_name']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.88rem; font-weight: 600;" class="text-dark">
                                            <?php echo htmlspecialchars($req['item_name']); ?>
                                        </div>
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            Quantity Requested: <?php echo $req['quantity_requested']; ?> unit(s)
                                        </small>
                                    </td>
                                    <td>
                                        <?php 
                                            if ($req['status'] === 'Served') {
                                                $badge = 'bg-success-subtle text-success border border-success-subtle';
                                            } elseif ($req['status'] === 'Partially Served') {
                                                $badge = 'bg-primary-subtle text-primary border border-primary-subtle';
                                            } elseif ($req['status'] === 'Rejected') {
                                                $badge = 'bg-danger-subtle text-danger border border-danger-subtle';
                                            } else {
                                                $badge = 'bg-warning-subtle text-warning border border-warning-subtle';
                                            }
                                        ?>
                                        <span class="badge badge-action <?php echo $badge; ?>">
                                            <?php echo $req['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fa-regular fa-folder-open d-block fs-3 mb-2 text-secondary"></i>
                                    <span class="fw-medium">No recent requests found.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 2. Recent Activities Panel (Bottom Full-Width Column) -->
    <div class="col-12 mt-4">
        <div class="standard-card">
            <div class="card-header-styled">
                <h5 class="card-title-styled">
                    <i class="bi bi-clock-history text-primary"></i>
                    <span>Recent Activities</span>
                </h5>
                <a href="<?php echo base_url('dashboard/audit_trail'); ?>" class="btn btn-primary d-flex align-items-center gap-2" id="kpiQuickActionBtn">
                    <span>View All</span>
                </a>
            </div>
            
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 18%">Date/Time</th>
                            <th style="width: 12%">User</th>
                            <th style="width: 12%">Action</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_logs)): ?>
                            <?php $count = 1; ?>
                            <?php foreach ($recent_logs as $log): ?>
                                <tr>
                                    <td><span class="text-muted small"><?php echo $count++; ?></span></td>
                                    <td>
                                        <span class="fw-semibold text-dark" style="font-size: 0.9rem;">
                                            <?php echo date('F j, Y g:i A', strtotime($log['created_at'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold" style="font-size: 0.85rem;">
                                            <?php echo htmlspecialchars($log['username']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                            $badge_class = 'bg-secondary';
                                            if (in_array($log['action'], array('LOGIN', 'CREATE_DEPT', 'CREATE_USER', 'ADD_ITEM', 'COMPLETE_PARTIAL_SUPPLY_REQUEST', 'SERVE_SUPPLY_REQUEST', 'PARTIAL_SUPPLY_REQUEST'))) {
                                                $badge_class = 'bg-success-subtle text-success border border-success-subtle';
                                            } elseif (in_array($log['action'], array('LOGOUT', 'DELETE_DEPT', 'DELETE_USER', 'DELETE_ITEM', 'DELETE_SUPPLY_REQUEST', 'BULK_DELETE_SUPPLY_REQUESTS'))) {
                                                $badge_class = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
                                            } elseif (in_array($log['action'], array('LOGIN_FAIL', 'SYSTEM_ERR', 'REJECT_SUPPLY_REQUEST'))) {
                                                $badge_class = 'bg-danger-subtle text-danger border border-danger-subtle';
                                            } elseif (in_array($log['action'], array('UPDATE_DEPT', 'UPDATE_USER', 'UPDATE_ITEM', 'UPDATE_PROFILE'))) {
                                                $badge_class = 'bg-info-subtle text-info border border-info-subtle';
                                            }
                                        ?>
                                        <span class="badge badge-action <?php echo $badge_class; ?>">
                                            <?php echo $log['action']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-secondary small d-block" style="word-break: break-word; white-space: normal;" title="<?php echo htmlspecialchars($log['description']); ?>">
                                            <?php echo htmlspecialchars($log['description']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
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
