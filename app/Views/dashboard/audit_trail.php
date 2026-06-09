<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?php echo session()->getFlashdata('success'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('warning')): ?>
    <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
        <i class="fa-solid fa-circle-exclamation me-2"></i><?php echo session()->getFlashdata('warning'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo session()->getFlashdata('error'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="page-title-section fade-in-up d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="page-title mb-1">Audit Trail</h1>
    </div>
</div>


<!-- Log Data Table Area -->
<div class="standard-card fade-in-up" style="animation-delay: 0.2s;">
    <div class="card-header-styled mb-4">
        <h5 class="card-title-styled">
            <span>Audit Log</span>
        </h5>
        <!-- Container for DataTable Buttons Injection -->
        <div id="tableActionsContainer" class="d-flex align-items-center gap-2"></div>
    </div>

    <div class="table-responsive-custom" id="auditTableWrapper">
        <table class="table table-custom table-hover w-100" id="auditLogsTable">
            <thead>
                <tr>
                    <th style="width: 14%">Date/Time</th>
                    <th style="width: 10%">User</th>
                    <th style="width: 10%">Action</th>
                    <th>Description</th>
                    <?php if (is_admin_role()): ?>
                        <th style="width: 10%">IP Address</th>
                        <th style="width: 12%">Device</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr class="audit-log-row" data-log-id="<?php echo $log['log_id']; ?>">
                            <td data-order="<?php echo $log['created_at']; ?>">
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
                                    if ($log['action'] === 'LOGIN') {
                                        $badge_class = 'bg-success-subtle text-success border border-success-subtle';
                                    } elseif ($log['action'] === 'LOGOUT') {
                                        $badge_class = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
                                    } elseif ($log['action'] === 'LOGIN_FAIL') {
                                        $badge_class = 'bg-danger-subtle text-danger border border-danger-subtle';
                                    } elseif ($log['action'] === 'PAGE_VIEW') {
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
                            <?php if (is_admin_role()): ?>
                            <td>
                                <span class="text-muted small font-monospace">
                                    <?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-muted small" style="font-size: 0.75rem; word-break: break-word;" title="<?php echo htmlspecialchars($log['user_agent'] ?? ''); ?>">
                                    <?php 
                                        $ua = $log['user_agent'] ?? '';
                                        echo htmlspecialchars(!empty($ua) ? (strlen($ua) > 40 ? substr($ua, 0, 40) . '...' : $ua) : '-');
                                    ?>
                                </span>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
