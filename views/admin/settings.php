<?php
// File: /views/admin/settings.php
$pageTitle = 'Settings - Admin - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="settings-container">
    <!-- Header Section -->
    <div class="settings-header">
        <div class="header-content">
            <h1 class="page-title">
                <i class="fas fa-sliders-h"></i>
                System Configuration
            </h1>
            <p class="page-subtitle">Customize and manage your platform settings</p>
        </div>
        <div class="header-actions">
            <button class="btn-save-all" onclick="saveAllSettings()">
                <i class="fas fa-save"></i>
                Save All Changes
            </button>
        </div>
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

    <!-- Settings Grid -->
    <div class="settings-grid">
        <!-- General Settings Card -->
        <div class="settings-card">
            <div class="card-header">
                <div class="card-icon" style="background: linear-gradient(135deg, #8B5CF6, #7C3AED);">
                    <i class="fas fa-globe"></i>
                </div>
                <div class="card-title">
                    <h2>General Settings</h2>
                    <p>Basic platform information</p>
                </div>
                <span class="card-badge">Required</span>
            </div>
            
            <div class="card-body">
                <form class="settings-form" id="generalSettingsForm">
                    <div class="form-group">
                        <label for="site_name">
                            <i class="fas fa-tag"></i>
                            Site Name
                            <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input 
                                type="text" 
                                id="site_name" 
                                name="site_name" 
                                value="Rays of Grace E-Learning" 
                                placeholder="Enter your site name"
                                required
                            >
                            <span class="input-hint">This will appear in the browser title</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="site_description">
                            <i class="fas fa-align-left"></i>
                            Site Description
                        </label>
                        <div class="input-wrapper">
                            <textarea 
                                id="site_description" 
                                name="site_description" 
                                rows="3" 
                                placeholder="Describe your platform"
                            >Quality education for every child, anywhere, anytime.</textarea>
                            <span class="input-hint">Brief description for SEO and sharing</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contact_email">
                            <i class="fas fa-envelope"></i>
                            Contact Email
                            <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input 
                                type="email" 
                                id="contact_email" 
                                name="contact_email" 
                                value="info@raysofgrace.com" 
                                placeholder="contact@example.com"
                                required
                            >
                            <span class="input-hint">Primary email for system notifications</span>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-footer">
                <button class="btn-save" onclick="saveSettings('general')">
                    <i class="fas fa-save"></i>
                    Save General Settings
                </button>
                <button class="btn-reset" onclick="resetForm('general')">
                    <i class="fas fa-undo"></i>
                    Reset
                </button>
            </div>
        </div>

        <!-- Subscription Plans Card -->
        <div class="settings-card">
            <div class="card-header">
                <div class="card-icon" style="background: linear-gradient(135deg, #F97316, #EA580C);">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="card-title">
                    <h2>Subscription Plans</h2>
                    <p>Configure pricing and plans</p>
                </div>
                <span class="card-badge success">Active</span>
            </div>
            
            <div class="card-body">
                <form class="settings-form" id="subscriptionForm">
                    <div class="price-inputs">
                        <div class="form-group price-group">
                            <label for="monthly_price">
                                <i class="fas fa-calendar-alt"></i>
                                Monthly
                            </label>
                            <div class="currency-input">
                                <span class="currency-symbol">UGX</span>
                                <input 
                                    type="number" 
                                    id="monthly_price" 
                                    name="monthly_price" 
                                    value="15000" 
                                    min="0" 
                                    step="1000"
                                >
                            </div>
                        </div>

                        <div class="form-group price-group">
                            <label for="termly_price">
                                <i class="fas fa-calendar-week"></i>
                                Termly
                            </label>
                            <div class="currency-input">
                                <span class="currency-symbol">UGX</span>
                                <input 
                                    type="number" 
                                    id="termly_price" 
                                    name="termly_price" 
                                    value="40000" 
                                    min="0" 
                                    step="1000"
                                >
                            </div>
                            <span class="save-badge">Save 11%</span>
                        </div>

                        <div class="form-group price-group">
                            <label for="yearly_price">
                                <i class="fas fa-calendar"></i>
                                Yearly
                            </label>
                            <div class="currency-input">
                                <span class="currency-symbol">UGX</span>
                                <input 
                                    type="number" 
                                    id="yearly_price" 
                                    name="yearly_price" 
                                    value="120000" 
                                    min="0" 
                                    step="1000"
                                >
                            </div>
                            <span class="save-badge popular">Save 33%</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="trial_days">
                            <i class="fas fa-gift"></i>
                            Free Trial Days
                        </label>
                        <div class="input-wrapper">
                            <input 
                                type="number" 
                                id="trial_days" 
                                name="trial_days" 
                                value="60" 
                                min="0" 
                                max="365"
                            >
                            <span class="input-hint">Number of days for free trial (0 to disable)</span>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-footer">
                <button class="btn-save" onclick="saveSettings('subscription')">
                    <i class="fas fa-save"></i>
                    Update Plans
                </button>
                <button class="btn-reset" onclick="resetForm('subscription')">
                    <i class="fas fa-undo"></i>
                    Reset
                </button>
            </div>
        </div>

        <!-- Email Configuration Card -->
        <div class="settings-card">
            <div class="card-header">
                <div class="card-icon" style="background: linear-gradient(135deg, #10B981, #059669);">
                    <i class="fas fa-mail-bulk"></i>
                </div>
                <div class="card-title">
                    <h2>Email Configuration</h2>
                    <p>SMTP and email settings</p>
                </div>
                <span class="card-badge warning">Test Required</span>
            </div>
            
            <div class="card-body">
                <form class="settings-form" id="emailForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="smtp_host">
                                <i class="fas fa-server"></i>
                                SMTP Host
                            </label>
                            <input 
                                type="text" 
                                id="smtp_host" 
                                name="smtp_host" 
                                value="smtp.gmail.com" 
                                placeholder="e.g., smtp.gmail.com"
                            >
                        </div>

                        <div class="form-group">
                            <label for="smtp_port">
                                <i class="fas fa-plug"></i>
                                SMTP Port
                            </label>
                            <input 
                                type="number" 
                                id="smtp_port" 
                                name="smtp_port" 
                                value="587" 
                                placeholder="587"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="smtp_username">
                            <i class="fas fa-user"></i>
                            SMTP Username
                        </label>
                        <input 
                            type="email" 
                            id="smtp_username" 
                            name="smtp_username" 
                            value="noreply@raysofgrace.com" 
                            placeholder="email@example.com"
                        >
                    </div>

                    <div class="form-group">
                        <label for="smtp_password">
                            <i class="fas fa-lock"></i>
                            SMTP Password
                        </label>
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                id="smtp_password" 
                                name="smtp_password" 
                                value="password123" 
                                placeholder="Enter your password"
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword('smtp_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="from_email">
                            <i class="fas fa-paper-plane"></i>
                            From Email
                        </label>
                        <input 
                            type="email" 
                            id="from_email" 
                            name="from_email" 
                            value="noreply@raysofgrace.com" 
                            placeholder="noreply@example.com"
                        >
                    </div>
                </form>
            </div>
            
            <div class="card-footer">
                <button class="btn-save" onclick="saveSettings('email')">
                    <i class="fas fa-save"></i>
                    Save Email Settings
                </button>
                <button class="btn-test" onclick="testEmailConfig()">
                    <i class="fas fa-vial"></i>
                    Test Connection
                </button>
            </div>
        </div>

        <!-- Security Settings Card -->
        <div class="settings-card">
            <div class="card-header">
                <div class="card-icon" style="background: linear-gradient(135deg, #EC4899, #DB2777);">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="card-title">
                    <h2>Security Settings</h2>
                    <p>Password and session configuration</p>
                </div>
            </div>
            
            <div class="card-body">
                <form class="settings-form" id="securityForm">
                    <div class="form-group toggle-group">
                        <div class="toggle-label">
                            <i class="fas fa-lock"></i>
                            <div>
                                <strong>Two-Factor Authentication</strong>
                                <p>Require 2FA for admin accounts</p>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="enable_2fa" checked>
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
                                <option value="30">30 minutes</option>
                                <option value="60" selected>1 hour</option>
                                <option value="120">2 hours</option>
                                <option value="240">4 hours</option>
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
                            <input type="checkbox" name="strong_passwords" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </form>
            </div>
            
            <div class="card-footer">
                <button class="btn-save" onclick="saveSettings('security')">
                    <i class="fas fa-save"></i>
                    Save Security Settings
                </button>
            </div>
        </div>

        <!-- Appearance Settings Card -->
        <div class="settings-card">
            <div class="card-header">
                <div class="card-icon" style="background: linear-gradient(135deg, #8B5CF6, #F97316);">
                    <i class="fas fa-paint-brush"></i>
                </div>
                <div class="card-title">
                    <h2>Appearance</h2>
                    <p>Customize the look and feel</p>
                </div>
            </div>
            
            <div class="card-body">
                <form class="settings-form" id="appearanceForm">
                    <div class="form-group">
                        <label for="theme_color">
                            <i class="fas fa-palette"></i>
                            Theme Color
                        </label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="theme_color" 
                                name="theme_color" 
                                value="#8B5CF6"
                            >
                            <span class="color-value">#8B5CF6</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="accent_color">
                            <i class="fas fa-palette"></i>
                            Accent Color
                        </label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="accent_color" 
                                name="accent_color" 
                                value="#F97316"
                            >
                            <span class="color-value">#F97316</span>
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
                            <input type="checkbox" name="dark_mode" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </form>
            </div>
            
            <div class="card-footer">
                <button class="btn-save" onclick="saveSettings('appearance')">
                    <i class="fas fa-save"></i>
                    Save Appearance
                </button>
                <button class="btn-preview" onclick="previewChanges()">
                    <i class="fas fa-eye"></i>
                    Preview
                </button>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
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
                        <p>Remove all cached data and temporary files</p>
                    </div>
                </div>
                <button class="btn-danger" onclick="clearCache()">
                    <i class="fas fa-broom"></i>
                    Clear Cache
                </button>
            </div>
            
            <div class="danger-item">
                <div class="danger-info">
                    <i class="fas fa-rotate-left"></i>
                    <div>
                        <strong>Reset to Defaults</strong>
                        <p>Restore all settings to factory defaults</p>
                    </div>
                </div>
                <button class="btn-danger" onclick="resetToDefaults()">
                    <i class="fas fa-undo-alt"></i>
                    Reset All
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Main Container */
.settings-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

/* Header Styles */
.settings-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.page-title {
    font-size: 2.2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-subtitle {
    color: #64748B;
    font-size: 1rem;
}

.btn-save-all {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
    border: none;
    padding: 14px 32px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
}

.btn-save-all:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(139, 92, 246, 0.4);
}

/* Settings Grid */
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

/* Settings Card */
.settings-card {
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(139, 92, 246, 0.1);
    display: flex;
    flex-direction: column;
}

.settings-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 60px rgba(139, 92, 246, 0.15);
    border-color: #8B5CF6;
}

/* Card Header */
.card-header {
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 15px;
    border-bottom: 2px solid #F1F5F9;
    position: relative;
}

.card-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.card-icon i {
    font-size: 2rem;
    color: white;
}

.card-title {
    flex: 1;
}

.card-title h2 {
    color: #1E293B;
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 4px;
}

.card-title p {
    color: #64748B;
    font-size: 0.85rem;
}

.card-badge {
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    background: #EFF6FF;
    color: #1E40AF;
}

.card-badge.success {
    background: #F0FDF4;
    color: #166534;
}

.card-badge.warning {
    background: #FEF3C7;
    color: #92400E;
}

/* Card Body */
.card-body {
    padding: 25px;
    flex: 1;
}

.settings-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Form Groups */
.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 600;
    font-size: 0.9rem;
    color: #1E293B;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group label i {
    color: #8B5CF6;
    font-size: 1rem;
}

.required {
    color: #EF4444;
    margin-left: 3px;
    font-size: 1.1rem;
}

/* Input Wrappers */
.input-wrapper {
    position: relative;
}

.input-wrapper input,
.input-wrapper textarea,
.form-group input:not([type="checkbox"]):not([type="color"]),
.form-group select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    font-family: 'Inter', sans-serif;
    background: white;
}

.input-wrapper input:focus,
.input-wrapper textarea:focus,
.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #8B5CF6;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.input-wrapper input:hover,
.form-group input:hover,
.form-group select:hover {
    border-color: #8B5CF6;
}

.input-hint {
    display: block;
    font-size: 0.8rem;
    color: #64748B;
    margin-top: 5px;
}

/* Form Row */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

/* Price Inputs */
.price-inputs {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.price-group {
    position: relative;
    background: #F8FAFC;
    padding: 15px;
    border-radius: 12px;
    border: 1px solid #E2E8F0;
}

.currency-input {
    display: flex;
    align-items: center;
    gap: 8px;
}

.currency-symbol {
    padding: 10px 15px;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
}

.currency-input input {
    flex: 1;
    border: 2px solid #E2E8F0;
    padding: 10px 15px;
    border-radius: 10px;
    font-size: 1rem;
}

.save-badge {
    position: absolute;
    top: -8px;
    right: 10px;
    background: #10B981;
    color: white;
    padding: 3px 10px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
}

.save-badge.popular {
    background: #F97316;
}

/* Toggle Groups */
.toggle-group {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    background: #F8FAFC;
    padding: 15px;
    border-radius: 12px;
}

.toggle-label {
    display: flex;
    align-items: center;
    gap: 15px;
}

.toggle-label i {
    font-size: 1.3rem;
    color: #8B5CF6;
}

.toggle-label strong {
    display: block;
    color: #1E293B;
    margin-bottom: 3px;
}

.toggle-label p {
    color: #64748B;
    font-size: 0.85rem;
}

/* Toggle Switch */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 28px;
    flex-shrink: 0;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
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

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
}

input:checked + .toggle-slider:before {
    transform: translateX(24px);
}

/* Password Wrapper */
.password-wrapper {
    position: relative;
}

.password-wrapper input {
    width: 100%;
    padding: 12px 45px 12px 16px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
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

/* Color Input */
.color-input-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}

.color-input-wrapper input[type="color"] {
    width: 60px;
    height: 60px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    cursor: pointer;
    padding: 5px;
}

.color-value {
    padding: 12px 20px;
    background: #F8FAFC;
    border-radius: 10px;
    font-family: monospace;
    font-size: 1rem;
    color: #1E293B;
    border: 2px solid #E2E8F0;
}

/* Select Wrapper */
.select-wrapper {
    position: relative;
    min-width: 150px;
}

.select-wrapper select {
    width: 100%;
    padding: 10px 15px;
    border: 2px solid #E2E8F0;
    border-radius: 10px;
    font-size: 0.95rem;
    background: white;
    cursor: pointer;
    appearance: none;
}

.select-wrapper::after {
    content: '\f078';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748B;
    pointer-events: none;
}

/* Card Footer */
.card-footer {
    padding: 20px 25px;
    background: #F8FAFC;
    border-top: 2px solid #F1F5F9;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-save {
    flex: 1;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
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
    transition: all 0.3s ease;
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
}

.btn-reset {
    background: white;
    color: #64748B;
    border: 2px solid #E2E8F0;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-reset:hover {
    background: #F1F5F9;
    border-color: #94A3B8;
    color: #1E293B;
}

.btn-test {
    background: #10B981;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-test:hover {
    background: #059669;
    transform: translateY(-2px);
}

.btn-preview {
    background: #8B5CF6;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-preview:hover {
    background: #7C3AED;
    transform: translateY(-2px);
}

/* Danger Zone */
.danger-zone {
    background: #FEF2F2;
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
}

.danger-header i {
    color: #EF4444;
    font-size: 1.3rem;
}

.danger-header h3 {
    color: #B91C1C;
    font-size: 1.2rem;
    font-weight: 600;
}

.danger-content {
    padding: 25px;
}

.danger-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px;
    background: white;
    border-radius: 12px;
    margin-bottom: 15px;
    border: 1px solid #FECACA;
}

.danger-item:last-child {
    margin-bottom: 0;
}

.danger-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.danger-info i {
    font-size: 1.5rem;
    color: #EF4444;
}

.danger-info strong {
    display: block;
    color: #B91C1C;
    margin-bottom: 3px;
}

.danger-info p {
    color: #7F1D1D;
    font-size: 0.85rem;
}

.btn-danger {
    background: #EF4444;
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
    transition: all 0.3s ease;
    white-space: nowrap;
}

.btn-danger:hover {
    background: #DC2626;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
}

/* Alert Messages */
.alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 15px;
    animation: slideDown 0.3s ease;
    position: relative;
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

.alert-content {
    flex: 1;
}

.alert-content strong {
    display: block;
    margin-bottom: 3px;
}

.alert-content p {
    font-size: 0.95rem;
    opacity: 0.9;
}

.alert-close {
    background: none;
    border: none;
    font-size: 1.3rem;
    cursor: pointer;
    color: currentColor;
    opacity: 0.7;
    padding: 0 5px;
    transition: opacity 0.3s ease;
}

.alert-close:hover {
    opacity: 1;
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

/* Responsive Design */
@media (max-width: 1024px) {
    .settings-grid {
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    }
}

@media (max-width: 768px) {
    .settings-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .btn-save-all {
        width: 100%;
    }
    
    .settings-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .card-footer {
        flex-direction: column;
    }
    
    .btn-save,
    .btn-reset,
    .btn-test,
    .btn-preview {
        width: 100%;
    }
    
    .danger-item {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .danger-info {
        flex-direction: column;
        text-align: center;
    }
    
    .btn-danger {
        width: 100%;
    }
    
    .toggle-group {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .toggle-switch {
        align-self: flex-end;
    }
}

@media (max-width: 480px) {
    .page-title {
        font-size: 1.8rem;
    }
    
    .card-header {
        flex-direction: column;
        text-align: center;
    }
    
    .card-icon {
        margin: 0 auto;
    }
    
    .card-badge {
        position: absolute;
        top: 10px;
        right: 10px;
    }
    
    .price-group {
        text-align: center;
    }
    
    .currency-input {
        flex-direction: column;
    }
    
    .currency-symbol {
        width: 100%;
        text-align: center;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .settings-card {
        background: #1E293B;
    }
    
    .card-header {
        border-bottom-color: #334155;
    }
    
    .card-title h2 {
        color: #F1F5F9;
    }
    
    .card-title p {
        color: #94A3B8;
    }
    
    .form-group label {
        color: #F1F5F9;
    }
    
    .input-wrapper input,
    .input-wrapper textarea,
    .form-group input,
    .form-group select {
        background: #0F172A;
        border-color: #334155;
        color: #F1F5F9;
    }
    
    .card-footer {
        background: #334155;
        border-top-color: #475569;
    }
    
    .btn-reset {
        background: transparent;
        color: #94A3B8;
        border-color: #475569;
    }
    
    .btn-reset:hover {
        background: #475569;
        color: #F1F5F9;
    }
    
    .toggle-group {
        background: #334155;
    }
    
    .toggle-label strong {
        color: #F1F5F9;
    }
    
    .price-group {
        background: #334155;
    }
    
    .color-value {
        background: #334155;
        color: #F1F5F9;
    }
    
    .danger-item {
        background: #1E293B;
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

function saveSettings(type) {
    // Simulate saving settings
    showNotification(`${type} settings saved successfully!`, 'success');
}

function saveAllSettings() {
    showNotification('All settings have been saved successfully!', 'success');
}

function resetForm(type) {
    if (confirm(`Are you sure you want to reset ${type} settings to default values?`)) {
        showNotification(`${type} settings have been reset`, 'info');
    }
}

function testEmailConfig() {
    showNotification('Testing email configuration...', 'info');
    setTimeout(() => {
        showNotification('Email test successful! Check your inbox.', 'success');
    }, 1500);
}

function previewChanges() {
    showNotification('Preview mode activated', 'info');
}

function clearCache() {
    if (confirm('Are you sure you want to clear the system cache? This may temporarily affect performance.')) {
        showNotification('Cache cleared successfully!', 'success');
    }
}

function resetToDefaults() {
    if (confirm('⚠️ WARNING: This will reset ALL settings to factory defaults. This action cannot be undone. Are you absolutely sure?')) {
        showNotification('All settings have been reset to defaults', 'info');
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <div class="alert-content">
            <p>${message}</p>
        </div>
        <button class="alert-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    document.querySelector('.settings-container').insertBefore(notification, document.querySelector('.settings-container').firstChild);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>