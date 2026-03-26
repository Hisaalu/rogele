<?php
// File: /views/admin/view_quiz.php
$pageTitle = 'View Quiz - Admin - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

$quiz = $quiz ?? [];
$questions = $quiz['questions'] ?? [];
?>

<div class="view-quiz-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <a href="<?php echo BASE_URL; ?>/admin/quizzes" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Quizzes
            </a>
            <h1 class="page-title">
                <i class="fas fa-pencil-alt"></i>
                View Quiz
            </h1>
        </div>
    </div>

    <!-- Quiz Details -->
    <div class="quiz-card">
        <div class="quiz-header">
            <h2><?php echo htmlspecialchars($quiz['title'] ?? ''); ?></h2>
            <div class="quiz-meta">
                <span class="meta-item">
                    <i class="fas fa-user"></i>
                    Teacher: <?php echo htmlspecialchars($quiz['teacher_name'] ?? 'Unknown'); ?>
                </span>
                <span class="meta-item">
                    <i class="fas fa-graduation-cap"></i>
                    Class: <?php echo htmlspecialchars($quiz['class_name'] ?? 'All Levels'); ?>
                </span>
                <span class="meta-item">
                    <i class="fas fa-book"></i>
                    Subject: <?php echo htmlspecialchars($quiz['subject_name'] ?? 'General'); ?>
                </span>
                <span class="meta-item">
                    <i class="fas fa-clock"></i>
                    Time Limit: <?php echo $quiz['time_limit'] ?? 30; ?> minutes
                </span>
                <span class="meta-item">
                    <i class="fas fa-trophy"></i>
                    Pass Score: <?php echo $quiz['passing_score'] ?? 50; ?>%
                </span>
                <span class="meta-item">
                    <i class="fas fa-calendar"></i>
                    Created: <?php echo date('M d, Y H:i', strtotime($quiz['created_at'] ?? 'now')); ?>
                </span>
            </div>
            <div class="quiz-status">
                <span class="status-badge <?php echo $quiz['is_published'] ? 'published' : 'draft'; ?>">
                    <?php echo $quiz['is_published'] ? 'Published' : 'Draft'; ?>
                </span>
            </div>
        </div>

        <?php if (!empty($quiz['description'])): ?>
        <div class="quiz-description">
            <h3>Description</h3>
            <p><?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>
        </div>
        <?php endif; ?>

        <!-- Questions Section -->
        <div class="questions-section">
            <h3>Questions (<?php echo count($questions); ?>)</h3>
            
            <?php if (empty($questions)): ?>
                <p class="no-questions">No questions added to this quiz yet.</p>
            <?php else: ?>
                <div class="questions-list">
                    <?php foreach ($questions as $index => $question): ?>
                    <div class="question-item">
                        <div class="question-header">
                            <span class="question-number">Question <?php echo $index + 1; ?></span>
                            <span class="question-points"><?php echo $question['points']; ?> points</span>
                        </div>
                        <p class="question-text"><?php echo htmlspecialchars($question['question']); ?></p>
                        
                        <div class="options-list">
                            <div class="option <?php echo $question['correct_answer'] == 'A' ? 'correct' : ''; ?>">
                                <span class="option-letter">A</span>
                                <span class="option-text"><?php echo htmlspecialchars($question['option_a']); ?></span>
                                <?php if ($question['correct_answer'] == 'A'): ?>
                                    <span class="correct-badge"><i class="fas fa-check"></i> Correct</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="option <?php echo $question['correct_answer'] == 'B' ? 'correct' : ''; ?>">
                                <span class="option-letter">B</span>
                                <span class="option-text"><?php echo htmlspecialchars($question['option_b']); ?></span>
                                <?php if ($question['correct_answer'] == 'B'): ?>
                                    <span class="correct-badge"><i class="fas fa-check"></i> Correct</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($question['option_c'])): ?>
                            <div class="option <?php echo $question['correct_answer'] == 'C' ? 'correct' : ''; ?>">
                                <span class="option-letter">C</span>
                                <span class="option-text"><?php echo htmlspecialchars($question['option_c']); ?></span>
                                <?php if ($question['correct_answer'] == 'C'): ?>
                                    <span class="correct-badge"><i class="fas fa-check"></i> Correct</span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($question['option_d'])): ?>
                            <div class="option <?php echo $question['correct_answer'] == 'D' ? 'correct' : ''; ?>">
                                <span class="option-letter">D</span>
                                <span class="option-text"><?php echo htmlspecialchars($question['option_d']); ?></span>
                                <?php if ($question['correct_answer'] == 'D'): ?>
                                    <span class="correct-badge"><i class="fas fa-check"></i> Correct</span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Admin Actions -->
        <div class="admin-actions">
            <a href="<?php echo BASE_URL; ?>/admin/quizzes/delete/<?php echo $quiz['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this quiz? This action cannot be undone.')">
                <i class="fas fa-trash"></i> Delete Quiz
            </a>
        </div>
    </div>
</div>

<style>
.view-quiz-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 30px 20px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: black;
    text-decoration: none;
    font-size: 0.95rem;
    margin-bottom: 15px;
    transition: color 0.3s ease;
}

.back-link:hover {
    color: #7f2677;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #7f2677);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 30px;
}

.quiz-card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.quiz-header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #F1F5F9;
}

.quiz-header h2 {
    color: black;
    font-size: 2rem;
    margin-bottom: 15px;
}

.quiz-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 15px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: black;
    font-size: 0.95rem;
}

.meta-item i {
    color: #f06724;
}

.quiz-status {
    display: flex;
    gap: 10px;
}

.status-badge {
    display: inline-block;
    padding: 6px 15px;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-badge.published {
    background: #F0FDF4;
    color: #166534;
}

.status-badge.draft {
    background: #F1F5F9;
    color: #64748B;
}

.quiz-description {
    margin-bottom: 30px;
    padding: 20px;
    background: #F8FAFC;
    border-radius: 12px;
}

.quiz-description h3 {
    color: #1E293B;
    font-size: 1.1rem;
    margin-bottom: 10px;
}

.quiz-description p {
    color: #475569;
    line-height: 1.6;
}

.questions-section h3 {
    color: #1E293B;
    font-size: 1.2rem;
    margin-bottom: 20px;
}

.no-questions {
    text-align: center;
    padding: 40px;
    background: #F8FAFC;
    border-radius: 12px;
    color: #64748B;
}

.questions-list {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.question-item {
    background: #F8FAFC;
    border-radius: 12px;
    padding: 20px;
    border-left: 4px solid #8B5CF6;
}

.question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.question-number {
    font-weight: 600;
    color: #8B5CF6;
}

.question-points {
    background: #F97316;
    color: white;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
}

.question-text {
    color: #1E293B;
    font-weight: 500;
    margin-bottom: 15px;
    font-size: 1rem;
}

.options-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    background: white;
    border-radius: 8px;
    border: 1px solid #E2E8F0;
}

.option.correct {
    background: #F0FDF4;
    border-color: #10B981;
}

.option-letter {
    width: 28px;
    height: 28px;
    background: #8B5CF6;
    color: white;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
}

.option-text {
    flex: 1;
    color: #1E293B;
}

.correct-badge {
    color: #10B981;
    font-weight: 600;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 4px;
}

.admin-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 2px solid #F1F5F9;
}

.btn-delete {
    flex: 1;
    padding: 14px 20px;
    background: #EF4444;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1rem;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-delete:hover {
    background: #DC2626;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .quiz-card {
        padding: 25px;
    }
    
    .quiz-header h2 {
        font-size: 1.5rem;
    }
    
    .quiz-meta {
        flex-direction: column;
        gap: 10px;
    }
    
    .admin-actions {
        flex-direction: column;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .quiz-card {
        background: #1E293B;
    }
    
    .quiz-header h2,
    .questions-section h3 {
        color: #F1F5F9;
    }
    
    .quiz-description {
        background: #334155;
    }
    
    .quiz-description p {
        color: #94A3B8;
    }
    
    .question-item {
        background: #334155;
    }
    
    .question-text {
        color: #F1F5F9;
    }
    
    .option {
        background: #1E293B;
    }
    
    .option-text {
        color: #F1F5F9;
    }
    
    .no-questions {
        background: #334155;
        color: #94A3B8;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>