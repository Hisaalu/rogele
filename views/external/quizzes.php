<?php
// File: /views/external/quizzes.php
$pageTitle = 'Practice Quizzes - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

$quizzes = $quizzes ?? [];
$results = $results ?? [];

// Group results by quiz for quick lookup
$userResults = [];
foreach ($results as $result) {
    $userResults[$result['quiz_id']][] = $result;
}

// Debug info (visible in page source) - FIXED: moved inside the foreach loop
echo "<!-- Total quizzes loaded: " . count($quizzes) . " -->\n";
// Only loop through quizzes if they exist
if (!empty($quizzes)) {
    foreach ($quizzes as $quiz) {
        echo "<!-- Quiz: ID={$quiz['id']} - Title={$quiz['title']} - Max Attempts: " . ($quiz['max_attempts'] ?? 3) . " -->\n";
    }
}
?>

<div class="quizzes-container">
    <!-- Header -->
    <div class="quizzes-header">
        <h1 class="page-title">
            <i class="fas fa-pencil-alt"></i>
            Practice Quizzes
        </h1>
        <p class="page-subtitle">Test your knowledge with interactive quizzes</p>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <!-- Quizzes Grid -->
    <?php if (empty($quizzes)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-pencil-alt"></i>
            </div>
            <h3>No Quizzes Available</h3>
            <p>Check back later for new quizzes. We're constantly adding new content!</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-clock"></i>
                    <h4>Timed Quizzes</h4>
                    <p>Practice under real exam conditions</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-chart-line"></i>
                    <h4>Track Progress</h4>
                    <p>See your improvement over time</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-trophy"></i>
                    <h4>Earn Badges</h4>
                    <p>Get rewarded for achievements</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="quizzes-grid">
            <?php foreach ($quizzes as $index => $quiz): 
                $attempts = isset($userResults[$quiz['id']]) ? $userResults[$quiz['id']] : [];
                $bestScore = !empty($attempts) ? max(array_column($attempts, 'score')) : 0;
                $attemptCount = count($attempts);
                $maxAttempts = isset($quiz['max_attempts']) ? (int)$quiz['max_attempts'] : 3;
                $remainingAttempts = max(0, $maxAttempts - $attemptCount);
                
                $hasInProgress = isset($quiz['in_progress']) && $quiz['in_progress'] === true;
                $hasCompleted = isset($quiz['completed']) && $quiz['completed'] === true;
                $resultAttemptId = isset($quiz['attempt_id']) ? $quiz['attempt_id'] : null;
                $hasQuestions = (isset($quiz['question_count']) ? $quiz['question_count'] : 0) > 0;
                $endDate = isset($quiz['end_date']) ? $quiz['end_date'] : null;
                
                // Check if quiz is expired
                $isExpired = false;
                if (!empty($endDate) && strtotime($endDate) < time()) {
                    $isExpired = true;
                }
                
                // Check if user has used all attempts (no attempts left)
                $noAttemptsLeft = ($remainingAttempts <= 0 && $attemptCount > 0);
                
                // Get subject and class names with defaults
                $subjectName = isset($quiz['subject_name']) ? $quiz['subject_name'] : 'General';
                $className = isset($quiz['class_name']) ? $quiz['class_name'] : 'All Levels';
                $questionCount = isset($quiz['question_count']) ? $quiz['question_count'] : 0;
                $timeLimit = isset($quiz['time_limit']) ? $quiz['time_limit'] : 30;
                $passingScore = isset($quiz['passing_score']) ? $quiz['passing_score'] : 70;
                $quizTitle = isset($quiz['title']) ? $quiz['title'] : 'Untitled Quiz';
                $quizDescription = isset($quiz['description']) ? $quiz['description'] : '';
            ?>
                <div class="quiz-card <?php echo $noAttemptsLeft ? 'no-attempts-left' : ($hasInProgress ? 'in-progress-quiz' : ''); ?>">
                    <div class="quiz-header">
                        <span class="quiz-subject"><?php echo htmlspecialchars($subjectName); ?></span>
                        <span class="quiz-class"><?php echo htmlspecialchars($className); ?></span>
                    </div>
                    
                    <h3 class="quiz-title"><?php echo htmlspecialchars($quizTitle); ?></h3>
                    
                    <?php if (!empty($quizDescription)): ?>
                        <p class="quiz-description"><?php echo htmlspecialchars(substr($quizDescription, 0, 100)); ?><?php echo strlen($quizDescription) > 100 ? '...' : ''; ?></p>
                    <?php endif; ?>
                    
                    <div class="quiz-meta">
                        <span title="Questions">
                            <i class="fas fa-question-circle"></i>
                            <?php echo $questionCount; ?> questions
                        </span>
                        <span title="Time Limit">
                            <i class="fas fa-clock"></i>
                            <?php echo $timeLimit; ?> min
                        </span>
                        <span title="Passing Score">
                            <i class="fas fa-trophy"></i>
                            <?php echo $passingScore; ?>% to pass
                        </span>
                        <span title="Attempts Allowed">
                            <i class="fas fa-redo-alt"></i>
                            <?php echo $attemptCount; ?>/<?php echo $maxAttempts; ?> attempts used
                        </span>
                        
                        <!-- Deadline/Expiration Information -->
                        <?php if (!empty($endDate)): ?>
                            <span title="Deadline" class="deadline-badge <?php echo $isExpired ? 'deadline-expired' : 'deadline-active'; ?>">
                                <i class="fas fa-calendar-times"></i>
                                <?php if ($isExpired): ?>
                                    Expired on <?php echo date('M d, Y', strtotime($endDate)); ?>
                                <?php else: ?>
                                    Due: <?php echo date('M d, Y', strtotime($endDate)); ?>
                                    <?php 
                                    $daysRemaining = ceil((strtotime($endDate) - time()) / 86400);
                                    if ($daysRemaining <= 3 && $daysRemaining > 0): ?>
                                        <span class="urgent">(<?php echo $daysRemaining; ?> days left!)</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- User Progress -->
                    <?php if ($attemptCount > 0): ?>
                        <div class="quiz-progress">
                            <div class="progress-header">
                                <span>Your best score: <strong><?php echo $bestScore; ?>%</strong></span>
                                <?php if ($remainingAttempts > 0 && !$hasInProgress): ?>
                                <span><?php echo $remainingAttempts; ?> attempt(s) left</span>
                                <?php endif; ?>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $bestScore; ?>%; background: <?php echo $bestScore >= $passingScore ? '#10B981' : '#F97316'; ?>"></div>
                            </div>
                            <?php if ($bestScore >= $passingScore): ?>
                                <div class="passed-badge">
                                    <i class="fas fa-check-circle"></i> Passing Score Achieved!
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Action Buttons -->
                    <div class="quiz-actions">
                        <?php if ($isExpired): ?>
                            <div class="quiz-expired">
                                <i class="fas fa-hourglass-end"></i> Quiz Expired
                            </div>
                            <?php if ($resultAttemptId): ?>
                                <a href="<?php echo BASE_URL; ?>/external/quiz-result/<?php echo $resultAttemptId; ?>" class="btn-results">
                                    <i class="fas fa-chart-bar"></i> View Results
                                </a>
                            <?php endif; ?>
                            
                        <?php elseif ($hasInProgress): ?>
                            <div class="quiz-inprogress-badge">
                                <i class="fas fa-hourglass-half"></i> In Progress
                            </div>
                            <a href="<?php echo BASE_URL; ?>/external/take-quiz/<?php echo $quiz['id']; ?>" class="btn-resume">
                                <i class="fas fa-play"></i> Resume Quiz
                            </a>
                            
                        <?php elseif ($noAttemptsLeft): ?>
                            <div class="quiz-no-attempts">
                                <i class="fas fa-ban"></i> No Attempts Left
                            </div>
                            <?php if ($resultAttemptId): ?>
                                <a href="<?php echo BASE_URL; ?>/external/quiz-result/<?php echo $resultAttemptId; ?>" class="btn-results">
                                    <i class="fas fa-chart-bar"></i> View Results
                                </a>
                            <?php endif; ?>
                            
                        <?php elseif ($remainingAttempts > 0): ?>
                            <a href="<?php echo BASE_URL; ?>/external/take-quiz/<?php echo $quiz['id']; ?>" class="btn-start">
                                <span>Start Quiz</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                            <?php if ($attemptCount > 0): ?>
                                <div class="attempts-badge">
                                    <i class="fas fa-redo-alt"></i> <?php echo $remainingAttempts; ?> attempt(s) remaining
                                </div>
                            <?php endif; ?>
                            
                        <?php elseif (!$hasQuestions): ?>
                            <div class="quiz-expired">
                                <i class="fas fa-exclamation-triangle"></i> No Questions Available
                            </div>
                            
                        <?php else: ?>
                            <div class="quiz-expired">
                                <i class="fas fa-ban"></i> No Attempts Left
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Warning for single attempt quizzes -->
                    <?php if ($maxAttempts == 1 && $remainingAttempts > 0 && !$hasInProgress): ?>
                        <div class="one-time-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>You can only take this quiz once</span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
/* Add these new styles */
.quiz-no-attempts {
    background: #FEF2F2;
    color: #B91C1C;
    padding: 10px 15px;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 600;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex: 1;
}

.attempts-badge {
    background: #F1F5F9;
    color: #1E293B;
    padding: 6px 12px;
    border-radius: 30px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}

.quiz-card.no-attempts-left {
    border: 2px solid #EF4444;
    background: linear-gradient(135deg, white, #FEF2F2);
    opacity: 0.9;
}

.deadline-badge {
    background: #FEF3C7;
    color: #92400E;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

/* Deadline Badge Styles */
.deadline-badge {
    background: #FEF3C7;
    color: #92400E;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.deadline-badge.deadline-active {
    background: #FEF3C7;
    color: #92400E;
}

.deadline-badge.deadline-expired {
    background: #FEF2F2;
    color: #B91C1C;
}

.deadline-badge .urgent {
    color: #EF4444;
    font-weight: 600;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

.quiz-expired {
    background: #FEF2F2;
    color: #B91C1C;
    padding: 10px 15px;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 600;
    text-align: center;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.quizzes-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.quizzes-header {
    text-align: center;
    margin-bottom: 40px;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
}

.page-subtitle {
    color: #64748B;
    font-size: 1.1rem;
}

/* Alerts */
.alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideDown 0.3s ease;
}

.alert-success {
    background: #F0FDF4;
    color: #166534;
    border: 1px solid #BBF7D0;
}

.alert-error {
    background: #FEF2F2;
    color: #B91C1C;
    border: 1px solid #FECACA;
}

@keyframes slideDown {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.empty-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-icon i {
    font-size: 3rem;
    color: white;
}

.empty-state h3 {
    color: #1E293B;
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.empty-state p {
    color: #64748B;
    margin-bottom: 30px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.feature-card {
    background: #F8FAFC;
    padding: 25px;
    border-radius: 12px;
    text-align: center;
    transition: transform 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.feature-card i {
    font-size: 2rem;
    color: #8B5CF6;
    margin-bottom: 15px;
}

.feature-card h4 {
    color: #1E293B;
    margin-bottom: 10px;
}

.feature-card p {
    color: #64748B;
    font-size: 0.9rem;
}

/* Quizzes Grid */
.quizzes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
}

.quiz-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    position: relative;
}

.quiz-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 60px rgba(139, 92, 246, 0.2);
}

.quiz-card.completed-quiz {
    border: 2px solid #10B981;
    background: linear-gradient(135deg, white, #F0FDF4);
}

.quiz-card.in-progress-quiz {
    border: 2px solid #F59E0B;
    background: linear-gradient(135deg, white, #FEF3C7);
}

.quiz-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 8px;
}

.quiz-subject {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
}

.quiz-class {
    background: #F1F5F9;
    color: #64748B;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.8rem;
}

.quiz-title {
    color: #1E293B;
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.quiz-description {
    color: #64748B;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 20px;
    flex: 1;
}

.quiz-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    font-size: 0.9rem;
    color: #64748B;
    flex-wrap: wrap;
}

.quiz-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.quiz-meta i {
    color: #8B5CF6;
}

/* Progress */
.quiz-progress {
    margin-bottom: 20px;
    padding: 15px 0;
    border-top: 1px solid #E2E8F0;
    border-bottom: 1px solid #E2E8F0;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 0.85rem;
    color: #64748B;
    flex-wrap: wrap;
    gap: 5px;
}

.progress-header strong {
    color: #1E293B;
}

.progress-bar {
    height: 6px;
    background: #E2E8F0;
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.3s ease;
}

.passed-badge {
    margin-top: 8px;
    font-size: 0.75rem;
    color: #10B981;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Actions */
.quiz-actions {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.btn-start, .btn-resume {
    flex: 1;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
    text-decoration: none;
    padding: 12px 20px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-resume {
    background: linear-gradient(135deg, #F59E0B, #D97706);
}

.btn-start:hover, .btn-resume:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);
}

.btn-results {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #F1F5F9;
    color: #8B5CF6;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    font-size: 1.1rem;
}

.btn-results:hover {
    background: #8B5CF6;
    color: white;
    transform: rotate(15deg);
}

.quiz-expired, .quiz-completed-badge, .quiz-inprogress-badge {
    flex: 1;
    padding: 12px;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 600;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.quiz-expired {
    background: #FEF2F2;
    color: #B91C1C;
}

.quiz-completed-badge {
    background: #F0FDF4;
    color: #166534;
}

.quiz-inprogress-badge {
    background: #FEF3C7;
    color: #92400E;
}

.one-time-warning {
    margin-top: 12px;
    padding: 8px 12px;
    background: #FEF3C7;
    border-radius: 8px;
    font-size: 0.75rem;
    color: #92400E;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Responsive */
@media (max-width: 768px) {
    .quizzes-grid {
        grid-template-columns: 1fr;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .quiz-meta {
        flex-direction: column;
        gap: 8px;
    }
    
    .quiz-header {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .page-title {
        font-size: 2rem;
    }
    
    .progress-header {
        flex-direction: column;
        gap: 5px;
    }
    
    .quiz-actions {
        flex-direction: column;
    }
    
    .btn-results {
        width: 100%;
        border-radius: 50px;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .quiz-card {
        background: #1E293B;
    }
    
    .quiz-card.completed-quiz {
        background: linear-gradient(135deg, #1E293B, #1A4731);
    }
    
    .quiz-card.in-progress-quiz {
        background: linear-gradient(135deg, #1E293B, #332411);
    }
    
    .quiz-title {
        color: #F1F5F9;
    }
    
    .quiz-class {
        background: #334155;
        color: #94A3B8;
    }
    
    .empty-state {
        background: #1E293B;
    }
    
    .feature-card {
        background: #334155;
    }
    
    .feature-card h4 {
        color: #F1F5F9;
    }
    
    .btn-results {
        background: #334155;
        color: #94A3B8;
    }
    
    .btn-results:hover {
        background: #8B5CF6;
        color: white;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>