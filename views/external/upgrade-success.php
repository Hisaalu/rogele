<?php
// File: /views/external/upgrade-success.php
$pageTitle = 'Upgrade Successful | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$toPlan = $toPlan ?? '';
$upgradePrice = $upgradePrice ?? 0;
$newEndDate = $newEndDate ?? date('Y-m-d H:i:s');
?>

<div style="min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div style="background: white; border-radius: 30px; padding: 50px; max-width: 500px; width: 100%; text-align: center; box-shadow: 0 30px 70px rgba(0, 0, 0, 0.2);">
        <div style="width: 100px; height: 100px; background: #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;">
            <i class="fas fa-check-circle" style="font-size: 60px; color: white;"></i>
        </div>
        
        <h1 style="font-size: 2rem; color: black; margin-bottom: 10px;">Upgrade Successful! 🎉</h1>
        <p style="color: black; margin-bottom: 30px;">Your subscription has been successfully upgraded.</p>
        
        <div style="background: #F8FAFC; border-radius: 16px; padding: 25px; margin-bottom: 30px; text-align: left;">
            <h3 style="color: black; margin-bottom: 15px;">Upgrade Summary</h3>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span>New Plan:</span>
                <strong><?php echo ucfirst($toPlan); ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span>Amount Paid:</span>
                <strong>UGX <?php echo number_format($upgradePrice); ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Valid Until:</span>
                <strong><?php echo date('F j, Y', strtotime($newEndDate)); ?></strong>
            </div>
        </div>
        
        <div style="display: flex; gap: 15px;">
            <a href="<?php echo BASE_URL; ?>/external/dashboard" style="flex: 1; padding: 14px; background: linear-gradient(135deg, #7f2677); color: white; text-decoration: none; border-radius: 12px; font-weight: 600;">
                Go to Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>/external/materials" style="flex: 1; padding: 14px; background: #F1F5F9; color: black; text-decoration: none; border-radius: 12px; font-weight: 600;">
                Start Learning
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>