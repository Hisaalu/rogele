<!-- File: /views/auth/register.php -->
<?php 
$pageTitle = 'Create Account - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<style>
/* Register Page Specific Styles - No Horizontal Scroll */
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

.register-page {
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
.register-page::before {
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

.register-page::after {
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

.register-container {
    width: 100%;
    max-width: 550px;
    margin: 0 auto;
    position: relative;
    z-index: 10;
}

/* Register Card */
.register-card {
    background: white;
    border-radius: 30px;
    box-shadow: 0 25px 50px -12px rgba(139, 92, 246, 0.25);
    padding: 40px;
    width: 100%;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(139, 92, 246, 0.1);
}

.register-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-primary);
}

/* Card Header */
.register-header {
    text-align: center;
    margin-bottom: 30px;
}

.register-header h2 {
    font-size: 2rem;
    font-weight: 800;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
}

.register-header p {
    color: #64748B;
    font-size: 0.95rem;
}

/* Register Form */
.register-form {
    width: 100%;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
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

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary-purple);
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.form-group input.error,
.form-group select.error {
    border-color: #EF4444;
}

.form-group input::placeholder,
.form-group select::placeholder {
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

/* Password Strength Meter */
.password-strength {
    margin-top: 8px;
    width: 100%;
}

.strength-bar {
    display: flex;
    gap: 5px;
    margin-bottom: 5px;
    width: 100%;
}

.strength-segment {
    flex: 1;
    height: 4px;
    background: #E2E8F0;
    border-radius: 2px;
    transition: all 0.3s ease;
}

.strength-segment.active:nth-child(1) { background: #EF4444; }
.strength-segment.active:nth-child(2) { background: #F97316; }
.strength-segment.active:nth-child(3) { background: #EAB308; }
.strength-segment.active:nth-child(4) { background: #22C55E; }

.strength-text {
    font-size: 0.8rem;
    color: #64748B;
}

/* Trial Info */
.trial-info {
    background: var(--gradient-soft);
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
    border: 1px solid rgba(139, 92, 246, 0.2);
    width: 100%;
}

.trial-info i {
    font-size: 2rem;
    color: var(--secondary-orange);
}

.trial-info-content {
    flex: 1;
}

.trial-info-content p {
    color: #1E293B;
    font-size: 0.95rem;
    line-height: 1.5;
}

.trial-info-content strong {
    color: var(--primary-purple);
}

/* Terms Group */
.terms-group {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 20px;
    width: 100%;
}

.terms-group input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-top: 2px;
    accent-color: var(--primary-purple);
    flex-shrink: 0;
}

.terms-group label {
    font-size: 0.9rem;
    color: #64748B;
    line-height: 1.5;
}

.terms-group a {
    color: var(--primary-purple);
    text-decoration: none;
    font-weight: 600;
}

.terms-group a:hover {
    text-decoration: underline;
}

/* Register Button */
.btn-register {
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

.btn-register::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
}

.btn-register:hover::before {
    left: 100%;
}

.btn-register:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.5);
}

.btn-register:active {
    transform: translateY(0);
}

.btn-register i {
    transition: transform 0.3s ease;
}

.btn-register:hover i {
    transform: translateX(5px);
}

/* Login Link */
.login-link {
    text-align: center;
    margin-top: 25px;
    padding-top: 25px;
    border-top: 1px solid #E2E8F0;
}

.login-link p {
    color: #64748B;
    font-size: 0.95rem;
}

.login-link a {
    color: var(--primary-purple);
    text-decoration: none;
    font-weight: 700;
    margin-left: 5px;
    position: relative;
}

.login-link a::after {
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

.login-link a:hover::after {
    transform: scaleX(1);
}

/* Benefits Grid */
.benefits-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-top: 25px;
    width: 100%;
}

.benefit-item {
    text-align: center;
    padding: 15px 10px;
    background: var(--gradient-soft);
    border-radius: 12px;
    transition: transform 0.3s ease;
}

.benefit-item:hover {
    transform: translateY(-5px);
}

.benefit-item i {
    font-size: 1.5rem;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 8px;
}

.benefit-item span {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    color: #1E293B;
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

/* Responsive Design */
@media (max-width: 768px) {
    .register-page {
        padding: 20px 15px;
    }
    
    .register-card {
        padding: 30px 25px;
    }
    
    .register-header h2 {
        font-size: 1.8rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .trial-info {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .trial-info i {
        font-size: 1.8rem;
    }
    
    .benefits-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }
}

@media (max-width: 480px) {
    .register-page {
        padding: 15px 10px;
    }
    
    .register-card {
        padding: 25px 20px;
    }
    
    .register-header h2 {
        font-size: 1.6rem;
    }
    
    .form-group input,
    .form-group select {
        padding: 10px 14px;
        font-size: 0.95rem;
    }
    
    .btn-register {
        padding: 12px;
        font-size: 0.95rem;
    }
    
    .terms-group {
        align-items: flex-start;
    }
    
    .terms-group label {
        font-size: 0.85rem;
    }
    
    .benefit-item {
        padding: 12px 8px;
    }
    
    .benefit-item span {
        font-size: 0.75rem;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .register-page {
        background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
    }
    
    .register-card {
        background: #1E293B;
        border-color: #334155;
    }
    
    .form-group label {
        color: #F1F5F9;
    }
    
    .form-group input,
    .form-group select {
        background: #0F172A;
        border-color: #334155;
        color: #F1F5F9;
    }
    
    .form-group input::placeholder,
    .form-group select::placeholder {
        color: #64748B;
    }
    
    .terms-group label {
        color: #94A3B8;
    }
    
    .login-link {
        border-top-color: #334155;
    }
    
    .login-link p {
        color: #94A3B8;
    }
    
    .benefit-item span {
        color: #F1F5F9;
    }
    
    .trial-info-content p {
        color: #F1F5F9;
    }
}
</style>

<div class="register-page">
    <div class="register-container">
        <!-- Register Card -->
        <div class="register-card">
            <div class="register-header">
                <h2>Create Account</h2>
                <p>Join Rays of Grace learning community</p>
            </div>
            
            <!-- Display Session Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error" style="margin-bottom: 20px; padding: 12px; border-radius: 10px; background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo BASE_URL; ?>/register" method="POST" class="register-form" id="registerForm">
                <!-- Name Fields -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">
                            <i class="fas fa-user"></i>
                            First Name
                        </label>
                        <input type="text" id="first_name" name="first_name" placeholder="John" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">
                            <i class="fas fa-user"></i>
                            Last Name
                        </label>
                        <input type="text" id="last_name" name="last_name" placeholder="Doe" required>
                    </div>
                </div>
                
                <!-- Email -->
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" placeholder="john.doe@example.com" required>
                </div>
                
                <!-- Phone -->
                <div class="form-group">
                    <label for="phone">
                        <i class="fas fa-phone"></i>
                        Phone Number
                    </label>
                    <input type="tel" id="phone" name="phone" placeholder="+256 XXX XXX XXX" required>
                </div>
                
                <!-- Password Fields -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" placeholder="Create a password" required minlength="8">
                            <button type="button" class="toggle-password" data-target="password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar">
                                <span class="strength-segment"></span>
                                <span class="strength-segment"></span>
                                <span class="strength-segment"></span>
                                <span class="strength-segment"></span>
                            </div>
                            <span class="strength-text">Enter a password</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i>
                            Confirm Password
                        </label>
                        <div class="password-field">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                            <button type="button" class="toggle-password" data-target="confirm_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Class Selection (for learners) -->
                <div class="form-group" id="classGroup">
                    <label for="class">
                        <i class="fas fa-graduation-cap"></i>
                        Select Class (Optional for external users)
                    </label>
                    <select id="class" name="class">
                        <option value="">Select your class</option>
                        <option value="p1">Primary 1</option>
                        <option value="p2">Primary 2</option>
                        <option value="p3">Primary 3</option>
                        <option value="p4">Primary 4</option>
                        <option value="p5">Primary 5</option>
                        <option value="p6">Primary 6</option>
                        <option value="p7">Primary 7</option>
                    </select>
                </div>
                
                <!-- Trial Information -->
                <div class="trial-info">
                    <i class="fas fa-gift"></i>
                    <div class="trial-info-content">
                        <p><strong>🎁 2 Months Free Trial!</strong> Get full access to all features for 60 days. No credit card required.</p>
                    </div>
                </div>
                
                <!-- Terms and Conditions -->
                <div class="terms-group">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">
                        I agree to the <a href="#" target="_blank">Terms of Service</a> and 
                        <a href="#" target="_blank">Privacy Policy</a>
                    </label>
                </div>
                
                <div class="terms-group">
                    <input type="checkbox" id="updates" name="updates">
                    <label for="updates">
                        I want to receive updates about new features and learning tips
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn-register" id="registerButton">
                    <span>Create Account</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
                
                <!-- Login Link -->
                <div class="login-link">
                    <p>Already have an account? <a href="<?php echo BASE_URL; ?>/login">Sign In</a></p>
                </div>
                
                <!-- Benefits -->
                <div class="benefits-grid">
                    <div class="benefit-item">
                        <i class="fas fa-infinity"></i>
                        <span>Unlimited Access</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-rocket"></i>
                        <span>2 Months Free</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-headset"></i>
                        <span>24/7 Support</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.dataset.target;
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

    // Password strength meter
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const strength = calculatePasswordStrength(this.value);
            updateStrengthMeter(strength);
        });
    }

    function calculatePasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]+/)) strength++;
        if (password.match(/[A-Z]+/)) strength++;
        if (password.match(/[0-9]+/)) strength++;
        if (password.match(/[$@#&!]+/)) strength++;
        return strength;
    }

    function updateStrengthMeter(strength) {
        const segments = document.querySelectorAll('.strength-segment');
        const text = document.querySelector('.strength-text');
        
        segments.forEach((seg, index) => {
            if (index < strength) {
                seg.classList.add('active');
            } else {
                seg.classList.remove('active');
            }
        });

        const texts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        text.textContent = texts[strength] || 'Enter a password';
    }

    // Form submission with validation
    const registerForm = document.getElementById('registerForm');
    const registerButton = document.getElementById('registerButton');

    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            const terms = document.getElementById('terms').checked;
            
            if (password !== confirm) {
                e.preventDefault();
                showNotification('Passwords do not match!', 'error');
                return;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                showNotification('Password must be at least 8 characters', 'error');
                return;
            }
            
            if (!terms) {
                e.preventDefault();
                showNotification('Please accept the Terms and Conditions', 'error');
                return;
            }
            
            // Add loading state
            registerButton.classList.add('loading');
            registerButton.disabled = true;
        });
    }

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;
        
        const registerCard = document.querySelector('.register-card');
        registerCard.insertBefore(notification, registerCard.firstChild);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Add animation style
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>