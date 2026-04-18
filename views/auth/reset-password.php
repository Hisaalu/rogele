<?php
// File: /views/auth/reset-password.php
$hideHeader = true;
$pageTitle = 'Reset Password | ROGELE';
$token = $token ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/public/images/logo.png">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f5f5f5;
        }

        .reset-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: #f5f5f5;
        }

        .reset-container {
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }

        /* Card */
        .reset-card {
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
            width: 70px;
            height: auto;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo img {
            width: 100%;
            height: auto;
            object-fit: contain;
        }

        .logo-section h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0;
        }

        .logo-section p {
            font-size: 0.85rem;
            color: black;
            margin-top: 4px;
        }

        /* Form */
        .reset-form {
            width: 100%;
        }

        .form-group {
            margin-bottom: 20px;
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
        }

        .toggle-password:hover {
            color: #f06724;
        }

        /* Button */
        .btn-reset {
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

        .btn-reset:hover {
            background: #e05a1a;
        }

        .btn-reset:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Login Link */
        .login-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #eee;
            font-size: 0.85rem;
            color: black;
        }

        .login-link a {
            color: #7f2677;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
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
        .btn-reset.loading {
            position: relative;
            color: transparent;
            pointer-events: none;
        }

        .btn-reset.loading::after {
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
            .reset-card {
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
            
            .btn-reset {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
<div class="reset-page">
    <div class="reset-container">
        <div class="reset-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo">
                    <?php 
                    $logoPath = BASE_URL . '/public/images/logo.png';
                    $logoFile = __DIR__ . '/../../public/images/logo.png';
                    ?>
                    <?php if (file_exists($logoFile)): ?>
                        <img src="<?php echo $logoPath; ?>" alt="ROGELE Logo">
                    <?php else: ?>
                        <span style="font-size: 2.5rem; font-weight: 700; color: #f06724;">RG</span>
                    <?php endif; ?>
                </div>
                <h1>Reset password</h1>
                <p>To complete this process, provide your new password and its confirmation below.</p>
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
            
            <!-- Reset Password Form -->
            <form action="<?php echo BASE_URL; ?>/auth/process-reset-password" method="POST" class="reset-form" id="resetForm">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">
                
                <div class="form-group">
                    <div class="password-field">
                        <input type="password" name="password" id="password" placeholder="New password" required>
                        <button type="button" class="toggle-password" data-target="password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="password-field">
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm password" required>
                        <button type="button" class="toggle-password" data-target="confirm_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn-reset" id="resetButton">
                    Reset password
                </button>
                
                <div class="login-link">
                    <span>Remember your password?</span>
                    <a href="<?php echo BASE_URL; ?>/login">Back to login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const resetForm = document.getElementById('resetForm');
    const resetButton = document.getElementById('resetButton');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const token = document.querySelector('input[name="token"]').value;
    
    // Check if token exists
    if (!token) {
        window.location.href = '<?php echo BASE_URL; ?>/login';
    }
    
    // Password visibility toggle
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Auto-focus on password field
    if (passwordInput) {
        passwordInput.focus();
    }
    
    // Form validation
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            
            if (!password || !confirm) {
                e.preventDefault();
                showAlert('Please fill in all fields', 'error');
                return;
            }
            
            if (password !== confirm) {
                e.preventDefault();
                showAlert('Passwords do not match', 'error');
                return;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                showAlert('Password must be at least 8 characters', 'error');
                return;
            }
            
            // Show loading state
            resetButton.classList.add('loading');
            resetButton.disabled = true;
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
        
        const resetCard = document.querySelector('.reset-card');
        const logoSection = document.querySelector('.logo-section');
        
        if (resetCard && logoSection) {
            resetCard.insertBefore(alertDiv, logoSection.nextSibling);
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