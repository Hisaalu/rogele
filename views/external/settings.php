<?php
// File: /views/external/settings.php
$pageTitle = 'Settings - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

$activeTab = $_GET['tab'] ?? 'password';
?>

<div class="settings-container">
    <!-- Header Section -->
    <div class="settings-header">
        <h1 class="page-title">
            <i class="fas fa-cog"></i>
            Settings
        </h1>
        <p class="page-subtitle">Manage your account settings and preferences</p>
    </div>

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
        <a href="?tab=delete" class="tab <?php echo $activeTab === 'delete' ? 'active' : ''; ?>">
            <i class="fas fa-trash"></i>
            <span>Delete Account</span>
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
                
                <form method="POST" action="/rays-of-grace/external/change-password" class="settings-form" id="passwordForm">
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

                    <button type="submit" class="btn-save" id="submitBtn">
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
                
                <form method="POST" action="/rays-of-grace/external/update-notifications" class="settings-form">
                    <div class="notification-group">
                        <h4>Email Notifications</h4>
                        
                        <label class="toggle-item">
                            <div class="toggle-info">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <strong>New Lessons</strong>
                                    <p>Get notified when new lessons are added</p>
                                </div>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="notify_new_lessons" checked>
                                <span class="slider"></span>
                            </div>
                        </label>

                        <label class="toggle-item">
                            <div class="toggle-info">
                                <i class="fas fa-pencil-alt"></i>
                                <div>
                                    <strong>Quiz Reminders</strong>
                                    <p>Get reminders about upcoming quizzes</p>
                                </div>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="notify_quizzes" checked>
                                <span class="slider"></span>
                            </div>
                        </label>

                        <label class="toggle-item">
                            <div class="toggle-info">
                                <i class="fas fa-star"></i>
                                <div>
                                    <strong>Learning Tips</strong>
                                    <p>Receive weekly learning tips and resources</p>
                                </div>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="notify_tips">
                                <span class="slider"></span>
                            </div>
                        </label>

                        <label class="toggle-item">
                            <div class="toggle-info">
                                <i class="fas fa-gift"></i>
                                <div>
                                    <strong>Promotions & Updates</strong>
                                    <p>Get updates about new features and offers</p>
                                </div>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="notify_promotions">
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
                
                <form method="POST" action="/rays-of-grace/external/update-privacy" class="settings-form">
                    <div class="privacy-group">
                        <label class="toggle-item">
                            <div class="toggle-info">
                                <i class="fas fa-globe"></i>
                                <div>
                                    <strong>Public Profile</strong>
                                    <p>Allow other users to see your profile</p>
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
                                    <strong>Show Progress</strong>
                                    <p>Display your learning progress publicly</p>
                                </div>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="show_progress">
                                <span class="slider"></span>
                            </div>
                        </label>

                        <label class="toggle-item">
                            <div class="toggle-info">
                                <i class="fas fa-trophy"></i>
                                <div>
                                    <strong>Leaderboard</strong>
                                    <p>Include me in public leaderboards</p>
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

        <?php elseif ($activeTab === 'delete'): ?>
            <!-- Delete Account Tab - FIXED VERSION -->
            <div class="settings-card delete-card">
                <div class="delete-header">
                    <div class="delete-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 class="card-title" style="color: #EF4444;">Delete Account</h3>
                    <p class="card-description">This action is permanent and cannot be undone</p>
                </div>
                
                <!-- Warning Box -->
                <div class="warning-box">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Warning:</strong> Deleting your account will:
                        <ul>
                            <li>Permanently remove all your personal information</li>
                            <li>Delete your quiz history and progress</li>
                            <li>Remove all bookmarked lessons</li>
                            <li>Cancel any active subscriptions (no refunds)</li>
                        </ul>
                    </div>
                </div>

                <!-- DELETE FORM - FIXED with proper action -->
                <form method="POST" action="/rays-of-grace/external/delete-account" class="delete-form" id="deleteForm">
                    <div class="form-group">
                        <label for="delete_password">
                            <i class="fas fa-lock"></i>
                            Enter your password to confirm
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="delete_password" 
                                name="password" 
                                required 
                                placeholder="Enter your current password"
                                autocomplete="off"
                            >
                        </div>
                    </div>

                    <label class="confirm-checkbox">
                        <input type="checkbox" name="confirm_delete" required>
                        <span>I understand that this action is permanent and cannot be undone</span>
                    </label>

                    <button type="submit" class="btn-delete" id="deleteAccountBtn">
                        <i class="fas fa-trash"></i>
                        Permanently Delete My Account
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

.page-title {
    font-size: 2.2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-subtitle {
    color: #64748B;
    font-size: 1rem;
    margin-bottom: 30px;
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
    color: #64748B;
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
    color: #8B5CF6;
}

.tab:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.15);
    border-color: #8B5CF6;
    color: #1E293B;
}

.tab.active {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
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
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.settings-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 50px rgba(139, 92, 246, 0.15);
}

.card-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1E293B;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-description {
    color: #64748B;
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
    color: #1E293B;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group label i {
    color: #8B5CF6;
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
    font-family: 'Inter', sans-serif;
}

.password-input-wrapper input:focus {
    outline: none;
    border-color: #8B5CF6;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.password-input-wrapper input:hover {
    border-color: #8B5CF6;
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
    color: #64748B;
}

.password-match {
    font-size: 0.85rem;
    margin-top: 5px;
}

/* Password Requirements */
.password-requirements {
    background: #F8FAFC;
    padding: 20px;
    border-radius: 12px;
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
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
}

.password-requirements li {
    font-size: 0.9rem;
    color: #64748B;
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
    color: #8B5CF6;
    font-size: 1.2rem;
}

.toggle-info strong {
    display: block;
    color: #1E293B;
    margin-bottom: 3px;
}

.toggle-info p {
    font-size: 0.85rem;
    color: #64748B;
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
    background: linear-gradient(135deg, #8B5CF6, #F97316);
}

input:checked + .slider:before {
    transform: translateX(24px);
}

/* Delete Account */
.delete-card {
    border: 2px solid #FEE2E2;
}

.warning-box {
    background: #FEF2F2;
    border: 1px solid #FECACA;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
    display: flex;
    gap: 15px;
    align-items: flex-start;
}

.warning-box i {
    color: #EF4444;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.warning-box strong {
    color: #B91C1C;
    display: block;
    margin-bottom: 10px;
}

.warning-box ul {
    margin-left: 20px;
    margin-top: 5px;
    color: #7F1D1D;
}

.warning-box li {
    margin-bottom: 5px;
}

.confirm-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 10px;
    background: #FEF2F2;
    border-radius: 12px;
    color: #B91C1C;
}

.confirm-checkbox input {
    width: 18px;
    height: 18px;
    accent-color: #EF4444;
}

.btn-delete {
    background: #EF4444;
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
    margin-top: 20px;
}

.btn-delete:hover {
    background: #DC2626;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(239, 68, 68, 0.4);
}

.btn-save {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
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

/* Responsive Design */
@media (max-width: 768px) {
    .settings-container {
        padding: 20px 15px;
    }
    
    .page-title {
        font-size: 1.8rem;
    }
    
    .settings-tabs {
        flex-direction: column;
    }
    
    .tab {
        width: 100%;
    }
    
    .settings-card {
        padding: 25px;
    }
    
    .toggle-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .toggle-switch {
        align-self: flex-end;
    }
    
    .password-requirements ul {
        grid-template-columns: 1fr;
    }
    
    .warning-box {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .warning-box ul {
        text-align: left;
    }
}

@media (max-width: 480px) {
    .page-title {
        font-size: 1.5rem;
    }
    
    .settings-card {
        padding: 20px;
    }
    
    .card-title {
        font-size: 1.3rem;
    }
    
    .toggle-info i {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }
    
    .btn-save, .btn-delete {
        padding: 14px;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .settings-card {
        background: #1E293B;
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
    
    .password-requirements {
        background: #334155;
    }
    
    .password-requirements p {
        color: #F1F5F9;
    }
    
    .tab {
        background: #1E293B;
        color: #94A3B8;
    }
    
    .tab:hover {
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

function confirmDelete() {
    return confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.');
}

// Enhanced delete form validation
function validateDeleteForm() {
    const password = document.getElementById('delete_password').value;
    const checkboxes = document.querySelectorAll('.delete-checkboxes input[type="checkbox"]');
    let allChecked = true;
    
    // Check if all checkboxes are checked
    checkboxes.forEach(cb => {
        if (!cb.checked) {
            allChecked = false;
        }
    });
    
    if (!password) {
        alert('Please enter your password to confirm account deletion.');
        return false;
    }
    
    if (!allChecked) {
        alert('Please check all confirmation boxes to proceed with account deletion.');
        return false;
    }
    
    if (password.length < 8) {
        alert('Please enter your correct password.');
        return false;
    }
    
    // Final confirmation
    const userConfirmed = confirm('⚠️ FINAL WARNING: Are you absolutely sure you want to permanently delete your account?\n\nThis action CANNOT be undone and all your data will be lost forever.');
    
    if (userConfirmed) {
        // Double-check with a more specific prompt
        const typeConfirmation = prompt('To confirm, please type "DELETE" in all caps:');
        return typeConfirmation === 'DELETE';
    }
    
    return false;
}

// Enable delete button only when all checkboxes are checked
// Make sure the delete form submits properly
document.addEventListener('DOMContentLoaded', function() {
    const deleteForm = document.getElementById('deleteForm');
    
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            const password = document.getElementById('delete_password').value;
            const checkbox = document.querySelector('input[name="confirm_delete"]');
            
            if (!password) {
                e.preventDefault();
                alert('Please enter your password');
                return false;
            }
            
            if (!checkbox.checked) {
                e.preventDefault();
                alert('Please confirm that you understand this action is permanent');
                return false;
            }
            
            // Final confirmation
            if (!confirm('⚠️ Are you absolutely sure you want to delete your account? This cannot be undone!')) {
                e.preventDefault();
                return false;
            }
            
            // Allow the form to submit normally
            return true;
        });
    }
});

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