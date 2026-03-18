<?php
// File: /views/external/upgrade-confirmation.php
$pageTitle = 'Confirm Upgrade - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="upgrade-container">
    <!-- Progress Steps -->
    <div class="progress-steps">
        <div class="step completed">
            <div class="step-icon"><i class="fas fa-check"></i></div>
            <div class="step-label">Review</div>
        </div>
        <div class="step active">
            <div class="step-icon">2</div>
            <div class="step-label">Payment</div>
        </div>
        <div class="step">
            <div class="step-icon">3</div>
            <div class="step-label">Confirmation</div>
        </div>
    </div>

    <div class="upgrade-card">
        <div class="card-header">
            <div class="header-icon">
                <i class="fas fa-rocket"></i>
            </div>
            <h1>Confirm Your Upgrade</h1>
            <p>Please review your upgrade details below</p>
        </div>

        <!-- Plan Comparison -->
        <div class="plan-comparison">
            <div class="plan-card current-plan">
                <div class="plan-badge">Current Plan</div>
                <div class="plan-icon">
                    <i class="fas fa-<?php echo $fromPlan === 'yearly' ? 'crown' : ($fromPlan === 'termly' ? 'star' : 'user'); ?>"></i>
                </div>
                <h3><?php echo ucfirst($fromPlanDetails['name']); ?></h3>
                <div class="plan-price">
                    <small>UGX</small>
                    <span><?php echo number_format($fromPlanDetails['price']); ?></span>
                </div>
                <ul class="plan-features">
                    <?php foreach ($fromPlanDetails['features'] as $feature): ?>
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
                <h3><?php echo ucfirst($toPlanDetails['name']); ?></h3>
                <div class="plan-price">
                    <small>UGX</small>
                    <span><?php echo number_format($toPlanDetails['price']); ?></span>
                </div>
                <ul class="plan-features">
                    <?php foreach ($toPlanDetails['features'] as $feature): ?>
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
                <strong>UGX <?php echo number_format($priceCalculation['new_price']); ?></strong>
            </div>
            
            <div class="breakdown-item">
                <span>Remaining Value (<?php echo $priceCalculation['days_remaining']; ?> days):</span>
                <strong class="text-success">- UGX <?php echo number_format($priceCalculation['remaining_value']); ?></strong>
            </div>
            
            <div class="breakdown-divider"></div>
            
            <div class="breakdown-item total">
                <span>You Pay Today:</span>
                <strong class="total-amount">UGX <?php echo number_format($priceCalculation['upgrade_price']); ?></strong>
            </div>
            
            <div class="savings-note">
                <i class="fas fa-info-circle"></i>
                <span>You're only paying the difference! Your remaining subscription value has been credited.</span>
            </div>
        </div>

        <!-- Payment Form -->
        <form action="/rays-of-grace/external/process-upgrade" method="POST" class="payment-form">
            <input type="hidden" name="from_plan" value="<?php echo $fromPlan; ?>">
            <input type="hidden" name="to_plan" value="<?php echo $toPlan; ?>">
            <input type="hidden" name="amount" value="<?php echo $priceCalculation['upgrade_price']; ?>">
            
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
                
                <label class="payment-method">
                    <input type="radio" name="payment_method" value="bank_transfer">
                    <div class="method-content">
                        <i class="fas fa-university"></i>
                        <div>
                            <strong>Bank Transfer</strong>
                            <small>Direct bank deposit</small>
                        </div>
                    </div>
                </label>
            </div>

            <!-- Mobile Money Details (shown when mobile money selected) -->
            <div class="payment-details" id="mobileMoneyDetails">
                <div class="form-group">
                    <label for="phone_number">Mobile Money Number</label>
                    <input type="tel" id="phone_number" name="phone_number" placeholder="e.g., 0772 123 456">
                </div>
                <div class="form-group">
                    <label for="provider">Provider</label>
                    <select name="provider" id="provider">
                        <option value="mtn">MTN Uganda</option>
                        <option value="airtel">Airtel Uganda</option>
                    </select>
                </div>
            </div>

            <!-- Card Details (hidden initially) -->
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
                <div class="form-group">
                    <label for="card_name">Name on Card</label>
                    <input type="text" id="card_name" placeholder="John Doe">
                </div>
            </div>

            <!-- Bank Transfer Details (hidden initially) -->
            <div class="payment-details" id="bankDetails" style="display: none;">
                <div class="bank-info">
                    <p><strong>Bank:</strong> Stanbic Bank Uganda</p>
                    <p><strong>Account Name:</strong> Rays of Grace E-Learning</p>
                    <p><strong>Account Number:</strong> 9030012345678</p>
                    <p><strong>Branch:</strong> Kampala Main</p>
                    <p><strong>Swift Code:</strong> SBICUGKX</p>
                </div>
                <div class="form-group">
                    <label for="transaction_ref">Transaction Reference</label>
                    <input type="text" id="transaction_ref" name="transaction_ref" placeholder="Enter bank transaction reference">
                </div>
            </div>

            <!-- Terms and Submit -->
            <div class="terms-section">
                <label class="checkbox-label">
                    <input type="checkbox" name="terms" required>
                    <span>I agree to the <a href="/rays-of-grace/terms">Terms of Service</a> and <a href="/rays-of-grace/privacy">Privacy Policy</a></span>
                </label>
            </div>

            <div class="form-actions">
                <a href="/rays-of-grace/external/subscription" class="btn-cancel">
                    <i class="fas fa-arrow-left"></i> Cancel
                </a>
                <button type="submit" class="btn-pay">
                    Pay UGX <?php echo number_format($priceCalculation['upgrade_price']); ?>
                    <i class="fas fa-lock"></i>
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

/* Progress Steps */
.progress-steps {
    display: flex;
    justify-content: center;
    margin-bottom: 50px;
    position: relative;
}

.progress-steps::before {
    content: '';
    position: absolute;
    top: 25px;
    left: 50%;
    transform: translateX(-50%);
    width: 70%;
    height: 2px;
    background: #E2E8F0;
    z-index: 1;
}

.step {
    position: relative;
    z-index: 2;
    text-align: center;
    flex: 1;
    max-width: 120px;
}

.step.completed .step-icon {
    background: linear-gradient(135deg, #48BB78, #38A169);
    color: white;
    border-color: #48BB78;
}

.step.active .step-icon {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-color: #667eea;
    transform: scale(1.1);
}

.step-icon {
    width: 50px;
    height: 50px;
    background: white;
    border: 2px solid #E2E8F0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-weight: 700;
    transition: all 0.3s ease;
}

.step-label {
    font-size: 0.9rem;
    color: #64748B;
    font-weight: 500;
}

.step.active .step-label {
    color: #667eea;
    font-weight: 600;
}

/* Upgrade Card */
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
    background: linear-gradient(135deg, #667eea, #764ba2);
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
    font-size: 2.2rem;
    color: #1E293B;
    margin-bottom: 10px;
}

.card-header p {
    color: #64748B;
    font-size: 1.1rem;
}

/* Plan Comparison */
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
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.plan-card.current-plan {
    border-color: #94A3B8;
}

.plan-card.new-plan {
    border-color: #667eea;
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
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.plan-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea, #764ba2);
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
    text-align: center;
    font-size: 1.5rem;
    margin-bottom: 15px;
    color: #1E293B;
}

.plan-price {
    text-align: center;
    margin-bottom: 20px;
}

.plan-price small {
    font-size: 0.9rem;
    color: #64748B;
    margin-right: 5px;
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
}

.plan-features li {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
    color: #4A5568;
    font-size: 0.95rem;
}

.plan-features li i {
    color: #48BB78;
    font-size: 0.9rem;
}

.upgrade-arrow {
    display: flex;
    flex-direction: column;
    align-items: center;
    color: #667eea;
    font-size: 2rem;
}

.upgrade-arrow i:first-child {
    display: block;
}

.upgrade-arrow i:last-child {
    display: none;
}

/* Price Breakdown */
.price-breakdown {
    background: linear-gradient(135deg, #F8FAFC, #EDF2F7);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 40px;
}

.price-breakdown h3 {
    color: #1E293B;
    margin-bottom: 25px;
    font-size: 1.3rem;
}

.breakdown-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    color: #4A5568;
}

.breakdown-item.total {
    margin-top: 20px;
    font-size: 1.2rem;
    font-weight: 700;
}

.total-amount {
    color: #667eea;
    font-size: 1.5rem;
}

.text-success {
    color: #48BB78;
}

.breakdown-divider {
    height: 2px;
    background: linear-gradient(90deg, transparent, #CBD5E0, transparent);
    margin: 20px 0;
}

.savings-note {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #FEF3C7;
    border: 1px solid #F59E0B;
    border-radius: 12px;
    padding: 15px;
    margin-top: 20px;
    color: #92400E;
}

.savings-note i {
    font-size: 1.2rem;
}

/* Payment Form */
.payment-form h3 {
    color: #1E293B;
    margin-bottom: 25px;
    font-size: 1.3rem;
}

.payment-methods {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.payment-method {
    position: relative;
    cursor: pointer;
}

.payment-method input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.method-content {
    border: 2px solid #E2E8F0;
    border-radius: 16px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: all 0.3s ease;
}

.payment-method input[type="radio"]:checked + .method-content {
    border-color: #667eea;
    background: #F8FAFC;
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.1);
}

.method-content i {
    font-size: 1.8rem;
    color: #667eea;
}

.method-content strong {
    display: block;
    color: #1E293B;
    margin-bottom: 5px;
}

.method-content small {
    color: #64748B;
    font-size: 0.8rem;
}

/* Payment Details */
.payment-details {
    background: #F8FAFC;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 25px;
    animation: slideDown 0.3s ease;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #4A5568;
    font-weight: 500;
    font-size: 0.95rem;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.bank-info {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #E2E8F0;
}

.bank-info p {
    margin-bottom: 10px;
    color: #4A5568;
}

/* Terms */
.terms-section {
    margin: 30px 0;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    color: #4A5568;
}

.checkbox-label a {
    color: #667eea;
    text-decoration: none;
}

.checkbox-label a:hover {
    text-decoration: underline;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn-cancel,
.btn-pay {
    flex: 1;
    padding: 16px 30px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.1rem;
    text-align: center;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-cancel {
    background: #F1F5F9;
    color: #64748B;
}

.btn-cancel:hover {
    background: #E2E8F0;
}

.btn-pay {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.btn-pay:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
}

.btn-pay i {
    font-size: 1rem;
}

/* Animations */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
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
    
    .progress-steps::before {
        width: 90%;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .payment-methods {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .upgrade-card {
        padding: 25px;
    }
    
    .card-header h1 {
        font-size: 1.8rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .method-content {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script>
// Toggle payment details based on selected method
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Hide all payment details
        document.getElementById('mobileMoneyDetails').style.display = 'none';
        document.getElementById('cardDetails').style.display = 'none';
        document.getElementById('bankDetails').style.display = 'none';
        
        // Show selected payment details
        if (this.value === 'mobile_money') {
            document.getElementById('mobileMoneyDetails').style.display = 'block';
        } else if (this.value === 'card') {
            document.getElementById('cardDetails').style.display = 'block';
        } else if (this.value === 'bank_transfer') {
            document.getElementById('bankDetails').style.display = 'block';
        }
    });
});

// Format card number
document.getElementById('card_number')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, '');
    if (value.length > 0) {
        value = value.match(new RegExp('.{1,4}', 'g')).join(' ');
    }
    e.target.value = value;
});

// Format expiry date
document.getElementById('expiry_date')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\//g, '');
    if (value.length >= 2) {
        value = value.slice(0, 2) + '/' + value.slice(2);
    }
    e.target.value = value;
});

// Format phone number
document.getElementById('phone_number')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 0) {
        if (value.startsWith('0')) {
            value = value.slice(0, 4) + ' ' + value.slice(4, 7) + ' ' + value.slice(7, 10);
        }
    }
    e.target.value = value;
});

// Confirm before submit
document.querySelector('.payment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let amount = <?php echo $priceCalculation['upgrade_price']; ?>;
    
    if (confirm(`💰 Confirm Payment\n\nYou are about to pay UGX ${amount.toLocaleString()} for your upgrade.\n\nClick OK to proceed with payment.`)) {
        this.submit();
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>