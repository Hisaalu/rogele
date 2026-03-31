<?php
// File: /views/teacher/settings.php
$pageTitle = 'Settings | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$activeTab = $_GET['tab'] ?? 'password';
?>

<div class="settings-container">
    <!-- Header -->
    <div class="settings-header">
        <h1 class="page-title">
            <i class="fas fa-cog"></i>
            Settings
        </h1>
        <p class="page-subtitle">Manage your account settings and preferences</p>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <!-- Settings Tabs -->
    <div class="settings-tabs">
        <a href="?tab=password" class="tab <?php echo $activeTab === 'password' ? 'active' : ''; ?>">
            <i class="fas fa-lock"></i>
            <span>Change Password</span>
        </a>
        <a href="?tab=notifications" class="tab <?php echo $activeTab === 'notifications' ? 'active' : ''; ?>">
            <i class="fas fa-bell"></i>
            <span>Notifications</span>
        </a>
        <a href="?tab=privacy" class="tab <?php echo $activeTab === 'privacy' ? 'active' : ''; ?>">
            <i class="fas fa-shield-alt"></i>
            <span>Privacy</span>
        </a>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
        <?php if ($activeTab === 'password'): ?>
            <!-- Change Password Tab -->
            <div class="settings-card">
                <h3 class="card-title">
                    <i class="fas fa-key"></i>
                    Change Password
                </h3>
                <p class="card-description">Ensure your account is secure by using a strong password</p>
                
                <form method="POST" action="<?php echo BASE_URL; ?>/teacher/change-password" class="settings-form" id="passwordForm">
                    <div class="form-group">
                        <label for="current_password">
                            <i class="fas fa-lock"></i>
                            Current Password
                        </label>
                        <div class="password-input-wrapper">
                            <input type="password" id="current_password" name="current_password" required placeholder="Enter your current password">
                            <button type="button" class="toggle-password" onclick="togglePassword('current_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="new_password">
                            <i class="fas fa-key"></i>
                            New Password
                        </label>
                        <div class="password-input-wrapper">
                            <input type="password" id="new_password" name="new_password" required placeholder="Enter new password" onkeyup="checkPasswordStrength()">
                            <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
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
                            Confirm New Password
                        </label>
                        <div class="password-input-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm new password" onkeyup="checkPasswordMatch()">
                            <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-match" id="passwordMatch"></div>
                    </div>

                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i>
                        Update Password
                    </button>
                </form>
            </div>

        <?php elseif ($activeTab === 'notifications'): ?>
            <!-- Notifications Tab -->
            <div class="settings-card">
                <h3 class="card-title">
                    <i class="fas fa-bell"></i>
                    Notification Preferences
                </h3>
                <p class="card-description">Choose what updates you want to receive</p>
                
                <form method="POST" action="<?php echo BASE_URL; ?>/teacher/update-notifications" class="settings-form">
                    <div class="notification-group">
                        <h4>Email Notifications</h4>
                        
                        <label class="toggle-item">
                            <div class="toggle-info">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <strong>New Student Registration</strong>
                                    <p>Get notified when new students join your class</p>
                                </div>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="notify_new_students" checked>
                                <span class="slider"></span>
                            </div>
                        </label>

                        <label class="toggle-item">
                            <div class="toggle-info">
                                <i class="fas fa-pencil-alt"></i>
                                <div>
                                    <strong>Quiz Submissions</strong>
                                    <p>Get notified when students submit quizzes</p>
                                </div>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="notify_quiz_submissions" checked>
                                <span class="slider"></span>
                            </div>
                        </label>

                        <label class="toggle-item">
                            <div class="toggle-info">
                                <i class="fas fa-star"></i>
                                <div>
                                    <strong>Low Performance Alerts</strong>
                                    <p>Get alerts when students score below 50%</p>
                                </div>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="notify_low_performance" checked>
                                <span class="slider"></span>
                            </div>
                        </label>

                        <label class="toggle-item">
                            <div class="toggle-info">
                                <i class="fas fa-gift"></i>
                                <div>
                                    <strong>Weekly Reports</strong>
                                    <p>Receive weekly performance summaries</p>
                                </div>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="notify_weekly_reports">
                                <span class="slider"></span>
                            </div>
                        </label>
                    </div>

                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i>
                        Save Preferences
                    </button>
                </form>
            </div>

        <?php elseif ($activeTab === 'privacy'): ?>
            <!-- Privacy Tab -->
            <div class="settings-card">
                <h3 class="card-title">
                    <i class="fas fa-shield-alt"></i>
                    Privacy Settings
                </h3>
                <p class="card-description">Control your privacy and data sharing preferences</p>
                
                <form method="POST" action="<?php echo BASE_URL; ?>/teacher/update-privacy" class="settings-form">
                    <div class="privacy-group">
                        <label class="toggle-item">
                            <div class="toggle-info">
                                <i class="fas fa-globe"></i>
                                <div>
                                    <strong>Public Profile</strong>
                                    <p>Allow students to see your profile</p>
                                </div>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="public_profile" checked>
                                <span class="slider"></span>
                            </div>
                        </label>

                        <label class="toggle-item">
                            <div class="toggle-info">
                                <i class="fas fa-chart-line"></i>
                                <div>
                                    <strong>Show Contact Info</strong>
                                    <p>Display your email to students</p>
                                </div>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="show_contact" checked>
                                <span class="slider"></span>
                            </div>
                        </label>

                        <label class="toggle-item">
                            <div class="toggle-info">
                                <i class="fas fa-trophy"></i>
                                <div>
                                    <strong>Leaderboard</strong>
                                    <p>Include your students in public leaderboards</p>
                                </div>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="leaderboard" checked>
                                <span class="slider"></span>
                            </div>
                        </label>

                        <label class="toggle-item">
                            <div class="toggle-info">
                                <i class="fas fa-analytics"></i>
                                <div>
                                    <strong>Data Collection</strong>
                                    <p>Allow anonymous usage data collection</p>
                                </div>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="data_collection" checked>
                                <span class="slider"></span>
                            </div>
                        </label>
                    </div>

                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i>
                        Save Privacy Settings
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.settings-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 40px 20px;
}

.settings-header {
    margin-bottom: 30px;
}

.page-title {
    font-size: 2.2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #7f2677);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-subtitle {
    color: black;
    font-size: 1rem;
}

/* Alerts */
.alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideDown 0.3s ease;
}

.alert-success {
    background: #F0FDF4;
    color: #166534;
    border: 1px solid #BBF7D0;
}

.alert-error {
    background: #FEF2F2;
    color: #B91C1C;
    border: 1px solid #FECACA;
}

@keyframes slideDown {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Settings Tabs */
.settings-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.tab {
    flex: 1;
    min-width: 120px;
    padding: 15px 20px;
    background: white;
    border-radius: 12px;
    text-decoration: none;
    color: black;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.tab i {
    font-size: 1.2rem;
    color: #f06724;
}

.tab:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.15);
    border-color: #f06724;
    color: black;
}

.tab.active {
    background: linear-gradient(135deg, #7f2677);
    color: white;
}

.tab.active i {
    color: white;
}

/* Settings Card */
.settings-card {
    background: white;
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
}

.card-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #f06724;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-description {
    color: black;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #F1F5F9;
}

/* Forms */
.settings-form {
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
    color: black;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group label i {
    color: #f06724;
}

.password-input-wrapper {
    position: relative;
}

.password-input-wrapper input {
    width: 100%;
    padding: 14px 45px 14px 16px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.password-input-wrapper input:focus {
    outline: none;
    border-color: #f06724;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
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
    color: #f06724;
}

/* Password Strength */
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
    color: black;
}

.password-match {
    font-size: 0.85rem;
    margin-top: 5px;
}

/* Toggle Switches */
.toggle-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px;
    border-radius: 12px;
    background: #F8FAFC;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 10px;
}

.toggle-item:hover {
    background: #F1F5F9;
    transform: translateX(5px);
}

.toggle-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.toggle-info i {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(249, 115, 22, 0.1));
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #f06724;
    font-size: 1.2rem;
}

.toggle-info strong {
    display: block;
    color: black;
    margin-bottom: 3px;
}

.toggle-info p {
    font-size: 0.85rem;
    color: black;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #CBD5E1;
    transition: .3s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
}

input:checked + .slider {
    background: linear-gradient(135deg, #f06724, #F97316);
}

input:checked + .slider:before {
    transform: translateX(24px);
}

/* Button */
.btn-save {
    background: linear-gradient(135deg, #7f2677);
    color: white;
    border: none;
    padding: 16px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);
}

/* Responsive */
@media (max-width: 768px) {
    .settings-card {
        padding: 25px;
    }
    
    .settings-tabs {
        flex-direction: column;
    }
    
    .tab {
        width: 100%;
    }
    
    .toggle-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .toggle-switch {
        align-self: flex-end;
    }
}

/* Dark Mode */
/* @media (prefers-color-scheme: dark) {
    .settings-card {
        background: black;
    }
    
    .card-title {
        color: #F1F5F9;
    }
    
    .card-description {
        border-bottom-color: #334155;
    }
    
    .form-group label {
        color: #F1F5F9;
    }
    
    .password-input-wrapper input {
        background: #0F172A;
        border-color: #334155;
        color: #F1F5F9;
    }
    
    .toggle-item {
        background: #334155;
    }
    
    .toggle-item:hover {
        background: #475569;
    }
    
    .toggle-info strong {
        color: #F1F5F9;
    }
    
    .tab {
        background: black;
        color: #94A3B8;
    }
    
    .tab:hover {
        color: #F1F5F9;
    }
} */
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
    const password = document.getElementById('new_password').value;
    const strengthBars = document.querySelectorAll('.strength-segment');
    const strengthText = document.querySelector('.strength-text');
    
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[$@#&!]+/)) strength++;
    
    strengthBars.forEach((bar, index) => {
        if (index < strength) {
            if (strength <= 2) bar.style.background = '#EF4444';
            else if (strength <= 3) bar.style.background = '#F97316';
            else if (strength <= 4) bar.style.background = '#EAB308';
            else bar.style.background = '#10B981';
        } else {
            bar.style.background = '#E2E8F0';
        }
    });
    
    const texts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
    strengthText.textContent = strength > 0 ? texts[strength - 1] : 'Enter a password';
    strengthText.style.color = strength <= 2 ? '#EF4444' : strength <= 3 ? '#F97316' : strength <= 4 ? '#EAB308' : '#10B981';
    
    // Update requirements
    document.getElementById('req-length').className = password.length >= 8 ? 'valid' : '';
    document.getElementById('req-uppercase').className = /[A-Z]/.test(password) ? 'valid' : '';
    document.getElementById('req-lowercase').className = /[a-z]/.test(password) ? 'valid' : '';
    document.getElementById('req-number').className = /[0-9]/.test(password) ? 'valid' : '';
    document.getElementById('req-special').className = /[$@#&!]/.test(password) ? 'valid' : '';
    
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
    const password = document.getElementById('new_password').value;
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
document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
    const password = document.getElementById('new_password').value;
    const confirm = document.getElementById('confirm_password').value;
    
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
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>