<?php
// File: /views/external/subscription.php
$pageTitle = 'Subscription - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

// Get settings from controller
$subscriptionSettings = $subscriptionSettings ?? [];
$monthlyPrice = $subscriptionSettings['monthly_price'] ?? 15000;
$termlyPrice = $subscriptionSettings['termly_price'] ?? 40000;
$yearlyPrice = $subscriptionSettings['yearly_price'] ?? 120000;
$trialDays = $subscriptionSettings['trial_days'] ?? 60;
?>

<div class="subscription-container">
    <!-- Header Section -->
    <div class="subscription-header">
        <h1 class="page-title">
            <i class="fas fa-crown"></i>
            Choose Your Plan
        </h1>
        <p class="page-subtitle">Select the perfect plan for your learning journey</p>
        <?php if ($trialDays > 0): ?>
        <div class="trial-badge">
            <i class="fas fa-gift"></i>
            Your free trial will end!
        </div>
        <?php endif; ?>
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

    <?php if ($currentSubscription): ?>
        <!-- Active Subscription Info -->
        <div class="active-subscription">
            <div class="active-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="active-content">
                <h3>You have an active <?php echo ucfirst($currentSubscription['plan_type']); ?> subscription</h3>
                <p>Valid until <?php echo date('F j, Y', strtotime($currentSubscription['end_date'])); ?></p>
            </div>
            <?php
            // Determine the next tier plan
            $currentPlan = $currentSubscription['plan_type'];
            $nextPlan = 'yearly'; // Default

            if ($currentPlan === 'monthly') {
                $nextPlan = 'termly';
            } elseif ($currentPlan === 'termly') {
                $nextPlan = 'yearly';
            }
            ?>

            <a href="/rays-of-grace/external/upgrade-confirmation?from=<?php echo $currentPlan; ?>&to=<?php echo $nextPlan; ?>" class="btn-primary">
                <i class="fas fa-rocket"></i> Upgrade to <?php echo ucfirst($nextPlan); ?>
            </a>
        </div>
    <?php endif; ?>

    <!-- Pricing Cards -->
    <div class="pricing-grid">
        <!-- Monthly Plan -->
        <div class="pricing-card">
            <div class="pricing-header">
                <h3>Monthly</h3>
                <div class="price">
                    <span class="currency">UGX</span>
                    <span class="amount"><?php echo number_format($monthlyPrice); ?></span>
                </div>
                <p class="period">per month</p>
            </div>
            
            <ul class="pricing-features">
                <li><i class="fas fa-check"></i> Full access to all lessons</li>
                <li><i class="fas fa-check"></i> Practice quizzes</li>
                <li><i class="fas fa-check"></i> Progress tracking</li>
                <li><i class="fas fa-check"></i> Email support</li>
            </ul>
            
            <?php if (!$currentSubscription): ?>
            <a href="/rays-of-grace/external/purchase?plan=monthly" class="btn-select">Select Plan</a>
            <?php endif; ?>
        </div>

        <!-- Termly Plan (Popular) -->
        <div class="pricing-card popular">
            <div class="popular-badge">Most Popular</div>
            <div class="pricing-header">
                <h3>Termly</h3>
                <div class="price">
                    <span class="currency">UGX</span>
                    <span class="amount"><?php echo number_format($termlyPrice); ?></span>
                </div>
                <p class="period">per term (3 months)</p>
            </div>
            
            <?php 
            $monthlyTotal = $monthlyPrice * 3;
            $savings = $monthlyTotal - $termlyPrice;
            $savingsPercent = $monthlyTotal > 0 ? round(($savings / $monthlyTotal) * 100) : 0;
            ?>
            <div class="savings-badge">Save <?php echo $savingsPercent; ?>%</div>
            
            <ul class="pricing-features">
                <li><i class="fas fa-check"></i> Everything in Monthly</li>
                <li><i class="fas fa-check"></i> Save <?php echo number_format($savings); ?> UGX</li>
                <li><i class="fas fa-check"></i> Priority support</li>
                <li><i class="fas fa-check"></i> Downloadable materials</li>
            </ul>
            
            <?php if (!$currentSubscription): ?>
            <a href="/rays-of-grace/external/purchase?plan=termly" class="btn-select popular-btn">Select Plan</a>
            <?php endif; ?>
        </div>

        <!-- Yearly Plan -->
        <div class="pricing-card">
            <div class="pricing-header">
                <h3>Yearly</h3>
                <div class="price">
                    <span class="currency">UGX</span>
                    <span class="amount"><?php echo number_format($yearlyPrice); ?></span>
                </div>
                <p class="period">per year</p>
            </div>
            
            <?php 
            $monthlyYearTotal = $monthlyPrice * 12;
            $yearlySavings = $monthlyYearTotal - $yearlyPrice;
            $yearlySavingsPercent = $monthlyYearTotal > 0 ? round(($yearlySavings / $monthlyYearTotal) * 100) : 0;
            ?>
            <div class="savings-badge best-value">Save <?php echo $yearlySavingsPercent; ?>%</div>
            
            <ul class="pricing-features">
                <li><i class="fas fa-check"></i> Everything in Termly</li>
                <li><i class="fas fa-check"></i> 2 months free</li>
                <li><i class="fas fa-check"></i> Certificate of completion</li>
                <li><i class="fas fa-check"></i> 1-on-1 tutoring sessions</li>
            </ul>
            
            <?php if (!$currentSubscription): ?>
            <a href="/rays-of-grace/external/purchase?plan=yearly" class="btn-select">Select Plan</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment History -->
    <?php if (!empty($paymentHistory)): ?>
        <div class="history-section">
            <h2 class="section-title">
                <i class="fas fa-history"></i>
                Payment & Subscription History
            </h2>
            <div class="table-responsive">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paymentHistory as $payment): ?>
                        <tr>
                            <td>
                                <div class="date-cell">
                                    <span class="date-day"><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></span>
                                    <span class="date-time"><?php echo date('h:i A', strtotime($payment['created_at'])); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="type-badge <?php echo $payment['history_type'] ?? 'subscription'; ?>">
                                    <?php if (($payment['history_type'] ?? '') === 'payment'): ?>
                                        <i class="fas fa-credit-card"></i> Payment
                                    <?php elseif (isset($payment['from_plan']) && isset($payment['to_plan'])): ?>
                                        <i class="fas fa-arrow-up"></i> Upgrade
                                    <?php else: ?>
                                        <i class="fas fa-crown"></i> Subscription
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <span class="plan-name">
                                    <?php 
                                    if (isset($payment['to_plan']) && $payment['to_plan']) {
                                        echo ucfirst($payment['to_plan']);
                                    } else {
                                        echo ucfirst($payment['plan_type'] ?? 'N/A');
                                    }
                                    ?>
                                </span>
                                <?php if (isset($payment['from_plan']) && $payment['from_plan']): ?>
                                    <small class="from-plan">(from <?php echo ucfirst($payment['from_plan']); ?>)</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="amount-cell">
                                    UGX <?php echo number_format($payment['amount']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($payment['payment_method'])): ?>
                                <span class="payment-method">
                                    <i class="fas fa-<?php echo $payment['payment_method'] === 'mobile_money' ? 'mobile-alt' : 'credit-card'; ?>"></i>
                                    <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?>
                                </span>
                                <?php else: ?>
                                <span class="payment-method">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $payment['status']; ?>">
                                    <?php echo ucfirst($payment['status']); ?>
                                </span>
                                <?php if (!empty($payment['transaction_id'])): ?>
                                <small class="transaction-id">ID: <?php echo substr($payment['transaction_id'], -8); ?></small>
                                <?php endif; ?>
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
.subscription-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.subscription-header {
    text-align: center;
    margin-bottom: 50px;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 15px;
}

.page-subtitle {
    color: #64748B;
    font-size: 1.1rem;
    margin-bottom: 20px;
}

.trial-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
    padding: 12px 30px;
    border-radius: 50px;
    font-weight: 600;
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
}

.trial-badge i {
    font-size: 1.2rem;
}

/* Active Subscription */
.active-subscription {
    background: linear-gradient(135deg, #F0FDF4, #FFFFFF);
    border: 2px solid #10B981;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 40px;
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.active-icon {
    width: 50px;
    height: 50px;
    background: #10B981;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.active-content {
    flex: 1;
}

.active-content h3 {
    color: #065F46;
    font-size: 1.2rem;
    margin-bottom: 5px;
}

.active-content p {
    color: #047857;
}

.btn-primary {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
    padding: 12px 30px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
}

/* Pricing Grid */
.pricing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 50px;
}

.pricing-card {
    background: white;
    border-radius: 20px;
    padding: 40px 30px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
    border: 2px solid transparent;
}

.pricing-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 60px rgba(139, 92, 246, 0.15);
}

.pricing-card.popular {
    border-color: #8B5CF6;
    transform: scale(1.05);
}

.popular-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
    padding: 5px 20px;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 600;
    white-space: nowrap;
}

.pricing-header {
    text-align: center;
    margin-bottom: 30px;
}

.pricing-header h3 {
    font-size: 1.8rem;
    color: #1E293B;
    margin-bottom: 15px;
}

.price {
    margin-bottom: 5px;
}

.currency {
    font-size: 1rem;
    color: #64748B;
    vertical-align: top;
}

.amount {
    font-size: 3rem;
    font-weight: 800;
    color: #1E293B;
    line-height: 1;
}

.period {
    color: #64748B;
    font-size: 0.9rem;
}

.savings-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: #10B981;
    color: white;
    padding: 5px 15px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
}

.savings-badge.best-value {
    background: #F97316;
}

.pricing-features {
    list-style: none;
    margin: 30px 0;
    padding: 0;
}

.pricing-features li {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
    color: #1E293B;
}

.pricing-features li i {
    color: #10B981;
    font-size: 1rem;
}

.btn-select {
    display: block;
    width: 100%;
    padding: 15px;
    background: white;
    color: #8B5CF6;
    border: 2px solid #8B5CF6;
    border-radius: 50px;
    text-align: center;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-select:hover {
    background: #8B5CF6;
    color: white;
}

.btn-select.popular-btn {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
    border: none;
}

.btn-select.popular-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
}

/* Payment History */
.history-section {
    margin-top: 60px;
}

.section-title {
    font-size: 1.8rem;
    color: #1E293B;
    margin-bottom: 30px;
    text-align: center;
}

.table-responsive {
    overflow-x: auto;
    border-radius: 12px;
    border: 1px solid #E2E8F0;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.history-table th {
    background: #F8FAFC;
    padding: 16px 20px;
    text-align: left;
    font-weight: 600;
    color: #1E293B;
    border-bottom: 2px solid #E2E8F0;
}

.history-table td {
    padding: 14px 20px;
    border-bottom: 1px solid #E2E8F0;
    color: #1E293B;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-badge.completed {
    background: #F0FDF4;
    color: #166534;
}

.status-badge.pending {
    background: #FEF3C7;
    color: #92400E;
}

.status-badge.failed {
    background: #FEF2F2;
    color: #B91C1C;
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

/* Enhanced Payment History Styles */
.date-cell {
    display: flex;
    flex-direction: column;
}

.date-day {
    font-weight: 600;
    color: #1E293B;
}

.date-time {
    font-size: 0.75rem;
    color: #64748B;
}

.type-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.type-badge.payment {
    background: #EFF6FF;
    color: #1E40AF;
}

.type-badge.subscription {
    background: #F0FDF4;
    color: #166534;
}

.plan-name {
    font-weight: 600;
    color: #1E293B;
    display: block;
}

.from-plan {
    font-size: 0.7rem;
    color: #64748B;
    display: block;
    margin-top: 2px;
}

.amount-cell {
    font-weight: 700;
    color: #059669;
}

.payment-method {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.85rem;
    color: #4A5568;
}

.payment-method i {
    color: #667eea;
}

.transaction-id {
    display: block;
    font-size: 0.65rem;
    color: #94A3B8;
    margin-top: 3px;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .date-day {
        color: #F1F5F9;
    }
    
    .plan-name {
        color: #F1F5F9;
    }
    
    .type-badge.payment {
        background: #1E3A5F;
        color: #90CDF4;
    }
    
    .type-badge.subscription {
        background: #1A4731;
        color: #9AE6B4;
    }
    
    .payment-method {
        color: #CBD5E0;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .pricing-card.popular {
        transform: scale(1);
    }
    
    .active-subscription {
        flex-direction: column;
        text-align: center;
    }
    
    .page-title {
        font-size: 2rem;
    }
}

@media (max-width: 480px) {
    .subscription-container {
        padding: 20px 15px;
    }
    
    .page-title {
        font-size: 1.8rem;
    }
    
    .trial-badge {
        font-size: 0.9rem;
        padding: 10px 20px;
    }
    
    .pricing-card {
        padding: 30px 20px;
    }
    
    .amount {
        font-size: 2.5rem;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .pricing-card {
        background: #1E293B;
    }
    
    .pricing-header h3,
    .amount,
    .pricing-features li {
        color: #F1F5F9;
    }
    
    .history-table {
        background: #1E293B;
    }
    
    .history-table th {
        background: #334155;
        color: #F1F5F9;
    }
    
    .history-table td {
        color: #F1F5F9;
        border-bottom-color: #334155;
    }
    
    .active-subscription {
        background: linear-gradient(135deg, #1E293B, #2D3A4F);
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>