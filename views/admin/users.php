<?php
// File: /views/admin/users.php
$pageTitle = 'Manage Users - Admin - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

// Get parameters
$page = $_GET['page'] ?? 1;
$role = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';
?>

<div class="users-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-users-cog"></i>
                Manage Users
            </h1>
            <p class="page-subtitle">View, edit, suspend, and manage all users on the platform</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/admin/users/create" class="btn-primary">
            <i class="fas fa-user-plus"></i>
            Add New User
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <div class="alert-content">
                <strong>Success!</strong>
                <p><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <div class="alert-content">
                <strong>Error!</strong>
                <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Filters and Search -->
    <div class="filters-card">
        <form method="GET" class="filters-form" id="filterForm">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search by name, email, or ID..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                >
            </div>
            
            <div class="filter-group">
                <select name="role" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Administrators</option>
                    <option value="teacher" <?php echo $role === 'teacher' ? 'selected' : ''; ?>>Teachers</option>
                    <option value="learner" <?php echo $role === 'learner' ? 'selected' : ''; ?>>Learners</option>
                    <option value="external" <?php echo $role === 'external' ? 'selected' : ''; ?>>External Users</option>
                </select>
            </div>
            
            <button type="submit" class="btn-filter">Apply Filters</button>
            <a href="<?php echo BASE_URL; ?>/admin/users" class="btn-reset">Reset</a>
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
                                        <img src="<?php echo BASE_URL; ?>/<?php echo $user['profile_photo']; ?>" alt="<?php echo $user['first_name']; ?>">
                                    <?php else: ?>
                                        <div class="avatar-placeholder" style="background: linear-gradient(135deg, #f06724);">
                                            <?php 
                                            $initial1 = strtoupper(substr($user['first_name'] ?? 'U', 0, 1));
                                            $initial2 = strtoupper(substr($user['last_name'] ?? 'S', 0, 1));
                                            echo $initial1 . $initial2;
                                            ?>
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
                                <!-- Edit Button -->
                                <a href="<?php echo BASE_URL; ?>/admin/users/edit/<?php echo $user['id']; ?>" class="action-btn edit" title="Edit User">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <!-- Suspend/Activate Button -->
                                <?php if ($user['is_suspended']): ?>
                                    <a href="<?php echo BASE_URL; ?>/admin/users/activate/<?php echo $user['id']; ?>" class="action-btn activate" title="Activate User" onclick="return confirm('Activate this user?')">
                                        <i class="fas fa-check-circle"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>/admin/users/suspend/<?php echo $user['id']; ?>" class="action-btn suspend" title="Suspend User" onclick="return confirm('Suspend this user? They will not be able to log in.')">
                                        <i class="fas fa-ban"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Delete Button (cannot delete yourself) -->
                                <?php if ($_SESSION['user_id'] != $user['id']): ?>
                                    <a href="<?php echo BASE_URL; ?>/admin/users/delete/<?php echo $user['id']; ?>" class="action-btn delete" title="Delete User" onclick="return confirmDelete(<?php echo $user['id']; ?>, '<?php echo addslashes($user['first_name'] . ' ' . $user['last_name']); ?>')">
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
                <a href="?page=<?php echo $page - 1; ?>&role=<?php echo urlencode($role); ?>&search=<?php echo urlencode($search); ?>" class="page-link">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&role=<?php echo urlencode($role); ?>&search=<?php echo urlencode($search); ?>" 
                   class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&role=<?php echo urlencode($role); ?>&search=<?php echo urlencode($search); ?>" class="page-link">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Main Container */
.users-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
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

.btn-primary {
    background: linear-gradient(135deg, #7f2677);
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

/* Alert Messages */
.alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 15px;
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
    margin-bottom: 3px;
}

.alert-content p {
    font-size: 0.95rem;
    opacity: 0.9;
}

.alert-close {
    background: none;
    border: none;
    font-size: 1.3rem;
    cursor: pointer;
    color: currentColor;
    opacity: 0.7;
    padding: 0 5px;
    transition: opacity 0.3s ease;
}

.alert-close:hover {
    opacity: 1;
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

/* Filters Card */
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
    border-color: #f06724;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.filter-group select {
    padding: 12px 20px;
    border: 2px solid #f06724;
    border-radius: 50px;
    font-size: 0.95rem;
    background: white;
    min-width: 150px;
    cursor: pointer;
}

.btn-filter {
    background: #7f2677;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-filter:hover {
    background: #f06724;
    transform: translateY(-2px);
}

.btn-reset {
    color: white;
    text-decoration: none;
    padding: 12px 20px;
    border-radius: 50px;
    transition: all 0.3s ease;
    background: #7f2677;
}

.btn-reset:hover {
    background: #f06724;
    color: white;
}

/* Table Card */
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
    color: black;
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
    color: black;
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
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 1.1rem;
}

.action-btn.edit {
    background: #EFF6FF;
    color: #7f2677;
}

.action-btn.edit:hover {
    background: #f06724;
    color: white;
    transform: translateY(-2px);
}

.action-btn.suspend {
    background: #FEF3C7;
    color: #D97706;
}

.action-btn.suspend:hover {
    background: #D97706;
    color: white;
    transform: translateY(-2px);
}

.action-btn.activate {
    background: #F0FDF4;
    color: #059669;
}

.action-btn.activate:hover {
    background: #059669;
    color: white;
    transform: translateY(-2px);
}

.action-btn.delete {
    background: #FEF2F2;
    color: #DC2626;
}

.action-btn.delete:hover {
    background: #DC2626;
    color: white;
    transform: translateY(-2px);
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
    border-radius: 10px;
    text-decoration: none;
    color: black;
    transition: all 0.3s ease;
}

.page-link:hover {
    background: #F1F5F9;
}

.page-link.active {
    background: #7f2677;
    color: white;
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
    
    .actions-cell {
        justify-content: center;
    }
}

/* Dark Mode */
/* @media (prefers-color-scheme: dark) {
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
    
    .btn-reset {
        color: #94A3B8;
    }
    
    .btn-reset:hover {
        background: #334155;
        color: #F1F5F9;
    }
} */
</style>

<script>
function confirmDelete(userId, userName) {
    return confirm(`Are you sure you want to permanently delete ${userName}? This action cannot be undone.`);
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>