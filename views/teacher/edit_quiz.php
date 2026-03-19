<?php
// File: /views/teacher/edit_quiz.php
$pageTitle = 'Edit Quiz - Teacher - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

$quiz = $quiz ?? [];
$classes = $classes ?? [];
$subjects = $subjects ?? [];
$questions = $quiz['questions'] ?? [];
?>

<div class="edit-quiz-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <a href="<?php echo BASE_URL; ?>/teacher/quizzes" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Quizzes
            </a>
            <h1 class="page-title">
                <i class="fas fa-edit"></i>
                Edit Quiz
            </h1>
            <p class="page-subtitle">Editing: <?php echo htmlspecialchars($quiz['title'] ?? ''); ?></p>
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

    <!-- Edit Quiz Form -->
    <div class="form-card">
        <form method="POST" action="<?php echo BASE_URL; ?>/teacher/quizzes/edit/<?php echo $quiz['id']; ?>" class="quiz-form" id="quizForm">
            <!-- Basic Information -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Basic Information
                </h3>
                
                <div class="form-group">
                    <label for="title">
                        <i class="fas fa-heading"></i>
                        Quiz Title <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        required 
                        value="<?php echo htmlspecialchars($quiz['title'] ?? ''); ?>"
                        placeholder="e.g., Mathematics Quiz 1"
                    >
                </div>

                <div class="form-group">
                    <label for="description">
                        <i class="fas fa-align-left"></i>
                        Description
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="3" 
                        placeholder="Brief description of the quiz..."
                    ><?php echo htmlspecialchars($quiz['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="class_id">
                            <i class="fas fa-graduation-cap"></i>
                            Class <span class="required">*</span>
                        </label>
                        <select id="class_id" name="class_id" required onchange="filterSubjects()">
                            <option value="">Select a class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" 
                                    <?php echo ($class['id'] == ($quiz['class_id'] ?? '')) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subject_id">
                            <i class="fas fa-book"></i>
                            Subject <span class="required">*</span>
                        </label>
                        <select id="subject_id" name="subject_id" required>
                            <option value="">Select a subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>" 
                                        data-class="<?php echo $subject['class_id']; ?>"
                                        class="subject-option"
                                        <?php echo ($subject['id'] == ($quiz['subject_id'] ?? '')) ? 'selected' : ''; ?>
                                        style="<?php echo ($subject['class_id'] != ($quiz['class_id'] ?? '')) ? 'display: none;' : ''; ?>">
                                    <?php echo htmlspecialchars($subject['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Quiz Settings -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-cog"></i>
                    Quiz Settings
                </h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="time_limit">
                            <i class="fas fa-clock"></i>
                            Time Limit (minutes)
                        </label>
                        <input 
                            type="number" 
                            id="time_limit" 
                            name="time_limit" 
                            value="<?php echo htmlspecialchars($quiz['time_limit'] ?? 30); ?>" 
                            min="1" 
                            max="180"
                        >
                    </div>

                    <div class="form-group">
                        <label for="passing_score">
                            <i class="fas fa-trophy"></i>
                            Passing Score (%)
                        </label>
                        <input 
                            type="number" 
                            id="passing_score" 
                            name="passing_score" 
                            value="<?php echo htmlspecialchars($quiz['passing_score'] ?? 50); ?>" 
                            min="0" 
                            max="100"
                        >
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="max_attempts">
                            <i class="fas fa-redo"></i>
                            Maximum Attempts
                        </label>
                        <input 
                            type="number" 
                            id="max_attempts" 
                            name="max_attempts" 
                            value="<?php echo htmlspecialchars($quiz['max_attempts'] ?? 3); ?>" 
                            min="1" 
                            max="10"
                        >
                    </div>

                    <div class="form-group">
                        <label for="end_date">
                            <i class="fas fa-calendar"></i>
                            End Date (Optional)
                        </label>
                        <input 
                            type="datetime-local" 
                            id="end_date" 
                            name="end_date"
                            value="<?php echo !empty($quiz['end_date']) ? date('Y-m-d\TH:i', strtotime($quiz['end_date'])) : ''; ?>"
                        >
                    </div>
                </div>
            </div>

            <!-- Publishing Options -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-globe"></i>
                    Publishing Options
                </h3>
                
                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_published" value="1" 
                            <?php echo ($quiz['is_published'] ?? 0) ? 'checked' : ''; ?>>
                        <span>Publish immediately</span>
                    </label>
                    <small class="input-hint">Uncheck to save as draft</small>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    Update Quiz
                </button>
                <a href="<?php echo BASE_URL; ?>/teacher/quizzes" class="btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Questions Section -->
    <?php if (!empty($questions)): ?>
    <div class="questions-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-list"></i>
                Quiz Questions (<?php echo count($questions); ?>)
            </h2>
            <a href="<?php echo BASE_URL; ?>/teacher/quizzes/add-questions/<?php echo $quiz['id']; ?>" class="btn-add-questions">
                <i class="fas fa-plus-circle"></i>
                Add More Questions
            </a>
        </div>

        <div class="questions-list">
            <?php foreach ($questions as $index => $question): ?>
            <div class="question-item">
                <div class="question-number"><?php echo $index + 1; ?></div>
                <div class="question-content">
                    <p class="question-text"><?php echo htmlspecialchars($question['question']); ?></p>
                    <div class="question-options">
                        <span class="option <?php echo $question['correct_answer'] == 'A' ? 'correct' : ''; ?>">
                            A. <?php echo htmlspecialchars($question['option_a']); ?>
                        </span>
                        <span class="option <?php echo $question['correct_answer'] == 'B' ? 'correct' : ''; ?>">
                            B. <?php echo htmlspecialchars($question['option_b']); ?>
                        </span>
                        <?php if (!empty($question['option_c'])): ?>
                        <span class="option <?php echo $question['correct_answer'] == 'C' ? 'correct' : ''; ?>">
                            C. <?php echo htmlspecialchars($question['option_c']); ?>
                        </span>
                        <?php endif; ?>
                        <?php if (!empty($question['option_d'])): ?>
                        <span class="option <?php echo $question['correct_answer'] == 'D' ? 'correct' : ''; ?>">
                            D. <?php echo htmlspecialchars($question['option_d']); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="question-meta">
                        <span><i class="fas fa-star"></i> <?php echo $question['points']; ?> points</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.edit-quiz-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 30px 20px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #64748B;
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
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-subtitle {
    color: #64748B;
    font-size: 1rem;
    margin-bottom: 30px;
}

/* Form Card */
.form-card {
    background: white;
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    margin-bottom: 40px;
}

.form-section {
    margin-bottom: 35px;
    padding-bottom: 35px;
    border-bottom: 2px solid #F1F5F9;
}

.form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.section-title {
    color: #1E293B;
    font-size: 1.2rem;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: #8B5CF6;
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

.form-group label i {
    color: #8B5CF6;
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
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #8B5CF6;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.input-hint {
    display: block;
    font-size: 0.8rem;
    color: #64748B;
    margin-top: 5px;
}

/* Checkbox */
.checkbox-group {
    margin-top: 10px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #8B5CF6;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn-primary {
    flex: 1;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
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
    color: #64748B;
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
    background: #F1F5F9;
    border-color: #94A3B8;
    color: #1E293B;
}

/* Questions Section */
.questions-section {
    background: white;
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.btn-add-questions {
    background: #8B5CF6;
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-add-questions:hover {
    background: #7C3AED;
    transform: translateY(-2px);
}

.questions-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.question-item {
    display: flex;
    gap: 15px;
    padding: 20px;
    background: #F8FAFC;
    border-radius: 12px;
    border-left: 4px solid #8B5CF6;
}

.question-number {
    width: 40px;
    height: 40px;
    background: #8B5CF6;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.question-content {
    flex: 1;
}

.question-text {
    color: #1E293B;
    font-weight: 600;
    margin-bottom: 10px;
    font-size: 1.1rem;
}

.question-options {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 10px;
}

.option {
    padding: 5px 15px;
    background: white;
    border-radius: 30px;
    font-size: 0.9rem;
    border: 1px solid #E2E8F0;
}

.option.correct {
    background: #F0FDF4;
    border-color: #10B981;
    color: #166534;
}

.option.correct::after {
    content: ' ✓';
    color: #10B981;
    font-weight: 600;
}

.question-meta {
    color: #64748B;
    font-size: 0.85rem;
    display: flex;
    gap: 15px;
}

.question-meta i {
    color: #F97316;
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

/* Responsive */
@media (max-width: 768px) {
    .form-card,
    .questions-section {
        padding: 25px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .question-item {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .question-options {
        justify-content: center;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .form-card,
    .questions-section {
        background: #1E293B;
    }
    
    .section-title {
        color: #F1F5F9;
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
    
    .question-item {
        background: #334155;
    }
    
    .question-text {
        color: #F1F5F9;
    }
    
    .option {
        background: #1E293B;
        color: #94A3B8;
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

<script>
function filterSubjects() {
    const classId = document.getElementById('class_id').value;
    const subjectSelect = document.getElementById('subject_id');
    const options = subjectSelect.querySelectorAll('.subject-option');
    
    // Reset subject select
    subjectSelect.value = '';
    
    if (!classId) {
        options.forEach(opt => opt.style.display = 'none');
        return;
    }
    
    // Show only subjects for selected class
    options.forEach(opt => {
        if (opt.getAttribute('data-class') == classId) {
            opt.style.display = 'block';
        } else {
            opt.style.display = 'none';
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>