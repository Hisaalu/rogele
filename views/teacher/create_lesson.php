<?php
// File: /views/teacher/create_lesson.php
$pageTitle = 'Create Lesson | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

// Get subjects and classes from controller
$subjects = $subjects ?? [];
$classes = $classes ?? [];
?>

<div class="create-lesson-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <a href="<?php echo BASE_URL; ?>/teacher/lessons" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Lessons
            </a>
            <h1 class="page-title">
                <i class="fas fa-plus-circle"></i>
                Create New Lesson
            </h1>
            <p class="page-subtitle">Create engaging content for your students</p>
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

    <!-- Create Lesson Form -->
    <div class="form-card">
        <form method="POST" action="<?php echo BASE_URL; ?>/teacher/lessons/create" enctype="multipart/form-data" class="lesson-form" id="lessonForm">
            <!-- Basic Information -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Basic Information
                </h3>
                
                <div class="form-group">
                    <label for="title">
                        <i class="fas fa-heading"></i>
                        Lesson Title <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        required 
                        placeholder="e.g., Introduction to Fractions"
                    >
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="class_id">
                            <i class="fas fa-graduation-cap"></i>
                            Class <span class="required">*</span>
                        </label>
                        <select id="class_id" name="class_id" required onchange="filterSubjects()">
                            <option value="">Select a class</option>
                            <?php if (!empty($classes)): ?>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>">
                                        <?php echo htmlspecialchars($class['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No classes available</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subject_id">
                            <i class="fas fa-book"></i>
                            Subject <span class="required">*</span>
                        </label>
                        <select id="subject_id" name="subject_id" required>
                            <option value="">First select a class</option>
                            <?php if (!empty($subjects)): ?>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?php echo $subject['id']; ?>" 
                                            data-class="<?php echo $subject['class_id']; ?>"
                                            class="subject-option" style="display: none;">
                                        <?php echo htmlspecialchars($subject['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small class="input-hint">Select a class first to see available subjects</small>
                    </div>
                </div>
            </div>

            <!-- Lesson Content -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-align-left"></i>
                    Lesson Content
                </h3>
                
                <div class="form-group">
                    <label for="content">
                        <i class="fas fa-file-alt"></i>
                        Content
                    </label>
                    <textarea 
                        id="content" 
                        name="content" 
                        rows="8" 
                        placeholder="Write your lesson content here..."
                    ></textarea>
                </div>
            </div>

            <!-- Media Section -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-video"></i>
                    Media & Materials
                </h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="video_url">
                            <i class="fab fa-youtube"></i>
                            YouTube Video URL
                        </label>
                        <input 
                            type="url" 
                            id="video_url" 
                            name="video_url" 
                            placeholder="https://www.youtube.com/watch?v=..."
                        >
                        <small class="input-hint">Optional: Add a YouTube video to your lesson</small>
                    </div>

                    <div class="form-group">
                        <label for="duration">
                            <i class="fas fa-clock"></i>
                            Duration (minutes)
                        </label>
                        <input 
                            type="number" 
                            id="duration" 
                            name="duration" 
                            min="1" 
                            max="300" 
                            placeholder="e.g., 45"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="materials">
                        <i class="fas fa-file-upload"></i>
                        Upload Materials
                    </label>
                    <div class="file-upload-area" id="fileUploadArea">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Drag & drop files here or click to browse</p>
                        <input 
                            type="file" 
                            id="materials" 
                            name="materials[]" 
                            multiple 
                            accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png"
                        >
                        <small class="input-hint">Allowed: PDF, DOC, PPT, XLS, Images (Max 10MB each)</small>
                    </div>
                    <div id="fileList" class="file-list"></div>
                </div>
            </div>

            <!-- Publishing Options -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-globe"></i>
                    Publishing Options
                </h3>
                
                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_published" value="1" checked>
                        <span>Publish immediately</span>
                    </label>
                    <small class="input-hint">Uncheck to save as draft</small>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    Create Lesson
                </button>
                <a href="<?php echo BASE_URL; ?>/teacher/lessons" class="btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.create-lesson-container {
    max-width: 900px;
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
    color: #f06724;
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

.form-section {
    margin-bottom: 35px;
    padding-bottom: 35px;
    border-bottom: 2px solid #F1F5F9;
}

.form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.section-title {
    color: black;
    font-size: 1.2rem;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: #f06724;
}

/* Form Groups */
.form-group {
    margin-bottom: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 0.95rem;
    color: black;
    margin-bottom: 8px;
}

.form-group label i {
    color: #f06724;
}

.required {
    color: #EF4444;
    margin-left: 3px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #E2E8F0;
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
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.form-group input:hover,
.form-group select:hover,
.form-group textarea:hover {
    border-color: #f06724;
}

.form-group textarea {
    resize: vertical;
    min-height: 150px;
}

.input-hint {
    display: block;
    font-size: 0.8rem;
    color: black;
    margin-top: 5px;
}

/* File Upload */
.file-upload-area {
    border: 2px dashed #E2E8F0;
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.file-upload-area:hover {
    border-color: #f06724;
    background: #F8FAFC;
}

.file-upload-area i {
    font-size: 3rem;
    color: #f06724;
    margin-bottom: 15px;
}

.file-upload-area p {
    color: black;
    font-weight: 500;
    margin-bottom: 10px;
}

.file-upload-area input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.file-list {
    margin-top: 15px;
}

.file-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #F8FAFC;
    border-radius: 8px;
    margin-bottom: 5px;
}

.file-item i {
    color: #F97316;
}

.file-item .file-name {
    flex: 1;
    color: black;
}

.file-item .file-size {
    color: black;
    font-size: 0.85rem;
}

.file-item .remove-file {
    color: #EF4444;
    cursor: pointer;
}

/* Checkbox */
.checkbox-group {
    margin-top: 10px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #f06724;
}

.checkbox-label span {
    font-weight: 500;
    color: black;
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
    background: #7f2677;
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
    border-color: #f06724;
    color: white;
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
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .page-title {
        font-size: 1.6rem;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .form-card {
        background: black;
    }
    
    .section-title {
        color: #F1F5F9;
    }
    
    .form-group label {
        color: #F1F5F9;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        background: #0F172A;
        border-color: #334155;
        color: #F1F5F9;
    }
    
    .file-upload-area {
        border-color: #334155;
    }
    
    .file-upload-area:hover {
        background: #334155;
    }
    
    .file-item {
        background: #334155;
    }
    
    .file-item .file-name {
        color: #F1F5F9;
    }
    
    .btn-secondary {
        background: transparent;
        color: #94A3B8;
        border-color: #334155;
    }
    
    .btn-secondary:hover {
        background: #334155;
        color: #F1F5F9;
    }
}
</style>

<script>
// File upload handling
document.getElementById('materials').addEventListener('change', function(e) {
    const fileList = document.getElementById('fileList');
    fileList.innerHTML = '';
    
    Array.from(this.files).forEach(file => {
        const fileSize = (file.size / 1024).toFixed(2);
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.innerHTML = `
            <i class="fas fa-file"></i>
            <span class="file-name">${file.name}</span>
            <span class="file-size">${fileSize} KB</span>
            <i class="fas fa-times remove-file" onclick="this.parentElement.remove()"></i>
        `;
        fileList.appendChild(fileItem);
    });
});

// Drag and drop highlight
const uploadArea = document.getElementById('fileUploadArea');
if (uploadArea) {
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.classList.add('highlight');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.classList.remove('highlight');
        });
    });

    uploadArea.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        document.getElementById('materials').files = files;
        
        // Trigger change event
        const event = new Event('change');
        document.getElementById('materials').dispatchEvent(event);
    });
}

// Subject filtering based on selected class
function filterSubjects() {
    const classId = document.getElementById('class_id').value;
    const subjectSelect = document.getElementById('subject_id');
    
    // Clear current options
    subjectSelect.innerHTML = '';
    
    if (!classId) {
        subjectSelect.innerHTML = '<option value="">First select a class</option>';
        return;
    }
    
    // Add default option
    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = 'Select a subject';
    subjectSelect.appendChild(defaultOption);
    
    // Add subjects for selected class
    let hasSubjects = false;
    <?php 
    // Organize subjects by class for JavaScript
    $subjectsByClass = [];
    foreach ($subjects as $subject) {
        $classId = $subject['class_id'];
        if (!isset($subjectsByClass[$classId])) {
            $subjectsByClass[$classId] = [];
        }
        $subjectsByClass[$classId][] = $subject;
    }
    ?>
    
    // Create a JavaScript object with subjects by class
    const subjectsByClass = <?php echo json_encode($subjectsByClass); ?>;
    
    if (subjectsByClass[classId] && subjectsByClass[classId].length > 0) {
        subjectsByClass[classId].forEach(subject => {
            const option = document.createElement('option');
            option.value = subject.id;
            option.textContent = subject.name;
            subjectSelect.appendChild(option);
        });
    } else {
        subjectSelect.innerHTML = '<option value="">No subjects for this class</option>';
    }
}

// Form validation before submit
document.getElementById('lessonForm').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const classId = document.getElementById('class_id').value;
    const subjectId = document.getElementById('subject_id').value;
    
    if (!title) {
        e.preventDefault();
        alert('Please enter a lesson title');
        return false;
    }
    
    if (!classId) {
        e.preventDefault();
        alert('Please select a class');
        return false;
    }
    
    if (!subjectId) {
        e.preventDefault();
        alert('Please select a subject');
        return false;
    }
    
    return true;
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set up class change event
    const classSelect = document.getElementById('class_id');
    if (classSelect) {
        classSelect.addEventListener('change', filterSubjects);
        
        // If a class is pre-selected, filter subjects
        if (classSelect.value) {
            filterSubjects();
        }
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>