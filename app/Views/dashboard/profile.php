<!-- Page Title Section -->
<div class="page-breadcrumb">
    <a href="<?php echo base_url('dashboard'); ?>">Dashboard</a>
    <span class="separator">/</span>
    <span class="current">Profile Settings</span>
</div>

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
                    <?php
                    $errs = validation_errors();
                    if (!empty($errs)):
                        foreach ($errs as $e):
                            echo "<li>" . esc($e) . "</li>";
                        endforeach;
                    endif;
                    ?>
                    <?php if (isset($error)) echo "<li>" . esc($error) . "</li>"; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function togglePasswordVisibility(btnId, inputId) {
        var btn = document.getElementById(btnId);
        var input = document.getElementById(inputId);
        if (!btn || !input) return;
        btn.addEventListener('click', function() {
            var icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }
    togglePasswordVisibility('toggleOldPassword', 'old_password');
    togglePasswordVisibility('toggleNewPassword', 'password');
    togglePasswordVisibility('toggleConfirmPassword', 'confirm_password');
});
</script>
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
    <div class="col-lg-6 col-12">
        <div class="standard-card">
            <div class="card-header-styled mb-4">
                <h5 class="card-title-styled">
                    <span>Profile</span>
                </h5>
            </div>

            <form method="POST" action="<?php echo base_url('dashboard/profile'); ?>" class="row g-3">
                
                <!-- 1. First Name -->
                <div class="col-lg-6 col-12">
                    <label for="first_name" class="form-label small fw-semibold text-secondary">First Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control input-custom" 
                           id="first_name" 
                           name="first_name" 
                           placeholder="Enter first name"
                           value="<?php echo set_value('first_name', $user['first_name']); ?>"
                           required>
                </div>



                <!-- 2. Last Name -->
                <div class="col-lg-6 col-12">
                    <label for="last_name" class="form-label small fw-semibold text-secondary">Last Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control input-custom" 
                           id="last_name" 
                           name="last_name" 
                           placeholder="Enter last name"
                           value="<?php echo set_value('last_name', $user['last_name']); ?>"
                           required>
                </div>

               
                <!-- 3. Username -->
                <div class="col-lg-6 col-12">
                    <label for="username" class="form-label small fw-semibold text-secondary">Username <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control input-custom" 
                           id="username" 
                           name="username"
                           placeholder="Enter username"
                           value="<?php echo set_value('username', $user['username']); ?>"
                           required>
                </div>


                <!-- 4. Email -->
                <div class="col-lg-6 col-12">
                    <label for="email" class="form-label small fw-semibold text-secondary">Email</label>
                    <input type="email"
                           class="form-control input-custom"
                           id="email"
                           name="email"
                           placeholder="Enter email"
                           value="<?php echo set_value('email', $user['email'] ?? ''); ?>">
                </div>

                <!-- 5. Current Password -->
                <div class="col-lg-6 col-12">
                    <label for="old_password" class="form-label small fw-semibold text-secondary">Current Password</label>
                    <div class="position-relative">
                        <input type="password"
                               class="form-control input-custom"
                               id="old_password"
                               name="old_password"
                               placeholder="Enter current password"
                               style="padding-right: 40px;">
                        <button type="button" id="toggleOldPassword" tabindex="-1"
                                style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); border: none; background: none; color: #475569; cursor: pointer; padding: 4px 8px; z-index: 5; display: none;">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <!-- 5. New Password -->
                <div class="w-50"></div>
                <div class="col-lg-6 col-12">
                    <label for="password" class="form-label small fw-semibold text-secondary">New Password</label>
                    <div class="position-relative">
                        <input type="password" 
                               class="form-control input-custom" 
                               id="password" 
                               name="password" 
                               placeholder="Enter new password"
                               style="padding-right: 40px;">
                        <button type="button" id="toggleNewPassword" tabindex="-1"
                                style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); border: none; background: none; color: #475569; cursor: pointer; padding: 4px 8px; z-index: 5; display: none;">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <!-- 6. Confirm Password -->
                <div class="col-lg-6 col-12">
                    <label for="confirm_password" class="form-label small fw-semibold text-secondary">Confirm New Password</label>
                    <div class="position-relative">
                        <input type="password"
                               class="form-control input-custom"
                               id="confirm_password"
                               name="confirm_password"
                               placeholder="Re-enter new password"
                               style="padding-right: 40px;">
                        <button type="button" id="toggleConfirmPassword" tabindex="-1"
                                style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); border: none; background: none; color: #475569; cursor: pointer; padding: 4px 8px; z-index: 5; display: none;">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <!-- Submission Actions -->
                <div class="col-12 d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary-custom px-4 py-2 d-flex align-items-center gap-2 hover-lift">
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var toggles = [
        { btn: 'toggleOldPassword', inp: 'old_password' },
        { btn: 'toggleNewPassword', inp: 'password' },
        { btn: 'toggleConfirmPassword', inp: 'confirm_password' }
    ];
    toggles.forEach(function(t) {
        var btn = document.getElementById(t.btn);
        var inp = document.getElementById(t.inp);
        if (!btn || !inp) return;
        inp.addEventListener('input', function() {
            btn.style.display = this.value ? '' : 'none';
        });
        btn.addEventListener('click', function() {
            var icon = btn.querySelector('i');
            if (inp.type === 'password') {
                inp.type = 'text';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                inp.type = 'password';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        });
    });
});
</script>
