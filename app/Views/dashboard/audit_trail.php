<!-- Page Title Section -->
<div class="page-title-section fade-in-up">
    <div>
        <h1 class="page-title mb-1">Audit Trail Log</h1>
        <p class="text-secondary mb-0">Track and review administrative operations and security event records</p>
    </div>
</div>

<!-- Search & Filtering Drawer Card -->
<div class="filter-card fade-in-up" style="animation-delay: 0.1s;">
    <h5 class="font-heading mb-3" style="font-size: 1rem;">
        <span>Filter Logs</span>
    </h5>
    
    <form method="GET" action="<?php echo base_url('dashboard/audit_trail'); ?>" class="row g-3" id="auditFilterForm">
        <!-- 1. Start Date -->
        <div class="col-12 col-sm-6 col-md-3 col-xl-2">
            <label for="start_date" class="form-label small fw-semibold text-secondary">Start Date</label>
            <input type="date" 
                   class="form-control input-custom" 
                   id="start_date" 
                   name="start_date" 
                   value="<?php echo isset($filters['start_date']) ? htmlspecialchars($filters['start_date']) : ''; ?>">
        </div>

        <!-- 2. End Date -->
        <div class="col-12 col-sm-6 col-md-3 col-xl-2">
            <label for="end_date" class="form-label small fw-semibold text-secondary">End Date</label>
            <input type="date" 
                   class="form-control input-custom" 
                   id="end_date" 
                   name="end_date" 
                   value="<?php echo isset($filters['end_date']) ? htmlspecialchars($filters['end_date']) : ''; ?>">
        </div>

        <!-- 3. Username -->
        <div class="col-12 col-sm-6 col-md-3 col-xl-2">
            <label for="username" class="form-label small fw-semibold text-secondary">User/Account</label>
            <input type="text" 
                   class="form-control input-custom" 
                   id="username" 
                   name="username" 
                   placeholder="Search username"
                   value="<?php echo isset($filters['username']) ? htmlspecialchars($filters['username']) : ''; ?>">
        </div>

        <!-- 4. Action Type Select -->
        <div class="col-12 col-sm-6 col-md-3 col-xl-2">
            <label for="action" class="form-label small fw-semibold text-secondary">Action</label>
            <select class="form-select input-custom" id="action" name="action">
                <option value="">-- All Actions --</option>
                <?php foreach ($unique_actions as $act): ?>
                    <option value="<?php echo $act; ?>" <?php echo (isset($filters['action']) && $filters['action'] === $act) ? 'selected' : ''; ?>>
                        <?php echo $act; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- 5. Form Submission Buttons -->
        <div class="col-12 col-md-3 col-xl-4 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2" id="btnFilterSubmit">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>Filter</span>
            </button>
            <a href="<?php echo base_url('dashboard/audit_trail'); ?>" class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-content-center gap-2" id="btnFilterReset">
                <i class="fa-solid fa-rotate-left"></i>
                <span>Reset</span>
            </a>
        </div>
    </form>
</div>

<!-- Log Data Table Area -->
<div class="standard-card fade-in-up" style="animation-delay: 0.2s;">
    <div class="card-header-styled mb-4">
        <h5 class="card-title-styled">
            <span>Log Database History</span>
        </h5>
        <!-- Container for DataTable Buttons Injection -->
        <div id="tableActionsContainer" class="d-flex align-items-center gap-2"></div>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-custom table-hover w-100" id="auditLogsTable">
            <thead>
                <tr>
                    <th style="width: 8%">#</th>
                    <th style="width: 22%">Timestamp</th>
                    <th style="width: 18%">User</th>
                    <th style="width: 18%">Action</th>
                    <th style="width: 34%">Description</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logs)): ?>
                    <?php $count = 1; ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><span class="text-muted small"><?php echo $count++; ?></span></td>
                            <td class="font-monospace" style="font-size: 0.85rem;">
                                <?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-circle" style="width: 24px; height: 24px; font-size: 0.65rem; background: #e2e8f0; color: #475569;">
                                        <?php echo strtoupper(substr($log['username'], 0, 2)); ?>
                                    </div>
                                    <span class="fw-semibold" style="font-size: 0.85rem;"><?php echo htmlspecialchars($log['username']); ?></span>
                                </div>
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
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
