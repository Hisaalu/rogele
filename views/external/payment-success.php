<?php
$pageTitle = 'Payment Successful | ROGELE';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="success-container">
    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1>Payment Successful! 🎉</h1>
        <p class="success-message">Your subscription has been activated.</p>
        
        <div class="payment-summary">
            <h3>Payment Summary</h3>
            
            <div class="summary-item">
                <span class="summary-label">Plan:</span>
                <span class="summary-value"><?php echo ucfirst($planType ?? 'Premium'); ?></span>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">Amount Paid:</span>
                <span class="summary-value amount">UGX <?php echo number_format($amount ?? 0); ?></span>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="<?php echo BASE_URL; ?>/external/dashboard" class="btn-primary">
                <i class="fas fa-tachometer-alt"></i>
                Go to Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>/external/materials" class="btn-secondary">
                <i class="fas fa-book-open"></i>
                Start Learning
            </a>
        </div>
    </div>
</div>

<style>
.success-container {
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.success-card {
    background: white;
    border-radius: 30px;
    padding: 50px;
    max-width: 500px;
    width: 100%;
    text-align: center;
    box-shadow: 0 30px 70px rgba(0, 0, 0, 0.2);
}

.success-icon {
    width: 80px;
    height: 80px;
    background: #10B981;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
}

.success-icon i {
    font-size: 40px;
    color: white;
}

.success-card h1 {
    font-size: 2rem;
    color: #1E293B;
    margin-bottom: 10px;
}

.success-message {
    color: #64748B;
    margin-bottom: 30px;
}

.payment-summary {
    background: #F8FAFC;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 30px;
    text-align: left;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #E2E8F0;
}

.summary-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.summary-label {
    color: #64748B;
}

.summary-value {
    font-weight: 700;
    color: #1E293B;
}

.summary-value.amount {
    color: #10B981;
    font-size: 1.2rem;
}

.action-buttons {
    display: flex;
    gap: 15px;
}

.btn-primary, .btn-secondary {
    flex: 1;
    padding: 12px 20px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    text-align: center;
}

.btn-primary {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
}

.btn-secondary {
    background: #F1F5F9;
    color: #1E293B;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>