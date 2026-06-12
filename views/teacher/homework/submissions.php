<?php
// File: /views/teacher/homework/submissions.php
$pageTitle = 'Homework Submissions | ROGELE';
require_once __DIR__ . '/../../layouts/header.php';

$homework = $homework ?? [];
$submissions = $submissions ?? [];
$isAcceptingSubmissions = $homework['is_active'] ?? true;

// Calculate statistics
$totalSubmissions = count($submissions);
$gradedCount = count(array_filter($submissions, function($s) { return $s['status'] === 'graded'; }));
$lateCount = count(array_filter($submissions, function($s) { return $s['status'] === 'late'; }));
$averageGrade = 0;
if ($gradedCount > 0) {
    $grades = array_filter(array_column($submissions, 'grade'));
    $averageGrade = !empty($grades) ? round(array_sum($grades) / count($grades), 1) : 0;
}
?>

<div class="submissions-container">
    <div class="page-header">
        <div>
            <a href="<?php echo BASE_URL; ?>/teacher/homework" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Homework
            </a>
            <h1 class="page-title">
                <i class="fas fa-users"></i>
                Homework Submissions
            </h1>
            <p class="page-subtitle"><?php echo htmlspecialchars($homework['title'] ?? ''); ?></p>
        </div>
        
        <div class="header-actions">
            <!-- Stop/Start Submissions Toggle -->
            <div class="submission-toggle">
                <span class="toggle-label">
                    <i class="fas fa-door-open"></i>
                    Accepting Submissions
                </span>
                <label class="toggle-switch">
                    <input type="checkbox" id="toggleSubmissions" <?php echo $isAcceptingSubmissions ? 'checked' : ''; ?> 
                           data-homework-id="<?php echo $homework['id']; ?>">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <!-- Export Button -->
            <?php if (!empty($submissions)): ?>
                <button onclick="exportGrades()" class="btn-export">
                    <i class="fas fa-download"></i>
                    Export Grades (CSV)
                </button>
            <?php endif; ?>
        </div>
    </div>

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

    <!-- Status Banner -->
    <?php if (!$isAcceptingSubmissions): ?>
        <div class="status-banner closed">
            <i class="fas fa-ban"></i>
            <div>
                <strong>Submissions Closed</strong>
                <p>This homework is no longer accepting submissions. Students cannot submit or resubmit their work.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="status-banner open">
            <i class="fas fa-check-circle"></i>
            <div>
                <strong>Submissions Open</strong>
                <p>This homework is currently accepting submissions from students.</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <?php if (!empty($submissions)): ?>
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon" style="background-color: #f06724;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo $totalSubmissions; ?></span>
                    <span class="stat-label">Total Submissions</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: #f06724;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo $gradedCount; ?></span>
                    <span class="stat-label">Graded</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: #f06724;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo $lateCount; ?></span>
                    <span class="stat-label">Late</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: #f06724;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo $averageGrade; ?>%</span>
                    <span class="stat-label">Average Grade</span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($submissions)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No Submissions Yet</h3>
            <p>Students haven't submitted this homework yet.</p>
        </div>
    <?php else: ?>
        <div class="submissions-grid">
            <?php foreach ($submissions as $index => $submission): ?>
                <div class="submission-card" data-submission-id="<?php echo $submission['id']; ?>">
                    <div class="submission-header">
                        <div class="student-info">
                            <div class="student-avatar">
                                <?php if (!empty($submission['profile_photo'])): ?>
                                    <img src="<?php echo BASE_URL; ?>/<?php echo $submission['profile_photo']; ?>" alt="">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?php echo strtoupper(substr($submission['first_name'] ?? '', 0, 1) . substr($submission['last_name'] ?? '', 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h3><?php echo htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name']); ?></h3>
                                <p><?php echo htmlspecialchars($submission['email']); ?></p>
                            </div>
                        </div>
                        <div class="submission-status">
                            <?php if ($submission['status'] === 'graded'): ?>
                                <span class="badge graded"><i class="fas fa-check-circle"></i> Graded</span>
                            <?php elseif ($submission['status'] === 'late'): ?>
                                <span class="badge late"><i class="fas fa-exclamation-triangle"></i> Late</span>
                            <?php else: ?>
                                <span class="badge submitted"><i class="fas fa-clock"></i> Submitted</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="submission-details">
                        <div class="detail-item">
                            <span class="detail-label">Submitted:</span>
                            <span class="detail-value"><?php echo date('M d, Y h:i A', strtotime($submission['submitted_at'])); ?></span>
                        </div>
                        
                        <?php if (!empty($submission['text_answer'])): ?>
                            <div class="detail-item">
                                <span class="detail-label">Answer:</span>
                                <div class="answer-text"><?php echo nl2br(htmlspecialchars($submission['text_answer'])); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($submission['files'])): ?>
                            <div class="detail-item">
                                <span class="detail-label">Attachments:</span>
                                <div class="file-list">
                                    <?php foreach ($submission['files'] as $file): ?>
                                        <a href="<?php echo BASE_URL; ?>/teacher/homework/download-file/<?php echo $file['id']; ?>" class="file-link">
                                            <i class="fas fa-download"></i>
                                            <?php echo htmlspecialchars($file['file_name']); ?>
                                            <span class="file-size">(<?php echo round($file['file_size'] / 1024, 2); ?> KB)</span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Grade & Feedback Section -->
                        <div class="grade-feedback-section">
                            <h4><i class="fas fa-star"></i> Grade & Feedback</h4>
                            
                            <?php if ($submission['status'] === 'graded'): ?>
                                <div class="grade-display-mode" id="display-mode-<?php echo $submission['id']; ?>">
                                    <div class="grade-info">
                                        <div class="grade-row">
                                            <span class="grade-label">Grade:</span>
                                            <span class="grade-value"><?php echo $submission['grade']; ?>%</span>
                                            <button class="btn-edit-grade" onclick="toggleEditMode(<?php echo $submission['id']; ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </div>
                                        <?php if (!empty($submission['feedback'])): ?>
                                            <div class="feedback-row">
                                                <span class="feedback-label">Feedback:</span>
                                                <p><?php echo nl2br(htmlspecialchars($submission['feedback'])); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="grade-edit-mode" id="edit-mode-<?php echo $submission['id']; ?>" style="display: none;">
                                    <div class="form-group">
                                        <label>Grade (%)</label>
                                        <input type="number" id="grade_<?php echo $submission['id']; ?>" class="grade-input" 
                                               value="<?php echo $submission['grade']; ?>" min="0" max="100" step="0.01">
                                    </div>
                                    <div class="form-group">
                                        <label>Feedback</label>
                                        <textarea id="feedback_<?php echo $submission['id']; ?>" class="feedback-input" rows="3"><?php echo htmlspecialchars($submission['feedback'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="edit-actions">
                                        <button onclick="updateGrade(<?php echo $submission['id']; ?>)" class="btn-save-grade">
                                            <i class="fas fa-save"></i> Save Changes
                                        </button>
                                    </div>
                                </div>
                                
                            <?php else: ?>
                                <div class="grade-form">
                                    <div class="form-group">
                                        <label>Grade (%)</label>
                                        <input type="number" id="grade_<?php echo $submission['id']; ?>" class="grade-input" 
                                               placeholder="Enter grade" min="0" max="100" step="0.01">
                                    </div>
                                    <div class="form-group">
                                        <label>Feedback</label>
                                        <textarea id="feedback_<?php echo $submission['id']; ?>" class="feedback-input" rows="3" 
                                                  placeholder="Provide feedback to the student..."></textarea>
                                    </div>
                                    <button onclick="gradeSubmission(<?php echo $submission['id']; ?>)" class="btn-grade">
                                        <i class="fas fa-check-circle"></i> Submit Grade
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.submissions-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 30px 20px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #000;
    text-decoration: none;
    margin-bottom: 20px;
    transition: all 0.3s;
}

.back-link:hover {
    color: #f06724;
    transform: translateX(-3px);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 20px;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    background-color: #7f2677;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
}

.page-subtitle {
    color: #555;
    font-size: 1rem;
}

/* Submission Toggle */
.submission-toggle {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #F8FAFC;
    padding: 8px 16px;
    border-radius: 50px;
    border: 1px solid #E2E8F0;
}

.toggle-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    color: #1E293B;
}

.toggle-label i {
    color: #f06724;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.3s;
    border-radius: 34px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: #10B981;
}

input:checked + .toggle-slider:before {
    transform: translateX(26px);
}

/* Status Banner */
.status-banner {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
}

.status-banner.open {
    background: #F0FDF4;
    border: 1px solid #BBF7D0;
    color: #166534;
}

.status-banner.closed {
    background: #FEF2F2;
    border: 1px solid #FECACA;
    color: #B91C1C;
}

.status-banner i {
    font-size: 1.5rem;
}

.status-banner strong {
    display: block;
    margin-bottom: 3px;
}

.status-banner p {
    font-size: 0.85rem;
    margin: 0;
}

/* Export Button */
.btn-export {
    background: linear-gradient(135deg, #10B981, #059669);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-export:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
}

/* Statistics Cards */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border: 1px solid #E2E8F0;
}

.stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: white;
}

.stat-info {
    flex: 1;
}

.stat-value {
    display: block;
    font-size: 1.3rem;
    font-weight: 700;
    color: #000;
    line-height: 1.2;
}

.stat-label {
    display: block;
    font-size: 0.7rem;
    color: #555;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Submissions Grid */
.submissions-grid {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.submission-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    border: 1px solid #E2E8F0;
    transition: all 0.3s;
}

.submission-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border-color: #f06724;
}

.submission-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #E2E8F0;
    flex-wrap: wrap;
    gap: 15px;
}

.student-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.student-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    background-color: #7f2677;
}

.student-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    color: #7f2677;
    font-size: 1.2rem;
    font-weight: 700;
}

.student-info h3 {
    color: #1E293B;
    font-size: 1rem;
    margin-bottom: 3px;
}

.student-info p {
    color: #64748B;
    font-size: 0.85rem;
}

.badge {
    padding: 5px 14px;
    border-radius: 30px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.badge.submitted {
    background: #EFF6FF;
    color: #1E40AF;
}

.badge.graded {
    background: #F0FDF4;
    color: #166534;
}

.badge.late {
    background: #FEF3C7;
    color: #92400E;
}

.submission-details {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.detail-item {
    margin-bottom: 10px;
}

.detail-label {
    font-weight: 600;
    color: #555;
    margin-right: 10px;
    display: inline-block;
    min-width: 100px;
    font-size: 0.85rem;
}

.detail-value {
    color: #000;
    font-size: 0.9rem;
}

.answer-text {
    margin-top: 5px;
    padding: 12px;
    background: #F8FAFC;
    border-radius: 10px;
    color: #555;
    font-size: 0.9rem;
    line-height: 1.5;
}

.file-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 5px;
}

.file-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #f06724;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.3s;
}

.file-link:hover {
    color: #7f2677;
    transform: translateX(5px);
}

.file-size {
    color: #555;
    font-size: 0.7rem;
}

/* Grade & Feedback Section */
.grade-feedback-section {
    margin-top: 20px;
    padding: 20px;
    background: #F8FAFC;
    border-radius: 16px;
}

.grade-feedback-section h4 {
    color: #000;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.grade-feedback-section h4 i {
    color: #f06724;
}

.grade-info {
    background: white;
    padding: 15px;
    border-radius: 12px;
}

.grade-row {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 10px;
    flex-wrap: wrap;
}

.grade-label {
    font-weight: 600;
    color: #555;
}

.grade-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #10B981;
}

.btn-edit-grade {
    background: #EFF6FF;
    color: #2563EB;
    border: none;
    padding: 6px 14px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.75rem;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-edit-grade:hover {
    background: #2563EB;
    color: white;
}

.grade-form {
    background: white;
    padding: 15px;
    border-radius: 12px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    color: #555;
    font-size: 0.85rem;
}

.grade-input {
    width: 200px;
    padding: 10px 14px;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.grade-input:focus {
    outline: none;
    border-color: #f06724;
    box-shadow: 0 0 0 2px rgba(240, 103, 36, 0.25);
}

.feedback-input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    font-size: 0.9rem;
    font-family: inherit;
    resize: vertical;
    transition: all 0.3s;
}

.feedback-input:focus {
    outline: none;
    border-color: #f06724;
    box-shadow: 0 0 0 2px rgba(240, 103, 36, 0.25);
}

.edit-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn-save-grade, .btn-grade {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 0.85rem;
}

.btn-save-grade {
    background: #10B981;
    color: white;
    border: none;
}

.btn-save-grade:hover {
    background: #059669;
    transform: translateY(-1px);
}

.btn-grade {
    background-color: #7f2677;
    color: white;
    border: none;
}

.btn-grade:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(240, 103, 36, 0.3);
}

.feedback-row {
    margin-top: 10px;
}

.feedback-row p {
    margin-top: 8px;
    padding: 12px;
    background: #F8FAFC;
    border-radius: 8px;
    color: #475569;
    font-size: 0.85rem;
    line-height: 1.5;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 20px;
}

.empty-state  p {
    color: #555;
}

.empty-state i {
    font-size: 4rem;
    color: #CBD5E1;
    margin-bottom: 20px;
}

/* Alerts */
.alert {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
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
    .stats-cards {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .submission-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .grade-row {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .btn-edit-grade {
        margin-top: 5px;
    }
    
    .page-header {
        flex-direction: column;
    }
    
    .header-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .btn-export {
        width: auto;
    }
}

@media (max-width: 480px) {
    .stats-cards {
        grid-template-columns: 1fr;
    }
    
    .detail-label {
        display: block;
        margin-bottom: 5px;
    }
    
    .header-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .submission-toggle {
        justify-content: center;
    }
    
    .btn-export {
        justify-content: center;
    }
}
</style>

<script>
// Toggle submissions (stop/start receiving submissions)
const toggleSwitch = document.getElementById('toggleSubmissions');
if (toggleSwitch) {
    toggleSwitch.addEventListener('change', function() {
        const homeworkId = this.dataset.homeworkId;
        const isActive = this.checked ? 1 : 0;
        
        fetch('<?php echo BASE_URL; ?>/teacher/homework/toggle-submissions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                homework_id: homeworkId,
                is_active: isActive
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Failed to update submission status');
                this.checked = !this.checked;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
            this.checked = !this.checked;
        });
    });
}

// Export grades to CSV
function exportGrades() {
    const submissions = <?php echo json_encode($submissions); ?>;
    const homeworkTitle = "<?php echo htmlspecialchars($homework['title'] ?? 'Homework'); ?>";
    
    // Prepare CSV data
    const headers = ['Student Name', 'Email', 'Status', 'Submitted At', 'Grade (%)', 'Feedback'];
    const rows = [];
    
    submissions.forEach(sub => {
        rows.push([
            sub.first_name + ' ' + sub.last_name,
            sub.email,
            sub.status,
            new Date(sub.submitted_at).toLocaleString(),
            sub.grade || 'Not graded',
            sub.feedback || ''
        ]);
    });
    
    // Create CSV content
    let csvContent = headers.join(',') + '\n';
    rows.forEach(row => {
        const escapedRow = row.map(cell => {
            if (typeof cell === 'string' && (cell.includes(',') || cell.includes('"'))) {
                return '"' + cell.replace(/"/g, '""') + '"';
            }
            return cell;
        });
        csvContent += escapedRow.join(',') + '\n';
    });
    
    // Download file
    const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    const filename = `${homeworkTitle.replace(/[^a-z0-9]/gi, '_')}_grades_${new Date().toISOString().slice(0,19).replace(/:/g, '-')}.csv`;
    
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    
    // Show success message
    showToast('Grades exported successfully!', 'success');
}

// Toast notification
function showToast(message, type = 'success') {
    let toast = document.getElementById('customToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'customToast';
        document.body.appendChild(toast);
        
        const style = document.createElement('style');
        style.textContent = `
            #customToast {
                position: fixed;
                bottom: 20px;
                right: 20px;
                padding: 12px 24px;
                border-radius: 8px;
                background: #10B981;
                color: white;
                font-weight: 500;
                z-index: 9999;
                opacity: 0;
                transition: opacity 0.3s ease;
                pointer-events: none;
            }
            #customToast.error { background: #EF4444; }
            #customToast.show { opacity: 1; }
        `;
        document.head.appendChild(style);
    }
    
    toast.className = type === 'error' ? 'error show' : 'show';
    toast.textContent = message;
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Function to toggle edit mode
function toggleEditMode(submissionId) {
    document.getElementById('display-mode-' + submissionId).style.display = 'none';
    document.getElementById('edit-mode-' + submissionId).style.display = 'block';
}

// Function to update grade (for editing existing grades)
function updateGrade(submissionId) {
    const grade = document.getElementById('grade_' + submissionId).value;
    const feedback = document.getElementById('feedback_' + submissionId).value;
    
    if (!grade) {
        alert('Please enter a grade');
        return;
    }
    
    if (grade < 0 || grade > 100) {
        alert('Grade must be between 0 and 100');
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>/teacher/homework/grade-submission', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'submission_id=' + submissionId + '&grade=' + grade + '&feedback=' + encodeURIComponent(feedback)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Failed to update grade');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

// Function to grade new submission
function gradeSubmission(submissionId) {
    const grade = document.getElementById('grade_' + submissionId).value;
    const feedback = document.getElementById('feedback_' + submissionId).value;
    
    if (!grade) {
        alert('Please enter a grade');
        return;
    }
    
    if (grade < 0 || grade > 100) {
        alert('Grade must be between 0 and 100');
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>/teacher/homework/grade-submission', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'submission_id=' + submissionId + '&grade=' + grade + '&feedback=' + encodeURIComponent(feedback)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Failed to grade submission');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>