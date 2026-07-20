<?php
// File: /views/layouts/admin_header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Admin Panel'; ?></title>
    <base href="<?php echo BASE_URL; ?>/">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #7f2677;
            --primary-dark: #5c1856;
            --accent: #f06724;
            --dark: #000;
            --gray: #555;
            --bg-light: #eef2f5;
            --sidebar-width: 270px;
            --sidebar-mini-width: 78px;
            --navbar-height: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-light);
            color: var(--dark);
            overflow-x: hidden;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }

        .admin-top-navbar {
            height: var(--navbar-height);
            background: #ffffff;
            display: flex;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .navbar-brand-block {
            width: var(--sidebar-width);
            background: var(--primary);
            height: 100%;
            display: flex;
            align-items: center;
            padding: 0 20px;
            gap: 12px;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            flex-shrink: 0;
        }

        body.sidebar-collapsed .navbar-brand-block {
            width: var(--sidebar-mini-width);
            padding: 0;
            justify-content: center;
        }

        .menu-toggle-btn {
            background: transparent;
            border: none;
            color: #ffffff;
            font-size: 1.3rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            transition: background 0.2s;
            -webkit-text-stroke: 1px var(--primary);
        }

        .menu-toggle-btn:hover {
            background: var(--dark);
            border-radius: 50%;
            -webkit-text-stroke: 1px var(--dark);
        }

        body.sidebar-collapsed .brand-logo-details {
            display: none;
        }

        .brand-logo-details {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .brand-logo-details img {
            width: 35px;
            height: auto;
            border-radius: 6px;
        }

        .brand-text-wrapper {
            display: flex;
            flex-direction: column;
        }

        .brand-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .brand-subtitle {
            font-size: 0.65rem;
            color: rgba(255,255,255,0.75);
            font-weight: 500;
        }

        .navbar-right-block {
            flex: 1;
            padding: 0 32px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            background: #ffffff;
        }

        .mobile-brand-wrapper {
            display: none;
            align-items: center;
            gap: 10px;
            margin-left: 12px;
        }
        .mobile-brand-wrapper img {
            width: 34px;
            height: 34px;
            object-fit: contain;
        }

        .profile-trigger {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 6px 16px;
            border-radius: 50px;
            background: var(--bg-light);
            position: relative;
            transition: background 0.2s;
        }

        .profile-trigger:hover {
            background: #e2e8f0;
        }

        .admin-avatar {
            width: 36px;
            height: 36px;
            background: var(--accent);
            color: #ffffff;
            font-weight: 600;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        .admin-meta {
            display: flex;
            flex-direction: column;
        }

        .admin-meta span {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--dark);
        }

        .admin-meta small {
            font-size: 0.72rem;
            color: var(--gray);
        }

        .profile-dropdown-card {
            position: absolute;
            top: 52px;
            right: 0;
            background: #ffffff;
            width: 220px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            display: none;
            flex-direction: column;
            padding: 8px 0;
            z-index: 200;
        }

        .profile-dropdown-card.show {
            display: flex;
        }

        .profile-dropdown-card a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            color: var(--accent);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .profile-dropdown-card a:hover {
            background: var(--bg-light);
            color: var(--accent);
        }

        .profile-dropdown-card .divider {
            height: 1px;
            background: #e2e8f0;
            margin: 6px 0;
        }

        .logout-link {
            color: #dc2626 !important;
            font-weight: 600;
        }

        .admin-sidebar {
            width: var(--sidebar-width);
            background: var(--primary-dark);
            position: fixed;
            top: var(--navbar-height);
            bottom: 0;
            left: 0;
            z-index: 150;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.sidebar-collapsed .admin-sidebar {
            width: var(--sidebar-mini-width);
        }

        .sidebar-scroll-container {
            flex: 1;
            padding: 24px 12px;
            overflow-y: auto;
            scrollbar-width: none;
        }

        .sidebar-scroll-container::-webkit-scrollbar {
            display: none;
        }

        .menu-group-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 0.8px;
            padding: 14px 16px 6px 16px;
        }

        body.sidebar-collapsed .menu-group-label {
            display: none;
        }

        .menu-node-wrapper {
            margin-bottom: 6px;
            position: relative;
        }

        .menu-anchor-item {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.92rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
            width: 100%;
            background: transparent;
            border: none;
            text-align: left;
        }

        .menu-anchor-item:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.05);
        }

        .menu-anchor-item.active {
            background: linear-gradient(135deg, var(--accent), #d45216);
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(240, 103, 36, 0.25);
        }

        .menu-anchor-item i.anchor-icon {
            width: 22px;
            font-size: 1.15rem;
            text-align: center;
            margin-right: 14px;
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
            font-size: 0.75rem;
            opacity: 0.7;
            transition: transform 0.2s ease;
        }

        .menu-node-wrapper.expanded .carat-indicator {
            transform: rotate(90deg);
        }

        .submenu-node-box {
            list-style: none;
            padding-left: 20px;
            margin-top: 4px;
            display: none;
        }

        .menu-node-wrapper.expanded .submenu-node-box {
            display: block;
        }

        .submenu-anchor {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .submenu-anchor:hover, .submenu-anchor.active {
            color: #ffffff;
            padding-left: 18px;
            background: rgba(255, 255, 255, 0.04);
        }

        .admin-view-body {
            flex: 1;
            margin-left: var(--sidebar-width);
            margin-top: var(--navbar-height);
            display: flex;
            flex-direction: column;
            min-width: 0;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.sidebar-collapsed .admin-view-body {
            margin-left: var(--sidebar-mini-width);
        }

        .view-content-wrapper {
            padding: 30px;
            flex: 1;
        }

        .sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 140;
            display: none;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        body.sidebar-open .sidebar-backdrop {
            display: block;
            opacity: 1;
        }

        @media (max-width: 992px) {
            .navbar-brand-block {
                display: none;
            }

            .navbar-right-block {
                padding: 0 16px;
                justify-content: space-between;
            }

            .mobile-brand-wrapper {
                display: flex;
            }

            .mobile-menu-btn {
                background: transparent;
                border: none;
                color: #555;
                font-size: 1.4rem;
                cursor: pointer;
                width: 38px;
                height: 38px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s ease;
            }

            .mobile-menu-btn:hover {
                background: var(--accent);
                color: #ffffff;
                border-radius: 50%;
                -webkit-text-stroke: 1px var(--accent);
            }

            .admin-sidebar {
                transform: translateX(-100%);
                top: var(--navbar-height) !important; 
                height: calc(100vh - var(--navbar-height)) !important;
                box-shadow: 4px 2px 15px rgba(0,0,0,0.1);
                width: var(--sidebar-width) !important;
            }

            body.sidebar-open .admin-sidebar {
                transform: translateX(0);
            }

            .admin-sidebar .anchor-label-text,
            .admin-sidebar .carat-indicator {
                display: inline-block !important;
            }

            .admin-sidebar .menu-group-label {
                display: block !important;
            }

            .admin-sidebar .menu-anchor-item i.anchor-icon {
                margin-right: 14px !important;
            }

            .admin-view-body, body.sidebar-collapsed .admin-view-body {
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
            <button class="menu-toggle-btn id-desktop-toggle" id="menuToggleDesktop" aria-label="Toggle Navigation Side Draw">
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
                <button class="mobile-menu-btn" id="menuToggleMobile" aria-label="Open Side Drawer">
                    <i class="fas fa-bars"></i>
                </button>
                <img src="<?php echo BASE_URL; ?>/public/images/logo.png" alt="School Logo" onerror="this.style.display='none';">
            </div>

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
                <i class="fas fa-chevron-down" style="font-size: 0.75rem; color: var(--gray); margin-left: 4px;"></i>

                <div class="profile-dropdown-card" id="profileDropdown">
                    <a href="<?php echo BASE_URL; ?>/admin/profile" style="color: #000; text-decoration: none;">
                        <i class="fas fa-user-shield" style="color: #f06724; margin-right: 10px;"></i>View Profile
                    </a>

                    <a href="<?php echo BASE_URL; ?>/admin/settings" style="color: #000; text-decoration: none;">
                        <i class="fas fa-sliders-h" style="color: #f06724; margin-right: 10px;"></i>Account Settings
                    </a>

                    <a href="<?php echo BASE_URL; ?>/admin/login-activity" style="color: #000; text-decoration: none;">
                        <i class="fas fa-history" style="color: #f06724; margin-right: 10px;"></i>Login Activity
                    </a>
                    <div class="divider"></div>
                    <a href="<?php echo BASE_URL; ?>/logout" class="logout-link"><i class="fas fa-sign-out-alt"></i>Sign Out</a>
                </div>
            </div>
        </div>
    </header>

    <aside class="admin-sidebar" id="sidebar">
        <div class="sidebar-scroll-container">
            
            <div class="menu-node-wrapper">
                <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="menu-anchor-item <?php echo strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-columns anchor-icon"></i>
                    <span class="anchor-label-text">ROGELE Dashboard</span>
                </a>
            </div>

            <div class="menu-group-label">User Management</div>
            <div class="menu-node-wrapper">
                <a href="<?php echo BASE_URL; ?>/admin/users" class="menu-anchor-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false && strpos($_SERVER['REQUEST_URI'], '/create') === false) ? 'active' : ''; ?>">
                    <i class="fas fa-users anchor-icon"></i>
                    <span class="anchor-label-text">Manage Users</span>
                </a>
            </div>
            <div class="menu-node-wrapper">
                <a href="<?php echo BASE_URL; ?>/admin/users/create" class="menu-anchor-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/users/create') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-user-plus anchor-icon"></i>
                    <span class="anchor-label-text">Add User</span>
                </a>
            </div>

            <div class="menu-group-label">Academics & More</div>
            <div class="menu-node-wrapper">
                <a href="<?php echo BASE_URL; ?>/admin/lessons" class="menu-anchor-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'lessons') !== false || strpos($_SERVER['REQUEST_URI'], 'view_lesson') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-book-open anchor-icon"></i>
                    <span class="anchor-label-text">Lessons</span>
                </a>
            </div>
            <div class="menu-node-wrapper">
                <a href="<?php echo BASE_URL; ?>/admin/quizzes" class="menu-anchor-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'quizzes') !== false || strpos($_SERVER['REQUEST_URI'], 'view_quiz') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-graduation-cap anchor-icon"></i>
                    <span class="anchor-label-text">Quizzes</span>
                </a>
            </div>
            <div class="menu-node-wrapper">
                <a href="<?php echo BASE_URL; ?>/admin/homework" class="menu-anchor-item <?php echo strpos($_SERVER['REQUEST_URI'], 'homework') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-scroll anchor-icon"></i>
                    <span class="anchor-label-text">Homework</span>
                </a>
            </div>

            <div class="menu-group-label">Billing Gateways</div>
            <div class="menu-node-wrapper <?php echo strpos($_SERVER['REQUEST_URI'], 'subscriptions') !== false ? 'expanded' : ''; ?>">
                <div class="menu-anchor-item submenu-toggle-trigger <?php echo strpos($_SERVER['REQUEST_URI'], 'subscriptions') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-credit-card anchor-icon"></i> 
                    <span class="anchor-label-text">Subscriptions</span>
                    <i class="fas fa-chevron-right carat-indicator"></i>
                </div>
                
                <ul class="submenu-node-box">
                    <li>
                        <a href="<?php echo BASE_URL; ?>/admin/subscriptions" class="submenu-anchor <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/subscriptions') !== false && strpos($_SERVER['REQUEST_URI'], '/reports') === false && strpos($_SERVER['REQUEST_URI'], '/plans') === false) ? 'active' : ''; ?>">
                            <i class="fas fa-list" style="font-size: 0.7rem;"></i> Subscribers
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/admin/subscriptions/reports" class="submenu-anchor <?php echo strpos($_SERVER['REQUEST_URI'], 'subscriptions/reports') !== false ? 'active' : ''; ?>">
                            <i class="fas fa-list" style="font-size: 0.7rem;"></i> Reports & Analytics
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/admin/subscriptions/plans" class="submenu-anchor <?php echo strpos($_SERVER['REQUEST_URI'], 'subscriptions/plans') !== false ? 'active' : ''; ?>">
                            <i class="fas fa-list" style="font-size: 0.7rem;"></i> Plan Settings
                        </a>
                    </li>
                </ul>
            </div>

            <div class="menu-group-label">Global Configuration</div>
            <div class="menu-node-wrapper">
                <a href="<?php echo BASE_URL; ?>/admin/reports" class="menu-anchor-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'reports') !== false && strpos($_SERVER['REQUEST_URI'], 'subscriptions') === false) ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line anchor-icon"></i>
                    <span class="anchor-label-text">Data Reports</span>
                </a>
            </div>
            <div class="menu-node-wrapper">
                <a href="<?php echo BASE_URL; ?>/admin/settings" class="menu-anchor-item <?php echo strpos($_SERVER['REQUEST_URI'], 'settings') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-sliders-h anchor-icon"></i>
                    <span class="anchor-label-text">System Settings</span>
                </a>
            </div>
        </div>
    </aside>

    <div class="admin-view-body">
        <div class="view-content-wrapper">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert-toast alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert-toast alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const profileTrigger = document.getElementById('profileTrigger');
                const profileDropdown = document.getElementById('profileDropdown');

                profileTrigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    profileDropdown.classList.toggle('show');
                });

                document.addEventListener('click', function() {
                    profileDropdown.classList.remove('show');
                });

                const menuToggleDesktop = document.getElementById('menuToggleDesktop');
                const menuToggleMobile = document.getElementById('menuToggleMobile');
                const sidebarBackdrop = document.getElementById('sidebarBackdrop');
                const bodyElement = document.body;

                if (menuToggleDesktop) {
                    menuToggleDesktop.addEventListener('click', function(e) {
                        e.stopPropagation();
                        bodyElement.classList.toggle('sidebar-collapsed');
                    });
                }

                if (menuToggleMobile) {
                    menuToggleMobile.addEventListener('click', function(e) {
                        e.stopPropagation();
                        bodyElement.classList.add('sidebar-open');
                    });
                }

                sidebarBackdrop.addEventListener('click', function() {
                    bodyElement.classList.remove('sidebar-open');
                });

                const submenuToggles = document.querySelectorAll('.submenu-toggle-trigger');
                submenuToggles.forEach(toggle => {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        const nodeWrapper = this.parentElement;
                        nodeWrapper.classList.toggle('expanded');
                    });
                });
            });
            </script>