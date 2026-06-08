<!-- Page Title Section -->
<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">Profile Settings</h1>
        </div>
        <div>
            <a href="<?php echo base_url('dashboard'); ?>" class="btn btn-outline-secondary d-flex align-items-center gap-2 hover-lift">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>
    </div>
</div>

<!-- Validation Error Alerts if any -->
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

<!-- Success Alerts if any -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 rounded-3" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <span><?php echo session()->getFlashdata('success'); ?></span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Profile Settings Form Card -->
<div class="row fade-in-up" style="animation-delay: 0.1s;">
    <div class="col-12 col-lg-8 col-xl-6">
        <div class="standard-card">
            <div class="card-header-styled mb-4">
                <h5 class="card-title-styled">
                    <span>Profile</span>
                </h5>
            </div>

            <form method="POST" action="<?php echo base_url('dashboard/profile'); ?>" class="row g-3">
                
                <!-- 1. First Name -->
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

               
                <!-- 3. Username -->
                <div class="col-12 col-sm-6">
                    <label for="username" class="form-label small fw-semibold text-secondary">Username <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control input-custom" 
                           id="username" 
                           name="username"
                           placeholder="e.g. juan.delacruz"
                           value="<?php echo set_value('username', $user['username']); ?>"
                           required>
                </div>


                <!-- 4. New Password -->
                <div class="col-12 col-sm-6">
                    <label for="password" class="form-label small fw-semibold text-secondary">New Password</label>
                    <input type="password" 
                           class="form-control input-custom" 
                           id="password" 
                           name="password" 
                           placeholder="Leave blank to keep current">
                </div>

                <?php $isAdmin = is_admin_role($user['role']); ?>
                <!-- 5. Role -->
                <div class="col-12 col-sm-6">
                    <label for="role_display" class="form-label small fw-semibold text-secondary">Role <span class="text-danger">*</span></label>
                    <select class="form-select input-custom bg-light" id="role_display" disabled style="cursor: not-allowed;">
                        <option value="<?php echo htmlspecialchars($user['role']); ?>" selected>
                            <?php echo ucfirst($user['role']); ?>
                        </option>
                    </select>
                    <input type="hidden" name="role" value="<?php echo htmlspecialchars($user['role']); ?>">
                </div>

                <!-- 6. Department -->
                <div class="col-12 col-sm-6">
                    <label for="department_id" class="form-label small fw-semibold text-secondary">Department</label>
                    <?php if ($isAdmin): ?>
                        <select class="form-select input-custom" id="department_id" name="department_id">
                            <option value="">Administrator</option>
                            <?php if (!empty($departments)): ?>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?php echo $d['id']; ?>" <?php echo ($user['department_id'] == $d['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    <?php else: ?>
                        <select class="form-select input-custom bg-light" id="department_id" disabled style="cursor: not-allowed;">
                            <option value="" selected>
                                <?php if (!empty($user['department_code'])): ?>
                                    <?php echo htmlspecialchars($user['department_name']); ?>
                                <?php else: ?>
                                    Administrator
                                <?php endif; ?>
                            </option>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- Submission Actions -->
                <div class="col-12 d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2 hover-lift">
                        <i class="fa-solid fa-save"></i>
                        <span>Save Changes</span>
                    </button>
                    <a href="<?php echo base_url('dashboard'); ?>" class="btn btn-outline-secondary d-flex align-items-center hover-lift">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
