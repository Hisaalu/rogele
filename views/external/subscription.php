<?php
// File: /views/external/subscription.php
$pageTitle = 'Subscription | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

// Get settings from controller
$subscriptionSettings = $subscriptionSettings ?? [];
$monthlyPrice = $subscriptionSettings['monthly_price'] ?? 15000;
$termlyPrice = $subscriptionSettings['termly_price'] ?? 40000;
$yearlyPrice = $subscriptionSettings['yearly_price'] ?? 120000;
$trialDays = $subscriptionSettings['trial_days'] ?? 60;

// Calculate savings
$monthlyTotal3 = $monthlyPrice * 3;
$monthlyTotal12 = $monthlyPrice * 12;
$termlySavings = $monthlyTotal3 - $termlyPrice;
$yearlySavings = $monthlyTotal12 - $yearlyPrice;
$termlySavingsPercent = $monthlyTotal3 > 0 ? round(($termlySavings / $monthlyTotal3) * 100) : 0;
$yearlySavingsPercent = $monthlyTotal12 > 0 ? round(($yearlySavings / $monthlyTotal12) * 100) : 0;
?>

<div class="subscription-container">
    <!-- Header Section -->
    <div class="subscription-header">
        <div class="badge">ROGELE </div>
        <h1 class="page-title">Choose Your Learning Path</h1>
        <p class="page-subtitle">Select the perfect plan for your educational journey</p>
        <?php if ($trialDays > 0 && !$currentSubscription): ?>
        <div class="trial-badge">
            <i class="fas fa-gift"></i>
            <span><?php echo $trialDays; ?> Days Free Trial on All Plans!</span>
            <i class="fas fa-star"></i>
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
                <h3>🎉 You're on the <?php echo ucfirst($currentSubscription['plan_type']); ?> Plan!</h3>
                <p>Valid until <?php echo date('F j, Y', strtotime($currentSubscription['end_date'])); ?></p>
            </div>
            <?php
            $currentPlan = $currentSubscription['plan_type'];
            $nextPlan = $currentPlan === 'monthly' ? 'termly' : ($currentPlan === 'termly' ? 'yearly' : null);
            if ($nextPlan):
            ?>
            <a href="<?php echo BASE_URL; ?>/external/upgrade-confirmation?from=<?php echo $currentPlan; ?>&to=<?php echo $nextPlan; ?>" class="btn-upgrade">
                <i class="fas fa-rocket"></i> Upgrade to <?php echo ucfirst($nextPlan); ?>
            </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Pricing Cards -->
    <div class="pricing-grid">
        <!-- Monthly Plan -->
        <div class="pricing-card" data-plan="monthly" data-price="<?php echo $monthlyPrice; ?>">
            <div class="plan-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <h3 class="plan-name">Monthly</h3>
            <div class="price-wrapper">
                <span class="currency">UGX</span>
                <span class="amount"><?php echo number_format($monthlyPrice); ?></span>
            </div>
            <p class="period">per month • cancel anytime</p>
            <ul class="features-list">
                <li><i class="fas fa-check-circle"></i> Full access to all lessons</li>
                <li><i class="fas fa-check-circle"></i> Practice quizzes & assessments</li>
                <li><i class="fas fa-check-circle"></i> Progress tracking dashboard</li>
                <li><i class="fas fa-check-circle"></i> 24/7 email support</li>
            </ul>
            <?php if (!$currentSubscription): ?>
            <button class="btn-select open-payment-modal" data-plan="monthly" data-price="<?php echo $monthlyPrice; ?>">
                <i class="fas fa-shopping-cart"></i> Select Plan
            </button>
            <?php endif; ?>
        </div>

        <!-- Termly Plan (Most Popular) -->
        <div class="pricing-card popular" data-plan="termly" data-price="<?php echo $termlyPrice; ?>">
            <div class="popular-badge">⭐ MOST POPULAR</div>
            <div class="plan-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <h3 class="plan-name">Termly</h3>
            <div class="price-wrapper">
                <span class="currency">UGX</span>
                <span class="amount"><?php echo number_format($termlyPrice); ?></span>
            </div>
            <p class="period">per term (3 months)</p>
            <div class="savings-tag">Save <?php echo number_format($termlySavings); ?> UGX (<?php echo $termlySavingsPercent; ?>%)</div>
            <ul class="features-list">
                <li><i class="fas fa-check-circle"></i> Everything in Monthly</li>
                <li><i class="fas fa-check-circle"></i> Save <?php echo number_format($termlySavings); ?> UGX</li>
                <li><i class="fas fa-check-circle"></i> Priority support</li>
                <li><i class="fas fa-check-circle"></i> Downloadable materials</li>
            </ul>
            <?php if (!$currentSubscription): ?>
            <button class="btn-select btn-primary open-payment-modal" data-plan="termly" data-price="<?php echo $termlyPrice; ?>">
                <i class="fas fa-rocket"></i> Select Plan
            </button>
            <?php endif; ?>
        </div>

        <!-- Yearly Plan -->
        <div class="pricing-card" data-plan="yearly" data-price="<?php echo $yearlyPrice; ?>">
            <div class="plan-icon">
                <i class="fas fa-crown"></i>
            </div>
            <h3 class="plan-name">Yearly</h3>
            <div class="price-wrapper">
                <span class="currency">UGX</span>
                <span class="amount"><?php echo number_format($yearlyPrice); ?></span>
            </div>
            <p class="period">per year • best value</p>
            <div class="savings-tag best">Save <?php echo number_format($yearlySavings); ?> UGX (<?php echo $yearlySavingsPercent; ?>%)</div>
            <ul class="features-list">
                <li><i class="fas fa-check-circle"></i> Everything in Termly</li>
                <li><i class="fas fa-check-circle"></i> 2 months free</li>
                <li><i class="fas fa-check-circle"></i> Certificate of completion</li>
                <li><i class="fas fa-check-circle"></i> 1-on-1 tutoring sessions</li>
            </ul>
            <?php if (!$currentSubscription): ?>
            <button class="btn-select open-payment-modal" data-plan="yearly" data-price="<?php echo $yearlyPrice; ?>">
                <i class="fas fa-crown"></i> Select Plan
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment History -->
    <?php if (!empty($paymentHistory)): ?>
        <div class="history-section">
            <h2 class="section-title">
                <i class="fas fa-history"></i>
                Payment History
            </h2>
            <div class="table-responsive">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paymentHistory as $payment): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></td>
                            <td><strong><?php echo ucfirst($payment['plan_type'] ?? 'N/A'); ?></strong></td>
                            <td>UGX <?php echo number_format($payment['amount']); ?></td>
                            <td>
                                <i class="fas fa-<?php echo ($payment['payment_method'] ?? 'mobile_money') === 'mobile_money' ? 'mobile-alt' : 'credit-card'; ?>"></i>
                                <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'] ?? 'unknown')); ?>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $payment['status']; ?>">
                                    <?php echo ucfirst($payment['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Pesapal Payment Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-credit-card"></i> Complete Payment</h3>
            <span class="close">&times;</span>
        </div>
        
        <form id="paymentForm" action="<?php echo BASE_URL; ?>/external/process-pesapal-payment" method="POST">
            <input type="hidden" name="plan" id="selectedPlan">
            
            <div class="plan-summary">
                <p>You're subscribing to: <strong id="planNameDisplay"></strong></p>
                <p class="amount-display">Total: <span id="planAmountDisplay"></span></p>
            </div>
            
            <div class="payment-methods">
                <label class="method-option">
                    <input type="radio" name="payment_method" value="mobile_money" checked>
                    <div class="method-card">
                        <i class="fas fa-mobile-alt"></i>
                        <span>Mobile Money</span>
                    </div>
                </label>
                <label class="method-option">
                    <input type="radio" name="payment_method" value="card">
                    <div class="method-card">
                        <i class="fas fa-credit-card"></i>
                        <span>Card Payment</span>
                    </div>
                </label>
            </div>
            
            <div id="mobileMoneyFields" class="payment-fields">
                <div class="input-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone_number" placeholder="0772 123 456" required>
                    <small>Enter your MTN or Airtel number</small>
                </div>
            </div>
            
            <div id="cardFields" class="payment-fields" style="display: none;">
                <div class="input-group">
                    <label>Card Number</label>
                    <input type="text" id="card_number" placeholder="1234 5678 9012 3456">
                </div>
                <div class="row">
                    <div class="input-group">
                        <label>Expiry Date</label>
                        <input type="text" id="expiry_date" placeholder="MM/YY">
                    </div>
                    <div class="input-group">
                        <label>CVV</label>
                        <input type="text" id="cvv" placeholder="123">
                    </div>
                </div>
            </div>
            
            <div class="secure-badge">
                <i class="fas fa-shield-alt"></i>
                <span>Secured by PesaPal</span>
            </div>
            
            <div class="modal-buttons">
                <button type="button" class="btn-cancel">Cancel</button>
                <button type="submit" class="btn-submit">Pay Now</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Main Container */
.subscription-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 48px 24px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Header */
.subscription-header {
    text-align: center;
    margin-bottom: 60px;
}

.badge {
    display: inline-block;
    background: linear-gradient(135deg, #7f2677);
    color: white;
    padding: 6px 18px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 20px;
    letter-spacing: 1px;
}

.page-title {
    font-size: 3rem;
    font-weight: 800;
    background: linear-gradient(135deg, black);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 16px;
}

.page-subtitle {
    color: black;
    font-size: 1.2rem;
    max-width: 600px;
    margin: 0 auto;
}

.trial-badge {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(135deg, #f06724);
    color: white;
    padding: 12px 28px;
    border-radius: 60px;
    font-weight: 600;
    margin-top: 25px;
    box-shadow: 0 10px 25px rgba(245, 158, 11, 0.3);
    animation: pulse 2s infinite;
}

/* Active Subscription */
.active-subscription {
    background: linear-gradient(135deg, #F0FDF4, #FFFFFF);
    border: 2px solid #10B981;
    border-radius: 24px;
    padding: 24px 32px;
    margin-bottom: 48px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
}

.active-icon {
    width: 56px;
    height: 56px;
    background: #10B981;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.active-icon i {
    font-size: 1.8rem;
    color: white;
}

.active-content h3 {
    color: #065F46;
    font-size: 1.2rem;
    margin-bottom: 4px;
}

.active-content p {
    color: #047857;
}

.btn-upgrade {
    background: linear-gradient(135deg, #7f2677);
    color: white;
    padding: 12px 28px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-upgrade:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
}

/* Pricing Grid */
.pricing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 32px;
    margin-bottom: 60px;
}

.pricing-card {
    background: white;
    border-radius: 32px;
    padding: 40px 32px;
    position: relative;
    transition: all 0.3s ease;
    box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1);
    border: 2px solid transparent;
}

.pricing-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 30px 45px -15px rgba(0, 0, 0, 0.2);
}

.pricing-card.popular {
    border-color: #7f2677;
    background: linear-gradient(135deg, #FFFFFF, #FFFBEB);
}

.popular-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #f06724);
    color: white;
    padding: 6px 24px;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.8rem;
    white-space: nowrap;
}

.plan-icon {
    width: 64px;
    height: 64px;
    background: #F1F5F9;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px;
}

.plan-icon i {
    font-size: 2rem;
    color: #7f2677;
}

.pricing-card.popular .plan-icon i {
    color: #7f2677;
}

.plan-name {
    font-size: 1.8rem;
    font-weight: 800;
    color: black;
    margin-bottom: 16px;
}

.price-wrapper {
    margin-bottom: 8px;
}

.currency {
    font-size: 1rem;
    color: black;
    vertical-align: top;
}

.amount {
    font-size: 3rem;
    font-weight: 800;
    color: black;
    line-height: 1;
}

.period {
    color: black;
    font-size: 0.9rem;
    margin-bottom: 24px;
}

.savings-tag {
    display: inline-block;
    background: #10B981;
    color: white;
    padding: 5px 12px;
    border-radius: 30px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 20px;
}

.savings-tag.best {
    background: #f06724;
}

.features-list {
    list-style: none;
    margin: 28px 0;
    padding: 0;
}

.features-list li {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
    color: black;
    font-size: 0.95rem;
}

.features-list li i {
    color: #10B981;
    font-size: 1rem;
    width: 20px;
}

.btn-select {
    width: 100%;
    padding: 14px;
    background: #F1F5F9;
    border: none;
    border-radius: 60px;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #7f2677;
}

.btn-select:hover {
    background: #f06724;
    transform: translateY(-2px);
}

.btn-select.btn-primary {
    background: linear-gradient(135deg, #7f2677);
    color: white;
}

.btn-select.btn-primary:hover {
    box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3);
}

/* Payment History */
.history-section {
    background: white;
    border-radius: 28px;
    padding: 32px;
    box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.05);
}

.section-title {
    font-size: 1.5rem;
    color: black;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.table-responsive {
    overflow-x: auto;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
}

.history-table th {
    text-align: left;
    padding: 12px 16px;
    background: #F8FAFC;
    font-weight: 600;
    color: black;
    border-radius: 12px;
}

.history-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #E2E8F0;
    color: black;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.75rem;
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

/* Alert Messages */
.alert {
    padding: 16px 20px;
    border-radius: 16px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert-success {
    background: #F0FDF4;
    border: 1px solid #BBF7D0;
    color: #166534;
}

.alert-error {
    background: #FEF2F2;
    border: 1px solid #FECACA;
    color: #B91C1C;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 28px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    animation: slideUp 0.3s ease;
}

.modal-header {
    background: linear-gradient(135deg, #7f2677);
    padding: 20px 24px;
    border-radius: 28px 28px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
}

.modal-header h3 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.close {
    font-size: 28px;
    cursor: pointer;
    line-height: 1;
    transition: transform 0.2s;
}

.close:hover {
    transform: scale(1.1);
}

#paymentForm {
    padding: 24px;
}

.plan-summary {
    background: #F8FAFC;
    border-radius: 16px;
    padding: 16px;
    text-align: center;
    margin-bottom: 24px;
}

.plan-summary strong {
    color: #7f2677;
    font-size: 1.1rem;
}

.amount-display {
    margin-top: 8px;
    font-size: 1.2rem;
}

.amount-display span {
    font-weight: 700;
    color: #7f2677;
}

.payment-methods {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
}

.method-option {
    flex: 1;
    cursor: pointer;
}

.method-option input {
    display: none;
}

.method-card {
    border: 2px solid #E2E8F0;
    border-radius: 16px;
    padding: 16px;
    text-align: center;
    transition: all 0.3s ease;
}

.method-option input:checked + .method-card {
    border-color: #7f2677;
    background: #F8FAFC;
}

.method-card i {
    font-size: 1.5rem;
    color: black;
    margin-bottom: 8px;
    display: block;
}

.method-card span {
    font-weight: 600;
    color: #1E293B;
}

.payment-fields {
    background: #F8FAFC;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
}

.input-group {
    margin-bottom: 16px;
}

.input-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: black;
    font-size: 0.9rem;
}

.input-group input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.input-group input:focus {
    outline: none;
    border-color: black;
}

.input-group small {
    display: block;
    margin-top: 6px;
    color: black;
    font-size: 0.75rem;
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.secure-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #EFF6FF;
    border-radius: 12px;
    padding: 12px;
    margin-bottom: 24px;
    font-size: 0.85rem;
    color: #1E40AF;
    justify-content: center;
}

.modal-buttons {
    display: flex;
    gap: 12px;
}

.btn-cancel, .btn-submit {
    flex: 1;
    padding: 14px;
    border-radius: 50px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-cancel {
    background: #F1F5F9;
    color: #1E293B;
}

.btn-cancel:hover {
    background: #E2E8F0;
}

.btn-submit {
    background: linear-gradient(135deg, #7f2677);
    color: white;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3);
}

/* Animations */
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .subscription-container {
        padding: 24px 16px;
    }
    
    .page-title {
        font-size: 2rem;
    }
    
    .pricing-grid {
        grid-template-columns: 1fr;
        gap: 24px;
    }
    
    .active-subscription {
        flex-direction: column;
        text-align: center;
    }
    
    .active-icon {
        margin-bottom: 8px;
    }
    
    .modal-content {
        width: 95%;
    }
    
    .row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .plan-name {
        font-size: 1.5rem;
    }
    
    .amount {
        font-size: 2.5rem;
    }
    
    .pricing-card {
        padding: 32px 24px;
    }
    
    .payment-methods {
        flex-direction: column;
    }
}

/* Dark Mode */
/* @media (prefers-color-scheme: dark) {
    .pricing-card {
        background: #f06724;
    }
    
    .pricing-card.popular {
        background: linear-gradient(135deg, #f06724);
    }
    
    .plan-name, .amount {
        color: #F1F5F9;
    }
    
    .features-list li {
        color: #CBD5E0;
    }
    
    .btn-select {
        background: black;
        color: #F1F5F9;
    }
    
    .btn-select:hover {
        background: #475569;
    }
    
    .history-section {
        background: #1E293B;
    }
    
    .section-title {
        color: #F1F5F9;
    }
    
    .history-table td {
        color: #CBD5E0;
        border-bottom-color: #334155;
    }
    
    .history-table th {
        background: #334155;
        color: #F1F5F9;
    }
    
    .modal-content {
        background: #1E293B;
    }
    
    .plan-summary {
        background: #334155;
    }
    
    .plan-summary p {
        color: #F1F5F9;
    }
    
    .payment-fields {
        background: #334155;
    }
    
    .input-group label {
        color: #F1F5F9;
    }
    
    .input-group input {
        background: #1E293B;
        border-color: #475569;
        color: #F1F5F9;
    }
    
    .method-card {
        border-color: black;
    }
    
    .method-card span {
        color: #F1F5F9;
    }
    
    .btn-cancel {
        background: #334155;
        color: #F1F5F9;
    }
} */
</style>

<script>
// Modal elements
const modal = document.getElementById('paymentModal');
const modalClose = document.querySelector('.close');
const cancelBtn = document.querySelector('.btn-cancel');
const paymentForm = document.getElementById('paymentForm');

// Open modal function
function openModal(plan, price) {
    document.getElementById('selectedPlan').value = plan;
    document.getElementById('planNameDisplay').innerHTML = plan.charAt(0).toUpperCase() + plan.slice(1) + ' Plan';
    document.getElementById('planAmountDisplay').innerHTML = 'UGX ' + parseInt(price).toLocaleString();
    modal.style.display = 'flex';
}

// Close modal function
function closeModal() {
    modal.style.display = 'none';
    paymentForm.reset();
}

// Add click handlers to all "Select Plan" buttons
document.querySelectorAll('.open-payment-modal').forEach(button => {
    button.addEventListener('click', function() {
        openModal(this.dataset.plan, this.dataset.price);
    });
});

// Close modal handlers
if (modalClose) modalClose.addEventListener('click', closeModal);
if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

// Close when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === modal) {
        closeModal();
    }
});

// Toggle payment method sections
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const mobileFields = document.getElementById('mobileMoneyFields');
        const cardFields = document.getElementById('cardFields');
        
        if (this.value === 'mobile_money') {
            mobileFields.style.display = 'block';
            cardFields.style.display = 'none';
        } else {
            mobileFields.style.display = 'none';
            cardFields.style.display = 'block';
        }
    });
});

// Format phone number
const phoneInput = document.querySelector('input[name="phone_number"]');
if (phoneInput) {
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 0 && value.startsWith('0')) {
            if (value.length > 3) value = value.slice(0, 4) + ' ' + value.slice(4, 7) + ' ' + value.slice(7, 10);
        }
        e.target.value = value;
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>