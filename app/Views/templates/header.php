<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . ' - Hospital IMS' : 'Hospital Inventory Management System'; ?></title>
    
    <!-- Meta Descriptions for SEO -->
    <meta name="description" content="Core administrative dashboard and user activities audit logs portal for the Hospital Inventory Management System (IMS).">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- FontAwesome & Bootstrap 5 & jQuery DataTables Buttons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body>

<?php if (session()->get('logged_in')): ?>
    <!-- Mobile Sidebar Backdrop Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="app-wrapper">
        <!-- 1. LEFT SIDEBAR PANEL -->
        <aside class="sidebar" id="sidebarPanel">
            <div class="sidebar-header">
                <a href="<?php echo base_url('dashboard'); ?>" class="sidebar-brand d-flex align-items-center gap-2 py-3 px-3">
                    <img src="<?php echo base_url('assets/images/logo-placeholder.png'); ?>" alt="Biñan City Hospital Logo" style="height: 32px; width: 32px; border-radius: 50%; object-fit: cover;">
                    <span style="font-size: 1.05rem; font-weight: 700; color: var(--text-dark);">Biñan City Hospital</span>
                </a>
            </div>
            
            <ul class="sidebar-menu">
                <li class="sidebar-item">
                    <a href="<?php echo base_url('dashboard'); ?>" 
                       class="sidebar-link <?php echo (isset($title) && $title === 'Dashboard') ? 'active' : ''; ?>" id="navDashboard">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <?php if (session()->get('role') === 'admin'): ?>
                <li class="sidebar-item">
                    <a href="<?php echo base_url('users'); ?>" 
                       class="sidebar-link <?php echo (isset($title) && ($title === 'User Management' || $title === 'Add User' || $title === 'Edit User')) ? 'active' : ''; ?>" id="navUsers">
                        <i class="fa-solid fa-users-gear"></i>
                        <span>User Management</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="sidebar-item">
                    <a href="<?php echo base_url('dashboard/audit_trail'); ?>" 
                       class="sidebar-link <?php echo (isset($title) && ($title === 'Audit Trail' || $title === 'Audit Trail Log')) ? 'active' : ''; ?>" id="navAuditTrail">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>Audit Trail</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <div class="user-profile-widget">
                    <div class="user-avatar-square">
                        <?php 
                            $name = session()->get('full_name');
                            echo !empty($name) ? strtoupper(substr($name, 0, 1)) : 'U';
                        ?>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <h6 class="text-dark mb-0 text-truncate fs-7 fw-semibold" style="font-size:0.875rem;"><?php echo $name; ?></h6>
                        <small class="text-muted text-capitalize text-truncate d-block fs-8" style="font-size:0.75rem;"><?php echo session()->get('role'); ?></small>
                    </div>
                    <a href="<?php echo base_url('logout'); ?>" class="text-danger fs-5 ms-auto p-1" title="Log Out" id="sidebarLogout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- 2. RIGHT MAIN CONTENT PANEL -->
        <div class="main-panel" id="mainContentPanel">
            <!-- Top Header Navbar -->
            <header class="top-navbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="navbar-action-btn d-lg-none" id="sidebarToggleMobile" aria-label="Toggle Sidebar">
                        <i class="fa-solid fa-bars-staggered"></i>
                    </button>
                    <h4 class="navbar-page-title mb-0"><?php echo isset($title) ? htmlspecialchars($title) : 'Dashboard'; ?></h4>
                </div>
                
                <div class="d-flex align-items-center gap-4 navbar-datetime">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-regular fa-calendar text-muted"></i>
                        <span id="liveClockDate"><?php echo date('l, F d, Y'); ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-regular fa-clock text-muted"></i>
                        <span id="liveClockTime"><?php echo date('h:i:s A'); ?></span>
                    </div>
                </div>
            </header>
            
            <main class="content-area">
<?php endif; ?>
