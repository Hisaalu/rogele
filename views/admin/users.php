<?php
// File: /views/admin/users.php
$pageTitle = 'Manage Users - Admin - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

$page = $_GET['page'] ?? 1;
$role = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';
?>

<div class="admin-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-users-cog"></i>
                Manage Users
            </h1>
            <p class="page-subtitle">View, edit, and manage all users on the platform</p>
        </div>
        <a href="/rays-of-grace/admin/users/create" class="btn-primary">
            <i class="fas fa-user-plus"></i>
            Add New User
        </a>
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

    <!-- Filters -->
    <div class="filters-card">
        <form method="GET" class="filters-form">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search by name or email..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                >
            </div>
            
            <div class="filter-group">
                <select name="role">
                    <option value="">All Roles</option>
                    <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Administrators</option>
                    <option value="teacher" <?php echo $role === 'teacher' ? 'selected' : ''; ?>>Teachers</option>
                    <option value="learner" <?php echo $role === 'learner' ? 'selected' : ''; ?>>Learners</option>
                    <option value="external" <?php echo $role === 'external' ? 'selected' : ''; ?>>External Users</option>
                </select>
            </div>
            
            <button type="submit" class="btn-filter">Apply Filters</button>
            <a href="/rays-of-grace/admin/users" class="btn-reset">Reset</a>
        </form>
    </div>

    <!-- Users Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="empty-message">
                                <i class="fas fa-users"></i>
                                <p>No users found</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="user-cell">
                                <div class="user-avatar">
                                    <?php if (!empty($user['profile_photo'])): ?>
                                        <img src="/rays-of-grace/<?php echo $user['profile_photo']; ?>" alt="<?php echo $user['first_name']; ?>">
                                    <?php else: ?>
                                        <div class="avatar-placeholder" style="background: linear-gradient(135deg, #8B5CF6, #F97316);">
                                            <?php echo strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? 'S', 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="user-info">
                                    <div class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                    <div class="user-meta">ID: <?php echo $user['id']; ?></div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo $user['role']; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['is_suspended']): ?>
                                    <span class="status-badge suspended">Suspended</span>
                                <?php elseif (!$user['is_active']): ?>
                                    <span class="status-badge inactive">Inactive</span>
                                <?php else: ?>
                                    <span class="status-badge active">Active</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td class="actions-cell">
                                <a href="/rays-of-grace/admin/users/edit/<?php echo $user['id']; ?>" class="action-btn edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($user['is_suspended']): ?>
                                    <a href="/rays-of-grace/admin/users/activate/<?php echo $user['id']; ?>" class="action-btn activate" title="Activate" onclick="return confirm('Activate this user?')">
                                        <i class="fas fa-check-circle"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="/rays-of-grace/admin/users/suspend/<?php echo $user['id']; ?>" class="action-btn suspend" title="Suspend" onclick="return confirm('Suspend this user?')">
                                        <i class="fas fa-ban"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($_SESSION['user_id'] != $user['id']): ?>
                                    <a href="/rays-of-grace/admin/users/delete/<?php echo $user['id']; ?>" class="action-btn delete" title="Delete" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (!empty($users) && $totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&role=<?php echo $role; ?>&search=<?php echo urlencode($search); ?>" class="page-link">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&role=<?php echo $role; ?>&search=<?php echo urlencode($search); ?>" 
                   class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&role=<?php echo $role; ?>&search=<?php echo urlencode($search); ?>" class="page-link">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.admin-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.page-title {
    font-size: 2rem;
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

.btn-primary {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    box-shadow: 0 4px 6px rgba(139, 92, 246, 0.3);
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);
}

/* Filters */
.filters-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.filters-form {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
}

.search-box {
    flex: 1;
    min-width: 250px;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #94A3B8;
}

.search-box input {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border: 2px solid #E2E8F0;
    border-radius: 50px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: #8B5CF6;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.filter-group select {
    padding: 12px 20px;
    border: 2px solid #E2E8F0;
    border-radius: 50px;
    font-size: 0.95rem;
    background: white;
    min-width: 150px;
    cursor: pointer;
}

.btn-filter {
    background: #8B5CF6;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-filter:hover {
    background: #7C3AED;
    transform: translateY(-2px);
}

.btn-reset {
    color: #64748B;
    text-decoration: none;
    padding: 12px 20px;
    border-radius: 50px;
    transition: all 0.3s ease;
}

.btn-reset:hover {
    background: #F1F5F9;
    color: #1E293B;
}

/* Table */
.table-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.05);
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #F8FAFC;
    color: #1E293B;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 16px 20px;
    text-align: left;
    border-bottom: 2px solid #E2E8F0;
}

.data-table td {
    padding: 16px 20px;
    border-bottom: 1px solid #F1F5F9;
    color: #1E293B;
}

.data-table tr:hover td {
    background: #F8FAFC;
}

/* User Cell */
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
    font-size: 1rem;
}

.user-info {
    flex: 1;
}

.user-name {
    font-weight: 600;
    margin-bottom: 3px;
}

.user-meta {
    font-size: 0.8rem;
    color: #64748B;
}

/* Role Badges */
.role-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
}

.role-admin {
    background: #FEF2F2;
    color: #B91C1C;
}

.role-teacher {
    background: #EFF6FF;
    color: #1E40AF;
}

.role-learner {
    background: #F0FDF4;
    color: #166534;
}

.role-external {
    background: #FEF3C7;
    color: #92400E;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-badge.active {
    background: #F0FDF4;
    color: #166534;
}

.status-badge.inactive {
    background: #F1F5F9;
    color: #64748B;
}

.status-badge.suspended {
    background: #FEF2F2;
    color: #B91C1C;
}

/* Action Buttons */
.actions-cell {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.action-btn {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
}

.action-btn.edit {
    background: #EFF6FF;
    color: #2563EB;
}

.action-btn.edit:hover {
    background: #2563EB;
    color: white;
}

.action-btn.suspend {
    background: #FEF3C7;
    color: #D97706;
}

.action-btn.suspend:hover {
    background: #D97706;
    color: white;
}

.action-btn.activate {
    background: #F0FDF4;
    color: #059669;
}

.action-btn.activate:hover {
    background: #059669;
    color: white;
}

.action-btn.delete {
    background: #FEF2F2;
    color: #DC2626;
}

.action-btn.delete:hover {
    background: #DC2626;
    color: white;
}

/* Empty Message */
.empty-message {
    text-align: center;
    padding: 60px !important;
    color: #94A3B8;
}

.empty-message i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-message p {
    font-size: 1.1rem;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    padding: 20px;
    border-top: 1px solid #E2E8F0;
}

.page-link {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    text-decoration: none;
    color: #1E293B;
    transition: all 0.3s ease;
}

.page-link:hover {
    background: #F1F5F9;
}

.page-link.active {
    background: #8B5CF6;
    color: white;
}

/* Alert */
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

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .filters-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-box,
    .filter-group select,
    .btn-filter,
    .btn-reset {
        width: 100%;
    }
    
    .data-table td {
        min-width: 120px;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .filters-card,
    .table-card {
        background: #1E293B;
    }
    
    .data-table th {
        background: #334155;
        color: #F1F5F9;
    }
    
    .data-table td {
        color: #F1F5F9;
        border-bottom-color: #334155;
    }
    
    .data-table tr:hover td {
        background: #334155;
    }
    
    .page-link {
        color: #94A3B8;
    }
    
    .page-link:hover {
        background: #334155;
        color: #F1F5F9;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>