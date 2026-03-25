<?php
// File: /views/auth/register.php
$hideHeader = true;
$pageTitle = 'Create Account | ROGELE';
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

        .register-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: #f5f5f5;
        }

        .register-container {
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }

        /* Card */
        .register-card {
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
        .register-form {
            width: 100%;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background: white;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #f06724;
            box-shadow: 0 0 0 2px rgba(240, 103, 36, 0.1);
        }

        .form-group input::placeholder,
        .form-group select::placeholder {
            color: #999;
        }

        /* Phone Number Field - Special styling */
        .phone-field {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .country-code {
            width: 80px;
            flex-shrink: 0;
            text-align: center;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px 8px;
            font-size: 0.95rem;
            color: #666;
        }

        .phone-field input {
            flex: 1;
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
        .btn-register {
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

        .btn-register:hover {
            background: #e05a1a;
        }

        .btn-register:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Terms */
        .terms-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 20px 0;
        }

        .terms-group input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin-top: 2px;
            cursor: pointer;
        }

        .terms-group label {
            font-size: 0.8rem;
            color: black;
            cursor: pointer;
        }

        .terms-group a {
            color: #7f2677;
            text-decoration: none;
        }

        .terms-group a:hover {
            text-decoration: underline;
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
        .btn-register.loading {
            position: relative;
            color: transparent;
            pointer-events: none;
        }

        .btn-register.loading::after {
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
            .register-card {
                padding: 32px 24px;
            }
            
            .logo {
                width: 55px;
                height: 55px;
            }
            
            .logo-section h1 {
                font-size: 1.3rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
            
            .form-group input,
            .form-group select {
                padding: 10px 14px;
            }
            
            .btn-register {
                padding: 10px;
            }
            
            .phone-field {
                flex-direction: column;
                align-items: stretch;
            }
            
            .country-code {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="register-page">
    <div class="register-container">
        <div class="register-card">
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
                <h1>Welcome to ROGELE</h1>
                <p>Let's create your account</p>
            </div>
            
            <!-- Alert Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Register Form -->
            <form action="<?php echo BASE_URL; ?>/register" method="POST" class="register-form" id="registerForm">
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="first_name" id="first_name" placeholder="First name" required>
                    </div>
                    
                    <div class="form-group">
                        <input type="text" name="last_name" id="last_name" placeholder="Last name" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <input type="email" name="email" id="email" placeholder="Email address" required>
                </div>
                
                <!-- Phone Number Field -->
                <div class="form-group">
                    <div class="phone-field">
                        <span class="country-code">+256</span>
                        <input type="tel" name="phone" id="phone" placeholder="Phone number (e.g., 701234567)" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <select name="class_id" id="class_id">
                        <option value="">Select your class</option>
                        <option value="1">Primary 1</option>
                        <option value="2">Primary 2</option>
                        <option value="3">Primary 3</option>
                        <option value="4">Primary 4</option>
                        <option value="5">Primary 5</option>
                        <option value="6">Primary 6</option>
                        <option value="7">Primary 7</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <div class="password-field">
                        <input type="password" name="password" id="password" placeholder="Password" required>
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
                
                <div class="terms-group">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">
                        I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" class="btn-register" id="registerButton">
                    Create account
                </button>
                
                <div class="login-link">
                    <span>Already have an account?</span>
                    <a href="<?php echo BASE_URL; ?>/login">Sign in</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    const registerButton = document.getElementById('registerButton');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const phoneInput = document.getElementById('phone');
    
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
    
    // Format phone number as user types
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                // Remove leading zero if present
                if (value.startsWith('0')) {
                    value = value.substring(1);
                }
                // Format as XXX XXX XXX
                if (value.length > 3) {
                    value = value.slice(0, 3) + ' ' + value.slice(3);
                }
                if (value.length > 7) {
                    value = value.slice(0, 7) + ' ' + value.slice(7, 10);
                }
                e.target.value = value;
            }
        });
    }
    
    // Auto-focus on first name field
    const firstNameInput = document.getElementById('first_name');
    if (firstNameInput) {
        firstNameInput.focus();
    }
    
    // Form validation
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const firstName = document.getElementById('first_name').value.trim();
            const lastName = document.getElementById('last_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = phoneInput ? phoneInput.value.trim() : '';
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            const terms = document.getElementById('terms').checked;
            
            if (!firstName || !lastName || !email || !phone || !password || !confirm) {
                e.preventDefault();
                showAlert('Please fill in all fields', 'error');
                return;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                showAlert('Please enter a valid email address', 'error');
                return;
            }
            
            // Phone validation (Ugandan format)
            const cleanPhone = phone.replace(/\s/g, '');
            const phoneRegex = /^[0-9]{9}$/;
            if (!phoneRegex.test(cleanPhone)) {
                e.preventDefault();
                showAlert('Please enter a valid 9-digit phone number (e.g., 701234567)', 'error');
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
            
            if (!terms) {
                e.preventDefault();
                showAlert('Please accept the Terms of Service', 'error');
                return;
            }
            
            // Show loading state
            registerButton.classList.add('loading');
            registerButton.disabled = true;
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
        
        const registerCard = document.querySelector('.register-card');
        const logoSection = document.querySelector('.logo-section');
        
        if (registerCard && logoSection) {
            registerCard.insertBefore(alertDiv, logoSection.nextSibling);
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