<?php
// File: /views/teacher/preview_quiz.php
$pageTitle = 'Preview Quiz - Teacher - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

$quiz = $quiz ?? [];
$questions = $quiz['questions'] ?? [];
?>

<div class="preview-container">
    <!-- Header -->
    <div class="preview-header">
        <h1 class="page-title">
            <i class="fas fa-eye"></i>
            Quiz Preview
        </h1>
        <a href="<?php echo BASE_URL; ?>/teacher/quizzes" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Quizzes
        </a>
    </div>

    <div class="preview-card">
        <div class="preview-badge">Preview Mode</div>
        
        <!-- Quiz Info -->
        <div class="quiz-info">
            <h2 class="quiz-title"><?php echo htmlspecialchars($quiz['title'] ?? ''); ?></h2>
            
            <div class="quiz-meta">
                <span><i class="fas fa-graduation-cap"></i> <?php echo $quiz['class_name'] ?? 'All Classes'; ?></span>
                <span><i class="fas fa-book"></i> <?php echo $quiz['subject_name'] ?? 'General'; ?></span>
                <span><i class="fas fa-clock"></i> <?php echo $quiz['time_limit'] ?? 30; ?> minutes</span>
                <span><i class="fas fa-question-circle"></i> <?php echo count($questions); ?> questions</span>
                <span><i class="fas fa-trophy"></i> Pass: <?php echo $quiz['passing_score'] ?? 50; ?>%</span>
            </div>

            <?php if (!empty($quiz['description'])): ?>
            <p class="quiz-description"><?php echo htmlspecialchars($quiz['description']); ?></p>
            <?php endif; ?>
        </div>

        <!-- Questions -->
        <?php if (empty($questions)): ?>
            <div class="empty-questions">
                <i class="fas fa-pencil-alt"></i>
                <h3>No Questions Added Yet</h3>
                <p>This quiz doesn't have any questions. <a href="<?php echo BASE_URL; ?>/teacher/quizzes/add-questions/<?php echo $quiz['id']; ?>">Add questions now</a>.</p>
            </div>
        <?php else: ?>
            <div class="questions-list">
                <?php foreach ($questions as $index => $question): ?>
                <div class="question-card">
                    <div class="question-header">
                        <span class="question-number">Question <?php echo $index + 1; ?></span>
                        <span class="question-points"><?php echo $question['points']; ?> points</span>
                    </div>
                    
                    <p class="question-text"><?php echo htmlspecialchars($question['question']); ?></p>
                    
                    <div class="options-list">
                        <div class="option-item <?php echo $question['correct_answer'] == 'A' ? 'correct' : ''; ?>">
                            <span class="option-letter">A</span>
                            <span class="option-text"><?php echo htmlspecialchars($question['option_a']); ?></span>
                            <?php if ($question['correct_answer'] == 'A'): ?>
                                <span class="correct-badge"><i class="fas fa-check"></i> Correct</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="option-item <?php echo $question['correct_answer'] == 'B' ? 'correct' : ''; ?>">
                            <span class="option-letter">B</span>
                            <span class="option-text"><?php echo htmlspecialchars($question['option_b']); ?></span>
                            <?php if ($question['correct_answer'] == 'B'): ?>
                                <span class="correct-badge"><i class="fas fa-check"></i> Correct</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($question['option_c'])): ?>
                        <div class="option-item <?php echo $question['correct_answer'] == 'C' ? 'correct' : ''; ?>">
                            <span class="option-letter">C</span>
                            <span class="option-text"><?php echo htmlspecialchars($question['option_c']); ?></span>
                            <?php if ($question['correct_answer'] == 'C'): ?>
                                <span class="correct-badge"><i class="fas fa-check"></i> Correct</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($question['option_d'])): ?>
                        <div class="option-item <?php echo $question['correct_answer'] == 'D' ? 'correct' : ''; ?>">
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

        <!-- Actions -->
        <div class="preview-actions">
            <a href="<?php echo BASE_URL; ?>/teacher/quizzes/edit/<?php echo $quiz['id']; ?>" class="btn-edit">
                <i class="fas fa-edit"></i> Edit Quiz
            </a>
            <a href="<?php echo BASE_URL; ?>/teacher/quizzes/add-questions/<?php echo $quiz['id']; ?>" class="btn-add">
                <i class="fas fa-plus-circle"></i> Add/Edit Questions
            </a>
        </div>
    </div>
</div>

<style>
.preview-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 30px 20px;
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.back-link {
    color: #64748B;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.back-link:hover {
    background: #F1F5F9;
    color: #8B5CF6;
}

.preview-card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    position: relative;
}

.preview-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: #F97316;
    color: white;
    padding: 5px 15px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.quiz-info {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 2px solid #F1F5F9;
}

.quiz-title {
    color: #1E293B;
    font-size: 2rem;
    margin-bottom: 20px;
    padding-right: 100px;
}

.quiz-meta {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.quiz-meta span {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #64748B;
    font-size: 0.95rem;
}

.quiz-meta i {
    color: #8B5CF6;
}

.quiz-description {
    color: #475569;
    line-height: 1.6;
}

/* Questions */
.questions-list {
    display: flex;
    flex-direction: column;
    gap: 25px;
    margin-bottom: 30px;
}

.question-card {
    background: #F8FAFC;
    border-radius: 16px;
    padding: 25px;
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
    font-size: 1.1rem;
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
    font-size: 1.1rem;
    font-weight: 500;
    margin-bottom: 20px;
    line-height: 1.6;
}

.options-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.option-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: white;
    border-radius: 12px;
    border: 2px solid #E2E8F0;
    transition: all 0.3s ease;
}

.option-item.correct {
    background: #F0FDF4;
    border-color: #10B981;
}

.option-letter {
    width: 30px;
    height: 30px;
    background: #8B5CF6;
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    flex-shrink: 0;
}

.option-text {
    flex: 1;
    color: #1E293B;
}

.correct-badge {
    color: #10B981;
    font-weight: 600;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Empty State */
.empty-questions {
    text-align: center;
    padding: 60px 20px;
}

.empty-questions i {
    font-size: 4rem;
    color: #CBD5E1;
    margin-bottom: 20px;
}

.empty-questions h3 {
    color: #1E293B;
    font-size: 1.3rem;
    margin-bottom: 10px;
}

.empty-questions p {
    color: #64748B;
}

.empty-questions a {
    color: #8B5CF6;
    text-decoration: none;
    font-weight: 600;
}

.empty-questions a:hover {
    text-decoration: underline;
}

/* Preview Actions */
.preview-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 2px solid #F1F5F9;
}

.btn-edit,
.btn-add {
    flex: 1;
    padding: 14px 20px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-edit {
    background: #EFF6FF;
    color: #2563EB;
    border: 2px solid transparent;
}

.btn-edit:hover {
    background: #2563EB;
    color: white;
    transform: translateY(-2px);
}

.btn-add {
    background: #8B5CF6;
    color: white;
    border: 2px solid transparent;
}

.btn-add:hover {
    background: #7C3AED;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);
}

/* Responsive */
@media (max-width: 768px) {
    .preview-card {
        padding: 25px;
    }
    
    .quiz-title {
        font-size: 1.5rem;
        padding-right: 0;
    }
    
    .preview-badge {
        position: static;
        display: inline-block;
        margin-bottom: 15px;
    }
    
    .quiz-meta {
        flex-direction: column;
        gap: 10px;
    }
    
    .preview-actions {
        flex-direction: column;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .preview-card {
        background: #1E293B;
    }
    
    .quiz-title {
        color: #F1F5F9;
    }
    
    .quiz-description {
        color: #94A3B8;
    }
    
    .question-card {
        background: #334155;
    }
    
    .question-text {
        color: #F1F5F9;
    }
    
    .option-item {
        background: #1E293B;
        border-color: #475569;
    }
    
    .option-text {
        color: #F1F5F9;
    }
    
    .option-item.correct {
        background: #1E3A5F;
        border-color: #3B82F6;
    }
    
    .btn-edit {
        background: #334155;
        color: #8B5CF6;
    }
    
    .btn-edit:hover {
        background: #8B5CF6;
        color: white;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>