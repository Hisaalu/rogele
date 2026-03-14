<?php
// File: /views/external/purchase.php
$pageTitle = 'Purchase Subscription - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

$plan = $_GET['plan'] ?? 'monthly';
$planPrices = [
    'monthly' => 15000,
    'termly' => 40000,
    'yearly' => 120000
];
$planNames = [
    'monthly' => 'Monthly',
    'termly' => 'Termly',
    'yearly' => 'Yearly'
];
$selectedPrice = $planPrices[$plan] ?? 15000;
$selectedName = $planNames[$plan] ?? 'Monthly';
?>

<div style="padding: 40px 20px; max-width: 800px; margin: 0 auto;">
    <h1 style="font-size: 2rem; margin-bottom: 10px; background: linear-gradient(135deg, #8B5CF6, #F97316); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        Complete Your Purchase
    </h1>
    <p style="color: #64748B; margin-bottom: 40px;">You're about to purchase the <?php echo $selectedName; ?> plan</p>
    
    <div style="background: white; border-radius: 20px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
        <!-- Order Summary -->
        <div style="background: #F8FAFC; border-radius: 15px; padding: 20px; margin-bottom: 30px;">
            <h3 style="color: #1E293B; margin-bottom: 15px;">Order Summary</h3>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span style="color: #64748B;"><?php echo $selectedName; ?> Plan</span>
                <span style="font-weight: 600;">UGX <?php echo number_format($selectedPrice); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span style="color: #64748B;">Tax</span>
                <span style="font-weight: 600;">UGX 0</span>
            </div>
            <div style="border-top: 2px dashed #E2E8F0; margin: 15px 0; padding-top: 15px;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="font-weight: 700; color: #1E293B;">Total</span>
                    <span style="font-size: 1.5rem; font-weight: 700; color: #8B5CF6;">UGX <?php echo number_format($selectedPrice); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Payment Form -->
        <form method="POST" action="/rays-of-grace/external/process-payment">
            <input type="hidden" name="plan" value="<?php echo $plan; ?>">
            
            <div style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #1E293B;">
                    <i class="fas fa-mobile-alt" style="color: #8B5CF6; margin-right: 8px;"></i>
                    Mobile Money Number
                </label>
                <input type="tel" name="phone_number" required placeholder="e.g., 0772XXXXXX" 
                       style="width: 100%; padding: 15px; border: 2px solid #E2E8F0; border-radius: 12px; font-size: 1rem;">
                <p style="color: #64748B; font-size: 0.8rem; margin-top: 5px;">Enter your MTN or Airtel mobile money number</p>
            </div>
            
            <div style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #1E293B;">
                    <i class="fas fa-credit-card" style="color: #8B5CF6; margin-right: 8px;"></i>
                    Payment Method
                </label>
                <select name="payment_method" required style="width: 100%; padding: 15px; border: 2px solid #E2E8F0; border-radius: 12px; font-size: 1rem;">
                    <option value="mtn">MTN Mobile Money</option>
                    <option value="airtel">Airtel Money</option>
                </select>
            </div>
            
            <div style="background: #FEF2F2; border-radius: 10px; padding: 15px; margin-bottom: 25px; border: 1px solid #FECACA;">
                <p style="color: #B91C1C; font-size: 0.9rem;">
                    <i class="fas fa-info-circle"></i>
                    By completing this purchase, you agree to our Terms of Service and will be charged immediately.
                </p>
            </div>
            
            <button type="submit" style="width: 100%; background: linear-gradient(135deg, #8B5CF6, #F97316); color: white; border: none; padding: 18px; border-radius: 50px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                <i class="fas fa-lock"></i>
                Pay UGX <?php echo number_format($selectedPrice); ?>
            </button>
            
            <p style="text-align: center; margin-top: 20px; color: #64748B; font-size: 0.9rem;">
                <i class="fas fa-shield-alt" style="color: #8B5CF6;"></i>
                Your payment information is secure and encrypted
            </p>
        </form>
    </div>
</div>

<style>
    button:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>