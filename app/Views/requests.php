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
                    <span>Supply Request</span>
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
                                    <?php if ($req['request_status'] === 'Served'): ?>
                                        <span class="text-success fw-bold" title="Served Quantity"><?php echo $servedQty; ?></span>
                                    <?php elseif ($req['request_status'] === 'Partially Served'): ?>
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
                                    if ($req['request_status'] === 'Served') {
                                        $badge = 'bg-success-subtle text-success border border-success-subtle';
                                    } elseif ($req['request_status'] === 'Partially Served') {
                                        $badge = 'bg-primary-subtle text-primary border border-primary-subtle';
                                    } elseif ($req['request_status'] === 'Rejected') {
                                        $badge = 'bg-danger-subtle text-danger border border-danger-subtle';
                                    } else {
                                        $badge = 'bg-warning-subtle text-warning border border-warning-subtle';
                                    }
                                ?>
                                <span class="badge badge-action <?php echo $badge; ?>">
                                    <?php echo $req['request_status']; ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <?php if (is_admin_role() && $req['request_status'] === 'Pending'): ?>
                                    <div class="d-inline-flex gap-2">
                                        <!-- Serve Button Trigger -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-success d-flex align-items-center gap-1 rounded-2"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#serveModal_<?php echo $req['request_id']; ?>"
                                                id="btnTriggerServe_<?php echo $req['request_id']; ?>"
                                                title="Serve Request">
                                            <i class="bi bi-check-circle"></i>
                                            <span class="small fw-semibold">Serve</span>
                                        </button>

                                        <!-- Partial Serve Trigger -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 rounded-2"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#partialModal_<?php echo $req['request_id']; ?>"
                                                id="btnTriggerPartial_<?php echo $req['request_id']; ?>"
                                                title="Serve Partially">
                                            <i class="fa-solid fa-circle-half-stroke"></i>
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

                                        <!-- Archive Button Trigger -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center rounded-2"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#archiveSingleModal_<?php echo $req['request_id']; ?>"
                                                id="btnTriggerArchive_<?php echo $req['request_id']; ?>"
                                        title="Archive Request"
                                        style="width: 32px; height: 32px; padding: 0;">
                                            <i class="fa-regular fa-folder"></i>
                                        </button>
                                    </div>
                                <?php elseif (is_admin_role() && $req['request_status'] === 'Partially Served'): ?>
                                    <div class="d-inline-flex gap-2">
                                        <!-- Partial Serve Again -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 rounded-2"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#partialModal_<?php echo $req['request_id']; ?>"
                                                id="btnTriggerPartial_<?php echo $req['request_id']; ?>"
                                                title="Serve Partially">
                                            <i class="fa-solid fa-circle-half-stroke"></i>
                                            <span class="small fw-semibold">Partial</span>
                                        </button>

                                        <!-- Complete -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-success d-flex align-items-center gap-1 rounded-2"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#completePartialModal_<?php echo $req['request_id']; ?>"
                                                id="btnTriggerCompletePartial_<?php echo $req['request_id']; ?>"
                                                title="Complete Partially Served Request">
                                            <i class="bi bi-check-circle"></i>
                                            <span class="small fw-semibold">Complete</span>
                                        </button>

                                        <!-- Archive -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center rounded-2"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#archiveSingleModal_<?php echo $req['request_id']; ?>"
                                                id="btnTriggerArchive_<?php echo $req['request_id']; ?>"
                                                title="Archive Request"
                                                style="width: 32px; height: 32px; padding: 0;">
                                            <i class="fa-regular fa-folder"></i>
                                            </button>
                                    </div>
                                <?php elseif ($req['request_status'] !== 'Pending'): ?>
                                    <div class="d-inline-flex gap-2">
                                        <!-- View Details (icon only) -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center rounded-2"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewModal_<?php echo $req['request_id']; ?>"
                                                id="btnTriggerView_<?php echo $req['request_id']; ?>"
                                                title="View Details"
                                                style="width: 32px; height: 32px; padding: 0 !important; flex-shrink: 0;">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <?php if (is_admin_role()): ?>
                                            <!-- Archive Button Trigger -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center rounded-2"                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#archiveSingleModal_<?php echo $req['request_id']; ?>"
                                                    id="btnTriggerArchive_<?php echo $req['request_id']; ?>"
                                        title="Archive Request"
                                        style="width: 32px; height: 32px; padding: 0;">
                                            <i class="fa-regular fa-folder"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="d-inline-flex gap-2">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center rounded-2"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewModal_<?php echo $req['request_id']; ?>"
                                                title="View Details"
                                                style="width: 32px; height: 32px; padding: 0 !important; flex-shrink: 0;">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
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
        <?php if ($req['request_status'] === 'Pending'): ?>
            <!-- Serve Modal -->
            <div class="modal fade" id="serveModal_<?php echo $req['request_id']; ?>" tabindex="-1" aria-labelledby="serveModalLabel_<?php echo $req['request_id']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                        <div class="modal-header border-bottom px-4">
                            <h5 class="modal-title fw-bold text-dark" id="serveModalLabel_<?php echo $req['request_id']; ?>">Serve Supply Request</h5>
                            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                        </div>
                        <form method="POST" action="<?php echo base_url('requests/serve/' . $req['request_id']); ?>">
                            <div class="modal-body px-4 py-4 text-center">
                                <h5 class="fw-semibold text-dark mb-2">Confirm Full Serve</h5>
                                <p class="text-muted small mb-3">
                                    Transfer <strong><?php echo $req['quantity_requested']; ?> unit(s)</strong> of <strong><?php echo htmlspecialchars($req['item_name']); ?></strong>
                                    to <strong><?php echo htmlspecialchars($req['requester_full_name']); ?></strong> (<?php echo htmlspecialchars($req['department_name'] ?? ''); ?>).
                                </p>
                                <div class="d-flex justify-content-center gap-3 small mb-3">
                                    <div class="text-center">
                                        <div class="fw-bold text-dark"><?php echo $req['quantity_requested']; ?></div>
                                        <div class="text-muted">Requested</div>
                                    </div>
                                </div>
                                <?php if (isset($batches_by_code[$req['item_name']]) && count($batches_by_code[$req['item_name']]) > 1): ?>
                                <div class="mb-3 text-start">
                                    <label for="serve_batch_<?php echo $req['request_id']; ?>" class="form-label small fw-semibold text-secondary">Select Inventory <span class="text-danger">*</span></label>
                                    <select class="form-select input-custom" id="serve_batch_<?php echo $req['request_id']; ?>" name="central_supply_id" required>
                                        <option value="" disabled selected hidden>Select Inventory</option>
                                        <?php foreach ($batches_by_code[$req['item_name']] as $batch): ?>
                                        <option value="<?php echo $batch['central_supply_id']; ?>" <?php echo ((int)$batch['central_supply_id'] === (int)$req['central_supply_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($batch['item_code']); ?> &mdash; Exp: <?php echo $batch['expiration_date'] ? date('M j, Y', strtotime($batch['expiration_date'])) : 'N/A'; ?> &mdash; Available: <?php echo (int)$batch['quantity_on_hand']; ?> <?php echo htmlspecialchars($batch['unit'] ?? ''); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                                <button type="button"
                                        data-bs-dismiss="modal"
                                        style="background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                        onmouseover="this.style.background='#f9fafb'"
                                        onmouseout="this.style.background='#fff'">
                                    Cancel
                                </button>
                                <button type="submit"
                                        style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(16,185,129,0.3); transition: background 0.15s, box-shadow 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                        onmouseover="this.style.background='#059669';this.style.boxShadow='0 4px 12px rgba(16,185,129,0.4)'"
                                        onmouseout="this.style.background='#10b981';this.style.boxShadow='0 2px 8px rgba(16,185,129,0.3)'">
                                    Serve Supplies
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
                            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                        </div>
                        <form method="POST" action="<?php echo base_url('requests/reject/' . $req['request_id']); ?>">
                            <div class="modal-body px-4 py-4 text-center">
                                <h5 class="fw-semibold text-dark mb-2">Reject This Request?</h5>
                                <p class="text-muted small mb-0">
                                    This will mark request <strong>#<?php echo $req['request_id']; ?></strong> from
                                    <strong><?php echo htmlspecialchars($req['requester_full_name']); ?></strong>
                                    as <strong class="text-danger">Rejected</strong>.
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
                                    Cancel
                                </button>
                                <button type="submit"
                                        style="background: #ef4444; color: #fff; border: 1px solid transparent; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(245,158,11,0.3); transition: background 0.15s, box-shadow 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                        onmouseover="this.style.background='#dc2626';this.style.boxShadow='0 4px 12px rgba(245,158,11,0.4)'"
                                        onmouseout="this.style.background='#ef4444';this.style.boxShadow='0 2px 8px rgba(245,158,11,0.3)'">
                                    Reject Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($req['request_status'] === 'Pending' || $req['request_status'] === 'Partially Served'): ?>
            <?php
                $remaining = $req['quantity_requested'] - $req['quantity_served'];
                $partialMax = $remaining > 0 ? $remaining - 1 : 0;
            ?>
            <!-- Partial Serve Modal -->
            <div class="modal fade" id="partialModal_<?php echo $req['request_id']; ?>" tabindex="-1" aria-labelledby="partialModalLabel_<?php echo $req['request_id']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                        <div class="modal-header border-bottom px-4">
                            <h5 class="modal-title fw-bold text-dark" id="partialModalLabel_<?php echo $req['request_id']; ?>">Partially Serve Request</h5>
                            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                        </div>
                        <form method="POST" action="<?php echo base_url('requests/partial/' . $req['request_id']); ?>">
                            <div class="modal-body px-4 py-4">
                                <div class="text-center mb-3">
                                    <h6 class="fw-semibold text-dark">Specify Quantity to Serve</h6>
                                    <p class="text-muted small mb-0">
                                        Requested: <strong><?php echo $req['quantity_requested']; ?> unit(s)</strong> of <strong><?php echo htmlspecialchars($req['item_name']); ?></strong>.<br>
                                        <?php if ((int)$req['quantity_served'] > 0): ?>
                                            Already Served: <strong><?php echo $req['quantity_served']; ?> unit(s)</strong> &mdash; Remaining: <strong><?php echo $remaining; ?> unit(s)</strong>.<br>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label for="served_qty_<?php echo $req['request_id']; ?>" class="form-label small fw-semibold text-secondary">Serve Quantity <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control input-custom" 
                                           id="served_qty_<?php echo $req['request_id']; ?>" 
                                           name="served_quantity" 
                                           min="1" 
                                           max="<?php echo min($partialMax, $req['item_current_stock']); ?>" 
                                           required>
                                </div>
                                <?php if (isset($batches_by_code[$req['item_name']]) && count($batches_by_code[$req['item_name']]) > 1): ?>
                                <div class="mb-3">
                                    <label for="partial_batch_<?php echo $req['request_id']; ?>" class="form-label small fw-semibold text-secondary">Select Inventory <span class="text-danger">*</span></label>
                                    <select class="form-select input-custom" id="partial_batch_<?php echo $req['request_id']; ?>" name="central_supply_id" required>
                                        <option value="" disabled selected hidden>Select Inventory</option>
                                        <?php foreach ($batches_by_code[$req['item_name']] as $batch): ?>
                                        <option value="<?php echo $batch['central_supply_id']; ?>" <?php echo ((int)$batch['central_supply_id'] === (int)$req['central_supply_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($batch['item_code']); ?> &mdash; Exp: <?php echo $batch['expiration_date'] ? date('M j, Y', strtotime($batch['expiration_date'])) : 'N/A'; ?> &mdash; Available: <?php echo (int)$batch['quantity_on_hand']; ?> <?php echo htmlspecialchars($batch['unit'] ?? ''); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
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
                                    Cancel
                                </button>
                                <button type="submit"
                                        style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(16,185,129,0.3); transition: background 0.15s, box-shadow 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                        onmouseover="this.style.background='#059669';this.style.boxShadow='0 4px 12px rgba(16,185,129,0.4)'"
                                        onmouseout="this.style.background='#10b981';this.style.boxShadow='0 2px 8px rgba(16,185,129,0.3)'">
                                    Serve Partial
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($req['request_status'] === 'Partially Served'): ?>
            <!-- Complete Partial Serve Modal -->
            <div class="modal fade" id="completePartialModal_<?php echo $req['request_id']; ?>" tabindex="-1" aria-labelledby="completePartialModalLabel_<?php echo $req['request_id']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                        <div class="modal-header border-bottom px-4">
                            <h5 class="modal-title fw-bold text-dark" id="completePartialModalLabel_<?php echo $req['request_id']; ?>">Complete Partially Served Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="<?php echo base_url('requests/complete_partial/' . $req['request_id']); ?>">
                            <div class="modal-body px-4 py-4 text-center">
                                <?php $remaining = $req['quantity_requested'] - $req['quantity_served']; ?>
                                <h5 class="fw-semibold text-dark mb-2">Serve Remaining Quantity</h5>
                                <p class="text-muted small mb-3">
                                    This request has already been partially served.<br>
                                    Requested: <strong><?php echo $req['quantity_requested']; ?></strong> unit(s), Already Served: <strong><?php echo $req['quantity_served']; ?></strong> unit(s).<br>
                                    Remaining to serve: <strong><?php echo $remaining; ?></strong> unit(s) of <strong><?php echo htmlspecialchars($req['item_name']); ?></strong> to <strong><?php echo htmlspecialchars($req['requester_full_name']); ?></strong> (<?php echo htmlspecialchars($req['department_name'] ?? ''); ?>).<br>
                                </p>
                                <?php if (isset($batches_by_code[$req['item_name']]) && count($batches_by_code[$req['item_name']]) > 1): ?>
                                <div class="mb-3 text-start">
                                    <label for="complete_batch_<?php echo $req['request_id']; ?>" class="form-label small fw-semibold text-secondary">Select Inventory <span class="text-danger">*</span></label>
                                    <select class="form-select input-custom" id="complete_batch_<?php echo $req['request_id']; ?>" name="central_supply_id" required>
                                        <option value="" disabled selected hidden>Select Inventory</option>
                                        <?php foreach ($batches_by_code[$req['item_name']] as $batch): ?>
                                        <option value="<?php echo $batch['central_supply_id']; ?>" <?php echo ((int)$batch['central_supply_id'] === (int)$req['central_supply_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($batch['item_code']); ?> &mdash; Exp: <?php echo $batch['expiration_date'] ? date('M j, Y', strtotime($batch['expiration_date'])) : 'N/A'; ?> &mdash; Available: <?php echo (int)$batch['quantity_on_hand']; ?> <?php echo htmlspecialchars($batch['unit'] ?? ''); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                                <button type="button"
                                        data-bs-dismiss="modal"
                                        style="background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                        onmouseover="this.style.background='#f9fafb'"
                                        onmouseout="this.style.background='#fff'">
                                    Cancel
                                </button>
                                <button type="submit"
                                        style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(16,185,129,0.3); transition: background 0.15s, box-shadow 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                        onmouseover="this.style.background='#059669';this.style.boxShadow='0 4px 12px rgba(16,185,129,0.4)'"
                                        onmouseout="this.style.background='#10b981';this.style.boxShadow='0 2px 8px rgba(16,185,129,0.3)'">
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
                                <?php 
                                    if ($req['request_status'] === 'Served') {
                                        $badge = 'bg-success-subtle text-success border border-success-subtle';
                                    } elseif ($req['request_status'] === 'Partially Served') {
                                        $badge = 'bg-primary-subtle text-primary border border-primary-subtle';
                                    } elseif ($req['request_status'] === 'Rejected') {
                                        $badge = 'bg-danger-subtle text-danger border border-danger-subtle';
                                    } else {
                                        $badge = 'bg-warning-subtle text-warning border border-warning-subtle';
                                    }
                                ?>
                                <span class="badge <?php echo $badge; ?>"><?php echo $req['request_status']; ?></span>
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
    <?php endforeach; ?>
<?php endif; ?>

        <!-- Archive Confirmation Modals (Admin only) -->
        <?php if (is_admin_role() && !empty($requests)): ?>
            <?php foreach ($requests as $req): ?>
                <div class="modal fade" id="archiveSingleModal_<?php echo $req['request_id']; ?>" tabindex="-1" aria-labelledby="archiveSingleModalLabel_<?php echo $req['request_id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                            <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                                <div class="d-flex align-items-center gap-3">
                                    <h5 class="modal-title fw-bold mb-0" id="archiveSingleModalLabel_<?php echo $req['request_id']; ?>" style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                                        Archive Supply Request
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
                                        Cancel
                                    </button>
                                    <button type="submit"
                                            style="background: #ef4444; color: #fff; border: 1px solid transparent; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(245,158,11,0.3); transition: background 0.15s, box-shadow 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                            onmouseover="this.style.background='#dc2626';this.style.boxShadow='0 4px 12px rgba(245,158,11,0.4)'"
                                            onmouseout="this.style.background='#ef4444';this.style.boxShadow='0 2px 8px rgba(245,158,11,0.3)'">
                                        Archive Supply Request
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
                    <div class="alert alert-danger border-0 rounded-3 mb-4 py-3 shadow-sm">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                            <div>
                                <span class="fw-bold d-block mb-1">Please correct the errors below:</span>
                                <div class="small"><?php echo $create_errors; ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Column Labels for Desktop/Tablet -->
                    <div class="row g-3 mb-2 d-none d-md-flex">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Category</label>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold text-secondary">Item Name <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-secondary">QTY <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-secondary">Action</label>
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
                        Cancel
                    </button>
                    <button type="submit"
                            style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(16,185,129,0.3); transition: background 0.15s, box-shadow 0.15s; display: inline-flex; align-items: center; height: 38px;"
                            onmouseover="this.style.background='#059669';this.style.boxShadow='0 4px 12px rgba(16,185,129,0.4)'"
                            onmouseout="this.style.background='#10b981';this.style.boxShadow='0 2px 8px rgba(16,185,129,0.3)'"
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
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-secondary d-md-none">Category</label>
                <select class="form-select input-custom row-category-select" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                    <option value="">All Categories</option>
                    ${categories.map(c => `<option value="${c.category_id}">${escapeHtml(c.category_code + ' - ' + c.category_description)}</option>`).join('')}
                </select>
            </div>

            <!-- Item search combobox -->
            <div class="col-12 col-md-5">
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

            <!-- Qty -->
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold text-secondary d-md-none">QTY <span class="text-danger">*</span></label>
                <input type="number" class="form-control input-custom row-quantity-input" name="quantity[]" min="1" value="1" required placeholder="QTY" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
            </div>

            <!-- Add/Remove Actions -->
            <div class="col-12 col-md-2 d-flex gap-2 align-items-center justify-content-end justify-content-md-start">
                <button type="button" class="btn btn-add-row d-flex align-items-center gap-1" style="background: #10b981; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; transition: background 0.15s; height: 42px;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                    <span>ADD</span>
                </button>
                <button type="button" class="btn-remove-row btn btn-link text-decoration-none d-flex align-items-center gap-1 p-0 ms-2" style="font-size: 0.9rem; color: #64748b; cursor: pointer; transition: color 0.15s; border: none; background: none; outline: none; height: 42px;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#64748b'">
                    <i class="fa-regular fa-trash-can"></i> <span>Remove</span>
                </button>
            </div>
        </div>`;
        
        container.insertAdjacentHTML('beforeend', rowHtml);
        var newRow = document.getElementById(rowId);

        setupRowEvents(newRow);
        updateRemoveButtons();
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
        row.querySelector('.btn-add-row').addEventListener('click', function() {
            addNewRow();
        });

        // Remove button action
        row.querySelector('.btn-remove-row').addEventListener('click', function() {
            row.remove();
            updateRemoveButtons();
        });
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
            var matchSearch = !query || item.name.toLowerCase().indexOf(query) !== -1;
            return matchCat && matchSearch;
        });

        if (filtered.length === 0) {
            inner.innerHTML = '<div class="text-muted text-center py-3 small">No items found</div>';
        } else {
            var html = '';
            filtered.forEach(item => {
                html += `<div class="item-option" data-id="${item.id}" style="padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 0.9rem; color: #1f2937; transition: background 0.12s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
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

    function updateRemoveButtons() {
        var rows = container.querySelectorAll('.request-item-row');
        rows.forEach((row, index) => {
            var removeBtn = row.querySelector('.btn-remove-row');
            if (rows.length <= 1) {
                removeBtn.style.setProperty('display', 'none', 'important');
            } else {
                removeBtn.style.removeProperty('display');
            }
        });
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
        });
    });

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
                alert('Please select a valid item from the dropdown list for all rows.');
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

<style>
    #btnNewSupplyRequest:hover { background: #059669 !important; box-shadow: 0 4px 12px rgba(34,197,94,0.4) !important; }

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
</style>
