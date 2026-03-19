<!-- File: /views/auth/login.php -->
<?
$hideHeader = true; 
$pageTitle = 'Login - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<style>
/* Login Page Specific Styles - No Horizontal Scroll */
:root {
    --primary-purple: #8B5CF6;
    --primary-purple-dark: #7C3AED;
    --primary-purple-light: #A78BFA;
    --secondary-orange: #F97316;
    --secondary-orange-dark: #EA580C;
    --secondary-orange-light: #FB923C;
    --gradient-primary: linear-gradient(135deg, #8B5CF6, #F97316);
    --gradient-soft: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(249, 115, 22, 0.1));
}

/* Reset to prevent horizontal scroll */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    overflow-x: hidden;
    width: 100%;
    position: relative;
}

.login-page {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, #f5f3ff 0%, #fff7ed 100%);
    position: relative;
}

/* Decorative Background - Fixed positioning to prevent overflow */
.login-page::before {
    content: '';
    position: fixed;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
    z-index: 0;
}

.login-page::after {
    content: '';
    position: fixed;
    bottom: -50%;
    left: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(249, 115, 22, 0.1) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
    z-index: 0;
}

.login-container {
    width: 100%;
    max-width: 450px;
    margin: 0 auto;
    position: relative;
    z-index: 10;
}

/* Login Card */
.login-card {
    background: white;
    border-radius: 30px;
    box-shadow: 0 25px 50px -12px rgba(139, 92, 246, 0.25);
    padding: 40px;
    width: 100%;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(139, 92, 246, 0.1);
}

.login-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-primary);
}

/* Card Header */
.login-header {
    text-align: center;
    margin-bottom: 30px;
}

.login-header h2 {
    font-size: 2rem;
    font-weight: 800;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
}

.login-header p {
    color: #64748B;
    font-size: 0.95rem;
}

/* Login Form */
.login-form {
    width: 100%;
}

.form-group {
    margin-bottom: 20px;
    width: 100%;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    color: #1E293B;
    margin-bottom: 8px;
}

.form-group label i {
    color: var(--primary-purple);
    font-size: 1rem;
}

.form-group input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary-purple);
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.form-group input.error {
    border-color: #EF4444;
}

.form-group input::placeholder {
    color: #94A3B8;
    font-size: 0.95rem;
}

/* Password Field */
.password-field {
    position: relative;
    width: 100%;
}

.password-field input {
    padding-right: 45px;
}

.toggle-password {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94A3B8;
    cursor: pointer;
    font-size: 1.1rem;
    transition: color 0.3s ease;
    padding: 5px;
}

.toggle-password:hover {
    color: var(--primary-purple);
}

/* Form Options */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 10px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    color: #64748B;
    font-size: 0.95rem;
}

.checkbox-label input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: var(--primary-purple);
}

.forgot-link {
    color: var(--primary-purple);
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 600;
    transition: color 0.3s ease;
}

.forgot-link:hover {
    color: var(--secondary-orange);
}

/* Login Button */
.btn-login {
    width: 100%;
    padding: 14px;
    background: var(--gradient-primary);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-login::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
}

.btn-login:hover::before {
    left: 100%;
}

.btn-login:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.5);
}

.btn-login:active {
    transform: translateY(0);
}

.btn-login i {
    transition: transform 0.3s ease;
}

.btn-login:hover i {
    transform: translateX(5px);
}

/* Register Link */
.register-link {
    text-align: center;
    margin-top: 25px;
    padding-top: 25px;
    border-top: 1px solid #E2E8F0;
}

.register-link p {
    color: #64748B;
    font-size: 0.95rem;
}

.register-link a {
    color: var(--primary-purple);
    text-decoration: none;
    font-weight: 700;
    margin-left: 5px;
    position: relative;
}

.register-link a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100%;
    height: 2px;
    background: var(--gradient-primary);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.register-link a:hover::after {
    transform: scaleX(1);
}

/* Learner Note */
.learner-note {
    margin-top: 20px;
    background: var(--gradient-soft);
    border-radius: 12px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 12px;
    border: 1px solid rgba(139, 92, 246, 0.2);
}

.learner-note i {
    font-size: 1.5rem;
    color: var(--secondary-orange);
}

.learner-note p {
    color: #1E293B;
    font-size: 0.9rem;
    line-height: 1.5;
}

.learner-note strong {
    color: var(--primary-purple);
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
    width: 20px;
    height: 20px;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border: 2px solid white;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Alert Messages */
.alert {
    margin-bottom: 20px;
    padding: 12px 16px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.95rem;
    width: 100%;
}

.alert-error {
    background: #FEF2F2;
    color: #B91C1C;
    border: 1px solid #FECACA;
}

.alert-success {
    background: #F0FDF4;
    color: #166534;
    border: 1px solid #BBF7D0;
}

.alert i {
    font-size: 1.1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .login-page {
        padding: 20px 15px;
    }
    
    .login-card {
        padding: 30px 25px;
    }
    
    .login-header h2 {
        font-size: 1.8rem;
    }
    
    .login-header p {
        font-size: 0.9rem;
    }
    
    .form-options {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
}

@media (max-width: 480px) {
    .login-page {
        padding: 15px 10px;
    }
    
    .login-card {
        padding: 25px 20px;
    }
    
    .login-header h2 {
        font-size: 1.6rem;
    }
    
    .form-group input {
        padding: 10px 14px;
        font-size: 0.95rem;
    }
    
    .btn-login {
        padding: 12px;
        font-size: 0.95rem;
    }
    
    .learner-note {
        flex-direction: column;
        text-align: center;
        padding: 12px;
    }
    
    .learner-note i {
        font-size: 1.3rem;
    }
    
    .learner-note p {
        font-size: 0.85rem;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .login-page {
        background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
    }
    
    .login-card {
        background: #1E293B;
        border-color: #334155;
    }
    
    .form-group label {
        color: #F1F5F9;
    }
    
    .form-group input {
        background: #0F172A;
        border-color: #334155;
        color: #F1F5F9;
    }
    
    .form-group input::placeholder {
        color: #64748B;
    }
    
    .checkbox-label {
        color: #94A3B8;
    }
    
    .register-link {
        border-top-color: #334155;
    }
    
    .register-link p {
        color: #94A3B8;
    }
    
    .learner-note {
        background: rgba(139, 92, 246, 0.1);
    }
    
    .learner-note p {
        color: #F1F5F9;
    }
}
</style>

<div class="login-page">
    <div class="login-container">
        <!-- Login Card -->
        <div class="login-card">
            <div class="login-header">
                <h2>Welcome Back! 👋</h2>
                <p>Login to continue your learning journey</p>
            </div>
            
            <!-- Display Session Messages -->
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
            
            <form action="<?php echo BASE_URL; ?>/login" method="POST" class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Username or Email
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="e.g., ROG-P5-001 or email@example.com"
                        required
                        autocomplete="username"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-field">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="<?php echo BASE_URL; ?>/forgot-password" class="forgot-link">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn-login" id="loginButton">
                    <span>Login</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
                
                <div class="register-link">
                    <p>Don't have an account? <a href="<?php echo BASE_URL; ?>/register">Create free account</a></p>
                </div>
                
                <div class="learner-note">
                    <i class="fas fa-info-circle"></i>
                    <p><strong>For Learners:</strong> Use your registration number as both username and password (e.g., ROG-P5-001)</p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    const togglePassword = document.querySelector('.toggle-password');
    const password = document.getElementById('password');
    
    if (togglePassword && password) {
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
    
    // Form submission
    const loginForm = document.getElementById('loginForm');
    const loginButton = document.getElementById('loginButton');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                showNotification('Please fill in all fields', 'error');
                return;
            }
            
            // Show loading state
            loginButton.classList.add('loading');
            loginButton.disabled = true;
            loginButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
        });
    }
    
    // Show notification function
    window.showNotification = function(message, type) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;
        
        const loginCard = document.querySelector('.login-card');
        if (loginCard) {
            loginCard.insertBefore(notification, loginCard.firstChild);
        }
        
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    };
    
    // Auto-focus username field
    document.getElementById('username').focus();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>