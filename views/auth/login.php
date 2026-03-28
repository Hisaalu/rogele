<!-- File: /views/auth/login.php -->
<?php
$hideHeader = true; 
$pageTitle = 'Login | ROGELE';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/public/images/logo.png">
    <link rel="shortcut icon" type="image/png" href="<?php echo BASE_URL; ?>/public/images/logo.png">

    <!-- Font Awesome 6 (required for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
    /* Login Page Styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: #f5f5f5;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    }

    .login-page {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        background: #f5f5f5;
    }

    .login-container {
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }

    /* Login Card */
    .login-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        padding: 40px 32px;
        width: 100%;
    }

    /* Logo Section */
    .logo-section {
        text-align: center;
        margin-bottom: 32px;
    }

    .logo {
        width: 100px;
        height: 100px;
        margin: 0 auto 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: none;
    }

    .logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .logo-section h1 {
        font-size: 1.5rem;
        font-weight: 600;
        color: black;
        margin: 0;
    }

    .logo-section p {
        font-size: 0.85rem;
        color: black;
        margin-top: 4px;
    }

    /* Form */
    .login-form {
        width: 100%;
    }

    .form-group {
        margin-bottom: 20px;
        position: relative;
    }

    .form-group input {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        background: white;
    }

    .form-group input:focus {
        outline: none;
        border-color: #f06724;
        box-shadow: 0 0 0 2px rgba(240, 103, 36, 0.1);
    }

    .form-group input::placeholder {
        color: #999;
    }

    /* Password Field with Toggle Button */
    .password-field {
        position: relative;
        width: 100%;
    }

    .password-field input {
        padding-right: 45px;
    }

    .toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #999;
        font-size: 1.1rem;
        padding: 8px;
        transition: color 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }

    .toggle-password:hover {
        color: #f06724;
    }

    .toggle-password:focus {
        outline: none;
    }

    /* Login Button */
    .btn-login {
        width: 100%;
        padding: 12px;
        background: #f06724;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s ease;
        margin-top: 8px;
    }

    .btn-login:hover {
        background: #e05a1a;
    }

    /* Form Options */
    .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        font-size: 0.85rem;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        color: black;
    }

    .checkbox-label input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .forgot-link {
        color: #7f2677;
        text-decoration: none;
        font-size: 0.85rem;
    }

    .forgot-link:hover {
        text-decoration: underline;
    }

    /* Register Link */
    .register-link {
        text-align: center;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid #eee;
        font-size: 0.85rem;
        color: black;
    }

    .register-link a {
        color: #7f2677;
        text-decoration: none;
        font-weight: 500;
    }

    .register-link a:hover {
        text-decoration: underline;
    }

    /* Alert Messages */
    .alert {
        margin-bottom: 20px;
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .alert-error {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .alert-success {
        background: #e6f4ea;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
    }

    .alert i {
        font-size: 1rem;
    }

    /* Loading State */
    .btn-login.loading {
        position: relative;
        color: transparent;
        pointer-events: none;
    }

    .btn-login.loading::after {
        content: '';
        position: absolute;
        width: 18px;
        height: 18px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border: 2px solid white;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 0.6s linear infinite;
    }

    @keyframes spin {
        to { transform: translate(-50%, -50%) rotate(360deg); }
    }

    /* Responsive */
    @media (max-width: 480px) {
        .login-card {
            padding: 32px 24px;
        }
        
        .logo {
            width: 55px;
            height: 55px;
        }
        
        .logo-section h1 {
            font-size: 1.3rem;
        }
        
        .form-group input {
            padding: 10px 14px;
        }
        
        .btn-login {
            padding: 10px;
        }
    }
    </style>
</head>
<body>
<div class="login-page">
    <div class="login-container">
        <div class="login-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo" style="background: none; box-shadow: none;">
                    <?php 
                    $logoPath = BASE_URL . '/public/images/logo.png';
                    $logoFile = __DIR__ . '/../../public/images/logo.png';
                    ?>
                    <?php if (file_exists($logoFile)): ?>
                        <img src="<?php echo $logoPath; ?>" alt="ROGELE Logo" style="width: 100px; height: 100px; object-fit: contain;">
                    <?php else: ?>
                        <span style="font-size: 3rem; font-weight: 700; color: #f06724;">RG</span>
                    <?php endif; ?>
                </div>
                <h1>Login to ROGELE</h1>
                <p>Rays of Grace E-Learning</p>
            </div>
            
            <!-- Alert Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form action="<?php echo BASE_URL; ?>/login" method="POST" class="login-form" id="loginForm">
                <div class="form-group">
                    <input 
                        type="text" 
                        name="username" 
                        id="username"
                        placeholder="Email address"
                        required
                        autocomplete="username"
                    >
                </div>
                
                <div class="form-group">
                    <div class="password-field">
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            placeholder="Password"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-password" id="togglePassword" aria-label="Show password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="<?php echo BASE_URL; ?>/forgot-password" class="forgot-link">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn-login" id="loginButton">
                    Log in
                </button>
                
                <div class="register-link">
                    <span>Don't have an account?</span>
                    <a href="<?php echo BASE_URL; ?>/register">Create account</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loginButton = document.getElementById('loginButton');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    
    // Auto-focus on email field
    if (usernameInput) {
        usernameInput.focus();
    }
    
    // Toggle password visibility
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            // Toggle the type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle the eye icon
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    }
    
    // Form submission
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = usernameInput.value.trim();
            const password = passwordInput.value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                showAlert('Please enter your email and password', 'error');
                return;
            }
            
            // Show loading state
            loginButton.classList.add('loading');
            loginButton.textContent = '';
        });
    }
    
    // Show alert function
    function showAlert(message, type) {
        const existingAlert = document.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;
        
        const loginCard = document.querySelector('.login-card');
        const logoSection = document.querySelector('.logo-section');
        
        if (loginCard && logoSection) {
            loginCard.insertBefore(alertDiv, logoSection.nextSibling);
        }
        
        setTimeout(() => {
            alertDiv.style.opacity = '0';
            setTimeout(() => alertDiv.remove(), 300);
        }, 5000);
    }
});
</script>
</body>
</html>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>