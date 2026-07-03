<!-- Page Title Section -->
<div class="page-breadcrumb">
    <a href="<?php echo base_url('dashboard'); ?>">Dashboard</a>
    <span class="separator">/</span>
    <span class="current">Suppliers</span>
</div>

<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">Suppliers</h1>
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

<!-- Suppliers Search Bar -->
<form method="GET" action="<?php echo base_url('suppliers'); ?>" id="suppliersSearchForm">
    <div class="db-search-bar">
        <div class="db-search-field db-search-field--keyword">
            <input
                type="text"
                id="sup_search_keyword"
                name="search"
                class="db-search-input"
                placeholder=" "
                value="<?php echo htmlspecialchars($search ?? ''); ?>"
                autocomplete="off"
            >
            <label for="sup_search_keyword">Enter Supplier Name / Type</label>
        </div>
        <div class="db-search-field db-search-field--dropdown">
            <select id="sup_search_type" name="type_filter" class="db-search-select">
                <option value="">- Select Type -</option>
                <option value="Supplier" <?php echo (($type_filter ?? '') === 'Supplier') ? 'selected' : ''; ?>>Supplier</option>
                <option value="Donation" <?php echo (($type_filter ?? '') === 'Donation') ? 'selected' : ''; ?>>Donation</option>
                <option value="Others"   <?php echo (($type_filter ?? '') === 'Others')   ? 'selected' : ''; ?>>Others</option>
            </select>
            <label for="sup_search_type">Type</label>
        </div>
        <div class="db-search-actions">
            <button type="submit" class="btn-db-search" id="btnSupSearch">
                 Search
            </button>
            <a href="<?php echo base_url('suppliers'); ?>" class="btn-db-clear" id="btnSupClear">
                Clear
            </a>
            <div class="db-search-separator"></div>
            <button type="button"
                    class="btn btn-db-search d-inline-flex align-items-center gap-2"
                    id="btnAddNewSupplier"
                    onclick="openSupplierModal('create')">
                <i class="fa-solid fa-plus"></i>
                <span>Add Supplier</span>
            </button>
        </div>
    </div>
</form>

    <div class="table-responsive-custom">
        <table class="table table-custom table-hover w-100" id="suppliersTable">
            <thead>
                <tr>
                    <th>Supplier Name</th>
                    <th>Type</th>
                    <th style="width: 10%">Status</th>
                    <th style="width: 10%" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($sources)): ?>
                    <?php foreach ($sources as $source): ?>
                        <tr>
                            <td class="fw text-dark"><?php echo htmlspecialchars($source['supplier_name']); ?></td>
                            <td><?php echo htmlspecialchars($source['source_type']); ?></td>
                            <td class="text-center">
                                <?php if (($source['status'] ?? 1) == 1): ?>
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
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="openSupplierModal('edit', <?php echo $source['source_id']; ?>, '<?php echo htmlspecialchars($source['source_type'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($source['supplier_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($source['contact_person'] ?? '', ENT_QUOTES); ?>', '<?php echo htmlspecialchars($source['contact_number'] ?? '', ENT_QUOTES); ?>', '<?php echo htmlspecialchars($source['address'] ?? '', ENT_QUOTES); ?>')" title="Manage Supplier">Manage</a></li>
                                        <?php if (($source['status'] ?? 1) == 1): ?>
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#archiveSupplierModal-<?php echo $source['source_id']; ?>" title="Archive Supplier">Archive</a></li>
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

<!-- ===================== SINGLE SUPPLIER MODAL (Add/Edit) ===================== -->
<div class="modal fade" id="supplierModal" tabindex="-1" aria-labelledby="supplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="modal-title fw-bold mb-0" id="supplierModalLabel"
                        style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                        Add New Supplier
                    </h5>
                </div>
                <button type="button"
                        class="btn-close btn-close-dark"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        style="opacity: 0.6;"></button>
            </div>

            <form method="POST" action="<?php echo base_url('suppliers/create'); ?>" id="supplierForm">
                <div class="modal-body px-4 py-4">

                    <?php
                        $modal_errors = session()->getFlashdata('modal_errors');
                        $modal_mode   = session()->getFlashdata('modal_mode');
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
                        <label for="supplier_name" class="form-label small fw-semibold text-secondary">
                            Supplier Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control input-custom"
                               id="supplier_name"
                               name="supplier_name"
                               value="<?php echo old('supplier_name'); ?>"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="source_type" class="form-label small fw-semibold text-secondary">
                            Supplier Type <span class="text-danger">*</span>
                        </label>
                        <select class="form-select input-custom" id="source_type" name="source_type" required>
                            <option value="" disabled selected hidden>Select Type</option>
                            <option value="Supplier" <?php echo old('source_type') === 'Supplier' ? 'selected' : ''; ?>>Supplier</option>
                            <option value="Donation" <?php echo old('source_type') === 'Donation' ? 'selected' : ''; ?>>Donation</option>
                            <option value="Others" <?php echo old('source_type') === 'Others' ? 'selected' : ''; ?> style="display:none;">Others</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="contact_person" class="form-label small fw-semibold text-secondary">
                            Contact Person
                        </label>
                        <input type="text"
                               class="form-control input-custom"
                               id="contact_person"
                               name="contact_person"
                               value="<?php echo old('contact_person'); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="contact_number" class="form-label small fw-semibold text-secondary">
                            Contact Number
                        </label>
                        <input type="text"
                               class="form-control input-custom"
                               id="contact_number"
                               name="contact_number"
                               value="<?php echo old('contact_number'); ?>">
                    </div>

                    <div>
                        <label for="address" class="form-label small fw-semibold text-secondary">
                            Address
                        </label>
                        <textarea class="form-control input-custom"
                                  id="address"
                                  name="address"
                                  rows="2"><?php echo old('address'); ?></textarea>
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
                    <button type="submit" id="supplierFormSubmitBtn"
                            style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer;"
                            onmouseover="this.style.background='#059669'"
                            onmouseout="this.style.background='#10b981'">
                        Add Supplier
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
function openSupplierModal(mode, id, type, name, person, number, address) {
    var form = document.getElementById('supplierForm');
    var label = document.getElementById('supplierModalLabel');
    var btn = document.getElementById('supplierFormSubmitBtn');
    if (mode === 'edit') {
        form.action = '<?php echo base_url('suppliers/edit'); ?>/' + id;
        label.textContent = 'Manage Supplier';
        btn.textContent = 'Update Supplier';
        document.getElementById('source_type').value = type || '';
        document.getElementById('supplier_name').value = name || '';
        document.getElementById('contact_person').value = person || '';
        document.getElementById('contact_number').value = number || '';
        document.getElementById('address').value = address || '';
    } else {
        form.action = '<?php echo base_url('suppliers/create'); ?>';
        label.textContent = 'Add New Supplier';
        btn.textContent = 'Add Supplier';
        document.getElementById('source_type').value = '';
        document.getElementById('supplier_name').value = '';
        document.getElementById('contact_person').value = '';
        document.getElementById('contact_number').value = '';
        document.getElementById('address').value = '';
    }
    new bootstrap.Modal(document.getElementById('supplierModal')).show();
}

<?php if ($modal_mode === 'edit' && $modal_edit_id): ?>
document.addEventListener('DOMContentLoaded', function () {
    openSupplierModal('edit', <?php echo $modal_edit_id; ?>, '<?php echo addslashes(old('source_type', '')); ?>', '<?php echo addslashes(old('supplier_name', '')); ?>', '<?php echo addslashes(old('contact_person', '')); ?>', '<?php echo addslashes(old('contact_number', '')); ?>', '<?php echo addslashes(old('address', '')); ?>');
});
<?php elseif ($modal_mode === 'create'): ?>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('source_type').value = '<?php echo addslashes(old('source_type', '')); ?>';
    document.getElementById('supplier_name').value = '<?php echo addslashes(old('supplier_name', '')); ?>';
    document.getElementById('contact_person').value = '<?php echo addslashes(old('contact_person', '')); ?>';
    document.getElementById('contact_number').value = '<?php echo addslashes(old('contact_number', '')); ?>';
    document.getElementById('address').value = '<?php echo addslashes(old('address', '')); ?>';
    new bootstrap.Modal(document.getElementById('supplierModal')).show();
});
<?php endif; ?>
document.getElementById('supplierModal')?.addEventListener('hidden.bs.modal', function () {
    var err = this.querySelector('.modal-body .alert.alert-danger');
    if (err) err.remove();
});
</script>

<?php if (!empty($sources)): ?>
    <?php foreach ($sources as $source): ?>
    <!-- ===================== ARCHIVE SUPPLIER MODAL ===================== -->
    <div class="modal fade" id="archiveSupplierModal-<?php echo $source['source_id']; ?>" tabindex="-1"
         aria-labelledby="archiveSupplierModalLabel-<?php echo $source['source_id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

                <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="modal-title fw-bold mb-0" id="archiveSupplierModalLabel-<?php echo $source['source_id']; ?>"
                            style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                            Archive Supplier
                        </h5>
                    </div>
                    <button type="button"
                            class="btn-close btn-close-dark"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                            style="opacity: 0.6;"></button>
                </div>

                <div class="modal-body px-4 py-4">
                    <div class="p-3 bg-light rounded-3 border border-light-subtle mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                    <?php echo htmlspecialchars($source['supplier_name']); ?>
                                </div>
                                <div class="text-muted small"><?php echo htmlspecialchars($source['source_type']); ?></div>
                            </div>
                        </div>
                    </div>

                    <p class="text-secondary mb-0" style="font-size: 0.925rem; line-height: 1.5;">
                        Are you sure you want to archive this supplier?
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
                    <a href="<?php echo base_url('suppliers/archive/' . $source['source_id']); ?>"
                       style="
                               background: #ef4444;;
                               color: #fff;
                               border: 1px solid transparent;
                               border-radius: 8px;
                               padding: 0.5rem 1.5rem;
                               font-size: 0.9rem;
                               font-weight: 600;
                               text-decoration: none;
                                cursor: pointer;
                                display: inline-flex;
                                align-items: center;
                                height: 38px;
                            "
                            onmouseover="this.style.background='#dc2626'"
                            onmouseout="this.style.background='#ef4444'">
                         Archive Supplier
                    </a>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>


