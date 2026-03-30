<?php
// File: /views/teacher/create_quiz.php
$pageTitle = 'Create Quiz | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$classes = $classes ?? [];
$subjects = $subjects ?? [];
?>

<div class="create-quiz-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <a href="<?php echo BASE_URL; ?>/teacher/quizzes" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Quizzes
            </a>
            <h1 class="page-title">
                <i class="fas fa-plus-circle"></i>
                Create New Quiz
            </h1>
            <p class="page-subtitle">Design a quiz for your students</p>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <!-- Create Quiz Form -->
    <div class="form-card">
        <form method="POST" action="<?php echo BASE_URL; ?>/teacher/quizzes/create" class="quiz-form" id="quizForm">
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
                    ></textarea>
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
                                <option value="<?php echo $class['id']; ?>">
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
                                        class="subject-option" style="display: none;">
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
                            value="30" 
                            min="1" 
                            max="180"
                        >
                        <small class="input-hint">Time allowed for students to complete the quiz</small>
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
                            value="70" 
                            min="0" 
                            max="100"
                        >
                        <small class="input-hint">Minimum percentage required to pass</small>
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
                            value="3" 
                            min="1" 
                            max="10"
                        >
                        <small class="input-hint">Number of times a student can attempt this quiz</small>
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
                        >
                        <small class="input-hint">Quiz will expire on this date (leave blank for no expiry)</small>
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
                        <input type="checkbox" name="is_published" id="is_published" value="1" checked>
                        <span><i class="fas fa-globe"></i> Publish immediately</span>
                    </label>
                    <small class="input-hint">
                        <span id="publishHint" class="text-success">
                            <i class="fas fa-check-circle"></i> Students will see this quiz immediately
                        </span>
                        <span id="draftHint" style="display: none;" class="text-warning">
                            <i class="fas fa-eye-slash"></i> Quiz will be saved as draft. Students won't see it until you publish it.
                        </span>
                    </small>
                </div>
                
                <!-- Warning for publishing without questions -->
                <div class="alert alert-info" id="publishWarning" style="display: none; margin-top: 15px;">
                    <i class="fas fa-info-circle"></i>
                    <span>Don't forget to add questions after creating the quiz. Students will only see the quiz once you've added questions and published it.</span>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    Create Quiz
                </button>
                <a href="<?php echo BASE_URL; ?>/teacher/quizzes" class="btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.create-quiz-container {
    max-width: 900px;
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
    color: #f06724;
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

/* Form Card */
.form-card {
    background: white;
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
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
    color: #black;
    font-size: 1.2rem;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: #f06724;
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
    color: #black;
    margin-bottom: 8px;
}

.form-group label i {
    color: #f06724;
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
    border-color: #f06724;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.input-hint {
    display: block;
    font-size: 0.8rem;
    color: black;
    margin-top: 5px;
}

.text-success {
    color: #10B981;
}

.text-warning {
    color: #F59E0B;
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
    accent-color: #f06724;
}

.checkbox-label span {
    font-weight: 500;
    color: #black;
    display: flex;
    align-items: center;
    gap: 8px;
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

.alert-error {
    background: #FEF2F2;
    color: #B91C1C;
    border: 1px solid #FECACA;
}

.alert-info {
    background: #EFF6FF;
    color: #1E40AF;
    border: 1px solid #BFDBFE;
}

/* Form Actions */
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
    color: black;
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
    color: #black;
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

/* Dark Mode */
/* @media (prefers-color-scheme: dark) {
    .form-card {
        background: #black;
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
    
    .checkbox-label span {
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
    
    .alert-info {
        background: #1E3A5F;
        color: #90CDF4;
        border-color: #2B6CB0;
    }
} */
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

// Handle publish/draft toggle
const publishCheckbox = document.getElementById('is_published');
const publishHint = document.getElementById('publishHint');
const draftHint = document.getElementById('draftHint');
const publishWarning = document.getElementById('publishWarning');

if (publishCheckbox) {
    publishCheckbox.addEventListener('change', function() {
        if (this.checked) {
            publishHint.style.display = 'inline';
            draftHint.style.display = 'none';
            publishWarning.style.display = 'none';
        } else {
            publishHint.style.display = 'none';
            draftHint.style.display = 'inline';
            publishWarning.style.display = 'block';
        }
    });
}

// Form validation
document.getElementById('quizForm').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const classId = document.getElementById('class_id').value;
    const subjectId = document.getElementById('subject_id').value;
    
    if (!title) {
        e.preventDefault();
        alert('Please enter a quiz title');
        return false;
    }
    
    if (!classId) {
        e.preventDefault();
        alert('Please select a class');
        return false;
    }
    
    if (!subjectId) {
        e.preventDefault();
        alert('Please select a subject');
        return false;
    }
    
    // Show confirmation for draft saves
    const isPublished = document.getElementById('is_published').checked;
    if (!isPublished) {
        return confirm('This quiz will be saved as a draft. Students will not see it until you publish it.\n\nClick OK to continue.');
    }
    
    return true;
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>