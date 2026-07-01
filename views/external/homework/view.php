<?php
// file: views/external/homework/view.php
$pageTitle = 'Homework Details | ROGELE';
require_once __DIR__ . '/../../layouts/header.php';

$homework = $homework ?? [];
$submission = $submission ?? [];
$isLate = !$submission && strtotime($homework['due_date'] ?? '') < time();
$canResubmit = (!$submission || $submission['status'] !== 'graded') && (!$submission || strtotime($homework['due_date']) > time());
$canDelete = $submission && $submission['status'] !== 'graded';
?>

<div class="homework-detail-container">
    <div class="page-header">
        <a href="<?php echo BASE_URL; ?>/external/homework" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Homework
        </a>
        <h1 class="page-title"><?php echo htmlspecialchars($homework['title'] ?? ''); ?></h1>
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

    <div class="homework-detail-card">
        <!-- Assignment Details Section -->
        <div class="info-section">
            <h3><i class="fas fa-info-circle"></i> Assignment Details</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Subject</span>
                    <span class="info-value"><?php echo htmlspecialchars($homework['subject_name'] ?? ''); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Class</span>
                    <span class="info-value"><?php echo htmlspecialchars($homework['class_name'] ?? ''); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Due Date</span>
                    <span class="info-value <?php echo $isLate ? 'text-danger' : ''; ?>">
                        <?php echo date('F j, Y h:i A', strtotime($homework['due_date'] ?? '')); ?>
                        <?php if ($isLate): ?>
                            <span class="late-badge">Overdue</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Teacher</span>
                    <span class="info-value">Tr. <?php echo htmlspecialchars($homework['teacher_first_name'] . ' ' . $homework['teacher_last_name']); ?></span>
                </div>
            </div>
        </div>

        <!-- Description Section -->
        <div class="info-section">
            <h3><i class="fas fa-align-left"></i> Description</h3>
            <div class="description-text">
                <?php echo nl2br(htmlspecialchars($homework['description'] ?? 'No description provided.')); ?>
            </div>
        </div>

        <!-- Attachments Section -->
        <?php if (!empty($homework['attachments'])): ?>
            <div class="info-section">
                <h3><i class="fas fa-paperclip"></i> Attachments</h3>
                <div class="attachments-list">
                    <?php foreach ($homework['attachments'] as $attachment): ?>
                        <a href="<?php echo BASE_URL; ?>/external/homework/download-attachment/<?php echo $attachment['id']; ?>" class="attachment-link">
                            <i class="fas fa-download"></i>
                            <?php echo htmlspecialchars($attachment['file_name']); ?>
                            <span class="file-size"><?php echo round($attachment['file_size'] / 1024, 2); ?> KB</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Graded Submission Section -->
        <?php if ($submission && $submission['status'] === 'graded'): ?>
            <div class="feedback-section">
                <h3><i class="fas fa-star"></i> Your Grade & Feedback</h3>
                <div class="grade-card">
                    <div class="grade-score">
                        <span class="grade-label">Your Score</span>
                        <span class="grade-value"><?php echo $submission['grade']; ?>%</span>
                    </div>
                    <?php if (!empty($submission['feedback'])): ?>
                        <div class="feedback-text">
                            <span class="feedback-label">Teacher's Feedback</span>
                            <p><?php echo nl2br(htmlspecialchars($submission['feedback'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Ungraded Submission Section with Delete Option -->
        <?php if ($submission && $submission['status'] !== 'graded'): ?>
            <div class="submission-info">
                <div class="submission-header">
                    <h3><i class="fas fa-check-circle"></i> Your Submission</h3>
                    <?php if ($canDelete): ?>
                        <button class="btn-delete-submission" onclick="confirmDeleteSubmission(<?php echo $submission['id']; ?>, <?php echo $homework['id']; ?>)">
                            <i class="fas fa-trash-alt"></i> Delete Submission
                        </button>
                    <?php endif; ?>
                </div>
                <div class="submission-details">
                    <div class="submission-meta">
                        <span><i class="fas fa-clock"></i> Submitted on <?php echo date('F j, Y h:i A', strtotime($submission['submitted_at'])); ?></span>
                        <?php if ($submission['status'] === 'late'): ?>
                            <span class="late-submission-badge"><i class="fas fa-exclamation-triangle"></i> Late Submission</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($submission['text_answer'])): ?>
                        <div class="submitted-answer">
                            <strong>Your Answer</strong>
                            <div class="answer-box"><?php echo nl2br(htmlspecialchars($submission['text_answer'])); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($submission['files'])): ?>
                        <div class="submitted-files">
                            <strong>Your Attachments</strong>
                            <div class="file-list">
                                <?php foreach ($submission['files'] as $file): ?>
                                    <div class="file-item">
                                        <i class="fas fa-file-alt"></i>
                                        <span class="file-name"><?php echo htmlspecialchars($file['file_name']); ?></span>
                                        <span class="file-size">(<?php echo round($file['file_size'] / 1024, 2); ?> KB)</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Submit/Resubmit Form -->
        <?php if ($canResubmit): ?>
            <div class="submission-form">
                <h3><i class="fas fa-upload"></i> <?php echo $submission ? 'Resubmit Your Work' : 'Submit Your Work'; ?></h3>
                <form method="POST" enctype="multipart/form-data" action="<?php echo BASE_URL; ?>/external/homework/submit/<?php echo $homework['id']; ?>">
                    <div class="form-group">
                        <label for="text_answer">Your Answer <span class="optional">(Optional)</span></label>
                        <textarea id="text_answer" name="text_answer" rows="5" placeholder="Type your answer here..."><?php echo htmlspecialchars($submission['text_answer'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="submission_files">Attach Files</label>
                        <input type="file" id="submission_files" name="submission_files[]" multiple class="file-input">
                    </div>
                    
                    <?php if ($isLate): ?>
                        <div class="warning-message">
                            <i class="fas fa-exclamation-triangle"></i>
                            This homework is past the due date. Late submissions may be penalized.
                        </div>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> <?php echo $submission ? 'Resubmit Homework' : 'Submit Homework'; ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Delete Submission</h3>
            <span class="modal-close">&times;</span>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete your submission?</p>
            <p class="warning-text">This action cannot be undone. You will need to resubmit your work.</p>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel-modal">Cancel</button>
            <button id="confirmDeleteBtn" class="btn-confirm-delete">
                <i class="fas fa-trash-alt"></i> Delete Submission
            </button>
        </div>
    </div>
</div>

<style>
.homework-detail-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 30px 20px;
}

/* Back Link */
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

/* Page Title */
.page-title {
    font-size: 2rem;
    font-weight: 700;
    background-color: #7f2677;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 20px;
}

/* Main Card */
.homework-detail-card {
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
}

/* Info Sections */
.info-section {
    padding: 28px 30px;
    border-bottom: 1px solid #F1F5F9;
}

.info-section:last-child {
    border-bottom: none;
}

.info-section h3 {
    color: #000;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.2rem;
}

.info-section h3 i {
    color: #f06724;
}

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-label {
    font-weight: 600;
    color: #555;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}

.info-value {
    font-weight: 600;
    color: #000;
    font-size: 1rem;
}

.text-danger {
    color: #EF4444;
}

.late-badge {
    margin-left: 8px;
    padding: 2px 10px;
    background: #FEF2F2;
    border-radius: 20px;
    font-size: 0.7rem;
    color: #B91C1C;
    font-weight: 500;
}

/* Description */
.description-text {
    background: #F8FAFC;
    padding: 18px;
    border-radius: 16px;
    line-height: 1.7;
    color: #475569;
    font-size: 0.95rem;
}

/* Attachments */
.attachments-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.attachment-link {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: #F8FAFC;
    border-radius: 12px;
    color: #f06724;
    text-decoration: none;
    transition: all 0.3s;
    border: 1px solid transparent;
}

.attachment-link:hover {
    background: #F1F5F9;
    border-color: #f06724;
    transform: translateX(5px);
}

.file-size {
    color: #000;
    font-size: 0.75rem;
    margin-left: auto;
}

/* Feedback Section */
.feedback-section {
    margin: 20px 30px 30px 30px;
    padding: 25px;
    background: linear-gradient(135deg, #F0FDF4, #FFFFFF);
    border-radius: 20px;
    border: 1px solid #BBF7D0;
}

.feedback-section h3 {
    color: #166534;
    margin-bottom: 20px;
}

.grade-card {
    text-align: center;
}

.grade-score {
    margin-bottom: 20px;
}

.grade-label {
    display: block;
    font-size: 0.85rem;
    color: #000;
    margin-bottom: 5px;
}

.grade-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: #10B981;
}

.feedback-text {
    text-align: left;
    background: white;
    padding: 20px;
    border-radius: 16px;
    margin-top: 15px;
}

.feedback-label {
    display: block;
    font-weight: 600;
    color: #000;
    margin-bottom: 8px;
}

/* Submission Section */
.submission-info {
    margin: 20px 30px 30px 30px;
    padding: 25px;
    background: linear-gradient(135deg, #EFF6FF, #FFFFFF);
    border-radius: 20px;
    border: 1px solid #BFDBFE;
}

.submission-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.submission-header h3 {
    color: #1E40AF;
    margin: 0;
}

.btn-delete-submission {
    background: #FEF2F2;
    color: #DC2626;
    border: 1px solid #FECACA;
    padding: 8px 16px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-delete-submission:hover {
    background: #DC2626;
    color: white;
    border-color: #DC2626;
}

.submission-meta {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #E2E8F0;
    flex-wrap: wrap;
}

.submission-meta span {
    font-size: 0.85rem;
    color: #555;
    display: flex;
    align-items: center;
    gap: 5px;
}

.late-submission-badge {
    background: #FEF3C7;
    color: #92400E;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.7rem;
}

.submitted-answer, .submitted-files {
    margin-bottom: 20px;
}

.submitted-answer strong, .submitted-files strong {
    display: block;
    color: #000;
    margin-bottom: 10px;
}

.answer-box {
    background: white;
    padding: 15px;
    border-radius: 12px;
    border: 1px solid #E2E8F0;
    line-height: 1.6;
    color: #475569;
}

.file-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.file-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 15px;
    background: white;
    border-radius: 10px;
    border: 1px solid #E2E8F0;
}

.file-item i {
    color: #f06724;
}

.file-name {
    flex: 1;
    color: #000;
}

/* Submission Form */
.submission-form {
    margin: 20px 30px 30px 30px;
    padding: 25px;
    background: #F8FAFC;
    border-radius: 20px;
}

.submission-form h3 {
    color: #000;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.submission-form h3 i {
    color: #f06724;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #000;
}

.optional {
    font-weight: normal;
    font-size: 0.8rem;
    color: #000;
}

.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    font-family: inherit;
    resize: vertical;
    transition: all 0.3s;
}

.form-group textarea:focus {
    outline: none;
    border-color: #f06724;
    box-shadow: 0 0 0 2px rgba(240, 103, 36, 0.25);
}

.file-input {
    width: 100%;
    padding: 12px;
    border: 2px dashed #E2E8F0;
    border-radius: 12px;
    background: white;
    cursor: pointer;
}

.file-input:hover {
    border-color: #f06724;
}

.warning-message {
    padding: 12px 16px;
    background: #FEF3C7;
    border-radius: 12px;
    color: #92400E;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.85rem;
}

.btn-submit {
    width: 100%;
    background-color: #7f2677;
    color: white;
    border: none;
    padding: 14px;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(240, 103, 36, 0.3);
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 20px;
    max-width: 450px;
    width: 90%;
    overflow: hidden;
    animation: modalSlideUp 0.3s ease;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    background: #FEF2F2;
    border-bottom: 2px solid #FECACA;
}

.modal-header h3 {
    margin: 0;
    color: #B91C1C;
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal-close {
    font-size: 1.5rem;
    cursor: pointer;
    color: #94A3B8;
    transition: color 0.2s;
}

.modal-close:hover {
    color: #000;
}

.modal-body {
    padding: 24px;
}

.modal-body p {
    margin-bottom: 16px;
    color: #000;
}

.warning-text {
    background: #FEF2F2;
    padding: 12px;
    border-radius: 10px;
    color: #B91C1C;
    font-size: 0.85rem;
    border-left: 3px solid #EF4444;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 20px 24px;
    border-top: 1px solid #E2E8F0;
}

.btn-cancel-modal {
    padding: 10px 20px;
    background: #F1F5F9;
    color: #475569;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel-modal:hover {
    background: #E2E8F0;
}

.btn-confirm-delete {
    padding: 10px 20px;
    background: #EF4444;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-confirm-delete:hover {
    background: #DC2626;
    transform: translateY(-1px);
}

/* Animations */
@keyframes modalSlideUp {
    from {
        transform: translateY(30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
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
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .info-section {
        padding: 20px;
    }
    
    .feedback-section, .submission-info, .submission-form {
        margin: 20px;
        padding: 20px;
    }
    
    .submission-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .btn-delete-submission {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .homework-detail-container {
        padding: 20px 15px;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .grade-value {
        font-size: 2rem;
    }
}
</style>

<script>
// Delete submission modal
let submissionToDelete = null;
let homeworkIdToRedirect = null;

function confirmDeleteSubmission(submissionId, homeworkId) {
    submissionToDelete = submissionId;
    homeworkIdToRedirect = homeworkId;
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
    submissionToDelete = null;
    homeworkIdToRedirect = null;
}

// Modal close handlers
document.querySelector('.modal-close')?.addEventListener('click', closeModal);
document.querySelector('.btn-cancel-modal')?.addEventListener('click', closeModal);
window.addEventListener('click', function(e) {
    if (e.target === document.getElementById('deleteModal')) {
        closeModal();
    }
});

// Confirm delete
document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
    if (!submissionToDelete) return;
    
    fetch('<?php echo BASE_URL; ?>/external/homework/delete-submission/' + submissionToDelete, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '<?php echo BASE_URL; ?>/external/homework/view/' + homeworkIdToRedirect;
        } else {
            alert(data.error || 'Failed to delete submission');
            closeModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting submission');
        closeModal();
    });
});
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>