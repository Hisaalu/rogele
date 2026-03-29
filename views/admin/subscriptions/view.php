<?php
// File: /views/admin/subscriptions/view.php
$pageTitle = 'Subscription Details - Admin - Rays of Grace';
require_once __DIR__ . '/../../layouts/header.php';

// Get data from controller
$subscription = $subscription ?? [];
$userHistory = $userHistory ?? [];
$paymentHistory = $paymentHistory ?? [];
?>

<div class="subscription-view-container">
    <!-- Header with actions -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-credit-card"></i>
                Subscription Details
            </h1>
            <p class="page-subtitle">View and manage subscription #<?php echo $subscription['id'] ?? ''; ?></p>
        </div>
        <div class="header-actions">
            <a href="<?php echo BASE_URL; ?>/admin/subscriptions" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Back to Subscriptions
            </a>
            <?php if (($subscription['status'] ?? '') === 'active'): ?>
                <a href="<?php echo BASE_URL; ?>/admin/subscriptions/cancel/<?php echo $subscription['id'] ?? 0; ?>" 
                   class="btn-cancel"
                   onclick="return confirm('Are you sure you want to cancel this subscription?')">
                    <i class="fas fa-ban"></i>
                    Cancel Subscription
                </a>
            <?php endif; ?>
        </div>
    </div>

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

    <!-- Main Content Grid -->
    <div class="details-grid">
        <!-- Left Column - Subscription Info -->
        <div class="info-card subscription-info">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Subscription Information</h2>
                <span class="status-badge <?php echo $subscription['status'] ?? ''; ?>">
                    <?php echo ucfirst($subscription['status'] ?? 'unknown'); ?>
                </span>
            </div>
            
            <div class="info-content">
                <div class="info-row">
                    <span class="info-label">Subscription ID:</span>
                    <span class="info-value">#<?php echo $subscription['id'] ?? 'N/A'; ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Plan Type:</span>
                    <span class="info-value plan-badge <?php echo $subscription['plan_type'] ?? ''; ?>">
                        <?php echo ucfirst($subscription['plan_type'] ?? 'N/A'); ?>
                        <?php if (!empty($subscription['is_upgrade'])): ?>
                            <i class="fas fa-arrow-up" title="Upgraded"></i>
                        <?php endif; ?>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Amount:</span>
                    <span class="info-value amount">UGX <?php echo number_format($subscription['amount'] ?? 0); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Start Date:</span>
                    <span class="info-value"><?php echo date('F j, Y', strtotime($subscription['start_date'] ?? 'now')); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">End Date:</span>
                    <span class="info-value <?php echo strtotime($subscription['end_date'] ?? 'now') < time() ? 'expired' : ''; ?>">
                        <?php echo date('F j, Y', strtotime($subscription['end_date'] ?? 'now')); ?>
                        <?php if (strtotime($subscription['end_date'] ?? 'now') < time()): ?>
                            <span class="expired-label">(Expired)</span>
                        <?php endif; ?>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Auto Renew:</span>
                    <span class="info-value">
                        <?php if (!empty($subscription['auto_renew'])): ?>
                            <span class="badge-success"><i class="fas fa-check"></i> Enabled</span>
                        <?php else: ?>
                            <span class="badge-warning"><i class="fas fa-times"></i> Disabled</span>
                        <?php endif; ?>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Payment Method:</span>
                    <span class="info-value">
                        <i class="fas fa-<?php echo $subscription['payment_method'] === 'mobile_money' ? 'mobile-alt' : 'credit-card'; ?>"></i>
                        <?php echo ucfirst(str_replace('_', ' ', $subscription['payment_method'] ?? 'unknown')); ?>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Transaction ID:</span>
                    <span class="info-value transaction-id"><?php echo $subscription['transaction_id'] ?? 'N/A'; ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Created At:</span>
                    <span class="info-value"><?php echo date('F j, Y h:i A', strtotime($subscription['created_at'] ?? 'now')); ?></span>
                </div>
            </div>
        </div>

        <!-- Right Column - User Info -->
        <div class="info-card user-info-card">
            <div class="card-header">
                <h2><i class="fas fa-user"></i> User Information</h2>
            </div>
            
            <div class="info-content">
                <div class="user-avatar">
                    <div class="avatar-placeholder">
                        <?php 
                        $firstInitial = strtoupper(substr($subscription['first_name'] ?? 'U', 0, 1));
                        $lastInitial = strtoupper(substr($subscription['last_name'] ?? 'S', 0, 1));
                        echo $firstInitial . $lastInitial;
                        ?>
                    </div>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">
                        <?php echo htmlspecialchars(($subscription['first_name'] ?? '') . ' ' . ($subscription['last_name'] ?? '')); ?>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">
                        <a href="mailto:<?php echo htmlspecialchars($subscription['email'] ?? ''); ?>">
                            <?php echo htmlspecialchars($subscription['email'] ?? 'N/A'); ?>
                        </a>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?php echo htmlspecialchars($subscription['phone'] ?? 'N/A'); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">User Role:</span>
                    <span class="info-value">
                        <span class="role-badge role-<?php echo $subscription['user_role'] ?? 'external'; ?>">
                            <?php echo ucfirst($subscription['user_role'] ?? 'external'); ?>
                        </span>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">User ID:</span>
                    <span class="info-value">#<?php echo $subscription['user_id'] ?? 'N/A'; ?></span>
                </div>
                
                <div class="user-actions">
                    <a href="<?php echo BASE_URL; ?>/admin/users/edit/<?php echo $subscription['user_id'] ?? 0; ?>" class="btn-view-user">
                        <i class="fas fa-user-edit"></i> View User Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Upgrade Information (if applicable) -->
    <?php if (!empty($subscription['is_upgrade'])): ?>
    <div class="upgrade-info-card">
        <div class="card-header">
            <h2><i class="fas fa-arrow-up"></i> Upgrade Information</h2>
        </div>
        
        <div class="upgrade-details">
            <div class="upgrade-item">
                <span class="upgrade-label">Upgraded From:</span>
                <span class="upgrade-value"><?php echo ucfirst($subscription['upgraded_from'] ?? 'N/A'); ?></span>
            </div>
            
            <div class="upgrade-item">
                <span class="upgrade-label">Upgraded At:</span>
                <span class="upgrade-value"><?php echo date('F j, Y h:i A', strtotime($subscription['upgraded_at'] ?? 'now')); ?></span>
            </div>
            
            <div class="upgrade-item">
                <span class="upgrade-label">Original Subscription ID:</span>
                <span class="upgrade-value">
                    <a href="<?php echo BASE_URL; ?>/admin/subscriptions/view/<?php echo $subscription['original_subscription_id'] ?? 0; ?>">
                        #<?php echo $subscription['original_subscription_id'] ?? 'N/A'; ?>
                    </a>
                </span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Payment History -->
    <?php if (!empty($paymentHistory) && is_array($paymentHistory)): ?>
        <div class="payment-history-card">
            <div class="card-header">
                <h2><i class="fas fa-history"></i> Payment History</h2>
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
                        <?php if (is_array($payment)): // Ensure each payment is an array ?>
                        <tr>
                            <td><?php echo isset($payment['created_at']) ? date('M d, Y h:i A', strtotime($payment['created_at'])) : 'N/A'; ?></td>
                            <td class="amount-cell">UGX <?php echo isset($payment['amount']) ? number_format($payment['amount']) : '0'; ?></td>
                            <td>
                                <?php 
                                $method = $payment['payment_method'] ?? 'unknown';
                                $icon = $method === 'mobile_money' ? 'mobile-alt' : 'credit-card';
                                ?>
                                <i class="fas fa-<?php echo $icon; ?>"></i>
                                <?php echo ucfirst(str_replace('_', ' ', $method)); ?>
                            </td>
                            <td class="transaction-id"><?php echo $payment['transaction_id'] ?? 'N/A'; ?></td>
                            <td>
                                <span class="status-badge <?php echo $payment['status'] ?? 'unknown'; ?>">
                                    <?php echo isset($payment['status']) ? ucfirst($payment['status']) : 'Unknown'; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
    <!-- Optional: Show a message when no payment history -->
    <div class="info-message">
        <i class="fas fa-info-circle"></i>
        <p>No payment history found for this subscription.</p>
    </div>
    <?php endif; ?>

    <!-- User Subscription History -->
    <?php if (!empty($userHistory)): ?>
    <div class="user-history-card">
        <div class="card-header">
            <h2><i class="fas fa-list"></i> User's Subscription History</h2>
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
                        if ($history['id'] == ($subscription['id'] ?? 0)) continue; // Skip current
                    ?>
                    <tr>
                        <td>#<?php echo $history['id']; ?></td>
                        <td>
                            <span class="plan-badge <?php echo $history['plan_type']; ?>">
                                <?php echo ucfirst($history['plan_type']); ?>
                            </span>
                        </td>
                        <td class="amount-cell">UGX <?php echo number_format($history['amount']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($history['start_date'])); ?></td>
                        <td><?php echo date('M d, Y', strtotime($history['end_date'])); ?></td>
                        <td>
                            <span class="status-badge <?php echo $history['status']; ?>">
                                <?php echo ucfirst($history['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>/admin/subscriptions/view/<?php echo $history['id']; ?>" class="btn-view-small">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.subscription-view-container {
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
}

.page-subtitle {
    color: #64748B;
    font-size: 1rem;
}

.header-actions {
    display: flex;
    gap: 15px;
}

.btn-back, .btn-cancel {
    padding: 12px 25px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-back {
    background: #F1F5F9;
    color: #1E293B;
}

.btn-cancel {
    background: #FEF2F2;
    color: #B91C1C;
}

.btn-back:hover, .btn-cancel:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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

/* Details Grid */
.details-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
    margin-bottom: 25px;
}

.info-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #F1F5F9;
}

.card-header h2 {
    color: #1E293B;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-header h2 i {
    color: #8B5CF6;
}

.info-content {
    display: flex;
    flex-direction: column;
    gap: 15px;
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
    color: #64748B;
    font-weight: 500;
    font-size: 0.95rem;
}

.info-value {
    color: #1E293B;
    font-weight: 600;
}

.info-value.amount {
    color: #059669;
    font-size: 1.1rem;
}

.info-value.expired {
    color: #EF4444;
}

.expired-label {
    font-size: 0.8rem;
    margin-left: 5px;
}

/* Status Badge */
.status-badge {
    display: inline-block;
    padding: 5px 15px;
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

/* Plan Badge */
.plan-badge {
    display: inline-block;
    padding: 5px 15px;
    border-radius: 50px;
    font-size: 0.85rem;
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

/* User Info */
.user-avatar {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}

.avatar-placeholder {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    font-weight: 700;
}

.role-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
}

.role-badge.role-admin {
    background: #FEF2F2;
    color: #B91C1C;
}

.role-badge.role-teacher {
    background: #EFF6FF;
    color: #1E40AF;
}

.role-badge.role-learner {
    background: #F0FDF4;
    color: #166534;
}

.role-badge.role-external {
    background: #FEF3C7;
    color: #92400E;
}

.user-actions {
    margin-top: 20px;
    text-align: center;
}

.btn-view-user {
    display: inline-block;
    padding: 10px 20px;
    background: #EFF6FF;
    color: #2563EB;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-view-user:hover {
    background: #2563EB;
    color: white;
}

/* Upgrade Info */
.upgrade-info-card {
    background: linear-gradient(135deg, #FEF3C7, #FFFAF0);
    border: 2px solid #F59E0B;
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 25px;
}

.upgrade-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.upgrade-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.upgrade-label {
    color: #92400E;
    font-size: 0.9rem;
    font-weight: 500;
}

.upgrade-value {
    color: #1E293B;
    font-weight: 700;
    font-size: 1.1rem;
}

.upgrade-value a {
    color: #8B5CF6;
    text-decoration: none;
}

.upgrade-value a:hover {
    text-decoration: underline;
}

/* Tables */
.payment-history-card,
.user-history-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.table-responsive {
    overflow-x: auto;
}

.payment-table,
.history-table {
    width: 100%;
    border-collapse: collapse;
}

.payment-table th,
.history-table th {
    background: #F8FAFC;
    padding: 15px 20px;
    text-align: left;
    font-weight: 600;
    color: #1E293B;
    border-bottom: 2px solid #E2E8F0;
}

.payment-table td,
.history-table td {
    padding: 15px 20px;
    border-bottom: 1px solid #F1F5F9;
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
    color: #64748B;
}

.btn-view-small {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    background: #EFF6FF;
    color: #2563EB;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-view-small:hover {
    background: #2563EB;
    color: white;
}

/* Badges */
.badge-success {
    background: #F0FDF4;
    color: #166534;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 0.8rem;
}

.badge-warning {
    background: #FEF3C7;
    color: #92400E;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 0.8rem;
}

.payment-count {
    background: #8B5CF6;
    color: white;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.info-message {
    background: #F8FAFC;
    border-radius: 12px;
    padding: 30px;
    text-align: center;
    color: #64748B;
    margin: 20px 0;
}

.info-message i {
    font-size: 2rem;
    margin-bottom: 10px;
    color: #8B5CF6;
}

.info-message p {
    font-size: 1rem;
}

/* Animations */
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
    
    .header-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
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

/* Dark Mode */
/* @media (prefers-color-scheme: dark) {
    .info-card,
    .payment-history-card,
    .user-history-card {
        background: #1E293B;
    }
    
    .card-header h2,
    .info-value,
    .upgrade-value {
        color: #F1F5F9;
    }
    
    .payment-table th,
    .history-table th {
        background: #334155;
        color: #F1F5F9;
    }
    
    .payment-table td,
    .history-table td {
        color: #CBD5E0;
        border-bottom-color: #334155;
    }
    
    .payment-table tr:hover td,
    .history-table tr:hover td {
        background: #334155;
    }
} */
</style>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>