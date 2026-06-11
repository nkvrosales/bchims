<!-- Page Title Section -->
<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">Departments</h1>
        </div>
        <div>
            <button type="button"
                    class="btn d-flex align-items-center gap-2"
                    id="btnAddNewDept"
                    onclick="openDeptModal('create')"
                    style="background: #10b981; color: #fff; font-weight: 600; border: none; padding: 0.5rem 1.1rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(16,185,129,0.3); transition: background 0.2s;">
                <i class="fa-solid fa-plus"></i>
                <span>Add Department</span>
            </button>
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

<!-- Departments Table -->

    <div class="table-responsive-custom">
        <table class="table table-custom table-hover w-100" id="departmentsTable">
            <thead>
                <tr>
                    <th style="width: 20%">Department Code</th>
                    <th style="width: 65%">Department Name</th>
                    <th style="width: 15%" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($departments)): ?>
                    <?php foreach ($departments as $dept): ?>
                        <tr>
                            <td>
                                <span class="fw text-dark" style="font-size: 0.9rem;"><?php echo htmlspecialchars($dept['code'] ?? '—'); ?></span>
                            </td>

                            <td>
                                <span class="fw text-dark"><?php echo htmlspecialchars($dept['name']); ?></span>
                            </td>

                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center rounded-2"
                                            style="width: 32px; height: 32px;"
                                            onclick="openDeptModal('edit', <?php echo $dept['id']; ?>, '<?php echo htmlspecialchars($dept['code'] ?? '', ENT_QUOTES); ?>', '<?php echo htmlspecialchars($dept['name'], ENT_QUOTES); ?>')"
                                            title="Edit Department">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center rounded-2"
                                            style="width: 32px; height: 32px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#archiveDeptModal-<?php echo $dept['id']; ?>"
                                            title="Archive Department">
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
                            <span class="fw-medium">No departments found.</span>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>


<!-- ===================== CREATE DEPARTMENT MODAL ===================== -->
<!-- ===================== SINGLE DEPARTMENT MODAL (Add/Edit) ===================== -->
<div class="modal fade" id="deptModal" tabindex="-1" aria-labelledby="deptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="modal-title fw-bold mb-0" id="deptModalLabel"
                        style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                        Add New Department
                    </h5>
                </div>
                <button type="button"
                        class="btn-close btn-close-dark"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        style="opacity: 0.6;"></button>
            </div>

            <form method="POST" action="<?php echo base_url('departments/create'); ?>" id="deptForm">
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
                        <label for="dept_code" class="form-label small fw-semibold text-secondary">
                            Department Code <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control input-custom"
                               id="dept_code"
                               name="code"
                               value="<?php echo old('code'); ?>"
                               required>
                    </div>

                    <div>
                        <label for="dept_name" class="form-label small fw-semibold text-secondary">
                            Department Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control input-custom"
                               id="dept_name"
                               name="name"
                               value="<?php echo old('name'); ?>"
                               required>
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
                    <button type="submit" id="deptFormSubmitBtn"
                            style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(16,185,129,0.3); transition: background 0.15s, box-shadow 0.15s;"
                            onmouseover="this.style.background='#059669';this.style.boxShadow='0 4px 12px rgba(16,185,129,0.4)'"
                            onmouseout="this.style.background='#10b981';this.style.boxShadow='0 2px 8px rgba(16,185,129,0.3)'">
                        Save Department
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
function openDeptModal(mode, id, code, name) {
    var form = document.getElementById('deptForm');
    var label = document.getElementById('deptModalLabel');
    var btn = document.getElementById('deptFormSubmitBtn');
    if (mode === 'edit') {
        form.action = '<?php echo base_url('departments/edit'); ?>/' + id;
        label.textContent = 'Edit Department';
        btn.textContent = 'Update Department';
        document.getElementById('dept_code').value = code || '';
        document.getElementById('dept_name').value = name || '';
    } else {
        form.action = '<?php echo base_url('departments/create'); ?>';
        label.textContent = 'Add New Department';
        btn.textContent = 'Save Department';
        document.getElementById('dept_code').value = '';
        document.getElementById('dept_name').value = '';
    }
    new bootstrap.Modal(document.getElementById('deptModal')).show();
}

<?php if ($modal_mode === 'edit' && $modal_edit_id): ?>
document.addEventListener('DOMContentLoaded', function () {
    openDeptModal('edit', <?php echo $modal_edit_id; ?>, '<?php echo addslashes(old('code', '')); ?>', '<?php echo addslashes(old('name', '')); ?>');
});
<?php elseif ($modal_mode === 'create'): ?>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('dept_code').value = '<?php echo addslashes(old('code', '')); ?>';
    document.getElementById('dept_name').value = '<?php echo addslashes(old('name', '')); ?>';
    new bootstrap.Modal(document.getElementById('deptModal')).show();
});
<?php endif; ?>
</script>

<?php if (!empty($departments)): ?>
    <?php foreach ($departments as $dept): ?>
    <!-- ===================== ARCHIVE DEPARTMENT MODAL ===================== -->
    <div class="modal fade" id="archiveDeptModal-<?php echo $dept['id']; ?>" tabindex="-1"
         aria-labelledby="archiveDeptModalLabel-<?php echo $dept['id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

                <!-- Modal Header -->
                <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="modal-title fw-bold mb-0" id="archiveDeptModalLabel-<?php echo $dept['id']; ?>"
                            style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                            Archive Department
                        </h5>
                    </div>
                    <button type="button"
                            class="btn-close btn-close-dark"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                            style="opacity: 0.6;"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body px-4 py-4">
                    <div class="p-3 bg-light rounded-3 border border-light-subtle mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </div>
                                <div class="text-muted small">Hospital Department</div>
                            </div>
                        </div>
                    </div>

                    <p class="text-secondary mb-0" style="font-size: 0.925rem; line-height: 1.5;">
                        Are you sure you want to archive this department?
                    </p>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                    <button type="button"
                            data-bs-dismiss="modal"
                            style="background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                            onmouseover="this.style.background='#f9fafb'"
                            onmouseout="this.style.background='#fff'">
                        Cancel
                    </button>
                    <a href="<?php echo base_url('departments/archive/' . $dept['id']); ?>"
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
                        Archive Department
                    </a>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<style>
    #btnAddNewDept:hover { background: #059669 !important; box-shadow: 0 4px 12px rgba(16,185,129,0.4) !important; }
</style>
