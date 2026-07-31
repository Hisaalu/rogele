<?php
// File: /views/external/upgrade-confirmation.php
$pageTitle = 'Confirm Upgrade | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$fromPlan = $fromPlan ?? '';
$toPlan = $toPlan ?? '';
$fromPlanDetails = $fromPlanDetails ?? [];
$toPlanDetails = $toPlanDetails ?? [];
$priceCalculation = $priceCalculation ?? [];

$comparisonPlans = [
    'current' => [
        'badge' => 'Current Plan',
        'badge_class' => 'current-plan',
        'icon' => $fromPlan === 'yearly' ? 'crown' : ($fromPlan === 'termly' ? 'star' : 'user'),
        'name' => ucfirst($fromPlanDetails['name'] ?? $fromPlan),
        'price' => $fromPlanDetails['price'] ?? 0,
        'features' => $fromPlanDetails['features'] ?? []
    ],
    'new' => [
        'badge' => 'New Plan',
        'badge_class' => 'new-plan',
        'icon' => $toPlan === 'yearly' ? 'crown' : ($toPlan === 'termly' ? 'star' : 'rocket'),
        'name' => ucfirst($toPlanDetails['name'] ?? $toPlan),
        'price' => $toPlanDetails['price'] ?? 0,
        'features' => $toPlanDetails['features'] ?? []
    ]
];
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

        <div class="plan-comparison">
            <?php foreach ($comparisonPlans as $type => $plan): ?>
                <div class="plan-card <?= $plan['badge_class'] ?>">
                    <div class="plan-badge"><?= $plan['badge'] ?></div>
                    <div class="plan-icon">
                        <i class="fas fa-<?= $plan['icon'] ?>"></i>
                    </div>
                    <h3><?= htmlspecialchars($plan['name']) ?></h3>
                    <div class="plan-price">
                        <small>UGX</small>
                        <span><?= number_format($plan['price']) ?></span>
                    </div>
                    <ul class="plan-features">
                        <?php foreach ($plan['features'] as $feature): ?>
                            <li><i class="fas fa-check"></i> <?= htmlspecialchars($feature) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php if ($type === 'current'): ?>
                    <div class="upgrade-arrow">
                        <i class="fas fa-arrow-right"></i>
                        <i class="fas fa-arrow-down"></i>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div class="price-breakdown">
            <h3>Payment Summary</h3>
            
            <div class="breakdown-item">
                <span>New Plan Price:</span>
                <strong>UGX <?= number_format($priceCalculation['new_price'] ?? 0) ?></strong>
            </div>
            
            <div class="breakdown-item">
                <span>Remaining Value (<?= (int)($priceCalculation['days_remaining'] ?? 0) ?> days):</span>
                <strong class="text-success">- UGX <?= number_format($priceCalculation['remaining_value'] ?? 0) ?></strong>
            </div>
            
            <div class="breakdown-divider"></div>
            
            <div class="breakdown-item total">
                <span>You Pay Today:</span>
                <strong class="total-amount">UGX <?= number_format($priceCalculation['upgrade_price'] ?? 0) ?></strong>
            </div>
            
            <div class="savings-note">
                <i class="fas fa-info-circle"></i>
                <span>You're only paying the difference! Your remaining subscription value has been credited.</span>
            </div>
        </div>

        <form action="<?= BASE_URL ?>/external/process-upgrade" method="POST" class="payment-form">
            <input type="hidden" name="from_plan" value="<?= htmlspecialchars($fromPlan) ?>">
            <input type="hidden" name="to_plan" value="<?= htmlspecialchars($toPlan) ?>">
            <input type="hidden" name="amount" value="<?= (float)($priceCalculation['upgrade_price'] ?? 0) ?>">
            <input type="hidden" name="payment_method" value="mobile_money">
            
            <h3>Payment Details</h3>

            <div class="payment-details">
                <div class="form-group">
                    <label for="provider">Mobile Network</label>
                    <select name="provider" id="provider" required>
                        <option value="mtn">MTN Mobile Money</option>
                        <option value="airtel">Airtel Money</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="phone_number">Mobile Money Number</label>
                    <input type="tel" id="phone_number" name="phone_number" placeholder="e.g., 0772 123 456" required>
                </div>
            </div>

            <div class="terms-section">
                <label class="checkbox-label">
                    <input type="checkbox" name="terms" required>
                    <span>I agree to the <a href="<?= BASE_URL ?>/terms-of-service" target="_blank">Terms of Service</a></span>
                </label>
            </div>

            <div class="form-actions">
                <a href="<?= BASE_URL ?>/external/subscription" class="btn-cancel">
                    <i class="fas fa-arrow-left"></i> Cancel
                </a>
                <button type="submit" class="btn-pay">
                    Pay UGX <?= number_format($priceCalculation['upgrade_price'] ?? 0) ?>
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
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
    background-color: #f06724;
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
    color: #000;
    margin-bottom: 10px;
}

.card-header p {
    color: #555;
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
    border: 2px solid #f06724;
}

.plan-card.new-plan {
    border: 2px solid #f06724;
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
    background: #f06724;
    color: white;
}

.new-plan .plan-badge {
    background-color: #7f2677;
    color: white;
}

.plan-icon {
    width: 60px;
    height: 60px;
    background-color: #f06724;
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
    color: #000;
}

.plan-price {
    margin-bottom: 20px;
}

.plan-price span {
    font-size: 2rem;
    font-weight: 800;
    color: #000;
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
    color: #555;
    font-size: 0.9rem;
}

.plan-features li i {
    color: #10B981;
}

.upgrade-arrow {
    display: flex;
    flex-direction: column;
    align-items: center;
    color: #7f2677;
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
    color: #000;
}

.breakdown-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    font-weight: 300;
    color: #000;
}

.breakdown-item.total {
    margin-top: 20px;
    font-size: 1.2rem;
    font-weight: 700;
}

.total-amount {
    color: #7f2677;
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

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    box-sizing: border-box;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #f06724;
    box-shadow: 0 0 0 2px rgba(240, 103, 36, 0.2);
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
    transition: all 0.3s ease;
}

.btn-cancel {
    background: #F1F5F9;
    color: #1E293B;
}

.btn-cancel:hover {
    background: #E2E8F0;
}

.btn-pay {
    background-color: #7f2677;
    color: white;
}

.btn-pay:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(127, 38, 119, 0.3);
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
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
const phoneInput = document.getElementById('phone_number');
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