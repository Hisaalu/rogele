<?php
// File: /views/admin/dashboard.php
$pageTitle = 'Admin Dashboard - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

// Get stats from controller
$totalUsers = $totalUsers ?? 0;
$totalTeachers = $totalTeachers ?? 0;
$totalLearners = $totalLearners ?? 0;
$totalExternal = $totalExternal ?? 0;
$recentUsers = $recentUsers ?? [];
$recentActivity = $recentActivity ?? [];
?>

<div class="admin-dashboard">
    <!-- Header -->
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-tachometer-alt"></i>
                Admin Dashboard
            </h1>
            <p class="page-subtitle">
                Welcome back, <?php 
                    $fullName = $_SESSION['user_name'] ?? '';
                    $firstName = explode(' ', trim($fullName))[0];
                    echo htmlspecialchars($firstName); 
                ?>! Here's what's happening with your platform.
            </p>
        </div>
        <div class="date-display">
            <i class="fas fa-calendar"></i>
            <?php echo date('l, F j, Y'); ?>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f06724);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo number_format($totalUsers); ?></span>
                <span class="stat-label">Total Users</span>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> 12% from last month
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f06724);">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo number_format($totalTeachers); ?></span>
                <span class="stat-label">Teachers</span>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> 5 new this month
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f06724);">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo number_format($totalLearners); ?></span>
                <span class="stat-label">Learners</span>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> 24 new this month
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f06724);">
                <i class="fas fa-globe"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo number_format($totalExternal); ?></span>
                <span class="stat-label">External Users</span>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> 8 new this month
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2 class="section-title">Quick Actions</h2>
        <div class="actions-grid">
            <a href="<?php echo BASE_URL; ?>/admin/users/create" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="action-content">
                    <h3>Add New User</h3>
                    <p>Create a new user account</p>
                </div>
                <i class="fas fa-arrow-right"></i>
            </a>

            <a href="<?php echo BASE_URL; ?>/admin/reports" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="action-content">
                    <h3>Generate Report</h3>
                    <p>View system analytics</p>
                </div>
                <i class="fas fa-arrow-right"></i>
            </a>

            <a href="<?php echo BASE_URL; ?>/admin/settings" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="action-content">
                    <h3>System Settings</h3>
                    <p>Configure platform</p>
                </div>
                <i class="fas fa-arrow-right"></i>
            </a>

            <a href="<?php echo BASE_URL; ?>/admin/users" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="action-content">
                    <h3>Manage Users</h3>
                    <p>View all users</p>
                </div>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Recent Users & Activity -->
    <div class="dashboard-grid">
        <!-- Recent Users -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-user-plus"></i> Recent Users</h3>
                <a href="<?php echo BASE_URL; ?>/admin/users" class="view-all">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentUsers)): ?>
                    <p class="empty-message">No recent users</p>
                <?php else: ?>
                    <?php 
                    // Show only the last 4 users
                    $displayUsers = array_slice($recentUsers, 0, 4);
                    foreach ($displayUsers as $user): 
                    ?>
                    <div class="user-item">
                        <div class="user-avatar">
                            <?php if (!empty($user['profile_photo'])): ?>
                                <img src="<?php echo BASE_URL; ?>/<?php echo $user['profile_photo']; ?>" alt="<?php echo $user['first_name']; ?>">
                            <?php else: ?>
                                <div class="avatar-placeholder" style="background: linear-gradient(135deg, #f06724);">
                                    <?php echo strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? 'S', 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="user-info">
                            <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                            <p><?php echo htmlspecialchars($user['email']); ?> • <?php echo ucfirst($user['role']); ?></p>
                        </div>
                        <span class="user-date"><?php echo date('M d', strtotime($user['created_at'])); ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> Recent Activity</h3>
                <a href="<?php echo BASE_URL; ?>/admin/reports?type=activity" class="view-all">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentActivity)): ?>
                    <p class="empty-message">No recent activity</p>
                <?php else: ?>
                    <?php 
                    // Show only the first 4 activities
                    $count = 0;
                    foreach ($recentActivity as $activity): 
                        if ($count >= 4) break;
                        $count++;
                    ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-circle" style="color: <?php 
                                echo $activity['action'] == 'LOGIN' ? '#10B981' : 
                                    ($activity['action'] == 'REGISTRATION' ? '#8B5CF6' : '#F97316'); 
                            ?>;"></i>
                        </div>
                        <div class="activity-info">
                            <p><?php echo htmlspecialchars($activity['description']); ?></p>
                            <small><?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.admin-dashboard {
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 20px;
}

.dashboard-header {
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

.date-display {
    background: white;
    padding: 12px 24px;
    border-radius: 50px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    color: black;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
}

.date-display i {
    color: #F97316;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 50px rgba(139, 92, 246, 0.15);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #7f2677);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}

.stat-icon i {
    font-size: 1.8rem;
    color: white;
}

.stat-content {
    margin-bottom: 15px;
}

.stat-value {
    display: block;
    font-size: 2.2rem;
    font-weight: 700;
    color: black;
    line-height: 1.2;
}

.stat-label {
    color: black;
    font-size: 0.9rem;
}

.stat-change {
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.stat-change.positive {
    color: #10B981;
}

.stat-change.negative {
    color: #EF4444;
}

/* Quick Actions */
.quick-actions {
    margin-bottom: 40px;
}

.section-title {
    font-size: 1.5rem;
    color: black;
    margin-bottom: 20px;
    font-weight: 600;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.action-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    border: 1px solid #E2E8F0;
}

.action-card:hover {
    transform: translateX(5px);
    border-color: #f06724;
    box-shadow: 0 10px 30px rgba(139, 92, 246, 0.15);
}

.action-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(249, 115, 22, 0.1));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #8B5CF6;
}

.action-content {
    flex: 1;
}

.action-content h3 {
    color: #1E293B;
    font-size: 1rem;
    margin-bottom: 3px;
}

.action-content p {
    color: #64748B;
    font-size: 0.85rem;
}

.action-card i:last-child {
    color: #f06724;
    opacity: 0;
    transition: all 0.3s ease;
}

.action-card:hover i:last-child {
    opacity: 1;
    transform: translateX(5px);
}

/* Dashboard Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
}

.dashboard-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.05);
}

.card-header {
    padding: 20px 25px;
    border-bottom: 1px solid #E2E8F0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    color: #1E293B;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-header h3 i {
    color: #f06724;
}

.view-all {
    color: #8B5CF6;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 600;
    transition: color 0.3s ease;
}

.view-all:hover {
    color: #F97316;
}

.card-body {
    padding: 20px 25px;
}

.empty-message {
    color: #94A3B8;
    text-align: center;
    padding: 30px;
    font-style: italic;
}

/* User Item */
.user-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px 0;
    border-bottom: 1px solid #F1F5F9;
}

.user-item:last-child {
    border-bottom: none;
}

.user-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
}

.user-info {
    flex: 1;
}

.user-info h4 {
    color: #1E293B;
    font-size: 0.95rem;
    margin-bottom: 3px;
}

.user-info p {
    color: #64748B;
    font-size: 0.85rem;
}

.user-date {
    color: #94A3B8;
    font-size: 0.8rem;
}

/* Activity Item */
.activity-item {
    display: flex;
    gap: 15px;
    padding: 12px 0;
    border-bottom: 1px solid #F1F5F9;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 20px;
    flex-shrink: 0;
}

.activity-icon i {
    font-size: 0.8rem;
}

.activity-info {
    flex: 1;
}

.activity-info p {
    color: #1E293B;
    font-size: 0.95rem;
    margin-bottom: 3px;
}

.activity-info small {
    color: #94A3B8;
    font-size: 0.8rem;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .stat-card,
    .action-card,
    .dashboard-card,
    .date-display {
        background: #1E293B;
    }
    
    .stat-value,
    .action-content h3,
    .user-info h4,
    .activity-info p {
        color: #F1F5F9;
    }
    
    .card-header {
        border-bottom-color: #334155;
    }
    
    .user-item,
    .activity-item {
        border-bottom-color: #334155;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>