<?php
// File: /views/external/trial_status.php
$pageTitle = 'Trial Status | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

// Get trial information from database
try {
    $conn = new PDO("mysql:host=localhost;dbname=rays_of_grace_elearning", "root", "");
    $stmt = $conn->prepare("SELECT * FROM free_trials WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $trial = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $trial = null;
}
?>

<div style="padding: 40px 20px; max-width: 800px; margin: 0 auto;">
    <div style="background: white; border-radius: 30px; padding: 40px; box-shadow: 0 20px 50px rgba(0,0,0,0.1);">
        <h1 style="font-size: 2rem; margin-bottom: 20px; background: linear-gradient(135deg, #8B5CF6, #f06724); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-align: center;">
            Free Trial Status
        </h1>
        
        <?php if ($trial): ?>
            <?php
            $startDate = new DateTime($trial['start_date']);
            $endDate = new DateTime($trial['end_date']);
            $now = new DateTime();
            $daysLeft = $now->diff($endDate)->days;
            $totalDays = $startDate->diff($endDate)->days;
            $percentageUsed = (($totalDays - $daysLeft) / $totalDays) * 100;
            ?>
            
            <div style="text-align: center; margin-bottom: 40px;">
                <div style="width: 150px; height: 150px; margin: 0 auto 20px; background: linear-gradient(135deg, #8B5CF6, #f06724); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-gift" style="font-size: 4rem; color: white;"></i>
                </div>
                <h2 style="color: black; margin-bottom: 10px;">You're on Free Trial</h2>
                <p style="color: black;">Enjoying full access to all features</p>
            </div>
            
            <!-- Progress Bar -->
            <div style="margin-bottom: 30px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: black;">
                    <span>Trial started: <?php echo $startDate->format('M d, Y'); ?></span>
                    <span>Trial ends: <?php echo $endDate->format('M d, Y'); ?></span>
                </div>
                <div style="width: 100%; height: 20px; background: #E2E8F0; border-radius: 10px; overflow: hidden;">
                    <div style="width: <?php echo $percentageUsed; ?>%; height: 100%; background: linear-gradient(90deg, #8B5CF6, #f06724);"></div>
                </div>
                <div style="text-align: center; margin-top: 10px;">
                    <span style="font-size: 1.5rem; font-weight: 700; color: #8B5CF6;"><?php echo $daysLeft; ?></span>
                    <span style="color: black;"> days remaining</span>
                </div>
            </div>
            
            <?php if ($daysLeft <= 7): ?>
                <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 15px; padding: 20px; margin-bottom: 30px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <i class="fas fa-exclamation-triangle" style="color: #f06724; font-size: 2rem;"></i>
                        <div>
                            <h3 style="color: #e21414; margin-bottom: 5px;">Your trial is ending soon!</h3>
                            <p style="color: black;">Subscribe now to continue uninterrupted access to all learning materials.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Call to Action -->
            <div style="text-align: center;">
                <a href="<?php echo BASE_URL; ?>/external/subscription" style="display: inline-block; background: linear-gradient(135deg, #8B5CF6, #f06724); color: white; text-decoration: none; padding: 15px 40px; border-radius: 50px; font-weight: 600; font-size: 1.1rem;">
                    Choose a Subscription Plan
                </a>
            </div>
            
        <?php else: ?>
            <div style="text-align: center;">
                <i class="fas fa-frown" style="font-size: 4rem; color: #CBD5E1; margin-bottom: 20px;"></i>
                <h3 style="color: black; margin-bottom: 10px;">No Active Trial</h3>
                <p style="color: black; margin-bottom: 30px;">You don't have an active free trial.</p>
                <a href="<?php echo BASE_URL; ?>/external/subscription" style="display: inline-block; background: linear-gradient(135deg, #8B5CF6, #f06724); color: white; text-decoration: none; padding: 15px 40px; border-radius: 50px; font-weight: 600;">
                    View Subscription Plans
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>