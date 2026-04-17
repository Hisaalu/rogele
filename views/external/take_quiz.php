<?php
// File: /views/external/take_quiz.php
$pageTitle = $quiz['title'] . ' | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

if (empty($questions)) {
    echo '<div style="text-align: center; padding: 50px;">
            <h2>No Questions Found</h2>
            <p>This quiz doesn\'t have any questions yet. Please contact the administrator.</p>
            <a href="' . BASE_URL . '/external/quizzes" class="btn-primary">Back to Quizzes</a>
          </div>';
    require_once __DIR__ . '/../layouts/footer.php';
    exit;
}

$timeLimitSeconds = isset($quiz['time_limit']) && $quiz['time_limit'] > 0 ? $quiz['time_limit'] * 60 : 0;
$quizId = $quiz['id'];
$attemptIdValue = $attemptId;
$endTime = time() + $timeLimitSeconds;
?>

<div class="quiz-take-container">
    <div class="sticky-timer-bar" id="stickyTimerBar">
        <div class="sticky-timer-content">
            <div class="sticky-timer-info">
                <i class="fas fa-hourglass-half"></i>
                <span id="stickyTimerDisplay">Loading...</span>
            </div>
            <div class="sticky-progress-info">
                <i class="fas fa-check-circle"></i>
                <span id="stickyAnsweredCount">0</span> of <?php echo count($questions); ?> Answered
            </div>
        </div>
    </div>

    <div class="quiz-header">
        <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
        
        <div class="quiz-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Important Notice:</strong>
                <p>Your answers are saved automatically. Do NOT refresh or close the browser.</p>
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
        <input type="hidden" id="quizEndTime" name="quiz_end_time" value="<?php echo $endTime; ?>">
        
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
/* Your existing styles remain the same */
.quiz-take-container {
    max-width: 900px;
    margin: 40px auto;
    padding: 0 20px;
}

.sticky-timer-bar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, #7f2677);
    color: white;
    padding: 12px 20px;
    z-index: 1000;
    transform: translateY(-100%);
    transition: transform 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.sticky-timer-bar.visible {
    transform: translateY(0);
}

.sticky-timer-content {
    max-width: 900px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.sticky-timer-info, .sticky-progress-info {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    font-size: 1rem;
}

.sticky-timer-info i, .sticky-progress-info i {
    font-size: 1.2rem;
}

#stickyTimerDisplay, #stickyAnsweredCount {
    font-family: monospace;
    font-size: 1.2rem;
    font-weight: 700;
    background: rgba(255,255,255,0.2);
    padding: 4px 12px;
    border-radius: 30px;
}

.quiz-header {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    position: sticky;
    top: 20px;
    z-index: 99;
    transition: all 0.3s ease;
}

.quiz-header.sticky-scrolled {
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    background: rgba(255, 255, 255, 0.98);
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
    background: #F8FAFC;
    padding: 8px 16px;
    border-radius: 50px;
}

.stat i {
    color: #f06724;
}

#timerDisplay {
    font-weight: 700;
    font-family: monospace;
    font-size: 1.2rem;
    color: #f06724;
    background: white;
    padding: 4px 12px;
    border-radius: 30px;
    margin-left: 5px;
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
    z-index: 98;
    background: transparent;
    margin-top: 20px;
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
    .quiz-header { padding: 20px; top: 10px; }
    .quiz-footer { position: static; margin-top: 20px; }
    .quiz-stats { gap: 15px; }
    .sticky-timer-content { justify-content: center; }
    .sticky-timer-info, .sticky-progress-info { font-size: 0.85rem; }
    #stickyTimerDisplay, #stickyAnsweredCount { font-size: 1rem; padding: 2px 8px; }
}
</style>

<script>
// Quiz configuration
const QUIZ_STORAGE_KEY = 'quiz_state_' + <?php echo $quizId; ?> + '_' + <?php echo $attemptId; ?>;
const ATTEMPT_ID = <?php echo $attemptId; ?>;
const TOTAL_QUESTIONS = <?php echo count($questions); ?>;
const TIME_LIMIT_SECONDS = <?php echo $timeLimitSeconds; ?>;
const QUIZ_ID = <?php echo $quizId; ?>;

// Use server-side end time for reliable timer across logouts
let endTime = localStorage.getItem('quiz_end_time_' + QUIZ_ID + '_' + ATTEMPT_ID);
if (!endTime) {
    endTime = Date.now() + (TIME_LIMIT_SECONDS * 1000);
    localStorage.setItem('quiz_end_time_' + QUIZ_ID + '_' + ATTEMPT_ID, endTime);
}
endTime = parseInt(endTime);

let formSubmitted = false;
let timerInterval = null;
let warningShown = false;

// DOM elements
const stickyTimerBar = document.getElementById('stickyTimerBar');
const stickyTimerDisplay = document.getElementById('stickyTimerDisplay');
const stickyAnsweredCount = document.getElementById('stickyAnsweredCount');
const quizHeader = document.querySelector('.quiz-header');
const timerDisplay = document.getElementById('timerDisplay');
const timerWarning = document.getElementById('timerWarning');
const answeredCountSpan = document.getElementById('answeredCount');
const submitBtn = document.getElementById('submitBtn');
const radioButtons = document.querySelectorAll('input[type="radio"]');
const quizForm = document.getElementById('quizForm');

function formatTime(seconds) {
    if (seconds < 0) seconds = 0;
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
}

function updateTimerDisplay() {
    const now = Date.now();
    let timeLeft = Math.max(0, Math.floor((endTime - now) / 1000));
    
    if (timerDisplay) {
        timerDisplay.textContent = formatTime(timeLeft);
    }
    if (stickyTimerDisplay) {
        stickyTimerDisplay.textContent = formatTime(timeLeft);
    }
    
    // Show warning when less than 1 minute
    if (timeLeft <= 60 && timeLeft > 0 && !warningShown) {
        if (timerWarning) timerWarning.style.display = 'flex';
        warningShown = true;
    }
    
    // Auto-submit when time is up
    if (timeLeft <= 0 && !formSubmitted) {
        autoSubmit();
    }
    
    // Store remaining time in localStorage
    localStorage.setItem('quiz_time_remaining_' + QUIZ_ID + '_' + ATTEMPT_ID, timeLeft);
}

function autoSubmit() {
    if (formSubmitted) return;
    
    console.log("Auto-submitting quiz...");
    formSubmitted = true;
    
    // Clear timer
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
    
    // Show alert
    alert('⏰ Time is up! Your quiz will be submitted automatically.');
    
    // Submit the form
    if (quizForm) {
        // Add a flag to indicate auto-submit
        const autoSubmitFlag = document.createElement('input');
        autoSubmitFlag.type = 'hidden';
        autoSubmitFlag.name = 'auto_submit';
        autoSubmitFlag.value = '1';
        quizForm.appendChild(autoSubmitFlag);
        quizForm.submit();
    }
}

function saveAnswers() {
    const radioButtonsChecked = document.querySelectorAll('input[type="radio"]:checked');
    const answers = {};
    radioButtonsChecked.forEach(radio => {
        const name = radio.name;
        const questionId = name.match(/\d+/)[0];
        answers[questionId] = radio.value;
    });
    localStorage.setItem(QUIZ_STORAGE_KEY, JSON.stringify(answers));
}

function loadSavedAnswers() {
    const savedState = localStorage.getItem(QUIZ_STORAGE_KEY);
    if (savedState) {
        try {
            const answers = JSON.parse(savedState);
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

function updateProgress() {
    const answered = new Set();
    radioButtons.forEach(radio => {
        if (radio.checked) {
            const name = radio.name;
            answered.add(name);
            const questionId = name.match(/\d+/)[0];
            const item = document.querySelector(`.question-item[data-question-id="${questionId}"]`);
            if (item) item.classList.add('answered');
        }
    });
    
    const count = answered.size;
    if (answeredCountSpan) answeredCountSpan.textContent = count;
    if (stickyAnsweredCount) stickyAnsweredCount.textContent = count;
    
    if (submitBtn) {
        submitBtn.disabled = count !== TOTAL_QUESTIONS;
    }
    
    saveAnswers();
}

function checkScrollPosition() {
    if (!stickyTimerBar) return;
    
    const scrollPosition = window.scrollY;
    
    if (scrollPosition > 100) {
        stickyTimerBar.classList.add('visible');
    } else {
        stickyTimerBar.classList.remove('visible');
    }
    
    if (quizHeader) {
        if (scrollPosition > 50) {
            quizHeader.classList.add('sticky-scrolled');
        } else {
            quizHeader.classList.remove('sticky-scrolled');
        }
    }
}

// Initialize timer
function initTimer() {
    updateTimerDisplay();
    timerInterval = setInterval(updateTimerDisplay, 1000);
}

// Block back navigation
history.pushState(null, null, location.href);
window.addEventListener('popstate', function(event) {
    if (confirm('WARNING: If you go back, you may lose your progress and cannot retake this quiz!\n\nDo you want to continue?')) {
        localStorage.removeItem(QUIZ_STORAGE_KEY);
        localStorage.removeItem('quiz_end_time_' + QUIZ_ID + '_' + ATTEMPT_ID);
        window.location.href = '<?php echo BASE_URL; ?>/external/quizzes';
    } else {
        history.pushState(null, null, location.href);
    }
});

// Warn before refresh
window.addEventListener('beforeunload', function (e) {
    if (!formSubmitted) {
        e.preventDefault();
        e.returnValue = 'If you refresh, you may lose your progress and cannot retake this quiz! Are you sure?';
        return e.returnValue;
    }
});

// Clean up on form submission
if (quizForm) {
    quizForm.addEventListener('submit', function() {
        formSubmitted = true;
        if (timerInterval) {
            clearInterval(timerInterval);
        }
        // Clear localStorage
        localStorage.removeItem(QUIZ_STORAGE_KEY);
        localStorage.removeItem('quiz_end_time_' + QUIZ_ID + '_' + ATTEMPT_ID);
        localStorage.removeItem('quiz_time_remaining_' + QUIZ_ID + '_' + ATTEMPT_ID);
    });
}

// Load saved answers
loadSavedAnswers();

// Setup radio button listeners
if (radioButtons.length > 0) {
    radioButtons.forEach(radio => {
        radio.addEventListener('change', updateProgress);
    });
}

// Initial progress update
updateProgress();

// Start timer
if (TIME_LIMIT_SECONDS > 0) {
    initTimer();
} else if (timerDisplay) {
    timerDisplay.textContent = 'No time limit';
    if (stickyTimerDisplay) stickyTimerDisplay.textContent = 'No time limit';
}

// Scroll listeners
window.addEventListener('scroll', checkScrollPosition);
window.addEventListener('resize', checkScrollPosition);
checkScrollPosition();

// Anti-cheat: Disable right-click
document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
    return false;
});

// Anti-cheat: Disable copy-paste
document.addEventListener('copy', function(e) {
    e.preventDefault();
    return false;
});

document.addEventListener('paste', function(e) {
    e.preventDefault();
    return false;
});

// Anti-cheat: Warn about tab switching
document.addEventListener('visibilitychange', function() {
    if (document.hidden && !formSubmitted) {
        alert('Please do not switch tabs during the quiz. Your attempt may be invalidated.');
    }
});

// Anti-cheat: Block refresh shortcuts
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey && (e.key === 'r' || e.key === 'R')) || 
        (e.ctrlKey && e.shiftKey && (e.key === 'r' || e.key === 'R')) ||
        e.key === 'F5') {
        e.preventDefault();
        alert('Please do not refresh the page during the quiz!');
        return false;
    }
    
    if (e.key === 'Backspace') {
        const target = e.target;
        if (target.tagName !== 'INPUT' && target.tagName !== 'TEXTAREA') {
            e.preventDefault();
            alert('Please do not use backspace to navigate away from the quiz!');
            return false;
        }
    }
});

console.log("Quiz initialized. End time: " + new Date(endTime));
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>