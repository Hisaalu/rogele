<?php
// File: /views/external/dashboard.php
$pageTitle = 'Dashboard | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

// Use variables passed from controller instead of calling model directly
$trialDays = $trialDays ?? 60;
$remainingTrialDays = $remainingTrialDays ?? 0;
$isInTrial = $isInTrial ?? false;
$hasActiveSubscription = $hasActiveSubscription ?? false;
$trialEndDate = $trialEndDate ?? null;
$trialPercentage = $trialPercentage ?? 0;
$currentPlan = $currentPlan ?? null;
$subscriptionEndDate = $subscriptionEndDate ?? null;

// Calculate access
$hasAccess = $hasActiveSubscription || $isInTrial;
?>

<div style="padding: 40px 20px; max-width: 1200px; margin: 0 auto;">
    <h1 style="font-size: 2rem; margin-bottom: 20px;">
        <span style="background: linear-gradient(135deg, #f06724); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Welcome back, <?php 
                $fullName = $_SESSION['user_name'] ?? 'User';
                $firstName = explode(' ', trim($fullName))[0];
                echo htmlspecialchars($firstName); 
            ?>!
        </span>
        <span>👋</span>
    </h1>
    
    <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
        <h2 style="color: black; margin-bottom: 20px;">External User Dashboard</h2>
        
        <!-- Access Status Banner -->
        <?php if ($hasActiveSubscription): ?>
            <!-- Active Subscription Banner -->
            <div style="background: linear-gradient(135deg, #F0FDF4, #FFFFFF); border-left: 4px solid #10B981; padding: 20px; margin-bottom: 30px; border-radius: 12px; display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <div style="background: #10B981; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-crown" style="color: white; font-size: 1.5rem;"></i>
                </div>
                <div style="flex: 1;">
                    <p style="color: #065F46; font-weight: 700; margin-bottom: 5px;">🌟 Active Subscription</p>
                    <p style="color: #047857;">You have full access to all premium features.</p>
                    <?php if ($subscriptionEndDate): ?>
                        <p style="color: #047857; font-size: 0.85rem;">Valid until: <?php echo date('F j, Y', strtotime($subscriptionEndDate)); ?></p>
                    <?php endif; ?>
                </div>
                <a href="<?php echo BASE_URL; ?>/external/subscription" style="background: #10B981; color: white; padding: 10px 24px; border-radius: 50px; text-decoration: none; font-weight: 600;">
                    Manage Plan
                </a>
            </div>
            
        <?php elseif ($isInTrial): ?>
            <!-- Active Trial Banner -->
            <div style="background: linear-gradient(135deg, #FEF3C7, #FFFAF0); border-left: 4px solid #F59E0B; padding: 20px; margin-bottom: 30px; border-radius: 12px;">
                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <div style="background: #F59E0B; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-hourglass-half" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <div style="flex: 1;">
                        <p style="color: #92400E; font-weight: 700; font-size: 1.1rem; margin-bottom: 5px;">
                            ⏳ Trial Period: <strong><?php echo $remainingTrialDays; ?> days remaining</strong>
                        </p>
                        <p style="color: #B45309; font-size: 0.95rem;">
                            You have <?php echo $remainingTrialDays; ?> days left to explore all features.
                            <?php if ($trialEndDate): ?>
                                Your trial ends on <strong><?php echo date('F j, Y', strtotime($trialEndDate)); ?></strong>.
                            <?php endif; ?>
                        </p>
                        <!-- Progress Bar -->
                        <?php 
                        $usedDays = $trialDays - $remainingTrialDays;
                        ?>
                        <div style="background: #FFEDD5; height: 8px; border-radius: 10px; margin-top: 10px; max-width: 400px;">
                            <div style="background: linear-gradient(90deg, #f06724); width: <?php echo $trialPercentage; ?>%; height: 100%; border-radius: 10px;"></div>
                        </div>
                        <p style="color: #B45309; font-size: 0.75rem; margin-top: 5px;">Day <?php echo $usedDays; ?> of <?php echo $trialDays; ?></p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/external/subscription" style="background: linear-gradient(135deg, #f06724); color: white; padding: 10px 24px; border-radius: 50px; text-decoration: none; font-weight: 600;">
                        Subscribe Now
                    </a>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Trial Ended Banner -->
            <div style="background: linear-gradient(135deg, #FEF2F2, #FFFFFF); border-left: 4px solid #EF4444; padding: 20px; margin-bottom: 30px; border-radius: 12px;">
                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <div style="background: #EF4444; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-clock" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <div style="flex: 1;">
                        <p style="color: #B91C1C; font-weight: 700; margin-bottom: 5px;">⚠️ Trial Expired</p>
                        <p style="color: #B91C1C;">Your free trial has ended. Subscribe now to continue accessing lessons and quizzes!</p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/external/subscription" style="background: linear-gradient(135deg, #EF4444, #DC2626); color: white; padding: 10px 24px; border-radius: 50px; text-decoration: none; font-weight: 600;">
                        Subscribe Now
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Feature Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <!-- Learning Materials Card -->
            <a href="<?php echo BASE_URL; ?>/external/materials" style="background: <?php echo $hasAccess ? 'linear-gradient(135deg, #7f2677)' : '#E2E8F0'; ?>; color: <?php echo $hasAccess ? 'white' : '#64748B'; ?>; padding: 30px; border-radius: 15px; text-decoration: none; text-align: center; transition: transform 0.3s ease; <?php echo !$hasAccess ? 'cursor: not-allowed;' : ''; ?>">
                <i class="fas fa-book-open" style="font-size: 2rem; margin-bottom: 15px; color: <?php echo $hasAccess ? '#f06724' : '#94A3B8'; ?>;"></i>
                <h3 style="margin-bottom: 10px;">Learning Materials</h3>
                <p style="opacity: 0.9; font-size: 0.9rem;">
                    <?php echo $hasAccess ? 'Access all lessons and resources' : 'Subscribe to access lessons'; ?>
                </p>
                <?php if (!$hasAccess): ?>
                    <div style="margin-top: 10px; font-size: 0.8rem;">🔒 Locked</div>
                <?php endif; ?>
            </a>
            
            <!-- Practice Quizzes Card -->
            <a href="<?php echo BASE_URL; ?>/external/quizzes" style="background: <?php echo $hasAccess ? 'linear-gradient(135deg, #7f2677)' : '#E2E8F0'; ?>; color: <?php echo $hasAccess ? 'white' : '#64748B'; ?>; padding: 30px; border-radius: 15px; text-decoration: none; text-align: center; transition: transform 0.3s ease; <?php echo !$hasAccess ? 'cursor: not-allowed;' : ''; ?>">
                <i class="fas fa-pencil-alt" style="font-size: 2rem; margin-bottom: 15px; color: <?php echo $hasAccess ? '#f06724' : '#94A3B8'; ?>;"></i>
                <h3 style="margin-bottom: 10px;">Practice Quizzes</h3>
                <p style="opacity: 0.9; font-size: 0.9rem;">
                    <?php echo $hasAccess ? 'Test your knowledge' : 'Subscribe to access quizzes'; ?>
                </p>
                <?php if (!$hasAccess): ?>
                    <div style="margin-top: 10px; font-size: 0.8rem;">🔒 Locked</div>
                <?php endif; ?>
            </a>
            
            <!-- Subscription Card (Always accessible) -->
            <a href="<?php echo BASE_URL; ?>/external/subscription" style="background: white; color: #1E293B; padding: 30px; border-radius: 15px; text-decoration: none; text-align: center; border: 2px solid #E2E8F0; transition: transform 0.3s ease;">
                <i class="fas fa-credit-card" style="font-size: 2rem; margin-bottom: 15px; color: #f06724;"></i>
                <h3 style="margin-bottom: 10px;">Subscription</h3>
                <p style="color: black; font-size: 0.9rem;">
                    <?php echo $hasActiveSubscription ? 'Manage your subscription' : ($isInTrial ? 'Upgrade to premium' : 'Subscribe to continue'); ?>
                </p>
            </a>
        </div>
        
        <!-- Trial Ended Additional Info -->
        <?php if (!$hasAccess && !$hasActiveSubscription): ?>
        <div style="margin-top: 30px; padding: 20px; background: #FEF2F2; border-radius: 12px; text-align: center;">
            <p style="color: #B91C1C; margin-bottom: 15px;">
                <i class="fas fa-exclamation-triangle"></i> 
                Your free trial has ended. To continue learning, please choose a subscription plan.
            </p>
            <a href="<?php echo BASE_URL; ?>/external/subscription" class="btn-subscribe" style="background: linear-gradient(135deg, #EF4444, #DC2626); color: white; padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: 600; display: inline-block;">
                View Subscription Plans
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
    a:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(139, 92, 246, 0.2);
    }
    
    .btn-subscribe:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3);
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>