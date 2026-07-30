<?php
// File: /views/external/subscription.php
$pageTitle = 'Subscription | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$subscriptionSettings = $subscriptionSettings ?? [];
$monthlyPrice = (float)($subscriptionSettings['monthly_price'] ?? 15000);
$termlyPrice  = (float)($subscriptionSettings['termly_price'] ?? 40000);
$yearlyPrice  = (float)($subscriptionSettings['yearly_price'] ?? 120000);

$monthlyTotal3  = $monthlyPrice * 3;
$monthlyTotal12 = $monthlyPrice * 12;

$termlySavings        = $monthlyTotal3 - $termlyPrice;
$yearlySavings        = $monthlyTotal12 - $yearlyPrice;
$termlySavingsPercent = $monthlyTotal3 > 0 ? round(($termlySavings / $monthlyTotal3) * 100) : 0;
$yearlySavingsPercent = $monthlyTotal12 > 0 ? round(($yearlySavings / $monthlyTotal12) * 100) : 0;
?>

<div class="subscription-container">
    <div class="subscription-header">
        <div class="badge">ROGELE</div>
        <h1 class="page-title">Choose Your Learning Path</h1>
        <p class="page-subtitle">Select the perfect plan for your educational journey</p>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($currentSubscription)): ?>
        <div class="active-subscription">
            <div class="active-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="active-content">
                <h3>You're on the <?= htmlspecialchars(ucfirst($currentSubscription['plan_type'])); ?> Plan!</h3>
                <p>Valid until <?= date('F j, Y', strtotime($currentSubscription['end_date'])); ?></p>
            </div>
            <?php
            $currentPlan = $currentSubscription['plan_type'];
            $nextPlan    = $currentPlan === 'monthly' ? 'termly' : ($currentPlan === 'termly' ? 'yearly' : null);
            if ($nextPlan):
            ?>
            <a href="<?= BASE_URL ?>/external/upgrade-confirmation?from=<?= $currentPlan ?>&to=<?= $nextPlan ?>" class="btn-upgrade">
                <i class="fas fa-rocket"></i> Upgrade to <?= ucfirst($nextPlan) ?>
            </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="pricing-grid">
        <!-- Monthly Plan -->
        <div class="pricing-card" data-plan="monthly" data-price="<?= $monthlyPrice ?>">
            <div class="plan-icon"><i class="fas fa-calendar-alt"></i></div>
            <h3 class="plan-name">Monthly</h3>
            <div class="price-wrapper">
                <span class="currency">UGX</span>
                <span class="amount"><?= number_format($monthlyPrice) ?></span>
            </div>
            <p class="period">per month • cancel anytime</p>
            <ul class="features-list">
                <li><i class="fas fa-check-circle"></i> Full access to all lessons</li>
                <li><i class="fas fa-check-circle"></i> Practice quizzes & assessments</li>
                <li><i class="fas fa-check-circle"></i> Homework support</li>
                <li><i class="fas fa-check-circle"></i> Progress tracking dashboard</li>
                <li><i class="fas fa-check-circle"></i> 24/7 support</li>
            </ul>
            <?php if (empty($currentSubscription)): ?>
            <button type="button" class="btn-select open-payment-modal" data-plan="monthly" data-price="<?= $monthlyPrice ?>">
                <i class="fas fa-shopping-cart"></i> Select Plan
            </button>
            <?php endif; ?>
        </div>

        <!-- Termly Plan -->
        <div class="pricing-card popular" data-plan="termly" data-price="<?= $termlyPrice ?>">
            <div class="popular-badge">RECOMMENDED</div>
            <div class="plan-icon"><i class="fas fa-chart-line"></i></div>
            <h3 class="plan-name">Termly</h3>
            <div class="price-wrapper">
                <span class="currency">UGX</span>
                <span class="amount"><?= number_format($termlyPrice) ?></span>
            </div>
            <p class="period">per term (3 months)</p>
            <div class="savings-tag">Save <?= number_format($termlySavings) ?> UGX (<?= $termlySavingsPercent ?>%)</div>
            <ul class="features-list">
                <li><i class="fas fa-check-circle"></i> Everything in Monthly</li>
                <li><i class="fas fa-check-circle"></i> Save <?= number_format($termlySavings) ?> UGX</li>
                <li><i class="fas fa-check-circle"></i> Priority support</li>
                <li><i class="fas fa-check-circle"></i> Downloadable materials</li>
                <li><i class="fas fa-check-circle"></i> Quiz solutions</li>
            </ul>
            <?php if (empty($currentSubscription)): ?>
            <button type="button" class="btn-select btn-primary open-payment-modal" data-plan="termly" data-price="<?= $termlyPrice ?>">
                <i class="fas fa-rocket"></i> Select Plan
            </button>
            <?php endif; ?>
        </div>

        <!-- Yearly Plan -->
        <div class="pricing-card" data-plan="yearly" data-price="<?= $yearlyPrice ?>">
            <div class="plan-icon"><i class="fas fa-crown"></i></div>
            <h3 class="plan-name">Yearly</h3>
            <div class="price-wrapper">
                <span class="currency">UGX</span>
                <span class="amount"><?= number_format($yearlyPrice) ?></span>
            </div>
            <p class="period">per year • best value</p>
            <div class="savings-tag best">Save <?= number_format($yearlySavings) ?> UGX (<?= $yearlySavingsPercent ?>%)</div>
            <ul class="features-list">
                <li><i class="fas fa-check-circle"></i> Everything in Termly</li>
                <li><i class="fas fa-check-circle"></i> 2 months free</li>
                <li><i class="fas fa-check-circle"></i> Full access to all resources</li>
                <li><i class="fas fa-check-circle"></i> ROGELE AI Assistant</li>
                <li><i class="fas fa-check-circle"></i> Certificate of completion</li>
            </ul>
            <?php if (empty($currentSubscription)): ?>
            <button type="button" class="btn-select open-payment-modal" data-plan="yearly" data-price="<?= $yearlyPrice ?>">
                <i class="fas fa-crown"></i> Select Plan
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment History -->
    <?php if (!empty($paymentHistory)): ?>
        <div class="history-section">
            <h2 class="section-title"><i class="fas fa-history"></i> Payment History</h2>
            <div class="table-responsive">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paymentHistory as $payment): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($payment['created_at'])) ?></td>
                            <td><strong><?= htmlspecialchars(ucfirst($payment['plan_type'] ?? 'N/A')) ?></strong></td>
                            <td>UGX <?= number_format($payment['amount']) ?></td>
                            <td>
                                <i class="fas fa-mobile-alt"></i>
                                <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $payment['payment_method'] ?? 'Mobile Money'))) ?>
                            </td>
                            <td>
                                <span class="status-badge <?= htmlspecialchars($payment['status']) ?>">
                                    <?= htmlspecialchars(ucfirst($payment['status'])) ?>
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

<!-- Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-credit-card"></i> Complete Payment</h3>
            <span class="close">&times;</span>
        </div>
        
        <form id="paymentForm" action="<?= BASE_URL ?>/external/process-mobile-money-payment" method="POST">
            <input type="hidden" name="plan_type" id="selectedPlan">
            
            <div class="plan-summary">
                <p>You're subscribing to: <strong id="planNameDisplay"></strong></p>
                <p class="amount-display">Total: <span id="planAmountDisplay"></span></p>
            </div>
            
            <div class="payment-fields">
                <div class="input-group">
                    <label for="phoneNumber">Mobile Money Phone Number</label>
                    <input type="tel" id="phoneNumber" name="phone_number" placeholder="0772 123 456" required>
                    <small>Enter your MTN or Airtel Mobile Money number</small>
                </div>
            </div>
            
            <div class="secure-badge">
                <i class="fas fa-shield-alt"></i>
                <span>Secured by ROGELE</span>
            </div>
            
            <div class="modal-buttons">
                <button type="button" class="btn-cancel">Cancel</button>
                <button type="submit" class="btn-submit">Pay Now</button>
            </div>
        </form>
    </div>
</div>

<style>
.subscription-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 48px 24px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
.subscription-header {
    text-align: center;
    margin-bottom: 60px;
}
.badge {
    display: inline-block;
    background-color: #7f2677;
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
    color: #7f2677;
    margin-bottom: 16px;
}
.page-subtitle {
    color: #555;
    font-size: 1.2rem;
    max-width: 600px;
    margin: 0 auto;
}
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
.active-content p { color: #047857; }
.btn-upgrade {
    background-color: #7f2677;
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
    box-shadow: 0 10px 25px rgba(127, 38, 119, 0.3);
}
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
    border-color: #f06724;
    background: linear-gradient(135deg, #FFFFFF, #FFFBEB);
}
.popular-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #7f2677;
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
.plan-icon i { font-size: 2rem; color: #7f2677; }
.plan-name { font-size: 1.8rem; font-weight: 800; color: #000; margin-bottom: 16px; }
.price-wrapper { margin-bottom: 8px; }
.currency { font-size: 0.95rem; color: #000; vertical-align: top; }
.amount { font-size: 3rem; font-weight: 800; color: #000; line-height: 1; }
.period { color: #555; font-size: 0.9rem; margin-bottom: 24px; }
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
.savings-tag.best { background: #f06724; }
.features-list { list-style: none; margin: 28px 0; padding: 0; }
.features-list li {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
    color: #555;
    font-size: 0.95rem;
}
.features-list li i { color: #10B981; font-size: 0.95rem; width: 20px; }
.btn-select {
    width: 100%;
    padding: 14px;
    background: #F1F5F9;
    border: none;
    border-radius: 60px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #7f2677;
}
.btn-select:hover { background: #f06724; color: white; transform: translateY(-2px); }
.btn-select.btn-primary { background-color: #7f2677; color: white; }
.history-section {
    background: white;
    border-radius: 28px;
    padding: 32px;
    box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.05);
}
.section-title {
    font-size: 1.5rem;
    color: #000;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.section-title i { color: #7f2677; }
.table-responsive { overflow-x: auto; }
.history-table { width: 100%; border-collapse: collapse; }
.history-table th {
    text-align: left;
    padding: 12px 16px;
    background: #F8FAFC;
    font-weight: 600;
    color: #000;
}
.history-table td { padding: 12px 16px; border-bottom: 1px solid #E2E8F0; color: #000; }
.status-badge { display: inline-block; padding: 4px 12px; border-radius: 30px; font-size: 0.75rem; font-weight: 600; }
.status-badge.completed, .status-badge.active { background: #F0FDF4; color: #166534; }
.status-badge.pending { background: #FEF3C7; color: #f06724; }
.status-badge.failed, .status-badge.expired { background: #FEF2F2; color: #B91C1C; }
.alert { padding: 16px 20px; border-radius: 16px; margin-bottom: 30px; display: flex; align-items: center; gap: 12px; }
.alert-success { background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; }
.alert-error { background: #FEF2F2; border: 1px solid #FECACA; color: #B91C1C; }
.modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
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
    background-color: #7f2677;
    padding: 20px 24px;
    border-radius: 28px 28px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
}
.modal-header h3 { margin: 0; display: flex; align-items: center; gap: 8px; }
.close { font-size: 28px; cursor: pointer; line-height: 1; }
.close:hover { transform: scale(1.1); }
#paymentForm { padding: 24px; }
.plan-summary { background: #F8FAFC; border-radius: 16px; padding: 16px; text-align: center; margin-bottom: 24px; }
.plan-summary strong { color: #7f2677; font-size: 0.95rem; }
.amount-display { margin-top: 8px; font-size: 1.2rem; }
.amount-display span { font-weight: 700; color: #7f2677; }
.payment-fields { background: #F8FAFC; border-radius: 16px; padding: 20px; margin-bottom: 20px; }
.input-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #000; font-size: 0.9rem; }
.input-group input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    font-size: 0.95rem;
}
.input-group input:focus { outline: none; border-color: #f06724; box-shadow: 0 0 0 2px rgba(240, 103, 36, 0.25); }
.input-group small { display: block; margin-top: 6px; color: #666; font-size: 0.75rem; }
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
.modal-buttons { display: flex; gap: 12px; }
.btn-cancel, .btn-submit {
    flex: 1;
    padding: 14px;
    border-radius: 50px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}
.btn-cancel { background: #F1F5F9; color: #1E293B; }
.btn-cancel:hover { background: #E2E8F0; }
.btn-submit { background-color: #7f2677; color: white; }
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(127, 38, 119, 0.3); }

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}
@media (max-width: 768px) {
    .subscription-container { padding: 24px 16px; }
    .page-title { font-size: 2rem; }
    .pricing-grid { grid-template-columns: 1fr; gap: 24px; }
    .active-subscription { flex-direction: column; text-align: center; }
}
</style>

<script>
const modal = document.getElementById('paymentModal');
const modalClose = document.querySelector('.close');
const cancelBtn = document.querySelector('.btn-cancel');
const paymentForm = document.getElementById('paymentForm');

function openModal(plan, price) {
    document.getElementById('selectedPlan').value = plan;
    document.getElementById('planNameDisplay').textContent = plan.charAt(0).toUpperCase() + plan.slice(1) + ' Plan';
    document.getElementById('planAmountDisplay').textContent = 'UGX ' + parseInt(price).toLocaleString();
    modal.style.display = 'flex';
}

function closeModal() {
    modal.style.display = 'none';
    paymentForm.reset();
}

document.querySelectorAll('.open-payment-modal').forEach(button => {
    button.addEventListener('click', function() {
        openModal(this.dataset.plan, this.dataset.price);
    });
});

if (modalClose) modalClose.addEventListener('click', closeModal);
if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

window.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
});

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

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const txId = urlParams.get('tx_id');

    if (txId) {
        let attempts = 0;
        const maxAttempts = 20;

        const pollInterval = setInterval(() => {
            attempts++;
            fetch('<?= BASE_URL ?>/external/check-payment-status?transaction_id=' + txId)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'completed') {
                        clearInterval(pollInterval);
                        alert('Payment confirmed! Your subscription is now active.');
                        window.location.href = '<?= BASE_URL ?>/external/subscription?status=success';
                    } else if (data.status === 'failed') {
                        clearInterval(pollInterval);
                        alert('Payment failed or was cancelled. Please try again.');
                    }
                })
                .catch(err => console.error('Error polling status:', err));

            if (attempts >= maxAttempts) {
                clearInterval(pollInterval);
            }
        }, 3000);
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>