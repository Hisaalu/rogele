<?php
// File: /views/admin/subscriptions/index.php
$pageTitle = 'Subscriptions | ROGELE';
require_once __DIR__ . '/../../layouts/admin_header.php';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$status = $_GET['status'] ?? '';
$planType = $_GET['plan_type'] ?? '';
$search = $_GET['search'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$queryParams = array_filter([
    'status' => $status,
    'plan_type' => $planType,
    'search' => $search,
    'date_from' => $dateFrom,
    'date_to' => $dateTo,
]);
$queryString = http_build_query($queryParams);
$exportUrl = BASE_URL . '/admin/subscriptions/export' . ($queryString ? '?' . $queryString : '');
?>

<div class="subscriptions-container">
    <header class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-credit-card" aria-hidden="true"></i>
                Manage Subscriptions
            </h1>
            <p class="page-subtitle">View and manage all user subscriptions on the platform</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo htmlspecialchars($exportUrl); ?>" class="btn-export">
                <i class="fas fa-download" aria-hidden="true"></i>
                Export CSV
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/subscriptions/reports" class="btn-reports">
                <i class="fas fa-chart-bar" aria-hidden="true"></i>
                View Reports
            </a>
        </div>
    </header>

    <?php if (!empty($stats)): ?>
    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon active">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
            </div>
            <div class="stat-info">
                <h3>Active</h3>
                <p class="stat-number"><?php echo number_format($stats['active'] ?? 0); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon expired">
                <i class="fas fa-clock" aria-hidden="true"></i>
            </div>
            <div class="stat-info">
                <h3>Expired</h3>
                <p class="stat-number"><?php echo number_format($stats['expired'] ?? 0); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-hourglass-half" aria-hidden="true"></i>
            </div>
            <div class="stat-info">
                <h3>Pending</h3>
                <p class="stat-number"><?php echo number_format($stats['pending'] ?? 0); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon revenue">
                <i class="fas fa-coins" aria-hidden="true"></i>
            </div>
            <div class="stat-info">
                <h3>Revenue</h3>
                <p class="stat-number">UGX <?php echo number_format($stats['total_revenue'] ?? 0); ?></p>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" role="status">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <span><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
            <button class="alert-close" onclick="this.parentElement.remove()" aria-label="Close alert">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error" role="alert">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
            <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
            <button class="alert-close" onclick="this.parentElement.remove()" aria-label="Close alert">&times;</button>
        </div>
    <?php endif; ?>

    <section class="filters-card">
        <form method="GET" action="<?php echo BASE_URL; ?>/admin/subscriptions" class="filters-form">
            <div class="search-box">
                <i class="fas fa-search" aria-hidden="true"></i>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search by user name, email, or transaction ID..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                >
            </div>
            
            <div class="filter-group">
                <select name="status" aria-label="Filter by Status">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="expired" <?php echo $status === 'expired' ? 'selected' : ''; ?>>Expired</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            
            <div class="filter-group">
                <select name="plan_type" aria-label="Filter by Plan Type">
                    <option value="">All Plans</option>
                    <option value="monthly" <?php echo $planType === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                    <option value="termly" <?php echo $planType === 'termly' ? 'selected' : ''; ?>>Termly</option>
                    <option value="yearly" <?php echo $planType === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                </select>
            </div>
            
            <div class="filter-group date-range">
                <input type="date" name="date_from" aria-label="From Date" value="<?php echo htmlspecialchars($dateFrom); ?>">
                <span>to</span>
                <input type="date" name="date_to" aria-label="To Date" value="<?php echo htmlspecialchars($dateTo); ?>">
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn-filter">Apply Filters</button>
                <a href="<?php echo BASE_URL; ?>/admin/subscriptions" class="btn-reset">Reset</a>
            </div>
        </form>
    </section>

    <main class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
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
                                <i class="fas fa-credit-card" aria-hidden="true"></i>
                                <p>No subscriptions found</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subscriptions as $sub): ?>
                        <tr>
                            <td class="user-cell">
                                <div class="user-info">
                                    <div class="user-name"><?php echo htmlspecialchars(trim(($sub['first_name'] ?? '') . ' ' . ($sub['last_name'] ?? ''))); ?></div>
                                    <div class="user-email"><?php echo htmlspecialchars($sub['email'] ?? ''); ?></div>
                                </div>
                            </td>
                            <td>
                                <span class="plan-badge <?php echo htmlspecialchars($sub['plan_type'] ?? ''); ?>">
                                    <?php echo htmlspecialchars(ucfirst($sub['plan_type'] ?? '')); ?>
                                    <?php if (!empty($sub['is_upgrade'])): ?>
                                        <i class="fas fa-arrow-up" title="Upgraded" aria-hidden="true"></i>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="amount-cell">UGX <?php echo number_format($sub['amount'] ?? 0); ?></td>
                            <td><?php echo !empty($sub['start_date']) ? date('M d, Y', strtotime($sub['start_date'])) : '—'; ?></td>
                            <td>
                                <?php 
                                $isExpired = !empty($sub['end_date']) && strtotime($sub['end_date']) < time();
                                ?>
                                <span class="end-date <?php echo $isExpired ? 'expired' : ''; ?>">
                                    <?php echo !empty($sub['end_date']) ? date('M d, Y', strtotime($sub['end_date'])) : '—'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?php echo htmlspecialchars($sub['status'] ?? ''); ?>">
                                    <?php echo htmlspecialchars(ucfirst($sub['status'] ?? '')); ?>
                                </span>
                            </td>
                            <td class="actions-cell">
                                <a href="<?php echo BASE_URL; ?>/admin/subscriptions/view/<?php echo urlencode($sub['id']); ?>" class="action-btn view" title="View Details">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </a>
                                
                                <?php if (($sub['status'] ?? '') === 'active'): ?>
                                    <a href="<?php echo BASE_URL; ?>/admin/subscriptions/cancel/<?php echo urlencode($sub['id']); ?>" 
                                       class="action-btn cancel" 
                                       title="Cancel Subscription"
                                       onclick="return confirm('Are you sure you want to cancel this subscription?')">
                                        <i class="fas fa-ban" aria-hidden="true"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($subscriptions) && isset($totalPages) && $totalPages > 1): ?>
        <nav class="pagination" aria-label="Subscription Pagination">
            <?php if ($page > 1): ?>
                <?php $prevParams = array_merge($queryParams, ['page' => $page - 1]); ?>
                <a href="<?php echo BASE_URL; ?>/admin/subscriptions?<?php echo http_build_query($prevParams); ?>" class="page-link" aria-label="Previous Page">
                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php $pageParams = array_merge($queryParams, ['page' => $i]); ?>
                <a href="<?php echo BASE_URL; ?>/admin/subscriptions?<?php echo http_build_query($pageParams); ?>" 
                   class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <?php $nextParams = array_merge($queryParams, ['page' => $page + 1]); ?>
                <a href="<?php echo BASE_URL; ?>/admin/subscriptions?<?php echo http_build_query($nextParams); ?>" class="page-link" aria-label="Next Page">
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
    --radius-lg: 16px;
    --radius-md: 10px;
    --shadow-md: 0 10px 30px rgba(0, 0, 0, 0.05);
    --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.subscriptions-container,
.subscriptions-container * {
    box-sizing: border-box;
}

.subscriptions-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: clamp(16px, 3vw, 32px);
    color: var(--text-dark);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: clamp(20px, 3vw, 30px);
    flex-wrap: wrap;
    gap: 16px;
}

.page-title {
    font-size: clamp(1.6rem, 3.5vw, 2.2rem);
    font-weight: 700;
    color: var(--primary-purple);
    margin: 0 0 6px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-subtitle {
    color: var(--text-muted);
    font-size: 0.95rem;
    margin: 0;
}

.header-actions {
    display: flex;
    gap: 12px;
}

.btn-export, .btn-reports {
    padding: 10px 18px;
    border-radius: var(--radius-md);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: var(--transition);
}

.btn-export {
    background: var(--accent-orange);
    color: white;
}

.btn-reports {
    background: var(--primary-purple);
    color: white;
}

.btn-export:hover, .btn-reports:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}

.stat-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-3px);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}

.stat-icon.active { background: #F0FDF4; color: #10B981; }
.stat-icon.expired { background: #FEF2F2; color: #EF4444; }
.stat-icon.pending { background: #FEF3C7; color: #F59E0B; }
.stat-icon.revenue { background: #EFF6FF; color: #3B82F6; }

.stat-info h3 {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin: 0 0 4px 0;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-number {
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--text-dark);
    margin: 0;
}

.filters-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    padding: 16px 20px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
}

.filters-form {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}

.search-box {
    flex: 2;
    min-width: 260px;
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

.search-box input:focus,
.filter-group select:focus,
.filter-group input:focus {
    outline: none;
    border-color: var(--primary-purple);
    box-shadow: 0 0 0 3px rgba(127, 38, 119, 0.15);
}

.filter-group select,
.filter-group input[type="date"] {
    padding: 10px 14px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: 0.9rem;
    background: var(--bg-surface);
    color: var(--text-dark);
    cursor: pointer;
}

.filter-group.date-range {
    display: flex;
    align-items: center;
    gap: 6px;
}

.filter-group.date-range span {
    color: var(--text-muted);
    font-size: 0.85rem;
}

.filter-actions {
    display: flex;
    gap: 8px;
}

.btn-filter {
    background: var(--primary-purple);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: var(--radius-md);
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: var(--transition);
}

.btn-filter:hover {
    background: var(--accent-orange);
}

.btn-reset {
    color: var(--text-muted);
    background: #F1F5F9;
    text-decoration: none;
    padding: 10px 16px;
    border-radius: var(--radius-md);
    font-weight: 600;
    font-size: 0.9rem;
    transition: var(--transition);
}

.btn-reset:hover {
    background: #E2E8F0;
    color: var(--text-dark);
}

.table-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.data-table th {
    background: #F8FAFC;
    color: var(--text-dark);
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 18px;
    border-bottom: 2px solid var(--border-color);
}

.data-table td {
    padding: 14px 18px;
    border-bottom: 1px solid #F1F5F9;
    font-size: 0.9rem;
}

.data-table tr:hover td {
    background: #F8FAFC;
}

.subscription-id {
    font-family: monospace;
    font-weight: 600;
    color: var(--primary-purple);
}

.user-cell .user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 600;
}

.user-email {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.plan-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 600;
}

.plan-badge.monthly { background: #EFF6FF; color: #1E40AF; }
.plan-badge.termly { background: #FEF3C7; color: #92400E; }
.plan-badge.yearly { background: #F0FDF4; color: #166534; }

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
    padding: 4px 10px;
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 600;
}

.status-badge.active { background: #F0FDF4; color: #166534; }
.status-badge.expired { background: #FEF2F2; color: #B91C1C; }
.status-badge.pending { background: #FEF3C7; color: #92400E; }
.status-badge.cancelled { background: #F1F5F9; color: var(--text-muted); }

.actions-cell {
    display: flex;
    gap: 6px;
}

.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: var(--transition);
}

.action-btn.view { background: #EFF6FF; color: var(--primary-purple); }
.action-btn.view:hover { background: var(--accent-orange); color: white; }

.action-btn.cancel { background: #FEF2F2; color: #DC2626; }
.action-btn.cancel:hover { background: #DC2626; color: white; }

.empty-message {
    text-align: center;
    padding: 48px !important;
    color: var(--text-muted);
}

.empty-message i {
    font-size: 2.5rem;
    margin-bottom: 12px;
    opacity: 0.5;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 6px;
    padding: 16px;
    border-top: 1px solid var(--border-color);
}

.page-link {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--text-dark);
    font-weight: 500;
    font-size: 0.85rem;
    transition: var(--transition);
}

.page-link:hover {
    background: #F1F5F9;
}

.page-link.active {
    background: var(--primary-purple);
    color: white;
}

.alert {
    padding: 14px 18px;
    border-radius: var(--radius-md);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
    font-size: 0.9rem;
}

.alert-success { background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; }
.alert-error { background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; }

.alert-close {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    color: currentColor;
    opacity: 0.7;
    margin-left: auto;
}

@media (max-width: 768px) {
    .filters-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group.date-range {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-actions {
        width: 100%;
    }
    
    .btn-filter, .btn-reset {
        flex: 1;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .page-header .header-actions {
        width: 100%;
        flex-direction: column;
    }
}
</style>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>