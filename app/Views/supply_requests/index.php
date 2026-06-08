<!-- Page Title Section -->
<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">Supply Requests</h1>
        </div>
        <div>
            <?php if (session()->get('role') === 'encoder'): ?>
                <button type="button"
                        class="btn d-flex align-items-center gap-2"
                        id="btnNewSupplyRequest"
                        data-bs-toggle="modal"
                        data-bs-target="#createRequestModal"
                        style="background: #10b981; color: #fff; font-weight: 600; border: none; padding: 0.5rem 1.1rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(34,197,94,0.3); transition: background 0.2s;">
                    <i class="fa-solid fa-file-circle-plus"></i>
                    <span>New Supply Request</span>
                </button>
            <?php endif; ?>
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

<!-- Supply Requests Database Card -->
<div class="standard-card fade-in-up" style="animation-delay: 0.1s;">
    <div class="card-header-styled mb-4">
        <h5 class="card-title-styled">
            <span><?php echo is_admin_role() ? 'All Staff Supply Requests' : 'Departmental Supply Requests'; ?></span>
        </h5>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-custom table-hover w-100" id="supplyRequestsTable">
            <thead>
                <tr>
                    <th style="width: 7%">ID</th>
                    <th style="width: 13%">Request Date</th>
                    <th style="width: 16%">Requester</th>
                    <th style="width: 12%">Department</th>
                    <th style="width: 20%">Item Requested</th>
                    <th style="width: 12%">Quantity</th>
                    <th style="width: 10%">Status</th>
                    <th style="width: 10%" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($requests)): ?>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td class="font-monospace fw-bold" style="font-size: 0.85rem; color: var(--text-secondary);">
                                #<?php echo $req['request_id']; ?>
                            </td>
                            <td data-order="<?php echo htmlspecialchars($req['created_at'] ?? ''); ?>">
                                <span class="text-dark"><?php echo !empty($req['created_at']) ? date('Y-m-d', strtotime($req['created_at'])) : 'N/A'; ?></span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($req['requester_full_name']); ?></div>
                            </td>
                            <td>
                                <span class="text-dark"><?php echo htmlspecialchars($req['department_name'] ?? 'N/A'); ?></span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($req['item_name']); ?></div>
                            </td>
                            <td>
                                <?php $servedQty = (int)($req['quantity_served'] ?? 0); ?>
                                <div>
                                    <?php if ($req['status'] === 'Served'): ?>
                                        <span class="text-success fw-bold" title="Served Quantity"><?php echo $servedQty; ?></span>
                                    <?php elseif ($req['status'] === 'Partially Served'): ?>
                                        <span class="text-primary fw-bold" title="Served Quantity"><?php echo $servedQty; ?></span>
                                    <?php else: ?>
                                        <span class="fw-bold text-dark" title="Served Quantity"><?php echo $servedQty; ?></span>
                                    <?php endif; ?>
                                    / <span class="fw-bold text-dark" title="Requested Quantity"><?php echo $req['quantity_requested']; ?></span>
                                    <small class="text-muted">pcs</small>
                                </div>
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
                            <td class="text-end">
                                <?php if (is_admin_role() && $req['status'] === 'Pending'): ?>
                                    <div class="d-inline-flex gap-2">
                                        <!-- Serve Button Trigger -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-success d-flex align-items-center gap-1 rounded-2"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#serveModal_<?php echo $req['request_id']; ?>"
                                                id="btnTriggerServe_<?php echo $req['request_id']; ?>"
                                                title="Serve Request">
                                            <i class="fa-solid fa-check"></i>
                                            <span class="small fw-semibold">Serve</span>
                                        </button>

                                        <!-- Partial Serve Trigger -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 rounded-2"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#partialModal_<?php echo $req['request_id']; ?>"
                                                id="btnTriggerPartial_<?php echo $req['request_id']; ?>"
                                                title="Serve Partially">
                                            <i class="fa-solid fa-percent"></i>
                                            <span class="small fw-semibold">Partial</span>
                                        </button>

                                        <!-- Reject Button Trigger -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 rounded-2"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#rejectModal_<?php echo $req['request_id']; ?>"
                                                id="btnTriggerReject_<?php echo $req['request_id']; ?>"
                                                title="Reject Request">
                                            <i class="fa-solid fa-xmark"></i>
                                            <span class="small fw-semibold">Reject</span>
                                        </button>

                                        <!-- Delete Button Trigger -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center rounded-2"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteSingleModal_<?php echo $req['request_id']; ?>"
                                                id="btnTriggerDelete_<?php echo $req['request_id']; ?>"
                                                title="Delete Request"
                                                style="width: 32px; height: 32px; padding: 0;">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                <?php elseif (is_admin_role() && $req['status'] === 'Partially Served'): ?>
                                    <div class="d-inline-flex gap-2">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-success d-flex align-items-center gap-1 rounded-2"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#completePartialModal_<?php echo $req['request_id']; ?>"
                                                id="btnTriggerCompletePartial_<?php echo $req['request_id']; ?>"
                                                title="Complete Partially Served Request">
                                            <i class="fa-solid fa-check-double"></i>
                                            <span class="small fw-semibold">Complete</span>
                                        </button>

                                        <!-- Delete Button Trigger -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center rounded-2"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteSingleModal_<?php echo $req['request_id']; ?>"
                                                id="btnTriggerDelete_<?php echo $req['request_id']; ?>"
                                                title="Delete Request"
                                                style="width: 32px; height: 32px; padding: 0;">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                <?php elseif ($req['status'] !== 'Pending'): ?>
                                    <div class="d-inline-flex gap-2">
                                        <!-- View Details (icon only) -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center rounded-2"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewModal_<?php echo $req['request_id']; ?>"
                                                id="btnTriggerView_<?php echo $req['request_id']; ?>"
                                                title="View Details"
                                                style="width: 32px; height: 32px; padding: 0 !important; flex-shrink: 0;">
                                            <i class="bi bi-search"></i>
                                        </button>

                                        <?php if (is_admin_role()): ?>
                                            <!-- Delete Button Trigger -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center rounded-2"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteSingleModal_<?php echo $req['request_id']; ?>"
                                                    id="btnTriggerDelete_<?php echo $req['request_id']; ?>"
                                                    title="Delete Request"
                                                    style="width: 32px; height: 32px; padding: 0 !important; flex-shrink: 0;">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <small class="text-warning fw-semibold">Pending Admin</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (is_admin_role() && !empty($requests)): ?>
    <?php foreach ($requests as $req): ?>
        <?php if ($req['status'] === 'Pending'): ?>
            <!-- Serve Modal -->
            <div class="modal fade" id="serveModal_<?php echo $req['request_id']; ?>" tabindex="-1" aria-labelledby="serveModalLabel_<?php echo $req['request_id']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                        <div class="modal-header border-bottom px-4">
                            <h5 class="modal-title fw-bold text-dark" id="serveModalLabel_<?php echo $req['request_id']; ?>">Serve Supply Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="<?php echo base_url('supply_requests/serve/' . $req['request_id']); ?>">
                            <div class="modal-body px-4 py-4 text-center">
                                <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(34,197,94,0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                    <i class="fa-solid fa-boxes-packing" style="font-size: 1.5rem; color: #22c55e;"></i>
                                </div>
                                <h5 class="fw-semibold text-dark mb-2">Confirm Full Serve</h5>
                                <p class="text-muted small mb-3">
                                    Transfer <strong><?php echo $req['quantity_requested']; ?> unit(s)</strong> of <strong><?php echo htmlspecialchars($req['item_name']); ?></strong>
                                    to <strong><?php echo htmlspecialchars($req['department_name']); ?></strong>.
                                </p>
                                <div class="d-flex justify-content-center gap-3 small">
                                    <div class="text-center">
                                        <div class="fw-bold text-dark"><?php echo $req['quantity_requested']; ?></div>
                                        <div class="text-muted">Requested</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="fw-bold text-success"><?php echo $req['item_current_stock']; ?></div>
                                        <div class="text-muted">In Stock</div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 px-4 pb-4 pt-0 justify-content-center">
                                <button type="button" class="btn btn-light rounded-2 px-3 py-2 fw-medium text-secondary border" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success rounded-2 px-4 py-2 fw-bold text-white shadow-sm" style="background: #22c55e; border: none;">
                                    <i class="fa-solid fa-check me-1"></i> Serve Supplies
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Partial Serve Modal -->
            <div class="modal fade" id="partialModal_<?php echo $req['request_id']; ?>" tabindex="-1" aria-labelledby="partialModalLabel_<?php echo $req['request_id']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                        <div class="modal-header border-bottom px-4">
                            <h5 class="modal-title fw-bold text-dark" id="partialModalLabel_<?php echo $req['request_id']; ?>">Partially Serve Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="<?php echo base_url('supply_requests/partial/' . $req['request_id']); ?>">
                            <div class="modal-body px-4 py-4">
                                <div class="text-center mb-3">
                                    <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(59,130,246,0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                        <i class="fa-solid fa-percent" style="font-size: 1.5rem; color: #3b82f6;"></i>
                                    </div>
                                    <h6 class="fw-semibold text-dark">Specify Quantity to Serve</h6>
                                    <p class="text-muted small mb-0">
                                        Requested: <strong><?php echo $req['quantity_requested']; ?> unit(s)</strong> of <strong><?php echo htmlspecialchars($req['item_name']); ?></strong>.<br>
                                        Central Supply available: <strong><?php echo $req['item_current_stock']; ?> unit(s)</strong>.
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label for="served_qty_<?php echo $req['request_id']; ?>" class="form-label small fw-semibold text-secondary">Served Quantity <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control input-custom" 
                                           id="served_qty_<?php echo $req['request_id']; ?>" 
                                           name="served_quantity" 
                                           min="1" 
                                           max="<?php echo min($req['quantity_requested'] - 1, $req['item_current_stock']); ?>" 
                                           required>
                                    
                                </div>
                            </div>
                            <div class="modal-footer border-0 px-4 pb-4 pt-0 justify-content-end">
                                <button type="button" class="btn btn-light rounded-2 px-3 py-2 fw-medium text-secondary border" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary rounded-2 px-4 py-2 fw-bold text-white shadow-sm" style="background: #3b82f6; border: none;">
                                     Serve Partial
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Reject Modal -->
            <div class="modal fade" id="rejectModal_<?php echo $req['request_id']; ?>" tabindex="-1" aria-labelledby="rejectModalLabel_<?php echo $req['request_id']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                        <div class="modal-header border-bottom px-4">
                            <h5 class="modal-title fw-bold text-dark" id="rejectModalLabel_<?php echo $req['request_id']; ?>">Reject Supply Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="<?php echo base_url('supply_requests/reject/' . $req['request_id']); ?>">
                            <div class="modal-body px-4 py-4 text-center">
                                <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(239,68,68,0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                    <i class="fa-solid fa-ban" style="font-size: 1.5rem; color: #ef4444;"></i>
                                </div>
                                <h5 class="fw-semibold text-dark mb-2">Reject This Request?</h5>
                                <p class="text-muted small mb-0">
                                    This will mark request <strong>#<?php echo $req['request_id']; ?></strong> from
                                    <strong><?php echo htmlspecialchars($req['requester_full_name']); ?></strong>
                                    as <strong class="text-danger">Rejected</strong>.
                                </p>
                            </div>
                            <div class="modal-footer border-0 px-4 pb-4 pt-0 justify-content-center">
                                <button type="button" class="btn btn-light rounded-2 px-3 py-2 fw-medium text-secondary border" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger rounded-2 px-4 py-2 fw-bold text-white shadow-sm" style="background: #ef4444; border: none;">
                                    <i class="fa-solid fa-xmark me-1"></i> Reject Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($req['status'] === 'Partially Served'): ?>
            <!-- Complete Partial Serve Modal -->
            <div class="modal fade" id="completePartialModal_<?php echo $req['request_id']; ?>" tabindex="-1" aria-labelledby="completePartialModalLabel_<?php echo $req['request_id']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                        <div class="modal-header border-bottom px-4">
                            <h5 class="modal-title fw-bold text-dark" id="completePartialModalLabel_<?php echo $req['request_id']; ?>">Complete Partially Served Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="<?php echo base_url('supply_requests/complete_partial/' . $req['request_id']); ?>">
                            <div class="modal-body px-4 py-4 text-center">
                                <?php $remaining = $req['quantity_requested'] - $req['quantity_served']; ?>
                                <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(34,197,94,0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                    <i class="fa-solid fa-check-double" style="font-size: 1.5rem; color: #22c55e;"></i>
                                </div>
                                <h5 class="fw-semibold text-dark mb-2">Serve Remaining Quantity</h5>
                                <p class="text-muted small mb-3">
                                    This request has already been partially served.<br>
                                    Requested: <strong><?php echo $req['quantity_requested']; ?></strong> unit(s), Already Served: <strong><?php echo $req['quantity_served']; ?></strong> unit(s).<br>
                                    Remaining to serve: <strong><?php echo $remaining; ?></strong> unit(s) of <strong><?php echo htmlspecialchars($req['item_name']); ?></strong> to <strong><?php echo htmlspecialchars($req['department_name']); ?></strong>.<br>
                                    Central Supply available: <strong><?php echo $req['item_current_stock']; ?></strong> unit(s).
                                </p>
                            </div>
                            <div class="modal-footer border-0 px-4 pb-4 pt-0 justify-content-center">
                                <button type="button" class="btn btn-light rounded-2 px-3 py-2 fw-medium text-secondary border" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success rounded-2 px-4 py-2 fw-bold text-white shadow-sm" style="background: #22c55e; border: none;">
                                     Complete Request
                                </button>
                            </div>
                        </form>
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
                            <i class="bi bi-search me-2"></i>Request Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 py-4">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="small fw-semibold text-secondary d-block">Request ID</label>
                                <span class="fw-bold text-dark">#<?php echo $req['request_id']; ?></span>
                            </div>
                            <div class="col-6">
                                <label class="small fw-semibold text-secondary d-block">Status</label>
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
                                <span class="badge <?php echo $badge; ?>"><?php echo $req['status']; ?></span>
                            </div>
                            <div class="col-12"><hr class="my-1"></div>
                            <div class="col-12">
                                <label class="small fw-semibold text-secondary d-block">Requester / Department</label>
                                <span class="text-dark"><?php echo htmlspecialchars($req['department_name'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="col-12">
                                <label class="small fw-semibold text-secondary d-block">Item Name & Code</label>
                                <span class="text-dark fw-medium"><?php echo htmlspecialchars($req['item_name'] ?? 'N/A'); ?></span>
                                <small class="text-muted d-block">(<?php echo htmlspecialchars($req['item_code'] ?? 'N/A'); ?>)</small>
                            </div>
                            <div class="col-6">
                                <label class="small fw-semibold text-secondary d-block">Requested Quantity</label>
                                <span class="text-dark fw-bold"><?php echo $req['quantity_requested']; ?> unit(s)</span>
                            </div>
                            <div class="col-6">
                                <label class="small fw-semibold text-secondary d-block">Served Quantity</label>
                                <span class="text-dark fw-bold"><?php echo $req['quantity_served'] ?? 0; ?> unit(s)</span>
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
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0 justify-content-center">
                        <button type="button" class="btn btn-secondary rounded-2 px-4 py-2 fw-medium text-white" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Single Delete Confirmation Modals (Admin only) -->
<?php if (is_admin_role() && !empty($requests)): ?>
    <?php foreach ($requests as $req): ?>
        <div class="modal fade" id="deleteSingleModal_<?php echo $req['request_id']; ?>" tabindex="-1" aria-labelledby="deleteSingleModalLabel_<?php echo $req['request_id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                    <div class="modal-header border-bottom px-4">
                        <h5 class="modal-title fw-bold text-dark" id="deleteSingleModalLabel_<?php echo $req['request_id']; ?>">
                            <i class="fa-solid fa-trash-can text-danger me-2"></i>Delete Supply Request
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="<?php echo base_url('supply_requests/delete/' . $req['request_id']); ?>">
                        <div class="modal-body px-4 py-4 text-center">
                            <div style="width:64px; height:64px; border-radius:50%; background:rgba(239,68,68,0.1); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
                                <i class="fa-solid fa-triangle-exclamation" style="font-size:1.6rem; color:#ef4444;"></i>
                            </div>
                            <h5 class="fw-semibold text-dark mb-2">Are you sure?</h5>
                            <p class="text-muted small mb-0">
                                You are about to permanently delete supply request <strong>#<?php echo $req['request_id']; ?></strong>.<br>
                                Item: <strong><?php echo htmlspecialchars($req['item_name']); ?></strong> (<?php echo htmlspecialchars($req['item_code']); ?>)<br>
                                Requested by: <strong><?php echo htmlspecialchars($req['department_name']); ?></strong><br>
                                This action <strong>cannot be undone</strong>.
                            </p>
                        </div>
                        <div class="modal-footer border-0 px-4 pb-4 pt-0 justify-content-center gap-3">
                            <button type="button" class="btn btn-light rounded-2 px-4 py-2 fw-medium text-secondary border" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger rounded-2 px-4 py-2 fw-bold text-white shadow-sm" style="background:#ef4444; border:none;">
                                <i class="fa-solid fa-trash-can me-1"></i> Confirm Delete
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (session()->get('role') === 'encoder'): ?>
<!-- ===================== NEW SUPPLY REQUEST MODAL ===================== -->
<div class="modal fade" id="createRequestModal" tabindex="-1" aria-labelledby="createRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <!-- Modal Header -->
            <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; ">
                        <i class="fa-solid fa-file-invoice" style="color: #000000; font-size: 1rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="createRequestModalLabel" style="color: #0f172a; font-size: 1.25rem; letter-spacing: -0.01em;">
                            New Supply Request
                        </h5>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
            </div>

            <!-- Form -->
            <form method="POST" action="<?php echo base_url('supply_requests/create'); ?>" id="supplyRequestForm">
                <div class="modal-body px-4 py-4">

                    <!-- Validation Errors -->
                    <?php if ($create_errors = session()->getFlashdata('create_request_validation_errors')): ?>
                    <div class="alert alert-danger border-0 rounded-3 mb-4 py-3">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                            <div>
                                <span class="fw-bold d-block mb-1">Please correct the errors below:</span>
                                <div class="small"><?php echo $create_errors; ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <!-- Select Item -->
                        <div class="col-12">
                            <label for="modal_item_id" class="form-label small fw-semibold text-secondary">
                                Select Item <span class="text-danger">*</span>
                            </label>
                            <select class="form-select input-custom" id="modal_item_id" name="item_id" required>
                                <option value="">Choose an item</option>
                                <?php foreach ($items as $item): ?>
                                    <option value="<?php echo $item['id']; ?>" <?php echo old('item_id') == $item['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($item['name']); ?> (<?php echo htmlspecialchars($item['item_code']); ?>) — Stock: <?php echo $item['quantity']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Quantity -->
                        <div class="col-12">
                            <label for="modal_quantity" class="form-label small fw-semibold text-secondary">
                                Requested Quantity <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control input-custom"
                                   id="modal_quantity"
                                   name="quantity"
                                   min="1"
                                   value="<?php echo old('quantity', '1'); ?>"
                                   required>
                        </div>

                        <!-- Notes -->
                        <div class="col-12">
                            <label for="modal_notes" class="form-label small fw-semibold text-secondary">Details</label>
                            <textarea class="form-control input-custom"
                                      id="modal_notes"
                                      name="notes"
                                      rows="3"
                                      ><?php echo old('notes'); ?></textarea>
                        </div>
                    </div><!-- /.row -->
                </div><!-- /.modal-body -->

                <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end">
                    <button type="button"
                            data-bs-dismiss="modal"
                            style="background: #fff; color: #374151; border: 1.5px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.4rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s, border-color 0.15s;"
                            onmouseover="this.style.background='#f9fafb'"
                            onmouseout="this.style.background='#fff'">
                        Cancel
                    </button>
                    <button type="submit"
                            style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(34,197,94,0.3); transition: background 0.15s, box-shadow 0.15s;"
                            onmouseover="this.style.background='#059669';this.style.boxShadow='0 4px 12px rgba(34,197,94,0.4)'"
                            onmouseout="this.style.background='#10b981';this.style.boxShadow='0 2px 8px rgba(34,197,94,0.3)'"
                            id="btnSubmitSupplyRequest">
                        Submit Request
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

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

<style>
    #btnNewSupplyRequest:hover { background: #059669 !important; box-shadow: 0 4px 12px rgba(34,197,94,0.4) !important; }
</style>
