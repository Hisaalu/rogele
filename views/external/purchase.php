<?php
// File: /views/external/purchase.php
$pageTitle = 'Purchase Subscription - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

$plan = $_GET['plan'] ?? 'monthly';
$validPlans = ['monthly', 'termly', 'yearly'];
if (!in_array($plan, $validPlans)) {
    $plan = 'monthly';
}

// Get subscription settings from database
$subscriptionSettings = $subscriptionSettings ?? [];
$planPrices = [
    'monthly' => $subscriptionSettings['monthly_price'] ?? 15000,
    'termly' => $subscriptionSettings['termly_price'] ?? 40000,
    'yearly' => $subscriptionSettings['yearly_price'] ?? 120000
];
$planNames = [
    'monthly' => 'Monthly',
    'termly' => 'Termly',
    'yearly' => 'Yearly'
];
$selectedPrice = $planPrices[$plan] ?? 15000;
$selectedName = $planNames[$plan] ?? 'Monthly';
$trialDays = $subscriptionSettings['trial_days'] ?? 60;
?>

<div class="purchase-container">
    <!-- Back Link -->
    <div class="back-link">
        <a href="/rays-of-grace/external/subscription">
            <i class="fas fa-arrow-left"></i> Back to Plans
        </a>
    </div>

    <div class="purchase-card">
        <h1 class="page-title">Complete Your Purchase</h1>
        <p class="page-subtitle">You're about to purchase the <strong><?php echo $selectedName; ?></strong> plan</p>

        <?php if ($trialDays > 0): ?>
        <div class="trial-notice">
            <i class="fas fa-gift"></i>
            <span>Your <?php echo $trialDays; ?>-day free trial starts immediately after payment</span>
        </div>
        <?php endif; ?>

        <!-- Order Summary -->
        <div class="order-summary">
            <h3>Order Summary</h3>
            <div class="summary-item">
                <span><?php echo $selectedName; ?> Plan</span>
                <span class="price">UGX <?php echo number_format($selectedPrice); ?></span>
            </div>
            <div class="summary-item">
                <span>Tax</span>
                <span class="price">UGX 0</span>
            </div>
            <div class="summary-total">
                <span>Total</span>
                <span class="total-price">UGX <?php echo number_format($selectedPrice); ?></span>
            </div>
        </div>

        <!-- Payment Form -->
        <form method="POST" action="/rays-of-grace/external/process-payment" class="payment-form">
            <input type="hidden" name="plan" value="<?php echo $plan; ?>">
            
            <div class="form-group">
                <label for="phone_number">
                    <i class="fas fa-mobile-alt"></i>
                    Mobile Money Number
                </label>
                <input 
                    type="tel" 
                    id="phone_number" 
                    name="phone_number" 
                    required 
                    placeholder="e.g., 0772XXXXXX"
                    pattern="[0-9]{10}"
                >
                <small class="input-hint">Enter your MTN or Airtel mobile money number</small>
            </div>

            <div class="form-group">
                <label for="payment_method">
                    <i class="fas fa-credit-card"></i>
                    Payment Method
                </label>
                <select id="payment_method" name="payment_method" required>
                    <option value="mtn">MTN Mobile Money</option>
                    <option value="airtel">Airtel Money</option>
                </select>
            </div>

            <div class="terms-group">
                <input type="checkbox" id="terms" required>
                <label for="terms">
                    I agree to the <a href="/terms" target="_blank">Terms of Service</a> and 
                    <a href="/privacy" target="_blank">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" class="btn-pay">
                <i class="fas fa-lock"></i>
                Pay UGX <?php echo number_format($selectedPrice); ?>
            </button>

            <div class="secure-note">
                <i class="fas fa-shield-alt"></i>
                <span>Your payment information is secure and encrypted</span>
            </div>
        </form>
    </div>
</div>

<style>
.purchase-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 40px 20px;
}

.back-link {
    margin-bottom: 30px;
}

.back-link a {
    color: #64748B;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: color 0.3s ease;
}

.back-link a:hover {
    color: #8B5CF6;
}

.purchase-card {
    background: white;
    border-radius: 30px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
    text-align: center;
}

.page-subtitle {
    color: #64748B;
    text-align: center;
    margin-bottom: 30px;
}

.page-subtitle strong {
    color: #1E293B;
}

.trial-notice {
    background: #F0FDF4;
    border: 1px solid #BBF7D0;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #166534;
}

.trial-notice i {
    font-size: 1.5rem;
}

.order-summary {
    background: #F8FAFC;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 30px;
}

.order-summary h3 {
    color: #1E293B;
    font-size: 1.2rem;
    margin-bottom: 20px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    color: #64748B;
}

.summary-item .price {
    font-weight: 600;
    color: #1E293B;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    padding-top: 15px;
    margin-top: 15px;
    border-top: 2px dashed #E2E8F0;
    font-weight: 700;
    font-size: 1.2rem;
    color: #1E293B;
}

.total-price {
    color: #8B5CF6;
}

.payment-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 600;
    color: #1E293B;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group label i {
    color: #8B5CF6;
}

.form-group input,
.form-group select {
    padding: 14px 16px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #8B5CF6;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.input-hint {
    font-size: 0.85rem;
    color: #64748B;
}

.terms-group {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 10px 0;
}

.terms-group input {
    width: 18px;
    height: 18px;
    accent-color: #8B5CF6;
}

.terms-group label {
    color: #64748B;
    font-size: 0.95rem;
}

.terms-group a {
    color: #8B5CF6;
    text-decoration: none;
}

.terms-group a:hover {
    text-decoration: underline;
}

.btn-pay {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
    border: none;
    padding: 16px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-pay:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
}

.secure-note {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #64748B;
    font-size: 0.9rem;
    margin-top: 20px;
}

.secure-note i {
    color: #10B981;
}

/* Responsive */
@media (max-width: 480px) {
    .purchase-card {
        padding: 25px;
    }
    
    .page-title {
        font-size: 1.6rem;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .purchase-card {
        background: #1E293B;
    }
    
    .order-summary {
        background: #334155;
    }
    
    .order-summary h3,
    .summary-item .price,
    .summary-total {
        color: #F1F5F9;
    }
    
    .form-group label {
        color: #F1F5F9;
    }
    
    .form-group input,
    .form-group select {
        background: #0F172A;
        border-color: #334155;
        color: #F1F5F9;
    }
    
    .trial-notice {
        background: #1E3A5F;
        border-color: #2563EB;
        color: #93C5FD;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>