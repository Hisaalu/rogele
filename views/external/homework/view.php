<?php
// file: views/external/homework/view.php
$pageTitle = 'Homework Details | ROGELE';
require_once __DIR__ . '/../../layouts/header.php';

$homework   = $homework ?? [];
$submission = $submission ?? [];

$dueDateTimestamp = !empty($homework['due_date']) ? strtotime($homework['due_date']) : 0;
$isLate           = !$submission && $dueDateTimestamp > 0 && $dueDateTimestamp < time();
$canResubmit      = (!$submission || ($submission['status'] ?? '') !== 'graded') && ($dueDateTimestamp > time() || !$submission);
$canDelete        = !empty($submission) && ($submission['status'] ?? '') !== 'graded';
?>

<div class="hw-container">
    <header class="hw-header">
        <a href="<?php echo BASE_URL; ?>/external/homework" class="hw-back-btn">
            <i class="fas fa-arrow-left"></i> <span>Back to Homework</span>
        </a>
        <h1 class="hw-title"><?php echo htmlspecialchars($homework['title'] ?? 'Homework Details'); ?></h1>
    </header>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="hw-alert hw-alert-success" role="alert">
            <i class="fas fa-check-circle hw-alert-icon"></i>
            <div><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="hw-alert hw-alert-error" role="alert">
            <i class="fas fa-exclamation-circle hw-alert-icon"></i>
            <div><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        </div>
    <?php endif; ?>

    <main class="hw-card">
        
        <section class="hw-section">
            <h2 class="hw-section-title"><i class="fas fa-info-circle"></i> Assignment Details</h2>
            <div class="hw-grid">
                <div class="hw-meta-item">
                    <span class="hw-meta-label">Subject</span>
                    <span class="hw-meta-value"><?php echo htmlspecialchars($homework['subject_name'] ?? 'N/A'); ?></span>
                </div>
                <div class="hw-meta-item">
                    <span class="hw-meta-label">Class</span>
                    <span class="hw-meta-value"><?php echo htmlspecialchars($homework['class_name'] ?? 'N/A'); ?></span>
                </div>
                <div class="hw-meta-item">
                    <span class="hw-meta-label">Due Date</span>
                    <span class="hw-meta-value <?php echo $isLate ? 'text-danger' : ''; ?>">
                        <?php echo $dueDateTimestamp ? date('F j, Y \a\t h:i A', $dueDateTimestamp) : 'No due date'; ?>
                        <?php if ($isLate): ?>
                            <span class="hw-badge hw-badge-danger">Overdue</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="hw-meta-item">
                    <span class="hw-meta-label">Teacher</span>
                    <span class="hw-meta-value">Tr. <?php echo htmlspecialchars(trim(($homework['teacher_first_name'] ?? '') . ' ' . ($homework['teacher_last_name'] ?? ''))); ?></span>
                </div>
            </div>
        </section>

        <section class="hw-section">
            <h2 class="hw-section-title"><i class="fas fa-align-left"></i> Description</h2>
            <div class="hw-description-box">
                <?php echo nl2br(htmlspecialchars($homework['description'] ?? 'No description provided.')); ?>
            </div>
        </section>

        <?php if (!empty($homework['attachments'])): ?>
            <section class="hw-section">
                <h2 class="hw-section-title"><i class="fas fa-paperclip"></i> Reference Attachments</h2>
                <div class="hw-attachments-grid">
                    <?php foreach ($homework['attachments'] as $attachment): ?>
                        <?php
                            $cleanFileName = basename($attachment['file_path']); 
                            $officialDownloadUrl = "https://docs.raysofgrace.ac.ug/rogele-platform/uploads/homework/" . $cleanFileName;
                        ?>
                        <a href="<?php echo htmlspecialchars($officialDownloadUrl); ?>" target="_blank" rel="noopener noreferrer" class="hw-attachment-card">
                            <div class="hw-file-icon"><i class="fas fa-file-download"></i></div>
                            <div class="hw-file-info">
                                <span class="hw-file-name"><?php echo htmlspecialchars($attachment['file_name']); ?></span>
                                <span class="hw-file-size"><?php echo round(($attachment['file_size'] ?? 0) / 1024, 2); ?> KB</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($submission) && ($submission['status'] ?? '') === 'graded'): ?>
            <section class="hw-section hw-feedback-wrapper">
                <h2 class="hw-section-title text-success"><i class="fas fa-award"></i> Grade & Feedback</h2>
                <div class="hw-grade-card">
                    <div class="hw-grade-badge">
                        <span class="hw-grade-score"><?php echo htmlspecialchars($submission['grade'] ?? '0'); ?>%</span>
                        <span class="hw-grade-label">Your Score</span>
                    </div>
                    <?php if (!empty($submission['feedback'])): ?>
                        <div class="hw-feedback-body">
                            <strong></i> Teacher's Comment</strong>
                            <p><?php echo nl2br(htmlspecialchars($submission['feedback'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($submission) && ($submission['status'] ?? '') !== 'graded'): ?>
            <section class="hw-section hw-submission-wrapper">
                <div class="hw-submission-header">
                    <h2 class="hw-section-title text-primary"><i class="fas fa-check-circle"></i> Your Submission</h2>
                    <?php if ($canDelete): ?>
                        <button type="button" class="hw-btn hw-btn-danger-outline" onclick="confirmDeleteSubmission(<?php echo (int)$submission['id']; ?>, <?php echo (int)$homework['id']; ?>)">
                            <i class="fas fa-trash-alt"></i> Delete Submission
                        </button>
                    <?php endif; ?>
                </div>

                <div class="hw-submission-details">
                    <div class="hw-meta-bar">
                        <span><i class="fas fa-clock"></i> Submitted on <?php echo date('F j, Y \a\t h:i A', strtotime($submission['submitted_at'])); ?></span>
                        <?php if (($submission['status'] ?? '') === 'late'): ?>
                            <span class="hw-badge hw-badge-warning"><i class="fas fa-exclamation-triangle"></i> Submitted Late</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($submission['text_answer'])): ?>
                        <div class="hw-submitted-block">
                            <label class="hw-input-label">Submitted Response</label>
                            <div class="hw-read-box"><?php echo nl2br(htmlspecialchars($submission['text_answer'])); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($submission['files'])): ?>
                        <div class="hw-submitted-block">
                            <label class="hw-input-label">Attached Files</label>
                            <div class="hw-attachments-grid">
                                <?php foreach ($submission['files'] as $file): ?>
                                    <?php 
                                        $cleanSubmissionFile = basename($file['file_path']); 
                                        $officialSubmissionUrl = "https://docs.raysofgrace.ac.ug/rogele-platform/uploads/submissions/" . $cleanSubmissionFile;
                                    ?>
                                    <a href="<?php echo htmlspecialchars($officialSubmissionUrl); ?>" target="_blank" rel="noopener noreferrer" class="hw-attachment-card">
                                        <div class="hw-file-icon"><i class="fas fa-file-alt"></i></div>
                                        <div class="hw-file-info">
                                            <span class="hw-file-name"><?php echo htmlspecialchars($file['file_name']); ?></span>
                                            <span class="hw-file-size"><?php echo round(($file['file_size'] ?? 0) / 1024, 2); ?> KB</span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($canResubmit): ?>
            <section class="hw-section hw-form-wrapper">
                <h2 class="hw-section-title"><i class="fas fa-upload"></i> <?php echo $submission ? 'Resubmit Your Work' : 'Submit Your Work'; ?></h2>
                <form id="homeworkForm" method="POST" enctype="multipart/form-data" action="<?php echo BASE_URL; ?>/external/homework/submit/<?php echo (int)($homework['id'] ?? 0); ?>">
                    <div class="hw-form-group">
                        <label for="text_answer" class="hw-input-label">Your Answer</label>
                        <textarea id="text_answer" name="text_answer" rows="5" class="hw-textarea" placeholder="Type your answer or response here..."><?php echo htmlspecialchars($submission['text_answer'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="hw-form-group">
                        <label class="hw-input-label">Attach Files (Max 5MB per file)</label>
                        <div class="hw-file-dropzone" onclick="document.getElementById('submission_files').click()">
                            <i class="fas fa-cloud-upload-alt hw-dropzone-icon"></i>
                            <p class="hw-dropzone-text">Click or drag files here to upload</p>
                            <span id="file-chosen-text" class="hw-dropzone-sub">No file chosen</span>
                            <input type="file" id="submission_files" name="submission_files[]" multiple class="hw-file-input" onchange="updateFileNameDisplay(this)">
                        </div>
                    </div>
                    
                    <?php if ($isLate): ?>
                        <div class="hw-alert hw-alert-warning">
                            <i class="fas fa-exclamation-triangle hw-alert-icon"></i>
                            <span>This assignment is past its due date. Late submissions may incur penalties.</span>
                        </div>
                    <?php endif; ?>
                    
                    <button type="submit" class="hw-btn hw-btn-primary hw-btn-full">
                        <i class="fas fa-paper-plane"></i> <span><?php echo $submission ? 'Resubmit Homework' : 'Submit Homework'; ?></span>
                    </button>
                </form>
            </section>
        <?php endif; ?>

    </main>
</div>

<div id="deleteModal" class="hw-modal" aria-hidden="true">
    <div class="hw-modal-card">
        <div class="hw-modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h3>
            <button type="button" class="hw-modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="hw-modal-body">
            <p>Are you sure you want to delete your submission?</p>
            <div class="hw-alert hw-alert-error compact">
                This action cannot be undone. You will need to submit your work again.
            </div>
        </div>
        <div class="hw-modal-footer">
            <button type="button" class="hw-btn hw-btn-secondary" onclick="closeModal()">Cancel</button>
            <button type="button" id="confirmDeleteBtn" class="hw-btn hw-btn-danger">
                <i class="fas fa-trash-alt"></i> Delete Submission
            </button>
        </div>
    </div>
</div>

<style>
:root {
    --hw-primary: #7f2677;
    --hw-primary-hover: #671e61;
    --hw-accent: #f06724;
    --hw-accent-hover: #d9581a;
    --hw-bg-light: #f8fafc;
    --hw-text-dark: #000;
    --hw-text-muted: #555;
    --hw-border-color: #e2e8f0;
    --hw-danger: #ef4444;
    --hw-danger-bg: #fef2f2;
    --hw-success: #10b981;
    --hw-success-bg: #f0fdf4;
    --hw-warning: #f59e0b;
    --hw-warning-bg: #fffbeb;
    --hw-radius-sm: 8px;
    --hw-radius-md: 14px;
    --hw-radius-lg: 20px;
    --hw-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 4px 12px -2px rgba(0, 0, 0, 0.02);
}

.hw-container {
    max-width: 920px;
    margin: 0 auto;
    padding: 30px 20px;
    font-family: 'Inter', sans-serif;
    color: var(--hw-text-dark);
}

.hw-header {
    margin-bottom: 24px;
}

.hw-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--hw-text-muted);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 12px;
    transition: color 0.2s ease, transform 0.2s ease;
}

.hw-back-btn:hover {
    color: var(--hw-accent);
    transform: translateX(-3px);
}

.hw-title {
    font-size: 1.85rem;
    font-weight: 800;
    background: var(--hw-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin: 0;
    line-height: 1.25;
}

.hw-card {
    background: #ffffff;
    border-radius: var(--hw-radius-lg);
    box-shadow: var(--hw-shadow);
    border: 1px solid var(--hw-border-color);
    overflow: hidden;
}

.hw-section {
    padding: 28px;
    border-bottom: 1px solid var(--hw-border-color);
}

.hw-section:last-child {
    border-bottom: none;
}

.hw-section-title {
    font-size: 1.15rem;
    font-weight: 700;
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--hw-text-dark);
}

.hw-section-title i {
    color: var(--hw-accent);
}

.hw-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.hw-meta-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.hw-meta-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 700;
    color: var(--hw-text-muted);
}

.hw-meta-value {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--hw-text-dark);
}

.hw-description-box,
.hw-read-box {
    background: var(--hw-bg-light);
    padding: 18px;
    border-radius: var(--hw-radius-md);
    line-height: 1.6;
    color: #555;
    font-size: 0.95rem;
    border: 1px solid #edf2f7;
}

.hw-attachments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 12px;
}

.hw-attachment-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: var(--hw-bg-light);
    border: 1px solid var(--hw-border-color);
    border-radius: var(--hw-radius-md);
    text-decoration: none;
    transition: all 0.2s ease;
}

.hw-attachment-card:hover {
    border-color: var(--hw-accent);
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(240, 103, 36, 0.08);
    transform: translateY(-2px);
}

.hw-file-icon {
    font-size: 1.25rem;
    color: var(--hw-accent);
}

.hw-file-info {
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.hw-file-name {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--hw-text-dark);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hw-file-size {
    font-size: 0.75rem;
    color: var(--hw-text-muted);
}

.hw-feedback-wrapper {
    background: linear-gradient(180deg, var(--hw-success-bg), #ffffff);
}

.hw-grade-card {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.hw-grade-badge {
    align-self: flex-start;
    background: #ffffff;
    border: 2px solid var(--hw-success);
    padding: 12px 24px;
    border-radius: var(--hw-radius-md);
    text-align: center;
}

.hw-grade-score {
    display: block;
    font-size: 2rem;
    font-weight: 800;
    color: var(--hw-success);
    line-height: 1;
}

.hw-grade-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    font-weight: 700;
    color: var(--hw-text-muted);
}

.hw-feedback-body {
    background: #ffffff;
    padding: 16px;
    border-radius: var(--hw-radius-md);
    border: 1px solid #bbf7d0;
}

.hw-feedback-body p {
    color: #2563EB;
}

.hw-submission-wrapper {
    background: linear-gradient(180deg, #f0f9ff, #ffffff);
}

.hw-submission-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
}

.hw-submission-header .hw-section-title {
    margin-bottom: 0;
}

.hw-meta-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.85rem;
    color: var(--hw-text-muted);
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.hw-submitted-block {
    margin-top: 16px;
}

.hw-form-wrapper {
    background: var(--hw-bg-light);
}

.hw-form-group {
    margin-bottom: 20px;
}

.hw-input-label {
    display: block;
    font-size: 0.88rem;
    font-weight: 700;
    margin-bottom: 8px;
    color: var(--hw-text-dark);
}

.hw-textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--hw-border-color);
    border-radius: var(--hw-radius-md);
    font-family: inherit;
    font-size: 0.95rem;
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
    resize: vertical;
}

.hw-textarea:focus {
    outline: none;
    border-color: var(--hw-accent);
    box-shadow: 0 0 0 3px rgba(240, 103, 36, 0.15);
}

.hw-file-dropzone {
    border: 2px dashed var(--hw-border-color);
    border-radius: var(--hw-radius-md);
    padding: 24px;
    text-align: center;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.2s ease;
}

.hw-file-dropzone:hover {
    border-color: var(--hw-accent);
    background: #fffaf8;
}

.hw-dropzone-icon {
    font-size: 2rem;
    color: var(--hw-accent);
    margin-bottom: 8px;
}

.hw-dropzone-text {
    margin: 0;
    font-weight: 600;
    font-size: 0.9rem;
}

.hw-dropzone-sub {
    font-size: 0.78rem;
    color: var(--hw-text-muted);
    display: block;
    margin-top: 4px;
}

.hw-file-input {
    display: none;
}

.hw-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 20px;
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: 50px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.hw-btn-primary {
    background-color: var(--hw-primary);
    color: #ffffff;
}

.hw-btn-primary:hover {
    background-color: var(--hw-primary-hover);
    box-shadow: 0 4px 12px rgba(127, 38, 119, 0.25);
    transform: translateY(-1px);
}

.hw-btn-danger-outline {
    background: var(--hw-danger-bg);
    color: var(--hw-danger);
    border: 1px solid #fecaca;
    padding: 6px 14px;
    font-size: 0.82rem;
    border-radius: var(--hw-radius-sm);
}

.hw-btn-danger-outline:hover {
    background: var(--hw-danger);
    color: #ffffff;
}

.hw-btn-danger {
    background: var(--hw-danger);
    color: #ffffff;
}

.hw-btn-danger:hover {
    background: #dc2626;
}

.hw-btn-secondary {
    background: #e2e8f0;
    color: var(--hw-text-dark);
}

.hw-btn-secondary:hover {
    background: #cbd5e1;
}

.hw-btn-full {
    width: 100%;
    padding: 14px;
    font-size: 1rem;
}

.hw-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.72rem;
    font-weight: 700;
}

.hw-badge-danger { background: var(--hw-danger-bg); color: var(--hw-danger); }
.hw-badge-warning { background: var(--hw-warning-bg); color: #b45309; }

.hw-alert {
    padding: 14px 18px;
    border-radius: var(--hw-radius-md);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.9rem;
}

.hw-alert-icon { font-size: 1.15rem; flex-shrink: 0; }
.hw-alert-success { background: var(--hw-success-bg); color: #15803d; border: 1px solid #a7f3d0; }
.hw-alert-error { background: var(--hw-danger-bg); color: #b91c1c; border: 1px solid #fecaca; }
.hw-alert-warning { background: var(--hw-warning-bg); color: #b45309; border: 1px solid #fde68a; }

.hw-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
    backdrop-filter: blur(4px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.hw-modal-card {
    background: #ffffff;
    border-radius: var(--hw-radius-lg);
    max-width: 440px;
    width: 100%;
    overflow: hidden;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
    animation: modalPop 0.2s ease-out;
}

.hw-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    background: var(--hw-danger-bg);
    border-bottom: 1px solid #fecaca;
}

.hw-modal-header h3 {
    margin: 0;
    color: #b91c1c;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.hw-modal-close {
    background: none;
    border: none;
    font-size: 1.4rem;
    cursor: pointer;
    color: var(--hw-text-muted);
}

.hw-modal-body { padding: 20px; }
.hw-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 20px;
    border-top: 1px solid var(--hw-border-color);
}

@keyframes modalPop {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

@media (max-width: 640px) {
    .hw-section { padding: 20px; }
    .hw-grid { grid-template-columns: 1fr; }
    .hw-submission-header { flex-direction: column; align-items: flex-start; }
    .hw-btn-danger-outline { width: 100%; text-align: center; }
}
</style>

<script>
let submissionToDelete = null;
let homeworkIdToRedirect = null;

function updateFileNameDisplay(input) {
    const displaySpan = document.getElementById('file-chosen-text');
    if (input.files && input.files.length > 0) {
        if (input.files.length === 1) {
            displaySpan.textContent = "Selected: " + input.files[0].name;
        } else {
            displaySpan.textContent = input.files.length + " files selected";
        }
        displaySpan.style.color = "var(--hw-primary)";
        displaySpan.style.fontWeight = "600";
    } else {
        displaySpan.textContent = "No file chosen";
        displaySpan.style.color = "var(--hw-text-muted)";
    }
}

function confirmDeleteSubmission(submissionId, homeworkId) {
    submissionToDelete = submissionId;
    homeworkIdToRedirect = homeworkId;
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    submissionToDelete = null;
    homeworkIdToRedirect = null;
}

window.addEventListener('click', function(e) {
    if (e.target === document.getElementById('deleteModal')) {
        closeModal();
    }
});

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

document.getElementById('homeworkForm')?.addEventListener('submit', function(e) {
    const textAnswer = document.getElementById('text_answer').value.trim();
    const fileInput = document.getElementById('submission_files');
    const maxSizeBytes = 5 * 1024 * 1024; // 5MB

    if (textAnswer === '' && (!fileInput || fileInput.files.length === 0)) {
        e.preventDefault();
        alert("Please provide a text answer or attach a file before submitting.");
        return false;
    }

    if (fileInput && fileInput.files.length > 0) {
        for (let i = 0; i < fileInput.files.length; i++) {
            if (fileInput.files[i].size > maxSizeBytes) {
                e.preventDefault();
                alert("File '" + fileInput.files[i].name + "' exceeds the 5MB size limit.");
                return false;
            }
        }
    }
});
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>