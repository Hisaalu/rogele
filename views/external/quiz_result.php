<?php
//File: /views/external/quiz_result.php
$pageTitle = 'Quiz Result | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

if (!isset($attemptDetails) || empty($attemptDetails)) {
    echo '<div class="error-container">
            <h2>Result not found</h2>
            <p>The quiz result you\'re looking for doesn\'t exist.</p>
            <a href="' . BASE_URL . '/external/quizzes" class="btn btn-primary">Back to Quizzes</a>
          </div>';
    require_once __DIR__ . '/../layouts/footer.php';
    exit;
}

$score = isset($attemptDetails['score']) ? (int)$attemptDetails['score'] : 0;
$passingScore = isset($attemptDetails['passing_score']) ? (int)$attemptDetails['passing_score'] : 70;
$totalQuestions = isset($attemptDetails['total_questions']) ? (int)$attemptDetails['total_questions'] : 0;
$correctAnswers = isset($attemptDetails['correct_answers']) ? (int)$attemptDetails['correct_answers'] : 0;
$timeTaken = isset($attemptDetails['time_taken']) ? (int)$attemptDetails['time_taken'] : 0;

$passed = $score >= $passingScore;
$incorrectAnswers = max(0, $totalQuestions - $correctAnswers);
$timeFormatted = sprintf('%02d:%02d', floor($timeTaken / 60), $timeTaken % 60);

$questions = $attemptDetails['questions'] ?? [];
$userAnswers = $attemptDetails['user_answers'] ?? [];
$letterMap = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3];
$letters = ['A', 'B', 'C', 'D'];
?>

<style>
    :root {
        --primary: #7f2677;
        --primary-hover: #661e60;
        --success: #10B981;
        --success-bg: #F0FDF4;
        --danger: #EF4444;
        --danger-bg: #FEF2F2;
        --warning: #F59E0B;
        --warning-bg: #FFFBEB;
        --card-bg: #FFFFFF;
        --bg-subtle: #F8FAFC;
        --border: #E2E8F0;
        --text-dark: #0F172A;
        --text-muted: #64748B;
        --radius: 16px;
    }

    .quiz-container {
        max-width: 880px;
        margin: 0 auto;
        padding: 40px 20px;
        font-family: system-ui, -apple-system, sans-serif;
    }

    .card {
        background: var(--card-bg);
        border-radius: calc(var(--radius) * 1.25);
        padding: 36px;
        margin-bottom: 24px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.01);
        border: 1px solid var(--border);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 16px;
        border-radius: 9999px;
        font-weight: 700;
        font-size: 0.85rem;
        letter-spacing: 0.05em;
    }

    .status-passed { background: var(--success); color: white; }
    .status-failed { background: var(--danger); color: white; }

    .score-circle-wrapper {
        position: relative;
        width: 160px;
        height: 160px;
        margin: 20px auto;
    }

    .score-circle-wrapper svg {
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
    }

    .score-circle-bg { stroke: var(--border); stroke-width: 8; fill: none; }
    .score-circle-progress {
        stroke-width: 8;
        stroke-linecap: round;
        fill: none;
        stroke-dasharray: 283;
        stroke-dashoffset: 283;
        animation: fillCircle 1.2s ease-out forwards;
    }

    @keyframes fillCircle {
        to { stroke-dashoffset: <?php echo 283 - (($score / 100) * 283); ?>; }
    }

    .score-text {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.25rem;
        font-weight: 800;
        color: var(--text-dark);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-top: 32px;
    }

    .stat-card {
        background: var(--bg-subtle);
        padding: 20px;
        border-radius: var(--radius);
        text-align: center;
        border: 1px solid var(--border);
    }

    .stat-card i { font-size: 1.25rem; margin-bottom: 8px; }
    .stat-card .val { font-size: 1.5rem; font-weight: 700; color: var(--text-dark); }
    .stat-card .lbl { font-size: 0.85rem; color: var(--text-muted); font-weight: 500; }

    .review-item {
        background: var(--bg-subtle);
        border-radius: var(--radius);
        padding: 24px;
        margin-bottom: 20px;
        border: 1px solid var(--border);
        border-left-width: 5px;
        transition: transform 0.2s ease;
    }

    .review-item.correct { border-left-color: var(--success); }
    .review-item.incorrect { border-left-color: var(--danger); }

    .option-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        margin-bottom: 8px;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: white;
        transition: all 0.2s ease;
    }

    .option-item.opt-correct { background: var(--success-bg); border-color: var(--success); }
    .option-item.opt-user-wrong { background: var(--danger-bg); border-color: var(--danger); }

    .option-badge {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        background: var(--bg-subtle);
        color: var(--text-muted);
    }

    .opt-correct .option-badge { background: var(--success); color: white; }
    .opt-user-wrong .option-badge { background: var(--danger); color: white; }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 28px;
        border-radius: 9999px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-outline { border: 2px solid var(--primary); color: var(--primary); }
    .btn-outline:hover { background: var(--primary); color: white; }
    .btn-primary { background: var(--primary); color: white; border: 2px solid var(--primary); }
    .btn-primary:hover { background: var(--primary-hover); border-color: var(--primary-hover); }

    .explanation {
        margin-top: 16px;
        padding: 14px;
        background: var(--warning-bg);
        border-radius: 10px;
        border-left: 3px solid var(--warning);
        font-size: 0.9rem;
        color: #78350F;
    }

    .error-container { text-align: center; padding: 60px 20px; }
</style>

<div class="quiz-container">
    <div class="card" style="text-align: center;">
        <div style="margin-bottom: 20px;">
            <div style="width: 80px; height: 80px; background: <?php echo $passed ? 'var(--success-bg)' : 'var(--danger-bg)'; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                <i class="fas <?php echo $passed ? 'fa-trophy' : 'fa-times-circle'; ?>" style="font-size: 2.5rem; color: <?php echo $passed ? 'var(--warning)' : 'var(--danger)'; ?>;"></i>
            </div>
            <span class="status-badge <?php echo $passed ? 'status-passed' : 'status-failed'; ?>">
                <?php echo $passed ? 'PASSED' : 'FAILED'; ?>
            </span>
            <span style="margin-left: 10px; color: var(--text-muted); font-size: 0.9rem;">Pass Mark: <?php echo $passingScore; ?>%</span>
        </div>
        
        <h1 style="font-size: 1.8rem; margin: 0 0 8px; color: var(--text-dark);">
            <?php echo $passed ? 'Congratulations!' : 'Better Luck Next Time!'; ?>
        </h1>
        <p style="color: var(--text-muted); margin: 0 0 20px;">
            You scored <?php echo $correctAnswers; ?> out of <?php echo $totalQuestions; ?> questions correctly.
        </p>

        <div class="score-circle-wrapper">
            <svg viewBox="0 0 100 100">
                <circle class="score-circle-bg" cx="50" cy="50" r="45"/>
                <circle class="score-circle-progress" cx="50" cy="50" r="45" stroke="<?php echo $passed ? 'var(--success)' : 'var(--danger)'; ?>"/>
            </svg>
            <div class="score-text"><?php echo $score; ?>%</div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-check-circle" style="color: var(--success);"></i>
                <div class="val"><?php echo $correctAnswers; ?></div>
                <div class="lbl">Correct</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-times-circle" style="color: var(--danger);"></i>
                <div class="val"><?php echo $incorrectAnswers; ?></div>
                <div class="lbl">Incorrect</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock" style="color: var(--warning);"></i>
                <div class="val"><?php echo $timeFormatted; ?></div>
                <div class="lbl">Time Taken</div>
            </div>
        </div>
    </div>

    <?php if (!empty($questions)): ?>
    <div class="card">
        <h2 style="color: var(--text-dark); margin: 0 0 8px; font-size: 1.4rem;">
            <i class="fas fa-list-check" style="color: var(--primary);"></i> Review & Corrections
        </h2>
        <p style="color: var(--text-muted); margin: 0 0 24px; font-size: 0.95rem;">
            Review your answers below. Corrections are highlighted in green.
        </p>
        
        <div class="questions-review">
            <?php foreach ($questions as $index => $question): 
                $qId = $question['id'] ?? null;
                $userAnswer = $userAnswers[$qId] ?? null;

                if (is_string($userAnswer) && isset($letterMap[strtoupper($userAnswer)])) {
                    $userAnswer = $letterMap[strtoupper($userAnswer)];
                } elseif (is_numeric($userAnswer)) {
                    $userAnswer = (int)$userAnswer;
                }

                $correctOption = isset($question['correct_option']) ? (int)$question['correct_option'] : 0;
                $isCorrect = ($userAnswer !== null && $userAnswer === $correctOption);
                $options = $question['options'] ?? [];
            ?>
                <div class="review-item <?php echo $isCorrect ? 'correct' : 'incorrect'; ?>">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-weight: 700; color: var(--text-dark);">Question <?php echo $index + 1; ?></span>
                            <span class="status-badge <?php echo $isCorrect ? 'status-passed' : 'status-failed'; ?>" style="font-size: 0.7rem; padding: 2px 10px;">
                                <?php echo $isCorrect ? 'Correct' : 'Incorrect'; ?>
                            </span>
                        </div>
                        <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 500;"><?php echo $question['points'] ?? 1; ?> point(s)</span>
                    </div>
                    
                    <div style="font-size: 1.05rem; font-weight: 600; color: var(--text-dark); margin-bottom: 16px;">
                        <?php echo htmlspecialchars($question['question_text']); ?>
                    </div>
                    
                    <div style="margin-bottom: 16px;">
                        <?php foreach ($options as $optIndex => $option):
                            $isUserSelected = ($userAnswer === $optIndex);
                            $isAnswerCorrect = ($optIndex === $correctOption);
                            
                            $class = '';
                            if ($isAnswerCorrect) $class = 'opt-correct';
                            elseif ($isUserSelected) $class = 'opt-user-wrong';
                        ?>
                            <div class="option-item <?php echo $class; ?>">
                                <span class="option-badge"><?php echo $letters[$optIndex] ?? ''; ?></span>
                                <span style="flex: 1; color: var(--text-dark); font-size: 0.95rem;"><?php echo htmlspecialchars($option); ?></span>
                                <?php if ($isAnswerCorrect): ?>
                                    <span style="font-size: 0.75rem; color: var(--success); font-weight: 600;"><i class="fas fa-check"></i> Correct Answer</span>
                                <?php elseif ($isUserSelected): ?>
                                    <span style="font-size: 0.75rem; color: var(--danger); font-weight: 600;"><i class="fas fa-times"></i> Your Answer</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (!empty($question['explanation'])): ?>
                        <div class="explanation">
                            <i class="fas fa-lightbulb" style="color: var(--warning);"></i>
                            <strong>Explanation:</strong>
                            <div style="margin-top: 4px;"><?php echo htmlspecialchars($question['explanation']); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="margin-top: 32px; padding: 24px; background: <?php echo $passed ? 'var(--success-bg)' : 'var(--danger-bg)'; ?>; border-radius: var(--radius); text-align: center;">
            <h3 style="color: <?php echo $passed ? 'var(--success)' : 'var(--danger)'; ?>; margin: 0 0 6px;">
                <?php echo $passed ? 'Great Job!' : 'Keep Practicing!'; ?>
            </h3>
            <p style="color: var(--text-dark); margin: 0; font-size: 0.95rem;">
                <?php echo $passed ? "You've successfully passed this quiz. Keep up the good work!" : 'Review the correct answers and try again to improve your score.'; ?>
            </p>
        </div>
        
        <div style="display: flex; gap: 16px; justify-content: center; margin-top: 32px; flex-wrap: wrap;">
            <a href="<?php echo BASE_URL; ?>/external/quizzes" class="btn btn-outline">
                <i class="fas fa-redo-alt"></i> Try Another Quiz
            </a>
            <a href="<?php echo BASE_URL; ?>/external/materials" class="btn btn-primary">
                <i class="fas fa-book-open"></i> Continue Learning
            </a>
        </div>
    </div>
    <?php else: ?>
        <div class="card" style="text-align: center;">
            <i class="fas fa-info-circle" style="font-size: 3rem; color: var(--primary); margin-bottom: 16px;"></i>
            <h3>No Questions Available</h3>
            <p style="color: var(--text-muted);">The questions for this quiz could not be loaded.</p>
            <a href="<?php echo BASE_URL; ?>/external/quizzes" class="btn btn-primary" style="margin-top: 12px;">Back to Quizzes</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>