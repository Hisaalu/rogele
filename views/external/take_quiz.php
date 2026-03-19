<?php
// File: /views/external/take_quiz.php
$pageTitle = 'Take Quiz - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

// Calculate end time for timer
$endTime = time() + ($quiz['time_limit'] * 60);
?>

<div style="padding: 20px; max-width: 900px; margin: 0 auto;">
    <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
        <!-- Quiz Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 20px;">
            <div>
                <h1 style="color: #1E293B; margin-bottom: 5px;"><?php echo htmlspecialchars($quiz['title']); ?></h1>
                <p style="color: #64748B;"><?php echo htmlspecialchars($quiz['description'] ?? 'Answer all questions carefully.'); ?></p>
            </div>
            <div style="background: linear-gradient(135deg, #8B5CF6, #F97316); color: white; padding: 15px 25px; border-radius: 15px; text-align: center;">
                <div style="font-size: 2rem; font-weight: 700;" id="timer"><?php echo $quiz['time_limit']; ?>:00</div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Time Remaining</div>
            </div>
        </div>
        
        <!-- Progress Bar -->
        <div style="margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: #64748B;">
                <span>Question <span id="current-question">1</span> of <span id="total-questions"><?php echo count($questions); ?></span></span>
                <span id="progress-percentage">0%</span>
            </div>
            <div style="width: 100%; height: 10px; background: #E2E8F0; border-radius: 5px; overflow: hidden;">
                <div id="progress-bar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #8B5CF6, #F97316); transition: width 0.3s ease;"></div>
            </div>
        </div>
        
        <!-- Quiz Form -->
        <form method="POST" action="<?php echo BASE_URL; ?>/external/take-quiz/<?php echo $quizId; ?>" id="quizForm">
            <input type="hidden" name="attempt_id" value="<?php echo $attemptId; ?>">
            
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-card" id="question-<?php echo $index + 1; ?>" style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>; margin-bottom: 30px;">
                    <h3 style="margin-bottom: 20px; color: #1E293B;">
                        <span style="background: #8B5CF6; color: white; width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; margin-right: 10px; font-size: 0.9rem;">
                            <?php echo $index + 1; ?>
                        </span>
                        <?php echo htmlspecialchars($question['question']); ?>
                    </h3>
                    
                    <div style="display: grid; gap: 15px;">
                        <label style="display: flex; align-items: center; gap: 15px; padding: 15px; border: 2px solid #E2E8F0; border-radius: 12px; cursor: pointer; transition: all 0.3s ease;">
                            <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="A" style="width: 20px; height: 20px; accent-color: #8B5CF6;">
                            <span>A. <?php echo htmlspecialchars($question['option_a']); ?></span>
                        </label>
                        
                        <label style="display: flex; align-items: center; gap: 15px; padding: 15px; border: 2px solid #E2E8F0; border-radius: 12px; cursor: pointer; transition: all 0.3s ease;">
                            <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="B" style="width: 20px; height: 20px; accent-color: #8B5CF6;">
                            <span>B. <?php echo htmlspecialchars($question['option_b']); ?></span>
                        </label>
                        
                        <?php if (!empty($question['option_c'])): ?>
                        <label style="display: flex; align-items: center; gap: 15px; padding: 15px; border: 2px solid #E2E8F0; border-radius: 12px; cursor: pointer; transition: all 0.3s ease;">
                            <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="C" style="width: 20px; height: 20px; accent-color: #8B5CF6;">
                            <span>C. <?php echo htmlspecialchars($question['option_c']); ?></span>
                        </label>
                        <?php endif; ?>
                        
                        <?php if (!empty($question['option_d'])): ?>
                        <label style="display: flex; align-items: center; gap: 15px; padding: 15px; border: 2px solid #E2E8F0; border-radius: 12px; cursor: pointer; transition: all 0.3s ease;">
                            <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="D" style="width: 20px; height: 20px; accent-color: #8B5CF6;">
                            <span>D. <?php echo htmlspecialchars($question['option_d']); ?></span>
                        </label>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Navigation Buttons -->
            <div style="display: flex; justify-content: space-between; margin-top: 30px;">
                <button type="button" id="prevBtn" onclick="changeQuestion(-1)" style="background: white; color: #64748B; border: 2px solid #E2E8F0; padding: 12px 30px; border-radius: 50px; font-weight: 600; cursor: pointer; display: none;">
                    <i class="fas fa-arrow-left"></i> Previous
                </button>
                
                <button type="button" id="nextBtn" onclick="changeQuestion(1)" style="background: linear-gradient(135deg, #8B5CF6, #F97316); color: white; border: none; padding: 12px 30px; border-radius: 50px; font-weight: 600; cursor: pointer;">
                    Next <i class="fas fa-arrow-right"></i>
                </button>
                
                <button type="submit" id="submitBtn" style="display: none; background: #10B981; color: white; border: none; padding: 12px 30px; border-radius: 50px; font-weight: 600; cursor: pointer;">
                    Submit Quiz <i class="fas fa-check"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentQuestion = 1;
const totalQuestions = <?php echo count($questions); ?>;
const endTime = <?php echo $endTime; ?> * 1000;

// Timer functionality
function updateTimer() {
    const now = new Date().getTime();
    const distance = endTime - now;
    
    if (distance < 0) {
        document.getElementById('timer').innerHTML = '0:00';
        document.getElementById('quizForm').submit();
        return;
    }
    
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
    document.getElementById('timer').innerHTML = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
    
    // Change color when time is running low
    if (minutes < 5) {
        document.getElementById('timer').style.color = '#F97316';
    }
    if (minutes < 2) {
        document.getElementById('timer').style.color = '#EF4444';
    }
}

setInterval(updateTimer, 1000);

// Question navigation
function changeQuestion(direction) {
    // Hide current question
    document.getElementById('question-' + currentQuestion).style.display = 'none';
    
    // Update question number
    currentQuestion += direction;
    document.getElementById('current-question').textContent = currentQuestion;
    
    // Show new question
    document.getElementById('question-' + currentQuestion).style.display = 'block';
    
    // Update progress bar
    const progress = (currentQuestion / totalQuestions) * 100;
    document.getElementById('progress-bar').style.width = progress + '%';
    document.getElementById('progress-percentage').textContent = Math.round(progress) + '%';
    
    // Update buttons
    document.getElementById('prevBtn').style.display = currentQuestion === 1 ? 'none' : 'inline-block';
    
    if (currentQuestion === totalQuestions) {
        document.getElementById('nextBtn').style.display = 'none';
        document.getElementById('submitBtn').style.display = 'inline-block';
    } else {
        document.getElementById('nextBtn').style.display = 'inline-block';
        document.getElementById('submitBtn').style.display = 'none';
    }
}

// Confirm before submitting
document.getElementById('quizForm').addEventListener('submit', function(e) {
    if (!confirm('Are you sure you want to submit your answers?')) {
        e.preventDefault();
    }
});

// Highlight selected answers
document.querySelectorAll('input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Remove highlight from all labels in this group
        const name = this.getAttribute('name');
        document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
            r.closest('label').style.background = 'white';
            r.closest('label').style.borderColor = '#E2E8F0';
        });
        
        // Highlight selected label
        this.closest('label').style.background = 'linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(249, 115, 22, 0.1))';
        this.closest('label').style.borderColor = '#8B5CF6';
    });
});
</script>

<style>
    label:hover {
        background: #F8FAFC;
        border-color: #8B5CF6;
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>