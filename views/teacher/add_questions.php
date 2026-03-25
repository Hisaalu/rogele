<?php
// File: /views/teacher/add_questions.php
$pageTitle = 'Add Questions | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$quiz = $quiz ?? [];
$quizId = $quiz['id'] ?? 0;
?>

<div class="add-questions-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <a href="<?php echo BASE_URL; ?>/teacher/quizzes" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Quizzes
            </a>
            <h1 class="page-title">
                <i class="fas fa-plus-circle"></i>
                Add Questions to: <?php echo htmlspecialchars($quiz['title'] ?? ''); ?>
            </h1>
            <p class="page-subtitle">Create multiple choice questions for your quiz</p>
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

    <!-- Questions Form -->
    <div class="form-card">
        <form method="POST" action="<?php echo BASE_URL; ?>/teacher/quizzes/add-questions/<?php echo $quizId; ?>" class="questions-form" id="questionsForm">
            <div id="questions-container">
                <!-- Question 1 (default) -->
                <div class="question-card" id="question-1">
                    <div class="question-header">
                        <h3 class="question-title">
                            <i class="fas fa-question-circle"></i>
                            Question 1
                        </h3>
                        <button type="button" class="remove-question" onclick="removeQuestion(1)" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="form-group">
                        <label for="q1_question">Question Text <span class="required">*</span></label>
                        <textarea 
                            id="q1_question" 
                            name="questions[1][question]" 
                            rows="2" 
                            required 
                            placeholder="Enter your question here..."
                        ></textarea>
                    </div>

                    <div class="options-grid">
                        <div class="form-group">
                            <label for="q1_option_a">Option A <span class="required">*</span></label>
                            <input 
                                type="text" 
                                id="q1_option_a" 
                                name="questions[1][option_a]" 
                                required 
                                placeholder="Option A"
                            >
                        </div>

                        <div class="form-group">
                            <label for="q1_option_b">Option B <span class="required">*</span></label>
                            <input 
                                type="text" 
                                id="q1_option_b" 
                                name="questions[1][option_b]" 
                                required 
                                placeholder="Option B"
                            >
                        </div>

                        <div class="form-group">
                            <label for="q1_option_c">Option C</label>
                            <input 
                                type="text" 
                                id="q1_option_c" 
                                name="questions[1][option_c]" 
                                placeholder="Option C (optional)"
                            >
                        </div>

                        <div class="form-group">
                            <label for="q1_option_d">Option D</label>
                            <input 
                                type="text" 
                                id="q1_option_d" 
                                name="questions[1][option_d]" 
                                placeholder="Option D (optional)"
                            >
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="q1_correct">Correct Answer <span class="required">*</span></label>
                            <select id="q1_correct" name="questions[1][correct_answer]" required>
                                <option value="">Select correct answer</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="q1_points">Points</label>
                            <input 
                                type="number" 
                                id="q1_points" 
                                name="questions[1][points]" 
                                value="1" 
                                min="1" 
                                max="10"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Question Button -->
            <div class="add-question-btn-container">
                <button type="button" class="btn-add-question" onclick="addQuestion()">
                    <i class="fas fa-plus-circle"></i>
                    Add Another Question
                </button>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    Save Questions
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
.add-questions-container {
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

/* Question Card */
.question-card {
    background: #F8FAFC;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 25px;
    border: 2px solid #E2E8F0;
    transition: all 0.3s ease;
}

.question-card:hover {
    border-color: #f06724;
}

.question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.question-title {
    color: black;
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.question-title i {
    color: #f06724;
}

.remove-question {
    background: #FEF2F2;
    color: #EF4444;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.remove-question:hover {
    background: #EF4444;
    color: white;
}

/* Form Groups */
.form-group {
    margin-bottom: 15px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 15px;
}

.options-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 15px;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    color: black;
    margin-bottom: 5px;
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
    padding: 10px 12px;
    border: 2px solid #E2E8F0;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    font-family: 'Inter', sans-serif;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #f06724;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

/* Add Question Button */
.add-question-btn-container {
    text-align: center;
    margin: 30px 0;
}

.btn-add-question {
    background: white;
    color: #7f2677;
    border: 2px dashed #f06724;
    padding: 15px 30px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-add-question:hover {
    background: #f06724;
    color: white;
    border-style: solid;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
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
    color: white;
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
    color: whiteB;
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
    .form-card {
        padding: 25px;
    }
    
    .options-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .form-card {
        background: #1E293B;
    }
    
    .question-card {
        background: #334155;
    }
    
    .question-title {
        color: #F1F5F9;
    }
    
    .form-group label {
        color: #F1F5F9;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        background: #0F172A;
        border-color: #475569;
        color: #F1F5F9;
    }
    
    .btn-add-question {
        background: transparent;
        color: #8B5CF6;
        border-color: #8B5CF6;
    }
    
    .btn-add-question:hover {
        background: #f06724;
        color: white;
    }
    
    .btn-secondary {
        background: transparent;
        color: #94A3B8;
        border-color: #475569;
    }
    
    .btn-secondary:hover {
        background: #f06724;
        color: #F1F5F9;
    }
}
</style>

<script>
let questionCount = 1;

function addQuestion() {
    questionCount++;
    
    const container = document.getElementById('questions-container');
    const newQuestion = document.createElement('div');
    newQuestion.className = 'question-card';
    newQuestion.id = `question-${questionCount}`;
    
    newQuestion.innerHTML = `
        <div class="question-header">
            <h3 class="question-title">
                <i class="fas fa-question-circle"></i>
                Question ${questionCount}
            </h3>
            <button type="button" class="remove-question" onclick="removeQuestion(${questionCount})">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="form-group">
            <label for="q${questionCount}_question">Question Text <span class="required">*</span></label>
            <textarea 
                id="q${questionCount}_question" 
                name="questions[${questionCount}][question]" 
                rows="2" 
                required 
                placeholder="Enter your question here..."
            ></textarea>
        </div>

        <div class="options-grid">
            <div class="form-group">
                <label for="q${questionCount}_option_a">Option A <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="q${questionCount}_option_a" 
                    name="questions[${questionCount}][option_a]" 
                    required 
                    placeholder="Option A"
                >
            </div>

            <div class="form-group">
                <label for="q${questionCount}_option_b">Option B <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="q${questionCount}_option_b" 
                    name="questions[${questionCount}][option_b]" 
                    required 
                    placeholder="Option B"
                >
            </div>

            <div class="form-group">
                <label for="q${questionCount}_option_c">Option C</label>
                <input 
                    type="text" 
                    id="q${questionCount}_option_c" 
                    name="questions[${questionCount}][option_c]" 
                    placeholder="Option C (optional)"
                >
            </div>

            <div class="form-group">
                <label for="q${questionCount}_option_d">Option D</label>
                <input 
                    type="text" 
                    id="q${questionCount}_option_d" 
                    name="questions[${questionCount}][option_d]" 
                    placeholder="Option D (optional)"
                >
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="q${questionCount}_correct">Correct Answer <span class="required">*</span></label>
                <select id="q${questionCount}_correct" name="questions[${questionCount}][correct_answer]" required>
                    <option value="">Select correct answer</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>
            </div>

            <div class="form-group">
                <label for="q${questionCount}_points">Points</label>
                <input 
                    type="number" 
                    id="q${questionCount}_points" 
                    name="questions[${questionCount}][points]" 
                    value="1" 
                    min="1" 
                    max="10"
                >
            </div>
        </div>
    `;
    
    container.appendChild(newQuestion);
    
    // Show remove button on first question if there are multiple
    if (questionCount > 1) {
        document.querySelector('#question-1 .remove-question').style.display = 'flex';
    }
}

function removeQuestion(id) {
    if (confirm('Are you sure you want to remove this question?')) {
        const question = document.getElementById(`question-${id}`);
        question.remove();
        
        // Update question count
        questionCount--;
        
        // Hide remove button on first question if only one remains
        if (questionCount === 1) {
            document.querySelector('#question-1 .remove-question').style.display = 'none';
        }
        
        // Renumber remaining questions
        renumberQuestions();
    }
}

function renumberQuestions() {
    const questions = document.querySelectorAll('.question-card');
    questions.forEach((q, index) => {
        const newNumber = index + 1;
        q.id = `question-${newNumber}`;
        
        // Update title
        q.querySelector('.question-title').innerHTML = `
            <i class="fas fa-question-circle"></i>
            Question ${newNumber}
        `;
        
        // Update all input names and ids
        const inputs = q.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                const newName = name.replace(/\[\d+\]/, `[${newNumber}]`);
                input.setAttribute('name', newName);
            }
            
            const id = input.getAttribute('id');
            if (id) {
                const newId = id.replace(/\d+/, newNumber);
                input.setAttribute('id', newId);
            }
        });
        
        // Update labels' for attributes
        const labels = q.querySelectorAll('label');
        labels.forEach(label => {
            const htmlFor = label.getAttribute('for');
            if (htmlFor) {
                const newFor = htmlFor.replace(/\d+/, newNumber);
                label.setAttribute('for', newFor);
            }
        });
        
        // Update remove button onclick
        const removeBtn = q.querySelector('.remove-question');
        if (removeBtn) {
            removeBtn.setAttribute('onclick', `removeQuestion(${newNumber})`);
        }
    });
}

// Form validation
document.getElementById('questionsForm').addEventListener('submit', function(e) {
    const questions = document.querySelectorAll('.question-card');
    let isValid = true;
    
    questions.forEach((q, index) => {
        const questionText = q.querySelector('textarea').value.trim();
        const optionA = q.querySelector('input[name*="[option_a]"]').value.trim();
        const optionB = q.querySelector('input[name*="[option_b]"]').value.trim();
        const correctAnswer = q.querySelector('select').value;
        
        if (!questionText) {
            alert(`Question ${index + 1}: Please enter the question text`);
            isValid = false;
            e.preventDefault();
            return;
        }
        
        if (!optionA || !optionB) {
            alert(`Question ${index + 1}: Options A and B are required`);
            isValid = false;
            e.preventDefault();
            return;
        }
        
        if (!correctAnswer) {
            alert(`Question ${index + 1}: Please select the correct answer`);
            isValid = false;
            e.preventDefault();
            return;
        }
    });
    
    return isValid;
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>