<?php
//views/external/take_quiz.php
$pageTitle = $quiz['title'] . ' | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

// Make sure questions exist
if (empty($questions)) {
    echo '<div style="text-align: center; padding: 50px;">
            <h2>No Questions Found</h2>
            <p>This quiz doesn\'t have any questions yet. Please contact the administrator.</p>
            <a href="' . BASE_URL . '/external/quizzes" class="btn-primary">Back to Quizzes</a>
          </div>';
    require_once __DIR__ . '/../layouts/footer.php';
    exit;
}

// Get time limit in seconds
$timeLimitSeconds = isset($quiz['time_limit']) && $quiz['time_limit'] > 0 ? $quiz['time_limit'] * 60 : 0;
$quizId = $quiz['id'];
$attemptIdValue = $attemptId;
?>

<div class="quiz-take-container">
    <div class="quiz-header">
        <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
        
        <div class="quiz-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>⚠️ Important Notice:</strong>
                <p>You can only take this quiz once. Do NOT refresh the page, close the browser, or use the back button. Your progress will be saved automatically as you answer.</p>
            </div>
        </div>
        
        <div class="quiz-stats">
            <div class="stat">
                <i class="fas fa-question-circle"></i>
                <span><?php echo count($questions); ?> Questions</span>
            </div>
            <div class="stat">
                <i class="fas fa-hourglass-half"></i>
                <span id="timerDisplay">Loading...</span>
            </div>
            <div class="stat" id="answeredStat">
                <i class="fas fa-check-circle"></i>
                <span id="answeredCount">0</span> Answered
            </div>
        </div>
    </div>

    <form id="quizForm" method="POST" action="<?php echo BASE_URL; ?>/external/take-quiz/<?php echo $quiz['id']; ?>">
        <input type="hidden" name="attempt_id" value="<?php echo $attemptId; ?>">
        
        <div class="questions-list">
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-item" data-question-id="<?php echo $question['id']; ?>">
                    <div class="question-number">Question <?php echo $index + 1; ?> of <?php echo count($questions); ?></div>
                    <div class="question-text"><?php echo htmlspecialchars($question['question_text']); ?></div>
                    
                    <div class="options">
                        <?php 
                        $options = $question['options'] ?? [];
                        $letters = ['A', 'B', 'C', 'D'];
                        if (empty($options)) {
                            echo '<p class="error">No options available for this question.</p>';
                        } else {
                            foreach ($options as $optIndex => $option):
                                $letter = $letters[$optIndex];
                        ?>
                            <label class="option">
                                <input type="radio" 
                                    name="answers[<?php echo $question['id']; ?>]" 
                                    value="<?php echo $optIndex; ?>"
                                    required>
                                <span class="option-letter"><?php echo $letter; ?></span>
                                <span class="option-text"><?php echo htmlspecialchars($option); ?></span>
                            </label>
                        <?php 
                            endforeach;
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="quiz-footer">
            <div class="timer-warning" id="timerWarning" style="display: none;">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Less than 1 minute remaining!</span>
            </div>
            <button type="submit" class="btn-submit" id="submitBtn" disabled>
                <i class="fas fa-check-circle"></i> Submit Quiz
            </button>
        </div>
    </form>
</div>

<style>
/* Your existing CSS styles remain the same */
.quiz-take-container {
    max-width: 900px;
    margin: 40px auto;
    padding: 0 20px;
}
.quiz-header {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}
.quiz-header h1 {
    color: black;
    margin-bottom: 20px;
}
.quiz-warning {
    background: #FEF3C7;
    border-left: 4px solid #f06724;
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: flex;
    gap: 12px;
}
.quiz-warning i {
    font-size: 1.2rem;
    color: #f06724;
}
.quiz-warning strong {
    color: #f06724;
    display: block;
    margin-bottom: 5px;
}
.quiz-warning p {
    color: #f06724;
    font-size: 0.85rem;
    margin: 0;
}
.quiz-stats {
    display: flex;
    gap: 30px;
    padding-top: 20px;
    border-top: 1px solid #E2E8F0;
    flex-wrap: wrap;
}
.stat {
    display: flex;
    align-items: center;
    gap: 8px;
    color: black;
}
.stat i {
    color: #f06724;
}
#timerDisplay {
    font-weight: 700;
    font-family: monospace;
    font-size: 1.1rem;
    color: black;
}
.questions-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 30px;
}
.question-item {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}
.question-item.answered {
    border-left: 4px solid #7f2677;
}
.question-number {
    font-size: 0.75rem;
    color: black;
    font-weight: 600;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.question-text {
    font-size: 1.1rem;
    font-weight: 600;
    color: black;
    margin-bottom: 20px;
}
.options {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: #F8FAFC;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}
.option:hover {
    background: #F1F5F9;
    transform: translateX(5px);
}
.option input[type="radio"] {
    display: none;
}
.option-letter {
    width: 30px;
    height: 30px;
    background: white;
    border: 2px solid #CBD5E0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: black;
    transition: all 0.3s ease;
}
.option input[type="radio"]:checked + .option-letter {
    background: #f06724;
    border-color: #7f2677;
    color: white;
}
.option-text {
    flex: 1;
    color: black;
}
.quiz-footer {
    position: sticky;
    bottom: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.timer-warning {
    background: #FEF2F2;
    border: 1px solid #EF4444;
    padding: 10px 20px;
    border-radius: 50px;
    color: #B91C1C;
    font-size: 0.9rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    animation: pulse 1s infinite;
}
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}
.btn-submit {
    background: linear-gradient(135deg, #7f2677);
    color: white;
    border: none;
    padding: 14px 40px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
}
.btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 15px 30px rgba(139, 92, 246, 0.4);
}
.btn-submit:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
@media (max-width: 768px) {
    .quiz-header { padding: 20px; }
    .quiz-footer { position: static; margin-top: 20px; }
    .quiz-stats { gap: 15px; }
}
</style>

<script>
// ============================================
// QUIZ STATE - Using localStorage for persistence
// ============================================
const QUIZ_STORAGE_KEY = 'quiz_state_' + <?php echo $quizId; ?> + '_' + <?php echo $attemptId; ?>;
const TOTAL_QUESTIONS = <?php echo count($questions); ?>;
const TIME_LIMIT_SECONDS = <?php echo $timeLimitSeconds; ?>;
const QUIZ_START_TIME = 'quiz_start_time_' + <?php echo $quizId; ?>;

// Load saved answers from localStorage
function loadSavedAnswers() {
    const savedState = localStorage.getItem(QUIZ_STORAGE_KEY);
    if (savedState) {
        try {
            const answers = JSON.parse(savedState);
            // Restore radio button selections
            for (const [questionId, value] of Object.entries(answers)) {
                const radio = document.querySelector(`input[name="answers[${questionId}]"][value="${value}"]`);
                if (radio) {
                    radio.checked = true;
                }
            }
            updateProgress();
        } catch (e) {
            console.error('Error loading saved answers:', e);
        }
    }
}

// Save answers to localStorage
function saveAnswers() {
    const radioButtons = document.querySelectorAll('input[type="radio"]:checked');
    const answers = {};
    radioButtons.forEach(radio => {
        const name = radio.name;
        const questionId = name.match(/\d+/)[0];
        answers[questionId] = radio.value;
    });
    localStorage.setItem(QUIZ_STORAGE_KEY, JSON.stringify(answers));
}

// ============================================
// TIMER - FIXED VERSION
// ============================================
let timeLeft = TIME_LIMIT_SECONDS;
let timerInterval = null;
let formSubmitted = false;
const timerDisplay = document.getElementById('timerDisplay');
const timerWarning = document.getElementById('timerWarning');

// Debug: Log the time limit
console.log("TIME_LIMIT_SECONDS: " + TIME_LIMIT_SECONDS);

// If no time limit, show message and enable submit
if (TIME_LIMIT_SECONDS <= 0) {
    if (timerDisplay) timerDisplay.textContent = 'No time limit';
    // Enable submit button immediately if all questions are answered
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn && document.querySelectorAll('input[type="radio"]:checked').length === TOTAL_QUESTIONS) {
        submitBtn.disabled = false;
    }
} else {
    // Start the timer only if there's a time limit
    startTimer();
}

function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
}

function updateTimerDisplay() {
    if (timerDisplay && timeLeft >= 0) {
        if (timeLeft > 0) {
            timerDisplay.textContent = formatTime(timeLeft);
        } else {
            timerDisplay.textContent = 'Time\'s Up!';
        }
    }
}

function autoSubmit() {
    if (!formSubmitted && timeLeft <= 0) {
        console.log("Auto-submitting quiz...");
        formSubmitted = true;
        alert('⏰ Time is up! Your quiz will be submitted automatically.');
        document.getElementById('quizForm').submit();
    }
}

function startTimer() {
    // Don't start if no time limit
    if (TIME_LIMIT_SECONDS <= 0) return;
    
    console.log("Starting timer with " + timeLeft + " seconds");
    updateTimerDisplay();
    
    // Show warning if less than 1 minute
    if (timeLeft <= 60 && timerWarning) {
        timerWarning.style.display = 'flex';
    }
    
    timerInterval = setInterval(() => {
        if (!formSubmitted && timeLeft > 0) {
            timeLeft--;
            updateTimerDisplay();
            
            // Show warning when less than 1 minute remaining
            if (timeLeft <= 60 && timeLeft > 0 && timerWarning) {
                timerWarning.style.display = 'flex';
            } else if (timerWarning && timeLeft > 60) {
                timerWarning.style.display = 'none';
            }
            
            // Auto-submit when time reaches 0
            if (timeLeft === 0) {
                clearInterval(timerInterval);
                autoSubmit();
            }
        }
    }, 1000);
}

// Reset timer function (for debugging)
function resetTimer() {
    if (timerInterval) clearInterval(timerInterval);
    timeLeft = TIME_LIMIT_SECONDS;
    updateTimerDisplay();
    startTimer();
}

// ============================================
// PREVENT BACK NAVIGATION
// ============================================
// Push a new state to prevent going back
history.pushState(null, null, location.href);
window.addEventListener('popstate', function(event) {
    if (confirm('⚠️ WARNING: If you go back, you will lose your progress and cannot retake this quiz!\n\nDo you want to continue?')) {
        // Clear storage and redirect
        localStorage.removeItem(QUIZ_STORAGE_KEY);
        localStorage.removeItem(QUIZ_START_TIME);
        window.location.href = '<?php echo BASE_URL; ?>/external/quizzes';
    } else {
        // Push state again to prevent going back
        history.pushState(null, null, location.href);
    }
});

// ============================================
// PREVENT REFRESH
// ============================================
window.addEventListener('beforeunload', function (e) {
    const submitBtn = document.getElementById('submitBtn');
    if (!formSubmitted && submitBtn && !submitBtn.disabled) {
        e.preventDefault();
        e.returnValue = '⚠️ If you refresh, you will lose your progress and cannot retake this quiz! Are you sure?';
        return e.returnValue;
    }
});

// ============================================
// TRACK ANSWERED QUESTIONS
// ============================================
const answeredCountSpan = document.getElementById('answeredCount');
const submitBtn = document.getElementById('submitBtn');
const radioButtons = document.querySelectorAll('input[type="radio"]');
const questionItems = document.querySelectorAll('.question-item');

function updateProgress() {
    const answered = new Set();
    radioButtons.forEach(radio => {
        if (radio.checked) {
            const name = radio.name;
            answered.add(name);
            
            // Highlight answered question
            const questionId = name.match(/\d+/)[0];
            const item = document.querySelector(`.question-item[data-question-id="${questionId}"]`);
            if (item) item.classList.add('answered');
        }
    });
    const count = answered.size;
    if (answeredCountSpan) answeredCountSpan.textContent = count;
    
    // Enable submit when all questions are answered
    if (submitBtn) {
        if (count === TOTAL_QUESTIONS) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    }
    
    // Save answers to localStorage
    saveAnswers();
}

// ============================================
// INITIALIZE
// ============================================
if (radioButtons.length > 0) {
    radioButtons.forEach(radio => {
        radio.addEventListener('change', updateProgress);
    });
    
    // Load saved answers from localStorage
    loadSavedAnswers();
    
    // Initial update
    updateProgress();
}

// Start the timer
startTimer();

// Mark form as submitted and clear storage on submit
const quizForm = document.getElementById('quizForm');
if (quizForm) {
    quizForm.addEventListener('submit', function() {
        formSubmitted = true;
        if (timerInterval) {
            clearInterval(timerInterval);
        }
        // Clear storage
        localStorage.removeItem(QUIZ_STORAGE_KEY);
        localStorage.removeItem(QUIZ_START_TIME);
    });
}

// ============================================
// ANTI-CHEAT MEASURES
// ============================================
// Block right-click
document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
    return false;
});

// Block copy-paste
document.addEventListener('copy', function(e) {
    e.preventDefault();
    return false;
});

document.addEventListener('paste', function(e) {
    e.preventDefault();
    return false;
});

// Warn about tab switching
document.addEventListener('visibilitychange', function() {
    if (document.hidden && !formSubmitted) {
        alert('⚠️ Please do not switch tabs during the quiz. Your attempt may be invalidated.');
    }
});

// Block keyboard shortcuts (Ctrl+R, Ctrl+Shift+R, F5)
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey && (e.key === 'r' || e.key === 'R')) || 
        (e.ctrlKey && e.shiftKey && (e.key === 'r' || e.key === 'R')) ||
        e.key === 'F5') {
        e.preventDefault();
        alert('⚠️ Refreshing the page is not allowed during the quiz!');
        return false;
    }
    
    // Block backspace
    if (e.key === 'Backspace') {
        const target = e.target;
        if (target.tagName !== 'INPUT' && target.tagName !== 'TEXTAREA') {
            e.preventDefault();
            alert('⚠️ Using backspace to go back is not allowed during the quiz!');
            return false;
        }
    }
});

document.getElementById('quizForm').addEventListener('submit', function(e) {
    var formData = new FormData(this);
    console.log("Form submission data:");
    for (var pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>