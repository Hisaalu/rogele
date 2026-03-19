<?
// File: /views/auth/reset-password.php
$hideHeader = true;
$pageTitle = 'Reset Password - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

$token = $_GET['token'] ?? '';
if (empty($token)) {
    header('Location: <?php echo BASE_URL; ?>/login');
    exit;
}
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h2>Reset Password 🔒</h2>
            <p>Enter your new password below</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/reset-password" method="POST" class="auth-form" id="resetForm">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                    New Password
                </label>
                <div class="password-input-wrapper">
                    <input type="password" id="password" name="password" required placeholder="Enter new password" minlength="8" onkeyup="checkPasswordStrength()">
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength" id="passwordStrength">
                    <div class="strength-bar">
                        <div class="strength-segment"></div>
                        <div class="strength-segment"></div>
                        <div class="strength-segment"></div>
                        <div class="strength-segment"></div>
                    </div>
                    <span class="strength-text">Enter a strong password</span>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">
                    <i class="fas fa-check-circle"></i>
                    Confirm Password
                </label>
                <div class="password-input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm new password" onkeyup="checkPasswordMatch()">
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-match" id="passwordMatch"></div>
            </div>

            <div class="password-requirements">
                <p><i class="fas fa-info-circle"></i> Password must contain:</p>
                <ul>
                    <li id="req-length"><i class="fas fa-times"></i> At least 8 characters</li>
                    <li id="req-uppercase"><i class="fas fa-times"></i> At least 1 uppercase letter</li>
                    <li id="req-lowercase"><i class="fas fa-times"></i> At least 1 lowercase letter</li>
                    <li id="req-number"><i class="fas fa-times"></i> At least 1 number</li>
                </ul>
            </div>

            <button type="submit" class="btn-primary btn-block" id="submitBtn">
                <span>Reset Password</span>
                <i class="fas fa-arrow-right"></i>
            </button>

            <div class="auth-footer">
                <p>Remember your password? <a href="<?php echo BASE_URL; ?>/login">Back to Login</a></p>
            </div>
        </form>
    </div>
</div>

<style>
/* Add these styles to the existing ones */
.password-input-wrapper {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94A3B8;
    cursor: pointer;
    padding: 5px;
    transition: color 0.3s ease;
}

.toggle-password:hover {
    color: #8B5CF6;
}

.password-strength {
    margin-top: 8px;
}

.strength-bar {
    display: flex;
    gap: 5px;
    margin-bottom: 5px;
}

.strength-segment {
    flex: 1;
    height: 4px;
    background: #E2E8F0;
    border-radius: 2px;
    transition: all 0.3s ease;
}

.strength-text {
    font-size: 0.85rem;
    color: #64748B;
}

.password-match {
    font-size: 0.85rem;
    margin-top: 5px;
}

.password-requirements {
    background: #F8FAFC;
    padding: 15px;
    border-radius: 10px;
    margin-top: 10px;
}

.password-requirements p {
    font-weight: 600;
    color: #1E293B;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.password-requirements ul {
    list-style: none;
}

.password-requirements li {
    font-size: 0.9rem;
    color: #64748B;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.password-requirements li i {
    font-size: 0.8rem;
}

.password-requirements li.valid {
    color: #10B981;
}

.password-requirements li.valid i {
    color: #10B981;
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
    .password-requirements {
        background: #334155;
    }
    
    .password-requirements p {
        color: #F1F5F9;
    }
}
</style>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = event.currentTarget.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthBars = document.querySelectorAll('.strength-segment');
    const strengthText = document.querySelector('.strength-text');
    
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    
    strengthBars.forEach((bar, index) => {
        if (index < strength) {
            if (strength <= 1) bar.style.background = '#EF4444';
            else if (strength <= 2) bar.style.background = '#F97316';
            else if (strength <= 3) bar.style.background = '#EAB308';
            else bar.style.background = '#10B981';
        } else {
            bar.style.background = '#E2E8F0';
        }
    });
    
    const texts = ['Weak', 'Fair', 'Good', 'Strong'];
    strengthText.textContent = strength > 0 ? texts[strength - 1] : 'Enter a password';
    strengthText.style.color = strength <= 1 ? '#EF4444' : strength <= 2 ? '#F97316' : strength <= 3 ? '#EAB308' : '#10B981';
    
    // Update requirements
    document.getElementById('req-length').className = password.length >= 8 ? 'valid' : '';
    document.getElementById('req-uppercase').className = /[A-Z]/.test(password) ? 'valid' : '';
    document.getElementById('req-lowercase').className = /[a-z]/.test(password) ? 'valid' : '';
    document.getElementById('req-number').className = /[0-9]/.test(password) ? 'valid' : '';
    
    document.querySelectorAll('.password-requirements li').forEach(li => {
        const icon = li.querySelector('i');
        if (li.classList.contains('valid')) {
            icon.className = 'fas fa-check-circle';
        } else {
            icon.className = 'fas fa-times-circle';
        }
    });
}

function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    const matchDiv = document.getElementById('passwordMatch');
    
    if (confirm === '') {
        matchDiv.innerHTML = '';
    } else if (password === confirm) {
        matchDiv.innerHTML = '<span style="color: #10B981;"><i class="fas fa-check-circle"></i> Passwords match</span>';
    } else {
        matchDiv.innerHTML = '<span style="color: #EF4444;"><i class="fas fa-times-circle"></i> Passwords do not match</span>';
    }
}

// Form validation
document.getElementById('resetForm')?.addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    const submitBtn = document.getElementById('submitBtn');
    
    if (password !== confirm) {
        e.preventDefault();
        alert('Passwords do not match!');
        return;
    }
    
    if (password.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters long');
        return;
    }
    
    // Add loading state
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>