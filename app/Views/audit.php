<!-- Page Title Section -->
<div class="page-breadcrumb">
    <a href="<?php echo base_url('dashboard'); ?>">Dashboard</a>
    <span class="separator">/</span>
    <span class="current">Audit Log</span>
</div>

<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">Audit Log</h1>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 rounded-3" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <span><?php echo session()->getFlashdata('success'); ?></span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('warning')): ?>
    <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-4 rounded-3" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-exclamation fs-5"></i>
            <span><?php echo session()->getFlashdata('warning'); ?></span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 rounded-3" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-exclamation fs-5"></i>
            <span><?php echo session()->getFlashdata('error'); ?></span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Search Bar -->
<form method="GET" action="<?php echo base_url('audit'); ?>" id="auditSearchForm">
    <div class="db-search-bar">
        <div class="db-search-field db-search-field--keyword">
            <input 
                type="text" 
                id="audit_search_keyword" 
                name="search" 
                class="db-search-input" 
                placeholder=" " 
                value="<?php echo htmlspecialchars($search ?? ''); ?>"
                autocomplete="off"
            >
            <label for="audit_search_keyword">Enter Description / User</label>
        </div>
        <div class="db-search-field db-search-field--dropdown">
            <select id="audit_search_action" name="action_filter" class="db-search-select">
                <option value="">- Select Action -</option>
                <option value="LOGIN"                          <?php echo (($action_filter ?? '') === 'LOGIN')                          ? 'selected' : ''; ?>>LOGIN</option>
                <option value="LOGIN_FAILED"                   <?php echo (($action_filter ?? '') === 'LOGIN_FAILED')                   ? 'selected' : ''; ?>>LOGIN_FAILED</option>
                <option value="LOGOUT"                         <?php echo (($action_filter ?? '') === 'LOGOUT')                         ? 'selected' : ''; ?>>LOGOUT</option>
                <option value="CREATE_USER"                    <?php echo (($action_filter ?? '') === 'CREATE_USER')                    ? 'selected' : ''; ?>>CREATE_USER</option>
                <option value="UPDATE_USER"                    <?php echo (($action_filter ?? '') === 'UPDATE_USER')                    ? 'selected' : ''; ?>>UPDATE_USER</option>
                <option value="ACTIVATE_USER"                  <?php echo (($action_filter ?? '') === 'ACTIVATE_USER')                  ? 'selected' : ''; ?>>ACTIVATE_USER</option>
                <option value="DEACTIVATE_USER"                <?php echo (($action_filter ?? '') === 'DEACTIVATE_USER')                ? 'selected' : ''; ?>>DEACTIVATE_USER</option>
                <option value="ADD_ITEM"                       <?php echo (($action_filter ?? '') === 'ADD_ITEM')                       ? 'selected' : ''; ?>>ADD_ITEM</option>
                <option value="UPDATE_ITEM"                    <?php echo (($action_filter ?? '') === 'UPDATE_ITEM')                    ? 'selected' : ''; ?>>UPDATE_ITEM</option>
                <option value="ARCHIVE_ITEM"                   <?php echo (($action_filter ?? '') === 'ARCHIVE_ITEM')                   ? 'selected' : ''; ?>>ARCHIVE_ITEM</option>
                <option value="RESTORE_ITEM"                   <?php echo (($action_filter ?? '') === 'RESTORE_ITEM')                   ? 'selected' : ''; ?>>RESTORE_ITEM</option>
                <option value="CONSUME_ITEM"                   <?php echo (($action_filter ?? '') === 'CONSUME_ITEM')                   ? 'selected' : ''; ?>>CONSUME_ITEM</option>
                <option value="CREATE_CATEGORY"                <?php echo (($action_filter ?? '') === 'CREATE_CATEGORY')                ? 'selected' : ''; ?>>CREATE_CATEGORY</option>
                <option value="UPDATE_CATEGORY"                <?php echo (($action_filter ?? '') === 'UPDATE_CATEGORY')                ? 'selected' : ''; ?>>UPDATE_CATEGORY</option>
                <option value="DEACTIVATE_CATEGORY"             <?php echo (($action_filter ?? '') === 'DEACTIVATE_CATEGORY')             ? 'selected' : ''; ?>>DEACTIVATE_CATEGORY</option>
                <option value="REACTIVATE_CATEGORY"             <?php echo (($action_filter ?? '') === 'REACTIVATE_CATEGORY')             ? 'selected' : ''; ?>>REACTIVATE_CATEGORY</option>
                <option value="CREATE_DEPT"                    <?php echo (($action_filter ?? '') === 'CREATE_DEPT')                    ? 'selected' : ''; ?>>CREATE_DEPT</option>
                <option value="UPDATE_DEPT"                    <?php echo (($action_filter ?? '') === 'UPDATE_DEPT')                    ? 'selected' : ''; ?>>UPDATE_DEPT</option>
                <option value="DEACTIVATE_DEPT"                 <?php echo (($action_filter ?? '') === 'DEACTIVATE_DEPT')                 ? 'selected' : ''; ?>>DEACTIVATE_DEPT</option>
                <option value="REACTIVATE_DEPT"                 <?php echo (($action_filter ?? '') === 'REACTIVATE_DEPT')                 ? 'selected' : ''; ?>>REACTIVATE_DEPT</option>
                <option value="DEACTIVATE_UNIT"                 <?php echo (($action_filter ?? '') === 'DEACTIVATE_UNIT')                 ? 'selected' : ''; ?>>DEACTIVATE_UNIT</option>
                <option value="REACTIVATE_UNIT"                 <?php echo (($action_filter ?? '') === 'REACTIVATE_UNIT')                 ? 'selected' : ''; ?>>REACTIVATE_UNIT</option>
                <option value="CREATE_SUPPLIER"                 <?php echo (($action_filter ?? '') === 'CREATE_SUPPLIER')                 ? 'selected' : ''; ?>>CREATE_SUPPLIER</option>
                <option value="UPDATE_SUPPLIER"                 <?php echo (($action_filter ?? '') === 'UPDATE_SUPPLIER')                 ? 'selected' : ''; ?>>UPDATE_SUPPLIER</option>
                <option value="DEACTIVATE_SUPPLIER"               <?php echo (($action_filter ?? '') === 'DEACTIVATE_SUPPLIER')               ? 'selected' : ''; ?>>DEACTIVATE_SUPPLIER</option>
                <option value="REACTIVATE_SUPPLIER"               <?php echo (($action_filter ?? '') === 'REACTIVATE_SUPPLIER')               ? 'selected' : ''; ?>>REACTIVATE_SUPPLIER</option>
                <option value="CREATE_SUPPLY_REQUEST"          <?php echo (($action_filter ?? '') === 'CREATE_SUPPLY_REQUEST')          ? 'selected' : ''; ?>>CREATE_SUPPLY_REQUEST</option>
                <option value="SERVE_SUPPLY_REQUEST"           <?php echo (($action_filter ?? '') === 'SERVE_SUPPLY_REQUEST')           ? 'selected' : ''; ?>>SERVE_SUPPLY_REQUEST</option>
                <option value="PARTIAL_SERVE_SUPPLY_REQUEST"   <?php echo (($action_filter ?? '') === 'PARTIAL_SERVE_SUPPLY_REQUEST')   ? 'selected' : ''; ?>>PARTIAL_SERVE_SUPPLY_REQUEST</option>
                <option value="COMPLETE_PARTIAL_SUPPLY_REQUEST" <?php echo (($action_filter ?? '') === 'COMPLETE_PARTIAL_SUPPLY_REQUEST') ? 'selected' : ''; ?>>COMPLETE_PARTIAL_SUPPLY_REQUEST</option>
                <option value="REJECT_SUPPLY_REQUEST"          <?php echo (($action_filter ?? '') === 'REJECT_SUPPLY_REQUEST')          ? 'selected' : ''; ?>>REJECT_SUPPLY_REQUEST</option>
                <option value="CANCEL_SUPPLY_REQUEST"          <?php echo (($action_filter ?? '') === 'CANCEL_SUPPLY_REQUEST')          ? 'selected' : ''; ?>>CANCEL_SUPPLY_REQUEST</option>
                <option value="ARCHIVE_SUPPLY_REQUEST"         <?php echo (($action_filter ?? '') === 'ARCHIVE_SUPPLY_REQUEST')         ? 'selected' : ''; ?>>ARCHIVE_SUPPLY_REQUEST</option>
                <option value="RESTORE_SUPPLY_REQUEST"         <?php echo (($action_filter ?? '') === 'RESTORE_SUPPLY_REQUEST')         ? 'selected' : ''; ?>>RESTORE_SUPPLY_REQUEST</option>
                <option value="UPDATE_PROFILE"                 <?php echo (($action_filter ?? '') === 'UPDATE_PROFILE')                 ? 'selected' : ''; ?>>UPDATE_PROFILE</option>
            </select>
            <label for="audit_search_action">Action</label>
        </div>
        <div class="db-search-actions">
            <button type="submit" class="btn-db-search" id="btnAuditSearch">
                 Search
            </button>
            <a href="<?php echo base_url('audit'); ?>" class="btn-db-clear" id="btnAuditClear">
                Clear
            </a>
        </div>
    </div>
</form>


<!-- Log Data Table Area -->
    <div class="table-responsive-custom" id="auditTableWrapper">
        <table class="table table-custom table-hover w-100" id="auditLogsTable">
            <thead>
                <tr>
                    <th style="width: 14%">Date/Time</th>
                    <th style="width: 10%">Action</th>
                    <th>Description</th>
                    <?php if (is_admin_role()): ?>
                        <th style="width: 10%">IP Address</th>
                        <th style="width: 12%">Device</th>
                    <?php endif; ?>
                    <th style="width: 10%">User</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr class="audit-log-row" data-log-id="<?php echo $log['log_id']; ?>">
                            <td data-order="<?php echo $log['created_at']; ?>">
                                <span class="text-dark" style="font-size: 0.9rem;">
                                    <?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php 
                                    $badge_class = 'bg-secondary';
                                    if ($log['action'] === 'LOGIN') {
                                        $badge_class = 'bg-success-subtle text-dark border border-success-subtle';
                                    } elseif ($log['action'] === 'LOGOUT') {
                                        $badge_class = 'bg-secondary-subtle text-dark border border-secondary-subtle';
                                    } elseif ($log['action'] === 'LOGIN_FAILED') {
                                        $badge_class = 'bg-danger-subtle text-dark border border-danger-subtle';
                                    } elseif ($log['action'] === 'PAGE_VIEW') {
                                        $badge_class = 'bg-info-subtle text-dark border border-info-subtle';
                                    }
                                ?>
                                <span class="badge badge-action rounded-pill <?php echo $badge_class; ?>">
                                    <?php echo $log['action']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-dark small d-block" style="word-break: break-word; white-space: normal;" title="<?php echo htmlspecialchars($log['description']); ?>">
                                    <?php echo htmlspecialchars($log['description']); ?>
                                </span>
                            </td>
                            <?php if (is_admin_role()): ?>
                            <td class="text-center">
                                <span class="text-dark small font-monospace">
                                    <?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-dark small" style="font-size: 0.75rem; word-break: break-word;" title="<?php echo htmlspecialchars($log['user_agent'] ?? ''); ?>">
                                    <?php 
                                        $ua = $log['user_agent'] ?? '';
                                        echo htmlspecialchars(!empty($ua) ? (strlen($ua) > 40 ? substr($ua, 0, 40) . '...' : $ua) : '-');
                                    ?>
                                </span>
                            </td>
                            <?php endif; ?>
                            <td class="text-center">
                                <span class="text-dark" style="font-size: 0.85rem;">
                                    <?php echo htmlspecialchars($log['username']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
