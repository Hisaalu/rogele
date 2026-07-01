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
        <div>
            <a href="<?php echo BASE_URL; ?>/teacher/homework" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Homework
            </a>
            <h1 class="page-title">
                <i class="fas fa-edit"></i>
                Edit Homework
            </h1>
            <p class="page-subtitle">Update homework details</p>
        </div>
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

            <div class="form-row">
                <div class="form-group">
                    <label for="class_id">Class <span class="required">*</span></label>
                    <select id="class_id" name="class_id" required onchange="filterSubjects()">
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>" <?php echo (($homework['class_id'] ?? '') == $class['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="subject_id">Subject <span class="required">*</span></label>
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

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" placeholder="Provide instructions or additional information..."><?php echo htmlspecialchars($homework['description'] ?? ''); ?></textarea>
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
                            <div class="attachment-item">
                                <i class="fas fa-paperclip"></i>
                                <span><?php echo htmlspecialchars($attachment['file_name']); ?></span>
                                <span class="file-size">(<?php echo round($attachment['file_size'] / 1024, 2); ?> KB)</span>
                                <button type="button" onclick="deleteAttachment(<?php echo $attachment['id']; ?>)" class="delete-attachment">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="new_attachments">Add New Attachments (Optional)</label>
                <input type="file" id="new_attachments" name="new_attachments[]" multiple class="file-input">
                <small class="form-hint">Supported formats: PDF, DOC, DOCX, JPG, PNG (Max 5MB per file)</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </div>
    </form>
</div>

<style>
.edit-homework-container {
    max-width: 800px;
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
    transition: color 0.3s;
}

.back-link:hover {
    color: #f06724;
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
}

.form-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
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

.required {
    color: #EF4444;
}

.form-group input, 
.form-group select, 
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    font-family: 'Inter', sans-serif;
}

.form-group input:focus, 
.form-group select:focus, 
.form-group textarea:focus {
    outline: none;
    border-color: #f06724;
    box-shadow: 0 0 0 2px rgba(240, 103, 36, 0.25);
}

.file-input {
    padding: 10px;
    border: 2px dashed #E2E8F0;
    background: #F8FAFC;
    cursor: pointer;
}

.form-hint {
    display: block;
    font-size: 0.75rem;
    color: #64748B;
    margin-top: 5px;
}

.current-attachments {
    background: #F8FAFC;
    border-radius: 12px;
    padding: 15px;
}

.attachment-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    background: white;
    border-radius: 8px;
    margin-bottom: 8px;
    border: 1px solid #E2E8F0;
}

.attachment-item:last-child {
    margin-bottom: 0;
}

.attachment-item i {
    color: #f06724;
}

.file-size {
    color: #64748B;
    font-size: 0.75rem;
    margin-left: auto;
}

.delete-attachment {
    background: #FEF2F2;
    color: #EF4444;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
}

.delete-attachment:hover {
    background: #EF4444;
    color: white;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn-primary {
    flex: 1;
    background-color: #7f2677;
    color: white;
    border: none;
    padding: 14px;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(240, 103, 36, 0.3);
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
function filterSubjects() {
    const classId = document.getElementById('class_id').value;
    const subjectSelect = document.getElementById('subject_id');
    const options = subjectSelect.querySelectorAll('option');
    
    if (!classId) {
        for (let i = 0; i < options.length; i++) {
            options[i].style.display = 'block';
        }
        return;
    }
    
    for (let i = 0; i < options.length; i++) {
        const option = options[i];
        const optionClassId = option.getAttribute('data-class');
        
        if (option.value === '') {
            option.style.display = 'block';
        } else if (optionClassId == classId) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    }
}

function deleteAttachment(attachmentId) {
    if (confirm('Are you sure you want to delete this attachment?')) {
        fetch('<?php echo BASE_URL; ?>/teacher/homework/delete-attachment/' + attachmentId, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to delete attachment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    filterSubjects();
});
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>