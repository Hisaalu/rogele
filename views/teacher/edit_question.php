<?php
// File: /views/teacher/edit_question.php
$pageTitle = 'Edit Question | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$question = $question ?? [];
$quiz = $quiz ?? [];
?>

<div class="edit-question-container">
    <div class="page-header">
        <div>
            <a href="<?php echo BASE_URL; ?>/teacher/quizzes/edit/<?php echo $quiz['id']; ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Quiz
            </a>
            <h1 class="page-title">
                <i class="fas fa-edit"></i>
                Edit Question
            </h1>
            <p class="page-subtitle">Editing question for: <?php echo htmlspecialchars($quiz['title']); ?></p>
        </div>
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

    <div class="form-card">
        <form method="POST" action="<?php echo BASE_URL; ?>/teacher/quizzes/edit-question/<?php echo $question['id']; ?>">
            <div class="form-group">
                <label for="question">Question Text <span class="required">*</span></label>
                <textarea id="question" name="question" rows="3" required><?php echo htmlspecialchars($question['question'] ?? ''); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="option_a">Option A <span class="required">*</span></label>
                    <input type="text" id="option_a" name="option_a" value="<?php echo htmlspecialchars($question['option_a'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="option_b">Option B <span class="required">*</span></label>
                    <input type="text" id="option_b" name="option_b" value="<?php echo htmlspecialchars($question['option_b'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="option_c">Option C</label>
                    <input type="text" id="option_c" name="option_c" value="<?php echo htmlspecialchars($question['option_c'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="option_d">Option D</label>
                    <input type="text" id="option_d" name="option_d" value="<?php echo htmlspecialchars($question['option_d'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="correct_answer">Correct Answer <span class="required">*</span></label>
                    <select id="correct_answer" name="correct_answer" required>
                        <option value="">Select correct answer</option>
                        <option value="A" <?php echo ($question['correct_answer'] ?? '') == 'A' ? 'selected' : ''; ?>>A</option>
                        <option value="B" <?php echo ($question['correct_answer'] ?? '') == 'B' ? 'selected' : ''; ?>>B</option>
                        <option value="C" <?php echo ($question['correct_answer'] ?? '') == 'C' ? 'selected' : ''; ?>>C</option>
                        <option value="D" <?php echo ($question['correct_answer'] ?? '') == 'D' ? 'selected' : ''; ?>>D</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="points">Points</label>
                    <input type="number" id="points" name="points" value="<?php echo $question['points'] ?? 1; ?>" min="1" max="10">
                </div>
            </div>

            <div class="form-group">
                <label for="explanation">Explanation (Optional)</label>
                <textarea id="explanation" name="explanation" rows="2" placeholder="Explain why this answer is correct..."><?php echo htmlspecialchars($question['explanation'] ?? ''); ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Update Question
                </button>
                <a href="<?php echo BASE_URL; ?>/teacher/quizzes/edit/<?php echo $quiz['id']; ?>" class="btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.edit-question-container {
    max-width: 800px;
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
    color: #8B5CF6;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #7f2677);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-subtitle {
    color: black;
    font-size: 1rem;
    margin-bottom: 30px;
}

.form-card {
    background: white;
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
}

.form-group {
    margin-bottom: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 0.95rem;
    color: #1E293B;
    margin-bottom: 8px;
}

.required {
    color: #EF4444;
    margin-left: 3px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #8B5CF6;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn-primary {
    flex: 1;
    background: linear-gradient(135deg, #7f2677);
    color: white;
    border: none;
    padding: 14px 30px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);
}

.btn-secondary {
    padding: 14px 30px;
    background: white;
    color: #7f2677;
    border: 2px solid #E2E8F0;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-secondary:hover {
    background: #f06724;
    border-color: #94A3B8;
    color: white;
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

@media (max-width: 768px) {
    .form-card {
        padding: 25px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
}

@media (prefers-color-scheme: dark) {
    .form-card {
        background: #1E293B;
    }
    
    .form-group label {
        color: #F1F5F9;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        background: #0F172A;
        border-color: #334155;
        color: #F1F5F9;
    }
    
    .btn-secondary {
        background: transparent;
        color: #94A3B8;
        border-color: #334155;
    }
    
    .btn-secondary:hover {
        background: #334155;
        color: #F1F5F9;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>