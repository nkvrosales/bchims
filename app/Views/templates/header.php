<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . '' : 'Hospital Inventory Management System'; ?></title>

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css?v=1.0.7'); ?>">


    <link rel="icon" href="<?php echo base_url('bchlogo.ico'); ?>" type="image/x-icon">

    <script>
        const BASE_URL = '<?php echo base_url(); ?>';
    </script>
</head>
<body>

<?php if (session()->get('logged_in')): ?>
    <!-- Mobile Sidebar Backdrop Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Check user collapsible preference on render to prevent layout shift -->
    <div class="app-wrapper <?php echo (isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] === 'true') ? 'collapsed' : ''; ?>">
        
        <!-- 1. TOP NAVBAR HEADER (Spans full width) -->
        <header class="top-navbar">
            <!-- Left: Toggle & Branding -->
            <div class="d-flex align-items-center gap-3">
                <!-- Sidebar Toggle Button (Desktop & Mobile) -->
                <button class="navbar-action-btn" id="sidebarToggle" aria-label="Toggle Sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <!-- Branding -->
                <a href="<?php echo base_url('dashboard'); ?>" class="d-flex align-items-center gap-2" style="text-decoration: none;">
                    <!-- Left Logo: City of Biñan -->
                    <img src="<?php echo base_url('assets/images/bclogo.png'); ?>" alt="City of Biñan Logo" style="height: 35px; width: 35px; object-fit: contain; flex-shrink: 0;">
                    
                    <!-- Brand Text -->
                    <div class="d-none d-md-flex flex-column justify-content-center align-items-start">
                        <div class="brand-title-serif" style="font-size: 0.8rem; font-weight: 800; color: #7e0000 !important; letter-spacing: -0.1px; line-height: 1.15; white-space: nowrap;">BIÑAN CITY HOSPITAL</div>
                        <div style="font-family: var(--font-body); font-weight: 600; letter-spacing: 0.03em; text-transform: uppercase; font-size: 0.46rem; color: #64748b; margin-top: 1px; white-space: nowrap;">Inventory Management System</div>
                    </div>
                    
                    <!-- Right Logo: Biñan City Hospital -->
                    <img src="<?php echo base_url('assets/images/bchlogo.png'); ?>" alt="Biñan City Hospital Logo" style="height: 35px; width: 35px; object-fit: contain; flex-shrink: 0;">
                </a>
            </div>

            <!-- Right: User Dropdown -->
            <?php 
                $fullName = session()->get('full_name'); 
                $username = session()->get('username');
                
                // Generate initials
                $initials = '';
                if (!empty($fullName)) {
                    $parts = explode(' ', trim($fullName));
                    if (count($parts) >= 2) {
                        $initials = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
                    } else {
                        $initials = strtoupper(substr($fullName, 0, 2));
                    }
                } else {
                    $initials = 'US';
                }
            ?>
            <div class="dropdown">
                <button class="navbar-user-profile-btn d-flex align-items-center gap-2 bg-transparent border-0 p-0 shadow-none" type="button" id="userDropdownMenu" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer; outline: none;">
                    <div style="border-left: 1px solid #cbd5e1; height: 32px; margin-right: 0.5rem; margin-left: 0.25rem;"></div>
                    <div class="navbar-user-info text-start d-none d-sm-block" style="font-size: 0.95rem; color: #64748b; font-weight: 500; font-family: var(--font-body);">
                        <?php 
                            $roleLower = strtolower((string)session()->get('role'));
                            $displayRole = ($roleLower === 'dev') ? 'DEVELOPER' : strtoupper($roleLower);
                        ?>
                        <span class="navbar-user-name" style="color: #64748b;"><?php echo htmlspecialchars($fullName); ?></span>
                        <span class="navbar-user-username" style="color: #64748b; margin-left: 0.25rem;">(<?php echo $displayRole; ?>)</span>
                    </div>
                    <i class="bi bi-person-circle" style="font-size: 2.3rem; color: #cbd5e1; line-height: 1;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end navbar-user-dropdown-menu" aria-labelledby="userDropdownMenu">
                    <li class="navbar-dropdown-user-info">
                        <span class="navbar-dropdown-name"><?php echo htmlspecialchars($fullName); ?></span>
                        <span class="navbar-dropdown-role"><?php echo ucfirst(session()->get('role')); ?></span>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item navbar-dropdown-profile" href="<?php echo base_url('dashboard/profile'); ?>" id="headerProfile">
                            <i class="fa-solid fa-user-gear"></i>
                            <span>Profile Settings</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item navbar-dropdown-logout" href="<?php echo base_url('logout'); ?>" id="headerLogout">
                            <i class="fa-solid fa-power-off"></i>
                            <span>Log Out</span>
                        </a>
                    </li>
                </ul>
            </div>
        </header>

        <!-- 2. LEFT SIDEBAR PANEL (Sits below header) -->
        <aside class="sidebar" id="sidebarPanel">
            <ul class="sidebar-menu">

                <!-- MAIN Section -->
                <li class="sidebar-section-label">MAIN</li>

                <li class="sidebar-item">
                    <a href="<?php echo base_url('dashboard'); ?>" 
                       class="sidebar-link <?php echo (isset($title) && $title === 'Dashboard') ? 'active' : ''; ?>" id="navDashboard" title="Dashboard">
                        <i class="bi bi-grid"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="<?php echo base_url('inventory'); ?>"
                       class="sidebar-link <?php echo (isset($title) && in_array($title, ['Inventory', 'Add Item', 'Edit Item'])) ? 'active' : ''; ?>" id="navInventory" title="Inventory">
                        <i class="bi bi-box-seam"></i>
                        <span>Inventory</span>
                    </a>
                </li>

                <?php if (strtolower((string) session()->get('role')) !== 'viewer'): ?>
                <li class="sidebar-item">
                    <a href="<?php echo base_url('requests'); ?>"
                       class="sidebar-link <?php echo (isset($title) && in_array($title, ['Supply Requests'])) ? 'active' : ''; ?>" id="navSupplyRequests" title="Requests">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Requests</span>
                    </a>
                </li>
                <?php endif; ?>

                <!-- SYSTEM Section -->
                <li class="sidebar-section-label">SYSTEM</li>

                <?php if (is_admin_role()): ?>
                <li class="sidebar-item">
                    <a href="<?php echo base_url('users'); ?>" 
                       class="sidebar-link <?php echo (isset($title) && in_array($title, ['User Management', 'Add User', 'Edit User'])) ? 'active' : ''; ?>" id="navUsers" title="Users">
                        <i class="bi bi-people"></i>
                        <span>Users</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="<?php echo base_url('categories'); ?>"
                       class="sidebar-link <?php echo (isset($title) && in_array($title, ['Categories', 'Add Category', 'Edit Category'])) ? 'active' : ''; ?>" id="navCategories" title="Categories">
                        <i class="bi bi-tags"></i>
                        <span>Categories</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="<?php echo base_url('departments'); ?>"
                       class="sidebar-link <?php echo (isset($title) && $title === 'Departments') ? 'active' : ''; ?>" id="navDepartments" title="Departments">
                        <i class="bi bi-building"></i>
                        <span>Departments</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="<?php echo base_url('sources'); ?>"
                       class="sidebar-link <?php echo (isset($title) && $title === 'Sources') ? 'active' : ''; ?>" id="navSources" title="Sources">
                        <i class="bi bi-truck"></i>
                        <span>Sources</span>
                    </a>
                </li>
                <?php endif; ?>

                <li class="sidebar-item">
                    <a href="<?php echo base_url('audit'); ?>"
                       class="sidebar-link <?php echo (isset($title) && $title === 'Audit Trail') ? 'active' : ''; ?>" id="navAuditTrail" title="Audit Trail">
                        <i class="bi bi-clock-history"></i>
                        <span>Audit Trail</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="<?php echo base_url('dashboard/profile'); ?>"
                       class="sidebar-link <?php echo (isset($title) && $title === 'Profile Settings') ? 'active' : ''; ?>" id="navSettings" title="Settings">
                        <i class="bi bi-gear"></i>
                        <span>Settings</span>
                    </a>
                </li>

            </ul>
        </aside>

        <!-- 3. RIGHT MAIN PANEL (Sits below header) -->
        <div class="main-panel" id="mainContentPanel">
            <main class="content-area">
<?php endif; ?>
