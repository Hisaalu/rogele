<?php
// File: /views/admin/view_quiz.php
$pageTitle = 'View Quiz | ROGELE';
require_once __DIR__ . '/../layouts/admin_header.php';

$quiz = $quiz ?? [];
$questions = $quiz['questions'] ?? [];
$optionsList = ['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'];
?>

<style>
:root {
    --primary-purple: #7f2677;
    --accent-purple: #8B5CF6;
    --accent-orange: #f06724;
    --text-dark: #000;
    --text-muted: #555;
    --bg-surface: #FFFFFF;
    --border-color: #E2E8F0;
    --radius-lg: 20px;
    --radius-md: 12px;
    --shadow-md: 0 10px 30px rgba(0, 0, 0, 0.05);
    --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.view-quiz-container,
.view-quiz-container * {
    box-sizing: border-box;
}

.view-quiz-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: clamp(16px, 3vw, 32px);
    color: var(--text-dark);
}

.page-header {
    margin-bottom: clamp(20px, 3vw, 30px);
}

.page-title {
    font-size: clamp(1.5rem, 3.5vw, 2.2rem);
    font-weight: 700;
    color: var(--primary-purple);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.quiz-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    padding: clamp(20px, 4vw, 40px);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
}

.quiz-header {
    margin-bottom: clamp(20px, 3vw, 30px);
    padding-bottom: clamp(16px, 2.5vw, 24px);
    border-bottom: 2px solid #F1F5F9;
}

.quiz-header h2 {
    color: var(--text-dark);
    font-size: clamp(1.3rem, 3vw, 1.8rem);
    font-weight: 700;
    margin: 0 0 16px 0;
    line-height: 1.3;
}

.quiz-meta {
    display: flex;
    flex-wrap: wrap;
    gap: clamp(12px, 2vw, 20px);
    margin-bottom: 16px;
}

.meta-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--text-dark);
    font-size: 0.9rem;
}

.meta-item i {
    color: var(--accent-orange);
}

.meta-item strong {
    color: var(--text-muted);
    font-weight: 600;
}

.quiz-status {
    display: flex;
    gap: 10px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-badge.published { background: #F0FDF4; color: #166534; }
.status-badge.draft { background: #F1F5F9; color: var(--text-muted); }

.quiz-description {
    margin-bottom: clamp(20px, 3vw, 30px);
    padding: clamp(16px, 2.5vw, 20px);
    background: #F8FAFC;
    border-radius: var(--radius-md);
    border: 1px solid #F1F5F9;
}

.quiz-description h3 {
    color: var(--text-dark);
    font-size: 0.95rem;
    font-weight: 700;
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 6px;
}

.quiz-description h3 i {
    color: var(--accent-orange);
}

.quiz-description p {
    color: var(--text-muted);
    line-height: 1.6;
    margin: 0;
    font-size: 0.95rem;
}

.section-title {
    color: var(--text-dark);
    font-size: 1.2rem;
    font-weight: 700;
    margin: 0 0 20px 0;
}

.no-questions {
    text-align: center;
    padding: 40px 20px;
    background: #F8FAFC;
    border-radius: var(--radius-md);
    color: var(--text-muted);
    border: 1px dashed var(--border-color);
}

.no-questions i {
    font-size: 2rem;
    margin-bottom: 10px;
    color: #94A3B8;
}

.no-questions p {
    margin: 0;
}

.questions-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.question-item {
    background: #F8FAFC;
    border-radius: var(--radius-md);
    padding: clamp(16px, 2.5vw, 24px);
    border-left: 4px solid var(--accent-purple);
    border-top: 1px solid #F1F5F9;
    border-right: 1px solid #F1F5F9;
    border-bottom: 1px solid #F1F5F9;
}

.question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.question-number {
    font-weight: 700;
    color: var(--accent-purple);
    font-size: 0.95rem;
}

.question-points {
    background: #FFEDD5;
    color: #C2410C;
    padding: 3px 10px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
}

.question-text {
    color: var(--text-dark);
    font-weight: 600;
    margin: 0 0 16px 0;
    font-size: 1rem;
    line-height: 1.5;
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
    padding: 10px 14px;
    background: var(--bg-surface);
    border-radius: 8px;
    border: 1px solid var(--border-color);
    transition: var(--transition);
}

.option.correct {
    background: #F0FDF4;
    border-color: #10B981;
}

.option-letter {
    width: 26px;
    height: 26px;
    background: #475569;
    color: white;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.option.correct .option-letter {
    background: #10B981;
}

.option-text {
    flex: 1;
    color: var(--text-dark);
    font-size: 0.95rem;
    word-break: break-word;
}

.correct-badge {
    color: #166534;
    font-weight: 600;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 4px;
    background: #DCFCE7;
    padding: 2px 8px;
    border-radius: 4px;
    flex-shrink: 0;
}

.admin-actions {
    display: flex;
    gap: 15px;
    margin-top: clamp(24px, 3vw, 32px);
    padding-top: clamp(20px, 3vw, 28px);
    border-top: 2px solid #F1F5F9;
}

.btn-delete {
    flex: 1;
    padding: 12px 20px;
    background: #EF4444;
    color: white;
    border: none;
    border-radius: var(--radius-md);
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: var(--transition);
    cursor: pointer;
}

.btn-delete:hover {
    background: #DC2626;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(239, 68, 68, 0.25);
}

@media (max-width: 600px) {
    .quiz-meta {
        flex-direction: column;
        gap: 8px;
    }

    .admin-actions {
        flex-direction: column;
    }
}
</style>

<div class="view-quiz-container">
    <header class="page-header">
        <div class="header-text">
            <h1 class="page-title">
                <i class="fas fa-pencil-alt" aria-hidden="true"></i>
                <span>View Quiz</span>
            </h1>
        </div>
    </header>

    <main class="quiz-card">
        <div class="quiz-header">
            <h2><?php echo htmlspecialchars($quiz['title'] ?? 'Untitled Quiz'); ?></h2>
            <div class="quiz-meta">
                <span class="meta-item">
                    <i class="fas fa-user" aria-hidden="true"></i>
                    <strong>Teacher:</strong> <?php echo htmlspecialchars($quiz['teacher_name'] ?? 'Unknown'); ?>
                </span>
                <span class="meta-item">
                    <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                    <strong>Class:</strong> <?php echo htmlspecialchars($quiz['class_name'] ?? 'All Levels'); ?>
                </span>
                <span class="meta-item">
                    <i class="fas fa-book" aria-hidden="true"></i>
                    <strong>Subject:</strong> <?php echo htmlspecialchars($quiz['subject_name'] ?? 'General'); ?>
                </span>
                <span class="meta-item">
                    <i class="fas fa-clock" aria-hidden="true"></i>
                    <strong>Time Limit:</strong> <?php echo htmlspecialchars((string)($quiz['time_limit'] ?? 30)); ?> minutes
                </span>
                <span class="meta-item">
                    <i class="fas fa-trophy" aria-hidden="true"></i>
                    <strong>Pass Score:</strong> <?php echo htmlspecialchars((string)($quiz['passing_score'] ?? 50)); ?>%
                </span>
                <span class="meta-item">
                    <i class="fas fa-calendar" aria-hidden="true"></i>
                    <strong>Created:</strong> <?php echo !empty($quiz['created_at']) ? date('M d, Y h:i A', strtotime($quiz['created_at'])) : 'N/A'; ?>
                </span>
            </div>
            <div class="quiz-status">
                <span class="status-badge <?php echo !empty($quiz['is_published']) ? 'published' : 'draft'; ?>">
                    <i class="fas <?php echo !empty($quiz['is_published']) ? 'fa-globe' : 'fa-pencil-alt'; ?>" aria-hidden="true"></i>
                    <?php echo !empty($quiz['is_published']) ? 'Published' : 'Draft'; ?>
                </span>
            </div>
        </div>

        <?php if (!empty($quiz['description'])): ?>
        <section class="quiz-description">
            <h3><i class="fas fa-align-left" aria-hidden="true"></i> Description</h3>
            <p><?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>
        </section>
        <?php endif; ?>

        <section class="questions-section">
            <h3 class="section-title">Questions (<?php echo count($questions); ?>)</h3>
            
            <?php if (empty($questions)): ?>
                <div class="no-questions">
                    <i class="fas fa-folder-open" aria-hidden="true"></i>
                    <p>No questions added to this quiz yet.</p>
                </div>
            <?php else: ?>
                <div class="questions-list">
                    <?php foreach ($questions as $index => $question): ?>
                        <article class="question-item">
                            <div class="question-header">
                                <span class="question-number">Question <?php echo $index + 1; ?></span>
                                <span class="question-points"><?php echo htmlspecialchars((string)($question['points'] ?? 1)); ?> pts</span>
                            </div>
                            <p class="question-text"><?php echo htmlspecialchars($question['question'] ?? ''); ?></p>
                            
                            <div class="options-list">
                                <?php foreach ($optionsList as $letter => $key): ?>
                                    <?php if (!empty($question[$key])): ?>
                                        <?php $isCorrect = strtoupper($question['correct_answer'] ?? '') === $letter; ?>
                                        <div class="option <?php echo $isCorrect ? 'correct' : ''; ?>">
                                            <span class="option-letter"><?php echo $letter; ?></span>
                                            <span class="option-text"><?php echo htmlspecialchars($question[$key]); ?></span>
                                            <?php if ($isCorrect): ?>
                                                <span class="correct-badge"><i class="fas fa-check-circle" aria-hidden="true"></i> Correct</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <div class="admin-actions">
            <a href="<?php echo BASE_URL; ?>/admin/quizzes/delete/<?php echo urlencode($quiz['id'] ?? ''); ?>" 
               class="btn-delete" 
               onclick="return confirm('Are you sure you want to delete this quiz? This action cannot be undone.')">
                <i class="fas fa-trash-alt" aria-hidden="true"></i> Delete Quiz
            </a>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>