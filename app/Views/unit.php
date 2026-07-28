<div class="page-breadcrumb">
    <a href="<?php echo base_url('dashboard'); ?>">Dashboard</a>
    <span class="separator">/</span>
    <span class="current">Unit</span>
</div>

<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">Unit</h1>
        </div>
    </div>
</div>

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

<form method="GET" action="<?php echo base_url('unit'); ?>" id="unitSearchForm">
    <div class="db-search-bar">
        <div class="db-search-field db-search-field--keyword">
            <input
                type="text"
                id="unit_search_keyword"
                name="search"
                class="db-search-input"
                placeholder=" "
                value="<?php echo htmlspecialchars($search ?? ''); ?>"
                autocomplete="off"
            >
            <label for="unit_search_keyword">Enter Unit Name / Code</label>
        </div>
        <div class="db-search-field db-search-field--dropdown">
            <select id="unit_search_status" name="status_filter" class="db-search-select">
                <option value="">- Select Status -</option>
                <option value="1" <?php echo (($status_filter ?? '') === '1') ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo (($status_filter ?? '') === '0') ? 'selected' : ''; ?>>Inactive</option>
            </select>
            <label for="unit_search_status">Status</label>
        </div>
        <div class="db-search-actions">
            <button type="submit" class="btn-db-search" id="btnUnitSearch">Search</button>
            <a href="<?php echo base_url('unit'); ?>" class="btn-db-clear" id="btnUnitClear">Clear</a>
            <div class="db-search-separator"></div>
            <button type="button"
                    class="btn btn-db-search d-inline-flex align-items-center gap-2"
                    id="btnAddNewUnit"
                    onclick="openUnitModal('create')">
                <span>Add Unit</span>
            </button>
        </div>
    </div>
</form>

<div class="table-responsive-custom">
    <table class="table table-custom table-hover w-100" id="unitsTable">
        <thead>
            <tr>
                <th style="width: 50%">Unit Name</th>
                <th style="width: 20%">Unit Code</th>
                <th style="width: 10%">Status</th>
                <th style="width: 10%" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($units)): ?>
                <?php foreach ($units as $unit): ?>
                    <tr>
                        <td class="fw text-dark"><?php echo htmlspecialchars($unit['unit_name']); ?></td>
                        <td class="fw text-dark" style="font-size: 0.875rem;"><?php echo htmlspecialchars($unit['unit_code']); ?></td>
                        <td class="text-center">
                            <?php if (($unit['status'] ?? 1) == 1): ?>
                                <span class="badge badge-action rounded-pill bg-success-subtle text-dark border border-success-subtle text-uppercase">Active</span>
                            <?php else: ?>
                                <span class="badge badge-action rounded-pill bg-secondary-subtle text-dark border border-secondary-subtle text-uppercase">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown" style="padding: 4px 12px; font-size: 0.75rem; font-weight: 600;">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" style="font-size: 0.8rem;">
                                    <?php if (($unit['status'] ?? 1) == 1): ?>
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="openUnitModal('edit', <?php echo $unit['unit_id']; ?>, '<?php echo htmlspecialchars($unit['unit_code'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($unit['unit_name'], ENT_QUOTES); ?>')" title="Manage Unit">Manage</a></li>
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#deactivateUnitModal-<?php echo $unit['unit_id']; ?>" title="Deactivate Unit">Deactivate</a></li>
                                    <?php else: ?>
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#reactivateUnitModal-<?php echo $unit['unit_id']; ?>" title="Reactivate Unit">Reactivate</a></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="unitModal" tabindex="-1" aria-labelledby="unitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="modal-title fw-bold mb-0" id="unitModalLabel" style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                        Add New Unit
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
            </div>

            <form method="POST" action="<?php echo base_url('unit/create'); ?>" id="unitForm">
                <div class="modal-body px-4 py-4">
                    <?php
                        $modal_errors = session()->getFlashdata('modal_errors');
                        $modal_mode = session()->getFlashdata('modal_mode');
                        $modal_edit_id = session()->getFlashdata('modal_edit_id');
                    ?>
                    <?php if ($modal_errors): ?>
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

                    <div class="mb-3">
                        <label for="unit_name" class="form-label small fw-semibold text-secondary">
                            Unit Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control input-custom" id="unit_name" name="unit_name" value="<?php echo old('unit_name'); ?>" required>
                    </div>

                    <div>
                        <label for="unit_code" class="form-label small fw-semibold text-secondary">
                            Unit Code <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control input-custom" id="unit_code" name="unit_code" value="<?php echo old('unit_code'); ?>" required>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                    <button type="button"
                            data-bs-dismiss="modal"
                            style="background: #fff; color: #374151; border: 1.5px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.4rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s;"
                            onmouseover="this.style.background='#f9fafb'"
                            onmouseout="this.style.background='#fff'">
                        Close
                    </button>
                    <button type="submit" id="unitFormSubmitBtn"
                            class="btn btn-success-custom">
                        Add Unit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openUnitModal(mode, id, code, name) {
    var form = document.getElementById('unitForm');
    var label = document.getElementById('unitModalLabel');
    var btn = document.getElementById('unitFormSubmitBtn');
    if (mode === 'edit') {
        form.action = '<?php echo base_url('unit/edit'); ?>/' + id;
        label.textContent = 'Manage Unit';
        btn.textContent = 'Update Unit';
        document.getElementById('unit_code').value = code || '';
        document.getElementById('unit_name').value = name || '';
    } else {
        form.action = '<?php echo base_url('unit/create'); ?>';
        label.textContent = 'Add New Unit';
        btn.textContent = 'Add Unit';
        document.getElementById('unit_code').value = '';
        document.getElementById('unit_name').value = '';
    }
    new bootstrap.Modal(document.getElementById('unitModal')).show();
}

<?php if ($modal_mode === 'edit' && $modal_edit_id): ?>
document.addEventListener('DOMContentLoaded', function () {
    openUnitModal('edit', <?php echo $modal_edit_id; ?>, '<?php echo addslashes(old('unit_code', '')); ?>', '<?php echo addslashes(old('unit_name', '')); ?>');
});
<?php elseif ($modal_mode === 'create'): ?>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('unit_code').value = '<?php echo addslashes(old('unit_code', '')); ?>';
    document.getElementById('unit_name').value = '<?php echo addslashes(old('unit_name', '')); ?>';
    new bootstrap.Modal(document.getElementById('unitModal')).show();
});
<?php endif; ?>
document.getElementById('unitModal')?.addEventListener('hidden.bs.modal', function () {
    var err = this.querySelector('.modal-body .alert.alert-danger');
    if (err) err.remove();
});
</script>

<?php if (!empty($units)): ?>
    <?php foreach ($units as $unit): ?>
    <div class="modal fade" id="deactivateUnitModal-<?php echo $unit['unit_id']; ?>" tabindex="-1" aria-labelledby="deactivateUnitModalLabel-<?php echo $unit['unit_id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="modal-title fw-bold mb-0" id="deactivateUnitModalLabel-<?php echo $unit['unit_id']; ?>" style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                            Deactivate Unit
                        </h5>
                    </div>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <div class="p-3 bg-light rounded-3 border border-light-subtle mb-3">
                        <div class="fw-bold text-dark" style="font-size: 0.95rem;"><?php echo htmlspecialchars($unit['unit_name']); ?></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($unit['unit_code']); ?></div>
                    </div>
                    <p class="text-secondary mb-0" style="font-size: 0.925rem; line-height: 1.5;">Are you sure you want to deactivate this unit?</p>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                    <button type="button"
                            data-bs-dismiss="modal"
                            style="background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                            onmouseover="this.style.background='#f9fafb'"
                            onmouseout="this.style.background='#fff'">
                        Close
                    </button>
                    <a href="<?php echo base_url('unit/deactivate/' . $unit['unit_id']); ?>"
                       style="background: #ef4444; color: #fff; border: 1px solid transparent; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; height: 38px;"
                       onmouseover="this.style.background='#dc2626'"
                       onmouseout="this.style.background='#ef4444'">
                        Deactivate Unit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reactivateUnitModal-<?php echo $unit['unit_id']; ?>" tabindex="-1" aria-labelledby="reactivateUnitModalLabel-<?php echo $unit['unit_id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="modal-title fw-bold mb-0" id="reactivateUnitModalLabel-<?php echo $unit['unit_id']; ?>" style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                            Reactivate Unit
                        </h5>
                    </div>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <div class="p-3 bg-light rounded-3 border border-light-subtle mb-3">
                        <div class="fw-bold text-dark" style="font-size: 0.95rem;"><?php echo htmlspecialchars($unit['unit_name']); ?></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($unit['unit_code']); ?></div>
                    </div>
                    <p class="text-secondary mb-0" style="font-size: 0.925rem; line-height: 1.5;">Are you sure you want to reactivate this unit?</p>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                    <button type="button"
                            data-bs-dismiss="modal"
                            style="background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                            onmouseover="this.style.background='#f9fafb'"
                            onmouseout="this.style.background='#fff'">
                        Close
                    </button>
                    <a href="<?php echo base_url('unit/reactivate/' . $unit['unit_id']); ?>"
                       class="btn btn-success-custom">
                        Reactivate Unit
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
