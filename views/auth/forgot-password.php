<?php
// File: /views/auth/forgot-password.php
$hideHeader = true;
$pageTitle = 'Forgot Password | ROGELE';
$oldInput = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .forgot-card {
            background: white;
            border-radius: 10px;
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
        }

        .logo img {
            width: 100%;
            height: auto;
            object-fit: contain;
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
            box-shadow: 0 0 0 2px rgba(240, 103, 36, 0.25);
        }

        .form-group input::placeholder {
            color: #999;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #f06724;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
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
            color: #000;
            margin-bottom: 12px;
        }

        .success-message p {
            color: #000;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 24px;
        }

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

        .alert i {
            font-size: 1.05rem;
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

<div class="alert-container" id="globalAlertContainer">
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

<div class="forgot-page">
    <div class="forgot-container">
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
                <div class="logo-section">
                <h1>Reset your password</h1>
            </div>
            </div>
        <div class="forgot-card">
            <div class="logo-section">
                    <p>Enter your ROGELE account email address and we will send you a password reset link.</p>
            </div>
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
                <form action="<?php echo BASE_URL; ?>/auth/process-forgot-password" method="POST" class="forgot-form" id="forgotForm">
                    <div class="form-group">
                        <input 
                            type="email" 
                            name="email" 
                            id="email"
                            placeholder="Your ROGELE Email address"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? $oldInput['email'] ?? ''); ?>"
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
    
    if (forgotForm) {
        forgotForm.addEventListener('submit', function(e) {
            const email = emailInput.value.trim();
            
            if (!email) {
                e.preventDefault();
                showAlert('Please enter your ROGELE email address', 'error');
                return;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                showAlert('Please enter a valid ROGELE email address', 'error');
                return;
            }
            
            submitBtn.textContent = 'Submitting...';
            submitBtn.disabled = true;
        });
    }
    
    function showAlert(message, type) {
        const existingAlert = document.querySelector('.alert-container .alert');
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