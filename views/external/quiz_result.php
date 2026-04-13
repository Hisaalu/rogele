<?php
// File: /views/external/quiz_result.php
$pageTitle = 'Quiz Result | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

// Check if data exists
if (!isset($attemptDetails) || empty($attemptDetails)) {
    echo '<div style="text-align: center; padding: 50px;">
            <h2>Result not found</h2>
            <p>The quiz result you\'re looking for doesn\'t exist.</p>
            <a href="' . BASE_URL . '/external/quizzes" class="btn-primary">Back to Quizzes</a>
          </div>';
    require_once __DIR__ . '/../layouts/footer.php';
    exit;
}

// Set default values if missing
$score = isset($attemptDetails['score']) ? (int)$attemptDetails['score'] : 0;
$passingScore = isset($attemptDetails['passing_score']) ? (int)$attemptDetails['passing_score'] : 70;
$totalQuestions = isset($attemptDetails['total_questions']) ? (int)$attemptDetails['total_questions'] : 0;
$correctAnswers = isset($attemptDetails['correct_answers']) ? (int)$attemptDetails['correct_answers'] : 0;
$timeTaken = isset($attemptDetails['time_taken']) ? (int)$attemptDetails['time_taken'] : 0;

$passed = $score >= $passingScore;
$incorrectAnswers = $totalQuestions - $correctAnswers;
$minutes = floor($timeTaken / 60);
$seconds = $timeTaken % 60;
$timeFormatted = $minutes . ':' . ($seconds < 10 ? '0' : '') . $seconds;

// Get questions with user's answers
$questions = isset($attemptDetails['questions']) ? $attemptDetails['questions'] : [];
$userAnswers = isset($attemptDetails['user_answers']) ? $attemptDetails['user_answers'] : [];
?>

<div style="padding: 40px 20px; max-width: 900px; margin: 0 auto;">
    <div style="background: white; border-radius: 30px; padding: 40px; margin-bottom: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.1); text-align: center;">
        <div style="margin-bottom: 30px;">
            <?php if ($passed): ?>
                <div style="width: 100px; height: 100px; background: #F0FDF4; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <i class="fas fa-trophy" style="font-size: 3rem; color: #F97316;"></i>
                </div>
                <div style="margin-top: 15px;">
                    <span style="background: #10B981; color: white; padding: 5px 20px; border-radius: 30px; font-size: 0.9rem;">PASSED</span>
                    <span style="margin-left: 10px; color: black;">Required: <?php echo $passingScore; ?>%</span>
                </div>
            <?php else: ?>
                <div style="width: 100px; height: 100px; background: #FEF2F2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <i class="fas fa-times-circle" style="font-size: 3rem; color: #e21414;"></i>
                </div>
                <div style="margin-top: 15px;">
                    <span style="background: #e21414; color: white; padding: 5px 20px; border-radius: 30px; font-size: 0.9rem;">FAILED</span>
                    <span style="margin-left: 10px; color: black;">Required: <?php echo $passingScore; ?>%</span>
                </div>
            <?php endif; ?>
        </div>
        
        <h1 style="font-size: 2rem; margin-bottom: 10px; color: black;">
            <?php echo $passed ? 'Congratulations!' : 'Better Luck Next Time!'; ?>
        </h1>
        
        <p style="color: black; margin-bottom: 30px;">
            You scored <?php echo $correctAnswers; ?> out of <?php echo $totalQuestions; ?> questions correctly
        </p>
        
        <div style="margin-bottom: 30px;">
            <div style="width: 180px; height: 180px; margin: 0 auto; position: relative;">
                <svg viewBox="0 0 100 100" style="width: 100%; height: 100%;">
                    <circle cx="50" cy="50" r="45" fill="none" stroke="#E2E8F0" stroke-width="8"/>
                    <circle cx="50" cy="50" r="45" fill="none" 
                            stroke="<?php echo $passed ? '#10B981' : '#e21414'; ?>" 
                            stroke-width="8" 
                            stroke-dasharray="<?php echo ($score / 100) * 283; ?> 283" 
                            stroke-dashoffset="0"
                            style="transition: stroke-dasharray 1s ease; transform: rotate(-90deg); transform-origin: 50% 50%;"/>
                </svg>
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                    <span style="font-size: 2.5rem; font-weight: 700; color: black;"><?php echo $score; ?>%</span>
                </div>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
            <div style="background: #F8FAFC; padding: 20px; border-radius: 15px;">
                <i class="fas fa-check-circle" style="color: #09e99e; font-size: 1.5rem; margin-bottom: 10px;"></i>
                <div style="font-size: 1.5rem; font-weight: 700; color: black;"><?php echo $correctAnswers; ?></div>
                <div style="color: black; font-size: 0.9rem;">Correct</div>
            </div>
            
            <div style="background: #F8FAFC; padding: 20px; border-radius: 15px;">
                <i class="fas fa-times-circle" style="color: #e21414; font-size: 1.5rem; margin-bottom: 10px;"></i>
                <div style="font-size: 1.5rem; font-weight: 700; color: black;"><?php echo $incorrectAnswers; ?></div>
                <div style="color: black; font-size: 0.9rem;">Incorrect</div>
            </div>
            
            <div style="background: #F8FAFC; padding: 20px; border-radius: 15px;">
                <i class="fas fa-clock" style="color: #f06724; font-size: 1.5rem; margin-bottom: 10px;"></i>
                <div style="font-size: 1.5rem; font-weight: 700; color: black;"><?php echo $timeFormatted; ?></div>
                <div style="color: black; font-size: 0.9rem;">Time Taken</div>
            </div>
        </div>
    </div>

    <!-- Detailed Review Section -->
    <?php if (!empty($questions)): ?>
        <div style="background: white; border-radius: 30px; padding: 40px; box-shadow: 0 20px 50px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
                <h2 style="color: black; margin: 0;">
                    <i class="fas fa-list-check"></i> Detailed Review
                </h2>
            </div>
            
            <!-- Feedback Section -->
            <div style="margin-top: 30px; padding: 20px; background: <?php echo $passed ? '#F0FDF4' : '#FEF2F2'; ?>; border-radius: 16px; text-align: center;">
                <?php if ($passed): ?>
                    <i class="fas fa-star" style="font-size: 2rem; color: #F97316;"></i>
                    <h3 style="color: #166534; margin-top: 10px;">Great Job!</h3>
                    <p style="color: #047857;">You've successfully passed this quiz. Keep up the good work!</p>
                <?php else: ?>
                    <i class="fas fa-book-open" style="font-size: 2rem; color: #e21414;"></i>
                    <h3 style="color: #e21414; margin-top: 10px;">Keep Practicing!</h3>
                    <p style="color: #e21414;">Review the correct answers and try again to improve your score.</p>
                <?php endif; ?>
            </div>
            
            <div style="display: flex; gap: 20px; justify-content: center; margin-top: 30px;">
                <a href="<?php echo BASE_URL; ?>/external/quizzes" style="background: white; color: #7f2677; border: 2px solid #7f2677; text-decoration: none; padding: 15px 30px; border-radius: 50px; font-weight: 600; transition: all 0.3s ease;">
                    <i class="fas fa-redo-alt"></i> Try Another Quiz
                </a>
                <a href="<?php echo BASE_URL; ?>/external/materials" style="background: linear-gradient(135deg, #7f2677); color: white; text-decoration: none; padding: 15px 30px; border-radius: 50px; font-weight: 600; transition: all 0.3s ease;">
                    <i class="fas fa-book-open"></i> Continue Learning
                </a>
            </div>
        </div>
    <?php else: ?>
        <div style="background: white; border-radius: 30px; padding: 40px; text-align: center;">
            <i class="fas fa-info-circle" style="font-size: 3rem; color: #8B5CF6; margin-bottom: 20px;"></i>
            <h3>No Questions Available</h3>
            <p>The questions for this quiz could not be loaded.</p>
            <a href="<?php echo BASE_URL; ?>/external/quizzes" class="btn-primary">Back to Quizzes</a>
        </div>
    <?php endif; ?>
</div>

<style>
    @keyframes fillCircle {
        from { stroke-dasharray: 0 283; }
        to { stroke-dasharray: <?php echo ($score / 100) * 283; ?> 283; }
    }
    circle:last-of-type {
        animation: fillCircle 1s ease forwards;
    }
    
    .toggle-btn:hover {
        background: #E2E8F0;
        transform: translateY(-1px);
    }
    
    .review-option {
        transition: all 0.2s ease;
    }
    
    .review-option:hover {
        transform: translateX(5px);
    }
    
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .action-buttons {
            flex-direction: column;
            gap: 15px;
        }
        
        .answer-summary {
            flex-direction: column;
            gap: 8px;
        }
    }
    
    @media (prefers-color-scheme: dark) {
        .review-question {
            background: #1E293B;
        }
        
        .question-text {
            color: #F1F5F9;
        }
        
        .option-text {
            color: #F1F5F9;
        }
        
        .toggle-btn {
            background: #334155;
            color: #F1F5F9;
        }
        
        .toggle-btn:hover {
            background: #475569;
        }
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>