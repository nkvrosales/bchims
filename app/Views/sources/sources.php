<!-- Page Title Section -->
<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">Sources</h1>
        </div>
        <div>
            <button type="button"
                    class="btn d-flex align-items-center gap-2"
                    id="btnAddNewSource"
                    onclick="openSourceModal('create')"
                    style="background: #10b981; color: #fff; font-weight: 600; border: none; padding: 0.5rem 1.1rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(16,185,129,0.3); transition: background 0.2s;">
                <i class="fa-solid fa-plus"></i>
                <span>Add Source</span>
            </button>
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

<div class="standard-card fade-in-up" style="animation-delay: 0.1s;">
    <div class="card-header-styled mb-4">
        <h5 class="card-title-styled">
            <span>Suppliers & Sources</span>
        </h5>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-custom table-hover w-100" id="sourcesTable">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Supplier Name</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($sources)): ?>
                    <?php foreach ($sources as $source): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($source['source_type']); ?></td>
                            <td class="fw text-dark"><?php echo htmlspecialchars($source['supplier_name']); ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center rounded-2"
                                            style="width: 32px; height: 32px;"
                                            onclick="openSourceModal('edit', <?php echo $source['source_id']; ?>, '<?php echo htmlspecialchars($source['source_type'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($source['supplier_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($source['contact_person'] ?? '', ENT_QUOTES); ?>', '<?php echo htmlspecialchars($source['contact_number'] ?? '', ENT_QUOTES); ?>', '<?php echo htmlspecialchars($source['address'] ?? '', ENT_QUOTES); ?>')"
                                            title="Edit Source">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center rounded-2"
                                            style="width: 32px; height: 32px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#archiveSourceModal-<?php echo $source['source_id']; ?>"
                                            title="Archive Source">
                                        <i class="fa-regular fa-folder"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center py-5 text-muted">
                            <i class="fa-regular fa-folder-open d-block fs-2 mb-2 text-secondary"></i>
                            <span class="fw-medium">No sources found.</span>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================== SINGLE SOURCE MODAL (Add/Edit) ===================== -->
<div class="modal fade" id="sourceModal" tabindex="-1" aria-labelledby="sourceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="modal-title fw-bold mb-0" id="sourceModalLabel"
                        style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                        Add New Source
                    </h5>
                </div>
                <button type="button"
                        class="btn-close btn-close-dark"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        style="opacity: 0.6;"></button>
            </div>

            <form method="POST" action="<?php echo base_url('sources/create'); ?>" id="sourceForm">
                <div class="modal-body px-4 py-4">

                    <?php
                        $modal_errors = session()->getFlashdata('modal_errors');
                        $modal_mode   = session()->getFlashdata('modal_mode');
                        $modal_edit_id = session()->getFlashdata('modal_edit_id');
                    ?>
                    <?php if ($modal_errors): ?>
                    <div class="alert alert-danger border-0 rounded-3 mb-4 py-3">
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
                        <label for="source_type" class="form-label small fw-semibold text-secondary">
                            Source Type <span class="text-danger">*</span>
                        </label>
                        <select class="form-select input-custom" id="source_type" name="source_type" required>
                            <option value="" disabled selected hidden>Select Type</option>
                            <option value="Supplier" <?php echo old('source_type') === 'Supplier' ? 'selected' : ''; ?>>Supplier</option>
                            <option value="Donation" <?php echo old('source_type') === 'Donation' ? 'selected' : ''; ?>>Donation</option>
                            <option value="Others" <?php echo old('source_type') === 'Others' ? 'selected' : ''; ?> style="display:none;">Others</option>
                        </select>
                    </div>

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
                        Cancel
                    </button>
                    <button type="submit" id="sourceFormSubmitBtn"
                            style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(16,185,129,0.3); transition: background 0.15s, box-shadow 0.15s;"
                            onmouseover="this.style.background='#059669';this.style.boxShadow='0 4px 12px rgba(16,185,129,0.4)'"
                            onmouseout="this.style.background='#10b981';this.style.boxShadow='0 2px 8px rgba(16,185,129,0.3)'">
                        Save Source
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
function openSourceModal(mode, id, type, name, person, number, address) {
    var form = document.getElementById('sourceForm');
    var label = document.getElementById('sourceModalLabel');
    var btn = document.getElementById('sourceFormSubmitBtn');
    if (mode === 'edit') {
        form.action = '<?php echo base_url('sources/edit'); ?>/' + id;
        label.textContent = 'Edit Source';
        btn.textContent = 'Update Source';
        document.getElementById('source_type').value = type || '';
        document.getElementById('supplier_name').value = name || '';
        document.getElementById('contact_person').value = person || '';
        document.getElementById('contact_number').value = number || '';
        document.getElementById('address').value = address || '';
    } else {
        form.action = '<?php echo base_url('sources/create'); ?>';
        label.textContent = 'Add New Source';
        btn.textContent = 'Save Source';
        document.getElementById('source_type').value = '';
        document.getElementById('supplier_name').value = '';
        document.getElementById('contact_person').value = '';
        document.getElementById('contact_number').value = '';
        document.getElementById('address').value = '';
    }
    new bootstrap.Modal(document.getElementById('sourceModal')).show();
}

<?php if ($modal_mode === 'edit' && $modal_edit_id): ?>
document.addEventListener('DOMContentLoaded', function () {
    openSourceModal('edit', <?php echo $modal_edit_id; ?>, '<?php echo addslashes(old('source_type', '')); ?>', '<?php echo addslashes(old('supplier_name', '')); ?>', '<?php echo addslashes(old('contact_person', '')); ?>', '<?php echo addslashes(old('contact_number', '')); ?>', '<?php echo addslashes(old('address', '')); ?>');
});
<?php elseif ($modal_mode === 'create'): ?>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('source_type').value = '<?php echo addslashes(old('source_type', '')); ?>';
    document.getElementById('supplier_name').value = '<?php echo addslashes(old('supplier_name', '')); ?>';
    document.getElementById('contact_person').value = '<?php echo addslashes(old('contact_person', '')); ?>';
    document.getElementById('contact_number').value = '<?php echo addslashes(old('contact_number', '')); ?>';
    document.getElementById('address').value = '<?php echo addslashes(old('address', '')); ?>';
    new bootstrap.Modal(document.getElementById('sourceModal')).show();
});
<?php endif; ?>
</script>

<?php if (!empty($sources)): ?>
    <?php foreach ($sources as $source): ?>
    <!-- ===================== ARCHIVE SOURCE MODAL ===================== -->
    <div class="modal fade" id="archiveSourceModal-<?php echo $source['source_id']; ?>" tabindex="-1"
         aria-labelledby="archiveSourceModalLabel-<?php echo $source['source_id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

                <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="modal-title fw-bold mb-0" id="archiveSourceModalLabel-<?php echo $source['source_id']; ?>"
                            style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                            Archive Source
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
                            <div style="width: 40px; height: 40px; border-radius: 10px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="bi bi-truck" style="color: #0369a1; font-size: 1.1rem;"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                    <?php echo htmlspecialchars($source['supplier_name']); ?>
                                </div>
                                <div class="text-muted small"><?php echo htmlspecialchars($source['source_type']); ?></div>
                            </div>
                        </div>
                    </div>

                    <p class="text-secondary mb-0" style="font-size: 0.925rem; line-height: 1.5;">
                        Are you sure you want to archive this source? It will be hidden from the active list but can be restored later.
                    </p>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                    <button type="button"
                            data-bs-dismiss="modal"
                            style="background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                            onmouseover="this.style.background='#f9fafb'"
                            onmouseout="this.style.background='#fff'">
                        Cancel
                    </button>
                    <a href="<?php echo base_url('sources/archive/' . $source['source_id']); ?>"
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
                               box-shadow: 0 2px 8px rgba(245,158,11,0.3);
                               transition: background 0.15s, box-shadow 0.15s;
                               display: inline-flex;
                               align-items: center;
                               height: 38px;
                           "
                           onmouseover="this.style.background='#dc2626';this.style.boxShadow='0 4px 12px rgba(245,158,11,0.4)'"
                           onmouseout="this.style.background='#ef4444';this.style.boxShadow='0 2px 8px rgba(245,158,11,0.3)'">
                        Archive Source
                    </a>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<style>
    #btnAddNewSource:hover { background: #059669 !important; box-shadow: 0 4px 12px rgba(16,185,129,0.4) !important; }
</style>
