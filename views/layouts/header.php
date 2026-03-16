<?php
// File: /views/layouts/header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title><?php echo isset($pageTitle) ? $pageTitle : SITE_NAME; ?></title>
    <base href="<?php echo BASE_URL; ?>/">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Rays of Grace E-Learning Platform - Quality education for Primary 1 to Primary 7 students. Interactive lessons, quizzes, and progress tracking.">
    <meta name="keywords" content="e-learning, primary education, online learning, uganda education, rays of grace">
    <meta name="author" content="Rays of Grace Junior School">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo SITE_NAME; ?>">
    <meta property="og:description" content="Quality education for every child, anywhere, anytime.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo BASE_URL; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='45' fill='%238B5CF6'/%3E%3Ctext x='50' y='70' font-size='60' text-anchor='middle' fill='white' font-weight='bold'%3ERG%3C/text%3E%3C/svg%3E">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            width: 100%;
            position: relative;
            background: #F8FAFC;
        }

        /* Header Styles */
        .site-header {
            background: white;
            box-shadow: 0 2px 20px rgba(139, 92, 246, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            width: 100%;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
        }

        /* Logo */
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            flex-shrink: 0;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #8B5CF6, #F97316);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            transform: rotate(-5deg);
            transition: transform 0.3s ease;
        }

        .logo:hover .logo-icon {
            transform: rotate(0deg);
        }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo-main {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1E293B;
            line-height: 1.2;
        }

        .logo-sub {
            font-size: 0.75rem;
            color: #64748B;
        }

        /* Desktop Navigation */
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 40px;
            flex: 1;
            justify-content: flex-end;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 5px;
            margin: 0;
            padding: 0;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            color: #1E293B;
            text-decoration: none;
            font-weight: 500;
            border-radius: 50px;
            transition: all 0.3s ease;
            font-size: 1rem;
            white-space: nowrap;
        }

        .nav-links a i {
            color: #8B5CF6;
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .nav-links a:hover {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(249, 115, 22, 0.1));
            color: #8B5CF6;
        }

        .nav-links a:hover i {
            transform: translateY(-2px);
            color: #F97316;
        }

        /* Auth Buttons */
        .nav-auth {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: 20px;
        }

        .btn-login {
            padding: 10px 28px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            color: #8B5CF6;
            border: 2px solid #8B5CF6;
            background: transparent;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
            line-height: 1;
            height: 45px;
        }

        .btn-login:hover {
            background: #8B5CF6;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 92, 246, 0.3);
        }

        .btn-register {
            padding: 10px 28px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            background: linear-gradient(135deg, #8B5CF6, #F97316);
            color: white;
            border: none;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
            line-height: 1;
            height: 45px;
            box-shadow: 0 4px 10px rgba(139, 92, 246, 0.2);
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(249, 115, 22, 0.3);
        }

        /* ===== FIXED USER DROPDOWN MENU - MATCHING YOUR HTML STRUCTURE ===== */
        .user-menu {
            position: relative;
            display: inline-block;
            margin-left: 20px;
        }

        .user-dropdown {
            position: relative;
            display: inline-block;
        }

        .user-initials {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #8B5CF6, #F97316);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .user-initials:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(139, 92, 246, 0.3);
            border-color: white;
        }

        /* Dropdown Content - Hidden by default */
        .dropdown-content {
            position: absolute;
            right: 0;
            top: 55px;
            background: white;
            min-width: 260px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            z-index: 1000;
            overflow: hidden;
            border: 1px solid #E2E8F0;
            display: none; /* Hidden by default */
        }

        /* Show dropdown when hovering over user-dropdown */
        .user-dropdown:hover .dropdown-content {
            display: block; /* Show on hover */
        }

        /* Keep dropdown visible when hovering over the dropdown itself */
        .dropdown-content:hover {
            display: block;
        }

        /* Optional: Add a small triangle/arrow */
        .dropdown-content::before {
            content: '';
            position: absolute;
            top: -8px;
            right: 20px;
            width: 16px;
            height: 16px;
            background: white;
            transform: rotate(45deg);
            border-left: 1px solid #E2E8F0;
            border-top: 1px solid #E2E8F0;
            z-index: -1;
        }

        .dropdown-header {
            padding: 15px 20px;
            background: linear-gradient(135deg, #F8FAFC, #FFFFFF);
            border-bottom: 1px solid #E2E8F0;
        }

        .dropdown-header p {
            font-weight: 600;
            color: #1E293B;
            margin-bottom: 3px;
        }

        .dropdown-header small {
            color: #64748B;
            font-size: 0.8rem;
        }

        .dropdown-divider {
            height: 1px;
            background: #E2E8F0;
            margin: 5px 0;
        }

        .dropdown-content a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            color: #1E293B;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .dropdown-content a i {
            width: 20px;
            color: #8B5CF6;
            font-size: 1.1rem;
        }

        .dropdown-content a:hover {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(249, 115, 22, 0.1));
            padding-left: 25px;
        }

        .dropdown-content a.logout {
            color: #EF4444;
        }

        .dropdown-content a.logout i {
            color: #EF4444;
        }

        /* Mobile Menu Button */
        .mobile-toggle {
            display: none;
            flex-direction: column;
            justify-content: space-between;
            width: 30px;
            height: 21px;
            cursor: pointer;
            z-index: 1002;
            margin-left: 15px;
        }

        .mobile-toggle span {
            display: block;
            width: 100%;
            height: 3px;
            background: linear-gradient(135deg, #8B5CF6, #F97316);
            border-radius: 3px;
            transition: all 0.3s ease;
        }

        .mobile-toggle.active span:nth-child(1) {
            transform: translateY(9px) rotate(45deg);
        }

        .mobile-toggle.active span:nth-child(2) {
            opacity: 0;
            transform: scale(0);
        }

        .mobile-toggle.active span:nth-child(3) {
            transform: translateY(-9px) rotate(-45deg);
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .nav-menu {
                gap: 20px;
            }
            
            .nav-links a {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
            
            .btn-login, .btn-register {
                padding: 8px 20px;
                font-size: 0.9rem;
                height: 40px;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                height: 70px;
            }
            
            .mobile-toggle {
                display: flex;
            }

            .nav-menu {
                display: none;
            }

            body.menu-open {
                overflow: hidden;
            }
            
            .logo-main {
                font-size: 1.1rem;
            }
            
            .logo-sub {
                display: none;
            }
            
            .logo-icon {
                width: 40px;
                height: 40px;
                font-size: 1.3rem;
            }
        }

        /* Mobile Menu Styles */
        .mobile-menu {
            position: fixed;
            top: 0;
            right: -100%;
            width: 85%;
            max-width: 380px;
            height: 100vh;
            background: white;
            z-index: 1001;
            transition: right 0.3s ease;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .mobile-menu.active {
            right: 0;
        }

        .mobile-menu-header {
            padding: 30px 20px;
            background: linear-gradient(135deg, #8B5CF6, #F97316);
            color: white;
            position: relative;
        }

        .mobile-close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .mobile-close-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .mobile-close-btn i {
            color: white;
            font-size: 1.2rem;
        }

        .mobile-user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 20px;
        }

        .mobile-user-initials {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            font-weight: 600;
            border: 3px solid white;
        }

        .mobile-user-details h3 {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .mobile-user-details p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .mobile-menu-content {
            flex: 1;
            padding: 20px;
        }

        .mobile-nav-links {
            list-style: none;
        }

        .mobile-nav-links li {
            margin-bottom: 5px;
        }

        .mobile-nav-links a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            color: #1E293B;
            text-decoration: none;
            font-weight: 500;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .mobile-nav-links a i {
            width: 24px;
            color: #8B5CF6;
            font-size: 1.2rem;
        }

        .mobile-nav-links a:hover {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(249, 115, 22, 0.1));
            transform: translateX(5px);
        }

        .mobile-nav-links a.logout {
            color: #EF4444;
            margin-top: 20px;
            border-top: 1px solid #E2E8F0;
            padding-top: 20px;
        }

        .mobile-nav-links a.logout i {
            color: #EF4444;
        }

        .mobile-auth {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 30px;
        }

        .mobile-login, .mobile-register {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .mobile-login {
            color: #8B5CF6;
            border: 2px solid #8B5CF6;
            background: transparent;
        }

        .mobile-register {
            background: linear-gradient(135deg, #8B5CF6, #F97316);
            color: white;
            border: none;
        }

        .mobile-menu-footer {
            padding: 20px;
            border-top: 1px solid #E2E8F0;
            text-align: center;
        }

        .mobile-copyright {
            font-size: 0.8rem;
            color: #94A3B8;
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
            backdrop-filter: blur(3px);
        }

        .mobile-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Alert Messages */
        .alert {
            max-width: 1200px;
            margin: 20px auto;
            padding: 15px 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease;
        }

        .alert-success {
            background: #F0FDF4;
            color: #166534;
            border: 1px solid #BBF7D0;
        }

        .alert-error {
            background: #FEF2F2;
            color: #B91C1C;
            border: 1px solid #FECACA;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .main-content {
            min-height: calc(100vh - 80px);
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <nav class="navbar">
                <!-- Logo -->
                <a href="<?php echo BASE_URL; ?>/" class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="logo-text">
                        <span class="logo-main">Rays of Grace</span>
                        <span class="logo-sub">E-Learning Platform</span>
                    </div>
                </a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Desktop Navigation for Logged-in Users -->
                    <div class="nav-menu">
                        <ul class="nav-links">
                            <li><a href="<?php echo BASE_URL; ?>/" class="<?php echo basename($_SERVER['REQUEST_URI']) == '' ? 'active' : ''; ?>">
                                <i class="fas fa-home"></i> Home
                            </a></li>
                            <li><a href="/rays-of-grace/<?php echo $_SESSION['user_role']; ?>/dashboard" class="<?php echo strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false ? 'active' : ''; ?>">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a></li>
                            <li><a href="/rays-of-grace/<?php echo $_SESSION['user_role']; ?>/lessons" class="<?php echo strpos($_SERVER['REQUEST_URI'], 'lessons') !== false ? 'active' : ''; ?>">
                                <i class="fas fa-book-open"></i> Lessons
                            </a></li>
                        </ul>
                        
                        <!-- ===== FIXED USER MENU WITH DROPDOWN - MATCHING YOUR HTML ===== -->
                        <div class="user-menu">
                            <div class="user-dropdown">
                                <div class="user-initials">
                                    <?php 
                                    // Get user initials
                                    $nameParts = explode(' ', $_SESSION['user_name'] ?? 'User');
                                    $initials = '';
                                    foreach ($nameParts as $part) {
                                        if (!empty($part)) {
                                            $initials .= strtoupper(substr($part, 0, 1));
                                        }
                                    }
                                    echo substr($initials, 0, 2);
                                    ?>
                                </div>
                                <div class="dropdown-content">
                                    <div class="dropdown-header">
                                        <p><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></p>
                                        <small><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></small>
                                    </div>
                                    <a href="/rays-of-grace/<?php echo $_SESSION['user_role']; ?>/profile">
                                        <i class="fas fa-user"></i> My Profile
                                    </a>
                                    <a href="/rays-of-grace/<?php echo $_SESSION['user_role']; ?>/settings">
                                        <i class="fas fa-cog"></i> Settings
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a href="<?php echo BASE_URL; ?>/logout" class="logout">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Desktop Navigation for Guests -->
                    <div class="nav-menu">
                        <ul class="nav-links">
                            <li><a href="<?php echo BASE_URL; ?>/"><i class="fas fa-home"></i> Home</a></li>
                            <li><a href="#about"><i class="fas fa-info-circle"></i> About</a></li>
                            <li><a href="#contact"><i class="fas fa-envelope"></i> Contact</a></li>
                        </ul>
                        
                        <div class="nav-auth">
                            <a href="<?php echo BASE_URL; ?>/login" class="btn-login">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                            <a href="<?php echo BASE_URL; ?>/register" class="btn-register">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Mobile Menu Toggle Button -->
                <div class="mobile-toggle" id="mobileToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </nav>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Mobile Menu for Logged-in Users -->
            <div class="mobile-menu-header">
                <div class="mobile-close-btn" id="mobileClose">
                    <i class="fas fa-times"></i>
                </div>
                <div class="mobile-user-info">
                    <div class="mobile-user-initials">
                        <?php 
                        $nameParts = explode(' ', $_SESSION['user_name'] ?? 'User');
                        $initials = '';
                        foreach ($nameParts as $part) {
                            if (!empty($part)) {
                                $initials .= strtoupper(substr($part, 0, 1));
                            }
                        }
                        echo substr($initials, 0, 2);
                        ?>
                    </div>
                    <div class="mobile-user-details">
                        <h3><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></h3>
                        <p><?php echo ucfirst($_SESSION['user_role'] ?? 'user'); ?></p>
                    </div>
                </div>
            </div>
            <div class="mobile-menu-content">
                <ul class="mobile-nav-links">
                    <li><a href="<?php echo BASE_URL; ?>/"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="/rays-of-grace/<?php echo $_SESSION['user_role']; ?>/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="/rays-of-grace/<?php echo $_SESSION['user_role']; ?>/lessons"><i class="fas fa-book-open"></i> Lessons</a></li>
                    <li><a href="/rays-of-grace/<?php echo $_SESSION['user_role']; ?>/quizzes"><i class="fas fa-pencil-alt"></i> Quizzes</a></li>
                    <li><a href="/rays-of-grace/<?php echo $_SESSION['user_role']; ?>/profile"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="/rays-of-grace/<?php echo $_SESSION['user_role']; ?>/settings"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/logout" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        <?php else: ?>
            <!-- Mobile Menu for Guests -->
            <div class="mobile-menu-header">
                <div class="mobile-close-btn" id="mobileClose">
                    <i class="fas fa-times"></i>
                </div>
                <div style="text-align: center; padding: 10px 0;">
                    <i class="fas fa-graduation-cap" style="font-size: 3rem; color: white; margin-bottom: 10px;"></i>
                    <h3 style="color: white;">Welcome to</h3>
                    <h2 style="color: white;">Rays of Grace</h2>
                </div>
            </div>
            <div class="mobile-menu-content">
                <ul class="mobile-nav-links">
                    <li><a href="<?php echo BASE_URL; ?>/"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="#about"><i class="fas fa-info-circle"></i> About</a></li>
                    <li><a href="#contact"><i class="fas fa-envelope"></i> Contact</a></li>
                </ul>
                <div class="mobile-auth">
                    <a href="<?php echo BASE_URL; ?>/login" class="mobile-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="<?php echo BASE_URL; ?>/register" class="mobile-register">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="mobile-menu-footer">
            <p class="mobile-copyright">© <?php echo date('Y'); ?> Rays of Grace</p>
        </div>
    </div>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <main class="main-content">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>