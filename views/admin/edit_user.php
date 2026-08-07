<?php
// File: /views/admin/edit_user.php
$pageTitle = 'Edit User | ROGELE';
require_once __DIR__ . '/../layouts/admin_header.php';

if (!isset($user) || empty($user)) {
    header('Location: ' . BASE_URL . '/admin/users');
    exit;
}

$classes = $classes ?? [];
$currentRole = $user['role'] ?? 'learner';
$fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
?>

<div class="edit-user-container">
    <header class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-user-edit" aria-hidden="true"></i>
                Edit User
            </h1>
            <p class="page-subtitle">Editing: <strong><?php echo htmlspecialchars($fullName ?: 'User'); ?></strong></p>
        </div>
    </header>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error" role="alert">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
            <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" role="status">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <span><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
        </div>
    <?php endif; ?>

    <main class="form-card">
        <form method="POST" class="edit-form" action="<?php echo BASE_URL; ?>/admin/users/edit/<?php echo urlencode($user['id']); ?>">
            
            <section class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-user" aria-hidden="true"></i>
                    Personal Information
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
                            value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" 
                            required
                            placeholder="Enter first name"
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
                            value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" 
                            required
                            placeholder="Enter last name"
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
                            value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                            required
                            placeholder="user@example.com"
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
                            value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                            placeholder="+256 XXX XXX XXX"
                        >
                    </div>
                </div>
            </section>

            <section class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-cog" aria-hidden="true"></i>
                    Account Settings
                </h2>

                <div class="form-row">
                    <div class="form-group">
                        <label for="role">
                            <i class="fas fa-user-tag" aria-hidden="true"></i>
                            User Role <span class="required">*</span>
                        </label>
                        <select id="role" name="role" required onchange="toggleClassSection(this.value)">
                            <option value="admin" <?php echo $currentRole === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                            <option value="teacher" <?php echo $currentRole === 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                            <option value="learner" <?php echo $currentRole === 'learner' ? 'selected' : ''; ?>>Learner</option>
                            <option value="external" <?php echo $currentRole === 'external' ? 'selected' : ''; ?>>External User</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">
                            <i class="fas fa-toggle-on" aria-hidden="true"></i>
                            Account Status
                        </label>
                        <select id="status" name="status">
                            <option value="active" <?php echo (!($user['is_suspended'] ?? false) && ($user['is_active'] ?? true)) ? 'selected' : ''; ?>>Active</option>
                            <option value="suspended" <?php echo ($user['is_suspended'] ?? false) ? 'selected' : ''; ?>>Suspended</option>
                            <option value="inactive" <?php echo !($user['is_active'] ?? true) ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="form-section" id="class-section" style="<?php echo in_array($currentRole, ['learner', 'external']) ? '' : 'display: none;'; ?>">
                <h2 class="section-title">
                    <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                    Class Assignment
                </h2>

                <div class="form-group">
                    <label for="class_id">
                        <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                        Assigned Class
                    </label>
                    <select id="class_id" name="class_id">
                        <option value="">No Class Assigned</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo htmlspecialchars($class['id']); ?>" <?php echo (($user['class_id'] ?? '') == $class['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-hint">Select the class this student belongs to</small>
                </div>
            </section>

            <div class="form-actions">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save" aria-hidden="true"></i>
                    Update User
                </button>
                <a href="<?php echo BASE_URL; ?>/admin/users" class="btn-cancel">
                    <i class="fas fa-times" aria-hidden="true"></i>
                    Cancel
                </a>
            </div>
        </form>
    </main>

    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $user['id']): ?>
    <section class="danger-zone">
        <div class="danger-header">
            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
            <h3>Danger Zone</h3>
        </div>
        <div class="danger-content">
            <div class="danger-item">
                <div class="danger-info">
                    <i class="fas fa-ban" aria-hidden="true"></i>
                    <div>
                        <strong>Suspend User</strong>
                        <p>Temporarily disable this user's access to the platform</p>
                    </div>
                </div>
                <?php if ($user['is_suspended'] ?? false): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/users/activate/<?php echo urlencode($user['id']); ?>" class="btn-action btn-activate" onclick="return confirm('Activate this user?')">
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                        Activate User
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/admin/users/suspend/<?php echo urlencode($user['id']); ?>" class="btn-action btn-suspend" onclick="return confirm('Suspend this user? They will not be able to log in.')">
                        <i class="fas fa-ban" aria-hidden="true"></i>
                        Suspend User
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="danger-item">
                <div class="danger-info">
                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                    <div>
                        <strong>Delete User</strong>
                        <p>Permanently delete this user and all associated data</p>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>/admin/users/delete/<?php echo urlencode($user['id']); ?>" 
                   class="btn-action btn-delete" 
                   onclick="return confirmDelete('<?php echo htmlspecialchars(addslashes($fullName), ENT_QUOTES); ?>')">
                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                    Delete User
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>

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
    --shadow-md: 0 10px 30px rgba(0, 0, 0, 0.05);
    --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.edit-user-container,
.edit-user-container * {
    box-sizing: border-box;
}

.edit-user-container {
    max-width: 900px;
    margin: 0 auto;
    padding: clamp(16px, 3vw, 32px);
    color: var(--text-dark);
}

.page-header {
    margin-bottom: clamp(20px, 3vw, 30px);
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--text-dark);
    text-decoration: none;
    font-size: 0.95rem;
    margin-bottom: 12px;
    transition: var(--transition);
    font-weight: 500;
}

.back-link:hover {
    color: var(--primary-purple);
}

.page-title {
    font-size: clamp(1.6rem, 3.5vw, 2.2rem);
    font-weight: 700;
    color: var(--primary-purple);
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-subtitle {
    color: var(--text-muted);
    font-size: 0.95rem;
    margin: 0;
}

.page-subtitle strong {
    color: var(--text-dark);
}

.alert {
    padding: 14px 18px;
    border-radius: var(--radius-md);
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.95rem;
}

.alert-success {
    background: #F0FDF4;
    color: #166534;
    border: 1px solid #BBF7D0;
}

.alert-error {
    background: #FEF2F2;
    color: #991B1B;
    border: 1px solid #FECACA;
}

.form-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    padding: clamp(20px, 4vw, 40px);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    margin-bottom: 30px;
}

.form-section {
    margin-bottom: clamp(24px, 3vw, 32px);
    padding-bottom: clamp(24px, 3vw, 32px);
    border-bottom: 2px solid #F1F5F9;
}

.form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.section-title {
    color: var(--text-dark);
    font-size: 1.15rem;
    font-weight: 700;
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
    margin-bottom: 20px;
}

.form-row:last-child {
    margin-bottom: 0;
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
    color: var(--accent-orange);
}

.required {
    color: #EF4444;
}

.form-group input,
.form-group select {
    padding: 12px 16px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: 0.95rem;
    transition: var(--transition);
    width: 100%;
    background-color: #FAFAFA;
    color: var(--text-dark);
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--accent-orange);
    background-color: #FFFFFF;
    box-shadow: 0 0 0 3px rgba(240, 103, 36, 0.15);
}

.form-hint {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-top: 2px;
}

.form-actions {
    display: flex;
    gap: 14px;
    margin-top: clamp(24px, 3vw, 32px);
    padding-top: clamp(20px, 3vw, 28px);
    border-top: 2px solid #F1F5F9;
}

.btn-save {
    flex: 2;
    background-color: var(--primary-purple);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: var(--transition);
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(127, 38, 119, 0.3);
}

.btn-cancel {
    flex: 1;
    padding: 12px 24px;
    background: #F1F5F9;
    color: var(--text-muted);
    border: 1px solid var(--border-color);
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: var(--transition);
    text-decoration: none;
}

.btn-cancel:hover {
    background: #E2E8F0;
    color: var(--text-dark);
}

.danger-zone {
    background: #FEF2F2;
    border: 1px solid #FECACA;
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.danger-header {
    padding: 16px 24px;
    background: #FEE2E2;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid #FECACA;
}

.danger-header i {
    color: #DC2626;
    font-size: 1.1rem;
}

.danger-header h3 {
    color: #991B1B;
    font-size: 1.05rem;
    font-weight: 700;
    margin: 0;
}

.danger-content {
    padding: clamp(16px, 2.5vw, 24px);
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.danger-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px;
    background: var(--bg-surface);
    border-radius: var(--radius-md);
    border: 1px solid #FECACA;
    gap: 16px;
}

.danger-info {
    display: flex;
    align-items: center;
    gap: 14px;
}

.danger-info i {
    font-size: 1.25rem;
    color: #DC2626;
}

.danger-info strong {
    display: block;
    color: #991B1B;
    font-size: 0.95rem;
    margin-bottom: 2px;
}

.danger-info p {
    color: #7F1D1D;
    font-size: 0.85rem;
    margin: 0;
}

.btn-action {
    padding: 10px 18px;
    border-radius: var(--radius-md);
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: var(--transition);
    text-decoration: none;
    white-space: nowrap;
}

.btn-suspend {
    background: #D97706;
    color: white;
}

.btn-suspend:hover {
    background: #B45309;
    transform: translateY(-2px);
}

.btn-activate {
    background: #059669;
    color: white;
}

.btn-activate:hover {
    background: #047857;
    transform: translateY(-2px);
}

.btn-delete {
    background: #DC2626;
    color: white;
}

.btn-delete:hover {
    background: #B91C1C;
    transform: translateY(-2px);
}

@media (max-width: 600px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .danger-item {
        flex-direction: column;
        text-align: center;
    }
    
    .danger-info {
        flex-direction: column;
    }
    
    .btn-action {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
function toggleClassSection(role) {
    const classSection = document.getElementById('class-section');
    if (role === 'learner' || role === 'external') {
        classSection.style.display = 'block';
    } else {
        classSection.style.display = 'none';
    }
}

function confirmDelete(userName) {
    return confirm(`Are you sure you want to permanently delete ${userName}? This action cannot be undone.`);
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>