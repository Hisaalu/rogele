<?php
// file: views/teacher/homework/create.php
$pageTitle = 'Create Homework | ROGELE';
require_once __DIR__ . '/../../layouts/header.php';

$classes = $classes ?? [];
$subjectsByClass = $subjectsByClass ?? [];
$allSubjects = $allSubjects ?? [];
?>

<div class="create-homework-container">
    <div class="page-header">
        <div>
            <a href="<?php echo BASE_URL; ?>/teacher/homework" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Homework
            </a>
            <h1 class="page-title">
                <i class="fas fa-plus-circle"></i>
                Create New Homework
            </h1>
            <p class="page-subtitle">Assign homework to your students</p>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['info'])): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <span><?php echo $_SESSION['info']; unset($_SESSION['info']); ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="homework-form">
        <div class="form-card">
            <div class="form-group">
                <label for="title">Homework Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" required placeholder="e.g., Mathematics Assignment 1">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="class_id">Class <span class="required">*</span></label>
                    <select id="class_id" name="class_id" required onchange="filterSubjects()">
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="subject_id">Subject <span class="required">*</span></label>
                    <select id="subject_id" name="subject_id" required>
                        <option value="">Select Subject</option>
                        <?php foreach ($allSubjects as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>" data-class="<?php echo $subject['class_id']; ?>">
                                <?php echo htmlspecialchars($subject['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" placeholder="Provide instructions or additional information..."></textarea>
            </div>

            <div class="form-group">
                <label for="due_date">Due Date <span class="required">*</span></label>
                <input type="datetime-local" id="due_date" name="due_date" required>
            </div>

            <div class="form-group">
                <label for="attachments">Attachments</label>
                <input type="file" id="attachments" name="attachments[]" multiple class="file-input">
                <small class="form-hint">Upload should be 5MB per file</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Create Homework</button>
            </div>
        </div>
    </form>
</div>

<style>
.create-homework-container {
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
    border: 1px dashed #E2E8F0;
    background: #F8FAFC;
    cursor: pointer;
}

.form-hint {
    display: block;
    font-size: 0.75rem;
    color: #555;
    margin-top: 5px;
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
    
    subjectSelect.value = '';
    
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
                alert("Failed to Create Homework, your upload exceeds 5Mb");
                return false;
            }
        }
    }
});
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>