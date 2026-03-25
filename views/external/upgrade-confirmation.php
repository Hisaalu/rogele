<?php
// File: /views/external/upgrade-confirmation.php
$pageTitle = 'Confirm Upgrade | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

// Get data from controller
$fromPlan = $fromPlan ?? '';
$toPlan = $toPlan ?? '';
$fromPlanDetails = $fromPlanDetails ?? [];
$toPlanDetails = $toPlanDetails ?? [];
$priceCalculation = $priceCalculation ?? [];
?>

<div class="upgrade-container">
    <div class="upgrade-card">
        <div class="card-header">
            <div class="header-icon">
                <i class="fas fa-rocket"></i>
            </div>
            <h1>Upgrade Your Plan</h1>
            <p>Review your upgrade details below</p>
        </div>

        <!-- Plan Comparison -->
        <div class="plan-comparison">
            <div class="plan-card current-plan">
                <div class="plan-badge">Current Plan</div>
                <div class="plan-icon">
                    <i class="fas fa-<?php echo $fromPlan === 'yearly' ? 'crown' : ($fromPlan === 'termly' ? 'star' : 'user'); ?>"></i>
                </div>
                <h3><?php echo ucfirst($fromPlanDetails['name'] ?? $fromPlan); ?></h3>
                <div class="plan-price">
                    <small>UGX</small>
                    <span><?php echo number_format($fromPlanDetails['price'] ?? 0); ?></span>
                </div>
                <ul class="plan-features">
                    <?php foreach (($fromPlanDetails['features'] ?? []) as $feature): ?>
                        <li><i class="fas fa-check"></i> <?php echo $feature; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="upgrade-arrow">
                <i class="fas fa-arrow-right"></i>
                <i class="fas fa-arrow-down"></i>
            </div>

            <div class="plan-card new-plan">
                <div class="plan-badge">New Plan</div>
                <div class="plan-icon">
                    <i class="fas fa-<?php echo $toPlan === 'yearly' ? 'crown' : ($toPlan === 'termly' ? 'star' : 'rocket'); ?>"></i>
                </div>
                <h3><?php echo ucfirst($toPlanDetails['name'] ?? $toPlan); ?></h3>
                <div class="plan-price">
                    <small>UGX</small>
                    <span><?php echo number_format($toPlanDetails['price'] ?? 0); ?></span>
                </div>
                <ul class="plan-features">
                    <?php foreach (($toPlanDetails['features'] ?? []) as $feature): ?>
                        <li><i class="fas fa-check"></i> <?php echo $feature; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Price Breakdown -->
        <div class="price-breakdown">
            <h3>💰 Payment Summary</h3>
            
            <div class="breakdown-item">
                <span>New Plan Price:</span>
                <strong>UGX <?php echo number_format($priceCalculation['new_price'] ?? 0); ?></strong>
            </div>
            
            <div class="breakdown-item">
                <span>Remaining Value (<?php echo $priceCalculation['days_remaining'] ?? 0; ?> days):</span>
                <strong class="text-success">- UGX <?php echo number_format($priceCalculation['remaining_value'] ?? 0); ?></strong>
            </div>
            
            <div class="breakdown-divider"></div>
            
            <div class="breakdown-item total">
                <span>You Pay Today:</span>
                <strong class="total-amount">UGX <?php echo number_format($priceCalculation['upgrade_price'] ?? 0); ?></strong>
            </div>
            
            <div class="savings-note">
                <i class="fas fa-info-circle"></i>
                <span>You're only paying the difference! Your remaining subscription value has been credited.</span>
            </div>
        </div>

        <!-- Payment Form -->
        <form action="<?php echo BASE_URL; ?>/external/process-upgrade" method="POST" class="payment-form">
            <input type="hidden" name="from_plan" value="<?php echo htmlspecialchars($fromPlan); ?>">
            <input type="hidden" name="to_plan" value="<?php echo htmlspecialchars($toPlan); ?>">
            <input type="hidden" name="amount" value="<?php echo $priceCalculation['upgrade_price'] ?? 0; ?>">
            
            <h3>💳 Select Payment Method</h3>
            
            <div class="payment-methods">
                <label class="payment-method">
                    <input type="radio" name="payment_method" value="mobile_money" checked>
                    <div class="method-content">
                        <i class="fas fa-mobile-alt"></i>
                        <div>
                            <strong>Mobile Money</strong>
                            <small>Pay via MTN or Airtel Money</small>
                        </div>
                    </div>
                </label>
                
                <label class="payment-method">
                    <input type="radio" name="payment_method" value="card">
                    <div class="method-content">
                        <i class="fas fa-credit-card"></i>
                        <div>
                            <strong>Card Payment</strong>
                            <small>Visa, Mastercard, or American Express</small>
                        </div>
                    </div>
                </label>
            </div>

            <div class="payment-details" id="mobileMoneyDetails">
                <div class="form-group">
                    <label for="phone_number">Mobile Money Number</label>
                    <input type="tel" id="phone_number" name="phone_number" placeholder="e.g., 0772 123 456">
                </div>
            </div>

            <div class="payment-details" id="cardDetails" style="display: none;">
                <div class="form-row">
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" id="card_number" placeholder="1234 5678 9012 3456">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="expiry_date">Expiry Date</label>
                        <input type="text" id="expiry_date" placeholder="MM/YY">
                    </div>
                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" placeholder="123">
                    </div>
                </div>
            </div>

            <div class="terms-section">
                <label class="checkbox-label">
                    <input type="checkbox" name="terms" required>
                    <span>I agree to the <a href="/terms">Terms of Service</a></span>
                </label>
            </div>

            <div class="form-actions">
                <a href="<?php echo BASE_URL; ?>/external/subscription" class="btn-cancel">
                    <i class="fas fa-arrow-left"></i> Cancel
                </a>
                <button type="submit" class="btn-pay">
                    Pay UGX <?php echo number_format($priceCalculation['upgrade_price'] ?? 0); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.upgrade-container {
    max-width: 1000px;
    margin: 40px auto;
    padding: 0 20px;
}

.upgrade-card {
    background: white;
    border-radius: 30px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
}

.card-header {
    text-align: center;
    margin-bottom: 40px;
}

.header-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.header-icon i {
    font-size: 40px;
    color: white;
}

.card-header h1 {
    font-size: 2rem;
    color: #1E293B;
    margin-bottom: 10px;
}

.card-header p {
    color: #64748B;
}

.plan-comparison {
    display: flex;
    align-items: center;
    gap: 30px;
    margin-bottom: 40px;
    flex-wrap: wrap;
    justify-content: center;
}

.plan-card {
    flex: 1;
    min-width: 250px;
    background: #F8FAFC;
    border-radius: 20px;
    padding: 30px;
    position: relative;
    text-align: center;
}

.plan-card.current-plan {
    border: 2px solid #94A3B8;
}

.plan-card.new-plan {
    border: 2px solid #8B5CF6;
    background: linear-gradient(135deg, #F8FAFC, white);
}

.plan-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    padding: 5px 20px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
}

.current-plan .plan-badge {
    background: #94A3B8;
    color: white;
}

.new-plan .plan-badge {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
}

.plan-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.plan-icon i {
    font-size: 30px;
    color: white;
}

.plan-card h3 {
    font-size: 1.5rem;
    margin-bottom: 15px;
    color: #1E293B;
}

.plan-price {
    margin-bottom: 20px;
}

.plan-price span {
    font-size: 2rem;
    font-weight: 800;
    color: #1E293B;
}

.plan-features {
    list-style: none;
    padding: 0;
    margin: 0;
    text-align: left;
}

.plan-features li {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    color: #4A5568;
    font-size: 0.9rem;
}

.plan-features li i {
    color: #10B981;
}

.upgrade-arrow {
    display: flex;
    flex-direction: column;
    align-items: center;
    color: #8B5CF6;
    font-size: 2rem;
}

.upgrade-arrow i:last-child {
    display: none;
}

.price-breakdown {
    background: #F8FAFC;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 40px;
}

.price-breakdown h3 {
    margin-bottom: 20px;
    color: #1E293B;
}

.breakdown-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    color: #4A5568;
}

.breakdown-item.total {
    margin-top: 20px;
    font-size: 1.2rem;
    font-weight: 700;
}

.total-amount {
    color: #8B5CF6;
    font-size: 1.3rem;
}

.text-success {
    color: #10B981;
}

.breakdown-divider {
    height: 2px;
    background: #E2E8F0;
    margin: 20px 0;
}

.savings-note {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #FEF3C7;
    border-radius: 12px;
    padding: 15px;
    margin-top: 20px;
    color: #92400E;
}

.payment-methods {
    display: flex;
    gap: 15px;
    margin: 20px 0;
}

.payment-method {
    flex: 1;
    cursor: pointer;
}

.payment-method input {
    display: none;
}

.method-content {
    border: 2px solid #E2E8F0;
    border-radius: 16px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: all 0.3s ease;
}

.payment-method input:checked + .method-content {
    border-color: #8B5CF6;
    background: #F8FAFC;
}

.method-content i {
    font-size: 1.5rem;
    color: #8B5CF6;
}

.payment-details {
    background: #F8FAFC;
    border-radius: 16px;
    padding: 20px;
    margin: 20px 0;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 12px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.terms-section {
    margin: 20px 0;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn-cancel, .btn-pay {
    flex: 1;
    padding: 14px;
    border-radius: 50px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.btn-cancel {
    background: #F1F5F9;
    color: #1E293B;
}

.btn-pay {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
}

@media (max-width: 768px) {
    .plan-comparison {
        flex-direction: column;
    }
    
    .upgrade-arrow i:first-child {
        display: none;
    }
    
    .upgrade-arrow i:last-child {
        display: block;
    }
    
    .payment-methods {
        flex-direction: column;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('mobileMoneyDetails').style.display = this.value === 'mobile_money' ? 'block' : 'none';
        document.getElementById('cardDetails').style.display = this.value === 'card' ? 'block' : 'none';
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>