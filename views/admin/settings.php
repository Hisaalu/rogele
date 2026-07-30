<?php
// File: /views/admin/settings.php
$pageTitle = 'Settings | ROGELE';
require_once __DIR__ . '/../layouts/admin_header.php';

$generalSettings = $generalSettings ?? [];
$subscriptionSettings = $subscriptionSettings ?? [];
$emailSettings = $emailSettings ?? [];
$securitySettings = $securitySettings ?? [];
$appearanceSettings = $appearanceSettings ?? [];
?>

<style>
:root {
    --primary-purple: #7f2677;
    --primary-orange: #f06724;
    --text-dark: #000;
    --text-light: #555;
    --bg-light: #F8FAFC;
    --border-color: #E2E8F0;
    --card-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
    --danger-red: #EF4444;
    --danger-bg: #FEF2F2;
    --success-green: #10B981;
}

.settings-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: clamp(20px, 4vw, 40px) clamp(15px, 2vw, 20px);
    width: 100%;
}

.settings-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 35px;
    flex-wrap: wrap;
    gap: 20px;
}

.page-title {
    font-size: clamp(1.8rem, 4vw, 2.2rem);
    font-weight: 700;
    color: var(--primary-purple);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-subtitle {
    color: var(--text-dark);
    font-size: 0.95rem;
}

.btn-save-all {
    background-color: var(--primary-purple);
    color: white;
    border: none;
    padding: 14px 32px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.2s ease;
    box-shadow: 0 4px 15px rgba(127, 38, 119, 0.2);
}

.btn-save-all:hover {
    background-color: var(--primary-orange);
    transform: translateY(-2px);
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 25px;
    margin-bottom: 35px;
}

.settings-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--card-shadow);
    transition: all 0.3s ease;
    border: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
}

.settings-card:hover {
    transform: translateY(-4px);
    border-color: var(--primary-orange);
}

.card-header {
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 15px;
    border-bottom: 2px solid var(--bg-light);
}

.card-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(240, 103, 36, 0.1);
    color: var(--primary-orange);
    font-size: 1.4rem;
    flex-shrink: 0;
}

.card-title {
    flex: 1;
}

.card-title h2 {
    color: var(--text-dark);
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 4px;
}

.card-title p {
    color: var(--text-light);
    font-size: 0.85rem;
}

.card-badge {
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    background: var(--bg-light);
    color: var(--text-light);
}

.card-badge.success { background: #F0FDF4; color: #166534; }
.card-badge.warning { background: #FEF3C7; color: #92400E; }

.card-body {
    padding: 25px;
    flex: 1;
}

.settings-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
    height: 100%;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group label i {
    color: var(--primary-orange);
}

.required {
    color: var(--danger-red);
    margin-left: 2px;
}

.form-group input:not([type="checkbox"]):not([type="color"]),
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid var(--primary-orange);
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    background: white;
}

.form-group input:focus,
.input-wrapper input:focus,
.form-group textarea:focus,
.input-wrapper textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary-orange);
    box-shadow: 0 0 0 3px rgba(240, 103, 36, 0.15);
}

.input-hint {
    font-size: 0.8rem;
    color: var(--text-light);
    margin-top: 2px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.price-inputs {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.price-group {
    position: relative;
    background: var(--bg-light);
    padding: 15px;
    border-radius: 12px;
    border: 1px solid var(--border-color);
}

.currency-input {
    display: flex;
    align-items: center;
    gap: 8px;
}

.currency-symbol {
    padding: 10px 14px;
    background-color: var(--primary-purple);
    color: white;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
}

.save-badge {
    position: absolute;
    top: -8px;
    right: 10px;
    background: var(--success-green);
    color: white;
    padding: 3px 10px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
}

.save-badge.popular { background: var(--primary-orange); }

.toggle-group {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    background: var(--bg-light);
    padding: 15px;
    border-radius: 12px;
    border: 1px solid var(--border-color);
}

.toggle-label {
    display: flex;
    align-items: center;
    gap: 15px;
}

.toggle-label i {
    font-size: 1.2rem;
    color: var(--primary-orange);
}

.toggle-label strong {
    display: block;
    color: var(--text-dark);
    font-size: 0.95rem;
}

.toggle-label p {
    color: var(--text-light);
    font-size: 0.85rem;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
    flex-shrink: 0;
}

.toggle-switch input { opacity: 0; width: 0; height: 0; }

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #CBD5E1;
    transition: .2s;
    border-radius: 34px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .2s;
    border-radius: 50%;
}

input:checked + .toggle-slider { background-color: var(--primary-orange); }
input:checked + .toggle-slider:before { transform: translateX(24px); }

.password-wrapper { position: relative; }
.password-wrapper input { padding-right: 45px !important; }

.toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-light);
    cursor: pointer;
    padding: 5px;
}

.color-input-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
}

.color-input-wrapper input[type="color"] {
    width: 50px;
    height: 50px;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    cursor: pointer;
    padding: 4px;
    background: transparent;
}

.color-value {
    padding: 12px 16px;
    background: var(--bg-light);
    border-radius: 10px;
    font-family: monospace;
    font-size: 0.95rem;
    color: var(--text-dark);
    border: 2px solid var(--border-color);
}

.card-footer {
    padding-top: 20px;
    margin-top: auto;
    border-top: 2px solid var(--bg-light);
    display: flex;
    gap: 10px;
}

.btn-save {
    flex: 1;
    background-color: var(--primary-purple);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.btn-save:hover { background-color: var(--primary-orange); }

.btn-test, .btn-preview {
    background: var(--bg-light);
    color: var(--text-dark);
    border: 2px solid var(--border-color);
    padding: 12px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.btn-test:hover, .btn-preview:hover {
    background: white;
    border-color: var(--primary-orange);
    color: var(--primary-orange);
}

.danger-zone {
    background: var(--danger-bg);
    border: 2px solid #FECACA;
    border-radius: 20px;
    overflow: hidden;
    margin-top: 40px;
}

.danger-header {
    padding: 20px 25px;
    background: #FEE2E2;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 2px solid #FECACA;
    color: #B91C1C;
}

.danger-header h3 { font-size: 1.2rem; font-weight: 600; }

.danger-content {
    padding: 25px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.danger-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px;
    background: white;
    border-radius: 12px;
    border: 1px solid #FECACA;
    gap: 20px;
}

.danger-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.danger-info i { font-size: 1.3rem; color: var(--danger-red); }
.danger-info strong { display: block; color: #B91C1C; margin-bottom: 2px; }
.danger-info p { color: #7F1D1D; font-size: 0.85rem; }

.btn-danger {
    background: var(--danger-red);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background 0.2s ease;
    white-space: nowrap;
    text-decoration: none;
}

.btn-danger:hover { background: #DC2626; }

.alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 15px;
    position: relative;
    border: 1px solid transparent;
}

.alert-success { background: #F0FDF4; color: #166534; border-color: #BBF7D0; }
.alert-error { background: var(--danger-bg); color: #B91C1C; border-color: #FECACA; }
.alert-content { flex: 1; }
.alert-content strong { display: block; margin-bottom: 2px; }
.alert-content p { font-size: 0.9rem; }

.alert-close {
    background: none;
    border: none;
    font-size: 1.3rem;
    cursor: pointer;
    color: currentColor;
    opacity: 0.6;
}

@media (max-width: 992px) {
    .settings-grid { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
    .settings-header { flex-direction: column; align-items: flex-start; }
    .btn-save-all { width: 100%; justify-content: center; }
    .form-row { grid-template-columns: 1fr; }
    .card-footer { flex-direction: column; }
    .danger-item { flex-direction: column; align-items: flex-start; }
    .btn-danger { width: 100%; justify-content: center; }
    .toggle-group { flex-direction: column; align-items: flex-start; gap: 12px; }
    .toggle-switch { align-self: flex-end; }
}

@media (max-width: 480px) {
    .card-header { flex-direction: column; text-align: center; }
    .currency-input { flex-direction: column; align-items: stretch; }
    .currency-symbol { text-align: center; }
}
</style>

<div class="settings-container">
    <div class="settings-header">
        <div>
            <h1 class="page-title"><i class="fas fa-sliders-h"></i>System Configuration</h1>
            <p class="page-subtitle">Customize and manage your platform settings</p>
        </div>
        <form method="POST" action="<?php echo BASE_URL; ?>/admin/settings/save-all">
            <button type="submit" class="btn-save-all"><i class="fas fa-save"></i>Save All Changes</button>
        </form>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <div class="alert-content">
                <strong>Success!</strong>
                <p><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()">×</button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <div class="alert-content">
                <strong>Error!</strong>
                <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()">×</button>
        </div>
    <?php endif; ?>

    <div class="settings-grid">
        <!-- General Settings Panel -->
        <div class="settings-card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-globe"></i></div>
                <div class="card-title">
                    <h2>General Settings</h2>
                    <p>Basic platform information</p>
                </div>
                <span class="card-badge">Required</span>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>/admin/settings/general" class="settings-form">
                    <div class="form-group">
                        <label for="site_name"><i class="fas fa-tag"></i>Site Name<span class="required">*</span></label>
                        <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($generalSettings['site_name'] ?? 'Rays of Grace E-Learning'); ?>" required>
                        <span class="input-hint">This will appear in the browser title</span>
                    </div>

                    <div class="form-group">
                        <label for="site_description"><i class="fas fa-align-left"></i>Site Description</label>
                        <textarea id="site_description" name="site_description" rows="3"><?php echo htmlspecialchars($generalSettings['site_description'] ?? 'Quality education for every child, anywhere, anytime.'); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="contact_email"><i class="fas fa-envelope"></i>Contact Email<span class="required">*</span></label>
                        <input type="email" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($generalSettings['contact_email'] ?? 'info@raysofgrace.com'); ?>" required>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn-save"><i class="fas fa-save"></i>Save General Settings</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Subscription Settings Panel -->
        <div class="settings-card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-credit-card"></i></div>
                <div class="card-title">
                    <h2>Subscription Plans</h2>
                    <p>Configure pricing and plans</p>
                </div>
                <span class="card-badge success">Active</span>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>/admin/settings/subscription" class="settings-form">
                    <div class="price-inputs">
                        <div class="form-group price-group">
                            <label for="monthly_price"><i class="fas fa-calendar-alt"></i>Monthly</label>
                            <div class="currency-input">
                                <span class="currency-symbol">UGX</span>
                                <input type="number" id="monthly_price" name="monthly_price" value="<?php echo htmlspecialchars($subscriptionSettings['monthly_price'] ?? 15000); ?>" min="0" step="any">
                            </div>
                        </div>

                        <div class="form-group price-group">
                            <label for="termly_price"><i class="fas fa-calendar-week"></i>Termly</label>
                            <div class="currency-input">
                                <span class="currency-symbol">UGX</span>
                                <input type="number" id="termly_price" name="termly_price" value="<?php echo htmlspecialchars($subscriptionSettings['termly_price'] ?? 40000); ?>" min="0" step="any">
                            </div>
                            <span class="save-badge">Save 17%</span>
                        </div>

                        <div class="form-group price-group">
                            <label for="yearly_price"><i class="fas fa-calendar"></i>Yearly</label>
                            <div class="currency-input">
                                <span class="currency-symbol">UGX</span>
                                <input type="number" id="yearly_price" name="yearly_price" value="<?php echo htmlspecialchars($subscriptionSettings['yearly_price'] ?? 120000); ?>" min="0" step="any">
                            </div>
                            <span class="save-badge popular">Save 25%</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="trial_days"><i class="fas fa-gift"></i>Free Trial Days</label>
                        <input type="number" id="trial_days" name="trial_days" value="<?php echo htmlspecialchars($subscriptionSettings['trial_days'] ?? 60); ?>" min="0" max="365">
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn-save"><i class="fas fa-save"></i>Update Plans</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Email Settings Panel -->
        <div class="settings-card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-mail-bulk"></i></div>
                <div class="card-title">
                    <h2>Email Configuration</h2>
                    <p>SMTP and outbound settings</p>
                </div>
                <span class="card-badge warning">Test Needed</span>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>/admin/settings/email" class="settings-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="smtp_host"><i class="fas fa-server"></i>SMTP Host</label>
                            <input type="text" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($emailSettings['smtp_host'] ?? 'smtp.gmail.com'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="smtp_port"><i class="fas fa-plug"></i>SMTP Port</label>
                            <input type="number" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($emailSettings['smtp_port'] ?? 587); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="smtp_username"><i class="fas fa-user"></i>SMTP Username</label>
                        <input type="email" id="smtp_username" name="smtp_username" value="<?php echo htmlspecialchars($emailSettings['smtp_username'] ?? 'noreply@raysofgrace.com'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="smtp_password"><i class="fas fa-lock"></i>SMTP Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="smtp_password" name="smtp_password" value="<?php echo htmlspecialchars($emailSettings['smtp_password'] ?? ''); ?>">
                            <button type="button" class="toggle-password" onclick="togglePassword('smtp_password')"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="from_email"><i class="fas fa-paper-plane"></i>From Email</label>
                        <input type="email" id="from_email" name="from_email" value="<?php echo htmlspecialchars($emailSettings['from_email'] ?? 'noreply@raysofgrace.com'); ?>">
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn-save"><i class="fas fa-save"></i>Save Email</button>
                        <button type="button" class="btn-test" onclick="window.location.href='<?php echo BASE_URL; ?>/admin/settings/test-email'"><i class="fas fa-vial"></i>Test Connection</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security Settings Panel -->
        <div class="settings-card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="card-title">
                    <h2>Security Settings</h2>
                    <p>Password and session configuration</p>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>/admin/settings/security" class="settings-form">
                    <div class="form-group toggle-group">
                        <div class="toggle-label">
                            <i class="fas fa-lock"></i>
                            <div>
                                <strong>Two-Factor Authentication</strong>
                                <p>Require 2FA for admin accounts</p>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="enable_2fa" <?php echo ($securitySettings['enable_2fa'] ?? true) ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="form-group toggle-group">
                        <div class="toggle-label">
                            <i class="fas fa-history"></i>
                            <div>
                                <strong>Session Timeout</strong>
                                <p>Auto-logout after inactivity</p>
                            </div>
                        </div>
                        <div class="select-wrapper">
                            <select name="session_timeout">
                                <option value="60" <?php echo ($securitySettings['session_timeout'] ?? 60) == 60 ? 'selected' : ''; ?>>1 hour</option>
                                <option value="30" <?php echo ($securitySettings['session_timeout'] ?? 60) == 30 ? 'selected' : ''; ?>>30 mins</option>
                                <option value="15" <?php echo ($securitySettings['session_timeout'] ?? 60) == 15 ? 'selected' : ''; ?>>15 mins</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group toggle-group">
                        <div class="toggle-label">
                            <i class="fas fa-key"></i>
                            <div>
                                <strong>Password Complexity</strong>
                                <p>Require strong passwords</p>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="strong_passwords" <?php echo ($securitySettings['strong_passwords'] ?? true) ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn-save"><i class="fas fa-save"></i>Save Security</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Appearance Settings Panel -->
        <div class="settings-card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-paint-brush"></i></div>
                <div class="card-title">
                    <h2>Appearance</h2>
                    <p>Customize the look and feel</p>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>/admin/settings/appearance" class="settings-form">
                    <div class="form-group">
                        <label for="theme_color"><i class="fas fa-palette"></i>Theme Color</label>
                        <div class="color-input-wrapper">
                            <input type="color" id="theme_color" name="theme_color" value="<?php echo htmlspecialchars($appearanceSettings['theme_color'] ?? '#7f2677'); ?>">
                            <span class="color-value"><?php echo htmlspecialchars($appearanceSettings['theme_color'] ?? '#7f2677'); ?></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="accent_color"><i class="fas fa-palette"></i>Accent Color</label>
                        <div class="color-input-wrapper">
                            <input type="color" id="accent_color" name="accent_color" value="<?php echo htmlspecialchars($appearanceSettings['accent_color'] ?? '#f06724'); ?>">
                            <span class="color-value"><?php echo htmlspecialchars($appearanceSettings['accent_color'] ?? '#f06724'); ?></span>
                        </div>
                    </div>

                    <div class="form-group toggle-group">
                        <div class="toggle-label">
                            <i class="fas fa-moon"></i>
                            <div>
                                <strong>Dark Mode</strong>
                                <p>Enable dark mode by default</p>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="dark_mode" <?php echo ($appearanceSettings['dark_mode'] ?? true) ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn-save"><i class="fas fa-save"></i>Save Appearance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Danger Zone Panel -->
    <div class="danger-zone">
        <div class="danger-header">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Danger Zone</h3>
        </div>
        <div class="danger-content">
            <div class="danger-item">
                <div class="danger-info">
                    <i class="fas fa-database"></i>
                    <div>
                        <strong>Clear System Cache</strong>
                        <p>Remove all cached data and temporary records</p>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>/admin/settings/clear-cache" class="btn-danger" onclick="return confirm('Clear system cache? Performance may drop temporarily.')"><i class="fas fa-broom"></i>Clear Cache</a>
            </div>
            
            <div class="danger-item">
                <div class="danger-info">
                    <i class="fas fa-rotate-left"></i>
                    <div>
                        <strong>Reset to Defaults</strong>
                        <p>Restore all application settings to factory values</p>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>/admin/settings/reset-defaults" class="btn-danger" onclick="return confirm('WARNING: Reset ALL settings? This action cannot be reversed.')"><i class="fas fa-undo-alt"></i>Reset All</a>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = event.currentTarget.querySelector('i');
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    icon.classList.toggle('fa-eye', !isPassword);
    icon.classList.toggle('fa-eye-slash', isPassword);
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>