<!-- Page Title Section -->
<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">Departments Directory</h1>
        </div>
        <div>
            <a href="<?php echo base_url('departments/create'); ?>" class="btn btn-primary d-flex align-items-center gap-2" id="btnAddNewDept">
                <i class="fa-solid fa-circle-plus"></i>
                <span>Add Department</span>
            </a>
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

<!-- Departments Table Card -->
<div class="standard-card fade-in-up" style="animation-delay: 0.1s;">
    <div class="card-header-styled mb-4">
        <h5 class="card-title-styled">
            <span>Hospital Units Database</span>
        </h5>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-custom table-hover w-100">
            <thead>
                <tr>
                    <th style="width: 15%">Code</th>
                    <th style="width: 25%">Name</th>
                    <th style="width: 40%">Description</th>
                    <th style="width: 10%">Created At</th>
                    <th style="width: 10%" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($departments)): ?>
                    <?php foreach ($departments as $dept): ?>
                        <tr>
                            <td class="font-monospace fw-bold text-dark" style="font-size: 0.875rem;">
                                <?php echo htmlspecialchars($dept['code']); ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-circle" style="width: 28px; height: 28px; font-size: 0.75rem; background: #e0f2fe; color: #0369a1;">
                                        <i class="fa-solid fa-hospital-user" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <span class="fw-semibold text-dark"><?php echo htmlspecialchars($dept['name']); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="text-secondary small">
                                    <?php echo !empty($dept['description']) ? htmlspecialchars($dept['description']) : '<em class="text-muted">No description provided.</em>'; ?>
                                </span>
                            </td>
                            <td class="small text-secondary">
                                <?php echo date('Y-m-d', strtotime($dept['created_at'])); ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="<?php echo base_url('departments/edit/' . $dept['id']); ?>" 
                                       class="btn btn-sm btn-outline-primary py-1 px-2 d-flex align-items-center gap-1 rounded-2" 
                                       title="Edit Department">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="<?php echo base_url('departments/delete/' . $dept['id']); ?>" 
                                       class="btn btn-sm btn-outline-danger py-1 px-2 d-flex align-items-center gap-1 rounded-2" 
                                       onclick="return confirm('Are you sure you want to permanently delete this department? This action will be logged.');"
                                       title="Delete Department">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fa-regular fa-folder-open d-block fs-2 mb-2 text-secondary"></i>
                            <span class="fw-medium">No department records found.</span>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
