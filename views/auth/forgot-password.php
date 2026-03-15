<?
// File: /views/auth/forgot-password.php
$hideHeader = true;
$pageTitle = 'Forgot Password - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <?php if (isset($_SESSION['success'])): ?>
            <!-- Success Message with Full Informative Content -->
            <div class="success-message">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                
                <h2>📧 Check Your Email!</h2>
                
                <div class="success-content">
                    <p class="success-text">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </p>
                    
                    <div class="info-box">
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <div class="info-text">
                                <strong>20 Minute Expiry</strong>
                                <span>The reset link will expire in 20 minutes for security reasons</span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <div class="info-text">
                                <strong>Check Your Spam Folder</strong>
                                <span>If you don't see the email, please check your spam/junk folder</span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-shield-alt"></i>
                            <div class="info-text">
                                <strong>Didn't Receive an Email?</strong>
                                <span>If you don't receive the email within a few minutes, click "Resend Email" below</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="warning-note">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>For security reasons, this link can only be used once. If you need to reset your password again, you'll need to request a new link.</span>
                    </div>
                </div>
                
                <div class="success-actions">
                    <a href="/rays-of-grace/login" class="btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Back to Login
                    </a>
                    <a href="/rays-of-grace/forgot-password" class="btn-secondary">
                        <i class="fas fa-redo-alt"></i>
                        Resend Email
                    </a>
                </div>
                
                <div class="contact-support">
                    <i class="fas fa-headset"></i>
                    <span>Need help? <a href="/rays-of-grace/contact">Contact Support</a></span>
                </div>
            </div>
        <?php else: ?>
            <!-- Forgot Password Form -->
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h2>Forgot Password? 🔐</h2>
                <p>No worries, we'll send you reset instructions</p>
            </div>

            <form action="/rays-of-grace/forgot-password" method="POST" class="auth-form" id="forgotForm">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        placeholder="Enter your registered email"
                        autocomplete="off"
                    >
                    <small class="input-hint">
                        <i class="fas fa-paper-plane"></i>
                        We'll send a password reset link to this email
                    </small>
                </div>

                <button type="submit" class="btn-primary btn-block" id="submitBtn">
                    <span>Send Reset Link</span>
                    <i class="fas fa-paper-plane"></i>
                </button>

                <div class="auth-footer">
                    <p>Remember your password? <a href="/rays-of-grace/login">Back to Login</a></p>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>
.auth-container {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, #f5f3ff 0%, #fff7ed 100%);
}

.auth-card {
    background: white;
    border-radius: 30px;
    box-shadow: 0 25px 50px -12px rgba(139, 92, 246, 0.25);
    padding: 40px;
    width: 100%;
    max-width: 520px;
    position: relative;
    overflow: hidden;
    animation: slideUp 0.5s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.auth-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
}

/* Auth Header Styles */
.auth-header {
    text-align: center;
    margin-bottom: 30px;
}

.auth-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

.auth-icon i {
    font-size: 2.5rem;
    color: white;
}

.auth-header h2 {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
}

.auth-header p {
    color: #64748B;
    font-size: 0.95rem;
}

/* Success Message Styles */
.success-message {
    text-align: center;
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.success-icon {
    width: 100px;
    height: 100px;
    background: #F0FDF4;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-8px);
    }
}

.success-icon i {
    font-size: 3.5rem;
    color: #10B981;
}

.success-message h2 {
    font-size: 1.8rem;
    color: #1E293B;
    margin-bottom: 20px;
    font-weight: 700;
}

.success-content {
    margin-bottom: 30px;
}

.success-text {
    color: #64748B;
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: 25px;
    padding: 15px;
    background: #F8FAFC;
    border-radius: 12px;
    border-left: 4px solid #10B981;
    text-align: left;
}

.info-box {
    background: #F8FAFC;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
    text-align: left;
}

.info-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #E2E8F0;
}

.info-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.info-item i {
    font-size: 1.5rem;
    color: #8B5CF6;
    margin-top: 2px;
}

.info-text {
    flex: 1;
}

.info-text strong {
    display: block;
    color: #1E293B;
    font-size: 1rem;
    margin-bottom: 5px;
}

.info-text span {
    color: #64748B;
    font-size: 0.9rem;
    line-height: 1.5;
}

.warning-note {
    background: #FEF2F2;
    border: 1px solid #FECACA;
    border-radius: 12px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 12px;
    text-align: left;
    margin-top: 20px;
}

.warning-note i {
    color: #EF4444;
    font-size: 1.3rem;
    flex-shrink: 0;
}

.warning-note span {
    color: #B91C1C;
    font-size: 0.9rem;
    line-height: 1.5;
}

.success-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin: 30px 0 20px;
}

.btn-primary {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
    border: none;
    padding: 14px 30px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
    text-decoration: none;
    box-shadow: 0 4px 6px rgba(139, 92, 246, 0.3);
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);
}

.btn-secondary {
    background: white;
    color: #8B5CF6;
    border: 2px solid #8B5CF6;
    padding: 12px 28px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-secondary:hover {
    background: #8B5CF6;
    color: white;
    transform: translateY(-2px);
}

.contact-support {
    text-align: center;
    padding-top: 20px;
    border-top: 2px solid #F1F5F9;
    color: #64748B;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.contact-support i {
    color: #F97316;
}

.contact-support a {
    color: #8B5CF6;
    text-decoration: none;
    font-weight: 600;
    margin-left: 5px;
}

.contact-support a:hover {
    text-decoration: underline;
}

/* Form Styles */
.auth-form {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 600;
    font-size: 0.95rem;
    color: #1E293B;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group label i {
    color: #8B5CF6;
}

.form-group input {
    padding: 14px 16px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    font-family: 'Inter', sans-serif;
}

.form-group input:focus {
    outline: none;
    border-color: #8B5CF6;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.form-group input:hover {
    border-color: #8B5CF6;
}

.input-hint {
    font-size: 0.9rem;
    color: #64748B;
    margin-top: 5px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.input-hint i {
    color: #F97316;
    font-size: 0.9rem;
}

.btn-block {
    width: 100%;
}

.auth-footer {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #F1F5F9;
}

.auth-footer p {
    color: #64748B;
    font-size: 0.95rem;
}

.auth-footer a {
    color: #8B5CF6;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.auth-footer a:hover {
    color: #F97316;
    text-decoration: underline;
}

/* Loading state */
.btn-primary.loading {
    position: relative;
    color: transparent;
    pointer-events: none;
}

.btn-primary.loading::after {
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

/* Responsive */
@media (max-width: 640px) {
    .auth-card {
        padding: 30px 20px;
    }
    
    .auth-header h2 {
        font-size: 1.6rem;
    }
    
    .auth-icon {
        width: 60px;
        height: 60px;
    }
    
    .auth-icon i {
        font-size: 2rem;
    }
    
    .success-icon {
        width: 80px;
        height: 80px;
    }
    
    .success-icon i {
        font-size: 2.8rem;
    }
    
    .success-message h2 {
        font-size: 1.5rem;
    }
    
    .info-item {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 10px;
    }
    
    .info-item i {
        margin-bottom: 5px;
    }
    
    .warning-note {
        flex-direction: column;
        text-align: center;
    }
    
    .success-actions {
        flex-direction: column;
    }
    
    .btn-primary, .btn-secondary {
        width: 100%;
    }
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
    .auth-container {
        background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
    }
    
    .auth-card {
        background: #1E293B;
    }
    
    .form-group label {
        color: #F1F5F9;
    }
    
    .form-group input {
        background: #0F172A;
        border-color: #334155;
        color: #F1F5F9;
    }
    
    .auth-footer {
        border-top-color: #334155;
    }
    
    .auth-footer p {
        color: #94A3B8;
    }
    
    .input-hint {
        color: #94A3B8;
    }
    
    .success-text {
        background: #334155;
        color: #F1F5F9;
    }
    
    .info-box {
        background: #334155;
    }
    
    .info-item {
        border-bottom-color: #475569;
    }
    
    .info-text strong {
        color: #F1F5F9;
    }
    
    .info-text span {
        color: #94A3B8;
    }
    
    .contact-support {
        border-top-color: #334155;
        color: #94A3B8;
    }
    
    .btn-secondary {
        background: transparent;
        color: #8B5CF6;
        border-color: #8B5CF6;
    }
    
    .btn-secondary:hover {
        background: #8B5CF6;
        color: white;
    }
}
</style>

<script>
document.getElementById('forgotForm')?.addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;
    const submitBtn = document.getElementById('submitBtn');
    
    if (!email) {
        e.preventDefault();
        alert('Please enter your email address');
        return;
    }
    
    // Simple email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address');
        return;
    }
    
    // Add loading state
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>