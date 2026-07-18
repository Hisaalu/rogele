<?php
// file: views/teacher/homework/edit.php
$pageTitle = 'Edit Homework | ROGELE';
require_once __DIR__ . '/../../layouts/header.php';

$homework = $homework ?? [];
$classes = $classes ?? [];
$subjects = $subjects ?? [];
$allSubjects = $allSubjects ?? [];
?>

<div class="edit-homework-container">
    <div class="page-header">
        <a href="<?php echo BASE_URL; ?>/teacher/homework" class="back-link">
            <i class="fas fa-arrow-left"></i> <span>Back to Homework</span>
        </a>
        <h1 class="page-title">
            <i class="fas fa-edit"></i>
            Edit Homework
        </h1>
        <p class="page-subtitle">Update homework details for your students</p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="homework-form">
        <div class="form-card">
            <div class="form-group">
                <label for="title">Homework Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($homework['title'] ?? ''); ?>" placeholder="e.g., Mathematics Assignment 1">
            </div>

            <div class="form-grid-2x">
                <div class="form-group">
                    <label for="class_id">Class <span class="required">*</span></label>
                    <div class="select-wrapper">
                        <select id="class_id" name="class_id" required onchange="filterSubjects()">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo (($homework['class_id'] ?? '') == $class['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="subject_id">Subject <span class="required">*</span></label>
                    <div class="select-wrapper">
                        <select id="subject_id" name="subject_id" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($allSubjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>" 
                                        data-class="<?php echo $subject['class_id']; ?>"
                                        <?php echo (($homework['subject_id'] ?? '') == $subject['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($subject['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5" placeholder="Provide clear instructions or additional information..."><?php echo htmlspecialchars($homework['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="due_date">Due Date <span class="required">*</span></label>
                <input type="datetime-local" id="due_date" name="due_date" required value="<?php echo !empty($homework['due_date']) ? date('Y-m-d\TH:i', strtotime($homework['due_date'])) : ''; ?>">
            </div>

            <?php if (!empty($homework['attachments'])): ?>
                <div class="form-group">
                    <label>Current Attachments</label>
                    <div class="current-attachments">
                        <?php foreach ($homework['attachments'] as $attachment): ?>
                            <div class="attachment-item" id="attachment-row-<?php echo $attachment['id']; ?>">
                                <div class="attachment-meta">
                                    <i class="fas fa-paperclip"></i>
                                    <span class="file-name"><?php echo htmlspecialchars($attachment['file_name']); ?></span>
                                    <span class="file-size">(<?php echo round($attachment['file_size'] / 1024, 2); ?> KB)</span>
                                </div>
                                <button type="button" onclick="deleteAttachment(<?php echo $attachment['id']; ?>)" class="delete-attachment" aria-label="Delete attachment">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Add New Attachments (Optional)</label>
                <div class="file-dropzone">
                    <input type="file" id="new_attachments" name="new_attachments[]" multiple class="file-input" onchange="updateFileLabel()">
                    <div class="dropzone-text">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span id="file-input-label">Tap to browse or upload documents</span>
                        <small class="form-hint">Supported formats: PDF, DOC, DOCX, JPG, PNG (Max 5MB per file)</small>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?php echo BASE_URL; ?>/teacher/homework" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </div>
    </form>
</div>

<style>
:root {
    --primary-color: #7f2677;
    --secondary-color: #f06724;
    --text-main: #000;
    --text-muted: #555;
    --border-color: #cbd5e1;
    --bg-light: #f8fafc;
    --danger-color: #ef4444;
}

.edit-homework-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 16px;
}

@media (min-width: 768px) {
    .edit-homework-container {
        padding: 30px 20px;
    }
}

.page-header {
    margin-bottom: 24px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--text-muted);
    text-decoration: none;
    margin-bottom: 12px;
    font-size: 0.95rem;
    font-weight: 500;
    padding: 6px 0;
}

.back-link:hover {
    color: var(--secondary-color);
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 12px;
}

@media (min-width: 768px) {
    .page-title {
        font-size: 2.25rem;
    }
}

.page-subtitle {
    color: var(--text-muted);
    font-size: 0.95rem;
}

.form-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

@media (min-width: 768px) {
    .form-card {
        padding: 35px;
        border-radius: 24px;
    }
}

.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: var(--text-main);
    font-size: 0.95rem;
}

.required {
    color: var(--danger-color);
}

.form-group input[type="text"], 
.form-group select, 
.form-group textarea,
.form-group input[type="datetime-local"] {
    width: 100%;
    padding: 14px 16px;
    border: 1px solid var(--border-color);
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.2s ease;
    font-family: inherit;
    background-color: #fff;
    color: var(--text-main);
    -webkit-appearance: none;
}

.form-group input:focus, 
.form-group select:focus, 
.form-group textarea:focus {
    outline: none;
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 3px rgba(240, 103, 36, 0.15);
}

.form-grid-2x {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0;
}

@media (min-width: 600px) {
    .form-grid-2x {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
}

.select-wrapper {
    position: relative;
}

.select-wrapper::after {
    content: '\f078';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    pointer-events: none;
    font-size: 0.85rem;
}

.file-dropzone {
    position: relative;
    border: 2px dashed var(--border-color);
    background: var(--bg-light);
    border-radius: 14px;
    padding: 24px;
    text-align: center;
    transition: all 0.2s ease;
    cursor: pointer;
}

.file-dropzone:hover, .file-dropzone:focus-within {
    border-color: var(--secondary-color);
    background: #fffbf9;
}

.file-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 2;
}

.dropzone-text {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: var(--text-main);
}

.dropzone-text i {
    font-size: 2rem;
    color: var(--secondary-color);
    margin-bottom: 4px;
}

.form-hint {
    font-size: 0.8rem;
    color: var(--text-muted);
    line-height: 1.4;
}

.current-attachments {
    background: var(--bg-light);
    border-radius: 14px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.attachment-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px;
    background: white;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}

.attachment-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    flex: 1;
}

.attachment-meta i {
    color: var(--secondary-color);
    flex-shrink: 0;
}

.file-name {
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 0.9rem;
    font-weight: 500;
}

.file-size {
    color: var(--text-muted);
    font-size: 0.8rem;
    flex-shrink: 0;
}

.delete-attachment {
    background: #fef2f2;
    color: var(--danger-color);
    border: none;
    width: 38px;
    height: 38px;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}

.delete-attachment:hover {
    background: var(--danger-color);
    color: white;
}

/* Form Action Buttons Container */
.form-actions {
    display: flex;
    flex-direction: column-reverse;
    gap: 12px;
    margin-top: 32px;
}

@media (min-width: 480px) {
    .form-actions {
        flex-direction: row;
        justify-content: flex-end;
    }
}

.btn-primary, .btn-secondary {
    width: 100%;
    padding: 14px 28px;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    box-sizing: border-box;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

@media (min-width: 480px) {
    .btn-primary, .btn-secondary {
        width: auto;
    }
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
    border: none;
}

.btn-primary:hover {
    background-color: #641e5e;
    transform: translateY(-1px);
}

.btn-secondary {
    background-color: white;
    color: var(--text-muted);
    border: 1px solid var(--border-color);
}

.btn-secondary:hover {
    background-color: var(--bg-light);
    color: var(--text-main);
}

.alert {
    padding: 14px 16px;
    border-radius: 12px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.95rem;
}

.alert-error {
    background-color: #fef2f2;
    border: 1px solid #fca5a5;
    color: #991b1b;
}
</style>

<script>
function filterSubjects() {
    const classId = document.getElementById('class_id').value;
    const subjectSelect = document.getElementById('subject_id');
    const options = subjectSelect.querySelectorAll('option');
    let hasSelectedValidOption = false;
    
    options.forEach(option => {
        const optionClassId = option.getAttribute('data-class');
        
        if (!classId || option.value === '' || optionClassId == classId) {
            option.style.display = 'block';
            option.disabled = false;
            if (option.selected && option.value !== '') {
                hasSelectedValidOption = true;
            }
        } else {
            option.style.display = 'none';
            option.disabled = true;
            if (option.selected) {
                option.selected = false;
            }
        }
    });

    if (!hasSelectedValidOption && classId !== "") {
        subjectSelect.value = "";
    }
}

function updateFileLabel() {
    const input = document.getElementById('new_attachments');
    const label = document.getElementById('file-input-label');
    if (input.files.length > 0) {
        label.textContent = input.files.length === 1 
            ? `Selected: ${input.files[0].name}` 
            : `${input.files.length} files selected to be added`;
    } else {
        label.textContent = 'Tap to browse or upload documents';
    }
}

function deleteAttachment(attachmentId) {
    if (confirm('Are you sure you want to permanently delete this attachment?')) {
        fetch('<?php echo BASE_URL; ?>/teacher/homework/delete-attachment/' + attachmentId, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const element = document.getElementById('attachment-row-' + attachmentId);
                if (element) {
                    element.remove();
                } else {
                    location.reload();
                }
            } else {
                alert(data.error || 'Failed to delete attachment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the asset.');
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    filterSubjects();
});

document.querySelector('.homework-form').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('new_attachments');
    const maxSizeBytes = 5 * 1024 * 1024;

    if (fileInput && fileInput.files.length > 0) {
        for (let i = 0; i < fileInput.files.length; i++) {
            if (fileInput.files[i].size > maxSizeBytes) {
                e.preventDefault();
                alert("Submission failed, your file exceeds 5Mb");
                return false;
            }
        }
    }
});
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>