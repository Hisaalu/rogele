<?php
// File: /views/external/upgrade-success.php
$pageTitle = 'Upgrade Successful - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

// Set default values if variables are not passed
$toPlan = $toPlan ?? '';
$priceCalculation = $priceCalculation ?? ['upgrade_price' => 0];
$upgradePrice = $priceCalculation['upgrade_price'] ?? 0;
$newEndDate = $newEndDate ?? date('Y-m-d H:i:s', strtotime('+1 year'));
$subscriptionId = $_GET['subscription_id'] ?? 0;
?>

<div class="success-container">
    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1>Upgrade Successful! 🎉</h1>
        <p class="success-message">Your subscription has been successfully upgraded.</p>
        
        <div class="upgrade-summary">
            <h3>Upgrade Summary</h3>
            
            <div class="summary-item">
                <span class="summary-label">New Plan:</span>
                <span class="summary-value"><?php echo ucfirst($toPlan ?: 'Premium'); ?></span>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">Amount Paid:</span>
                <span class="summary-value amount">UGX <?php echo number_format($upgradePrice); ?></span>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">Valid Until:</span>
                <span class="summary-value"><?php echo date('F j, Y', strtotime($newEndDate)); ?></span>
            </div>
        </div>
        
        <div class="benefits-note">
            <i class="fas fa-gift"></i>
            <div>
                <strong>You now have access to all premium features!</strong>
                <p>Check out your new learning materials and start exploring.</p>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="<?php echo BASE_URL; ?>/external/dashboard" class="btn-primary">
                <i class="fas fa-tachometer-alt"></i>
                Go to Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>/external/lessons" class="btn-secondary">
                <i class="fas fa-book-open"></i>
                Start Learning
            </a>
        </div>
        
        <div class="email-note">
            <i class="fas fa-envelope"></i>
            <span>A confirmation email has been sent to your inbox.</span>
        </div>
    </div>
</div>

<!-- Add this CSS if it's not already in your file -->
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
    max-width: 600px;
    width: 100%;
    text-align: center;
    box-shadow: 0 30px 70px rgba(0, 0, 0, 0.2);
    animation: slideUp 0.8s ease;
}

.success-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #48BB78, #38A169);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px;
    animation: scaleIn 0.5s ease 0.3s both;
}

.success-icon i {
    font-size: 60px;
    color: white;
}

.success-card h1 {
    font-size: 2.5rem;
    color: #1E293B;
    margin-bottom: 10px;
    animation: fadeInUp 0.5s ease 0.5s both;
}

.success-message {
    color: #64748B;
    font-size: 1.2rem;
    margin-bottom: 40px;
    animation: fadeInUp 0.5s ease 0.6s both;
}

.upgrade-summary {
    background: #F8FAFC;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    text-align: left;
    animation: fadeInUp 0.5s ease 0.7s both;
}

.upgrade-summary h3 {
    color: #1E293B;
    margin-bottom: 20px;
    font-size: 1.3rem;
    text-align: center;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #E2E8F0;
}

.summary-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
    margin-bottom: 0;
}

.summary-label {
    color: #64748B;
    font-weight: 500;
}

.summary-value {
    font-weight: 700;
    color: #1E293B;
}

.summary-value.amount {
    color: #667eea;
    font-size: 1.2rem;
}

.benefits-note {
    background: linear-gradient(135deg, #F0FDF4, #FFFFFF);
    border: 2px solid #48BB78;
    border-radius: 16px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 30px;
    text-align: left;
    animation: fadeInUp 0.5s ease 0.8s both;
}

.benefits-note i {
    font-size: 2rem;
    color: #48BB78;
}

.benefits-note strong {
    display: block;
    color: #22543D;
    margin-bottom: 5px;
}

.benefits-note p {
    color: #2F855A;
    font-size: 0.95rem;
    margin: 0;
}

.action-buttons {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
    animation: fadeInUp 0.5s ease 0.9s both;
}

.btn-primary,
.btn-secondary {
    flex: 1;
    padding: 16px 30px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    text-align: center;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #F1F5F9;
    color: #1E293B;
}

.btn-secondary:hover {
    background: #E2E8F0;
    transform: translateY(-3px);
}

.email-note {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    color: #64748B;
    font-size: 0.95rem;
    animation: fadeInUp 0.5s ease 1s both;
}

.email-note i {
    color: #667eea;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .success-card {
        padding: 30px 20px;
    }
    
    .success-card h1 {
        font-size: 2rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .benefits-note {
        flex-direction: column;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .summary-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>