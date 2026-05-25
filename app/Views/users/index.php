<!-- Page Title Section -->
<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">User Management</h1>
        </div>
        <div>
            <a href="<?php echo base_url('users/create'); ?>" class="btn btn-primary d-flex align-items-center gap-2" id="btnAddNewUser">
                <i class="fa-solid fa-user-plus"></i>
                <span>Add New User</span>
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

<!-- Users Table Card -->
<div class="standard-card fade-in-up" style="animation-delay: 0.1s;">
    <div class="card-header-styled mb-4">
        <h5 class="card-title-styled">
            <span>User Accounts Database</span>
        </h5>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-custom table-hover w-100">
            <thead>
                <tr>
                    <th style="width: 15%">Username</th>
                    <th style="width: 20%">Name</th>
                    <th style="width: 15%">Role</th>
                    <th style="width: 20%">Department</th>
                    <th style="width: 10%">Status</th>
                    <th style="width: 10%">Created At</th>
                    <th style="width: 10%" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php 
                    $current_user_id = session()->get('user_id');
                    foreach ($users as $u): 
                    ?>
                        <tr>
                            <td class="font-monospace fw-bold text-dark" style="font-size: 0.875rem;">
                                @<?php echo htmlspecialchars($u['username']); ?>
                                <?php if ((int)$u['id'] === (int)$current_user_id): ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill ms-1 font-sans" style="font-size:0.7rem; font-weight: 500;">You</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($u['full_name']); ?></span>
                            </td>
                            <td>
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span class="badge bg-indigo-subtle text-indigo border border-indigo-subtle rounded-2 px-2 py-1 small" style="background-color: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe;">
                                        <i class="fa-solid fa-user-shield me-1"></i>Administrator
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle rounded-2 px-2 py-1 small" style="background-color: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4;">
                                        <i class="fa-solid fa-user me-1"></i>Staff
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($u['department_code'])): ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-2 px-2 py-1 small">
                                        <i class="fa-solid fa-hospital me-1"></i><?php echo htmlspecialchars($u['department_code']); ?>
                                    </span>
                                    <small class="text-secondary d-block mt-1" style="font-size: 0.7rem;"><?php echo htmlspecialchars($u['department_name']); ?></small>
                                <?php else: ?>
                                    <span class="text-muted small"><i class="fa-solid fa-user-gear me-1"></i>None / Admin</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-2 px-2 py-1 small">
                                        Active
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-2 px-2 py-1 small">
                                        Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-secondary">
                                <?php echo date('Y-m-d', strtotime($u['created_at'])); ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="<?php echo base_url('users/edit/' . $u['id']); ?>" 
                                       class="btn btn-sm btn-outline-primary py-1 px-2 d-flex align-items-center gap-1 rounded-2" 
                                       title="Edit User">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    
                                    <?php if ((int)$u['id'] !== (int)$current_user_id): ?>
                                        <a href="<?php echo base_url('users/delete/' . $u['id']); ?>" 
                                           class="btn btn-sm btn-outline-danger py-1 px-2 d-flex align-items-center gap-1 rounded-2" 
                                           onclick="return confirm('Are you sure you want to permanently delete this user? This action will be logged.');"
                                           title="Delete User">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary py-1 px-2 d-flex align-items-center gap-1 rounded-2" 
                                                title="You cannot delete yourself" 
                                                disabled>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-regular fa-folder-open d-block fs-2 mb-2 text-secondary"></i>
                            <span class="fw-medium">No user records found.</span>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
