<?php
// File: /views/admin/edit_user.php
$pageTitle = 'Edit User - Admin - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="admin-container">
    <div class="page-header">
        <div class="header-content">
            <a href="/rays-of-grace/admin/users" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
            <h1 class="page-title">
                <i class="fas fa-user-edit"></i>
                Edit User
            </h1>
            <p class="page-subtitle">Edit user: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" class="admin-form">
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-user"></i>
                    Personal Information
                </h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-cog"></i>
                    Account Settings
                </h2>

                <div class="form-row">
                    <div class="form-group">
                        <label for="role">User Role</label>
                        <select id="role" name="role" required>
                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Administrator</option>
                            <option value="teacher" <?php echo $user['role'] == 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                            <option value="learner" <?php echo $user['role'] == 'learner' ? 'selected' : ''; ?>>Learner</option>
                            <option value="external" <?php echo $user['role'] == 'external' ? 'selected' : ''; ?>>External User</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    Update User
                </button>
                <a href="/rays-of-grace/admin/users" class="btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>