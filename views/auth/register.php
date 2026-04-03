<?php
// File: /views/auth/register.php
$hideHeader = true;
$pageTitle = 'Create Account | ROGELE';

$classes = $classes ?? [];

if (empty($classes)) {
    $classes = [
        ['id' => 1, 'name' => 'Primary 1'],
        ['id' => 2, 'name' => 'Primary 2'],
        ['id' => 3, 'name' => 'Primary 3'],
        ['id' => 4, 'name' => 'Primary 4'],
        ['id' => 5, 'name' => 'Primary 5'],
        ['id' => 6, 'name' => 'Primary 6'],
        ['id' => 7, 'name' => 'Primary 7']
    ];
}
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

        .register-card {
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

        .phone-field {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
        }

        .country-code {
            width: 70px;
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
            min-width: 0;
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
        }

        .toggle-password:hover {
            color: #f06724;
        }

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

        @media (max-width: 480px) {
            .register-card {
                padding: 32px 24px;
            }
            .logo {
                width: 55px;
                height: 55px;
            }
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
            .phone-field {
                flex-direction: row;
                gap: 8px;
            }
            .country-code {
                width: 70px;
                flex-shrink: 0;
            }
        }
    </style>
</head>
<body>
<div class="register-page">
    <div class="register-container">
        <div class="register-card">
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
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>
            
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
                
                <div class="form-group">
                    <div class="phone-field">
                        <span class="country-code">+256</span>
                        <input type="tel" name="phone" id="phone" placeholder="Contact No. (e.g, 701234567)" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <select name="class_id" id="class_id" required>
                        <option value="">Select your class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>">
                                <?php echo htmlspecialchars($class['name']); ?>
                            </option>
                        <?php endforeach; ?>
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
                        I agree to the <a href="<?php echo BASE_URL; ?>/terms-of-service" target="_blank" target="_blank">Terms of Service</a> and <a href="<?php echo BASE_URL; ?>/privacy-policy">Privacy Policy</a>
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
    const classSelect = document.getElementById('class_id');
    
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
    
    // Format phone number
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.startsWith('0')) {
                    value = value.substring(1);
                }
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
    
    // Form validation
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const firstName = document.getElementById('first_name').value.trim();
            const lastName = document.getElementById('last_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = phoneInput.value.trim();
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            const classId = classSelect.value;
            const terms = document.getElementById('terms').checked;
            
            if (!firstName || !lastName || !email || !phone || !password || !confirm) {
                e.preventDefault();
                alert('Please fill in all fields');
                return;
            }
            
            if (!classId) {
                e.preventDefault();
                alert('Please select your class');
                return;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return;
            }
            
            const cleanPhone = phone.replace(/\s/g, '');
            const phoneRegex = /^[0-9]{9}$/;
            if (!phoneRegex.test(cleanPhone)) {
                e.preventDefault();
                alert('Please enter a valid 9-digit phone number');
                return;
            }
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match');
                return;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters');
                return;
            }
            
            if (!terms) {
                e.preventDefault();
                alert('Please accept the Terms of Service');
                return;
            }
            
            registerButton.classList.add('loading');
            registerButton.disabled = true;
        });
    }
});
</script>
</body>
</html>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>