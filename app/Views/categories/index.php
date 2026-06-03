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
                    data-bs-toggle="modal"
                    data-bs-target="#createCategoryModal"
                    style="background: #10b981; color: #fff; font-weight: 600; border: none; padding: 0.5rem 1.1rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(16,185,129,0.3); transition: background 0.2s;">
                <i class="fa-solid fa-circle-plus"></i>
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
            <i class="bi bi-tags text-primary"></i>
            <span>Inventory Categories</span>
        </h5>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-custom table-hover w-100">
            <thead>
                <tr>
                    <th style="width: 20%">Code</th>
                    <th style="width: 65%">Description</th>
                    <th style="width: 15%" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td class="font-monospace fw-bold text-dark" style="font-size: 0.875rem;">
                                <?php echo htmlspecialchars($category['category_code']); ?>
                            </td>
                            <td class="text-secondary">
                                <?php echo !empty($category['category_description']) ? htmlspecialchars($category['category_description']) : 'N/A'; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center rounded-2"
                                            style="width: 32px; height: 32px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editCategoryModal-<?php echo $category['category_id']; ?>"
                                            title="Edit Category">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center rounded-2"
                                            style="width: 32px; height: 32px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteCategoryModal-<?php echo $category['category_id']; ?>"
                                            title="Delete Category">
                                        <i class="fa-solid fa-trash"></i>
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

<!-- ===================== CREATE CATEGORY MODAL ===================== -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <!-- Modal Header -->
            <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa-solid fa-circle-plus" style="color: #10b981; font-size: 1.2rem;"></i>
                    <h5 class="modal-title fw-bold mb-0" id="createCategoryModalLabel"
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

            <!-- Form -->
            <form method="POST" action="<?php echo base_url('categories/create'); ?>">
                <div class="modal-body px-4 py-4">

                    <!-- Validation Errors -->
                    <?php if ($create_errors = session()->getFlashdata('create_validation_errors')): ?>
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

                    <!-- Category Code -->
                    <div class="mb-3">
                        <label for="create_category_code" class="form-label small fw-semibold text-secondary">
                            Category Code <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control input-custom text-uppercase"
                               id="create_category_code"
                               name="category_code"
                               value="<?php echo old('category_code'); ?>"
                               required>
                    </div>

                    <!-- Category Description -->
                    <div>
                        <label for="create_category_description" class="form-label small fw-semibold text-secondary">
                            Description
                        </label>
                        <input type="text"
                               class="form-control input-custom"
                               id="create_category_description"
                               name="category_description"
                               value="<?php echo old('category_description'); ?>">
                    </div>

                </div><!-- /.modal-body -->

                <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                    <button type="button"
                            data-bs-dismiss="modal"
                            style="background: #fff; color: #374151; border: 1.5px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.4rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s;"
                            onmouseover="this.style.background='#f9fafb'"
                            onmouseout="this.style.background='#fff'">
                        Cancel
                    </button>
                    <button type="submit"
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
<!-- ============================================================= -->

<?php if (!empty($categories)): ?>
    <?php foreach ($categories as $category):
        $open_id  = session()->getFlashdata('edit_modal_open_id');
        $is_open  = ($open_id == $category['category_id']);
        $val_code = $is_open ? old('category_code', $category['category_code']) : $category['category_code'];
        $val_desc = $is_open ? old('category_description', $category['category_description']) : $category['category_description'];
    ?>

    <!-- ===================== EDIT CATEGORY MODAL ===================== -->
    <div class="modal fade" id="editCategoryModal-<?php echo $category['category_id']; ?>" tabindex="-1"
         aria-labelledby="editCategoryModalLabel-<?php echo $category['category_id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

                <!-- Modal Header -->
                <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fa-solid fa-pen-to-square" style="color: #000000; font-size: 1.1rem;"></i>
                        <h5 class="modal-title fw-bold mb-0" id="editCategoryModalLabel-<?php echo $category['category_id']; ?>"
                            style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                            Edit Category
                        </h5>
                    </div>
                    <button type="button"
                            class="btn-close btn-close-dark"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                            style="opacity: 0.6;"></button>
                </div>

                <!-- Form -->
                <form method="POST" action="<?php echo base_url('categories/edit/' . $category['category_id']); ?>">
                    <div class="modal-body px-4 py-4">

                        <!-- Validation Errors -->
                        <?php if ($is_open && $edit_errors = session()->getFlashdata('edit_validation_errors')): ?>
                        <div class="alert alert-danger border-0 rounded-3 mb-4 py-3">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                                <div>
                                    <span class="fw-bold d-block mb-1">Please correct the errors below:</span>
                                    <div class="small"><?php echo $edit_errors; ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Category Code -->
                        <div class="mb-3">
                            <label for="edit_category_code_<?php echo $category['category_id']; ?>" class="form-label small fw-semibold text-secondary">
                                Category Code <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control input-custom text-uppercase"
                                   id="edit_category_code_<?php echo $category['category_id']; ?>"
                                   name="category_code"
                                   value="<?php echo htmlspecialchars($val_code); ?>"
                                   required>
                        </div>

                        <!-- Category Description -->
                        <div>
                            <label for="edit_category_description_<?php echo $category['category_id']; ?>" class="form-label small fw-semibold text-secondary">
                                Description
                            </label>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="edit_category_description_<?php echo $category['category_id']; ?>"
                                   name="category_description"
                                   value="<?php echo htmlspecialchars($val_desc); ?>">
                        </div>

                    </div><!-- /.modal-body -->

                    <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                        <button type="button"
                                data-bs-dismiss="modal"
                                style="background: #fff; color: #374151; border: 1.5px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.4rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s;"
                                onmouseover="this.style.background='#f9fafb'"
                                onmouseout="this.style.background='#fff'">
                            Cancel
                        </button>
                        <button type="submit"
                                style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(16,185,129,0.3); transition: background 0.15s, box-shadow 0.15s;"
                                onmouseover="this.style.background='#059669';this.style.boxShadow='0 4px 12px rgba(16,185,129,0.4)'"
                                onmouseout="this.style.background='#10b981';this.style.boxShadow='0 2px 8px rgba(16,185,129,0.3)'">
                            Update Category
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Auto-open edit modal on validation error -->
    <?php if ($is_open): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('editCategoryModal-<?php echo $category['category_id']; ?>');
            if (el) { new bootstrap.Modal(el).show(); }
        });
    </script>
    <?php endif; ?>

    <!-- ===================== DELETE CATEGORY MODAL ===================== -->
    <div class="modal fade" id="deleteCategoryModal-<?php echo $category['category_id']; ?>" tabindex="-1"
         aria-labelledby="deleteCategoryModalLabel-<?php echo $category['category_id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

                <!-- Modal Header -->
                <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fa-solid fa-trash-can" style="color: #ef4444; font-size: 1.1rem;"></i>
                        <h5 class="modal-title fw-bold mb-0" id="deleteCategoryModalLabel-<?php echo $category['category_id']; ?>"
                            style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                            Delete Category
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
                        Are you sure you want to permanently delete this category? This action cannot be undone and will be recorded in the system audit trail.
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
                    <a href="<?php echo base_url('categories/delete/' . $category['category_id']); ?>"
                       style="background: #ef4444; color: #fff; border: 1px solid transparent; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; text-decoration: none; cursor: pointer; box-shadow: 0 2px 8px rgba(239,68,68,0.3); transition: background 0.15s, box-shadow 0.15s; display: inline-flex; align-items: center; height: 38px;"
                       onmouseover="this.style.background='#dc2626';this.style.boxShadow='0 4px 12px rgba(239,68,68,0.4)'"
                       onmouseout="this.style.background='#ef4444';this.style.boxShadow='0 2px 8px rgba(239,68,68,0.3)'">
                        Delete Category
                    </a>
                </div>

            </div>
        </div>
    </div>

    <?php endforeach; ?>
<?php endif; ?>

<!-- Auto-open create modal on validation failure -->
<?php if (session()->getFlashdata('create_modal_open')): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('createCategoryModal');
        if (el) { new bootstrap.Modal(el).show(); }
    });
</script>
<?php endif; ?>

<style>
    #btnAddNewCategory:hover { background: #059669 !important; box-shadow: 0 4px 12px rgba(16,185,129,0.4) !important; }
</style>
