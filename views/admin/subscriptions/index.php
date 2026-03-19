<?php
// File: /views/admin/subscriptions/index.php
$pageTitle = 'Manage Subscriptions - Admin - Rays of Grace';
require_once __DIR__ . '/../../layouts/header.php';

// Get parameters
$page = $_GET['page'] ?? 1;
$status = $_GET['status'] ?? '';
$planType = $_GET['plan_type'] ?? '';
$search = $_GET['search'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
?>

<div class="subscriptions-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-credit-card"></i>
                Manage Subscriptions
            </h1>
            <p class="page-subtitle">View and manage all user subscriptions on the platform</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo BASE_URL; ?>/admin/subscriptions/export?status=<?php echo urlencode($status); ?>&plan_type=<?php echo urlencode($planType); ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>" class="btn-export">
                <i class="fas fa-download"></i>
                Export CSV
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/subscriptions/reports" class="btn-reports">
                <i class="fas fa-chart-bar"></i>
                View Reports
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <?php if (!empty($stats)): ?>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon active">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3>Active</h3>
                <p class="stat-number"><?php echo $stats['active']; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon expired">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3>Expired</h3>
                <p class="stat-number"><?php echo $stats['expired']; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-info">
                <h3>Pending</h3>
                <p class="stat-number"><?php echo $stats['pending']; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon revenue">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-info">
                <h3>Revenue</h3>
                <p class="stat-number">UGX <?php echo number_format($stats['total_revenue']); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
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
                    placeholder="Search by user name, email, or transaction ID..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                >
            </div>
            
            <div class="filter-group">
                <select name="status">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="expired" <?php echo $status === 'expired' ? 'selected' : ''; ?>>Expired</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            
            <div class="filter-group">
                <select name="plan_type">
                    <option value="">All Plans</option>
                    <option value="monthly" <?php echo $planType === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                    <option value="termly" <?php echo $planType === 'termly' ? 'selected' : ''; ?>>Termly</option>
                    <option value="yearly" <?php echo $planType === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                </select>
            </div>
            
            <div class="filter-group date-range">
                <input type="date" name="date_from" placeholder="From" value="<?php echo htmlspecialchars($dateFrom); ?>">
                <span>to</span>
                <input type="date" name="date_to" placeholder="To" value="<?php echo htmlspecialchars($dateTo); ?>">
            </div>
            
            <button type="submit" class="btn-filter">Apply Filters</button>
            <a href="<?php echo BASE_URL; ?>/admin/subscriptions" class="btn-reset">Reset</a>
        </form>
    </div>

    <!-- Subscriptions Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Plan</th>
                        <th>Amount</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($subscriptions)): ?>
                        <tr>
                            <td colspan="8" class="empty-message">
                                <i class="fas fa-credit-card"></i>
                                <p>No subscriptions found</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subscriptions as $sub): ?>
                        <tr>
                            <td><span class="subscription-id">#<?php echo $sub['id']; ?></span></td>
                            <td class="user-cell">
                                <div class="user-info">
                                    <div class="user-name"><?php echo htmlspecialchars($sub['first_name'] . ' ' . $sub['last_name']); ?></div>
                                    <div class="user-email"><?php echo htmlspecialchars($sub['email']); ?></div>
                                </div>
                            </td>
                            <td>
                                <span class="plan-badge <?php echo $sub['plan_type']; ?>">
                                    <?php echo ucfirst($sub['plan_type']); ?>
                                    <?php if ($sub['is_upgrade']): ?>
                                        <i class="fas fa-arrow-up" title="Upgraded"></i>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="amount-cell">UGX <?php echo number_format($sub['amount']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($sub['start_date'])); ?></td>
                            <td>
                                <span class="end-date <?php echo strtotime($sub['end_date']) < time() ? 'expired' : ''; ?>">
                                    <?php echo date('M d, Y', strtotime($sub['end_date'])); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $sub['status']; ?>">
                                    <?php echo ucfirst($sub['status']); ?>
                                </span>
                            </td>
                            <td class="actions-cell">
                                <a href="<?php echo BASE_URL; ?>/admin/subscriptions/view/<?php echo $sub['id']; ?>" class="action-btn view" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if ($sub['status'] === 'active'): ?>
                                    <a href="<?php echo BASE_URL; ?>/admin/subscriptions/cancel/<?php echo $sub['id']; ?>" 
                                       class="action-btn cancel" 
                                       title="Cancel Subscription"
                                       onclick="return confirm('Are you sure you want to cancel this subscription?')">
                                        <i class="fas fa-ban"></i>
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
        <?php if (!empty($subscriptions) && $totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($status); ?>&plan_type=<?php echo urlencode($planType); ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>" class="page-link">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status); ?>&plan_type=<?php echo urlencode($planType); ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>" 
                   class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($status); ?>&plan_type=<?php echo urlencode($planType); ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>" class="page-link">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Add your CSS styles here - similar to users management page but with subscription-specific styles */
.subscriptions-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
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

.header-actions {
    display: flex;
    gap: 15px;
}

.btn-export, .btn-reports {
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-export {
    background: #10B981;
    color: white;
}

.btn-reports {
    background: #8B5CF6;
    color: white;
}

.btn-export:hover, .btn-reports:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-icon.active {
    background: #F0FDF4;
    color: #10B981;
}

.stat-icon.expired {
    background: #FEF2F2;
    color: #EF4444;
}

.stat-icon.pending {
    background: #FEF3C7;
    color: #F59E0B;
}

.stat-icon.revenue {
    background: #EFF6FF;
    color: #3B82F6;
}

.stat-info h3 {
    font-size: 0.9rem;
    color: #64748B;
    margin-bottom: 5px;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1E293B;
}

/* Filters */
.filters-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.filters-form {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
}

.search-box {
    flex: 2;
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

.filter-group select,
.filter-group input[type="date"] {
    padding: 12px 20px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 0.95rem;
    background: white;
    min-width: 150px;
    cursor: pointer;
}

.filter-group.date-range {
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-group.date-range span {
    color: #64748B;
}

.btn-filter {
    background: #8B5CF6;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 12px;
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
    border-radius: 12px;
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

.subscription-id {
    font-family: monospace;
    font-weight: 600;
    color: #8B5CF6;
}

.user-cell .user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 600;
    margin-bottom: 3px;
}

.user-email {
    font-size: 0.8rem;
    color: #64748B;
}

.plan-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
}

.plan-badge.monthly {
    background: #EFF6FF;
    color: #1E40AF;
}

.plan-badge.termly {
    background: #FEF3C7;
    color: #92400E;
}

.plan-badge.yearly {
    background: #F0FDF4;
    color: #166534;
}

.plan-badge i {
    font-size: 0.7rem;
}

.amount-cell {
    font-weight: 600;
    color: #059669;
}

.end-date.expired {
    color: #EF4444;
    font-weight: 600;
}

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

.status-badge.expired {
    background: #FEF2F2;
    color: #B91C1C;
}

.status-badge.pending {
    background: #FEF3C7;
    color: #92400E;
}

.status-badge.cancelled {
    background: #F1F5F9;
    color: #64748B;
}

/* Action Buttons */
.actions-cell {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
}

.action-btn.view {
    background: #EFF6FF;
    color: #2563EB;
}

.action-btn.view:hover {
    background: #2563EB;
    color: white;
}

.action-btn.cancel {
    background: #FEF2F2;
    color: #DC2626;
}

.action-btn.cancel:hover {
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

.alert-close {
    background: none;
    border: none;
    font-size: 1.3rem;
    cursor: pointer;
    color: currentColor;
    opacity: 0.7;
    margin-left: auto;
    padding: 0 5px;
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
    .filters-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group.date-range {
        flex-direction: column;
        align-items: stretch;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .header-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>