<!-- Page Title Section -->
<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">Overview Dashboard</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo base_url('dashboard/audit_trail'); ?>" class="btn btn-primary d-flex align-items-center gap-2" id="kpiQuickActionBtn">
                <i class="fa-solid fa-file-invoice"></i>
                <span>View Full Audit Logs</span>
            </a>
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
    <!-- Card 1: Registered Users -->
    <div class="col-12 col-md-6">
        <?php if (session()->get('role') === 'admin'): ?>
            <div class="kpi-card h-100" onclick="window.location='<?php echo base_url('users'); ?>'" style="cursor:pointer;">
                <div class="kpi-icon-wrapper kpi-icon-success">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="kpi-label">Registered Accounts</div>
                <h3 class="kpi-value"><?php echo $total_users; ?></h3>
            </div>
        <?php else: ?>
            <div class="kpi-card h-100">
                <div class="kpi-icon-wrapper kpi-icon-success">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="kpi-label">Registered Accounts</div>
                <h3 class="kpi-value"><?php echo $total_users; ?></h3>
            </div>
        <?php endif; ?>
    </div>

    <!-- Card 2: System Logs -->
    <div class="col-12 col-md-6">
        <div class="kpi-card h-100" onclick="window.location='<?php echo base_url('dashboard/audit_trail'); ?>'" style="cursor:pointer;">
            <div class="kpi-icon-wrapper kpi-icon-warning">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div class="kpi-label">System Operations Logs</div>
            <h3 class="kpi-value"><?php echo $total_logs; ?></h3>
        </div>
    </div>
</div>

<!-- Main Row Content -->
<div class="row g-4 fade-in-up" style="animation-delay: 0.1s;">
    <!-- Recent Logs Mini Panel (Full-Width) -->
    <div class="col-12">
        <div class="standard-card">
            <div class="card-header-styled">
                <h5 class="card-title-styled">
                    <i class="fa-solid fa-table-list text-primary me-2"></i>
                    <span>Recent System Operations</span>
                </h5>
                <a href="<?php echo base_url('dashboard/audit_trail'); ?>" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold text-primary" id="btnRecentLogsAll">
                    See All Operations <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
            
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover">
                    <thead>
                        <tr>
                            <th style="width: 15%">User</th>
                            <th style="width: 15%">Action</th>
                            <th style="width: 55%">Description</th>
                            <th style="width: 15%">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_logs)): ?>
                            <?php foreach ($recent_logs as $log): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="user-avatar-circle" style="width: 26px; height: 26px; font-size: 0.7rem; background: #e2e8f0; color: #475569;">
                                                <?php echo strtoupper(substr($log['username'], 0, 2)); ?>
                                            </div>
                                            <span class="fw-medium" style="font-size:0.875rem;"><?php echo $log['username']; ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                            $badge_class = 'bg-secondary';
                                            if (in_array($log['action'], array('LOGIN', 'CREATE_DEPT', 'CREATE_USER', 'ADD_ITEM'))) {
                                                $badge_class = 'bg-success-subtle text-success border border-success-subtle';
                                            } elseif (in_array($log['action'], array('LOGOUT', 'DELETE_DEPT', 'DELETE_USER', 'DELETE_ITEM'))) {
                                                $badge_class = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
                                            } elseif (in_array($log['action'], array('LOGIN_FAIL', 'SYSTEM_ERR'))) {
                                                $badge_class = 'bg-danger-subtle text-danger border border-danger-subtle';
                                            } elseif (in_array($log['action'], array('UPDATE_DEPT', 'UPDATE_USER', 'UPDATE_ITEM'))) {
                                                $badge_class = 'bg-info-subtle text-info border border-info-subtle';
                                            }
                                        ?>
                                        <span class="badge badge-action <?php echo $badge_class; ?>">
                                            <?php echo $log['action']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-secondary small d-inline-block text-truncate" style="max-width: 600px;" title="<?php echo htmlspecialchars($log['description']); ?>">
                                            <?php echo htmlspecialchars($log['description']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-secondary font-monospace" style="font-size:0.75rem;">
                                            <?php echo date('H:i:s', strtotime($log['created_at'])); ?>
                                        </small>
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
