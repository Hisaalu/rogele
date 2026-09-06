<?php
// File: /views/external/quizzes.php
$pageTitle = 'Do Quizzes | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$quizzes = $quizzes ?? [];
$results = $results ?? [];
$userResults = [];
$quizStats = [];
$totalScores = 0;
$completedAttempts = 0;

foreach ($results as $result) {
    $quizId = $result['quiz_id'] ?? null;
    if ($quizId === null) continue;

    $userResults[$quizId][] = $result;
    
    if (isset($result['score'])) {
        $score = (float)$result['score'];
        $totalScores += $score;
        $completedAttempts++;

        if (!isset($quizStats[$quizId]['best_score']) || $score > $quizStats[$quizId]['best_score']) {
            $quizStats[$quizId]['best_score'] = $score;
        }
    }
}

$averageScore = $completedAttempts > 0 ? round($totalScores / $completedAttempts, 1) : 0;
$hasTopPerformerBadge = ($completedAttempts > 0 && $averageScore >= 80);
$now = time();
?>

<style>
:root {
    --primary-purple: #7f2677;
    --primary-purple-hover: #671f61;
    --accent-orange: #f06724;
    --success-green: #079647;
    --warning-yellow: #f59e0b;
    --error-red: #b91c1c;
    --bg-light: #f8fafc;
    --text-dark: #000;
    --text-muted: #555;
    --radius-lg: 16px;
    --radius-sm: 8px;
    --shadow-card: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.quizzes-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: clamp(16px, 4vw, 40px) clamp(12px, 3vw, 24px);
}

.performance-banner {
    background: linear-gradient(135deg, var(--primary-purple), #5c1b57);
    border-radius: var(--radius-lg);
    padding: clamp(16px, 3vw, 28px);
    color: #ffffff;
    margin-bottom: 30px;
    box-shadow: 0 10px 25px rgba(127, 38, 119, 0.25);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}

.performance-info {
    display: flex;
    align-items: center;
    gap: 16px;
    flex: 1 1 300px;
}

.badge-icon-box {
    width: 56px;
    height: 56px;
    flex-shrink: 0;
    background: rgba(16, 185, 129, 0.2);
    border: 1px solid rgba(52, 211, 153, 0.4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: #34d399;
    backdrop-filter: blur(8px);
}

.performance-details h3 {
    margin: 0 0 4px 0;
    font-size: clamp(1.1rem, 2vw, 1.35rem);
    font-weight: 700;
}

.performance-details p {
    margin: 0;
    font-size: 0.9rem;
    opacity: 0.92;
    line-height: 1.4;
}

.performance-stats {
    display: flex;
    gap: 16px;
    background: rgba(255, 255, 255, 0.15);
    padding: 10px 18px;
    border-radius: 12px;
    backdrop-filter: blur(8px);
    flex-shrink: 0;
}

.stat-item {
    text-align: center;
}

.stat-item .stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    display: block;
}

.stat-item .stat-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.85;
}

.quizzes-header {
    text-align: center;
    margin-bottom: 32px;
}

.page-title {
    font-size: clamp(1.8rem, 4vw, 2.4rem);
    font-weight: 800;
    color: var(--primary-purple);
    margin-bottom: 6px;
}

.page-subtitle {
    color: var(--text-muted);
    font-size: 0.95rem;
}

.alert {
    padding: 14px 18px;
    border-radius: var(--radius-sm);
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.95rem;
}

.alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

.quizzes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 340px), 1fr));
    gap: 20px;
}

.quiz-card {
    background: #ffffff;
    border-radius: var(--radius-lg);
    padding: 20px;
    box-shadow: var(--shadow-card);
    border: 1px solid #e2e8f0;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
    position: relative;
}

.quiz-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
}

.quiz-card.completed-quiz {
    border-color: #bbf7d0;
    background: linear-gradient(180deg, #ffffff, #f0fdf4);
}

.quiz-card.in-progress-quiz {
    border-color: #fde68a;
    background: linear-gradient(180deg, #ffffff, #fef3c7);
}

.quiz-card.no-attempts-left {
    border-color: #fed7aa;
    background: linear-gradient(180deg, #ffffff, #fff7ed);
}

.quiz-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    gap: 8px;
    flex-wrap: wrap;
}

.badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.quiz-subject { background: #f3e8ff; color: var(--primary-purple); }
.quiz-class { background: #ffedd5; color: #c2410c; }

.quiz-title {
    color: var(--text-dark);
    font-size: 1.15rem;
    font-weight: 700;
    margin: 0 0 8px 0;
    line-height: 1.3;
}

.quiz-description {
    color: var(--text-muted);
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 16px;
    flex-grow: 1;
}

.quiz-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
    font-size: 0.8rem;
    color: var(--text-muted);
}

.quiz-meta span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.quiz-meta i { color: var(--accent-orange); }

.deadline-badge {
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
}
.deadline-active { background: #fef3c7; color: #92400e; }
.deadline-expired { background: #fef2f2; color: #b91c1c; }

.quiz-progress {
    margin-bottom: 16px;
    padding: 12px 0;
    border-top: 1px dashed #e2e8f0;
    border-bottom: 1px dashed #e2e8f0;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 6px;
    font-size: 0.8rem;
    color: var(--text-dark);
}

.progress-bar {
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.4s ease;
}

.passed-badge {
    margin-top: 6px;
    font-size: 0.75rem;
    color: var(--success-green);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
}

.quiz-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-top: auto;
}

.btn {
    min-height: 44px;
    padding: 0 16px;
    border-radius: 22px;
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
    transition: background-color 0.2s ease, transform 0.1s ease;
    cursor: pointer;
    border: none;
}

.btn-start {
    flex: 1;
    background-color: var(--primary-purple);
    color: #ffffff;
}
.btn-start:hover { background-color: var(--primary-purple-hover); }

.btn-resume {
    flex: 1;
    background-color: var(--warning-yellow);
    color: #ffffff;
}
.btn-resume:hover { background-color: #d97706; }

.btn-results {
    width: 44px;
    height: 44px;
    padding: 0;
    border-radius: 50%;
    background: #f1f5f9;
    color: var(--primary-purple);
    flex-shrink: 0;
}
.btn-results:hover { background: #e2e8f0; }

.status-badge {
    flex: 1;
    min-height: 44px;
    border-radius: 22px;
    font-size: 0.85rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0 12px;
}

.status-expired { background: #fef2f2; color: var(--error-red); }
.status-no-attempts { background: #fff7ed; color: #c2410c; }
.status-inprogress { background: #fef3c7; color: #92400e; }

.one-time-warning {
    margin-top: 10px;
    font-size: 0.75rem;
    color: #92400e;
    display: flex;
    align-items: center;
    gap: 5px;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    background: #ffffff;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-top: 24px;
}

.feature-card {
    background: var(--bg-light);
    padding: 20px;
    border-radius: 12px;
}

@media (max-width: 480px) {
    .performance-stats {
        width: 100%;
        justify-content: space-around;
    }
    .quiz-actions {
        flex-direction: column;
        align-items: stretch;
    }
    .btn-results {
        width: 100%;
        border-radius: 22px;
    }
}
</style>

<div class="quizzes-container">
    <?php if ($completedAttempts > 0): ?>
        <div class="performance-banner">
            <div class="performance-info">
                <div class="badge-icon-box" aria-hidden="true">
                    <i class="fas <?= $hasTopPerformerBadge ? 'fa-crown' : 'fa-chart-line' ?>"></i>
                </div>
                <div class="performance-details">
                    <?php if ($hasTopPerformerBadge): ?>
                        <h3>You are a Top Performer!</h3>
                        <p>Outstanding job! Your average score across all quizzes is <strong><?= $averageScore ?>%</strong>.</p>
                    <?php else: ?>
                        <h3>Keep Practicing to Earn the Badge!</h3>
                        <p>Your overall average score is <strong><?= $averageScore ?>%</strong>. Reach <strong>80% or higher</strong> to unlock the Top Performer badge.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="performance-stats">
                <div class="stat-item">
                    <span class="stat-value"><?= $averageScore ?>%</span>
                    <span class="stat-label">Average</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= $completedAttempts ?></span>
                    <span class="stat-label">Completed</span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="quizzes-header">
        <h1 class="page-title"><i class="fas fa-pencil-alt" aria-hidden="true"></i> Practice Quizzes</h1>
        <p class="page-subtitle">Test your knowledge with interactive quizzes</p>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" role="alert">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error" role="alert">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
            <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <?php if (empty($quizzes)): ?>
        <div class="empty-state">
            <i class="fas fa-pencil-alt" style="font-size: 2.5rem; color: var(--accent-orange);" aria-hidden="true"></i>
            <h3 style="margin: 12px 0 8px;">No Quizzes Available</h3>
            <p style="color: var(--text-muted);">Check back later for new quizzes!</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-clock" style="color: var(--accent-orange);" aria-hidden="true"></i>
                    <h4>Timed Quizzes</h4>
                    <p>Practice under exam conditions</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-chart-line" style="color: var(--accent-orange);" aria-hidden="true"></i>
                    <h4>Track Progress</h4>
                    <p>See your growth over time</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-trophy" style="color: var(--accent-orange);" aria-hidden="true"></i>
                    <h4>Earn Badges</h4>
                    <p>Get recognized for high performance</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="quizzes-grid">
            <?php foreach ($quizzes as $quiz): 
                $quizId = $quiz['id'];
                $attempts = $userResults[$quizId] ?? [];
                $attemptCount = count($attempts);
                $bestScore = $quizStats[$quizId]['best_score'] ?? 0;
                
                $maxAttempts = (int)($quiz['max_attempts'] ?? 3);
                $remainingAttempts = max(0, $maxAttempts - $attemptCount);
                
                $hasInProgress = !empty($quiz['in_progress']);
                $hasQuestions = ($quiz['question_count'] ?? 0) > 0;
                $endDate = $quiz['end_date'] ?? null;
                $isExpired = !empty($endDate) && strtotime($endDate) < $now;
                $noAttemptsLeft = ($remainingAttempts <= 0 && $attemptCount > 0);
                
                $subjectName = $quiz['subject_name'] ?? 'General';
                $className = $quiz['class_name'] ?? 'All Levels';
                $questionCount = (int)($quiz['question_count'] ?? 0);
                $timeLimit = (int)($quiz['time_limit'] ?? 30);
                $passingScore = (int)($quiz['passing_score'] ?? 70);
                $resultAttemptId = $quiz['attempt_id'] ?? null;
            ?>
                <div class="quiz-card <?= $noAttemptsLeft ? 'no-attempts-left' : ($hasInProgress ? 'in-progress-quiz' : ''); ?>">
                    <div class="quiz-header">
                        <span class="badge quiz-subject"><?= htmlspecialchars($subjectName); ?></span>
                        <span class="badge quiz-class"><?= htmlspecialchars($className); ?></span>
                    </div>
                    
                    <h3 class="quiz-title"><?= htmlspecialchars($quiz['title'] ?? 'Untitled Quiz'); ?></h3>
                    
                    <?php if (!empty($quiz['description'])): ?>
                        <p class="quiz-description"><?= htmlspecialchars(mb_strimwidth($quiz['description'], 0, 100, '...')); ?></p>
                    <?php endif; ?>
                    
                    <div class="quiz-meta">
                        <span><i class="fas fa-question-circle" aria-hidden="true"></i> <?= $questionCount; ?> qns</span>
                        <span><i class="fas fa-clock" aria-hidden="true"></i> <?= $timeLimit; ?>m</span>
                        <span><i class="fas fa-trophy" aria-hidden="true"></i> <?= $passingScore; ?>% pass</span>
                        <span><i class="fas fa-redo-alt" aria-hidden="true"></i> <?= $attemptCount; ?>/<?= $maxAttempts; ?> attempts</span>
                        
                        <?php if ($endDate): ?>
                            <span class="deadline-badge <?= $isExpired ? 'deadline-expired' : 'deadline-active'; ?>">
                                <i class="fas fa-calendar-times" aria-hidden="true"></i>
                                <?= $isExpired ? 'Expired' : 'Due: ' . date('M d, g:i A', strtotime($endDate)); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($attemptCount > 0): ?>
                        <div class="quiz-progress">
                            <div class="progress-header">
                                <span>Best: <strong><?= $bestScore; ?>%</strong></span>
                                <?php if ($remainingAttempts > 0 && !$hasInProgress): ?>
                                    <span><?= $remainingAttempts; ?> left</span>
                                <?php endif; ?>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= min(100, $bestScore); ?>%; background: <?= $bestScore >= $passingScore ? 'var(--success-green)' : 'var(--accent-orange)'; ?>"></div>
                            </div>
                            <?php if ($bestScore >= $passingScore): ?>
                                <div class="passed-badge">
                                    <i class="fas fa-check-circle" aria-hidden="true"></i> Passed
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="quiz-actions">
                        <?php if ($isExpired): ?>
                            <div class="status-badge status-expired">
                                <i class="fas fa-hourglass-end" aria-hidden="true"></i> Expired
                            </div>
                            <?php if ($resultAttemptId): ?>
                                <a href="<?= BASE_URL; ?>/external/quiz-result/<?= $resultAttemptId; ?>" class="btn btn-results" aria-label="View Results">
                                    <i class="fas fa-chart-bar" aria-hidden="true"></i>
                                </a>
                            <?php endif; ?>
                            
                        <?php elseif ($hasInProgress): ?>
                            <a href="<?= BASE_URL; ?>/external/take-quiz/<?= $quizId; ?>" class="btn btn-resume">
                                <i class="fas fa-play" aria-hidden="true"></i> Resume Quiz
                            </a>
                            
                        <?php elseif ($noAttemptsLeft): ?>
                            <div class="status-badge status-no-attempts">
                                <i class="fas fa-ban" aria-hidden="true"></i> No Attempts
                            </div>
                            <?php if ($resultAttemptId): ?>
                                <a href="<?= BASE_URL; ?>/external/quiz-result/<?= $resultAttemptId; ?>" class="btn btn-results" aria-label="View Results">
                                    <i class="fas fa-chart-bar" aria-hidden="true"></i>
                                </a>
                            <?php endif; ?>
                            
                        <?php elseif ($remainingAttempts > 0 && $hasQuestions): ?>
                            <a href="<?= BASE_URL; ?>/external/take-quiz/<?= $quizId; ?>" class="btn btn-start">
                                <span>Start Quiz</span>
                                <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            </a>
                            
                        <?php else: ?>
                            <div class="status-badge status-expired">
                                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Unavailable
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($maxAttempts === 1 && $remainingAttempts > 0 && !$hasInProgress): ?>
                        <div class="one-time-warning">
                            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Single attempt only
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>