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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.3/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css?v=1.1.2'); ?>">


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
                        <div class="brand-title-serif" style="font-size: 0.8rem; font-weight: 800; color: #000000 !important; letter-spacing: -0.1px; line-height: 1.15; white-space: nowrap;">BIÑAN CITY HOSPITAL</div>
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
                    <i class="fa-solid fa-circle-user" style="font-size: 2.3rem; color: #cbd5e1; line-height: 1;"></i>
                    <div class="navbar-user-info text-start d-none d-sm-block" style="font-size: 0.95rem; color: #64748b; font-weight: 500; font-family: var(--font-body);">
                        <?php 
                            $roleLower = strtolower((string)session()->get('role'));
                            $displayRole = ($roleLower === 'dev') ? 'DEVELOPER' : strtoupper($roleLower);
                        ?>
                        <span class="navbar-user-username" style=" text-transform: uppercase;"><?php echo htmlspecialchars($username); ?></span>
                    </div>
                    <i class="bi bi-chevron-down" style="font-size: 0.7rem; color: #94a3b8; line-height: 1;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end navbar-user-dropdown-menu" aria-labelledby="userDropdownMenu">
                    <li class="navbar-dropdown-user-info" style="padding: 0.75rem 1.25rem;">
                        <div style="font-weight: 700; font-size: 0.95rem; color: #1e293b; line-height: 1.3;"><?php echo htmlspecialchars($fullName); ?></div>
                        <div style="font-weight: 500; font-size: 0.78rem; color: #64748b; margin-top: 0.25rem; line-height: 1.3;">
                            <?php 
                                $_role = strtolower((string)session()->get('role'));
                                echo $_role === 'dev' ? 'Developer' : ucfirst($_role);
                            ?>
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem; line-height: 1.3;">
                            <?php
                                $_dept = session()->get('department_name');
                                $_r = strtolower((string)session()->get('role'));
                                if (!empty($_dept)) {
                                    echo htmlspecialchars($_dept);
                                } elseif (in_array($_r, ['admin', 'dev'])) {
                                    echo 'Administrator';
                                } else {
                                    echo 'N/A';
                                }
                            ?>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item navbar-dropdown-profile" href="<?php echo base_url('dashboard/profile'); ?>" id="headerProfile">
                            <i class="bi bi-person-gear"></i>
                            <span>Profile Settings</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item navbar-dropdown-logout" href="<?php echo base_url('logout'); ?>" id="headerLogout">
                            <i class="bi bi-box-arrow-right"></i>
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
                       class="sidebar-link <?php echo (isset($title) && (strpos($title, 'Inventory') !== false)) ? 'active' : ''; ?>" id="navInventory" title="Inventory">
                        <i class="bi bi-box-seam"></i>
                        <span>Inventory</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="<?php echo base_url('requests'); ?>"
                       class="sidebar-link <?php echo (isset($title) && (strpos($title, 'Requests') !== false)) ? 'active' : ''; ?>" id="navSupplyRequests" title="Requests">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Requests</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="<?php echo base_url('reports'); ?>"
                       class="sidebar-link <?php echo (isset($title) && $title === 'Reports') ? 'active' : ''; ?>" id="navReports" title="Reports">
                        <i class="bi bi-bar-chart"></i>
                        <span>Reports</span>
                    </a>
                </li>

                <!-- SYSTEM Section -->
                <li class="sidebar-section-label">SYSTEM</li>

                <?php if (is_admin_role()): ?>
                <li class="sidebar-item">
                    <a href="<?php echo base_url('users'); ?>" 
                       class="sidebar-link <?php echo (isset($title) && in_array($title, ['User Management', 'Add User', 'Edit User'])) ? 'active' : ''; ?>" id="navUsers" title="User Management">
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
                    <a href="<?php echo base_url('unit'); ?>"
                       class="sidebar-link <?php echo (isset($title) && $title === 'Unit') ? 'active' : ''; ?>" id="navUnit" title="Unit">
                        <i class="fa-solid fa-box"></i>
                        <span>Unit</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="<?php echo base_url('suppliers'); ?>"
                       class="sidebar-link <?php echo (isset($title) && $title === 'Suppliers') ? 'active' : ''; ?>" id="navSuppliers" title="Suppliers">
                        <i class="bi bi-truck"></i>
                        <span>Suppliers</span>
                    </a>
                </li>
                <?php endif; ?>

                <li class="sidebar-item">
                    <a href="<?php echo base_url('audit'); ?>"
                       class="sidebar-link <?php echo (isset($title) && $title === 'Audit Log') ? 'active' : ''; ?>" id="navAuditTrail" title="Audit Log">
                        <i class="bi bi-clock-history"></i>
                        <span>Audit Log</span>
                    </a>
                </li>

            </ul>
        </aside>

        <!-- 3. RIGHT MAIN PANEL (Sits below header) -->
        <div class="main-panel" id="mainContentPanel">
            <main class="content-area">
<?php endif; ?>
