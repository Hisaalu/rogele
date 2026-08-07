<?php
// File: /views/admin/dashboard.php
$pageTitle = 'Admin Dashboard | ROGELE';
require_once __DIR__ . '/../layouts/admin_header.php';

$totalUsers     = $totalUsers ?? 0;
$totalTeachers  = $totalTeachers ?? 0;
$totalLearners  = $totalLearners ?? 0;
$totalExternal  = $totalExternal ?? 0;
$recentUsers    = $recentUsers ?? [];
$recentActivity = $recentActivity ?? [];
?>

<div class="admin-dashboard">
    <header class="dashboard-header">
        <div class="header-text">
            <h1 class="page-title">
                <i class="fas fa-tachometer-alt" aria-hidden="true"></i>
                <span>Admin Dashboard</span>
            </h1>
            <p class="page-subtitle">
                Welcome back, <?php 
                    $fullName  = $_SESSION['user_name'] ?? '';
                    $firstName = explode(' ', trim($fullName))[0];
                    echo htmlspecialchars($firstName); 
                ?>! Here's what's happening with ROGELE.
            </p>
        </div>
        <div class="date-display">
            <i class="fas fa-calendar" aria-hidden="true"></i>
            <span><?php echo date('l, F j, Y'); ?></span>
        </div>
    </header>

    <section class="stats-grid" aria-label="Key Performance Statistics">
        <article class="stat-card">
            <div class="stat-icon" style="background-color: #f06724;">
                <i class="fas fa-users" aria-hidden="true"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo number_format($totalUsers); ?></span>
                <span class="stat-label">Total Users</span>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-icon" style="background-color: #f06724;">
                <i class="fas fa-chalkboard-teacher" aria-hidden="true"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo number_format($totalTeachers); ?></span>
                <span class="stat-label">Teachers</span>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-icon" style="background-color: #f06724;">
                <i class="fas fa-user-graduate" aria-hidden="true"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo number_format($totalLearners); ?></span>
                <span class="stat-label">Learners</span>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-icon" style="background-color: #f06724;">
                <i class="fas fa-globe" aria-hidden="true"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo number_format($totalExternal); ?></span>
                <span class="stat-label">External Users</span>
            </div>
        </article>
    </section>

    <section class="quick-actions">
        <h2 class="section-title">Quick Actions</h2>
        <div class="actions-grid">
            <a href="<?php echo BASE_URL; ?>/admin/users/create" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-user-plus" aria-hidden="true"></i>
                </div>
                <div class="action-content">
                    <h3>Add New User</h3>
                    <p>Create a new user account</p>
                </div>
                <i class="fas fa-arrow-right action-arrow" aria-hidden="true"></i>
            </a>

            <a href="<?php echo BASE_URL; ?>/admin/reports" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-chart-bar" aria-hidden="true"></i>
                </div>
                <div class="action-content">
                    <h3>Generate Report</h3>
                    <p>View system analytics</p>
                </div>
                <i class="fas fa-arrow-right action-arrow" aria-hidden="true"></i>
            </a>

            <a href="<?php echo BASE_URL; ?>/admin/settings" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-cog" aria-hidden="true"></i>
                </div>
                <div class="action-content">
                    <h3>System Settings</h3>
                    <p>Configure platform</p>
                </div>
                <i class="fas fa-arrow-right action-arrow" aria-hidden="true"></i>
            </a>

            <a href="<?php echo BASE_URL; ?>/admin/users" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-users-cog" aria-hidden="true"></i>
                </div>
                <div class="action-content">
                    <h3>Manage Users</h3>
                    <p>View all users</p>
                </div>
                <i class="fas fa-arrow-right action-arrow" aria-hidden="true"></i>
            </a>
        </div>
    </section>

    <div class="dashboard-grid">
        <section class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-user-plus" aria-hidden="true"></i> Recent Users</h3>
                <a href="<?php echo BASE_URL; ?>/admin/users" class="view-all">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentUsers)): ?>
                    <p class="empty-message">No recent users</p>
                <?php else: ?>
                    <div class="user-list">
                        <?php 
                        $displayUsers = array_slice($recentUsers, 0, 4);
                        foreach ($displayUsers as $user): 
                        ?>
                            <div class="user-item">
                                <div class="user-avatar">
                                    <?php if (!empty($user['profile_photo'])): ?>
                                        <img src="<?php echo BASE_URL; ?>/<?php echo $user['profile_photo']; ?>" alt="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                                    <?php else: ?>
                                        <div class="avatar-placeholder" style="background-color: #f06724;">
                                            <?php echo strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? 'S', 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="user-info">
                                    <h4><?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></h4>
                                    <p><?php echo htmlspecialchars($user['email'] ?? ''); ?> • <?php echo ucfirst($user['role'] ?? ''); ?></p>
                                </div>
                                <span class="user-date"><?php echo !empty($user['created_at']) ? date('M d', strtotime($user['created_at'])) : ''; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-history" aria-hidden="true"></i> Recent Activity</h3>
                <a href="<?php echo BASE_URL; ?>/admin/reports?type=activity" class="view-all">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentActivity)): ?>
                    <p class="empty-message">No recent activity</p>
                <?php else: ?>
                    <div class="activity-list">
                        <?php 
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
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<style>
:root {
    --primary-color: #7f2677;
    --accent-orange: #f06724;
    --text-dark: #000;
    --text-muted: #555;
    --bg-surface: #FFFFFF;
    --border-color: #E2E8F0;
    --radius-lg: 20px;
    --radius-md: 16px;
    --shadow-sm: 0 4px 12px rgba(0, 0, 0, 0.03);
    --shadow-md: 0 10px 30px rgba(0, 0, 0, 0.05);
    --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.admin-dashboard, 
.admin-dashboard * {
    box-sizing: border-box;
}

.admin-dashboard {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: clamp(16px, 4vw, 32px);
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: clamp(20px, 3vw, 32px);
    flex-wrap: wrap;
    gap: 12px;
}

.header-text {
    flex: 0 1 auto; 
}

.page-title {
    font-size: clamp(1.4rem, 3.5vw, 2rem);
    font-weight: 700;
    color: var(--primary-color);
    margin: 0 0 6px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-subtitle {
    color: var(--text-muted);
    font-size: clamp(0.85rem, 2vw, 0.95rem);
    margin: 0;
}

.date-display {
    background: var(--bg-surface);
    padding: 8px 16px;
    border-radius: 50px;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
    color: var(--text-dark);
    font-weight: 600;
    font-size: clamp(0.8rem, 1.8vw, 0.9rem);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    align-self: flex-start;
    margin-top: 4px;
}

.date-display i {
    font-size: 0.85rem;
    color: var(--accent-orange);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr));
    gap: clamp(16px, 2.5vw, 24px);
    margin-bottom: clamp(24px, 4vw, 36px);
}

.stat-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    padding: clamp(16px, 3vw, 24px);
    box-shadow: var(--shadow-md);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    border: 1px solid var(--border-color);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 36px rgba(127, 38, 119, 0.1);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background-color: var(--primary-color); 
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
}

.stat-icon i {
    font-size: 1.4rem;
    color: white;
}

.stat-content {
    margin-bottom: 12px;
}

.stat-value {
    display: block;
    font-size: clamp(1.6rem, 3.5vw, 2rem);
    font-weight: 700;
    color: var(--text-dark);
    line-height: 1.2;
}

.stat-label {
    color: var(--text-muted);
    font-size: 0.85rem;
    font-weight: 500;
}

.stat-change {
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
    flex-wrap: wrap;
}

.stat-change.positive { color: #10B981; }
.stat-change.negative { color: #EF4444; }

.quick-actions {
    margin-bottom: clamp(24px, 4vw, 36px);
}

.section-title {
    font-size: clamp(1.15rem, 2.5vw, 1.35rem);
    color: var(--text-dark);
    margin: 0 0 16px 0;
    font-weight: 700;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr));
    gap: clamp(12px, 2vw, 20px);
}

.action-card {
    background: var(--bg-surface);
    border-radius: var(--radius-md);
    padding: clamp(14px, 2.5vw, 18px);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    border: 1px solid var(--border-color);
}

.action-card:hover {
    transform: translateX(4px);
    border-color: var(--accent-orange);
    box-shadow: var(--shadow-md);
}

.action-icon {
    width: 44px;
    height: 44px;
    background: rgba(139, 92, 246, 0.08);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: var(--accent-orange);
    flex-shrink: 0;
}

.action-content {
    flex: 1;
    min-width: 0;
}

.action-content h3 {
    color: var(--text-dark);
    font-size: 0.9rem;
    margin: 0 0 2px 0;
    font-weight: 600;
}

.action-content p {
    color: var(--text-muted);
    font-size: 0.8rem;
    margin: 0;
}

.action-arrow {
    color: var(--accent-orange);
    opacity: 0;
    transform: translateX(-5px);
    transition: var(--transition);
    flex-shrink: 0;
}

.action-card:hover .action-arrow {
    opacity: 1;
    transform: translateX(0);
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 400px), 1fr));
    gap: clamp(16px, 2.5vw, 24px);
}

.dashboard-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    width: 100%;
}

.card-header {
    padding: 16px clamp(16px, 3vw, 24px);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.card-header h3 {
    color: var(--text-dark);
    font-size: 0.95rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-header h3 i {
    color: var(--accent-orange);
}

.view-all {
    color: var(--primary-color);
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
    transition: var(--transition);
    white-space: nowrap;
}

.view-all:hover {
    color: var(--accent-orange);
}

.card-body {
    padding: 8px clamp(16px, 3vw, 24px) 16px;
    flex: 1;
}

.empty-message {
    color: var(--text-muted);
    text-align: center;
    padding: 24px 0;
    font-style: italic;
    margin: 0;
}

.user-item, 
.activity-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #F1F5F9;
}

.user-item:last-child, 
.activity-item:last-child {
    border-bottom: none;
}

.user-avatar {
    width: 40px;
    height: 40px;
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
    font-size: 0.85rem;
}

.user-info, 
.activity-info {
    flex: 1;
    min-width: 0;
}

.user-info h4 {
    color: var(--text-dark);
    font-size: 0.875rem;
    font-weight: 600;
    margin: 0 0 2px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-info p {
    color: var(--text-muted);
    font-size: 0.8rem;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-date {
    color: var(--text-muted);
    font-size: 0.775rem;
    font-weight: 500;
    flex-shrink: 0;
    margin-left: auto;
}

.activity-item {
    align-items: flex-start;
}

.activity-icon {
    width: 16px;
    flex-shrink: 0;
    padding-top: 3px;
}

.activity-icon i {
    font-size: 0.6rem;
}

.activity-info p {
    color: var(--text-dark);
    font-size: 0.85rem;
    margin: 0 0 2px 0;
    line-height: 1.35;
    word-break: break-word;
}

.activity-info small {
    color: var(--text-muted);
    font-size: 0.75rem;
    display: block;
}

@media (max-width: 576px) {
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px; 
    }

    .date-display {
        margin-top: 0;
    }

    .user-item {
        flex-wrap: wrap;
    }

    .user-date {
        width: 100%;
        margin-left: 52px;
        margin-top: -4px;
    }

    .action-arrow {
        display: none;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>