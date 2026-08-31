<?php
// File: /views/layouts/admin_header.php
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$isActive = static function (string $route, bool $exact = false) use ($currentUri): string {
    $contains = strpos($currentUri, $route) !== false;
    if ($exact) {
        return ($contains && $currentUri === $route) ? 'active' : '';
    }
    return $contains ? 'active' : '';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') : SITE_NAME; ?></title>
    <base href="<?php echo BASE_URL; ?>/">
    
    <meta name="description" content="Rays of Grace E-Learning Environment - Quality education for Primary 1 to Primary 7 students. Interactive lessons, quizzes, and progress tracking.">
    <meta name="keywords" content="e-learning, primary education, online learning, uganda education, rays of grace, Hisaalu Nelson">
    <meta name="author" content="Hisaalu Nelson | Rays of Grace Junior School">
    
    <meta property="og:title" content="<?php echo SITE_NAME; ?>">
    <meta property="og:description" content="Quality education for every child, anywhere, anytime.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo BASE_URL; ?>">
    
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/public/images/logo.png">
    <link rel="shortcut icon" type="image/png" href="<?php echo BASE_URL; ?>/public/images/logo.png">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #5c1856;
            --primary-dark: #451041;
            --primary-light: #7f2677;
            --accent: #f06724;
            --accent-hover: #d8571a;
            
            --dark: #0f172a;
            --gray-900: #0f172a;
            --gray-700: #475569;
            --gray-500: #64748b;
            --gray-300: #cbd5e1;
            --gray-100: #f1f5f9;
            --bg-light: #f8fafc;
            --white: #ffffff;
            
            --sidebar-width: 280px;
            --sidebar-mini-width: 72px;
            --navbar-height: 70px;
            --transition-speed: 0.25s;
            --transition-curve: cubic-bezier(0.4, 0, 0.2, 1);
            
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -1px rgba(0, 0, 0, 0.04);
            --shadow-lg: 0 10px 25px -5px rgba(0, 0, 0, 0.12);
        }

        *, *::before, *::after {
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
            -moz-osx-font-smoothing: grayscale;
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        body.sidebar-collapsed .navbar-brand-block {
            width: var(--sidebar-mini-width);
            padding: 0 12px;
            justify-content: center;
        }

        .menu-toggle-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: var(--white);
            font-size: 0.95rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 8px;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .menu-toggle-btn:hover {
            background: rgba(255, 255, 255, 0.15);
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
            width: 36px;
            height: 36px;
            object-fit: contain;
            border-radius: 6px;
        }

        .brand-text-wrapper {
            display: flex;
            flex-direction: column;
        }

        .brand-title {
            font-size: 0.88rem;
            font-weight: 800;
            color: var(--white);
            letter-spacing: 0.5px;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .brand-subtitle {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 400;
        }

        .navbar-right-block {
            flex: 1;
            padding: 0 24px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            background: var(--white);
            min-width: 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .admin-top-navbar .header-actions {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 16px;
            width: auto;
        }

        .header-action-btn {
            background: transparent;
            border: none;
            color: var(--gray-700);
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            transition: background 0.2s, color 0.2s;
            flex-shrink: 0;
        }

        .header-action-btn:hover {
            background: var(--gray-100);
            color: var(--dark);
        }

        .profile-trigger {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 4px 10px 4px 4px;
            border-radius: 30px;
            border: 1px solid var(--gray-300);
            background: var(--white);
            position: relative;
            transition: all 0.2s ease;
            user-select: none;
            flex-shrink: 0;
        }

        .profile-trigger:hover {
            background: var(--bg-light);
            border-color: #cbd5e1;
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
            flex-shrink: 0;
        }

        .admin-meta {
            display: flex;
            flex-direction: column;
            text-align: left;
            padding-right: 2px;
        }

        .admin-meta span {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--dark);
            line-height: 1.2;
        }

        .admin-meta small {
            font-size: 0.68rem;
            color: var(--gray-500);
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
            transition: background 0.15s ease, color 0.15s ease;
        }

        .profile-dropdown-card a:hover {
            background: var(--bg-light);
            color: var(--accent);
        }

        .profile-dropdown-card a i {
            width: 16px;
            text-align: center;
            color: var(--accent);
            font-size: 0.9rem;
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

        .notification-dropdown-card {
            position: absolute;
            top: calc(100% + 12px);
            right: -10px;
            background: var(--white);
            width: 360px;
            max-width: calc(100vw - 32px);
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-300);
            display: none;
            flex-direction: column;
            z-index: 1100;
            overflow: hidden;
            animation: fadeIn 0.15s ease-out;
        }

        .notification-dropdown-card.show {
            display: flex;
        }

        .notification-header {
            padding: 12px 16px;
            border-bottom: 1px solid var(--gray-100);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafafa;
        }

        .notification-header h4 {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--dark);
        }

        .mark-read-btn {
            font-size: 0.72rem;
            color: var(--accent);
            cursor: pointer;
            background: none;
            border: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .mark-read-btn:hover {
            color: var(--accent-hover);
            text-decoration: underline;
        }

        .notification-list {
            max-height: 350px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .notification-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 16px;
            text-decoration: none;
            border-bottom: 1px solid var(--gray-100);
            transition: background 0.15s ease;
        }

        .notification-item:hover {
            background: var(--bg-light);
        }

        .notification-item.unread {
            background: #fcf4f0;
        }

        .notification-icon-box {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .icon-user { background: #e0f2fe; color: #0284c7; }
        .icon-subscriber { background: #dcfce7; color: #15803d; }
        .icon-homework { background: #fef3c7; color: #d97706; }
        .icon-quiz { background: #f3e8ff; color: #7e22ce; }
        .icon-lesson { background: #ffe4e6; color: #e11d48; }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-content p {
            font-size: 0.8rem;
            color: var(--gray-900);
            line-height: 1.35;
            margin-bottom: 3px;
            word-wrap: break-word;
        }

        .notification-content small {
            font-size: 0.68rem;
            color: var(--gray-500);
        }

        .notification-badge-count {
            position: absolute;
            top: 2px;
            right: 2px;
            background: var(--accent);
            color: var(--white);
            font-size: 0.65rem;
            font-weight: 700;
            border-radius: 10px;
            padding: 1px 5px;
            min-width: 16px;
            text-align: center;
        }

        .admin-sidebar {
            width: var(--sidebar-width);
            background: var(--primary);
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
            padding: 16px 12px;
            overflow-y: auto;
            -ms-overflow-style: none; 
            scrollbar-width: none; 
        }

        .sidebar-scroll-container::-webkit-scrollbar {
            display: none;
        }

        .menu-group-label {
            font-size: 0.68rem;
            text-transform: uppercase;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.8px;
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
            padding: 12px 14px;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-weight: 300;
            font-size: 0.88rem;
            border-radius: 10px;
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
            background: rgba(255, 255, 255, 0.1);
        }

        .menu-anchor-item.active {
            background: var(--accent);
            color: var(--white);
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(240, 103, 36, 0.35);
        }

        .menu-anchor-item i.anchor-icon {
            width: 22px;
            font-size: 1.05rem;
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
            font-size: 0.75rem;
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
            inset: 0;
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
                width: 100%;
                background: var(--primary);
                padding: 0 16px;
                justify-content: flex-start;
            }

            .navbar-right-block {
                position: absolute;
                right: 0;
                height: 100%;
                background: transparent;
                border: none;
            }

            .header-action-btn {
                color: var(--white);
            }

            .header-action-btn:hover {
                background: rgba(255, 255, 255, 0.15);
                color: var(--white);
            }
            
            .admin-meta, 
            .profile-trigger span, 
            .profile-trigger small, 
            .profile-trigger .fa-chevron-down {
                display: none;
            }
            
            .profile-trigger {
                border: none;
                background: transparent;
                padding: 0;
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
            .admin-sidebar .carat-indicator,
            .admin-sidebar .menu-group-label {
                display: inline-block !important;
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
        }

        @media (max-width: 576px) {
            .brand-title {
                font-size: 0.8rem;
            }

            .brand-subtitle {
                font-size: 0.65rem;
            }

            .notification-dropdown-card {
                position: fixed;
                top: calc(var(--navbar-height) + 6px);
                left: 12px;
                right: 12px;
                width: auto;
                max-width: none;
                box-shadow: var(--shadow-lg);
            }

            .profile-dropdown-card {
                right: 16px;
                top: calc(var(--navbar-height) + 6px);
            }

            .notification-list {
                max-height: 60vh;
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
            <div class="header-actions">
                <div style="position: relative;">
                    <button class="header-action-btn" id="notifTrigger" title="Notifications" aria-label="View notifications">
                        <i class="far fa-bell"></i>
                        <span class="notification-badge-count" id="notifBadge" style="display: none;">0</span>
                    </button>

                    <div class="notification-dropdown-card" id="notifDropdown">
                        <div class="notification-header">
                            <h4>Notifications</h4>
                            <button class="mark-read-btn" id="markAllReadBtn">Mark all as read</button>
                        </div>
                        <div class="notification-list" id="notifList">
                            <div style="padding: 20px; text-align: center; font-size: 0.8rem; color: #888;">Loading notifications...</div>
                        </div>
                    </div>
                </div>

                <div class="profile-trigger" id="profileTrigger">
                    <div class="admin-avatar">
                        <?php 
                        $userName = $_SESSION['user_name'] ?? 'Admin';
                        $nameParts = explode(' ', trim($userName));
                        $initials = '';
                        foreach ($nameParts as $part) {
                            if ($part !== '') $initials .= strtoupper(substr($part, 0, 1));
                        }
                        echo htmlspecialchars(substr($initials, 0, 2), ENT_QUOTES, 'UTF-8');
                        ?>
                    </div>
                    <div class="admin-meta">
                        <span><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></span>
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
                        <a href="<?php echo BASE_URL; ?>/admin/reports">
                            <i class="fas fa-history"></i> User Activity
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
                <a href="<?php echo BASE_URL; ?>/admin/classes-subjects" class="menu-anchor-item <?php echo $isActive('classes-subjects'); ?>">
                    <i class="fas fa-layer-group anchor-icon"></i>
                    <span class="anchor-label-text">Classes & Subjects</span>
                </a>
            </div>
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
                    <i class="fas fa-list-check anchor-icon"></i>
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

            <div class="menu-group-label">Accounts</div>
            <div class="menu-node-wrapper">
                <a href="<?php echo BASE_URL; ?>/logout" class="menu-anchor-item">
                    <i class="fas fa-sign-out-alt anchor-icon"></i>
                    <span class="anchor-label-text">Log Out</span>
                </a>
            </div>
        </div>
    </aside>

    <div class="admin-view-body">
        <div class="view-content-wrapper">
            <div class="toast-container">
                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert-toast alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert-toast alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', () => {
                const BASE_URL = '<?php echo BASE_URL; ?>';

                if (localStorage.getItem('admin_sidebar_collapsed') === 'true' && window.innerWidth > 992) {
                    document.body.classList.add('sidebar-collapsed');
                }

                const profileTrigger = document.getElementById('profileTrigger');
                const profileDropdown = document.getElementById('profileDropdown');
                const notifTrigger = document.getElementById('notifTrigger');
                const notifDropdown = document.getElementById('notifDropdown');
                const menuToggleDesktop = document.getElementById('menuToggleDesktop');
                const sidebarBackdrop = document.getElementById('sidebarBackdrop');

                if (profileTrigger && profileDropdown) {
                    profileTrigger.addEventListener('click', (e) => {
                        e.stopPropagation();
                        if (notifDropdown) notifDropdown.classList.remove('show');
                        profileDropdown.classList.toggle('show');
                    });
                }

                if (menuToggleDesktop) {
                    menuToggleDesktop.addEventListener('click', (e) => {
                        e.stopPropagation();
                        if (window.innerWidth <= 992) {
                            document.body.classList.toggle('sidebar-open');
                        } else {
                            document.body.classList.toggle('sidebar-collapsed');
                            localStorage.setItem('admin_sidebar_collapsed', document.body.classList.contains('sidebar-collapsed'));
                        }
                    });
                }

                if (sidebarBackdrop) {
                    sidebarBackdrop.addEventListener('click', () => {
                        document.body.classList.remove('sidebar-open');
                    });
                }

                document.querySelectorAll('.submenu-toggle-trigger').forEach(toggle => {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (document.body.classList.contains('sidebar-collapsed')) {
                            document.body.classList.remove('sidebar-collapsed');
                            localStorage.setItem('admin_sidebar_collapsed', 'false');
                        }
                        this.parentElement.classList.toggle('expanded');
                    });
                });

                document.addEventListener('click', (e) => {
                    if (profileDropdown && !profileDropdown.contains(e.target) && !profileTrigger.contains(e.target)) {
                        profileDropdown.classList.remove('show');
                    }
                    if (notifDropdown && !notifDropdown.contains(e.target) && !notifTrigger.contains(e.target)) {
                        notifDropdown.classList.remove('show');
                    }
                });

                const notifBadge = document.getElementById('notifBadge');
                const notifList = document.getElementById('notifList');
                const markAllReadBtn = document.getElementById('markAllReadBtn');

                const getIconMeta = (type) => {
                    const icons = {
                        user: { icon: 'fas fa-user-plus', class: 'icon-user' },
                        subscriber: { icon: 'fas fa-credit-card', class: 'icon-subscriber' },
                        homework: { icon: 'fas fa-tasks', class: 'icon-homework' },
                        quiz: { icon: 'fas fa-graduation-cap', class: 'icon-quiz' },
                        lesson: { icon: 'fas fa-book-open', class: 'icon-lesson' }
                    };
                    return icons[type] || { icon: 'fas fa-bell', class: 'icon-user' };
                };

                const fetchNotifications = async () => {
                    try {
                        const res = await fetch(`${BASE_URL}/admin/api/notifications`);
                        const data = await res.json();

                        if (data.status === 'success') {
                            if (data.unread_count > 0) {
                                notifBadge.textContent = data.unread_count;
                                notifBadge.style.display = 'block';
                            } else {
                                notifBadge.style.display = 'none';
                            }

                            if (!data.notifications || data.notifications.length === 0) {
                                notifList.innerHTML = '<div style="padding: 20px; text-align: center; font-size: 0.8rem; color: #888;">No notifications yet.</div>';
                                return;
                            }

                            notifList.innerHTML = data.notifications.map(item => {
                                const meta = getIconMeta(item.type);
                                const unreadClass = item.is_read == 0 ? 'unread' : '';
                                return `
                                    <a href="${item.link || '#'}" 
                                       class="notification-item ${unreadClass}" 
                                       data-id="${item.id}" 
                                       data-unread="${item.is_read == 0}">
                                        <div class="notification-icon-box ${meta.class}">
                                            <i class="${meta.icon}"></i>
                                        </div>
                                        <div class="notification-content">
                                            <p>${item.message}</p>
                                            <small>${new Date(item.created_at).toLocaleString()}</small>
                                        </div>
                                    </a>
                                `;
                            }).join('');
                        }
                    } catch (err) {
                        console.error('Failed to update notifications:', err);
                    }
                };

                if (notifTrigger && notifDropdown) {
                    notifTrigger.addEventListener('click', (e) => {
                        e.stopPropagation();
                        if (profileDropdown) profileDropdown.classList.remove('show');
                        notifDropdown.classList.toggle('show');
                        fetchNotifications();
                    });
                }

                if (markAllReadBtn) {
                    markAllReadBtn.addEventListener('click', async () => {
                        try {
                            await fetch(`${BASE_URL}/admin/api/notifications/read`, { method: 'POST' });
                            fetchNotifications();
                        } catch (err) {
                            console.error('Failed marking notifications read:', err);
                        }
                    });
                }

                fetchNotifications();
                
                if (notifList) {
                    notifList.addEventListener('click', async (e) => {
                        const item = e.target.closest('.notification-item');
                        if (!item) return;

                        const notifId = item.getAttribute('data-id');
                        const isUnread = item.getAttribute('data-unread') === 'true';

                        if (isUnread && notifId) {
                            e.preventDefault();
                            const targetUrl = item.getAttribute('href');

                            try {
                                await fetch(`${BASE_URL}/admin/api/notifications/read`, {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ id: notifId })
                                });
                            } catch (err) {
                                console.error('Failed to mark notification as read:', err);
                            } finally {
                                if (targetUrl && targetUrl !== '#') {
                                    window.location.href = targetUrl;
                                }
                            }
                        }
                    });
                }

                setInterval(fetchNotifications, 30000);
            });
            </script>