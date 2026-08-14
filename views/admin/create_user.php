<?php
// File: /views/admin/create_user.php
$pageTitle = 'Create User | ROGELE';
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<style>
:root {
    --primary-purple: #7f2677;
    --accent-orange: #f06724;
    --text-dark: #000;
    --text-muted: #555;
    --bg-surface: #FFFFFF;
    --border-color: #E2E8F0;
    --radius-lg: 20px;
    --radius-md: 12px;
    --shadow-sm: 0 4px 12px rgba(0,0,0,0.03);
    --shadow-md: 0 10px 30px rgba(0,0,0,0.06);
    --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.admin-container, 
.admin-container * {
    box-sizing: border-box;
}

.admin-container {
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
    padding: clamp(16px, 4vw, 36px);
}

.page-header {
    margin-bottom: clamp(20px, 3vw, 30px);
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 12px;
    transition: var(--transition);
}

.back-link:hover {
    color: var(--primary-purple);
    transform: translateX(-3px);
}

.page-title {
    font-size: clamp(1.4rem, 3.5vw, 2.1rem);
    font-weight: 700;
    color: var(--primary-purple);
    margin: 0 0 4px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-subtitle {
    color: var(--text-muted);
    font-size: clamp(0.85rem, 2vw, 0.95rem);
    margin: 0;
}

.alert {
    padding: 14px 18px;
    border-radius: var(--radius-md);
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideDown 0.3s ease;
}

.alert-error {
    background: #FEF2F2;
    color: #B91C1C;
    border: 1px solid #FECACA;
}

.alert-content { flex: 1; font-size: 0.9rem; }

.alert-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: currentColor;
    opacity: 0.7;
}

.alert-close:hover { opacity: 1; }

@keyframes slideDown {
    from { transform: translateY(-10px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.form-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    padding: clamp(20px, 4vw, 36px);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
}

.form-section {
    margin-bottom: 30px;
    padding-bottom: 25px;
    border-bottom: 1px solid var(--border-color);
}

.form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.section-title {
    color: var(--text-dark);
    font-size: clamp(1.05rem, 2vw, 1.25rem);
    font-weight: 600;
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: var(--accent-orange);
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 18px;
}

.form-row:last-child {
    margin-bottom: 0;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    width: 100%;
}

.form-group label {
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group label i {
    color: var(--accent-orange);
    font-size: 0.85rem;
}

.required {
    color: #EF4444;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: 0.9rem;
    color: var(--text-dark);
    background-color: var(--bg-surface);
    transition: var(--transition);
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--accent-orange);
    box-shadow: 0 0 0 3px rgba(240, 103, 36, 0.15);
}

.form-group input:hover,
.form-group select:hover {
    border-color: var(--accent-orange);
}

.class-field-hidden {
    display: none;
}

.password-input-wrapper {
    position: relative;
    width: 100%;
}

.password-input-wrapper input {
    padding-right: 42px;
}

.toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 6px;
    border-radius: 4px;
    transition: var(--transition);
}

.toggle-password:hover {
    color: var(--accent-orange);
}

.input-hint {
    font-size: 0.775rem;
    color: var(--text-muted);
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid var(--border-color);
    align-items: center;
}

.btn-primary, .btn-secondary {
    padding: 11px 26px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: var(--transition);
    text-decoration: none;
}

.btn-primary {
    background-color: var(--primary-purple);
    color: white;
    border: none;
    box-shadow: 0 4px 12px rgba(127, 38, 119, 0.25);
}

.btn-primary:hover {
    background-color: var(--accent-orange);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(240, 103, 36, 0.3);
}

.btn-secondary {
    background: #F1F5F9;
    color: var(--text-dark);
    border: 1px solid var(--border-color);
}

.btn-secondary:hover {
    background: #E2E8F0;
    color: var(--text-dark);
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .btn-primary, .btn-secondary {
        width: 100%;
    }
}
</style>

<div class="admin-container">
    <div class="page-header">
        <div class="header-content">
            <h1 class="page-title">
                <i class="fas fa-user-plus" aria-hidden="true"></i>
                <span>Create New User</span>
            </h1>
            <p class="page-subtitle">Add a new user account to the platform</p>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error" role="alert">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
            <div class="alert-content">
                <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Close alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" action="<?php echo BASE_URL; ?>/admin/users/create" class="admin-form" id="createUserForm">
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-user-circle" aria-hidden="true"></i>
                    Basic Information
                </h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">
                            <i class="fas fa-user" aria-hidden="true"></i>
                            First Name <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name" 
                            required 
                            placeholder="Enter first name"
                            autocomplete="given-name"
                        >
                    </div>

                    <div class="form-group">
                        <label for="last_name">
                            <i class="fas fa-user" aria-hidden="true"></i>
                            Last Name <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="last_name" 
                            name="last_name" 
                            required 
                            placeholder="Enter last name"
                            autocomplete="family-name"
                        >
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope" aria-hidden="true"></i>
                            Email Address <span class="required">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required 
                            placeholder="user@example.com"
                            autocomplete="email"
                        >
                    </div>

                    <div class="form-group">
                        <label for="phone">
                            <i class="fas fa-phone" aria-hidden="true"></i>
                            Phone Number
                        </label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            placeholder="+256 XXX XXX XXX"
                            autocomplete="tel"
                        >
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-sliders-h" aria-hidden="true"></i>
                    Account Settings
                </h2>

                <div class="form-row" id="role-class-row">
                    <div class="form-group" id="role-group">
                        <label for="role">
                            <i class="fas fa-user-tag" aria-hidden="true"></i>
                            User Role <span class="required">*</span>
                        </label>
                        <select id="role" name="role" required onchange="toggleClassField()">
                            <option value="">Select a role</option>
                            <option value="admin">Administrator</option>
                            <option value="teacher">Teacher</option>
                            <option value="learner">Learner</option>
                            <option value="external">External User</option>
                        </select>
                    </div>

                    <div class="form-group class-field-hidden" id="class-field">
                        <label for="class">
                            <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                            Class Assignment
                        </label>
                        <select id="class" name="class">
                            <option value="">Select a class</option>
                            <option value="p1">Primary 1</option>
                            <option value="p2">Primary 2</option>
                            <option value="p3">Primary 3</option>
                            <option value="p4">Primary 4</option>
                            <option value="p5">Primary 5</option>
                            <option value="p6">Primary 6</option>
                            <option value="p7">Primary 7</option>
                            <option value="comp">Computer Club</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock" aria-hidden="true"></i>
                            Password
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                value="Password123"
                                placeholder="Password123"
                                autocomplete="new-password"
                            >
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password', this)" aria-label="Toggle password visibility">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        <span class="input-hint">Default password set to "Password123".</span>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock" aria-hidden="true"></i>
                            Confirm Password
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                value="Password123"
                                placeholder="Password123"
                                autocomplete="new-password"
                            >
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirm_password', this)" aria-label="Toggle confirm password visibility">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    <span>Create User</span>
                </button>
                <a href="<?php echo BASE_URL; ?>/admin/users" class="btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
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

function toggleClassField() {
    const roleSelect = document.getElementById('role');
    const classField = document.getElementById('class-field');
    
    if (roleSelect.value === 'learner') {
        classField.classList.remove('class-field-hidden');
    } else {
        classField.classList.add('class-field-hidden');
        document.getElementById('class').value = '';
    }
}

document.getElementById('createUserForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    
    if ((password !== '' || confirm !== '') && password !== confirm) {
        e.preventDefault();
        alert('Passwords do not match. Please verify your entries.');
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>