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
                    data-bs-target="#userModal"
                    onclick="setupUserModal('add')"
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
            
            <span>Users</span>
        </h5>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-custom table-hover w-100" id="usersTable">
            <thead>
                <tr>
                    <th style="display:none;">ID</th>
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
                        $is_self = ((int)$u['id'] === (int)$current_user_id);
                    ?>
                    <tr>
                        <!-- Hidden ID -->
                        <td style="display:none;"><?php echo $u['id']; ?></td>
                        <!-- Username -->
                        <td>
                            <span class="text-dark" style="font-size: 0.9rem;">
                                <?php echo htmlspecialchars($u['username']); ?>
                            </span>
                        </td>

                        <!-- Name Only -->
                        <td>
                            <span class="text-dark" style="font-size: 0.9rem;">
                                <?php echo htmlspecialchars($u['full_name']); ?>
                            </span>
                        </td>

                        <!-- Role -->
                        <td data-order="<?php
                            $r = strtolower($u['role']);
                            if ($r === 'dev') echo 0;
                            elseif ($r === 'admin' || $r === 'administrator') echo 1;
                            elseif ($r === 'encoder') echo 2;
                            else echo 3;
                        ?>">
                            <?php if ($r === 'dev'): ?>
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
                            <span class="text-dark" style="font-size: 0.9rem;">
                                <?php if (!empty($u['department_name'])): ?>
                                    <?php echo htmlspecialchars($u['department_name']); ?>
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
                            <span class="text-dark" style="font-size: 0.9rem;">
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
                                        data-bs-target="#userModal"
                                        data-id="<?php echo $u['id']; ?>"
                                        data-username="<?php echo htmlspecialchars($u['username']); ?>"
                                        data-first-name="<?php echo htmlspecialchars($u['first_name']); ?>"
                                        data-last-name="<?php echo htmlspecialchars($u['last_name']); ?>"
                                        data-email="<?php echo htmlspecialchars($u['email'] ?? ''); ?>"
                                        data-role="<?php echo htmlspecialchars(strtolower($u['role'])); ?>"
                                        data-department-id="<?php echo htmlspecialchars($u['department_id'] ?? ''); ?>"
                                        data-is-active="<?php echo htmlspecialchars($u['is_active']); ?>"
                                        data-is-self="<?php echo $is_self ? 'true' : 'false'; ?>"
                                        onclick="setupUserModal('edit', this.dataset)"
                                        title="Edit User">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <?php if ((int)$u['id'] !== (int)$current_user_id): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center rounded-2"
                                            style="width: 32px; height: 32px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteUserModal-<?php echo $u['id']; ?>"
                                            title="Deactivate User">
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


<!-- ===================== USER MODAL ===================== -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <!-- Modal Header -->
            <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                <div class="d-flex align-items-center">
                    <h5 class="modal-title fw-bold mb-0" id="userModalLabel"
                        style="color: #000000ff; font-size: 1.4rem; letter-spacing: -0.01em;">
                        Add New User
                    </h5>
                </div>
                <button type="button"
                        class="btn-close btn-close-dark"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        style="opacity: 0.6;"></button>
            </div>

            <!-- Form -->
            <form method="POST" action="<?php echo base_url('users/create'); ?>" id="userForm">
                <div class="modal-body px-4 py-4">

                    <!-- Validation Errors -->
                    <?php 
                    $create_errors = session()->getFlashdata('create_validation_errors');
                    $edit_errors = session()->getFlashdata('edit_validation_errors');
                    $has_errors = !empty($create_errors) || !empty($edit_errors);
                    $errors_to_show = $create_errors ?: $edit_errors;
                    if ($has_errors): 
                    ?>
                    <div class="alert alert-danger border-0 rounded-3 mb-4 py-3">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                            <div>
                                <span class="fw-bold d-block mb-1">Please correct the errors below:</span>
                                <div class="small"><?php echo $errors_to_show; ?></div>
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
                                   name="email">
                        </div>

                        <!-- Password -->
                        <div class="col-12 col-sm-6">
                            <label for="modal_password" class="form-label small fw-semibold text-secondary" id="label_password">
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
                                <option value="" disabled selected hidden>Select Role</option>
                                <option value="admin">Administrator</option>
                                <option value="encoder">Encoder</option>
                                <option value="viewer">Viewer</option>
                            </select>
                            <div class="form-text small text-secondary" id="roleSelfInfo" style="display: none;">
                                <i class="fa-solid fa-circle-info me-1"></i>You cannot demote your active admin session.
                            </div>
                        </div>

                        <!-- Department -->
                        <div class="col-12 col-sm-6">
                            <label for="modal_department_id" class="form-label small fw-semibold text-secondary">Department</label>
                            <select class="form-select input-custom" id="modal_department_id" name="department_id" required>
                                <option value="" disabled selected hidden>Select Department</option>
                                <option value="0">Administrator</option>
                                <?php if (!empty($departments)): ?>
                                    <?php foreach ($departments as $d): ?>
                                        <option value="<?php echo $d['id']; ?>">
                                            <?php echo htmlspecialchars($d['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-12 col-sm-6" id="statusContainer">
                            <label for="modal_is_active" class="form-label small fw-semibold text-secondary">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select class="form-select input-custom" id="modal_is_active" name="is_active" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div class="form-text small text-secondary" id="statusSelfInfo" style="display: none;">
                                <i class="fa-solid fa-circle-info me-1"></i>You cannot deactivate your active admin session.
                            </div>
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
                            id="btnSubmitUser"
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
    ?>
        <!-- ===================== DELETE CONFIRMATION MODAL (User: @<?php echo htmlspecialchars($u['username']); ?>) ===================== -->
        <?php if (!$is_self): ?>
        <div class="modal fade" id="deleteUserModal-<?php echo $u['id']; ?>" tabindex="-1" aria-labelledby="deleteUserModalLabel-<?php echo $u['id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

                    <!-- Modal Header -->
                    <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                        <h5 class="modal-title fw-bold mb-0" id="deleteUserModalLabel-<?php echo $u['id']; ?>"
                            style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                            Deactivate User Account
                        </h5>
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
                            Are you sure you want to deactivate this user account? The user will be marked as <strong>Inactive</strong> and will no longer be able to log in. This action will be recorded in the system audit trail.
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
                            Deactivate Account
                        </a>
                    </div>

                </div>
            </div>
        </div>
        <?php endif; ?>

    <?php endforeach; ?>
<?php endif; ?>

<!-- Reset forms on modal close + password visibility toggle -->
<script>
    function setupUserModal(mode, data = {}) {
        const modal = document.getElementById('userModal');
        if (!modal) return;
        
        const form = modal.querySelector('form');
        const labelPassword = document.getElementById('label_password');
        const passwordInput = document.getElementById('modal_password');
        const statusContainer = document.getElementById('statusContainer');
        const modalTitle = document.getElementById('userModalLabel');
        const submitBtn = document.getElementById('btnSubmitUser');
        
        const roleSelect = document.getElementById('modal_role');
        const statusSelect = document.getElementById('modal_is_active');
        const roleSelfInfo = document.getElementById('roleSelfInfo');
        const statusSelfInfo = document.getElementById('statusSelfInfo');
        
        // Remove existing hidden self-inputs
        form.querySelectorAll('.self-hidden-input').forEach(el => el.remove());
        
        // Reset password toggle visibility
        var toggleBtn = document.getElementById('toggleCreatePassword');
        if (toggleBtn) toggleBtn.style.display = 'none';
        
        if (mode === 'add') {
            modalTitle.textContent = 'Add New User';
            form.action = '<?php echo base_url('users/create'); ?>';
            submitBtn.textContent = 'Add User';
            
            labelPassword.innerHTML = 'Password <span class="text-danger">*</span>';
            passwordInput.required = true;
            passwordInput.placeholder = '';
            
            statusContainer.style.display = 'none';
            statusSelect.required = false;
            
            // Populate fields
            document.getElementById('modal_first_name').value = data.firstName || '';
            document.getElementById('modal_last_name').value = data.lastName || '';
            document.getElementById('modal_username').value = data.username || '';
            document.getElementById('modal_email').value = data.email || '';
            passwordInput.value = '';
            roleSelect.value = data.role || '';
            document.getElementById('modal_department_id').value = data.departmentId || '';
            statusSelect.value = '1';
            
            roleSelect.disabled = false;
            statusSelect.disabled = false;
            if (roleSelfInfo) roleSelfInfo.style.display = 'none';
            if (statusSelfInfo) statusSelfInfo.style.display = 'none';
            
        } else if (mode === 'edit') {
            modalTitle.textContent = 'Edit User Account';
            form.action = '<?php echo base_url('users/edit'); ?>/' + data.id;
            submitBtn.textContent = 'Save Account';
            
            labelPassword.textContent = 'New Password';
            passwordInput.required = false;
            passwordInput.placeholder = 'Leave blank to keep current';
            
            statusContainer.style.display = 'block';
            statusSelect.required = true;
            
            // Populate fields
            document.getElementById('modal_first_name').value = data.firstName || '';
            document.getElementById('modal_last_name').value = data.lastName || '';
            document.getElementById('modal_username').value = data.username || '';
            document.getElementById('modal_email').value = data.email || '';
            passwordInput.value = '';
            document.getElementById('modal_department_id').value = data.departmentId || '';
            
            const isSelf = data.isSelf === 'true' || data.isSelf === true;
            if (isSelf) {
                roleSelect.value = data.role || '';
                roleSelect.disabled = true;
                if (roleSelfInfo) roleSelfInfo.style.display = 'block';
                
                statusSelect.value = '1';
                statusSelect.disabled = true;
                if (statusSelfInfo) statusSelfInfo.style.display = 'block';
                
                // Add hidden inputs since disabled fields are not submitted
                const hiddenRole = document.createElement('input');
                hiddenRole.type = 'hidden';
                hiddenRole.name = 'role';
                hiddenRole.value = data.role || '';
                hiddenRole.className = 'self-hidden-input';
                form.appendChild(hiddenRole);
                
                const hiddenActive = document.createElement('input');
                hiddenActive.type = 'hidden';
                hiddenActive.name = 'is_active';
                hiddenActive.value = '1';
                hiddenActive.className = 'self-hidden-input';
                form.appendChild(hiddenActive);
            } else {
                roleSelect.value = data.role || '';
                roleSelect.disabled = false;
                if (roleSelfInfo) roleSelfInfo.style.display = 'none';
                
                statusSelect.value = data.isActive !== undefined ? data.isActive : '1';
                statusSelect.disabled = false;
                if (statusSelfInfo) statusSelfInfo.style.display = 'none';
            }
        }
    }

    document.getElementById('userModal')?.addEventListener('hidden.bs.modal', function () {
        const form = this.querySelector('form');
        form.reset();
        form.querySelectorAll('.self-hidden-input').forEach(el => el.remove());
        document.getElementById('modal_role').disabled = false;
        document.getElementById('modal_is_active').disabled = false;
        
        var toggleBtn = document.getElementById('toggleCreatePassword');
        if (toggleBtn) toggleBtn.style.display = 'none';
        document.getElementById('modal_password').type = 'password';
        
        var icon = toggleBtn?.querySelector('i');
        if (icon) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    // Password visibility toggle
    var togglePasswordBtn = document.getElementById('toggleCreatePassword');
    var passwordInput = document.getElementById('modal_password');

    passwordInput?.addEventListener('input', function() {
        if (togglePasswordBtn) {
            togglePasswordBtn.style.display = this.value ? '' : 'none';
        }
    });

    togglePasswordBtn?.addEventListener('click', function() {
        var icon = this.querySelector('i');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
</script>

<!-- Auto-open modal on validation failure -->
<?php if (session()->getFlashdata('create_modal_open')): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const data = {
            firstName: <?php echo json_encode(old('first_name') ?? ''); ?>,
            lastName: <?php echo json_encode(old('last_name') ?? ''); ?>,
            username: <?php echo json_encode(old('username') ?? ''); ?>,
            email: <?php echo json_encode(old('email') ?? ''); ?>,
            role: <?php echo json_encode(old('role') ?? ''); ?>,
            departmentId: <?php echo json_encode(old('department_id') ?? ''); ?>,
        };
        setupUserModal('add', data);
        var el = document.getElementById('userModal');
        if (el) { new bootstrap.Modal(el).show(); }
    });
</script>
<?php endif; ?>

<?php if ($open_id = session()->getFlashdata('edit_modal_open_id')): 
    // Find user in $users
    $failed_user = null;
    foreach ($users as $u) {
        if ((int)$u['id'] === (int)$open_id) {
            $failed_user = $u;
            break;
        }
    }
    if ($failed_user):
        $is_self = ((int)$failed_user['id'] === (int)session()->get('user_id'));
?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const data = {
            id: <?php echo json_encode($failed_user['id']); ?>,
            firstName: <?php echo json_encode(old('first_name', $failed_user['first_name'])); ?>,
            lastName: <?php echo json_encode(old('last_name', $failed_user['last_name'])); ?>,
            username: <?php echo json_encode(old('username', $failed_user['username'])); ?>,
            email: <?php echo json_encode(old('email', $failed_user['email'])); ?>,
            role: <?php echo json_encode(old('role', strtolower($failed_user['role']))); ?>,
            departmentId: <?php echo json_encode(old('department_id', $failed_user['department_id'])); ?>,
            isActive: <?php echo json_encode(old('is_active', $failed_user['is_active'])); ?>,
            isSelf: <?php echo $is_self ? 'true' : 'false'; ?>
        };
        setupUserModal('edit', data);
        var el = document.getElementById('userModal');
        if (el) { new bootstrap.Modal(el).show(); }
    });
</script>
<?php endif; ?>
<?php endif; ?>

<!-- Hover style for Add New User button -->
<style>
    #btnAddNewUser:hover { background: #059669 !important; box-shadow: 0 4px 12px rgba(34,197,94,0.4) !important; }
</style>
