<?php
// File: /views/admin/users.php
$pageTitle = 'Users | ROGELE';
require_once __DIR__ . '/../layouts/admin_header.php';

$page   = $_GET['page'] ?? 1;
$role   = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';
$users  = $users ?? [];
$totalPages = $totalPages ?? 1;
?>

<div class="users-container">
    <header class="page-header">
        <div class="header-text">
            <h1 class="page-title">
                <i class="fas fa-users-cog" aria-hidden="true"></i>
                <span>Manage Users</span>
            </h1>
            <p class="page-subtitle">View, edit, suspend, and manage all users on ROGELE</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/admin/users/create" class="btn-primary">
            <i class="fas fa-user-plus" aria-hidden="true"></i>
            <span>Add New User</span>
        </a>
    </header>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" role="alert">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <div class="alert-content">
                <strong>Success!</strong>
                <p><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Close alert">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error" role="alert">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
            <div class="alert-content">
                <strong>Error!</strong>
                <p><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Close alert">&times;</button>
        </div>
    <?php endif; ?>

    <section class="filters-card">
        <form method="GET" class="filters-form" id="filterForm">
            <div class="search-box">
                <i class="fas fa-search" aria-hidden="true"></i>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search by name, email, or ID..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                    aria-label="Search users"
                >
            </div>
            
            <div class="filter-group">
                <select name="role" onchange="this.form.submit()" aria-label="Filter by role">
                    <option value="">All Roles</option>
                    <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Administrators</option>
                    <option value="teacher" <?php echo $role === 'teacher' ? 'selected' : ''; ?>>Teachers</option>
                    <option value="learner" <?php echo $role === 'learner' ? 'selected' : ''; ?>>Learners</option>
                    <option value="external" <?php echo $role === 'external' ? 'selected' : ''; ?>>External Users</option>
                </select>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn-filter">Apply Filters</button>
                <a href="<?php echo BASE_URL; ?>/admin/users" class="btn-reset">Reset</a>
            </div>
        </form>
    </section>

    <main class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="empty-message">
                                <i class="fas fa-users" aria-hidden="true"></i>
                                <p>No users found</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="user-cell">
                                <div class="user-avatar">
                                    <?php if (!empty($user['profile_photo'])): ?>
                                        <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                                    <?php else: ?>
                                        <div class="avatar-placeholder" style="background-color: #f06724;">
                                            <?php 
                                            $initial1 = strtoupper(substr($user['first_name'] ?? 'U', 0, 1));
                                            $initial2 = strtoupper(substr($user['last_name'] ?? 'S', 0, 1));
                                            echo $initial1 . $initial2;
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="user-info">
                                    <div class="user-name"><?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></div>
                                    <div class="user-meta">ID: <?php echo htmlspecialchars($user['id'] ?? ''); ?></div>
                                </div>
                            </td>
                            <td class="email-cell"><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo htmlspecialchars($user['role'] ?? 'default'); ?>">
                                    <?php echo ucfirst(htmlspecialchars($user['role'] ?? '')); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($user['is_suspended'])): ?>
                                    <span class="status-badge suspended">Suspended</span>
                                <?php elseif (isset($user['is_active']) && !$user['is_active']): ?>
                                    <span class="status-badge inactive">Inactive</span>
                                <?php else: ?>
                                    <span class="status-badge active">Active</span>
                                <?php endif; ?>
                            </td>
                            <td class="date-cell">
                                <?php echo !empty($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A'; ?>
                            </td>
                            <td class="actions-cell">
                                <a href="<?php echo BASE_URL; ?>/admin/users/edit/<?php echo $user['id']; ?>" class="action-btn edit" title="Edit User">
                                    <i class="fas fa-edit" aria-hidden="true"></i>
                                </a>
                                
                                <?php if (!empty($user['is_suspended'])): ?>
                                    <a href="<?php echo BASE_URL; ?>/admin/users/activate/<?php echo $user['id']; ?>" class="action-btn activate" title="Activate User" onclick="return confirm('Activate this user?')">
                                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>/admin/users/suspend/<?php echo $user['id']; ?>" class="action-btn suspend" title="Suspend User" onclick="return confirm('Suspend this user? They will not be able to log in.')">
                                        <i class="fas fa-ban" aria-hidden="true"></i>
                                    </a>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $user['id']): ?>
                                    <a href="<?php echo BASE_URL; ?>/admin/users/delete/<?php echo $user['id']; ?>" class="action-btn delete" title="Delete User" onclick="return confirmDelete(<?php echo $user['id']; ?>, '<?php echo addslashes(htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))); ?>')">
                                        <i class="fas fa-trash" aria-hidden="true"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($users) && $totalPages > 1): ?>
            <nav class="pagination" aria-label="Users pagination">
                <?php if ($page > 1): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/users?page=<?php echo $page - 1; ?>&role=<?php echo urlencode($role); ?>&search=<?php echo urlencode($search); ?>" class="page-link" aria-label="Previous Page">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/users?page=<?php echo $i; ?>&role=<?php echo urlencode($role); ?>&search=<?php echo urlencode($search); ?>" 
                    class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/users?page=<?php echo $page + 1; ?>&role=<?php echo urlencode($role); ?>&search=<?php echo urlencode($search); ?>" class="page-link" aria-label="Next Page">
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </main>
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
    --shadow-sm: 0 4px 12px rgba(0,0,0,0.03);
    --shadow-md: 0 10px 30px rgba(0,0,0,0.05);
    --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.users-container, 
.users-container * {
    box-sizing: border-box;
}

.users-container {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: clamp(16px, 4vw, 32px);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: clamp(20px, 3vw, 30px);
    flex-wrap: wrap;
    gap: 12px;
}

.header-text {
    flex: 0 1 auto;
}

.page-title {
    font-size: clamp(1.4rem, 3.5vw, 2.2rem);
    font-weight: 700;
    color: var(--primary-purple);
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

.btn-primary {
    background-color: var(--primary-purple);
    color: white;
    border: none;
    padding: 10px 22px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(127, 38, 119, 0.25);
    transition: var(--transition);
    white-space: nowrap;
    align-self: flex-start;
}

.btn-primary:hover {
    transform: translateY(-2px);
    background-color: var(--accent-orange);
    box-shadow: 0 8px 20px rgba(240, 103, 36, 0.35);
}

.alert {
    padding: 14px 18px;
    border-radius: var(--radius-md);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
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
    margin-bottom: 2px;
}

.alert-content p {
    font-size: 0.9rem;
    margin: 0;
    opacity: 0.9;
}

.alert-close {
    background: none;
    border: none;
    font-size: 1.3rem;
    cursor: pointer;
    color: currentColor;
    opacity: 0.7;
    padding: 0 4px;
    transition: opacity 0.2s ease;
}

.alert-close:hover {
    opacity: 1;
}

@keyframes slideDown {
    from { transform: translateY(-10px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.filters-card {
    background: var(--bg-surface);
    border-radius: var(--radius-md);
    padding: clamp(14px, 2.5vw, 20px);
    margin-bottom: clamp(20px, 3vw, 25px);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
}

.filters-form {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}

.search-box {
    flex: 1 1 240px;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
}

.search-box input {
    width: 100%;
    padding: 10px 14px 10px 40px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: 0.9rem;
    transition: var(--transition);
}

.search-box input:focus {
    outline: none;
    border-color: var(--accent-orange);
    box-shadow: 0 0 0 3px rgba(240, 103, 36, 0.15);
}

.filter-group {
    flex: 0 1 180px;
}

.filter-group select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: 0.9rem;
    background: var(--bg-surface);
    cursor: pointer;
    transition: var(--transition);
}

.filter-group select:focus {
    outline: none;
    border-color: var(--accent-orange);
    box-shadow: 0 0 0 3px rgba(240, 103, 36, 0.15);
}

.filter-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.btn-filter, .btn-reset {
    padding: 10px 20px;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
}

.btn-filter {
    background: var(--primary-purple);
    color: white;
    border: none;
}

.btn-filter:hover {
    background: var(--accent-orange);
}

.btn-reset {
    background: #F1F5F9;
    color: var(--text-dark);
    border: 1px solid var(--border-color);
}

.btn-reset:hover {
    background: #E2E8F0;
}

.table-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 650px;
}

.data-table th {
    background: #F8FAFC;
    color: var(--text-dark);
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 18px;
    text-align: left;
    border-bottom: 2px solid var(--border-color);
}

.data-table th.text-right {
    text-align: right;
}

.data-table td {
    padding: 14px 18px;
    border-bottom: 1px solid #F1F5F9;
    color: var(--text-dark);
    vertical-align: middle;
}

.data-table tr:hover td {
    background: #F8FAFC;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
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

.user-info {
    flex: 1;
    min-width: 0;
}

.user-name {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-meta {
    font-size: 0.775rem;
    color: var(--text-muted);
}

.email-cell {
    font-size: 0.875rem;
    word-break: break-word;
}

.date-cell {
    font-size: 0.85rem;
    color: var(--text-muted);
    white-space: nowrap;
}

.role-badge, .status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 50px;
    font-size: 0.775rem;
    font-weight: 600;
    white-space: nowrap;
}

.role-admin { background: #FEF2F2; color: #B91C1C; }
.role-teacher { background: #EFF6FF; color: #1E40AF; }
.role-learner { background: #F0FDF4; color: #166534; }
.role-external { background: #FEF3C7; color: #92400E; }
.role-default { background: #F1F5F9; color: var(--text-dark); }

.status-badge.active { background: #F0FDF4; color: #166534; }
.status-badge.inactive { background: #F1F5F9; color: var(--text-muted); }
.status-badge.suspended { background: #FEF2F2; color: #B91C1C; }

.actions-cell {
    display: flex;
    gap: 6px;
    justify-content: flex-end;
    align-items: center;
}

.action-btn {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: var(--transition);
    font-size: 0.875rem;
}

.action-btn.edit { background: #EFF6FF; color: var(--primary-purple); }
.action-btn.edit:hover { background: var(--accent-orange); color: white; transform: translateY(-2px); }

.action-btn.suspend { background: #FEF3C7; color: #D97706; }
.action-btn.suspend:hover { background: #D97706; color: white; transform: translateY(-2px); }

.action-btn.activate { background: #F0FDF4; color: #059669; }
.action-btn.activate:hover { background: #059669; color: white; transform: translateY(-2px); }

.action-btn.delete { background: #FEF2F2; color: #DC2626; }
.action-btn.delete:hover { background: #DC2626; color: white; transform: translateY(-2px); }

.empty-message {
    text-align: center;
    padding: 48px 20px !important;
    color: var(--text-muted);
}

.empty-message i {
    font-size: 2.5rem;
    margin-bottom: 12px;
    opacity: 0.5;
}

.empty-message p {
    font-size: 0.9rem;
    margin: 0;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 6px;
    padding: 16px 20px;
    border-top: 1px solid var(--border-color);
    flex-wrap: wrap;
}

.page-link {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    text-decoration: none;
    color: var(--text-dark);
    font-size: 0.875rem;
    font-weight: 500;
    transition: var(--transition);
    border: 1px solid transparent;
}

.page-link:hover {
    background: #F1F5F9;
}

.page-link.active {
    background: var(--primary-purple);
    color: white;
}

@media (max-width: 576px) {
    .page-header {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }
    
    .btn-primary {
        width: 100%;
        justify-content: center;
    }

    .filters-form {
        flex-direction: column;
        align-items: stretch;
    }

    .search-box, .filter-group {
        flex: 1 1 100%;
    }

    .filter-actions {
        width: 100%;
    }

    .btn-filter, .btn-reset {
        flex: 1;
        text-align: center;
    }
}
</style>

<script>
function confirmDelete(userId, userName) {
    return confirm(`Are you sure you want to permanently delete ${userName}? This action cannot be undone.`);
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>