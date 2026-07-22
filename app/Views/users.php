<!-- Page Title Section -->
<div class="page-breadcrumb">
    <a href="<?php echo base_url('dashboard'); ?>">Dashboard</a>
    <span class="separator">/</span>
    <span class="current">User Management</span>
</div>

<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">User Management</h1>
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

<!-- Users Search Bar -->
<form method="GET" action="<?php echo base_url('users'); ?>" id="usersSearchForm">
    <div class="db-search-bar">
        <div class="db-search-field db-search-field--keyword">
            <input
                type="text"
                id="users_search_keyword"
                name="search"
                class="db-search-input"
                placeholder=" "
                value="<?php echo htmlspecialchars($search ?? ''); ?>"
                autocomplete="off"
            >
            <label for="users_search_keyword">Enter Full Name / Username</label>
        </div>
        <div class="db-search-field db-search-field--dropdown">
            <select id="users_search_role" name="role_filter" class="db-search-select">
                <option value="">- Select Account Level -</option>
                <option value="admin"   <?php echo (($role_filter ?? '') === 'admin')   ? 'selected' : ''; ?>>Admin</option>
                <option value="encoder" <?php echo (($role_filter ?? '') === 'encoder') ? 'selected' : ''; ?>>Encoder</option>
                <option value="viewer"  <?php echo (($role_filter ?? '') === 'viewer')  ? 'selected' : ''; ?>>Viewer</option>
            </select>
            <label for="users_search_role">Account Level</label>
        </div>
        <div class="db-search-field db-search-field--dropdown">
            <select id="users_search_dept" name="dept_filter" class="db-search-select">
                <option value="">- Select Department -</option>
                <option value="0" <?php echo (($dept_filter ?? '') === '0') ? 'selected' : ''; ?>>Administrator</option>
                <?php if (!empty($departments)): ?>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?php echo $d['id']; ?>" <?php echo (($dept_filter ?? '') === (string)$d['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($d['name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <label for="users_search_dept">Department</label>
        </div>
        <div class="db-search-field db-search-field--dropdown">
            <select id="users_search_status" name="status_filter" class="db-search-select">
                <option value="">- Select Status -</option>
                <option value="1" <?php echo (($status_filter ?? '') === '1') ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo (($status_filter ?? '') === '0') ? 'selected' : ''; ?>>Inactive</option>
            </select>
            <label for="users_search_status">Status</label>
        </div>
        <div class="db-search-actions">
            <button type="submit" class="btn-db-search" id="btnUsersSearch">
                 Search
            </button>
            <a href="<?php echo base_url('users'); ?>" class="btn-db-clear" id="btnUsersClear">
                Clear
            </a>
            <div class="db-search-separator"></div>
            <button type="button"
                    class="btn btn-db-search d-inline-flex align-items-center gap-2"
                    id="btnAddNewUser"
                    data-bs-toggle="modal"
                    data-bs-target="#userModal"
                    onclick="setupUserModal('add')">
                <span>Add User</span>
            </button>
        </div>
    </div>
</form>

<!-- Users Table -->

    <div class="table-responsive-custom">
        <table class="table table-custom table-hover w-100" id="usersTable">
            <thead>
                <tr>
                    <th style="display:none;">ID</th>
                    <th style="width: 40%">Full Name</th>
                    <th style="width: 20%">Username</th>
                    <th style="width: 20%">Account Level</th>
                    <th style="width: 20%">Department</th>
                    <th style="width: 10%">Status</th>
                    <th style="width: 10%">Actions</th>
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
                        $current_user_role = session()->get('role');
                        $is_protected = (strtolower($current_user_role) === 'admin' && !$is_self && in_array(strtolower($u['role']), ['dev', 'admin']));
                        $manage_mode = $is_protected ? 'view' : 'edit';
                    ?>
                    <tr>
                        <!-- Hidden ID -->
                        <td style="display:none;"><?php echo $u['id']; ?></td>
                        <!-- Name (Last, First) -->
                        <td>
                            <span class="text-dark" style="font-size: 0.9rem;">
                                <?php echo htmlspecialchars($u['last_name'] . ', ' . $u['first_name']); ?>
                            </span>
                        </td>

                        <!-- Username -->
                        <td class="text-center">
                            <span class="text-dark" style="font-size: 0.9rem;">
                                <?php echo htmlspecialchars($u['username']); ?>
                            </span>
                        </td>

                        <!-- Role -->
                        <td class="text-center" data-order="<?php
                            $r = strtolower($u['role']);
                            if ($r === 'dev') echo 0;
                            elseif ($r === 'admin' || $r === 'administrator') echo 1;
                            elseif ($r === 'encoder') echo 2;
                            else echo 3;
                        ?>">
                            <span class="text-dark" style="font-size: 0.9rem;">
                                <?php echo htmlspecialchars(ucfirst($r)); ?>
                            </span>
                        </td>

                        <!-- Department -->
                        <td class="text-center">
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

                        <!-- Status -->
                        <td class="text-center">
                            <?php if ($u['is_active']): ?>
                                <span class="badge badge-action rounded-pill bg-success-subtle text-dark border border-success-subtle text-uppercase">Active</span>
                            <?php else: ?>
                                <span class="badge badge-action rounded-pill bg-secondary-subtle text-dark border border-secondary-subtle text-uppercase">Inactive</span>
                            <?php endif; ?>
                        </td>

                        <!-- Actions -->
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown" style="padding: 4px 12px; font-size: 0.75rem; font-weight: 600;">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" style="font-size: 0.8rem;">
                                    <?php if ((int)$u['is_active'] === 1): ?>
                                    <li><a class="dropdown-item" href="#"
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
                                            onclick="setupUserModal('<?php echo $manage_mode; ?>', this.dataset)"
                                            title="Manage User">Manage</a></li>
                                    <?php endif; ?>
                                    <?php if (!$is_protected && (int)$u['id'] !== (int)$current_user_id): ?>
                                        <?php if ((int)$u['is_active'] === 1): ?>
                                                <li><a class="dropdown-item" href="#"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteUserModal-<?php echo $u['id']; ?>"
                                                        title="Deactivate User">Deactivate</a></li>
                                                        <?php else: ?>
                                                        <li><a class="dropdown-item" href="#"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#activateUserModal-<?php echo $u['id']; ?>"
                                                                title="Activate User">Reactivate</a></li>
                                        <?php endif; ?>
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


<!-- ===================== USER MODAL ===================== -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
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
                    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-4 py-3">
                        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close" style="font-size: 0.75rem; top: 0.5rem; right: 0.5rem;"></button>
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
                        <div class="col-lg-6 col-12">
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
                        <div class="col-lg-6 col-12">
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
                        <div class="col-lg-6 col-12">
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
                        <div class="col-lg-6 col-12">
                            <label for="modal_email" class="form-label small fw-semibold text-secondary">
                                Email
                            </label>
                            <input type="email"
                                   class="form-control input-custom"
                                   id="modal_email"
                                   name="email">
                        </div>

                        <!-- Password -->
                        <div class="col-lg-6 col-12">
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
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Account Level -->
                        <div class="w-50"></div>
                        <div class="col-lg-6 col-12">
                            <label for="modal_role" class="form-label small fw-semibold text-secondary">
                                Account Level <span class="text-danger">*</span>
                            </label>
                            <select class="form-select input-custom" id="modal_role" name="role" required>
                                    <option value="" disabled selected hidden>Select Account Level</option>
                                    <option value="dev" hidden>Developer</option>
                                    <option value="admin">Administrator</option>
                                    <option value="encoder">Encoder</option>
                                    <option value="viewer">Viewer</option>
                                </select>
                            <div class="form-text small text-secondary" id="roleSelfInfo" style="display: none;">
                                <i class="fa-solid fa-circle-info me-1"></i>You cannot demote your active admin session.
                            </div>
                        </div>

                        <!-- Department -->
                        <div class="col-lg-6 col-12">
                            <label for="modal_department_id" class="form-label small fw-semibold text-secondary">Department</label>
                            <select class="form-select input-custom" id="modal_department_id" name="department_id" required>
                                <option value="" disabled selected hidden>Select Department</option>
                                <option value="0" id="optAdminDept">Administrator</option>
                                <?php if (!empty($departments)): ?>
                                    <?php foreach ($departments as $d): ?>
                                        <option value="<?php echo $d['id']; ?>">
                                            <?php echo htmlspecialchars($d['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Hidden is_active field (always 1 for new users) -->
                        <input type="hidden" name="is_active" value="1" id="modal_is_active">

                    </div><!-- /.row -->
                </div><!-- /.modal-body -->

                <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end">
                    <button type="button" id="userModalCancelBtn"
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
                        Close
                    </button>
                    <button type="submit"
                            id="btnSubmitUser"
                            class="btn btn-success-custom">
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
        <!-- ===================== STATUS TOGGLE MODAL (User: @<?php echo htmlspecialchars($u['username']); ?>) ===================== -->
        <?php if (!$is_self): ?>
            <?php if ((int)$u['is_active'] === 1): ?>
            <div class="modal fade" id="deleteUserModal-<?php echo $u['id']; ?>" tabindex="-1" aria-labelledby="deleteUserModalLabel-<?php echo $u['id']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                        <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                            <h5 class="modal-title fw-bold mb-0" id="deleteUserModalLabel-<?php echo $u['id']; ?>"
                                style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                                Deactivate User Account
                            </h5>
                            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                        </div>
                        <div class="modal-body px-4 py-3">
                            <div class="p-3 bg-light rounded-3 border border-light-subtle mb-3">
                                <div class="d-flex align-items-center gap-3">
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
                                Are you sure you want to deactivate this user account?
                            </p>
                        </div>
                        <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end">
                            <button type="button" data-bs-dismiss="modal"
                                    style="background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s, border-color 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                    onmouseover="this.style.background='#f9fafb'"
                                    onmouseout="this.style.background='#fff'">Cancel</button>
                            <a href="<?php echo base_url('users/delete/' . $u['id']); ?>"
                               style="background: #ef4444; color: #fff; border: 1px solid transparent; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; height: 38px;"
                               onmouseover="this.style.background='#dc2626'"
                               onmouseout="this.style.background='#ef4444'">Deactivate Account</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="modal fade" id="activateUserModal-<?php echo $u['id']; ?>" tabindex="-1" aria-labelledby="activateUserModalLabel-<?php echo $u['id']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                        <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                            <h5 class="modal-title fw-bold mb-0" id="activateUserModalLabel-<?php echo $u['id']; ?>"
                                style="color: #1e293b; font-size: 1.25rem; letter-spacing: -0.01em;">
                                Reactivate User Account
                            </h5>
                            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                        </div>
                        <div class="modal-body px-4 py-3">
                            <div class="p-3 bg-light rounded-3 border border-light-subtle mb-3">
                                <div class="d-flex align-items-center gap-3">
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
                                Are you sure you want to reactivate this user account?
                            </p>
                        </div>
                        <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end">
                            <button type="button" data-bs-dismiss="modal"
                                    style="background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s, border-color 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                    onmouseover="this.style.background='#f9fafb'"
                                    onmouseout="this.style.background='#fff'">Cancel</button>
                            <a href="<?php echo base_url('users/activate/' . $u['id']); ?>"
                               class="btn btn-success-custom">Reactivate Account</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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
        const modalTitle = document.getElementById('userModalLabel');
        const submitBtn = document.getElementById('btnSubmitUser');
        
        const roleSelect = document.getElementById('modal_role');
        const roleSelfInfo = document.getElementById('roleSelfInfo');
        
        // Remove existing hidden self-inputs
        form.querySelectorAll('.self-hidden-input').forEach(el => el.remove());
        
        // Reset password toggle visibility
        var toggleBtn = document.getElementById('toggleCreatePassword');
        if (toggleBtn) toggleBtn.style.display = 'none';
        
        if (mode === 'add') {
            modalTitle.textContent = 'Add New User';
            form.action = '<?php echo base_url('users/create'); ?>';
            submitBtn.textContent = 'Add User';
            submitBtn.style.display = '';
            
            labelPassword.innerHTML = 'Password <span class="text-danger">*</span>';
            passwordInput.required = true;
            passwordInput.placeholder = '';
            passwordInput.disabled = false;
            
            // Populate fields
            document.getElementById('modal_first_name').value = data.firstName || '';
            document.getElementById('modal_first_name').disabled = false;
            document.getElementById('modal_last_name').value = data.lastName || '';
            document.getElementById('modal_last_name').disabled = false;
            document.getElementById('modal_username').value = data.username || '';
            document.getElementById('modal_username').disabled = false;
            document.getElementById('modal_email').value = data.email || '';
            document.getElementById('modal_email').disabled = false;
            passwordInput.value = '';
            roleSelect.value = data.role || '';
            document.getElementById('modal_department_id').value = data.departmentId ?? '';
            document.getElementById('modal_department_id').disabled = false;
            
            roleSelect.disabled = false;
            if (roleSelfInfo) roleSelfInfo.style.display = 'none';
            document.getElementById('userModalCancelBtn').textContent = 'Close';
            
        } else if (mode === 'edit') {
            modalTitle.textContent = 'Manage User Account';
            form.action = '<?php echo base_url('users/edit'); ?>/' + data.id;
            submitBtn.textContent = 'Update User';
            submitBtn.style.display = '';
            
            labelPassword.textContent = 'Password';
            passwordInput.required = false;
            passwordInput.placeholder = 'Leave blank to keep current password';
            passwordInput.disabled = false;
            
            // Populate fields
            document.getElementById('modal_first_name').value = data.firstName || '';
            document.getElementById('modal_first_name').disabled = false;
            document.getElementById('modal_last_name').value = data.lastName || '';
            document.getElementById('modal_last_name').disabled = false;
            document.getElementById('modal_username').value = data.username || '';
            document.getElementById('modal_username').disabled = false;
            document.getElementById('modal_email').value = data.email || '';
            document.getElementById('modal_email').disabled = false;
            passwordInput.value = '';
            document.getElementById('modal_department_id').value = data.departmentId ?? '';
            document.getElementById('modal_department_id').disabled = false;
            
            const isSelf = data.isSelf === 'true' || data.isSelf === true;
            if (isSelf) {
                roleSelect.value = data.role || '';
                roleSelect.disabled = true;
                if (roleSelfInfo) roleSelfInfo.style.display = 'block';
                
                // Add hidden inputs since disabled fields are not submitted
                const hiddenRole = document.createElement('input');
                hiddenRole.type = 'hidden';
                hiddenRole.name = 'role';
                hiddenRole.value = data.role || '';
                hiddenRole.className = 'self-hidden-input';
                form.appendChild(hiddenRole);
            } else {
                roleSelect.value = data.role || '';
                roleSelect.disabled = false;
                if (roleSelfInfo) roleSelfInfo.style.display = 'none';
            }
            document.getElementById('userModalCancelBtn').textContent = 'Close';
        } else if (mode === 'view') {
            modalTitle.textContent = 'Manage User Account';
            form.action = '#';
            submitBtn.style.display = 'none';
            
            labelPassword.textContent = 'Password';
            passwordInput.required = false;
            passwordInput.placeholder = '••••••••';
            passwordInput.disabled = true;
            
            document.getElementById('modal_first_name').value = data.firstName || '';
            document.getElementById('modal_last_name').value = data.lastName || '';
            document.getElementById('modal_username').value = data.username || '';
            document.getElementById('modal_email').value = data.email || 'N/A';
            passwordInput.value = '';
            document.getElementById('modal_department_id').value = data.departmentId ?? '';
            roleSelect.value = data.role || '';
            roleSelect.disabled = true;
            
            document.getElementById('modal_first_name').disabled = true;
            document.getElementById('modal_last_name').disabled = true;
            document.getElementById('modal_username').disabled = true;
            document.getElementById('modal_email').disabled = true;
            document.getElementById('modal_department_id').disabled = true;
            
            if (roleSelfInfo) roleSelfInfo.style.display = 'none';
            document.getElementById('userModalCancelBtn').textContent = 'Close';
        }

        toggleAdminDept();
    }

    document.getElementById('userModal')?.addEventListener('hidden.bs.modal', function () {
        const form = this.querySelector('form');
        form.reset();
        form.querySelectorAll('.self-hidden-input').forEach(el => el.remove());
        document.getElementById('modal_role').disabled = false;
        document.getElementById('modal_password').disabled = false;
        document.getElementById('modal_first_name').disabled = false;
        document.getElementById('modal_last_name').disabled = false;
        document.getElementById('modal_username').disabled = false;
        document.getElementById('modal_email').disabled = false;
        document.getElementById('modal_department_id').disabled = false;
        document.getElementById('btnSubmitUser').style.display = '';
        
        var toggleBtn = document.getElementById('toggleCreatePassword');
        if (toggleBtn) toggleBtn.style.display = 'none';
        document.getElementById('modal_password').type = 'password';
        
        var icon = toggleBtn?.querySelector('i');
        if (icon) {
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }

        var errorAlert = this.querySelector('.modal-body .alert.alert-danger');
        if (errorAlert) errorAlert.remove();
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
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
    });

    function toggleAdminDept() {
        var role = document.getElementById('modal_role').value;
        var opt = document.getElementById('optAdminDept');
        var departmentSelect = document.getElementById('modal_department_id');
        var administratorRole = role === 'admin' || role === 'dev';
        if (opt) {
            opt.style.display = administratorRole ? '' : 'none';
        }
        // Do not retain the hidden Administrator value after changing to an
        // operational account level. The user must choose a real department.
        if (!administratorRole && departmentSelect && departmentSelect.value === '0') {
            departmentSelect.value = '';
        }
    }

    document.getElementById('modal_role')?.addEventListener('change', toggleAdminDept);
    document.addEventListener('DOMContentLoaded', toggleAdminDept);
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

<style>
    #modal_is_active:checked { background-color: #198754; border-color: #198754; }
    .form-switch .form-check-input:focus { box-shadow: none; outline: none; border-color: #198754; }
    .form-switch .form-check-input:focus-visible { outline: none; }
</style>
