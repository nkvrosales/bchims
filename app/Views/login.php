<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - BCHIMS</title>
    
    <!-- Meta tags -->
    <meta name="description" content="Secure administrative login for Biñan City Hospital Inventory Management System.">
    
    <!-- Bootstrap 5, FontAwesome, Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body class="login-bg">

<div class="login-card fade-in-up">
    <!-- Brand and Logo Header -->
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <!-- Left: bclogo -->
            <div style="width: 80px; height: 80px; flex-shrink: 0;">
                <img src="<?php echo base_url('assets/images/bclogo.png'); ?>" alt="City of Biñan Logo" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <!-- Center: title + subtitle -->
            <div class="text-center flex-grow-1">
                <h2 class="brand-title-serif mb-1" style="color: #000000 !important;">BIÑAN CITY HOSPITAL</h2>
                <p class="mb-0" style="font-family: var(--font-body); font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; font-size: 0.65rem; color: #64748b;">Inventory Management System</p>
            </div>
            <!-- Right: bchlogo -->
            <div style="width: 80px; height: 80px; flex-shrink: 0;">
                <img src="<?php echo base_url('assets/images/bchlogo.png'); ?>" alt="Biñan City Hospital Logo" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
        </div>
    </div>

    <!-- Error Flash Alerts -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 d-flex align-items-center gap-2 mb-4 fs-7 py-2.5 px-3" role="alert" id="loginAlertError" style="background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.2); color: #ef4444;">
            <i class="fa-solid fa-circle-exclamation fs-6"></i>
            <div class="flex-grow-1">
                <?php echo session()->getFlashdata('error'); ?>
            </div>
            <button type="button" class="btn-close btn-close-dark" onclick="this.parentElement.remove()" aria-label="Close" style="font-size: 0.75rem;"></button>
        </div>
    <?php endif; ?>

    <!-- Session Expired Notification -->
    <?php if (session()->getFlashdata('session_expired')): ?>
        <div class="alert border-0 rounded-3 d-flex align-items-center gap-2 mb-4 fs-7 py-2.5 px-3" role="alert" id="loginAlertExpired" style="background: rgba(245,158,11,0.12); border-color: rgba(245,158,11,0.2); color: #b45309;">
            <i class="fa-solid fa-clock"></i>
            <div>
                <?php echo session()->getFlashdata('session_expired'); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Login Form -->
    <?php echo form_open('auth/login', array('id' => 'loginForm', 'class' => 'needs-validation')); ?>
        
        <!-- Username input group -->
        <div class="mb-3">
            <label class="form-label text-secondary fw-semibold small mb-1" for="username">Username or Email</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted" style="border-right: none; border-color: #e2e8f0;"><i class="fa-solid fa-user"></i></span>
                <input type="text" 
                       class="form-control" 
                       id="username" 
                       name="username" 
                       placeholder="Enter username or email" 
                       required 
                       autocomplete="username"
                       value="<?php echo set_value('username'); ?>"
                       style="border-left: none; border-color: #e2e8f0;">
            </div>
        </div>

        <!-- Password input group -->
        <div class="mb-4">
            <label class="form-label text-secondary fw-semibold small mb-1" for="password">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted" style="border-right: none; border-color: #e2e8f0;"><i class="fa-solid fa-lock"></i></span>
                <input type="password" 
                       class="form-control" 
                       id="password" 
                       name="password" 
                       placeholder="Enter password" 
                       required
                       autocomplete="current-password"
                       style="border-left: none; border-right: none; border-color: #e2e8f0;">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1" style="border-color: #e2e8f0; border-left: none; color: #94a3b8; background-color: #ffffff;">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary-custom w-100 d-flex align-items-center justify-content-center gap-2 py-2" id="btnSubmit">
            <span class="spinner-border spinner-border-sm d-none me-2" role="status" id="btnSpinner"></span>
            <span id="btnText">Sign In</span>
        </button>

    <?php echo form_close(); ?>
</div>

<!-- Scripts -->
<script>
    // Toggle Password Visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
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

    // Form Submit Animations
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        if (this.checkValidity()) {
            const btnSubmit = document.getElementById('btnSubmit');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            
            btnSubmit.disabled = true;
            btnText.innerText = 'Verifying...';
            btnSpinner.classList.remove('d-none');
        }
    });

    // Clear password field on failed login
    if (document.getElementById('loginAlertError')) {
        document.getElementById('password').value = '';
    }
</script>

</body>
</html>
