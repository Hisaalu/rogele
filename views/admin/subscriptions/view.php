<?php
// File: /views/admin/subscriptions/view.php
$pageTitle = 'Subscription Details | ROGELE';
require_once __DIR__ . '/../../layouts/admin_header.php';

$subscription = $subscription ?? [];
$userHistory = $userHistory ?? [];
$paymentHistory = $paymentHistory ?? [];
?>

<div class="subscription-view-container">
    <header class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-credit-card" aria-hidden="true"></i>
                Subscription Details
            </h1>
            <p class="page-subtitle">View and manage subscription #<?php echo htmlspecialchars($subscription['id'] ?? 'N/A'); ?></p>
        </div>
    </header>

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

    <div class="details-grid">
        <section class="info-card subscription-info">
            <div class="card-header">
                <h2><i class="fas fa-info-circle" aria-hidden="true"></i> Subscription Information</h2>
                
                <form action="<?php echo BASE_URL; ?>/admin/subscriptions/update-status" method="POST" class="status-update-form">
                    <input type="hidden" name="subscription_id" value="<?php echo htmlspecialchars($subscription['id'] ?? 0); ?>">
                    <select name="status" onchange="this.form.submit()" class="status-select <?php echo htmlspecialchars($subscription['status'] ?? 'pending'); ?>" aria-label="Update Subscription Status">
                        <?php 
                        $statuses = ['active', 'expired', 'pending', 'cancelled'];
                        foreach ($statuses as $st): ?>
                            <option value="<?php echo $st; ?>" <?php echo (($subscription['status'] ?? 'pending') === $st) ? 'selected' : ''; ?>>
                                <?php echo ucfirst($st); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            
            <div class="info-content">
                <div class="info-row">
                    <span class="info-label">Subscription ID:</span>
                    <span class="info-value">#<?php echo htmlspecialchars($subscription['id'] ?? 'N/A'); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Plan Type:</span>
                    <span class="info-value plan-badge <?php echo htmlspecialchars($subscription['plan_type'] ?? ''); ?>">
                        <?php echo htmlspecialchars(ucfirst($subscription['plan_type'] ?? 'N/A')); ?>
                        <?php if (!empty($subscription['is_upgrade'])): ?>
                            <i class="fas fa-arrow-up" title="Upgraded" aria-hidden="true"></i>
                        <?php endif; ?>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Amount:</span>
                    <span class="info-value amount">UGX <?php echo number_format($subscription['amount'] ?? 0); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Start Date:</span>
                    <span class="info-value">
                        <?php echo !empty($subscription['start_date']) ? date('F j, Y', strtotime($subscription['start_date'])) : 'N/A'; ?>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">End Date:</span>
                    <?php 
                    $isExpired = !empty($subscription['end_date']) && strtotime($subscription['end_date']) < time();
                    ?>
                    <span class="info-value <?php echo $isExpired ? 'expired' : ''; ?>">
                        <?php echo !empty($subscription['end_date']) ? date('F j, Y', strtotime($subscription['end_date'])) : 'N/A'; ?>
                        <?php if ($isExpired): ?>
                            <span class="expired-label">(Expired)</span>
                        <?php endif; ?>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Auto Renew:</span>
                    <span class="info-value">
                        <?php if (!empty($subscription['auto_renew'])): ?>
                            <span class="badge-success"><i class="fas fa-check" aria-hidden="true"></i> Enabled</span>
                        <?php else: ?>
                            <span class="badge-warning"><i class="fas fa-times" aria-hidden="true"></i> Disabled</span>
                        <?php endif; ?>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Payment Method:</span>
                    <span class="info-value">
                        <i class="fas fa-<?php echo ($subscription['payment_method'] ?? 'mobile_money') === 'mobile_money' ? 'mobile-alt' : 'credit-card'; ?>" aria-hidden="true"></i>
                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $subscription['payment_method'] ?? 'unknown'))); ?>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Transaction ID:</span>
                    <span class="info-value transaction-id"><?php echo htmlspecialchars($subscription['transaction_id'] ?? 'N/A'); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Created At:</span>
                    <span class="info-value">
                        <?php echo !empty($subscription['created_at']) ? date('F j, Y h:i A', strtotime($subscription['created_at'])) : 'N/A'; ?>
                    </span>
                </div>
            </div>
        </section>

        <section class="info-card user-info-card">
            <div class="card-header">
                <h2><i class="fas fa-user" aria-hidden="true"></i> User Information</h2>
            </div>
            
            <div class="info-content">
                <div class="user-avatar">
                    <div class="avatar-placeholder">
                        <?php 
                        $firstInitial = strtoupper(substr($subscription['first_name'] ?? 'U', 0, 1));
                        $lastInitial = strtoupper(substr($subscription['last_name'] ?? 'S', 0, 1));
                        echo htmlspecialchars($firstInitial . $lastInitial);
                        ?>
                    </div>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">
                        <?php echo htmlspecialchars(trim(($subscription['first_name'] ?? '') . ' ' . ($subscription['last_name'] ?? ''))); ?>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">
                        <?php if (!empty($subscription['email'])): ?>
                            <a href="mailto:<?php echo htmlspecialchars($subscription['email']); ?>">
                                <?php echo htmlspecialchars($subscription['email']); ?>
                            </a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?php echo htmlspecialchars($subscription['phone'] ?? 'N/A'); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">User Role:</span>
                    <span class="info-value">
                        <span class="role-badge role-<?php echo htmlspecialchars($subscription['user_role'] ?? 'external'); ?>">
                            <?php echo htmlspecialchars(ucfirst($subscription['user_role'] ?? 'external')); ?>
                        </span>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">User ID:</span>
                    <span class="info-value">#<?php echo htmlspecialchars($subscription['user_id'] ?? 'N/A'); ?></span>
                </div>
                
                <?php if (!empty($subscription['user_id'])): ?>
                <div class="user-actions">
                    <a href="<?php echo BASE_URL; ?>/admin/users/edit/<?php echo urlencode($subscription['user_id']); ?>" class="btn-view-user">
                        <i class="fas fa-user-edit" aria-hidden="true"></i> View User Profile
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <?php if (!empty($subscription['is_upgrade'])): ?>
    <section class="upgrade-info-card">
        <div class="card-header">
            <h2><i class="fas fa-arrow-up" aria-hidden="true"></i> Upgrade Information</h2>
        </div>
        
        <div class="upgrade-details">
            <div class="upgrade-item">
                <span class="upgrade-label">Upgraded From:</span>
                <span class="upgrade-value"><?php echo htmlspecialchars(ucfirst($subscription['upgraded_from'] ?? 'N/A')); ?></span>
            </div>
            
            <div class="upgrade-item">
                <span class="upgrade-label">Upgraded At:</span>
                <span class="info-value">
                    <?php echo !empty($subscription['created_at']) ? date('F j, Y h:i A', strtotime($subscription['created_at'])) : 'N/A'; ?>
                </span>
            </div>
            
            <div class="upgrade-item">
                <span class="upgrade-label">Original Subscription ID:</span>
                <span class="upgrade-value">
                    <?php if (!empty($subscription['original_subscription_id'])): ?>
                        <a href="<?php echo BASE_URL; ?>/admin/subscriptions/view/<?php echo urlencode($subscription['original_subscription_id']); ?>">
                            #<?php echo htmlspecialchars($subscription['original_subscription_id']); ?>
                        </a>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </span>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($paymentHistory) && is_array($paymentHistory)): ?>
        <section class="payment-history-card">
            <div class="card-header">
                <h2><i class="fas fa-history" aria-hidden="true"></i> Payment History</h2>
                <span class="payment-count"><?php echo count($paymentHistory); ?> payment(s)</span>
            </div>
            
            <div class="table-responsive">
                <table class="payment-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Transaction ID</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paymentHistory as $payment): ?>
                        <?php if (is_array($payment)): ?>
                        <tr>
                            <td><?php echo !empty($payment['created_at']) ? date('M d, Y h:i A', strtotime($payment['created_at'])) : 'N/A'; ?></td>
                            <td class="amount-cell">UGX <?php echo number_format($payment['amount'] ?? 0); ?></td>
                            <td>
                                <?php 
                                $method = $payment['payment_method'] ?? 'mobile_money';
                                $icon = $method === 'mobile_money' ? 'mobile-alt' : 'credit-card';
                                ?>
                                <i class="fas fa-<?php echo $icon; ?>" aria-hidden="true"></i>
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $method))); ?>
                            </td>
                            <td class="transaction-id"><?php echo htmlspecialchars($payment['transaction_id'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="status-badge <?php echo htmlspecialchars($payment['status'] ?? 'unknown'); ?>">
                                    <?php echo htmlspecialchars(ucfirst($payment['status'] ?? 'Unknown')); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php else: ?>
    <div class="info-message">
        <i class="fas fa-info-circle" aria-hidden="true"></i>
        <p>No payment history found for this subscription.</p>
    </div>
    <?php endif; ?>

    <?php if (!empty($userHistory) && is_array($userHistory)): ?>
    <section class="user-history-card">
        <div class="card-header">
            <h2><i class="fas fa-list" aria-hidden="true"></i> User's Subscription History</h2>
        </div>
        
        <div class="table-responsive">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Plan</th>
                        <th>Amount</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($userHistory as $history): 
                        if (!is_array($history) || ($history['id'] ?? 0) == ($subscription['id'] ?? 0)) continue;
                    ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($history['id'] ?? ''); ?></td>
                        <td>
                            <span class="plan-badge <?php echo htmlspecialchars($history['plan_type'] ?? ''); ?>">
                                <?php echo htmlspecialchars(ucfirst($history['plan_type'] ?? '')); ?>
                            </span>
                        </td>
                        <td class="amount-cell">UGX <?php echo number_format($history['amount'] ?? 0); ?></td>
                        <td><?php echo !empty($history['start_date']) ? date('M d, Y', strtotime($history['start_date'])) : 'N/A'; ?></td>
                        <td><?php echo !empty($history['end_date']) ? date('M d, Y', strtotime($history['end_date'])) : 'N/A'; ?></td>
                        <td>
                            <span class="status-badge <?php echo htmlspecialchars($history['status'] ?? ''); ?>">
                                <?php echo htmlspecialchars(ucfirst($history['status'] ?? '')); ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>/admin/subscriptions/view/<?php echo urlencode($history['id'] ?? ''); ?>" class="btn-view-small" title="View Details">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>
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

.subscription-view-container,
.subscription-view-container * {
    box-sizing: border-box;
}

.subscription-view-container {
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

.btn-back {
    padding: 10px 18px;
    border-radius: var(--radius-md);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: var(--transition);
    background: var(--primary-purple);
    color: white;
}

.btn-back:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    background: var(--accent-orange);
}

.alert {
    padding: 14px 18px;
    border-radius: var(--radius-md);
    margin-bottom: 24px;
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

.details-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
}

.info-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    padding: 24px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 2px solid #F1F5F9;
    flex-wrap: wrap;
    gap: 12px;
}

.card-header h2 {
    color: var(--text-dark);
    font-size: 1.15rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

.card-header h2 i {
    color: var(--accent-orange);
}

.status-select {
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    border: 1px solid transparent;
    cursor: pointer;
    transition: var(--transition);
    background: var(--bg-surface);
    font-family: inherit;
}

.status-select:hover {
    filter: brightness(0.95);
}

.status-select.active { background: #F0FDF4; color: #166534; border-color: #BBF7D0; }
.status-select.expired { background: #FEF2F2; color: #B91C1C; border-color: #FECACA; }
.status-select.pending { background: #FEF3C7; color: #92400E; border-color: #FDE68A; }
.status-select.cancelled { background: #F1F5F9; color: var(--text-muted); border-color: #E2E8F0; }

.status-update-form {
    display: inline-flex;
    align-items: center;
}

.info-content {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px dashed #F1F5F9;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    color: var(--text-muted);
    font-weight: 500;
    font-size: 0.9rem;
}

.info-value {
    color: var(--text-dark);
    font-weight: 600;
    font-size: 0.9rem;
}

.info-value.amount {
    color: #059669;
}

.info-value.expired {
    color: #EF4444;
}

.expired-label {
    font-size: 0.8rem;
    margin-left: 4px;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 600;
}

.status-badge.active { background: #F0FDF4; color: #166534; }
.status-badge.expired { background: #FEF2F2; color: #B91C1C; }
.status-badge.pending { background: #FEF3C7; color: #92400E; }
.status-badge.cancelled { background: #F1F5F9; color: var(--text-muted); }

.plan-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 600;
}

.plan-badge.monthly { background: #EFF6FF; color: #1E40AF; }
.plan-badge.termly { background: #FEF3C7; color: #92400E; }
.plan-badge.yearly { background: #F0FDF4; color: #166534; }

.user-avatar {
    display: flex;
    justify-content: center;
    margin-bottom: 16px;
}

.avatar-placeholder {
    width: 72px;
    height: 72px;
    background-color: var(--accent-orange);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.6rem;
    font-weight: 700;
}

.role-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 600;
}

.role-badge.role-admin { background: #FEF2F2; color: #B91C1C; }
.role-badge.role-teacher { background: #EFF6FF; color: #1E40AF; }
.role-badge.role-learner { background: #F0FDF4; color: #166534; }
.role-badge.role-external { background: #FEF3C7; color: #92400E; }

.user-actions {
    margin-top: 16px;
    text-align: center;
}

.btn-view-user {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    background: var(--primary-purple);
    color: white;
    text-decoration: none;
    border-radius: var(--radius-md);
    font-size: 0.88rem;
    font-weight: 600;
    transition: var(--transition);
}

.btn-view-user:hover {
    background: var(--accent-orange);
    color: white;
}

.upgrade-info-card {
    background: linear-gradient(135deg, #FEF3C7, #FFFAF0);
    border: 1px solid #FDE68A;
    border-radius: var(--radius-lg);
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-md);
}

.upgrade-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-top: 12px;
}

.upgrade-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.upgrade-label {
    color: #92400E;
    font-size: 0.85rem;
    font-weight: 500;
}

.upgrade-value {
    color: var(--text-dark);
    font-weight: 700;
    font-size: 0.9rem;
}

.upgrade-value a {
    color: var(--primary-purple);
    text-decoration: none;
}

.upgrade-value a:hover {
    text-decoration: underline;
}

.payment-history-card,
.user-history-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
}

.table-responsive {
    overflow-x: auto;
}

.payment-table,
.history-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.payment-table th,
.history-table th {
    background: #F8FAFC;
    padding: 14px 18px;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-dark);
    border-bottom: 2px solid var(--border-color);
}

.payment-table td,
.history-table td {
    padding: 14px 18px;
    border-bottom: 1px solid #F1F5F9;
    font-size: 0.9rem;
}

.payment-table tr:hover td,
.history-table tr:hover td {
    background: #F8FAFC;
}

.amount-cell {
    font-weight: 700;
    color: #059669;
}

.transaction-id {
    font-family: monospace;
    color: var(--text-muted);
}

.btn-view-small {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: #EFF6FF;
    color: var(--primary-purple);
    text-decoration: none;
    border-radius: 6px;
    transition: var(--transition);
}

.btn-view-small:hover {
    background: var(--accent-orange);
    color: white;
}

.badge-success {
    background: #F0FDF4;
    color: #166534;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
}

.badge-warning {
    background: #FEF3C7;
    color: #92400E;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
}

.payment-count {
    background: var(--primary-purple);
    color: white;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
}

.info-message {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    padding: 36px;
    text-align: center;
    color: var(--text-muted);
    margin: 20px 0;
    border: 1px solid var(--border-color);
}

.info-message i {
    font-size: 2rem;
    margin-bottom: 10px;
    color: var(--accent-orange);
}

.info-message p {
    font-size: 0.95rem;
    margin: 0;
}

@media (max-width: 1024px) {
    .details-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    
    .card-header {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .payment-table th,
    .payment-table td,
    .history-table th,
    .history-table td {
        padding: 10px;
        font-size: 0.85rem;
    }
}
</style>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>