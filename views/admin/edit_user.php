<?php
// File: /views/admin/edit_user.php
$pageTitle = 'Edit User - Admin - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

// Ensure user data is available
if (!isset($user) || empty($user)) {
    header('Location: ' . BASE_URL . '/admin/users');
    exit;
}
?>

<div class="edit-user-container">
    <!-- Header with Back Link -->
    <div class="page-header">
        <div>
            <a href="/rays-of-grace/admin/users" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
            <h1 class="page-title">
                <i class="fas fa-user-edit"></i>
                Edit User
            </h1>
            <p class="page-subtitle">Editing: <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong></p>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
        </div>
    <?php endif; ?>

    <!-- Edit Form -->
    <div class="form-card">
        <form method="POST" class="edit-form" action="/rays-of-grace/admin/users/edit/<?php echo $user['id']; ?>">
            <!-- Personal Information Section -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-user"></i>
                    Personal Information
                </h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">
                            <i class="fas fa-user"></i>
                            First Name <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name" 
                            value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" 
                            required
                            placeholder="Enter first name"
                        >
                    </div>

                    <div class="form-group">
                        <label for="last_name">
                            <i class="fas fa-user"></i>
                            Last Name <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="last_name" 
                            name="last_name" 
                            value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" 
                            required
                            placeholder="Enter last name"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email Address <span class="required">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                        required
                        placeholder="user@example.com"
                    >
                </div>

                <div class="form-group">
                    <label for="phone">
                        <i class="fas fa-phone"></i>
                        Phone Number
                    </label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                        placeholder="+256 XXX XXX XXX"
                    >
                </div>
            </div>

            <!-- Account Settings Section -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-cog"></i>
                    Account Settings
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="role">
                            <i class="fas fa-user-tag"></i>
                            User Role <span class="required">*</span>
                        </label>
                        <select id="role" name="role" required>
                            <option value="admin" <?php echo ($user['role'] ?? '') == 'admin' ? 'selected' : ''; ?>>Administrator</option>
                            <option value="teacher" <?php echo ($user['role'] ?? '') == 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                            <option value="learner" <?php echo ($user['role'] ?? '') == 'learner' ? 'selected' : ''; ?>>Learner</option>
                            <option value="external" <?php echo ($user['role'] ?? '') == 'external' ? 'selected' : ''; ?>>External User</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">
                            <i class="fas fa-toggle-on"></i>
                            Account Status
                        </label>
                        <select id="status" name="status">
                            <option value="active" <?php echo (!($user['is_suspended'] ?? false) && ($user['is_active'] ?? true)) ? 'selected' : ''; ?>>Active</option>
                            <option value="suspended" <?php echo ($user['is_suspended'] ?? false) ? 'selected' : ''; ?>>Suspended</option>
                            <option value="inactive" <?php echo !($user['is_active'] ?? true) ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i>
                    Update User
                </button>
                <a href="/rays-of-grace/admin/users" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Danger Zone (only for other users, not self) -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $user['id']): ?>
    <div class="danger-zone">
        <div class="danger-header">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Danger Zone</h3>
        </div>
        <div class="danger-content">
            <div class="danger-item">
                <div class="danger-info">
                    <i class="fas fa-ban"></i>
                    <div>
                        <strong>Suspend User</strong>
                        <p>Temporarily disable this user's access to the platform</p>
                    </div>
                </div>
                <?php if ($user['is_suspended'] ?? false): ?>
                    <a href="/rays-of-grace/admin/users/activate/<?php echo $user['id']; ?>" class="btn-activate" onclick="return confirm('Activate this user?')">
                        <i class="fas fa-check-circle"></i>
                        Activate User
                    </a>
                <?php else: ?>
                    <a href="/rays-of-grace/admin/users/suspend/<?php echo $user['id']; ?>" class="btn-suspend" onclick="return confirm('Suspend this user? They will not be able to log in.')">
                        <i class="fas fa-ban"></i>
                        Suspend User
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="danger-item">
                <div class="danger-info">
                    <i class="fas fa-trash"></i>
                    <div>
                        <strong>Delete User</strong>
                        <p>Permanently delete this user and all associated data</p>
                    </div>
                </div>
                <a href="/rays-of-grace/admin/users/delete/<?php echo $user['id']; ?>" class="btn-delete" onclick="return confirmDelete(<?php echo $user['id']; ?>, '<?php echo addslashes($user['first_name'] . ' ' . $user['last_name']); ?>')">
                    <i class="fas fa-trash"></i>
                    Delete User
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.edit-user-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 30px 20px;
}

.page-header {
    margin-bottom: 30px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #64748B;
    text-decoration: none;
    font-size: 0.95rem;
    margin-bottom: 15px;
    transition: color 0.3s ease;
}

.back-link:hover {
    color: #8B5CF6;
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
}

.page-subtitle strong {
    color: #1E293B;
}

/* Alert Messages */
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

/* Form Card */
.form-card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}

.form-section {
    margin-bottom: 35px;
    padding-bottom: 35px;
    border-bottom: 2px solid #F1F5F9;
}

.form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.section-title {
    color: #1E293B;
    font-size: 1.3rem;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: #8B5CF6;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
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

.required {
    color: #EF4444;
    margin-left: 3px;
}

.form-group input,
.form-group select {
    padding: 12px 16px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    font-family: 'Inter', sans-serif;
    width: 100%;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #8B5CF6;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.form-group input:hover,
.form-group select:hover {
    border-color: #8B5CF6;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn-save {
    flex: 1;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
    border: none;
    padding: 14px 30px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);
}

.btn-cancel {
    padding: 14px 30px;
    background: white;
    color: #64748B;
    border: 2px solid #E2E8F0;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-cancel:hover {
    background: #F1F5F9;
    border-color: #94A3B8;
    color: #1E293B;
}

/* Danger Zone */
.danger-zone {
    background: #FEF2F2;
    border: 2px solid #FECACA;
    border-radius: 20px;
    overflow: hidden;
    margin-top: 30px;
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
    padding: 20px;
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
    margin: 0;
}

.btn-suspend {
    background: #D97706;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-suspend:hover {
    background: #B45309;
    transform: translateY(-2px);
}

.btn-activate {
    background: #059669;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-activate:hover {
    background: #047857;
    transform: translateY(-2px);
}

.btn-delete {
    background: #DC2626;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-delete:hover {
    background: #B91C1C;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .form-card {
        padding: 25px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
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
    
    .btn-suspend,
    .btn-activate,
    .btn-delete {
        width: 100%;
        justify-content: center;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .form-card {
        background: #1E293B;
    }
    
    .section-title {
        color: #F1F5F9;
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
    
    .btn-cancel {
        background: transparent;
        color: #94A3B8;
        border-color: #334155;
    }
    
    .btn-cancel:hover {
        background: #334155;
        color: #F1F5F9;
    }
    
    .page-subtitle strong {
        color: #F1F5F9;
    }
    
    .danger-item {
        background: #1E293B;
    }
}
</style>

<script>
function confirmDelete(userId, userName) {
    return confirm(`Are you sure you want to permanently delete ${userName}? This action cannot be undone.`);
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>