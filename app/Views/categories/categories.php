<!-- Page Title Section -->
<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">Categories</h1>
        </div>
        <div>
            <button type="button"
                    class="btn d-flex align-items-center gap-2"
                    id="btnAddNewCategory"
                    onclick="openCategoryModal('create')"
                    style="background: #10b981; color: #fff; font-weight: 600; border: none; padding: 0.5rem 1.1rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(16,185,129,0.3); transition: background 0.2s;">
                <i class="fa-solid fa-plus"></i>
                <span>Add Category</span>
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
            <span>Inventory Categories</span>
        </h5>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-custom table-hover w-100" id="categoriesTable">
            <thead>
                <tr>
                    <th style="width: 20%">Code</th>
                    <th style="width: 65%">Category</th>
                    <th style="width: 15%" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td class="fw text-dark" style="font-size: 0.875rem;">
                                <?php echo htmlspecialchars($category['category_code']); ?>
                            </td>
                            <td class="fw text-dark">
                                <?php echo !empty($category['category_description']) ? htmlspecialchars($category['category_description']) : 'N/A'; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center rounded-2"
                                            style="width: 32px; height: 32px;"
                                            onclick="openCategoryModal('edit', <?php echo $category['category_id']; ?>, '<?php echo htmlspecialchars($category['category_code'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($category['category_description'] ?? '', ENT_QUOTES); ?>')"
                                            title="Edit Category">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center rounded-2"
                                            style="width: 32px; height: 32px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#archiveCategoryModal-<?php echo $category['category_id']; ?>"
                                            title="Archive Category">
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
                            <span class="fw-medium">No categories found.</span>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================== SINGLE CATEGORY MODAL (Add/Edit) ===================== -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="modal-title fw-bold mb-0" id="categoryModalLabel"
                        style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                        Add New Category
                    </h5>
                </div>
                <button type="button"
                        class="btn-close btn-close-dark"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        style="opacity: 0.6;"></button>
            </div>

            <form method="POST" action="<?php echo base_url('categories/create'); ?>" id="categoryForm">
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
                        <label for="category_code" class="form-label small fw-semibold text-secondary">
                            Category Code <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control input-custom text-uppercase"
                               id="category_code"
                               name="category_code"
                               value="<?php echo old('category_code'); ?>"
                               required>
                    </div>

                    <div>
                        <label for="category_description" class="form-label small fw-semibold text-secondary">
                            Description
                        </label>
                        <input type="text"
                               class="form-control input-custom"
                               id="category_description"
                               name="category_description"
                               value="<?php echo old('category_description'); ?>">
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
                    <button type="submit" id="categoryFormSubmitBtn"
                            style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(16,185,129,0.3); transition: background 0.15s, box-shadow 0.15s;"
                            onmouseover="this.style.background='#059669';this.style.boxShadow='0 4px 12px rgba(16,185,129,0.4)'"
                            onmouseout="this.style.background='#10b981';this.style.boxShadow='0 2px 8px rgba(16,185,129,0.3)'">
                        Save Category
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
function openCategoryModal(mode, id, code, desc) {
    var form = document.getElementById('categoryForm');
    var label = document.getElementById('categoryModalLabel');
    var btn = document.getElementById('categoryFormSubmitBtn');
    if (mode === 'edit') {
        form.action = '<?php echo base_url('categories/edit'); ?>/' + id;
        label.textContent = 'Edit Category';
        btn.textContent = 'Update Category';
        document.getElementById('category_code').value = code || '';
        document.getElementById('category_description').value = desc || '';
    } else {
        form.action = '<?php echo base_url('categories/create'); ?>';
        label.textContent = 'Add New Category';
        btn.textContent = 'Save Category';
        document.getElementById('category_code').value = '';
        document.getElementById('category_description').value = '';
    }
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

<?php if ($modal_mode === 'edit' && $modal_edit_id): ?>
document.addEventListener('DOMContentLoaded', function () {
    openCategoryModal('edit', <?php echo $modal_edit_id; ?>, '<?php echo addslashes(old('category_code', '')); ?>', '<?php echo addslashes(old('category_description', '')); ?>');
});
<?php elseif ($modal_mode === 'create'): ?>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('category_code').value = '<?php echo addslashes(old('category_code', '')); ?>';
    document.getElementById('category_description').value = '<?php echo addslashes(old('category_description', '')); ?>';
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
});
<?php endif; ?>
</script>

<?php if (!empty($categories)): ?>
    <?php foreach ($categories as $category): ?>
    <!-- ===================== ARCHIVE CATEGORY MODAL ===================== -->
    <div class="modal fade" id="archiveCategoryModal-<?php echo $category['category_id']; ?>" tabindex="-1"
         aria-labelledby="archiveCategoryModalLabel-<?php echo $category['category_id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

                <!-- Modal Header -->
                <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="modal-title fw-bold mb-0" id="archiveCategoryModalLabel-<?php echo $category['category_id']; ?>"
                            style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                            Archive Category
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
                            <div style="width: 40px; height: 40px; border-radius: 10px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="bi bi-tags" style="color: #0369a1; font-size: 1.1rem;"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                    <?php echo htmlspecialchars($category['category_code']); ?>
                                </div>
                                <div class="text-muted small">
                                    <?php echo !empty($category['category_description']) ? htmlspecialchars($category['category_description']) : 'No description'; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-secondary mb-0" style="font-size: 0.925rem; line-height: 1.5;">
                        Are you sure you want to archive this category? It will be hidden from the active list but can be restored later.
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
                    <a href="<?php echo base_url('categories/archive/' . $category['category_id']); ?>"
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
                        Archive Category
                    </a>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<style>
    #btnAddNewCategory:hover { background: #059669 !important; box-shadow: 0 4px 12px rgba(16,185,129,0.4) !important; }
</style>
