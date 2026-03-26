<?php
// File: /views/teacher/profile.php
$pageTitle = 'Profile | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$profile = $profile ?? [];
?>

<div class="profile-container">
    <!-- Header -->
    <div class="profile-header">
        <h1 class="page-title">
            <i class="fas fa-user-circle"></i>
            My Profile
        </h1>
        <p class="page-subtitle">Manage your personal information and account settings</p>
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

    <div class="profile-grid">
        <!-- Left Column - Profile Photo & Info -->
        <div class="profile-card profile-card-left">
            <div class="profile-photo-section">
                <div class="profile-photo-wrapper">
                    <?php if (!empty($profile['profile_photo'])): ?>
                        <img src="<?php echo BASE_URL; ?>/<?php echo $profile['profile_photo']; ?>" alt="Profile Photo" class="profile-photo">
                    <?php else: ?>
                        <div class="profile-photo-placeholder">
                            <?php 
                            $nameParts = explode(' ', $_SESSION['user_name'] ?? 'Teacher');
                            $initials = '';
                            foreach ($nameParts as $part) {
                                if (!empty($part)) {
                                    $initials .= strtoupper(substr($part, 0, 1));
                                }
                            }
                            echo substr($initials, 0, 2);
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <button class="photo-upload-btn" onclick="document.getElementById('profilePhotoInput').click()">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                
                <form method="POST" action="<?php echo BASE_URL; ?>/teacher/update-profile-photo" enctype="multipart/form-data" id="photoUploadForm">
                    <input type="file" id="profilePhotoInput" name="profile_photo" accept="image/*" style="display: none;" onchange="document.getElementById('photoUploadForm').submit()">
                </form>
                
                <h2 class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Teacher'); ?></h2>
                <p class="profile-role"><?php echo ucfirst($_SESSION['user_role'] ?? 'teacher'); ?></p>
                
                <div class="photo-info">
                    <i class="fas fa-info-circle"></i>
                    <span>JPG, PNG or GIF (Max 2MB)</span>
                </div>
            </div>

            <div class="profile-stats">
                <div class="stat-item">
                    <i class="fas fa-calendar-alt stat-icon"></i>
                    <div class="stat-content">
                        <span class="stat-label">Member Since</span>
                        <span class="stat-value"><?php echo isset($profile['created_at']) ? date('M Y', strtotime($profile['created_at'])) : date('M Y'); ?></span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-clock stat-icon"></i>
                    <div class="stat-content">
                        <span class="stat-label">Last Login</span>
                        <span class="stat-value"><?php echo isset($profile['last_login']) ? date('M d, Y', strtotime($profile['last_login'])) : 'Today'; ?></span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-chalkboard-teacher stat-icon"></i>
                    <div class="stat-content">
                        <span class="stat-label">Classes Taught</span>
                        <span class="stat-value"><?php echo $profile['classes_count'] ?? 0; ?></span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-users stat-icon"></i>
                    <div class="stat-content">
                        <span class="stat-label">Total Students</span>
                        <span class="stat-value"><?php echo $profile['students_count'] ?? 0; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Edit Form -->
        <div class="profile-card profile-card-right">
            <h3 class="card-title">
                <i class="fas fa-edit"></i>
                Edit Personal Information
            </h3>
            
            <form method="POST" action="<?php echo BASE_URL; ?>/teacher/update-profile" class="profile-form" id="profileForm">
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
                            value="<?php echo htmlspecialchars($profile['first_name'] ?? ''); ?>" 
                            required
                            placeholder="Enter your first name"
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
                            value="<?php echo htmlspecialchars($profile['last_name'] ?? ''); ?>" 
                            required
                            placeholder="Enter your last name"
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
                        value="<?php echo htmlspecialchars($profile['email'] ?? $_SESSION['user_email'] ?? ''); ?>" 
                        required
                        placeholder="Enter your email address"
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
                        value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" 
                        placeholder="Enter your phone number"
                    >
                </div>

                <div class="form-group">
                    <label for="bio">
                        <i class="fas fa-align-left"></i>
                        Bio / About
                    </label>
                    <textarea 
                        id="bio" 
                        name="bio" 
                        rows="4" 
                        placeholder="Tell your students a little about yourself..."
                    ><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="qualification">
                        <i class="fas fa-graduation-cap"></i>
                        Qualifications
                    </label>
                    <input 
                        type="text" 
                        id="qualification" 
                        name="qualification" 
                        value="<?php echo htmlspecialchars($profile['qualification'] ?? ''); ?>" 
                        placeholder="e.g., Bachelor of Education"
                    >
                </div>

                <div class="form-group">
                    <label for="specialization">
                        <i class="fas fa-star"></i>
                        Specialization
                    </label>
                    <input 
                        type="text" 
                        id="specialization" 
                        name="specialization" 
                        value="<?php echo htmlspecialchars($profile['specialization'] ?? ''); ?>" 
                        placeholder="e.g., Mathematics, Science"
                    >
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                    <button type="reset" class="btn-cancel">
                        <i class="fas fa-undo"></i>
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.profile-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.profile-header {
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

/* Profile Grid */
.profile-grid {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 30px;
}

.profile-card {
    background: white;
    border-radius: 24px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.profile-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 50px rgba(139, 92, 246, 0.15);
}

.profile-card-left {
    padding: 30px;
}

/* Profile Photo */
.profile-photo-section {
    text-align: center;
    margin-bottom: 30px;
    position: relative;
}

.profile-photo-wrapper {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 0 auto 20px;
}

.profile-photo {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);
}

.profile-photo-placeholder {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, #f06724);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: 600;
    color: white;
    border: 4px solid white;
    box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);
}

.photo-upload-btn {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 40px;
    height: 40px;
    background: white;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: black;
    font-size: 1.2rem;
    cursor: pointer;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.photo-upload-btn:hover {
    background: #7f2677;
    color: white;
    transform: scale(1.1);
}

.profile-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: black;
    margin-bottom: 5px;
}

.profile-role {
    color: black;
    font-weight: 500;
    text-transform: capitalize;
    margin-bottom: 15px;
}

.photo-info {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: black;
    font-size: 0.85rem;
    background: #F8FAFC;
    padding: 8px 15px;
    border-radius: 50px;
    margin-top: 15px;
}

.photo-info i {
    color: #F97316;
}

/* Profile Stats */
.profile-stats {
    border-top: 2px solid #F1F5F9;
    padding-top: 25px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px;
    border-radius: 12px;
    transition: background 0.3s ease;
    margin-bottom: 5px;
}

.stat-item:hover {
    background: #F8FAFC;
}

.stat-icon {
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

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-label {
    font-size: 0.85rem;
    color: black;
}

.stat-value {
    font-weight: 600;
    color: #7f2677;
}

/* Right Column */
.profile-card-right {
    padding: 40px;
}

.card-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: black;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding-bottom: 15px;
    border-bottom: 2px solid #F1F5F9;
}

.card-title i {
    color: #F97316;
}

/* Form Styles */
.profile-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
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
    font-size: 1rem;
}

.required {
    color: #EF4444;
    margin-left: 3px;
}

.form-group input,
.form-group textarea {
    padding: 12px 16px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    font-family: 'Inter', sans-serif;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #f06724;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.form-group input:hover,
.form-group textarea:hover {
    border-color: #f06724;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.btn-save {
    flex: 1;
    background: linear-gradient(135deg, #7f2677);
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
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);
}

.btn-cancel {
    padding: 14px 30px;
    background: #7f2677;
    color: white;
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
}

.btn-cancel:hover {
    background: #f06724;
    border-color: #94A3B8;
    color: white;
}

/* Responsive Design */
@media (max-width: 992px) {
    .profile-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .profile-card-left {
        padding: 25px;
    }
}

@media (max-width: 768px) {
    .profile-container {
        padding: 20px 15px;
    }
    
    .page-title {
        font-size: 1.8rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .profile-card-right {
        padding: 25px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn-save, .btn-cancel {
        width: 100%;
    }
    
    .profile-photo-wrapper {
        width: 120px;
        height: 120px;
    }
    
    .photo-upload-btn {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }
}

@media (max-width: 480px) {
    .page-title {
        font-size: 1.5rem;
    }
    
    .profile-name {
        font-size: 1.3rem;
    }
    
    .stat-item {
        padding: 10px;
    }
    
    .stat-icon {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .profile-card {
        background: black;
    }
    
    .profile-name {
        color: #F1F5F9;
    }
    
    .card-title {
        color: #F1F5F9;
        border-bottom-color: #334155;
    }
    
    .form-group label {
        color: #F1F5F9;
    }
    
    .form-group input,
    .form-group textarea {
        background: #0F172A;
        border-color: #334155;
        color: #F1F5F9;
    }
    
    .stat-value {
        color: #F1F5F9;
    }
    
    .stat-item:hover {
        background: #334155;
    }
    
    .btn-cancel {
        background: transparent;
        color: #94A3B8;
        border-color: #475569;
    }
    
    .btn-cancel:hover {
        background: #475569;
        color: #F1F5F9;
    }
}
</style>

<script>
document.getElementById('profilePhotoInput')?.addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        const file = this.files[0];
        
        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            this.value = '';
            return;
        }
        
        // Validate file type
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            alert('Please select a valid image file (JPG, PNG, or GIF)');
            this.value = '';
            return;
        }
        
        // Show preview before upload
        const reader = new FileReader();
        reader.onload = function(e) {
            // You could show a preview here if you want
            console.log('Image selected, ready to upload');
        };
        reader.readAsDataURL(file);
        
        // Auto-submit the form
        document.getElementById('photoUploadForm').submit();
    }
});

// Form validation
document.getElementById('profileForm')?.addEventListener('submit', function(e) {
    const firstName = document.getElementById('first_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    const email = document.getElementById('email').value.trim();
    
    if (!firstName || !lastName || !email) {
        e.preventDefault();
        alert('Please fill in all required fields');
        return false;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address');
        return false;
    }
    
    return true;
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>