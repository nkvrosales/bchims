<?php
$requestStatusMap = [
    1 => 'Pending',
    2 => 'Partially Served',
    3 => 'Served',
    4 => 'Rejected',
    5 => 'Cancelled',
];
$requestBadgeMap = [
    1 => 'bg-warning-subtle text-dark border border-warning-subtle',
    2 => 'bg-primary-subtle text-dark border border-primary-subtle',
    3 => 'bg-success-subtle text-dark border border-success-subtle',
    4 => 'bg-danger-subtle text-dark border border-danger-subtle',
    5 => 'bg-secondary-subtle text-dark border border-secondary-subtle',
];
?>
    <!-- Page Title Section -->
    <div class="page-breadcrumb">
        <a href="<?php echo base_url('dashboard'); ?>">Dashboard</a>
        <span class="separator">/</span>
        <span class="current"><?php echo $title ?? (is_admin_role() ? 'Central Requests' : 'My Requests'); ?></span>
    </div>

    <div class="page-title-section fade-in-up">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h1 class="page-title mb-1"><?php echo $title ?? (is_admin_role() ? 'Central Requests' : 'My Requests'); ?></h1>
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

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 rounded-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-exclamation fs-5"></i>
                <span><?php echo session()->getFlashdata('error'); ?></span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Supply Requests Search Bar -->
    <form method="GET" action="<?php echo base_url('requests'); ?>" id="requestsSearchForm">
        <div class="db-search-bar">
            <div class="db-search-field db-search-field--keyword">
                <input
                    type="text"
                    id="req_search_keyword"
                    name="search"
                    class="db-search-input"
                    placeholder=" "
                    value="<?php echo htmlspecialchars($search ?? ''); ?>"
                    autocomplete="off"
                >
                <label for="req_search_keyword">Enter ID / Requester / Item</label>
            </div>
            <div class="db-search-field db-search-field--dropdown">
                <select id="req_search_status" name="status_filter" class="db-search-select">
                    <option value="">- Select Status -</option>
                    <option value="1" <?php echo (($status_filter ?? '') === '1') ? 'selected' : ''; ?>>Pending</option>
                    <option value="3" <?php echo (($status_filter ?? '') === '3') ? 'selected' : ''; ?>>Served</option>
                    <option value="2" <?php echo (($status_filter ?? '') === '2') ? 'selected' : ''; ?>>Partially Served</option>
                    <option value="4" <?php echo (($status_filter ?? '') === '4') ? 'selected' : ''; ?>>Rejected</option>
                    <option value="5" <?php echo (($status_filter ?? '') === '5') ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                <label for="req_search_status">Status</label>
            </div>
            <?php if (is_admin_role()): ?>
            <div class="db-search-field db-search-field--dropdown">
                <select id="req_search_dept" name="dept_filter" class="db-search-select">
                    <option value="">- Select Department -</option>
                    <?php if (!empty($departments)): ?>
                        <?php foreach ($departments as $d): ?>
                            <?php if (strtolower($d['name']) !== 'central supply'): ?>
                            <option value="<?php echo $d['id']; ?>" <?php echo (($dept_filter ?? '') === (string)$d['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['name']); ?>
                            </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <label for="req_search_dept">Department</label>
            </div>
            <?php endif; ?>
            <div class="db-search-actions">
                <button type="submit" class="btn-db-search" id="btnReqSearch">
                     Search
                </button>
                <a href="<?php echo base_url('requests'); ?>" class="btn-db-clear" id="btnReqClear">
                    Clear
                </a>
                <?php if (session()->get('role') === 'encoder'): ?>
                    <div class="db-search-separator"></div>
                    <button type="button"
                            class="btn btn-db-search d-inline-flex align-items-center gap-2"
                            id="btnNewSupplyRequest"
                            data-bs-toggle="modal"
                            data-bs-target="#createRequestModal">
                        <span>Request</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <!-- Supply Requests Table -->

        <div class="table-responsive-custom">
            <table class="table table-custom table-hover w-100" id="supplyRequestsTable">
                <thead>
                    <tr>
                        <th style="width: 7%">ID</th>
                        <th style="width: 11%">Request Date</th>
                        <th style="width: 11%" class="col-last-updated">Last Updated</th>
                        <th style="width: 11%">Requester</th>
                        <th style="width: 9%">Department</th>
                        <th style="width: 15%">Item Requested</th>
                        <th style="width: 9%">Quantity</th>
                        <th style="width: 7%">Unit</th>
                        <th style="width: 10%">Status</th>
                        <th style="width: 10%" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $req): ?>
                            <tr>
                                <td class="font-monospace" style="font-size: 0.85rem; color: var(--text-secondary);" data-order="<?php echo $req['request_id']; ?>">
                                    #<?php echo $req['request_id']; ?>
                                </td>
                                <td data-order="<?php echo htmlspecialchars($req['created_at'] ?? ''); ?>">
                                    <span class="text-dark"><?php echo !empty($req['created_at']) ? date('M j, Y h:i A', strtotime($req['created_at'])) : 'N/A'; ?></span>
                                </td>
                                <td class="col-last-updated" data-order="<?php echo htmlspecialchars($req['updated_at'] ?? $req['created_at'] ?? ''); ?>">
                                    <span class="text-dark"><?php echo !empty($req['updated_at']) ? date('M j, Y h:i A', strtotime($req['updated_at'])) : (!empty($req['created_at']) ? date('M j, Y h:i A', strtotime($req['created_at'])) : 'N/A'); ?></span>
                                </td>
                                <td>
                                    <div class="text-dark"><?php echo htmlspecialchars($req['requester_full_name']); ?></div>
                                </td>
                                <td>
                                    <span class="text-dark"><?php echo htmlspecialchars($req['department_name'] ?? 'N/A'); ?></span>
                                </td>
                                <td>
                                    <div class="text-dark"><?php echo htmlspecialchars($req['item_name']); ?></div>
                                </td>
                                <td>
                                    <?php $servedQty = (int)($req['quantity_served'] ?? 0); ?>
                                    <div>
                                        <span class="text-dark" title="Served Quantity"><?php echo $servedQty; ?></span> / <span class="text-dark" title="Requested Quantity"><?php echo $req['quantity_requested']; ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-dark"><?php echo htmlspecialchars($req['item_unit'] ?? ''); ?></span>
                                </td>
                                <td data-order="<?php echo $req['request_status']; ?>">
                                    <?php $badge = $requestBadgeMap[$req['request_status']] ?? 'bg-warning-subtle text-dark border border-warning-subtle'; ?>
                                    <span class="badge badge-action rounded-pill <?php echo $badge; ?>">
                                        <?php echo $requestStatusMap[$req['request_status']] ?? 'Unknown'; ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <?php if (($req['status'] ?? 1) == 0): ?>
                                        <?php if (is_admin_role()): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown" style="padding: 4px 12px; font-size: 0.75rem; font-weight: 600;">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" style="font-size: 0.8rem;">
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#restoreSingleModal_<?php echo $req['request_id']; ?>" id="btnTriggerRestore_<?php echo $req['request_id']; ?>" title="Restore Request">Restore</a></li>
                                            </ul>
                                        </div>
                                        <?php endif; ?>
                                    <?php elseif (session()->get('role') === 'encoder' && $req['request_status'] == 1): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown" style="padding: 4px 12px; font-size: 0.75rem; font-weight: 600;">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" style="font-size: 0.8rem;">
                                                <li><a class="dropdown-item btn-edit-request-trigger" href="#" data-bs-toggle="modal" data-bs-target="#createRequestModal" data-mode="edit" data-id="<?php echo $req['request_id']; ?>" data-category="<?php echo $req['category_id'] ?? ''; ?>" data-item-id="<?php echo $req['central_supply_id']; ?>" data-item-name="<?php echo htmlspecialchars($req['item_name']); ?>" data-qty="<?php echo $req['quantity_requested']; ?>" data-unit="<?php echo htmlspecialchars($req['item_unit'] ?? ''); ?>" data-notes="<?php echo htmlspecialchars($req['notes'] ?? ''); ?>" title="Manage Request">Manage</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#cancelModal_<?php echo $req['request_id']; ?>" title="Cancel Request">Cancel</a></li>
                                            </ul>
                                        </div>
                                    <?php elseif (session()->get('role') === 'encoder' && $req['request_status'] != 1): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown" style="padding: 4px 12px; font-size: 0.75rem; font-weight: 600;">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" style="font-size: 0.8rem;">
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewModal_<?php echo $req['request_id']; ?>" title="View Details">View</a></li>
                                                <?php if ((int)$req['request_status'] === 2): ?>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#cancelModal_<?php echo $req['request_id']; ?>" title="Cancel Request">Cancel</a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    <?php elseif (is_admin_role() && $req['request_status'] == 1): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown" style="padding: 4px 12px; font-size: 0.75rem; font-weight: 600;">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" style="font-size: 0.8rem;">
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewModal_<?php echo $req['request_id']; ?>" id="btnTriggerView_<?php echo $req['request_id']; ?>" title="View Details">View</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#serveModal_<?php echo $req['request_id']; ?>" id="btnTriggerServe_<?php echo $req['request_id']; ?>" title="Serve Request">Serve</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#partialModal_<?php echo $req['request_id']; ?>" id="btnTriggerPartial_<?php echo $req['request_id']; ?>" title="Serve Partially">Partial</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#rejectModal_<?php echo $req['request_id']; ?>" id="btnTriggerReject_<?php echo $req['request_id']; ?>" title="Reject Request">Reject</a></li>
                                            </ul>
                                        </div>
                                    <?php elseif (is_admin_role() && $req['request_status'] == 2): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown" style="padding: 4px 12px; font-size: 0.75rem; font-weight: 600;">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" style="font-size: 0.8rem;">
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewModal_<?php echo $req['request_id']; ?>" id="btnTriggerView_<?php echo $req['request_id']; ?>" title="View Details">View</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#completePartialModal_<?php echo $req['request_id']; ?>" id="btnTriggerCompletePartial_<?php echo $req['request_id']; ?>" title="Complete Partially Served Request">Complete</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#partialModal_<?php echo $req['request_id']; ?>" id="btnTriggerPartial_<?php echo $req['request_id']; ?>" title="Serve Partially">Partial</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#cancelModal_<?php echo $req['request_id']; ?>" title="Cancel Request">Cancel</a></li>
                                            </ul>
                                        </div>
                                    <?php elseif (is_admin_role() && $req['request_status'] != 1): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown" style="padding: 4px 12px; font-size: 0.75rem; font-weight: 600;">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" style="font-size: 0.8rem;">
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewModal_<?php echo $req['request_id']; ?>" id="btnTriggerView_<?php echo $req['request_id']; ?>" title="View Details">View</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#archiveSingleModal_<?php echo $req['request_id']; ?>" id="btnTriggerArchive_<?php echo $req['request_id']; ?>" title="Archive Request">Archive</a></li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php if (is_admin_role() && !empty($requests)): ?>
        <?php foreach ($requests as $req): ?>
            <?php if ($req['request_status'] == 1 || $req['request_status'] == 2): ?>
                <!-- Serve Modal -->
                <div class="modal fade" id="serveModal_<?php echo $req['request_id']; ?>" tabindex="-1" aria-labelledby="serveModalLabel_<?php echo $req['request_id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-md">
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                            <div class="modal-header border-bottom px-4">
                                <h5 class="modal-title fw-bold text-dark" id="serveModalLabel_<?php echo $req['request_id']; ?>">Serve Supply Request</h5>
                                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                            </div>
                            <form method="POST" action="<?php echo base_url('requests/serve/' . $req['request_id']); ?>">
                                <div class="modal-body px-4 py-4 text-center">
                                    <?php if (session()->getFlashdata('open_modal') === 'serveModal_' . $req['request_id'] && $modal_errors = session()->getFlashdata('modal_errors')): ?>
                                    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-4 py-3">
                                        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close" style="font-size: 0.75rem; top: 0.5rem; right: 0.5rem;"></button>
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                                            <div>
                                                <span class="fw-bold d-block mb-1">Please correct the errors below:</span>
                                                <div class="small"><?php echo $modal_errors; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <p class="text-muted small mb-3">
                                        Transfer <strong><?php echo $req['quantity_requested']; ?> <?php echo htmlspecialchars($req['item_unit'] ?? 'pcs'); ?></strong> of <strong><?php echo htmlspecialchars($req['item_name']); ?></strong>
                                        to <strong><?php echo htmlspecialchars($req['requester_full_name']); ?></strong> (<?php echo htmlspecialchars($req['department_name'] ?? ''); ?>).
                                    </p>
                                    <div class="d-flex justify-content-center gap-3 small mb-3">
                                        <div class="text-center">
                                            <div class="fw-bold text-dark"><?php echo $req['quantity_requested']; ?></div>
                                            <div class="text-muted">Requested</div>
                                        </div>
                                    </div>
                                    <div class="text-muted mb-3">
                                       Are you sure you want to serve this supply request?
                                    </div>
                                </div>
                                <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                                    <button type="button"
                                            data-bs-dismiss="modal"
                                            style="background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                            onmouseover="this.style.background='#f9fafb'"
                                            onmouseout="this.style.background='#fff'">
                                        Close
                                    </button>
                                    <button type="submit"
                                            style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; height: 38px;"
                                            onmouseover="this.style.background='#059669'"
                                            onmouseout="this.style.background='#10b981'">
                                        Serve Supplies
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($req['request_status'] == 1): ?>
                <!-- Reject Modal -->
                <div class="modal fade" id="rejectModal_<?php echo $req['request_id']; ?>" tabindex="-1" aria-labelledby="rejectModalLabel_<?php echo $req['request_id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                            <div class="modal-header border-bottom px-4">
                                <h5 class="modal-title fw-bold text-dark" id="rejectModalLabel_<?php echo $req['request_id']; ?>">Reject Request</h5>
                                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                            </div>
                            <form method="POST" action="<?php echo base_url('requests/reject/' . $req['request_id']); ?>">
                                <div class="modal-body px-4 py-4 text-center">
                                    <h5 class="fw-semibold text-dark mb-2">Reject This Request?</h5>
                                    <p class="text-muted small mb-0">
                                        This will mark request <strong>#<?php echo $req['request_id']; ?></strong> from
                                        <strong><?php echo htmlspecialchars($req['requester_full_name']); ?></strong>
                                        as <strong>Rejected</strong>.
                                    </p>
                                    <div class="mb-3 text-start">
                                        <label for="reject_notes_<?php echo $req['request_id']; ?>" class="form-label small fw-semibold text-secondary">Remarks</label>
                                        <textarea class="form-control input-custom"
                                                id="reject_notes_<?php echo $req['request_id']; ?>"
                                                name="reject_notes"
                                                rows="3"
                                                placeholder="Remarks about this rejection."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                                    <button type="button"
                                            data-bs-dismiss="modal"
                                            style="background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                            onmouseover="this.style.background='#f9fafb'"
                                            onmouseout="this.style.background='#fff'">
                                        Close
                                    </button>
                                    <button type="submit"
                                            style="background: #ef4444; color: #fff; border: 1px solid transparent; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; height: 38px;"
                                            onmouseover="this.style.background='#dc2626'"
                                            onmouseout="this.style.background='#ef4444'">
                                        Reject Request
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($req['request_status'] == 1 || $req['request_status'] == 2): ?>
                <?php
                    $remaining = $req['quantity_requested'] - $req['quantity_served'];
                    $partialMax = $remaining > 0 ? $remaining - 1 : 0;
                ?>
                <!-- Partial Serve Modal -->
                <div class="modal fade" id="partialModal_<?php echo $req['request_id']; ?>" tabindex="-1" aria-labelledby="partialModalLabel_<?php echo $req['request_id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                            <div class="modal-header border-bottom px-4">
                                <h5 class="modal-title fw-bold text-dark" id="partialModalLabel_<?php echo $req['request_id']; ?>">Partially Serve Request</h5>
                                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                            </div>
                            <form method="POST" action="<?php echo base_url('requests/partial/' . $req['request_id']); ?>">
                                <div class="modal-body px-4 py-4">
                                    <?php if (session()->getFlashdata('open_modal') === 'partialModal_' . $req['request_id'] && $modal_errors = session()->getFlashdata('modal_errors')): ?>
                                    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-4 py-3">
                                        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close" style="font-size: 0.75rem; top: 0.5rem; right: 0.5rem;"></button>
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                                            <div>
                                                <span class="fw-bold d-block mb-1">Please correct the errors below:</span>
                                                <div class="small"><?php echo $modal_errors; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <div class="text-center mb-3">
                                        <h6 class="fw-semibold text-dark">Specify Quantity to Serve</h6>
                                        <p class="text-muted small mb-0">
                                            Requested: <strong><?php echo $req['quantity_requested']; ?> <?php echo htmlspecialchars($req['item_unit'] ?? 'pcs'); ?></strong> of <strong><?php echo htmlspecialchars($req['item_name']); ?></strong>.<br>
                                            <?php if ((int)$req['quantity_served'] > 0): ?>
                                                Already Served: <strong><?php echo $req['quantity_served']; ?> <?php echo htmlspecialchars($req['item_unit'] ?? 'pcs'); ?></strong> &mdash; Remaining: <strong><?php echo $remaining; ?> <?php echo htmlspecialchars($req['item_unit'] ?? 'pcs'); ?></strong>.<br>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="mb-3 small text-muted">
                                        Specify quantities from each inventory batch below. The total will be served as a partial.
                                    </div>
                                    <div class="mb-3">
                                        <div class="row g-2 mb-2 d-none d-md-flex">
                                            <div class="col-lg-7">
                                                <label class="form-label small fw-semibold text-secondary">Select Inventory <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label small fw-semibold text-secondary">QTY <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-lg-3">
                                                <label class="form-label small fw-semibold text-secondary">Action</label>
                                            </div>
                                        </div>

                                        <div class="partial-batch-rows" data-request-id="<?php echo $req['request_id']; ?>">
                                            <div class="partial-batch-row row g-2 align-items-end mb-2 pb-2 border-bottom border-light-subtle">
                                                <!-- Inventory dropdown -->
                                                <div class="col-lg-7 col-12">
                                                    <label class="form-label small fw-semibold text-secondary d-md-none">Select Inventory <span class="text-danger">*</span></label>
                                                    <select class="form-select input-custom partial-batch-select" name="central_supply_id[]" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                                                        <option value="" disabled selected hidden>Select Inventory</option>
                                                        <?php $batches = $batches_by_code[$req['item_name']] ?? []; ?>
                                                        <?php $unitBatches = array_filter($batches, function($b) use ($req) { return $b['unit'] === ($req['item_unit'] ?? ''); }); ?>
                                                        <?php if (empty($unitBatches)): ?>
                                                        <option value="" disabled>No available stock</option>
                                                        <?php else: ?>
                                                        <?php foreach ($unitBatches as $batch): ?>
                                                        <option value="<?php echo $batch['central_supply_id']; ?>">
                                                            <?php echo htmlspecialchars($batch['item_name']); ?> (<?php echo htmlspecialchars($batch['inventory_code']); ?>) &mdash; Exp: <?php echo $batch['expiration_date'] ? date('M j, Y', strtotime($batch['expiration_date'])) : 'N/A'; ?> &mdash; Avail: <?php echo (int)$batch['quantity_on_hand']; ?> <?php echo htmlspecialchars($batch['unit'] ?? ''); ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                                <!-- Qty -->
                                                <div class="col-lg-2 col-12">
                                                    <label class="form-label small fw-semibold text-secondary d-md-none">QTY <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control input-custom partial-batch-qty" name="quantity[]" min="1" value="1" required placeholder="QTY" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                                                </div>
                                                <!-- Actions -->
                                                <div class="col-lg-3 col-12 d-flex gap-2 align-items-center justify-content-end justify-content-md-start">
                                                    <button type="button" onclick="batchRow.add(this)" class="btn btn-add-partial-batch d-flex align-items-center gap-1" style="background: #10b981; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; transition: background 0.15s; height: 42px;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                                                        <span>ADD</span>
                                                    </button>
                                                    <button type="button" onclick="batchRow.remove(this)" class="btn-remove-partial-batch btn btn-link text-decoration-none d-flex align-items-center gap-1 p-0" style="font-size: 0.9rem; color: #64748b; cursor: pointer; transition: color 0.15s; border: none; background: none; outline: none; height: 42px; display: none;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#64748b'">
                                                        <i class="fa-regular fa-trash-can"></i> <span>Remove</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="partial_notes_<?php echo $req['request_id']; ?>" class="form-label small fw-semibold text-secondary">Remarks</label>
                                        <textarea class="form-control input-custom"
                                                id="partial_notes_<?php echo $req['request_id']; ?>" 
                                                name="partial_notes" 
                                                rows="3" 
                                                placeholder="Remarks about this partial serve."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                                    <button type="button"
                                            data-bs-dismiss="modal"
                                            style="background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                            onmouseover="this.style.background='#f9fafb'"
                                            onmouseout="this.style.background='#fff'">
                                        Close
                                    </button>
                                    <button type="submit"
                                            style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; height: 38px;"
                                            onmouseover="this.style.background='#059669'"
                                            onmouseout="this.style.background='#10b981'">
                                        Serve Partial
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($req['request_status'] == 2): ?>
                <!-- Complete Partial Serve Modal -->
                <div class="modal fade" id="completePartialModal_<?php echo $req['request_id']; ?>" tabindex="-1" aria-labelledby="completePartialModalLabel_<?php echo $req['request_id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-md">
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                            <div class="modal-header border-bottom px-4">
                                <h5 class="modal-title fw-bold text-dark" id="completePartialModalLabel_<?php echo $req['request_id']; ?>">Complete Partially Served Request</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" action="<?php echo base_url('requests/complete_partial/' . $req['request_id']); ?>">
                                <div class="modal-body px-4 py-4 text-center">
                                    <?php if (session()->getFlashdata('open_modal') === 'completePartialModal_' . $req['request_id'] && $modal_errors = session()->getFlashdata('modal_errors')): ?>
                                    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-4 py-3">
                                        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close" style="font-size: 0.75rem; top: 0.5rem; right: 0.5rem;"></button>
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                                            <div>
                                                <span class="fw-bold d-block mb-1">Please correct the errors below:</span>
                                                <div class="small"><?php echo $modal_errors; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <?php $remaining = $req['quantity_requested'] - $req['quantity_served']; ?>
                                    <h5 class="fw-semibold text-dark mb-2">Serve Remaining Quantity</h5>
                                    <p class="text-muted small mb-3">
                                        This request has already been partially served.<br>
                                        Requested: <strong><?php echo $req['quantity_requested']; ?></strong> <?php echo htmlspecialchars($req['item_unit'] ?? 'pcs'); ?>, Already Served: <strong><?php echo $req['quantity_served']; ?></strong> <?php echo htmlspecialchars($req['item_unit'] ?? 'pcs'); ?>.<br>
                                        Remaining to serve: <strong><?php echo $remaining; ?></strong> <?php echo htmlspecialchars($req['item_unit'] ?? 'pcs'); ?> of <strong><?php echo htmlspecialchars($req['item_name']); ?></strong> to <strong><?php echo htmlspecialchars($req['requester_full_name']); ?></strong> (<?php echo htmlspecialchars($req['department_name'] ?? ''); ?>).<br>
                                    </p>
                                    <p class="text-muted mb-3">
                                        Are you sure you want to complete this supply request?
                                    </p>
                                </div>
                                <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                                    <button type="button"
                                            data-bs-dismiss="modal"
                                            style="background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                            onmouseover="this.style.background='#f9fafb'"
                                            onmouseout="this.style.background='#fff'">
                                        Close
                                    </button>
                                    <button type="submit"
                                            style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; height: 38px;"
                                            onmouseover="this.style.background='#059669'"
                                            onmouseout="this.style.background='#10b981'">
                                        Complete Request
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- View Request Details Modals (For all roles) -->
    <?php if (!empty($requests)): ?>
        <?php foreach ($requests as $req): ?>
            <div class="modal fade" id="viewModal_<?php echo $req['request_id']; ?>" tabindex="-1" aria-labelledby="viewModalLabel_<?php echo $req['request_id']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                        <div class="modal-header border-bottom px-4">
                            <h5 class="modal-title fw-bold text-dark" id="viewModalLabel_<?php echo $req['request_id']; ?>">
                                Request Details
                            </h5>
                                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                        </div>
                        <div class="modal-body px-4 py-4">
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="small fw-semibold text-secondary d-block">Request ID</label>
                                    <span class="fw-bold text-dark">#<?php echo $req['request_id']; ?></span>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-semibold text-secondary d-block">Status</label>
                                    <?php $badge = $requestBadgeMap[$req['request_status']] ?? 'bg-warning-subtle text-dark border border-warning-subtle'; ?>
                                    <span class="badge rounded-pill <?php echo $badge; ?>"><?php echo $requestStatusMap[$req['request_status']] ?? 'Unknown'; ?></span>
                                </div>
                                <div class="col-12"><hr class="my-1"></div>
                                <div class="col-6">
                                    <label class="small fw-semibold text-secondary d-block">Requester</label>
                                    <span class="text-dark"><?php echo htmlspecialchars($req['requester_full_name'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-semibold text-secondary d-block">Department</label>
                                    <span class="text-dark"><?php echo htmlspecialchars($req['department_name'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="col-12">
                                    <label class="small fw-semibold text-secondary d-block">Item Name</label>
                                    <span class="text-dark fw-medium"><?php echo htmlspecialchars($req['item_name'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-semibold text-secondary d-block">Requested Quantity</label>
                                    <span class="text-dark fw-bold"><?php echo $req['quantity_requested']; ?> <?php echo htmlspecialchars($req['item_unit'] ?? 'pcs'); ?></span>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-semibold text-secondary d-block">Served Quantity</label>
                                    <span class="text-dark fw-bold"><?php echo $req['quantity_served'] ?? 0; ?> <?php echo htmlspecialchars($req['item_unit'] ?? 'pcs'); ?></span>
                                </div>
                                <div class="col-12"><hr class="my-1"></div>
                                <div class="col-6">
                                    <label class="small fw-semibold text-secondary d-block">Request Date</label>
                                    <span class="text-dark small"><?php echo $req['request_date'] ? date('M d, Y h:i A', strtotime($req['request_date'])) : 'N/A'; ?></span>
                                </div>
                                <?php if (!empty($req['served_date'])): ?>
                                <div class="col-6">
                                    <label class="small fw-semibold text-secondary d-block">Served Date</label>
                                    <span class="text-dark small"><?php echo date('M d, Y h:i A', strtotime($req['served_date'])); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($req['partial_date'])): ?>
                                <div class="col-6">
                                    <label class="small fw-semibold text-secondary d-block">Partial Serve Date</label>
                                    <span class="text-dark small"><?php echo date('M d, Y h:i A', strtotime($req['partial_date'])); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($req['cancelled_date'])): ?>
                                <div class="col-6">
                                    <label class="small fw-semibold text-secondary d-block">Cancelled Date</label>
                                    <span class="text-dark small"><?php echo date('M d, Y h:i A', strtotime($req['cancelled_date'])); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($req['closed_date'])): ?>
                                <div class="col-6">
                                    <label class="small fw-semibold text-secondary d-block">Closed Date</label>
                                    <span class="text-dark small"><?php echo date('M d, Y h:i A', strtotime($req['closed_date'])); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($req['notes'])): ?>
                                <div class="col-12"><hr class="my-1"></div>
                                <div class="col-12">
                                    <label class="small fw-semibold text-secondary d-block mb-1">Notes</label>
                                    <div class="bg-light rounded-3 p-3 border" style="white-space: pre-line; font-size: 0.95rem; color: #1f2937; line-height: 1.6;">
                                        <?php echo htmlspecialchars($req['notes']); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                            <button type="button"
                                    data-bs-dismiss="modal"
                                    style="background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                    onmouseover="this.style.background='#f9fafb'"
                                    onmouseout="this.style.background='#fff'">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ((session()->get('role') === 'encoder' && in_array((int)$req['request_status'], [1, 2], true)) || (is_admin_role() && $req['request_status'] == 2)): ?>
                <!-- Cancel Modal -->
                <div class="modal fade" id="cancelModal_<?php echo $req['request_id']; ?>" tabindex="-1" aria-labelledby="cancelModalLabel_<?php echo $req['request_id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                            <div class="modal-header border-bottom px-4">
                                <h5 class="modal-title fw-bold text-dark" id="cancelModalLabel_<?php echo $req['request_id']; ?>">Cancel Request</h5>
                                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                            </div>
                            <form method="POST" action="<?php echo base_url('requests/cancel/' . $req['request_id']); ?>">
                                <div class="modal-body px-4 py-4">
                                    <div class="text-center mb-3">
                                        <p class="text-muted small mb-0">
                                            Are you sure you want to cancel request <strong>#<?php echo $req['request_id']; ?></strong> for <strong><?php echo $req['quantity_requested']; ?></strong> unit(s) of <strong><?php echo htmlspecialchars($req['item_name']); ?></strong>? This action cannot be undone.
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cancel_notes_<?php echo $req['request_id']; ?>" class="form-label small fw-semibold text-secondary">Reason</label>
                                        <textarea class="form-control input-custom"
                                                  id="cancel_notes_<?php echo $req['request_id']; ?>"
                                                  name="cancel_notes"
                                                  rows="3"
                                                   placeholder="Reason for cancellation..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                                    <button type="button"
                                            data-bs-dismiss="modal"
                                            style="background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                            onmouseover="this.style.background='#f9fafb'"
                                            onmouseout="this.style.background='#fff'">Close</button>
                                    <button type="submit"
                                            style="background: #ef4444; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; height: 38px;"
                                            onmouseover="this.style.background='#dc2626'"
                                            onmouseout="this.style.background='#ef4444'">Cancel Request</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

            <!-- Archive Confirmation Modals (Admin only) — exclude pending & partially served -->
            <?php if (is_admin_role() && !empty($requests)): ?>
                <?php foreach ($requests as $req): ?>
                    <?php if (!in_array((int)$req['request_status'], [1, 2], true)): ?>
                    <div class="modal fade" id="archiveSingleModal_<?php echo $req['request_id']; ?>" tabindex="-1" aria-labelledby="archiveSingleModalLabel_<?php echo $req['request_id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                                <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                                    <div class="d-flex align-items-center gap-3">
                                        <h5 class="modal-title fw-bold mb-0" id="archiveSingleModalLabel_<?php echo $req['request_id']; ?>" style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                                            Archive Request
                                        </h5>
                                    </div>
                                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                                </div>
                                <form method="POST" action="<?php echo base_url('requests/archive/' . $req['request_id']); ?>">
                                    <div class="modal-body px-4 py-4">
                                        <div class="p-3 bg-light rounded-3 border border-light-subtle mb-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div>
                                                    <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                                        #<?php echo $req['request_id']; ?> - <?php echo htmlspecialchars($req['item_name']); ?>
                                                    </div>
                                                    <div class="text-muted small">Code: <?php echo htmlspecialchars($req['item_code']); ?> &middot; Requested by: <?php echo htmlspecialchars($req['requester_full_name']); ?> (<?php echo htmlspecialchars($req['department_name'] ?? ''); ?>)</div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-secondary mb-0" style="font-size: 0.925rem; line-height: 1.5;">Are you sure you want to archive this supply request?</p>
                                    </div>
                                    <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                                        <button type="button"
                                                data-bs-dismiss="modal"
                                                style="background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                                onmouseover="this.style.background='#f9fafb'"
                                                onmouseout="this.style.background='#fff'">
                                            Close
                                        </button>
                                        <button type="submit"
                                                style="background: #ef4444; color: #fff; border: 1px solid transparent; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; height: 38px;"
                                                onmouseover="this.style.background='#dc2626'"
                                                onmouseout="this.style.background='#ef4444'">
                                            Archive Request
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Restore Confirmation Modals (Admin only) -->
            <?php if (is_admin_role() && !empty($requests)): ?>
                <?php foreach ($requests as $req): ?>
                    <?php if (($req['status'] ?? 1) == 0): ?>
                    <div class="modal fade" id="restoreSingleModal_<?php echo $req['request_id']; ?>" tabindex="-1" aria-labelledby="restoreSingleModalLabel_<?php echo $req['request_id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                                <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                                    <div class="d-flex align-items-center gap-3">
                                        <h5 class="modal-title fw-bold mb-0" id="restoreSingleModalLabel_<?php echo $req['request_id']; ?>" style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                                            Restore Request
                                        </h5>
                                    </div>
                                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                                </div>
                                <form method="POST" action="<?php echo base_url('requests/restore/' . $req['request_id']); ?>">
                                    <div class="modal-body px-4 py-4">
                                        <div class="p-3 bg-light rounded-3 border border-light-subtle mb-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div>
                                                    <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                                        #<?php echo $req['request_id']; ?> - <?php echo htmlspecialchars($req['item_name']); ?>
                                                    </div>
                                                    <div class="text-muted small">Code: <?php echo htmlspecialchars($req['item_code']); ?> &middot; Requested by: <?php echo htmlspecialchars($req['requester_full_name']); ?> (<?php echo htmlspecialchars($req['department_name'] ?? ''); ?>)</div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-secondary mb-0" style="font-size: 0.925rem; line-height: 1.5;">Are you sure you want to restore this supply request?</p>
                                    </div>
                                    <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                                        <button type="button"
                                                data-bs-dismiss="modal"
                                                style="background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                                onmouseover="this.style.background='#f9fafb'"
                                                onmouseout="this.style.background='#fff'">
                                            Close
                                        </button>
                                        <button type="submit"
                                                style="background: #10b981; color: #fff; border: 1px solid transparent; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; height: 38px;"
                                                onmouseover="this.style.background='#059669'"
                                                onmouseout="this.style.background='#10b981'">
                                            Restore Request
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

    <?php if (session()->get('role') === 'encoder'): ?>
    <!-- ===================== NEW SUPPLY REQUEST MODAL ===================== -->
    <div class="modal fade" id="createRequestModal" tabindex="-1" aria-labelledby="createRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: visible; background: #fff;">

                <!-- Modal Header -->
                <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem; background: #fff;">
                    <div class="d-flex align-items-center">
                        <h5 class="modal-title fw-bold mb-0" id="createRequestModalLabel" style="color: #0f172a; font-size: 1.25rem; letter-spacing: -0.01em;">
                            Create Supply Request
                        </h5>
                    </div>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                </div>

                <!-- Form -->
                <form method="POST" action="<?php echo base_url('requests/create'); ?>" id="supplyRequestForm">
                    <div class="modal-body px-4 py-4" style="overflow: visible;">

                        <!-- Validation Errors -->
                        <?php if ($create_errors = session()->getFlashdata('create_request_validation_errors')): ?>
                        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-4 py-3 shadow-sm">
                            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close" style="font-size: 0.75rem; top: 0.5rem; right: 0.5rem;"></button>
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                                <div>
                                    <span class="fw-bold d-block mb-1">Please correct the errors below:</span>
                                    <div class="small"><?php echo $create_errors; ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Inline validation alert (hidden by default) -->
                        <div id="createRequestAlert" class="alert alert-danger border-0 rounded-3 mb-4 py-3 shadow-sm" style="display: none;">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                                <div>
                                    <span class="fw-bold d-block mb-1">Please correct the errors below:</span>
                                    <div class="small">Item not found. Please choose from the available items in the dropdown.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Column Labels for Create Mode -->
                        <div class="row g-3 mb-2 d-none d-md-flex" id="create-modal-headers">
                            <div class="col-lg-3">
                                <label class="form-label small fw-semibold text-secondary">Category</label>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label small fw-semibold text-secondary">Item </span></label>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label small fw-semibold text-secondary">Unit</label>
                            </div>
                            <div class="col-lg-1">
                                <label class="form-label small fw-semibold text-secondary">QTY </span></label>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label small fw-semibold text-secondary">Action</label>
                            </div>
                        </div>

                        <!-- Column Labels for Edit Mode -->
                        <div class="row g-3 mb-2 d-none" id="edit-modal-headers">
                            <div class="col-lg-3">
                                <label class="form-label small fw-semibold text-secondary">Category</label>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label small fw-semibold text-secondary">Item </span></label>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label small fw-semibold text-secondary">Unit</label>
                            </div>
                            <div class="col-lg-1">
                                <label class="form-label small fw-semibold text-secondary">QTY </span></label>
                            </div>
                        </div>

                        <!-- Dynamic Request Items Rows -->
                        <div id="request-items-container" class="d-flex flex-column gap-1">
                            <!-- Rows will be dynamically appended here via JS -->
                        </div>

                        <!-- Details/Notes -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <label for="modal_notes" class="form-label small fw-semibold text-secondary">Details</label>
                                <textarea class="form-control input-custom"
                                        id="modal_notes"
                                        name="notes"
                                        rows="3"
                                        placeholder="Details..."
                                        style="resize: none; background: #fff; border-radius: 8px; border-color: #cbd5e1 !important;"
                                        ><?php echo old('notes'); ?></textarea>
                            </div>
                        </div>

                    </div><!-- /.modal-body -->

                    <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2" style="background: #fff;">
                        <button type="button"
                                data-bs-dismiss="modal"
                                style="background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                onmouseover="this.style.background='#f9fafb'"
                                onmouseout="this.style.background='#fff'">
                            Close
                        </button>
                        <button type="submit"
                                style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; height: 38px;"
                                onmouseover="this.style.background='#059669'"
                                onmouseout="this.style.background='#10b981'"
                                id="btnSubmitSupplyRequest">
                            Submit Request
                        </button>
                    </div>
                </form>
    <?php $items_json = json_encode($items); ?>
    <?php $categories_json = json_encode($categories); ?>
    <script>
    var allItems = <?php echo $items_json; ?>;
    var categories = <?php echo $categories_json; ?>;
    var unitsByItemName = <?php echo json_encode($units_by_name ?? []); ?>;

    document.addEventListener('DOMContentLoaded', function () {
        var container = document.getElementById('request-items-container');

        if (container) {
            // Add the initial row
            addNewRow();
        }

        function addNewRow() {
            var rowId = 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            var rowHtml = `
            <div class="request-item-row row g-2 align-items-end mb-2 pb-2 border-bottom border-light-subtle" id="${rowId}">
                <!-- Category select -->
                <div class="col-lg-3 col-12">
                    <label class="form-label small fw-semibold text-secondary d-md-none">Category</label>
                    <select class="form-select input-custom row-category-select" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                        <option value="">All Categories</option>
                        ${categories.map(c => `<option value="${c.category_id}">${escapeHtml(c.category_code + ' - ' + c.category_description)}</option>`).join('')}
                    </select>
                </div>

                <!-- Item search combobox -->
                <div class="col-lg-4 col-12">
                    <label class="form-label small fw-semibold text-secondary d-md-none">Item Name <span class="text-danger">*</span></label>
                    <div class="item-combobox">
                        <div class="position-relative">
                            <input type="text" class="form-control input-custom row-item-search" placeholder="Select Item" autocomplete="off" style="border-radius: 8px; border-color: #cbd5e1; height: 42px; padding-right: 30px;" required>
                            <input type="hidden" name="item_id[]" class="row-item-id">
                            <i class="fa-solid fa-xmark position-absolute top-50 end-0 translate-middle-y me-3 row-item-clear" style="color: #9ca3af; font-size: 0.9rem; cursor: pointer; display: none;"></i>
                        </div>
                        <div class="item-dropdown row-item-dropdown" style="display: none;">
                            <div class="item-dropdown-inner">
                                <div class="text-muted text-center py-3 small row-item-empty">No items found</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unit combobox -->
                <div class="col-lg-2 col-12">
                    <label class="form-label small fw-semibold text-secondary d-md-none">Unit</label>
                    <div class="unit-combobox position-relative">
                        <input type="text" class="form-control input-custom row-unit-search" name="unit[]" placeholder="Select Unit" autocomplete="off" style="border-radius: 8px; border-color: #cbd5e1; height: 42px; padding-right: 30px;">
                        <i class="fa-solid fa-xmark position-absolute top-50 end-0 translate-middle-y me-3 row-unit-clear" style="color: #9ca3af; font-size: 0.9rem; cursor: pointer; display: none;"></i>
                        <div class="unit-dropdown row-unit-dropdown" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:9999; background:#fff; border:1px solid #e2e8f0; border-radius:8px; box-shadow:0 4px 16px rgba(0,0,0,0.1); margin-top:4px; max-height:180px; overflow-y:auto;">
                            <div class="unit-dropdown-inner"></div>
                        </div>
                    </div>
                </div>

                <!-- Qty -->
                <div class="col-lg-1 col-12">
                    <label class="form-label small fw-semibold text-secondary d-md-none">QTY <span class="text-danger">*</span></label>
                    <input type="number" class="form-control input-custom row-quantity-input" name="quantity[]" min="1" value="1" required placeholder="QTY" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                </div>

                <!-- Add/Remove Actions -->
                <div class="request-item-actions col-lg-2 col-12 d-flex gap-2 align-items-center justify-content-end justify-content-md-start">
                    <button type="button" class="btn btn-add-row d-flex align-items-center gap-1" style="background: #10b981; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; transition: background 0.15s; height: 42px;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                        <span>ADD</span>
                    </button>
                    <button type="button" class="btn-remove-row btn btn-link text-decoration-none d-flex align-items-center gap-1 p-0" style="font-size: 0.9rem; color: #64748b; cursor: pointer; transition: color 0.15s; border: none; background: none; outline: none; height: 42px;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#64748b'">
                        <i class="fa-regular fa-trash-can"></i> <span>Remove</span>
                    </button>
                </div>
            </div>`;
            
            container.insertAdjacentHTML('beforeend', rowHtml);
            var newRow = document.getElementById(rowId);

            setupRowEvents(newRow);
            setupUnitCombobox(newRow);
            updateRowButtons();
        }

        function setupRowEvents(row) {
            var catSelect = row.querySelector('.row-category-select');
            var searchInput = row.querySelector('.row-item-search');
            var hiddenInput = row.querySelector('.row-item-id');
            var clearBtn = row.querySelector('.row-item-clear');
            var dropdown = row.querySelector('.row-item-dropdown');

            // Reset item when category changes
            catSelect.addEventListener('change', function() {
                hiddenInput.value = '';
                searchInput.value = '';
                clearBtn.style.display = 'none';
                filterAndRender(row);
            });

            // Filter on input
            searchInput.addEventListener('input', function() {
                filterAndRender(row);
            });

            // Show dropdown on focus
            searchInput.addEventListener('focus', function() {
                filterAndRender(row);
            });

            // Clear button action
            clearBtn.addEventListener('click', function() {
                hiddenInput.value = '';
                searchInput.value = '';
                clearBtn.style.display = 'none';
                searchInput.focus();
                filterAndRender(row);
            });

            // ADD button action
            var addBtn = row.querySelector('.btn-add-row');
            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    addNewRow();
                });
            }

            // Remove button action
            var removeBtn = row.querySelector('.btn-remove-row');
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    row.remove();
                    updateRowButtons();
                });
            }
        }

        function filterAndRender(row) {
            var catSelect = row.querySelector('.row-category-select');
            var searchInput = row.querySelector('.row-item-search');
            var dropdown = row.querySelector('.row-item-dropdown');
            var inner = dropdown.querySelector('.item-dropdown-inner');

            var catId = catSelect.value;
            var query = searchInput.value.toLowerCase();

            var filtered = allItems.filter(item => {
                var matchCat = !catId || String(item.category_id) === catId;
                var searchText = (item.name + ' ' + (item.item_code || '')).toLowerCase();
                var matchSearch = !query || searchText.indexOf(query) !== -1;
                return matchCat && matchSearch;
            });

            if (filtered.length === 0) {
                inner.innerHTML = '<div class="text-muted text-center py-3 small">No items found</div>';
            } else {
                var html = '';
                filtered.forEach(item => {
                    html += `<div class="item-option" data-id="${item.id}" data-unit="${escapeHtml(item.unit || '')}" style="padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 0.9rem; color: #1f2937; transition: background 0.12s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                        <span class="item-option-name" style="font-weight: 500;">${escapeHtml(item.name)}</span>
                    </div>`;
                });
                inner.innerHTML = html;

                // Add click events to options
                var options = inner.querySelectorAll('.item-option');
                options.forEach(opt => {
                    opt.addEventListener('click', function() {
                        var id = this.getAttribute('data-id');
                        var name = this.querySelector('.item-option-name').textContent;
                        
                        row.querySelector('.row-item-id').value = id;
                        row.querySelector('.row-item-search').value = name;
                        row.querySelector('.row-item-clear').style.display = 'block';
                        dropdown.style.display = 'none';
                    });
                });
            }

            dropdown.style.display = 'block';
        }

        function updateRowButtons() {
            var rows = container.querySelectorAll('.request-item-row');
            rows.forEach((row, index) => {
                var removeBtn = row.querySelector('.btn-remove-row');
                var addBtn = row.querySelector('.btn-add-row');
                
                // Only show ADD on the latest row (last row).
                if (index === rows.length - 1) {
                    addBtn.style.removeProperty('visibility');
                    addBtn.style.setProperty('display', 'inline-flex', 'important');
                } else {
                    addBtn.style.removeProperty('visibility');
                    addBtn.style.setProperty('display', 'none', 'important');
                }
                
                // Hide remove button if there is only 1 row
                if (rows.length <= 1) {
                    removeBtn.style.setProperty('display', 'none', 'important');
                } else {
                    removeBtn.style.setProperty('display', 'inline-flex', 'important');
                }
            });
        }

        // ---- Unit combobox logic ----
        function getAvailableUnits(row) {
            var itemId = row.querySelector('.row-item-id').value;
            if (itemId) {
                var selectedItem = allItems.find(function(item) { return String(item.id) === String(itemId); });
                if (selectedItem && selectedItem.name && unitsByItemName[selectedItem.name]) {
                    return unitsByItemName[selectedItem.name].slice();
                }
            }
            var units = [];
            allItems.forEach(function(item) {
                if (item.unit && units.indexOf(item.unit) === -1) {
                    units.push(item.unit);
                }
            });
            units.sort();
            return units;
        }

        function renderUnitDropdown(row, selectedUnit) {
            var availableUnits = getAvailableUnits(row);
            var inner = row.querySelector('.unit-dropdown-inner');
            var unitInput = row.querySelector('.row-unit-search');
            if (!inner) return;
            var query = unitInput ? unitInput.value.toLowerCase() : '';
            var filtered = availableUnits.filter(function(u) {
                return !query || u.toLowerCase().indexOf(query) !== -1;
            });
            if (filtered.length === 0) {
                inner.innerHTML = '<div style="padding:8px 12px; font-size:0.85rem; color:#9ca3af;">No units found</div>';
            } else {
                inner.innerHTML = filtered.map(function(u) {
                    var isSel = u === selectedUnit ? 'font-weight:600;' : '';
                    return `<div class="unit-option" data-unit="${escapeHtml(u)}" style="padding:8px 12px;border-radius:6px;cursor:pointer;font-size:0.9rem;color:#1f2937;transition:background 0.12s;${isSel}" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">${escapeHtml(u)}</div>`;
                }).join('');
                inner.querySelectorAll('.unit-option').forEach(function(opt) {
                    opt.addEventListener('click', function() {
                        var u = this.getAttribute('data-unit');
                        unitInput.value = u;
                        unitInput.setAttribute('data-selected-unit', u);
                        row.querySelector('.row-unit-dropdown').style.display = 'none';
                        var clearBtn = row.querySelector('.row-unit-clear');
                        if (clearBtn) clearBtn.style.display = 'block';
                    });
                });
            }
        }

        function setupUnitCombobox(row) {
            var unitInput = row.querySelector('.row-unit-search');
            var unitDropdown = row.querySelector('.row-unit-dropdown');
            var clearBtn = row.querySelector('.row-unit-clear');
            if (!unitInput || !unitDropdown) return;

            // Make input editable for typing to filter
            unitInput.removeAttribute('readonly');
            unitInput.addEventListener('focus', function() {
                renderUnitDropdown(row, unitInput.getAttribute('data-selected-unit') || '');
                unitDropdown.style.display = 'block';
            });
            unitInput.addEventListener('input', function() {
                renderUnitDropdown(row, unitInput.getAttribute('data-selected-unit') || '');
                unitDropdown.style.display = 'block';
            });

            // Clear button action
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    unitInput.value = '';
                    unitInput.setAttribute('data-selected-unit', '');
                    clearBtn.style.display = 'none';
                });
            }
        }

        // Hide dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            var rows = container.querySelectorAll('.request-item-row');
            rows.forEach(row => {
                var combobox = row.querySelector('.item-combobox');
                var dropdown = row.querySelector('.row-item-dropdown');
                if (combobox && !combobox.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
                var unitCombobox = row.querySelector('.unit-combobox');
                var unitDropdown = row.querySelector('.row-unit-dropdown');
                if (unitCombobox && !unitCombobox.contains(e.target)) {
                    if (unitDropdown) unitDropdown.style.display = 'none';
                }
            });
        });

        // Setup events for initial row (already added)
        var rows = container.querySelectorAll('.request-item-row');
        rows.forEach(row => { setupRowEvents(row); setupUnitCombobox(row); });

        // Form validation on submit
        var form = document.getElementById('supplyRequestForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                var rows = container.querySelectorAll('.request-item-row');
                var valid = true;
                rows.forEach((row, index) => {
                    var hiddenInput = row.querySelector('.row-item-id');
                    var searchInput = row.querySelector('.row-item-search');
                    if (!hiddenInput.value) {
                        valid = false;
                        searchInput.classList.add('is-invalid');
                        searchInput.style.borderColor = '#ef4444';
                    } else {
                        searchInput.classList.remove('is-invalid');
                        searchInput.style.borderColor = '#cbd5e1';
                    }
                });

                if (!valid) {
                    e.preventDefault();
                    var alertEl = document.getElementById('createRequestAlert');
                    if (alertEl) alertEl.style.display = '';
                }
            });
        }

        var requestModal = document.getElementById('createRequestModal');
        if (requestModal) {
            requestModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var mode = button.getAttribute('data-mode') || 'create';
                var modalTitle = requestModal.querySelector('.modal-title');
                var modalForm = requestModal.querySelector('form');
                var submitBtn = requestModal.querySelector('button[type="submit"]');
                var createHeaders = document.getElementById('create-modal-headers');
                var editHeaders = document.getElementById('edit-modal-headers');

                if (mode === 'edit') {
                    var id = button.getAttribute('data-id');
                    var categoryId = button.getAttribute('data-category');
                    var itemId = button.getAttribute('data-item-id');
                    var itemName = button.getAttribute('data-item-name');
                    var qty = button.getAttribute('data-qty');
                    var unit = button.getAttribute('data-unit') || '';
                    var notes = button.getAttribute('data-notes');

                    modalTitle.textContent = '<?php echo session()->get('role') === 'encoder' ? 'Manage' : 'Edit'; ?> Request';
                    modalForm.action = '<?php echo base_url('requests/edit'); ?>/' + id;
                    submitBtn.textContent = 'Update Request';

                    if (createHeaders) {
                        createHeaders.classList.add('d-none');
                        createHeaders.classList.remove('d-md-flex');
                    }
                    if (editHeaders) {
                        editHeaders.classList.remove('d-none');
                        editHeaders.classList.add('d-md-flex');
                    }

                    // Clear container and add exactly one row for editing
                    container.innerHTML = '';
                    
                    var rowId = 'row_edit_' + id;
                    // Find existing unit for this item from allItems
                    var editItemUnit = '';
                    var foundItem = allItems.find(function(it) { return String(it.id) === String(itemId); });
                    if (foundItem) editItemUnit = foundItem.unit || '';
                    var rowHtml = `
                    <div class="request-item-row row g-2 align-items-end mb-2 pb-2 border-bottom border-light-subtle" id="${rowId}">
                        <!-- Category select -->
                        <div class="col-lg-3 col-12">
                            <label class="form-label small fw-semibold text-secondary d-md-none">Category</label>
                            <select class="form-select input-custom row-category-select" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                                <option value="">All Categories</option>
                                ${categories.map(c => `<option value="${c.category_id}" ${String(c.category_id) === String(categoryId) ? 'selected' : ''}>${escapeHtml(c.category_code + ' - ' + c.category_description)}</option>`).join('')}
                            </select>
                        </div>

                        <!-- Item search combobox -->
                        <div class="col-lg-4 col-12">
                            <label class="form-label small fw-semibold text-secondary d-md-none">Item <span class="text-danger">*</span></label>
                            <div class="item-combobox">
                                <div class="position-relative">
                                    <input type="text" class="form-control input-custom row-item-search" placeholder="Select Item" autocomplete="off" value="${escapeHtml(itemName)}" style="border-radius: 8px; border-color: #cbd5e1; height: 42px; padding-right: 30px;" required>
                                    <input type="hidden" name="item_id" class="row-item-id" value="${itemId}">
                                    <i class="fa-solid fa-xmark position-absolute top-50 end-0 translate-middle-y me-3 row-item-clear" style="color: #9ca3af; font-size: 0.9rem; cursor: pointer; display: block;"></i>
                                </div>
                                <div class="item-dropdown row-item-dropdown" style="display: none;">
                                    <div class="item-dropdown-inner">
                                        <div class="text-muted text-center py-3 small row-item-empty">No items found</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Unit combobox -->
                        <div class="col-lg-2 col-12">
                            <label class="form-label small fw-semibold text-secondary d-md-none">Unit</label>
                            <div class="unit-combobox position-relative">
                                <input type="text" class="form-control input-custom row-unit-search" name="unit" placeholder="Select Unit" autocomplete="off" value="${escapeHtml(unit)}" data-selected-unit="${escapeHtml(unit)}" style="border-radius: 8px; border-color: #cbd5e1; height: 42px; padding-right: 30px;">
                                <i class="fa-solid fa-xmark position-absolute top-50 end-0 translate-middle-y me-3 row-unit-clear" style="color: #9ca3af; font-size: 0.9rem; cursor: pointer; display: ${unit ? 'block' : 'none'};"></i>
                                <div class="unit-dropdown row-unit-dropdown" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:9999; background:#fff; border:1px solid #e2e8f0; border-radius:8px; box-shadow:0 4px 16px rgba(0,0,0,0.1); margin-top:4px; max-height:180px; overflow-y:auto;">
                                    <div class="unit-dropdown-inner"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Qty -->
                        <div class="col-lg-1 col-12">
                            <label class="form-label small fw-semibold text-secondary d-md-none">QTY <span class="text-danger">*</span></label>
                            <input type="number" class="form-control input-custom row-quantity-input" name="quantity" min="1" value="${qty}" required placeholder="QTY" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                        </div>
                    </div>`;

                    container.insertAdjacentHTML('beforeend', rowHtml);
                    var newRow = document.getElementById(rowId);
                    setupRowEvents(newRow);
                    setupUnitCombobox(newRow);

                    // Pre-fill notes
                    var notesTextarea = document.getElementById('modal_notes');
                    if (notesTextarea) {
                        notesTextarea.value = notes;
                    }
                } else {
                    // Create mode
                    modalTitle.textContent = 'Create Request';
                    modalForm.action = '<?php echo base_url('requests/create'); ?>';
                    submitBtn.textContent = 'Submit Request';

                    if (editHeaders) {
                        editHeaders.classList.add('d-none');
                        editHeaders.classList.remove('d-md-flex');
                    }
                    if (createHeaders) {
                        createHeaders.classList.remove('d-none');
                        createHeaders.classList.add('d-md-flex');
                    }

                    // Clear notes
                    var notesTextarea = document.getElementById('modal_notes');
                    if (notesTextarea) {
                        notesTextarea.value = '';
                    }

                    // Reset container to single empty row
                    container.innerHTML = '';
                    addNewRow();
                }
            });
        }

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }
    });
    </script>

    <!-- Auto-open modal on validation failure -->
    <?php if (session()->getFlashdata('create_request_modal_open')): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('createRequestModal');
            if (el) { new bootstrap.Modal(el).show(); }
        });
    </script>
    <?php endif; ?>
    <?php endif; ?>

    <script>
    window.batchRow = {
        add: function(btn) {
            var row = btn.closest('.serve-batch-row, .partial-batch-row, .complete-batch-row');
            if (!row) return;
            var newRow = row.cloneNode(true);
            newRow.querySelectorAll('select').forEach(function(s) { s.selectedIndex = 0; });
            newRow.querySelectorAll('input[type="number"]').forEach(function(i) { i.value = '1'; });
            row.parentNode.insertBefore(newRow, row.nextSibling);
            this.updateAll();
        },
        remove: function(btn) {
            var row = btn.closest('.serve-batch-row, .partial-batch-row, .complete-batch-row');
            if (!row) return;
            var parent = row.parentNode;
            if (!parent) return;
            var cls = '.serve-batch-row, .partial-batch-row, .complete-batch-row';
            var siblings = parent.querySelectorAll(cls);
            if (siblings.length > 1) {
                row.remove();
                this.updateAll();
            }
        },
        updateAll: function() {
            document.querySelectorAll('.serve-batch-rows, .partial-batch-rows, .complete-batch-rows').forEach(function(c) {
                var rows = c.querySelectorAll('.serve-batch-row, .partial-batch-row, .complete-batch-row');
                rows.forEach(function(r, index) {
                    var addBtn = r.querySelector('.btn-add-serve-batch, .btn-add-partial-batch, .btn-add-complete-batch');
                    if (addBtn) {
                        if (index === rows.length - 1) {
                            addBtn.style.removeProperty('visibility');
                            addBtn.style.setProperty('display', 'inline-flex', 'important');
                        } else {
                            addBtn.style.removeProperty('visibility');
                            addBtn.style.setProperty('display', 'none', 'important');
                        }
                    }

                    var btn = r.querySelector('.btn-remove-serve-batch, .btn-remove-partial-batch, .btn-remove-complete-batch');
                    if (btn) {
                        if (rows.length > 1) {
                            btn.style.setProperty('display', 'inline-flex', 'important');
                            btn.style.alignItems = 'center';
                            btn.style.gap = '4px';
                        } else {
                            btn.style.setProperty('display', 'none', 'important');
                        }
                    }
                });
            });
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        window.batchRow.updateAll();
        document.addEventListener('show.bs.modal', function() {
            setTimeout(function() {
                window.batchRow.updateAll();
            }, 150);
        });
        document.addEventListener('hidden.bs.modal', function(e) {
            var id = e.target.id || '';
            var modal = e.target;
            // Clear reject notes
            var notes = modal.querySelector('textarea[name="reject_notes"]');
            if (notes) notes.value = '';
            // Reset batch rows to single default row
            ['.serve-batch-rows', '.partial-batch-rows', '.complete-batch-rows'].forEach(function(sel) {
                var container = modal.querySelector(sel);
                if (container) {
                    var rows = container.querySelectorAll('.serve-batch-row, .partial-batch-row, .complete-batch-row');
                    // Remove all rows except first
                    for (var i = rows.length - 1; i > 0; i--) {
                        rows[i].remove();
                    }
                    // Reset first row
                    var first = container.querySelector('.serve-batch-row, .partial-batch-row, .complete-batch-row');
                    if (first) {
                        var sel = first.querySelector('select');
                        if (sel) sel.selectedIndex = 0;
                        var qty = first.querySelector('input[type="number"]');
                        if (qty) qty.value = '1';
                    }
                    window.batchRow.updateAll();
                }
            });
        });
    });

    <?php if ($openModalId = session()->getFlashdata('open_modal')): ?>
    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('<?php echo $openModalId; ?>');
        if (el) new bootstrap.Modal(el).show();
    });
    <?php endif; ?>

    document.addEventListener('hidden.bs.modal', function(e) {
        var err = e.target.querySelector('.modal-body .alert.alert-danger');
        if (err) {
            if (err.id) {
                err.style.display = 'none';
            } else {
                err.remove();
            }
        }
    });
    </script>

    <style>
        #btnNewSupplyRequest,
        #btnNewSupplyRequest:hover,
        #btnNewSupplyRequest:focus,
        #btnNewSupplyRequest:active,
        #btnNewSupplyRequest:focus-visible {
            color: #fff !important;
            box-shadow: none !important;
        }

        #btnNewSupplyRequest:hover {
            background: #059669 !important;
        }

        <?php if (session()->get('role') === 'encoder'): ?>
        #supplyRequestsTable th:nth-child(1),
        #supplyRequestsTable td:nth-child(1),
        #supplyRequestsTable th:nth-child(4),
        #supplyRequestsTable td:nth-child(4),
        #supplyRequestsTable th:nth-child(5),
        #supplyRequestsTable td:nth-child(5) { display: none; }
        #supplyRequestsTable .col-last-updated { display: table-cell; }
    <?php else: ?>
        #supplyRequestsTable .col-last-updated { display: none; }
        <?php endif; ?>

        #createRequestModal .form-control.input-custom,
        #createRequestModal .form-select.input-custom {
            border-color: #cbd5e1 !important;
            box-shadow: none !important;
        }

        #createRequestModal .form-control.input-custom:focus,
        #createRequestModal .form-select.input-custom:focus {
            border-color: #0d9488 !important;
            box-shadow: 0 0 0 0.15rem rgba(13,148,136,0.18) !important;
        }

        .request-item-row:hover {
            border-color: #cbd5e1 !important;
        }

        #createRequestModal .btn-add-row,
        #createRequestModal .btn-add-row:hover,
        #createRequestModal .btn-add-row:focus,
        #createRequestModal .btn-add-row:active,
        #createRequestModal .btn-add-row:focus-visible {
            color: #fff !important;
            box-shadow: none !important;
        }

        #createRequestModal .btn-add-row:hover,
        #createRequestModal .btn-add-row:focus,
        #createRequestModal .btn-add-row:active,
        #createRequestModal .btn-add-row:focus-visible {
            background: #059669 !important;
        }

        #createRequestModal .btn-remove-row:hover,
        #createRequestModal .btn-remove-row:focus,
        #createRequestModal .btn-remove-row:active,
        #createRequestModal .btn-remove-row:focus-visible {
            box-shadow: none !important;
        }

        .item-combobox { position: relative; }
        .item-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1055;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            margin-top: 4px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            max-height: 220px;
            overflow-y: auto;
        }
        .item-dropdown-inner { padding: 4px; }
        .item-dropdown::-webkit-scrollbar { width: 6px; }
        .item-dropdown::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .item-dropdown::-webkit-scrollbar-track { background: transparent; }

        @media (max-width: 767.98px) {
            #create-modal-headers,
            #edit-modal-headers {
                display: none !important;
            }

            #createRequestModal .request-item-actions {
                display: flex !important;
                justify-content: flex-end !important;
                align-items: center !important;
                gap: 0.75rem !important;
                width: 100%;
            }

            #createRequestModal .btn-add-row,
            #createRequestModal .btn-remove-row {
                height: 42px !important;
                min-width: 0;
            }

            #createRequestModal .btn-add-row {
                justify-content: center !important;
                padding-inline: 1rem !important;
            }

            #createRequestModal .btn-remove-row {
                justify-content: center !important;
                overflow: hidden;
                white-space: nowrap;
            }
        }
    </style>
