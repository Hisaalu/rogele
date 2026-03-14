<?php
// File: /views/external/subscription.php
$pageTitle = 'Subscription - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';
?>

<div style="padding: 40px 20px; max-width: 1200px; margin: 0 auto;">
    <h1 style="font-size: 2rem; margin-bottom: 10px; background: linear-gradient(135deg, #8B5CF6, #F97316); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        Subscription Management
    </h1>
    <p style="color: #64748B; margin-bottom: 40px;">Manage your subscription plan and payment methods</p>
    
    <?php if ($currentSubscription): ?>
        <!-- Active Subscription Info -->
        <div style="background: white; border-radius: 20px; padding: 30px; margin-bottom: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border-left: 4px solid #8B5CF6;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                <div>
                    <h3 style="color: #1E293B; margin-bottom: 10px;">Current Plan: <span style="color: #8B5CF6; text-transform: capitalize;"><?php echo $currentSubscription['plan_type']; ?></span></h3>
                    <p style="color: #64748B;"><i class="fas fa-calendar"></i> Valid until: <?php echo date('F j, Y', strtotime($currentSubscription['end_date'])); ?></p>
                    <p style="color: #64748B;"><i class="fas fa-check-circle" style="color: #10B981;"></i> Status: Active</p>
                </div>
                <div style="text-align: right;">
                    <p style="font-size: 2rem; font-weight: 700; color: #1E293B;">UGX <?php echo number_format($currentSubscription['amount']); ?></p>
                    <p style="color: #64748B;">per <?php echo $currentSubscription['plan_type']; ?></p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- No Active Subscription -->
        <div style="background: #FEF2F2; border-radius: 20px; padding: 20px; margin-bottom: 40px; border: 1px solid #FECACA;">
            <p style="color: #B91C1C;"><i class="fas fa-exclamation-circle"></i> You don't have an active subscription. Choose a plan below to continue access.</p>
        </div>
    <?php endif; ?>
    
    <!-- Subscription Plans -->
    <h2 style="color: #1E293B; margin-bottom: 20px;">Choose Your Plan</h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 50px;">
        <!-- Monthly Plan -->
        <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); text-align: center; transition: transform 0.3s ease; border: 2px solid transparent;">
            <h3 style="color: #8B5CF6; margin-bottom: 15px;">Monthly</h3>
            <p style="font-size: 2.5rem; font-weight: 700; color: #1E293B; margin-bottom: 10px;">UGX 15,000</p>
            <p style="color: #64748B; margin-bottom: 25px;">per month</p>
            <ul style="list-style: none; margin-bottom: 30px; text-align: left;">
                <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #10B981; margin-right: 10px;"></i> Full access to all lessons</li>
                <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #10B981; margin-right: 10px;"></i> Practice quizzes</li>
                <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #10B981; margin-right: 10px;"></i> Progress tracking</li>
                <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #10B981; margin-right: 10px;"></i> Email support</li>
            </ul>
            <a href="/rays-of-grace/external/purchase?plan=monthly" style="display: block; background: white; color: #8B5CF6; border: 2px solid #8B5CF6; text-decoration: none; padding: 15px; border-radius: 50px; font-weight: 600; transition: all 0.3s ease;">
                Select Plan
            </a>
        </div>
        
        <!-- Termly Plan (Popular) -->
        <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 20px 50px rgba(139, 92, 246, 0.2); text-align: center; transform: scale(1.05); border: 2px solid #8B5CF6; position: relative; z-index: 2;">
            <div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, #8B5CF6, #F97316); color: white; padding: 5px 20px; border-radius: 50px; font-size: 0.8rem; font-weight: 600;">
                Most Popular
            </div>
            <h3 style="color: #F97316; margin-bottom: 15px;">Termly</h3>
            <p style="font-size: 2.5rem; font-weight: 700; color: #1E293B; margin-bottom: 10px;">UGX 40,000</p>
            <p style="color: #64748B; margin-bottom: 25px;">per term (3 months)</p>
            <ul style="list-style: none; margin-bottom: 30px; text-align: left;">
                <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #10B981; margin-right: 10px;"></i> Everything in Monthly</li>
                <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #10B981; margin-right: 10px;"></i> Save 11%</li>
                <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #10B981; margin-right: 10px;"></i> Priority support</li>
                <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #10B981; margin-right: 10px;"></i> Downloadable materials</li>
            </ul>
            <a href="/rays-of-grace/external/purchase?plan=termly" style="display: block; background: linear-gradient(135deg, #8B5CF6, #F97316); color: white; text-decoration: none; padding: 15px; border-radius: 50px; font-weight: 600; transition: all 0.3s ease;">
                Select Plan
            </a>
        </div>
        
        <!-- Yearly Plan -->
        <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); text-align: center; transition: transform 0.3s ease; border: 2px solid transparent;">
            <h3 style="color: #8B5CF6; margin-bottom: 15px;">Yearly</h3>
            <p style="font-size: 2.5rem; font-weight: 700; color: #1E293B; margin-bottom: 10px;">UGX 120,000</p>
            <p style="color: #64748B; margin-bottom: 25px;">per year <span style="background: #10B981; color: white; padding: 3px 10px; border-radius: 50px; font-size: 0.8rem; margin-left: 10px;">Save 33%</span></p>
            <ul style="list-style: none; margin-bottom: 30px; text-align: left;">
                <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #10B981; margin-right: 10px;"></i> Everything in Termly</li>
                <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #10B981; margin-right: 10px;"></i> 2 months free</li>
                <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #10B981; margin-right: 10px;"></i> Certificate of completion</li>
                <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #10B981; margin-right: 10px;"></i> 1-on-1 tutoring sessions</li>
            </ul>
            <a href="/rays-of-grace/external/purchase?plan=yearly" style="display: block; background: white; color: #8B5CF6; border: 2px solid #8B5CF6; text-decoration: none; padding: 15px; border-radius: 50px; font-weight: 600; transition: all 0.3s ease;">
                Select Plan
            </a>
        </div>
    </div>
    
    <!-- Payment History -->
    <?php if (!empty($paymentHistory)): ?>
    <h2 style="color: #1E293B; margin-bottom: 20px;">Payment History</h2>
    <div style="background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #F8FAFC;">
                    <th style="padding: 15px; text-align: left;">Date</th>
                    <th style="padding: 15px; text-align: left;">Plan</th>
                    <th style="padding: 15px; text-align: left;">Amount</th>
                    <th style="padding: 15px; text-align: left;">Status</th>
                    <th style="padding: 15px; text-align: left;">Transaction ID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paymentHistory as $payment): ?>
                <tr style="border-top: 1px solid #E2E8F0;">
                    <td style="padding: 15px;"><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></td>
                    <td style="padding: 15px; text-transform: capitalize;"><?php echo $payment['plan_type']; ?></td>
                    <td style="padding: 15px;">UGX <?php echo number_format($payment['amount']); ?></td>
                    <td style="padding: 15px;">
                        <span style="background: #F0FDF4; color: #166534; padding: 5px 10px; border-radius: 50px; font-size: 0.8rem;">
                            <?php echo ucfirst($payment['status']); ?>
                        </span>
                    </td>
                    <td style="padding: 15px; color: #64748B; font-size: 0.9rem;"><?php echo $payment['transaction_id']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<style>
    .plan-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 50px rgba(139, 92, 246, 0.15);
    }
    .plan-card.popular:hover {
        transform: scale(1.05) translateY(-5px);
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>