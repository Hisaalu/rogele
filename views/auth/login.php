<?php
//File: /views/auth/login.php
$hideHeader = true; 
$pageTitle = 'Login | ROGELE';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title> 
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/public/images/logo.png">
    <link rel="shortcut icon" type="image/png" href="<?php echo BASE_URL; ?>/public/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: #f5f5f5;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    }

    .alert-container {
        position: fixed;
        top: 24px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        width: 100%;
        max-width: 360px;
        padding: 0 20px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
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

    .login-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        padding: 40px 32px;
        width: 100%;
    }

    .logo-section {
        text-align: center;
        margin-bottom: 32px;
    }

    .logo {
        width: 100px;
        height: auto;
        margin: 0 auto 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: none;
    }

    .logo img {
        width: 100%;
        height: auto;
        object-fit: cover;
    }

    .logo-section h1 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #000;
        margin: 0;
    }

    .logo-section p {
        font-size: 0.85rem;
        color: #555;
        margin-top: 4px;
    }

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
        box-shadow: 0 0 0 2px rgba(240, 103, 36, 0.25);
    }

    .form-group input::placeholder {
        color: #999;
    }

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

    .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        font-size: 0.85rem;
    }

    .forgot-link {
        color: #7f2677;
        text-decoration: none;
        font-size: 0.85rem;
    }

    .forgot-link:hover {
        text-decoration: underline;
    }

    .register-link {
        text-align: center;
        font-size: 0.85rem;
        color: #000;
    }

    .register-link a {
        color: #7f2677;
        text-decoration: none;
        font-weight: 500;
    }

    .register-link a:hover {
        text-decoration: underline;
    }

    /* Updated Toast Alert Styling */
    .alert {
        pointer-events: auto;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12), 0 1px 3px rgba(0, 0, 0, 0.08);
        transition: opacity 0.3s ease;
        width: 100%;
    }

    .alert-error {
        background: #fee2e2;
        color: #dc2626;
        border-left: 4px solid #dc2626;
    }

    .alert-success {
        background: #e6f4ea;
        color: #2e7d32;
        border-left: 4px solid #2e7d32;
    }

    .alert-warning {
        background: #fff3cd;
        color: #dc2626;
        border-left: 4px solid #dc2626;
    }

    .alert i {
        font-size: 1.05rem;
    }

    .btn-login.loading::before {
        content: '';
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: modern-spin 0.55s cubic-bezier(0.45, 0.05, 0.55, 0.95) infinite;
    }

    .btn-login.loading {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: #d45216;
        pointer-events: none;
        cursor: not-allowed;
    }

    @keyframes modern-spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
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

<!-- Consolidated Notification Area at the top of the viewport -->
<div class="alert-container" id="globalAlertContainer">
    <div id="timeoutAlertContainer"></div>

    <?php if (isset($_SESSION['warning'])): ?>
        <div class="alert alert-warning">
            <i class="fas fa-clock"></i>
            <span><?php echo $_SESSION['warning']; ?></span>
        </div>
        <?php unset($_SESSION['warning']); ?>
    <?php endif; ?>

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
</div>

<div class="login-page">
    <div class="login-container">
            <div class="logo" style="background: none; box-shadow: none;">
                <?php 
                $logoPath = BASE_URL . '/public/images/logo.png';
                $logoFile = __DIR__ . '/../../public/images/logo.png';
                ?>
                <?php if (file_exists($logoFile)): ?>
                    <img src="<?php echo $logoPath; ?>" alt="ROGELE Logo">
                <?php else: ?>
                    <span style="font-size: 3rem; font-weight: 700; color: #f06724;">RG</span>
                <?php endif; ?>
            </div>
            <div class="logo-section">
                <h1>Login to ROGELE</h1>
                <p>Rays of Grace E-Learning</p>
            </div>
        <div class="login-card">
            
            <form action="<?php echo BASE_URL; ?>/login" method="POST" class="login-form" id="loginForm">
                <div class="form-group">
                    <input 
                        type="text" 
                        name="username" 
                        id="username"
                        placeholder="Your Email address"
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
                            placeholder="Your Password"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-password" id="togglePassword" aria-label="Show password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <a href="<?php echo BASE_URL; ?>/forgot-password" class="forgot-link">Forgot password?</a>
                    <div class="register-link">
                        <a href="<?php echo BASE_URL; ?>/register">Create account</a>
                    </div>
                </div>
                
                <button type="submit" class="btn-login" id="loginButton">
                    Log in
                </button>
                
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('showTimeoutAlert') === '1') {
        const timeoutContainer = document.getElementById('timeoutAlertContainer');
        if (timeoutContainer) {
            timeoutContainer.innerHTML = `
                <div class="alert alert-warning" style="display: flex !important; visibility: visible !important; opacity: 1 !important;">
                    <i class="fas fa-clock"></i>
                    <span>Session Expired. Please log in again!</span>
                </div>
            `;
        }
        localStorage.removeItem('showTimeoutAlert');
    }

    const loginForm = document.getElementById('loginForm');
    const loginButton = document.getElementById('loginButton');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    
    if (usernameInput) {
        usernameInput.focus();
    }
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    }
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = usernameInput.value.trim();
            const password = passwordInput.value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                showAlert('Please enter your email and password', 'error');
                return;
            }
            
            loginButton.textContent = 'Logging in...';
            loginButton.classList.add('loading');
        });
    }
    
    function showAlert(message, type) {
        const existingAlert = document.querySelector('.alert-container .alert:not(#timeoutAlertContainer .alert)');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;
        
        const globalAlertContainer = document.getElementById('globalAlertContainer');
        if (globalAlertContainer) {
            globalAlertContainer.appendChild(alertDiv);
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
