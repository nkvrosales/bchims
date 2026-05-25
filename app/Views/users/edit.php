<?php 
$current_admin_id = session()->get('user_id');
$is_self = ((int)$user['id'] === (int)$current_admin_id);
?>

<!-- Page Title Section -->
<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">Edit User Account</h1>
        </div>
        <div>
            <a href="<?php echo base_url('users'); ?>" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Users</span>
            </a>
        </div>
    </div>
</div>

<!-- Validation Error alerts if any -->
<?php if (validation_errors() || isset($error)): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-3 fade show" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="fa-solid fa-triangle-exclamation fs-5 mt-1"></i>
            <div>
                <span class="fw-bold d-block mb-1">Please correct the errors below:</span>
                <div class="small">
                    <?php echo validation_errors('<li>', '</li>'); ?>
                    <?php if (isset($error)) echo "<li>{$error}</li>"; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Self account guard alert -->
<?php if ($is_self): ?>
    <div class="alert alert-warning border-0 shadow-sm mb-4 rounded-3 fade show" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-user-shield fs-5"></i>
            <div>
                <span class="fw-bold">Self Account Protection Enabled:</span>
                <span class="small">You are editing your own administrator profile. For system security, your Role (Administrator) and Status (Active) cannot be altered.</span>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Edit User Form Card -->
<div class="row fade-in-up" style="animation-delay: 0.1s;">
    <div class="col-12 col-lg-8 col-xl-6">
        <div class="standard-card">
            <div class="card-header-styled mb-4">
                <h5 class="card-title-styled">
                    <i class="fa-solid fa-user-pen text-primary"></i>
                    <span>Profile Information</span>
                </h5>
            </div>

            <form method="POST" action="<?php echo base_url('users/edit/' . $user['id']); ?>" class="row g-3">
                <!-- 3. First Name -->
                <div class="col-12 col-sm-6">
                    <label for="first_name" class="form-label small fw-semibold text-secondary">First Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control input-custom" 
                           id="first_name" 
                           name="first_name" 
                           placeholder="e.g. Juan"
                           value="<?php echo set_value('first_name', $user['first_name']); ?>"
                           required>
                </div>

        

                <!-- 2. Last Name -->
                <div class="col-12 col-sm-6">
                    <label for="last_name" class="form-label small fw-semibold text-secondary">Last Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control input-custom" 
                           id="last_name" 
                           name="last_name" 
                           placeholder="e.g. Dela Cruz"
                           value="<?php echo set_value('last_name', $user['last_name']); ?>"
                           required>
                </div>

                <!-- 4. Username -->
                <div class="col-12 col-sm-6">
                    <label for="username" class="form-label small fw-semibold text-secondary">Username <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control input-custom" 
                           id="username" 
                           name="username" 
                           placeholder="e.g. staff_juan"
                           value="<?php echo set_value('username', $user['username']); ?>"
                           required>
                </div>

                <!-- 5. Password -->
                <div class="col-12 col-sm-6">
                    <label for="password" class="form-label small fw-semibold text-secondary">New Password</label>
                    <input type="password" 
                           class="form-control input-custom" 
                           id="password" 
                           name="password" 
                           placeholder="Leave blank to keep current">
                    
                </div>

                <!-- 6. Role -->
                <div class="col-12 col-sm-6">
                    <label for="role" class="form-label small fw-semibold text-secondary">Role <span class="text-danger">*</span></label>
                    <?php if ($is_self): ?>
                        <select class="form-select input-custom bg-light" id="role_disabled" disabled>
                            <option value="admin" selected>Administrator</option>
                        </select>
                        <input type="hidden" name="role" value="admin">
                    <?php else: ?>
                        <select class="form-select input-custom" id="role" name="role" required>
                            <option value="admin" <?php echo (set_value('role', $user['role']) === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                            <option value="staff" <?php echo (set_value('role', $user['role']) === 'staff') ? 'selected' : ''; ?>>Staff</option>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- 7. Department -->
                <div class="col-12 col-sm-6">
                    <label for="department_id" class="form-label small fw-semibold text-secondary">Department</label>
                    <select class="form-select input-custom" id="department_id" name="department_id">
                        <option value="">None / Administration (Admin)</option>
                        <?php if (!empty($departments)): ?>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?php echo $d['id']; ?>" <?php echo (set_value('department_id', $user['department_id']) == $d['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($d['name']); ?> (<?php echo htmlspecialchars($d['code']); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- 8. Status -->
                <div class="col-12 col-sm-6">
                    <label for="is_active" class="form-label small fw-semibold text-secondary">Status <span class="text-danger">*</span></label>
                    <?php if ($is_self): ?>
                        <select class="form-select input-custom bg-light" id="is_active_disabled" disabled>
                            <option value="1" selected>Active</option>
                        </select>
                        <input type="hidden" name="is_active" value="1">
                    <?php else: ?>
                        <select class="form-select input-custom" id="is_active" name="is_active" required>
                            <option value="1" <?php echo (set_value('is_active', $user['is_active']) == 1) ? 'selected' : ''; ?>>Active</option>
                            <option value="0" <?php echo (set_value('is_active', $user['is_active']) == 0) ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- Submission Actions -->
                <div class="col-12 d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-save"></i>
                        <span>Update Account</span>
                    </button>
                    <a href="<?php echo base_url('users'); ?>" class="btn btn-outline-secondary px-4 py-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
