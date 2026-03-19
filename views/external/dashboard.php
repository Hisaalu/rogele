<?php
// File: /views/external/dashboard.php
$pageTitle = 'External Dashboard - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

// Check if user has an active subscription
// You need to pass these variables from your controller
$hasActiveSubscription = $hasActiveSubscription ?? false;
$trialDays = $trialDays ?? 60; // Default trial days from settings
$remainingTrialDays = $remainingTrialDays ?? $trialDays; // Calculate remaining days
$trialEndDate = $trialEndDate ?? null; // Trial end date if available
$trialPercentage = $trialPercentage ?? 100; // Percentage of trial used
?>

<div style="padding: 40px 20px; max-width: 1200px; margin: 0 auto;">
    <h1 style="font-size: 2rem; margin-bottom: 20px; background: linear-gradient(135deg, #8B5CF6, #F97316); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        Welcome, <?php 
            $fullName = $_SESSION['user_name'] ?? 'User';
            $firstName = explode(' ', trim($fullName))[0];
            echo htmlspecialchars($firstName); 
        ?>! 👋
    </h1>
    
    <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
        <h2 style="color: #1E293B; margin-bottom: 20px;">External User Dashboard</h2>
        
        <!-- Conditional Trial Message - Only shows if user DOES NOT have active subscription -->
        <?php if (!$hasActiveSubscription): ?>
        <div style="background: linear-gradient(135deg, #FEF3C7, #FFFAF0); border-left: 4px solid #F59E0B; padding: 15px 20px; margin-bottom: 30px; border-radius: 10px; animation: slideIn 0.5s ease;">
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <div style="background: #F59E0B; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-hourglass-half" style="color: white; font-size: 1.5rem;"></i>
                </div>
                
                <div style="flex: 1;">
                    <p style="color: #92400E; font-weight: 700; font-size: 1.1rem; margin-bottom: 5px;">
                        ⏳ Trial Period: <strong><?php echo $remainingTrialDays; ?> days remaining</strong>
                    </p>
                    <p style="color: #B45309; font-size: 0.95rem; margin-bottom: 10px;">
                        <?php if ($remainingTrialDays > 0): ?>
                            You have <?php echo $remainingTrialDays; ?> days left to explore all features.
                            <?php if ($trialEndDate): ?>
                                Your trial ends on <strong><?php echo date('F j, Y', strtotime($trialEndDate)); ?></strong>.
                            <?php endif; ?>
                        <?php else: ?>
                            Your trial has ended. Subscribe now to continue accessing all features!
                        <?php endif; ?>
                    </p>
                    
                    <!-- Progress bar showing trial usage -->
                    <div style="background: #FFEDD5; height: 8px; border-radius: 10px; margin-top: 10px; max-width: 400px;">
                        <div style="background: linear-gradient(90deg, #F59E0B, #F97316); width: <?php echo $trialPercentage; ?>%; height: 100%; border-radius: 10px;"></div>
                    </div>
                </div>
                
                <a href="<?php echo BASE_URL; ?>/external/subscription" 
                   style="margin-left: auto; background: linear-gradient(135deg, #F59E0B, #F97316); color: white; padding: 10px 25px; border-radius: 50px; text-decoration: none; font-size: 0.95rem; font-weight: 600; transition: all 0.3s ease; white-space: nowrap; box-shadow: 0 4px 6px rgba(245, 158, 11, 0.3);">
                    <i class="fas fa-rocket"></i> Subscribe Now
                </a>
            </div>
            
            <!-- Show urgency message if trial is almost over -->
            <?php if ($remainingTrialDays <= 7 && $remainingTrialDays > 0): ?>
            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px dashed #FCD34D;">
                <p style="color: #B45309; font-size: 0.9rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>⚠️ Your trial ends in <strong><?php echo $remainingTrialDays; ?> days</strong>. Subscribe now to avoid interruption!</span>
                </p>
            </div>
            <?php endif; ?>
        </div>
        
        <?php else: ?>
        <!-- Show active subscription message -->
        <div style="background: linear-gradient(135deg, #F0FDF4, #FFFFFF); border-left: 4px solid #10B981; padding: 15px 20px; margin-bottom: 30px; border-radius: 10px; animation: slideIn 0.5s ease;">
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <div style="background: #10B981; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-crown" style="color: white; font-size: 1.5rem;"></i>
                </div>
                
                <div style="flex: 1;">
                    <p style="color: #065F46; font-weight: 700; font-size: 1.1rem; margin-bottom: 5px;">
                        🌟 Active <?php echo ucfirst($currentPlan ?? 'Subscription'); ?> Plan
                    </p>
                    <p style="color: #047857; font-size: 0.95rem;">
                        You have full access to all premium features.
                        <?php if (isset($subscriptionEndDate)): ?>
                            Your plan renews on <strong><?php echo date('F j, Y', strtotime($subscriptionEndDate)); ?></strong>.
                        <?php endif; ?>
                    </p>
                </div>
                
                <a href="<?php echo BASE_URL; ?>/external/subscription" 
                   style="margin-left: auto; background: #10B981; color: white; padding: 10px 25px; border-radius: 50px; text-decoration: none; font-size: 0.95rem; font-weight: 600; transition: all 0.3s ease; white-space: nowrap;">
                    <i class="fas fa-cog"></i> Manage Plan
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <a href="<?php echo BASE_URL; ?>/external/materials" style="background: linear-gradient(135deg, #8B5CF6, #F97316); color: white; padding: 30px; border-radius: 15px; text-decoration: none; text-align: center; transition: transform 0.3s ease;">
                <i class="fas fa-book-open" style="font-size: 2rem; margin-bottom: 15px;"></i>
                <h3 style="margin-bottom: 10px;">Learning Materials</h3>
                <p style="opacity: 0.9; font-size: 0.9rem;">Access all lessons and resources</p>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/external/quizzes" style="background: white; color: #1E293B; padding: 30px; border-radius: 15px; text-decoration: none; text-align: center; border: 2px solid #E2E8F0; transition: all 0.3s ease;">
                <i class="fas fa-pencil-alt" style="font-size: 2rem; margin-bottom: 15px; color: #F97316;"></i>
                <h3 style="margin-bottom: 10px;">Practice Quizzes</h3>
                <p style="color: #64748B; font-size: 0.9rem;">Test your knowledge</p>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/external/subscription" style="background: white; color: #1E293B; padding: 30px; border-radius: 15px; text-decoration: none; text-align: center; border: 2px solid #E2E8F0; transition: all 0.3s ease;">
                <i class="fas fa-credit-card" style="font-size: 2rem; margin-bottom: 15px; color: #8B5CF6;"></i>
                <h3 style="margin-bottom: 10px;">Subscription</h3>
                <p style="color: #64748B; font-size: 0.9rem;">Manage your subscription</p>
            </a>
        </div>
    </div>
</div>

<style>
    a:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(139, 92, 246, 0.2);
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Pulse animation for urgency message */
    @keyframes pulse {
        0% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
        100% {
            opacity: 1;
        }
    }
    
    .trial-banner a:hover {
        transform: translateX(5px);
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>