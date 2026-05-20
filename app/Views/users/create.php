<!-- Page Title Section -->
<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">Add User Account</h1>
            <p class="text-secondary mb-0">Create new administrative or staff account details</p>
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

<!-- Create User Form Card -->
<div class="row fade-in-up" style="animation-delay: 0.1s;">
    <div class="col-12 col-lg-8 col-xl-6">
        <div class="standard-card">
            <div class="card-header-styled mb-4">
                <h5 class="card-title-styled">
                    <i class="fa-solid fa-user-plus text-primary"></i>
                    <span>Account Specifications</span>
                </h5>
            </div>

            <form method="POST" action="<?php echo base_url('users/create'); ?>" class="row g-3">
                <!-- 1. Username -->
                <div class="col-12 col-sm-6">
                    <label for="username" class="form-label small fw-semibold text-secondary">Username <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control input-custom" 
                           id="username" 
                           name="username" 
                           placeholder="e.g. staff_juan"
                           value="<?php echo set_value('username'); ?>"
                           required>
                    <div class="form-text small text-muted">Use alphanumeric characters, underscores, and dashes only.</div>
                </div>

                <!-- 2. Full Name -->
                <div class="col-12 col-sm-6">
                    <label for="full_name" class="form-label small fw-semibold text-secondary">Full Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control input-custom" 
                           id="full_name" 
                           name="full_name" 
                           placeholder="e.g. Juan Dela Cruz"
                           value="<?php echo set_value('full_name'); ?>"
                           required>
                </div>

                <!-- 3. Password -->
                <div class="col-12 col-sm-6">
                    <label for="password" class="form-label small fw-semibold text-secondary">Password <span class="text-danger">*</span></label>
                    <input type="password" 
                           class="form-control input-custom" 
                           id="password" 
                           name="password" 
                           placeholder="Enter secure password"
                           required>
                    <div class="form-text small text-muted">Must be at least 4 characters long.</div>
                </div>

                <!-- 4. Role -->
                <div class="col-12 col-sm-6">
                    <label for="role" class="form-label small fw-semibold text-secondary">Role <span class="text-danger">*</span></label>
                    <select class="form-select input-custom" id="role" name="role" required>
                        <option value="">-- Select Role --</option>
                        <option value="admin" <?php echo set_select('role', 'admin'); ?>>Administrator</option>
                        <option value="staff" <?php echo set_select('role', 'staff'); ?>>Staff</option>
                    </select>
                </div>

                <!-- 5. Department -->
                <div class="col-12 col-sm-6">
                    <label for="department_id" class="form-label small fw-semibold text-secondary">Department</label>
                    <select class="form-select input-custom" id="department_id" name="department_id">
                        <option value="">None / Administration (Admin)</option>
                        <?php if (!empty($departments)): ?>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?php echo $d['id']; ?>" <?php echo set_select('department_id', $d['id']); ?>>
                                    <?php echo htmlspecialchars($d['name']); ?> (<?php echo htmlspecialchars($d['code']); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="form-text small text-muted">Assign department access for medical staff profiles.</div>
                </div>

                <!-- 6. Status -->
                <div class="col-12 col-sm-6">
                    <label for="is_active" class="form-label small fw-semibold text-secondary">Status <span class="text-danger">*</span></label>
                    <select class="form-select input-custom" id="is_active" name="is_active" required>
                        <option value="1" <?php echo set_select('is_active', '1', TRUE); ?>>Active</option>
                        <option value="0" <?php echo set_select('is_active', '0'); ?>>Inactive</option>
                    </select>
                </div>

                <!-- Submission Actions -->
                <div class="col-12 d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-save"></i>
                        <span>Save Account</span>
                    </button>
                    <a href="<?php echo base_url('users'); ?>" class="btn btn-outline-secondary px-4 py-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
