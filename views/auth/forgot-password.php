<?php
// File: /views/auth/forgot-password.php
$hideHeader = true;
$pageTitle = 'Forgot Password | ROGELE';
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

        .forgot-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: #f5f5f5;
        }

        .forgot-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        /* Card */
        .forgot-card {
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
        }

        .logo img {
            width: 100%;
            height: 100%;
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
        .forgot-form {
            width: 100%;
        }

        .form-group {
            margin-bottom: 24px;
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

        /* Button */
        .btn-submit {
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
        }

        .btn-submit:hover {
            background: #e05a1a;
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Back to Login Link */
        .back-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #eee;
        }

        .back-link a {
            color: #7f2677;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        /* Success Message */
        .success-message {
            text-align: center;
        }

        .success-icon {
            width: 60px;
            height: 60px;
            background: #e6f4ea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .success-icon i {
            font-size: 2rem;
            color: #2e7d32;
        }

        .success-message h2 {
            font-size: 1.3rem;
            font-weight: 600;
            color: black;
            margin-bottom: 12px;
        }

        .success-message p {
            color: black;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 24px;
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
        .btn-submit.loading {
            position: relative;
            color: transparent;
            pointer-events: none;
        }

        .btn-submit.loading::after {
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
            .forgot-card {
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
            
            .btn-submit {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
<div class="forgot-page">
    <div class="forgot-container">
        <div class="forgot-card">
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
                <h1>Reset your password</h1>
                <p>Enter your account email address and we'll send you a password reset link.</p>
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
            
            <!-- Success Message (Full Page) -->
            <?php if (isset($_SESSION['reset_sent'])): ?>
                <div class="success-message">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2>Check your email</h2>
                    <p><?php echo $_SESSION['reset_sent']; unset($_SESSION['reset_sent']); ?></p>
                    <div class="back-link" style="border-top: none; padding-top: 0;">
                        <a href="<?php echo BASE_URL; ?>/login">Back to login</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Forgot Password Form -->
                <form action="<?php echo BASE_URL; ?>/auth/process-forgot-password" method="POST" class="forgot-form" id="forgotForm">
                    <div class="form-group">
                        <input 
                            type="email" 
                            name="email" 
                            id="email"
                            placeholder="Email address"
                            required
                            autocomplete="off"
                        >
                    </div>
                    
                    <button type="submit" class="btn-submit" id="submitBtn">
                        Submit
                    </button>
                    
                    <div class="back-link">
                        <a href="<?php echo BASE_URL; ?>/login">Remember password? Login</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const forgotForm = document.getElementById('forgotForm');
    const submitBtn = document.getElementById('submitBtn');
    const emailInput = document.getElementById('email');
    
    // Auto-focus on email field
    if (emailInput) {
        emailInput.focus();
    }
    
    // Form submission
    if (forgotForm) {
        forgotForm.addEventListener('submit', function(e) {
            const email = emailInput.value.trim();
            
            if (!email) {
                e.preventDefault();
                showAlert('Please enter your email address', 'error');
                return;
            }
            
            // Simple email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                showAlert('Please enter a valid email address', 'error');
                return;
            }
            
            // Show loading state
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
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
        
        const forgotCard = document.querySelector('.forgot-card');
        const logoSection = document.querySelector('.logo-section');
        
        if (forgotCard && logoSection) {
            forgotCard.insertBefore(alertDiv, logoSection.nextSibling);
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