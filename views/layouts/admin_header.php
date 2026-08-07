<?php
// File: /views/layouts/admin_header.php
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$isActive = function ($route, $exact = false) use ($currentUri) {
    if ($exact) {
        return (strpos($currentUri, $route) !== false && $currentUri === $route) ? 'active' : '';
    }
    return (strpos($currentUri, $route) !== false) ? 'active' : '';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Admin Panel'; ?> - ROGELE Platform</title>
    <base href="<?php echo BASE_URL; ?>/">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #7f2677;
            --primary-dark: #5c1856;
            --primary-light: #9a3391;
            --accent: #f06724;
            --accent-hover: #d8571a;
            --dark: #000;
            --gray-900: #000;
            --gray-700: #555;
            --gray-300: #cbd5e1;
            --gray-100: #f1f5f9;
            --bg-light: #f8fafc;
            --white: #ffffff;
            
            --sidebar-width: 260px;
            --sidebar-mini-width: 72px;
            --navbar-height: 64px;
            --transition-speed: 0.25s;
            --transition-curve: cubic-bezier(0.4, 0, 0.2, 1);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.06), 0 2px 4px -1px rgba(0, 0, 0, 0.04);
            --shadow-lg: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--bg-light);
            color: var(--dark);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }

        .admin-top-navbar {
            height: var(--navbar-height);
            background: var(--white);
            display: flex;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: var(--shadow-sm);
        }

        .navbar-brand-block {
            width: var(--sidebar-width);
            background: var(--primary);
            height: 100%;
            display: flex;
            align-items: center;
            padding: 0 16px;
            gap: 12px;
            transition: width var(--transition-speed) var(--transition-curve);
            flex-shrink: 0;
        }

        body.sidebar-collapsed .navbar-brand-block {
            width: var(--sidebar-mini-width);
            padding: 0 12px;
            justify-content: center;
        }

        .menu-toggle-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: var(--white);
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .menu-toggle-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .brand-logo-details {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            overflow: hidden;
            white-space: nowrap;
        }

        body.sidebar-collapsed .brand-logo-details {
            display: none;
        }

        .brand-logo-details img {
            width: 32px;
            height: 32px;
            object-fit: contain;
            border-radius: 6px;
        }

        .brand-text-wrapper {
            display: flex;
            flex-direction: column;
        }

        .brand-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--white);
            letter-spacing: 0.5px;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .brand-subtitle {
            font-size: 0.65rem;
            color: rgba(255, 255, 255, 0.75);
            font-weight: 500;
        }

        .navbar-right-block {
            flex: 1;
            padding: 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--white);
        }

        .mobile-brand-wrapper {
            display: none;
            align-items: center;
            gap: 12px;
        }

        .mobile-brand-wrapper img {
            height: 30px;
            width: auto;
            object-fit: contain;
        }

        .header-search-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--gray-100);
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid transparent;
            transition: all 0.2s ease;
            width: 280px;
        }

        .header-search-bar:focus-within {
            background: var(--white);
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(240, 103, 36, 0.1);
        }

        .header-search-bar i {
            color: var(--accent);
            font-size: 0.85rem;
        }

        .header-search-bar input {
            border: none;
            background: transparent;
            outline: none;
            font-size: 0.82rem;
            color: var(--dark);
            width: 100%;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-left: auto;
        }

        .header-action-btn {
            background: transparent;
            border: none;
            color: var(--gray-700);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            transition: background 0.2s;
        }

        .header-action-btn:hover {
            background: var(--gray-100);
            color: var(--dark);
        }

        .notification-indicator {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            background: var(--accent);
            border-radius: 50%;
            border: 2px solid var(--white);
        }

        .profile-trigger {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 4px 8px 4px 4px;
            border-radius: 30px;
            border: 1px solid var(--gray-200);
            background: var(--white);
            position: relative;
            transition: all 0.2s ease;
            user-select: none;
        }

        .profile-trigger:hover {
            background: var(--bg-light);
            border-color: var(--gray-300);
        }

        .admin-avatar {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            color: var(--white);
            font-weight: 600;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .admin-meta {
            display: flex;
            flex-direction: column;
            text-align: left;
            padding-right: 4px;
        }

        .admin-meta span {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--dark);
            line-height: 1.2;
        }

        .admin-meta small {
            font-size: 0.68rem;
            color: var(--gray-700);
        }

        .profile-dropdown-card {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: var(--white);
            width: 210px;
            border-radius: 10px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-300);
            display: none;
            flex-direction: column;
            padding: 6px 0;
            z-index: 1100;
            animation: fadeIn 0.15s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .profile-dropdown-card.show {
            display: flex;
        }

        .profile-dropdown-card a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 16px;
            color: var(--gray-700);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: background 0.15s ease;
        }

        .profile-dropdown-card a:hover {
            background: var(--bg-light);
            color: var(--primary);
        }

        .profile-dropdown-card a i {
            width: 16px;
            text-align: center;
            color: var(--accent);
            font-size: 0.9rem;
        }

        .profile-dropdown-card a:hover i {
            color: var(--primary);
        }

        .profile-dropdown-card .divider {
            height: 1px;
            background: var(--gray-100);
            margin: 4px 0;
        }

        .profile-dropdown-card a.logout-link {
            color: #ef4444;
        }

        .profile-dropdown-card a.logout-link i {
            color: #ef4444;
        }

        .profile-dropdown-card a.logout-link:hover {
            background: #fef2f2;
            color: #dc2626;
        }

        .admin-sidebar {
            width: var(--sidebar-width);
            background: var(--primary-dark);
            position: fixed;
            top: var(--navbar-height);
            bottom: 0;
            left: 0;
            z-index: 900;
            display: flex;
            flex-direction: column;
            transition: width var(--transition-speed) var(--transition-curve), transform var(--transition-speed) var(--transition-curve);
        }

        body.sidebar-collapsed .admin-sidebar {
            width: var(--sidebar-mini-width);
        }

        .sidebar-scroll-container {
            flex: 1;
            padding: 16px 10px;
            overflow-y: auto;
            
            &::-webkit-scrollbar {
                display: none;
            }
            -ms-overflow-style: none; 
            scrollbar-width: none; 
        }

        .menu-group-label {
            font-size: 0.65rem;
            text-transform: uppercase;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 1px;
            padding: 16px 12px 6px 12px;
            white-space: nowrap;
            overflow: hidden;
        }

        body.sidebar-collapsed .menu-group-label {
            display: none;
        }

        .menu-node-wrapper {
            margin-bottom: 4px;
            position: relative;
        }

        .menu-anchor-item {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.88rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
            width: 100%;
            background: transparent;
            border: none;
            text-align: left;
            white-space: nowrap;
        }

        .menu-anchor-item:hover {
            color: var(--white);
            background: rgba(255, 255, 255, 0.08);
        }

        .menu-anchor-item.active {
            background: var(--accent);
            color: var(--white);
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(240, 103, 36, 0.3);
        }

        .menu-anchor-item i.anchor-icon {
            width: 20px;
            font-size: 1rem;
            text-align: center;
            margin-right: 12px;
            flex-shrink: 0;
        }

        body.sidebar-collapsed .menu-anchor-item i.anchor-icon {
            margin-right: 0;
        }

        body.sidebar-collapsed .anchor-label-text,
        body.sidebar-collapsed .carat-indicator {
            display: none;
        }

        .carat-indicator {
            margin-left: auto;
            font-size: 0.7rem;
            opacity: 0.7;
            transition: transform 0.2s ease;
        }

        .menu-node-wrapper.expanded .carat-indicator {
            transform: rotate(90deg);
        }

        .submenu-node-box {
            list-style: none;
            padding-left: 12px;
            margin-top: 2px;
            display: none;
        }

        .menu-node-wrapper.expanded .submenu-node-box {
            display: block;
        }

        body.sidebar-collapsed .submenu-node-box {
            display: none !important;
        }

        .submenu-anchor {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px 8px 22px;
            color: rgba(255, 255, 255, 0.65);
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .submenu-anchor:hover {
            color: var(--white);
            background: rgba(255, 255, 255, 0.05);
        }

        .submenu-anchor.active {
            color: var(--white);
            font-weight: 600;
            background: rgba(255, 255, 255, 0.12);
        }

        .admin-view-body {
            flex: 1;
            margin-left: var(--sidebar-width);
            margin-top: var(--navbar-height);
            display: flex;
            flex-direction: column;
            min-width: 0;
            transition: margin-left var(--transition-speed) var(--transition-curve);
        }

        body.sidebar-collapsed .admin-view-body {
            margin-left: var(--sidebar-mini-width);
        }

        .view-content-wrapper {
            padding: 24px;
            flex: 1;
        }

        .sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(2px);
            z-index: 850;
            display: none;
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .toast-container {
            margin-bottom: 20px;
        }

        .alert-toast {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 500;
            box-shadow: var(--shadow-sm);
            margin-bottom: 12px;
        }

        .alert-toast.alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-toast.alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        @media (max-width: 992px) {
            .navbar-brand-block {
                display: none;
            }

            .header-search-bar {
                display: none;
            }

            .navbar-right-block {
                padding: 0 16px;
            }

            .mobile-brand-wrapper {
                display: flex;
            }

            .mobile-menu-btn {
                background: transparent;
                border: none;
                color: var(--gray-700);
                font-size: 1.25rem;
                cursor: pointer;
                width: 36px;
                height: 36px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 6px;
                transition: background 0.2s;
            }

            .mobile-menu-btn:hover {
                background: var(--gray-100);
            }

            .admin-sidebar {
                transform: translateX(-100%);
                top: var(--navbar-height);
                height: calc(100vh - var(--navbar-height));
                box-shadow: var(--shadow-lg);
                width: var(--sidebar-width) !important;
            }

            body.sidebar-open .admin-sidebar {
                transform: translateX(0);
            }

            body.sidebar-open .sidebar-backdrop {
                display: block;
                opacity: 1;
            }

            .admin-sidebar .anchor-label-text,
            .admin-sidebar .carat-indicator {
                display: inline-block !important;
            }

            .admin-sidebar .menu-group-label {
                display: block !important;
            }

            .admin-sidebar .menu-anchor-item i.anchor-icon {
                margin-right: 12px !important;
            }

            .admin-view-body, 
            body.sidebar-collapsed .admin-view-body {
                margin-left: 0 !important;
            }

            .view-content-wrapper {
                padding: 16px;
            }

            .admin-meta {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="admin-layout">
    <header class="admin-top-navbar">
        <div class="navbar-brand-block">
            <button class="menu-toggle-btn" id="menuToggleDesktop" aria-label="Toggle Navigation Side Drawer" title="Collapse/Expand Menu">
                <i class="fas fa-bars"></i>
            </button>
            <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="brand-logo-details">
                <img src="<?php echo BASE_URL; ?>/public/images/logo.png" alt="Logo" onerror="this.style.display='none';">
                <div class="brand-text-wrapper">
                    <span class="brand-title">RAYS OF GRACE</span>
                    <span class="brand-subtitle">E-Learning Environment</span>
                </div>
            </a>
        </div>

        <div class="navbar-right-block">
            <div class="mobile-brand-wrapper">
                <button class="mobile-menu-btn" id="menuToggleMobile" aria-label="Open Mobile Drawer">
                    <i class="fas fa-bars"></i>
                </button>
                <img src="<?php echo BASE_URL; ?>/public/images/logo.png" alt="School Logo" onerror="this.style.display='none';">
            </div>

            <div class="header-search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search system resources..." aria-label="Search">
            </div>

            <div class="header-actions">
                <button class="header-action-btn" title="Notifications" aria-label="View notifications">
                    <i class="far fa-bell"></i>
                    <span class="notification-indicator"></span>
                </button>

                <div class="profile-trigger" id="profileTrigger">
                    <div class="admin-avatar">
                        <?php 
                        $nameParts = explode(' ', $_SESSION['user_name'] ?? 'Admin');
                        $initials = '';
                        foreach ($nameParts as $part) {
                            if (!empty($part)) $initials .= strtoupper(substr($part, 0, 1));
                        }
                        echo substr($initials, 0, 2);
                        ?>
                    </div>
                    <div class="admin-meta">
                        <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Administrator'); ?></span>
                        <small>System Admin</small>
                    </div>
                    <i class="fas fa-chevron-down" style="font-size: 0.65rem; color: var(--gray-700); margin-left: 2px;"></i>

                    <div class="profile-dropdown-card" id="profileDropdown">
                        <a href="<?php echo BASE_URL; ?>/admin/profile">
                            <i class="fas fa-user-shield"></i> View Profile
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/settings">
                            <i class="fas fa-sliders-h"></i> System Settings
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/login-activity">
                            <i class="fas fa-history"></i> Login Activity
                        </a>
                        <div class="divider"></div>
                        <a href="<?php echo BASE_URL; ?>/logout" class="logout-link">
                            <i class="fas fa-sign-out-alt"></i> Sign Out
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <aside class="admin-sidebar" id="sidebar">
        <div class="sidebar-scroll-container">
            
            <div class="menu-node-wrapper">
                <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="menu-anchor-item <?php echo $isActive('dashboard'); ?>">
                    <i class="fas fa-chart-pie anchor-icon"></i>
                    <span class="anchor-label-text">Dashboard</span>
                </a>
            </div>

            <div class="menu-group-label">User Management</div>
            <div class="menu-node-wrapper">
                <a href="<?php echo BASE_URL; ?>/admin/users" class="menu-anchor-item <?php echo (strpos($currentUri, '/admin/users') !== false && strpos($currentUri, '/create') === false) ? 'active' : ''; ?>">
                    <i class="fas fa-users anchor-icon"></i>
                    <span class="anchor-label-text">Manage Users</span>
                </a>
            </div>
            <div class="menu-node-wrapper">
                <a href="<?php echo BASE_URL; ?>/admin/users/create" class="menu-anchor-item <?php echo $isActive('/users/create'); ?>">
                    <i class="fas fa-user-plus anchor-icon"></i>
                    <span class="anchor-label-text">Add New User</span>
                </a>
            </div>

            <div class="menu-group-label">Academics & Content</div>
            <div class="menu-node-wrapper">
                <a href="<?php echo BASE_URL; ?>/admin/lessons" class="menu-anchor-item <?php echo ($isActive('lessons') || $isActive('view_lesson')) ? 'active' : ''; ?>">
                    <i class="fas fa-book-open anchor-icon"></i>
                    <span class="anchor-label-text">Lessons</span>
                </a>
            </div>
            <div class="menu-node-wrapper">
                <a href="<?php echo BASE_URL; ?>/admin/quizzes" class="menu-anchor-item <?php echo ($isActive('quizzes') || $isActive('view_quiz')) ? 'active' : ''; ?>">
                    <i class="fas fa-graduation-cap anchor-icon"></i>
                    <span class="anchor-label-text">Quizzes</span>
                </a>
            </div>
            <div class="menu-node-wrapper">
                <a href="<?php echo BASE_URL; ?>/admin/homework" class="menu-anchor-item <?php echo $isActive('homework'); ?>">
                    <i class="fas fa-tasks anchor-icon"></i>
                    <span class="anchor-label-text">Homework</span>
                </a>
            </div>

            <div class="menu-group-label">Finance & Billing</div>
            <div class="menu-node-wrapper <?php echo $isActive('subscriptions') ? 'expanded' : ''; ?>">
                <div class="menu-anchor-item submenu-toggle-trigger <?php echo $isActive('subscriptions') ? 'active' : ''; ?>">
                    <i class="fas fa-credit-card anchor-icon"></i> 
                    <span class="anchor-label-text">Subscriptions</span>
                    <i class="fas fa-chevron-right carat-indicator"></i>
                </div>
                
                <ul class="submenu-node-box">
                    <li>
                        <a href="<?php echo BASE_URL; ?>/admin/subscriptions" class="submenu-anchor <?php echo (strpos($currentUri, '/admin/subscriptions') !== false && strpos($currentUri, '/reports') === false && strpos($currentUri, '/plans') === false) ? 'active' : ''; ?>">
                            <i class="fas fa-list-ul" style="font-size: 0.65rem;"></i> Subscribers List
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/admin/subscriptions/reports" class="submenu-anchor <?php echo $isActive('subscriptions/reports'); ?>">
                            <i class="fas fa-chart-bar" style="font-size: 0.65rem;"></i> Revenue Reports
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/admin/settings" class="submenu-anchor <?php echo $isActive('subscriptions/settings'); ?>">
                            <i class="fas fa-tags" style="font-size: 0.65rem;"></i> Plan Packages
                        </a>
                    </li>
                </ul>
            </div>

            <div class="menu-group-label">System Control</div>
            <div class="menu-node-wrapper">
                <a href="<?php echo BASE_URL; ?>/admin/reports" class="menu-anchor-item <?php echo (strpos($currentUri, 'reports') !== false && strpos($currentUri, 'subscriptions') === false) ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line anchor-icon"></i>
                    <span class="anchor-label-text">Data Analytics</span>
                </a>
            </div>
            <div class="menu-node-wrapper">
                <a href="<?php echo BASE_URL; ?>/admin/settings" class="menu-anchor-item <?php echo $isActive('settings'); ?>">
                    <i class="fas fa-cog anchor-icon"></i>
                    <span class="anchor-label-text">System Settings</span>
                </a>
            </div>
        </div>
    </aside>

    <div class="admin-view-body">
        <div class="view-content-wrapper">
            
            <div class="toast-container">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert-toast alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert-toast alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (localStorage.getItem('admin_sidebar_collapsed') === 'true') {
                    document.body.classList.add('sidebar-collapsed');
                }

                const profileTrigger = document.getElementById('profileTrigger');
                const profileDropdown = document.getElementById('profileDropdown');

                if (profileTrigger && profileDropdown) {
                    profileTrigger.addEventListener('click', function(e) {
                        e.stopPropagation();
                        profileDropdown.classList.toggle('show');
                    });

                    document.addEventListener('click', function(e) {
                        if (!profileDropdown.contains(e.target)) {
                            profileDropdown.classList.remove('show');
                        }
                    });
                }

                const menuToggleDesktop = document.getElementById('menuToggleDesktop');
                if (menuToggleDesktop) {
                    menuToggleDesktop.addEventListener('click', function(e) {
                        e.stopPropagation();
                        document.body.classList.toggle('sidebar-collapsed');
                        const isCollapsed = document.body.classList.contains('sidebar-collapsed');
                        localStorage.setItem('admin_sidebar_collapsed', isCollapsed);
                    });
                }

                const menuToggleMobile = document.getElementById('menuToggleMobile');
                const sidebarBackdrop = document.getElementById('sidebarBackdrop');

                if (menuToggleMobile) {
                    menuToggleMobile.addEventListener('click', function(e) {
                        e.stopPropagation();
                        document.body.classList.add('sidebar-open');
                    });
                }

                if (sidebarBackdrop) {
                    sidebarBackdrop.addEventListener('click', function() {
                        document.body.classList.remove('sidebar-open');
                    });
                }

                const submenuToggles = document.querySelectorAll('.submenu-toggle-trigger');
                submenuToggles.forEach(toggle => {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (document.body.classList.contains('sidebar-collapsed')) {
                            document.body.classList.remove('sidebar-collapsed');
                            localStorage.setItem('admin_sidebar_collapsed', 'false');
                        }
                        const nodeWrapper = this.parentElement;
                        nodeWrapper.classList.toggle('expanded');
                    });
                });
            });
            </script>