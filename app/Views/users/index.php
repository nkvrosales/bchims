<!-- Page Title Section -->
<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">User Management</h1>
        </div>
        <div>
            <button type="button"
                    class="btn d-flex align-items-center gap-2"
                    id="btnAddNewUser"
                    data-bs-toggle="modal"
                    data-bs-target="#createUserModal"
                    style="background: #10b981 ; color: #fff; font-weight: 600; border: none; padding: 0.5rem 1.1rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(34,197,94,0.3); transition: background 0.2s;">
                <i class="fa-solid fa-user-plus"></i>
                <span>Add New User</span>
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

<!-- Users Table Card -->
<div class="standard-card fade-in-up" style="animation-delay: 0.1s;">
    <div class="card-header-styled mb-4">
        <h5 class="card-title-styled">
            <i class="fa-solid fa-users text-primary"></i>
            <span>Users</span>
        </h5>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-custom table-hover w-100" id="usersTable">
            <thead>
                <tr>
                    <th style="width: 15%">Username</th>
                    <th style="width: 25%">Name</th>
                    <th style="width: 12%">Role</th>
                    <th style="width: 20%">Department</th>
                    <th style="width: 10%">Status</th>
                    <th style="width: 15%">Last Login</th>
                    <th style="width: 8%" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php
                    $avatar_palette = ['#1e293b','#7c3aed','#0369a1','#065f46','#be185d','#b45309','#0891b2','#9333ea','#15803d','#b91c1c'];
                    $current_user_id = session()->get('user_id');
                    foreach ($users as $u):
                        $initials = strtoupper(substr($u['first_name'], 0, 1)) . strtoupper(substr($u['last_name'], 0, 1));
                        $color = $avatar_palette[ord(strtoupper($u['last_name'][0] ?? 'A')) % count($avatar_palette)];
                    ?>
                    <tr>
                        <!-- Username -->
                        <td>
                            <span class="fw-semibold text-dark" style="font-size: 0.9rem;">
                                <?php echo htmlspecialchars($u['username']); ?>
                            </span>
                        </td>

                        <!-- Name Only -->
                        <td>
                            <span class="fw-semibold text-dark" style="font-size: 0.9rem;">
                                <?php echo htmlspecialchars($u['full_name']); ?>
                            </span>
                        </td>

                        <!-- Role -->
                        <td>
                            <?php 
                                $r = strtolower($u['role']);
                                if ($r === 'dev'):
                            ?>
                                <span class="badge rounded-2 px-2 py-1 small fw-semibold"
                                      style="background:#f3e8ff; color:#7c3aed; border:1px solid #d8b4fe;">
                                    DEV
                                </span>
                            <?php elseif ($r === 'admin' || $r === 'administrator'): ?>
                                <span class="badge rounded-2 px-2 py-1 small fw-semibold"
                                      style="background:#e0e7ff; color:#4338ca; border:1px solid #c7d2fe;">
                                    ADMIN
                                </span>
                            <?php elseif ($r === 'encoder'): ?>
                                <span class="badge rounded-2 px-2 py-1 small fw-semibold"
                                      style="background:#ccfbf1; color:#0f766e; border:1px solid #99f6e4;">
                                    ENCODER
                                </span>
                            <?php else: ?>
                                <span class="badge rounded-2 px-2 py-1 small fw-semibold"
                                      style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1;">
                                    VIEWER
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- Department -->
                        <td>
                            <span class="fw-semibold text-dark" style="font-size: 0.9rem;">
                                <?php if (!empty($u['department_code'])): ?>
                                    <?php echo htmlspecialchars($u['department_code']); ?>
                                <?php elseif (in_array(strtolower($u['role']), ['admin', 'dev', 'administrator'])): ?>
                                    Administrator
                                <?php else: ?>
                                    <span class="text-muted fw-normal">None</span>
                                <?php endif; ?>
                            </span>
                        </td>

                        <!-- Status with dot indicator -->
                        <td>
                            <?php if ($u['is_active']): ?>
                                <div class="d-flex align-items-center gap-2">
                                    <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;flex-shrink:0;box-shadow:0 0 0 2px rgba(34,197,94,0.2);"></span>
                                    <span class="small fw-semibold" style="color:#16a34a;">ACTIVE</span>
                                </div>
                            <?php else: ?>
                                <div class="d-flex align-items-center gap-2">
                                    <span style="width:8px;height:8px;border-radius:50%;background:#94a3b8;display:inline-block;flex-shrink:0;"></span>
                                    <span class="small fw-semibold text-secondary">INACTIVE</span>
                                </div>
                            <?php endif; ?>
                        </td>

                        <!-- Last Login -->
                        <td>
                            <span class="fw-semibold text-dark" style="font-size: 0.9rem;">
                                <?php echo !empty($u['last_login']) ? date('M j, Y g:i A', strtotime($u['last_login'])) : '—'; ?>
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center rounded-2"
                                        style="width: 32px; height: 32px;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editUserModal-<?php echo $u['id']; ?>"
                                        title="Edit User">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <?php if ((int)$u['id'] !== (int)$current_user_id): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center rounded-2"
                                            style="width: 32px; height: 32px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteUserModal-<?php echo $u['id']; ?>"
                                            title="Delete User">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center rounded-2"
                                            style="width: 32px; height: 32px; padding: 0 !important;"
                                            title="You cannot delete yourself"
                                            disabled>
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- ===================== CREATE USER MODAL ===================== -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <!-- Modal Header -->
            <div class="modal-header border-bottom px-4"
                 style=" padding-top: 1.1rem; padding-bottom: 1.1rem;">
                <div class="d-flex align-items-center">
                    <div style="
                        width: 40px; height: 40px;
                        border-radius: 10px;
                        background: rgba(255,255,255,0.12);
                        border: 1px solid rgba(255,255,255,0.18);
                        display: flex; align-items: center; justify-content: center;
                        flex-shrink: 0;
                    ">
                        <i class="fa-solid fa-user-plus" style="color: #000000ff; font-size: 1rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="createUserModalLabel"
                            style="color: #000000ff; font-size: 1.4rem; letter-spacing: -0.01em;">
                            Add New User
                        </h5>
                    </div>
                </div>
                <button type="button"
                        class="btn-close btn-close-dark"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        style="opacity: 0.6;"></button>
            </div>

            <!-- Form -->
            <form method="POST" action="<?php echo base_url('users/create'); ?>">
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

                    <div class="row g-3">

                        <!-- First Name -->
                        <div class="col-12 col-sm-6">
                            <label for="modal_first_name" class="form-label small fw-semibold text-secondary">
                                First Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="modal_first_name"
                                   name="first_name"
                                   value="<?php echo old('first_name'); ?>"
                                   required>
                        </div>

                        <!-- Last Name -->
                        <div class="col-12 col-sm-6">
                            <label for="modal_last_name" class="form-label small fw-semibold text-secondary">
                                Last Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="modal_last_name"
                                   name="last_name"
                                   value="<?php echo old('last_name'); ?>"
                                   required>
                        </div>

                        <!-- Username -->
                        <div class="col-12 col-sm-6">
                            <label for="modal_username" class="form-label small fw-semibold text-secondary">
                                Username <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="modal_username"
                                   name="username"
                                   value="<?php echo old('username'); ?>"
                                   required>
                        </div>

                        <!-- Email -->
                        <div class="col-12 col-sm-6">
                            <label for="modal_email" class="form-label small fw-semibold text-secondary">
                                Email
                            </label>
                            <input type="email"
                                   class="form-control input-custom"
                                   id="modal_email"
                                   name="email"
                                   value="<?php echo old('email'); ?>"
                                   >
                        </div>

                        <!-- Password -->
                        <div class="col-12 col-sm-6">
                            <label for="modal_password" class="form-label small fw-semibold text-secondary">
                                Password <span class="text-danger">*</span>
                            </label>
                            <div class="position-relative">
                                <input type="password"
                                       class="form-control input-custom"
                                       id="modal_password"
                                       name="password"
                                       required
                                       style="padding-right: 40px;">
                                <button type="button" id="toggleCreatePassword" tabindex="-1"
                                        style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); border: none; background: none; color: #475569; cursor: pointer; padding: 4px 8px; z-index: 5; display: none;">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Role -->
                        <div class="col-12 col-sm-6">
                            <label for="modal_role" class="form-label small fw-semibold text-secondary">
                                Role <span class="text-danger">*</span>
                            </label>
                            <select class="form-select input-custom" id="modal_role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="dev" <?php echo old('role') === 'dev' ? 'selected' : ''; ?>>Dev</option>
                                <option value="admin" <?php echo old('role') === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                                <option value="encoder" <?php echo old('role') === 'encoder' ? 'selected' : ''; ?>>Encoder</option>
                                <option value="viewer" <?php echo old('role') === 'viewer' ? 'selected' : ''; ?>>Viewer</option>
                            </select>
                        </div>

                        <!-- Department -->
                        <div class="col-12 col-sm-6">
                            <label for="modal_department_id" class="form-label small fw-semibold text-secondary">Department</label>
                            <select class="form-select input-custom" id="modal_department_id" name="department_id">
                                <option value="">Administrator</option>
                                <?php if (!empty($departments)): ?>
                                    <?php foreach ($departments as $d): ?>
                                        <option value="<?php echo $d['id']; ?>"
                                            <?php echo old('department_id') == $d['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($d['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-12 col-sm-6">
                            <label for="modal_is_active" class="form-label small fw-semibold text-secondary">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select class="form-select input-custom" id="modal_is_active" name="is_active" required>
                                <option value="1" <?php echo (old('is_active', '1') === '1') ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo old('is_active') === '0' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>

                    </div><!-- /.row -->
                </div><!-- /.modal-body -->

                <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end">
                    <button type="button"
                            data-bs-dismiss="modal"
                            style="
                                background: #fff;
                                color: #374151;
                                border: 1.5px solid #d1d5db;
                                border-radius: 8px;
                                padding: 0.5rem 1.4rem;
                                font-size: 0.9rem;
                                font-weight: 500;
                                cursor: pointer;
                                transition: background 0.15s, border-color 0.15s;
                            "
                            onmouseover="this.style.background='#f9fafb'"
                            onmouseout="this.style.background='#fff'">
                        Cancel
                    </button>
                    <button type="submit"
                            style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(16,185,129,0.3); transition: background 0.15s, box-shadow 0.15s;"
                            onmouseover="this.style.background='#059669';this.style.boxShadow='0 4px 12px rgba(16,185,129,0.4)'"
                            onmouseout="this.style.background='#10b981';this.style.boxShadow='0 2px 8px rgba(16,185,129,0.3)'">
                        Add User
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
<!-- ============================================================= -->

<?php if (!empty($users)): ?>
    <?php foreach ($users as $u):
        $current_user_id = session()->get('user_id');
        $is_self = ((int)$u['id'] === (int)$current_user_id);
        
        $initials = strtoupper(substr($u['first_name'], 0, 1)) . strtoupper(substr($u['last_name'], 0, 1));
        $color = $avatar_palette[ord(strtoupper($u['last_name'][0] ?? 'A')) % count($avatar_palette)];
        
        // Handle validation errors or old input values for this specific user edit modal
        $open_id = session()->getFlashdata('edit_modal_open_id');
        $is_open = ($open_id == $u['id']);
        
        $val_first_name = $is_open ? old('first_name', $u['first_name']) : $u['first_name'];
        $val_last_name = $is_open ? old('last_name', $u['last_name']) : $u['last_name'];
        $val_username = $is_open ? old('username', $u['username']) : $u['username'];
        $val_role = strtolower($is_open ? old('role', $u['role']) : $u['role']);
        $val_dept_id = $is_open ? old('department_id', $u['department_id']) : $u['department_id'];
        $val_is_active = $is_open ? old('is_active', $u['is_active']) : $u['is_active'];
    ?>
        <!-- ===================== EDIT USER MODAL (User: @<?php echo htmlspecialchars($u['username']); ?>) ===================== -->
        <div class="modal fade" id="editUserModal-<?php echo $u['id']; ?>" tabindex="-1" aria-labelledby="editUserModalLabel-<?php echo $u['id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

                    <!-- Modal Header -->
                    <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                        <div class="d-flex align-items-center">
                            <div style="
                                width: 40px; height: 40px;
                               
                                display: flex; align-items: center; justify-content: center;
                                flex-shrink: 0;
                            ">
                                <i class="fa-solid fa-user-pen" style="color: #000000ff; font-size: 1rem;"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0" id="editUserModalLabel-<?php echo $u['id']; ?>"
                                    style="color: #1e293b; font-size: 1.4rem; letter-spacing: -0.01em; margin-left: 0.75rem;">
                                    Edit User Account
                                </h5>
                                
                            </div>
                        </div>
                        <button type="button"
                                class="btn-close btn-close-dark"
                                data-bs-dismiss="modal"
                                aria-label="Close"
                                style="opacity: 0.6;"></button>
                    </div>

                    <!-- Form -->
                    <form method="POST" action="<?php echo base_url('users/edit/' . $u['id']); ?>">
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

                            <div class="row g-3">

                                <!-- First Name -->
                                <div class="col-12 col-sm-6">
                                    <label for="modal_edit_first_name_<?php echo $u['id']; ?>" class="form-label small fw-semibold text-secondary">
                                        First Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control input-custom"
                                           id="modal_edit_first_name_<?php echo $u['id']; ?>"
                                           name="first_name"
                                           value="<?php echo htmlspecialchars($val_first_name); ?>"
                                           required>
                                </div>

                                <!-- Last Name -->
                                <div class="col-12 col-sm-6">
                                    <label for="modal_edit_last_name_<?php echo $u['id']; ?>" class="form-label small fw-semibold text-secondary">
                                        Last Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control input-custom"
                                           id="modal_edit_last_name_<?php echo $u['id']; ?>"
                                           name="last_name"
                                           value="<?php echo htmlspecialchars($val_last_name); ?>"
                                           required>
                                </div>

                                <!-- Username -->
                                <div class="col-12 col-sm-6">
                                    <label for="modal_edit_username_<?php echo $u['id']; ?>" class="form-label small fw-semibold text-secondary">
                                        Username <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control input-custom"
                                           id="modal_edit_username_<?php echo $u['id']; ?>"
                                           name="username"
                                           value="<?php echo htmlspecialchars($val_username); ?>"
                                           required>
                                </div>

                                <!-- Email -->
                                <div class="col-12 col-sm-6">
                                    <label for="modal_edit_email_<?php echo $u['id']; ?>" class="form-label small fw-semibold text-secondary">
                                        Email
                                    </label>
                                    <input type="email"
                                           class="form-control input-custom"
                                           id="modal_edit_email_<?php echo $u['id']; ?>"
                                           name="email"
                                           value="<?php echo htmlspecialchars($is_open ? old('email', $u['email']) : $u['email']); ?>"
                                           >
                                </div>

                                <!-- Password (Optional) -->
                                <div class="col-12 col-sm-6">
                                    <label for="modal_edit_password_<?php echo $u['id']; ?>" class="form-label small fw-semibold text-secondary">
                                        New Password
                                    </label>
                                    <div class="position-relative">
                                        <input type="password"
                                               class="form-control input-custom"
                                               id="modal_edit_password_<?php echo $u['id']; ?>"
                                               name="password"
                                               placeholder="Leave blank to keep current"
                                               style="padding-right: 40px;">
                                        <button type="button" class="toggle-edit-password" data-target="modal_edit_password_<?php echo $u['id']; ?>" tabindex="-1"
                                                style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); border: none; background: none; color: #475569; cursor: pointer; padding: 4px 8px; z-index: 5; display: none;">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Role -->
                                <div class="col-12 col-sm-6">
                                    <label for="modal_edit_role_<?php echo $u['id']; ?>" class="form-label small fw-semibold text-secondary">
                                        Role <span class="text-danger">*</span>
                                    </label>
                                    <?php if ($is_self): ?>
                                        <select class="form-select input-custom bg-light" id="modal_edit_role_disabled_<?php echo $u['id']; ?>" disabled style="cursor: not-allowed;">
                                            <option value="<?php echo htmlspecialchars($val_role); ?>" selected><?php echo htmlspecialchars(ucfirst($val_role)); ?></option>
                                        </select>
                                        <input type="hidden" name="role" value="<?php echo htmlspecialchars($val_role); ?>">
                                        <div class="form-text small text-secondary"><i class="fa-solid fa-circle-info me-1"></i>You cannot demote your active admin session.</div>
                                    <?php else: ?>
                                        <select class="form-select input-custom" id="modal_edit_role_<?php echo $u['id']; ?>" name="role" required>
                                            <option value="dev" <?php echo ($val_role === 'dev') ? 'selected' : ''; ?>>Dev</option>
                                            <option value="admin" <?php echo ($val_role === 'admin' || $val_role === 'administrator') ? 'selected' : ''; ?>>Administrator</option>
                                            <option value="encoder" <?php echo ($val_role === 'encoder') ? 'selected' : ''; ?>>Encoder</option>
                                            <option value="viewer" <?php echo ($val_role === 'viewer') ? 'selected' : ''; ?>>Viewer</option>
                                        </select>
                                    <?php endif; ?>
                                </div>

                                <!-- Department -->
                                <div class="col-12 col-sm-6">
                                    <label for="modal_edit_department_id_<?php echo $u['id']; ?>" class="form-label small fw-semibold text-secondary">Department</label>
                                    <select class="form-select input-custom" id="modal_edit_department_id_<?php echo $u['id']; ?>" name="department_id">
                                        <option value="">Administrator</option>
                                        <?php if (!empty($departments)): ?>
                                            <?php foreach ($departments as $d): ?>
                                                <option value="<?php echo $d['id']; ?>"
                                                    <?php echo ($val_dept_id == $d['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($d['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <!-- Status -->
                                <div class="col-12 col-sm-6">
                                    <label for="modal_edit_is_active_<?php echo $u['id']; ?>" class="form-label small fw-semibold text-secondary">
                                        Status <span class="text-danger">*</span>
                                    </label>
                                    <?php if ($is_self): ?>
                                        <select class="form-select input-custom bg-light" id="modal_edit_is_active_disabled_<?php echo $u['id']; ?>" disabled style="cursor: not-allowed;">
                                            <option value="1" selected>Active</option>
                                        </select>
                                        <input type="hidden" name="is_active" value="1">
                                        <div class="form-text small text-secondary"><i class="fa-solid fa-circle-info me-1"></i>You cannot deactivate your active admin session.</div>
                                    <?php else: ?>
                                        <select class="form-select input-custom" id="modal_edit_is_active_<?php echo $u['id']; ?>" name="is_active" required>
                                            <option value="1" <?php echo ($val_is_active == 1) ? 'selected' : ''; ?>>Active</option>
                                            <option value="0" <?php echo ($val_is_active == 0) ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    <?php endif; ?>
                                </div>

                            </div><!-- /.row -->
                        </div><!-- /.modal-body -->

                        <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end">
                            <button type="button"
                                    data-bs-dismiss="modal"
                                    style="
                                        background: #fff;
                                        color: #374151;
                                        border: 1.5px solid #d1d5db;
                                        border-radius: 8px;
                                        padding: 0.5rem 1.4rem;
                                        font-size: 0.9rem;
                                        font-weight: 500;
                                        cursor: pointer;
                                        transition: background 0.15s, border-color 0.15s;
                                    "
                                    onmouseover="this.style.background='#f9fafb'"
                                    onmouseout="this.style.background='#fff'">
                                Cancel
                            </button>
                            <button type="submit"
                                    style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(16,185,129,0.3); transition: background 0.15s, box-shadow 0.15s;"
                            onmouseover="this.style.background='#059669';this.style.boxShadow='0 4px 12px rgba(16,185,129,0.4)'"
                            onmouseout="this.style.background='#10b981';this.style.boxShadow='0 2px 8px rgba(16,185,129,0.3)'">
                                Save Account
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- Auto-open logic for edit validation error -->
        <?php if ($is_open): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var el = document.getElementById('editUserModal-<?php echo $u['id']; ?>');
                if (el) { new bootstrap.Modal(el).show(); }
            });
        </script>
        <?php endif; ?>


        <!-- ===================== DELETE CONFIRMATION MODAL (User: @<?php echo htmlspecialchars($u['username']); ?>) ===================== -->
        <?php if (!$is_self): ?>
        <div class="modal fade" id="deleteUserModal-<?php echo $u['id']; ?>" tabindex="-1" aria-labelledby="deleteUserModalLabel-<?php echo $u['id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

                    <!-- Modal Header -->
                    <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                        <div class="d-flex align-items-center gap-2">
                            <div style="
                                
                                display: flex; align-items: center; justify-content: center;
                                flex-shrink: 0;
                            ">
                                <i class="fa-solid fa-trash-can" style="color: #000000ff; font-size: 1rem;"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0" id="deleteUserModalLabel-<?php echo $u['id']; ?>"
                                    style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em; margin-left: 0.75rem;">
                                    Delete User Account
                                </h5>
                            </div>
                        </div>
                        <button type="button"
                                class="btn-close btn-close-dark"
                                data-bs-dismiss="modal"
                                aria-label="Close"
                                style="opacity: 0.6;"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body px-4 py-3">
                        <div class="p-3 bg-light rounded-3 border border-light-subtle mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div style="
                                    width: 44px; height: 44px; border-radius: 50%;
                                    background: <?php echo $color; ?>;
                                    color: #fff;
                                    display: flex; align-items: center; justify-content: center;
                                    font-size: 0.85rem; font-weight: 700;
                                    flex-shrink: 0;
                                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                                ">
                                    <?php echo $initials; ?>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                        <?php echo htmlspecialchars($u['full_name']); ?>
                                    </div>
                                    <div class="text-muted small">
                                        @<?php echo htmlspecialchars($u['username']); ?> &middot; <?php echo ucfirst($u['role']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-secondary mb-0" style="font-size: 0.925rem; line-height: 1.5;">
                            Are you sure you want to permanently delete this user account? This action **cannot be undone** and will be recorded in the system audit trail.
                        </p>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end">
                        <button type="button"
                                data-bs-dismiss="modal"
                                style="
                                    background: #fff;
                                    color: #374151;
                                    border: 1px solid #d1d5db;
                                    border-radius: 8px;
                                    padding: 0.5rem 1.5rem;
                                    font-size: 0.9rem;
                                    font-weight: 500;
                                    cursor: pointer;
                                    transition: background 0.15s, border-color 0.15s;
                                    display: inline-flex;
                                    align-items: center;
                                    height: 38px;
                                "
                                onmouseover="this.style.background='#f9fafb'"
                                onmouseout="this.style.background='#fff'">
                            Cancel
                        </button>
                        <a href="<?php echo base_url('users/delete/' . $u['id']); ?>"
                           style="
                               background: #ef4444;
                               color: #fff;
                               border: 1px solid transparent;
                               border-radius: 8px;
                               padding: 0.5rem 1.5rem;
                               font-size: 0.9rem;
                               font-weight: 600;
                               text-decoration: none;
                               cursor: pointer;
                               box-shadow: 0 2px 8px rgba(239,68,68,0.3);
                               transition: background 0.15s, box-shadow 0.15s;
                               display: inline-flex;
                               align-items: center;
                               height: 38px;
                           "
                           onmouseover="this.style.background='#dc2626';this.style.boxShadow='0 4px 12px rgba(239,68,68,0.4)'"
                           onmouseout="this.style.background='#ef4444';this.style.boxShadow='0 2px 8px rgba(239,68,68,0.3)'">
                            Delete Account
                        </a>
                    </div>

                </div>
            </div>
        </div>
        <?php endif; ?>

    <?php endforeach; ?>
<?php endif; ?>

<!-- Auto-open modal on validation failure -->
<?php if (session()->getFlashdata('create_modal_open')): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('createUserModal');
        if (el) { new bootstrap.Modal(el).show(); }
    });
</script>
<?php endif; ?>

<!-- Reset forms on modal close + password visibility toggle -->
<script>
    document.getElementById('createUserModal')?.addEventListener('hidden.bs.modal', function () {
        this.querySelector('form').reset();
        var btn = document.getElementById('toggleCreatePassword');
        if (btn) btn.style.display = 'none';
    });

    document.querySelectorAll('[id^="editUserModal-"]').forEach(function(modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            this.querySelector('form').reset();
            this.querySelectorAll('.toggle-edit-password').forEach(function(btn) {
                btn.style.display = 'none';
            });
        });
    });

    var toggleCreateBtn = document.getElementById('toggleCreatePassword');
    var createInput = document.getElementById('modal_password');

    createInput?.addEventListener('input', function() {
        toggleCreateBtn.style.display = this.value ? '' : 'none';
    });

    toggleCreateBtn?.addEventListener('click', function() {
        var icon = this.querySelector('i');
        if (createInput.type === 'password') {
            createInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            createInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    document.querySelectorAll('.toggle-edit-password').forEach(function(btn) {
        var editInput = document.getElementById(btn.dataset.target);

        editInput?.addEventListener('input', function() {
            btn.style.display = this.value ? '' : 'none';
        });

        btn.addEventListener('click', function() {
            var icon = this.querySelector('i');
            if (editInput.type === 'password') {
                editInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                editInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
</script>

<!-- Hover style for Add New User button -->
<style>
    #btnAddNewUser:hover { background: #059669 !important; box-shadow: 0 4px 12px rgba(34,197,94,0.4) !important; }
</style>
