<?php
// File: /views/admin/profile.php
$pageTitle = 'Profile | ROGELE';
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<style>
:root {
    --primary-purple: #7f2677;
    --primary-orange: #f06724;
    --text-dark: #000;
    --text-light: #555;
    --bg-light: #F8FAFC;
    --border-color: #E2E8F0;
    --card-shadow: 0 10px 40px rgba(0, 0, 0, 0.06);
}

.profile-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: clamp(20px, 4vw, 40px) clamp(15px, 2vw, 20px);
    width: 100%;
}

.profile-header {
    margin-bottom: 30px;
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
    color: var(--text-light);
    font-size: clamp(0.9rem, 2vw, 1rem);
}

.profile-grid {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 25px;
    align-items: start;
}

.profile-card {
    background: white;
    border-radius: 20px;
    box-shadow: var(--card-shadow);
    padding: clamp(20px, 3vw, 35px);
}

.profile-photo-section {
    text-align: center;
    margin-bottom: 25px;
}

.profile-photo-wrapper {
    position: relative;
    width: 140px;
    height: 140px;
    margin: 0 auto 15px;
    cursor: pointer;
}

.profile-photo, .profile-photo-placeholder {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: 0 8px 20px rgba(127, 38, 119, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease;
}

.profile-photo-placeholder {
    background-color: var(--primary-orange);
    font-size: 2.8rem;
    font-weight: 700;
    color: white;
}

.profile-photo-wrapper:hover .profile-photo,
.profile-photo-wrapper:hover .profile-photo-placeholder {
    transform: scale(1.02);
}

.photo-upload-badge {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 36px;
    height: 36px;
    background: var(--primary-purple);
    border: 3px solid white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.95rem;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}

.profile-name {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 4px;
}

.profile-role {
    color: var(--primary-purple);
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.photo-info {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--text-light);
    font-size: 0.8rem;
    background: var(--bg-light);
    padding: 6px 14px;
    border-radius: 30px;
}

.photo-info i { color: var(--primary-orange); }

.profile-stats {
    border-top: 1px solid var(--border-color);
    padding-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    border-radius: 12px;
    transition: background 0.2s ease;
}

.stat-item:hover { background: var(--bg-light); }

.stat-icon {
    width: 38px;
    height: 38px;
    background: rgba(240, 103, 36, 0.1);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-orange);
    font-size: 0.95rem;
    flex-shrink: 0;
}

.stat-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.stat-label {
    font-size: 0.8rem;
    color: var(--text-light);
}

.stat-value {
    font-weight: 600;
    color: var(--text-dark);
    font-size: 0.9rem;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--bg-light);
}

.card-title i { color: var(--primary-orange); }

.profile-form {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-group label {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 6px;
}

.form-group label i {
    color: var(--primary-orange);
    font-size: 0.9rem;
}

.form-group input {
    padding: 12px 14px;
    border: 1px solid var(--border-color);
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary-orange);
    box-shadow: 0 0 0 3px rgba(240, 103, 36, 0.15);
}

.form-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 10px;
}

.btn-save {
    flex: 1;
    background: var(--primary-purple);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.btn-save:hover {
    background: var(--primary-orange);
    transform: translateY(-1px);
}

.alert {
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.9rem;
    font-weight: 500;
}

.alert-success { background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; }
.alert-error { background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; }

@media (max-width: 992px) {
    .profile-grid { grid-template-columns: 1fr; }
}

@media (max-width: 576px) {
    .form-row { grid-template-columns: 1fr; gap: 18px; }
    .form-actions { flex-direction: column; }
}
</style>

<div class="profile-container">
    <div class="profile-header">
        <h1 class="page-title"><i class="fas fa-user-shield"></i>Admin Profile</h1>
        <p class="page-subtitle">Manage your personal information and account settings</p>
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

    <div class="profile-grid">
        <div class="profile-card">
            <div class="profile-photo-section">
                <div class="profile-photo-wrapper" onclick="document.getElementById('profilePhotoInput').click()" title="Change Profile Photo">
                    <?php if (!empty($profile['profile_photo'])): ?>
                        <img src="<?php echo BASE_URL; ?>/<?php echo $profile['profile_photo']; ?>" alt="Profile Photo" class="profile-photo">
                    <?php else: ?>
                        <div class="profile-photo-placeholder">
                            <?php 
                            $nameParts = explode(' ', $_SESSION['user_name'] ?? 'Admin');
                            $initials = '';
                            foreach ($nameParts as $part) {
                                if (!empty($part)) $initials .= strtoupper(substr($part, 0, 1));
                            }
                            echo substr($initials, 0, 2);
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <h2 class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></h2>
                <p class="profile-role">System Administrator</p>
            </div>

            <div class="profile-stats">
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                    <div class="stat-content">
                        <span class="stat-label">Admin Since</span>
                        <span class="stat-value"><?php echo isset($profile['created_at']) ? date('M Y', strtotime($profile['created_at'])) : date('M Y'); ?></span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-content">
                        <span class="stat-label">Last Login</span>
                        <span class="stat-value"><?php echo isset($profile['last_login']) ? date('M d, Y', strtotime($profile['last_login'])) : 'Today'; ?></span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-shield-alt"></i></div>
                    <div class="stat-content">
                        <span class="stat-label">Account Status</span>
                        <span class="stat-value">Active</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <h3 class="card-title"><i class="fas fa-edit"></i>Personal Information</h3>
            
            <form method="POST" action="<?php echo BASE_URL; ?>/admin/update-profile" class="profile-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name"><i class="fas fa-user"></i>First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($profile['first_name'] ?? ''); ?>" required placeholder="First name">
                    </div>
                    <div class="form-group">
                        <label for="last_name"><i class="fas fa-user"></i>Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($profile['last_name'] ?? ''); ?>" required placeholder="Last name">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i>Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($profile['email'] ?? $_SESSION['user_email'] ?? ''); ?>" required placeholder="Email address">
                </div>

                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone"></i>Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" placeholder="Phone number">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>