<?php
//File: views/teacher/preview_quiz.php
$pageTitle = 'Preview: ' . ($quiz['title'] ?? 'Quiz') . ' - Teacher - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="preview-container">
    <div class="preview-header">
        <div class="header-content">
            <h1><i class="fas fa-eye"></i> Preview: <?php echo htmlspecialchars($quiz['title'] ?? 'Quiz'); ?></h1>
            <p class="subtitle">This is how students will see the quiz</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo BASE_URL; ?>/teacher/quizzes" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Quizzes
            </a>
            <a href="<?php echo BASE_URL; ?>/teacher/quizzes/edit/<?php echo $quiz['id']; ?>" class="btn-edit">
                <i class="fas fa-edit"></i> Edit Quiz
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <?php if (empty($questions)): ?>
        <div class="no-questions">
            <div class="no-questions-icon">
                <i class="fas fa-question-circle"></i>
            </div>
            <h3>No Questions Added Yet</h3>
            <p>This quiz doesn't have any questions. Add questions to preview.</p>
            <a href="<?php echo BASE_URL; ?>/teacher/quizzes/edit/<?php echo $quiz['id']; ?>" class="btn-add-questions">
                <i class="fas fa-plus"></i> Add Questions
            </a>
        </div>
    <?php else: ?>
        <!-- Quiz Info Card -->
        <div class="quiz-info-card">
            <div class="quiz-stats">
                <div class="stat">
                    <i class="fas fa-question-circle"></i>
                    <span><?php echo count($questions); ?> Questions</span>
                </div>
                <div class="stat">
                    <i class="fas fa-clock"></i>
                    <span><?php echo $quiz['time_limit'] ?? 'N/A'; ?> minutes</span>
                </div>
                <div class="stat">
                    <i class="fas fa-trophy"></i>
                    <span><?php echo $quiz['passing_score'] ?? 'N/A'; ?>% to pass</span>
                </div>
                <div class="stat">
                    <i class="fas fa-redo-alt"></i>
                    <span><?php echo $quiz['max_attempts'] ?? 'N/A'; ?> attempts allowed</span>
                </div>
            </div>
            <?php if (!empty($quiz['description'])): ?>
                <div class="quiz-description">
                    <strong>Description:</strong>
                    <p><?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Questions Preview -->
        <div class="questions-preview">
            <h2><i class="fas fa-list"></i> Questions (<?php echo count($questions); ?>)</h2>
            
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-preview-card">
                    <div class="question-header">
                        <span class="question-number">Question <?php echo $index + 1; ?></span>
                        <span class="question-points"><?php echo $question['points'] ?? 1; ?> point(s)</span>
                    </div>
                    
                    <div class="question-text">
                        <?php echo htmlspecialchars($question['question_text']); ?>
                    </div>
                    
                    <div class="options-preview">
                        <?php 
                        $letters = ['A', 'B', 'C', 'D'];
                        $options = $question['options'] ?? [];
                        $correctIndex = $question['correct_option'] ?? 0;
                        
                        foreach ($options as $optIndex => $option):
                            $isCorrect = ($optIndex == $correctIndex);
                        ?>
                            <div class="option-preview <?php echo $isCorrect ? 'correct-option' : ''; ?>">
                                <span class="option-letter"><?php echo $letters[$optIndex]; ?></span>
                                <span class="option-text"><?php echo htmlspecialchars($option); ?></span>
                                <?php if ($isCorrect): ?>
                                    <span class="correct-badge"><i class="fas fa-check-circle"></i> Correct Answer</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (!empty($question['explanation'])): ?>
                        <div class="explanation">
                            <strong><i class="fas fa-lightbulb"></i> Explanation:</strong>
                            <p><?php echo htmlspecialchars($question['explanation']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Preview Actions -->
        <div class="preview-actions">
            <div class="preview-note">
                <i class="fas fa-info-circle"></i>
                <span>This is a preview. Students will see the quiz in a similar format but with interactive elements and timer.</span>
            </div>
            <div class="action-buttons">
                <a href="<?php echo BASE_URL; ?>/teacher/quizzes/edit/<?php echo $quiz['id']; ?>" class="btn-edit-questions">
                    <i class="fas fa-edit"></i> Edit Questions
                </a>
                <a href="<?php echo BASE_URL; ?>/teacher/quizzes" class="btn-done">
                    <i class="fas fa-check"></i> Done
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.preview-container {
    max-width: 900px;
    margin: 40px auto;
    padding: 0 20px;
}

.preview-header {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.preview-header h1 {
    color: #1E293B;
    font-size: 1.8rem;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.preview-header h1 i {
    color: #8B5CF6;
}

.subtitle {
    color: #64748B;
    font-size: 0.9rem;
}

.header-actions {
    display: flex;
    gap: 15px;
}

.btn-back, .btn-edit {
    padding: 10px 20px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-back {
    background: #F1F5F9;
    color: #1E293B;
}

.btn-edit {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
}

.btn-back:hover, .btn-edit:hover {
    transform: translateY(-2px);
}

.quiz-info-card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.quiz-stats {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #E2E8F0;
}

.stat {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #64748B;
}

.stat i {
    color: #8B5CF6;
}

.quiz-description {
    color: #1E293B;
    line-height: 1.6;
}

.quiz-description p {
    margin-top: 8px;
    color: #64748B;
}

.questions-preview {
    margin-bottom: 30px;
}

.questions-preview h2 {
    color: #1E293B;
    font-size: 1.3rem;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.question-preview-card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.question-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #E2E8F0;
}

.question-number {
    font-weight: 600;
    color: #8B5CF6;
}

.question-points {
    color: #64748B;
    font-size: 0.85rem;
}

.question-text {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1E293B;
    margin-bottom: 20px;
}

.options-preview {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 15px;
}

.option-preview {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 15px;
    background: #F8FAFC;
    border-radius: 10px;
    position: relative;
}

.option-preview.correct-option {
    background: #F0FDF4;
    border: 1px solid #10B981;
}

.option-letter {
    width: 28px;
    height: 28px;
    background: white;
    border: 2px solid #CBD5E0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #64748B;
}

.correct-option .option-letter {
    background: #10B981;
    border-color: #10B981;
    color: white;
}

.option-text {
    flex: 1;
    color: #1E293B;
}

.correct-badge {
    font-size: 0.75rem;
    color: #10B981;
    background: #F0FDF4;
    padding: 4px 8px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.explanation {
    margin-top: 15px;
    padding: 12px;
    background: #FEF3C7;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #92400E;
}

.explanation strong {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 5px;
}

.no-questions {
    text-align: center;
    padding: 60px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.no-questions-icon {
    width: 80px;
    height: 80px;
    background: #F1F5F9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.no-questions-icon i {
    font-size: 2.5rem;
    color: #8B5CF6;
}

.no-questions h3 {
    color: #1E293B;
    margin-bottom: 10px;
}

.no-questions p {
    color: #64748B;
    margin-bottom: 25px;
}

.btn-add-questions {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
    padding: 12px 25px;
    border-radius: 10px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
}

.preview-actions {
    background: white;
    border-radius: 16px;
    padding: 25px;
    margin-top: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.preview-note {
    background: #EFF6FF;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #1E40AF;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.btn-edit-questions, .btn-done {
    padding: 12px 30px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-edit-questions {
    background: #F1F5F9;
    color: #1E293B;
}

.btn-done {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
}

.btn-edit-questions:hover, .btn-done:hover {
    transform: translateY(-2px);
}

.alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert-error {
    background: #FEF2F2;
    color: #B91C1C;
    border: 1px solid #FECACA;
}

@media (max-width: 768px) {
    .preview-header {
        flex-direction: column;
        text-align: center;
    }
    
    .quiz-stats {
        justify-content: center;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .header-actions {
        width: 100%;
    }
    
    .header-actions a {
        flex: 1;
        text-align: center;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>