<?php
// File: /views/external/quiz_result.php
$pageTitle = 'Quiz Result - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

$passed = $attemptDetails['score'] >= $attemptDetails['passing_score'];
?>

<div style="padding: 40px 20px; max-width: 800px; margin: 0 auto;">
    <div style="background: white; border-radius: 30px; padding: 40px; box-shadow: 0 20px 50px rgba(0,0,0,0.1); text-align: center;">
        <!-- Result Icon -->
        <div style="margin-bottom: 30px;">
            <?php if ($passed): ?>
                <div style="width: 100px; height: 100px; background: #F0FDF4; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <i class="fas fa-trophy" style="font-size: 3rem; color: #F97316;"></i>
                </div>
            <?php else: ?>
                <div style="width: 100px; height: 100px; background: #FEF2F2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <i class="fas fa-times-circle" style="font-size: 3rem; color: #EF4444;"></i>
                </div>
            <?php endif; ?>
        </div>
        
        <h1 style="font-size: 2rem; margin-bottom: 10px; color: #1E293B;">
            <?php echo $passed ? 'Congratulations!' : 'Better Luck Next Time!'; ?>
        </h1>
        
        <p style="color: #64748B; margin-bottom: 40px;">
            You scored <?php echo $attemptDetails['correct_answers']; ?> out of <?php echo $attemptDetails['total_questions']; ?> questions correctly
        </p>
        
        <!-- Score Circle -->
        <div style="margin-bottom: 40px;">
            <div style="width: 200px; height: 200px; margin: 0 auto; position: relative;">
                <svg viewBox="0 0 100 100" style="width: 100%; height: 100%;">
                    <circle cx="50" cy="50" r="45" fill="none" stroke="#E2E8F0" stroke-width="10"/>
                    <circle cx="50" cy="50" r="45" fill="none" 
                            stroke="<?php echo $passed ? '#10B981' : '#EF4444'; ?>" 
                            stroke-width="10" 
                            stroke-dasharray="<?php echo ($attemptDetails['score'] / 100) * 283; ?> 283" 
                            stroke-dashoffset="0"
                            style="transition: stroke-dasharray 1s ease; transform: rotate(-90deg); transform-origin: 50% 50%;"/>
                </svg>
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                    <span style="font-size: 3rem; font-weight: 700; color: #1E293B;"><?php echo $attemptDetails['score']; ?>%</span>
                </div>
            </div>
        </div>
        
        <!-- Stats Grid -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px;">
            <div style="background: #F8FAFC; padding: 20px; border-radius: 15px;">
                <i class="fas fa-check-circle" style="color: #10B981; font-size: 1.5rem; margin-bottom: 10px;"></i>
                <div style="font-size: 1.5rem; font-weight: 700; color: #1E293B;"><?php echo $attemptDetails['correct_answers']; ?></div>
                <div style="color: #64748B; font-size: 0.9rem;">Correct</div>
            </div>
            
            <div style="background: #F8FAFC; padding: 20px; border-radius: 15px;">
                <i class="fas fa-times-circle" style="color: #EF4444; font-size: 1.5rem; margin-bottom: 10px;"></i>
                <div style="font-size: 1.5rem; font-weight: 700; color: #1E293B;"><?php echo $attemptDetails['total_questions'] - $attemptDetails['correct_answers']; ?></div>
                <div style="color: #64748B; font-size: 0.9rem;">Incorrect</div>
            </div>
            
            <div style="background: #F8FAFC; padding: 20px; border-radius: 15px;">
                <i class="fas fa-clock" style="color: #8B5CF6; font-size: 1.5rem; margin-bottom: 10px;"></i>
                <div style="font-size: 1.5rem; font-weight: 700; color: #1E293B;">
                    <?php 
                        $minutes = floor($attemptDetails['time_taken'] / 60);
                        $seconds = $attemptDetails['time_taken'] % 60;
                        echo $minutes . ':' . ($seconds < 10 ? '0' : '') . $seconds;
                    ?>
                </div>
                <div style="color: #64748B; font-size: 0.9rem;">Time Taken</div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div style="display: flex; gap: 20px; justify-content: center;">
            <a href="/rays-of-grace/external/quizzes" style="background: white; color: #8B5CF6; border: 2px solid #8B5CF6; text-decoration: none; padding: 15px 30px; border-radius: 50px; font-weight: 600;">
                Try Another Quiz
            </a>
            <a href="/rays-of-grace/external/materials" style="background: linear-gradient(135deg, #8B5CF6, #F97316); color: white; text-decoration: none; padding: 15px 30px; border-radius: 50px; font-weight: 600;">
                Continue Learning
            </a>
        </div>
    </div>
</div>

<style>
    @keyframes fillCircle {
        from { stroke-dasharray: 0 283; }
        to { stroke-dasharray: <?php echo ($attemptDetails['score'] / 100) * 283; ?> 283; }
    }
    circle:last-of-type {
        animation: fillCircle 1s ease forwards;
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>