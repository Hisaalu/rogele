<?php
// File: /views/teacher/edit_lesson.php
$pageTitle = 'Edit Lesson - Teacher - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

// Get lesson data from controller
$lesson = $lesson ?? [];
$subjects = $subjects ?? [];
$classes = $classes ?? [];
?>

<div class="edit-lesson-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <a href="/rays-of-grace/teacher/lessons" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Lessons
            </a>
            <h1 class="page-title">
                <i class="fas fa-edit"></i>
                Edit Lesson
            </h1>
            <p class="page-subtitle">Editing: <?php echo htmlspecialchars($lesson['title'] ?? ''); ?></p>
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

    <!-- Edit Lesson Form -->
    <div class="form-card">
        <form method="POST" action="/rays-of-grace/teacher/lessons/edit/<?php echo $lesson['id']; ?>" enctype="multipart/form-data" class="lesson-form" id="lessonForm">
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
                        value="<?php echo htmlspecialchars($lesson['title'] ?? ''); ?>"
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
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" 
                                    <?php echo ($class['id'] == ($lesson['class_id'] ?? '')) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subject_id">
                            <i class="fas fa-book"></i>
                            Subject <span class="required">*</span>
                        </label>
                        <select id="subject_id" name="subject_id" required>
                            <option value="">Select a subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>" 
                                    data-class="<?php echo $subject['class_id']; ?>"
                                    class="subject-option"
                                    <?php echo ($subject['id'] == ($lesson['subject_id'] ?? '')) ? 'selected' : ''; ?>
                                    style="<?php echo ($subject['class_id'] != ($lesson['class_id'] ?? '')) ? 'display: none;' : ''; ?>">
                                    <?php echo htmlspecialchars($subject['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="input-hint">Select a class to filter subjects</small>
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
                    ><?php echo htmlspecialchars($lesson['content'] ?? ''); ?></textarea>
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
                            value="<?php echo htmlspecialchars($lesson['video_url'] ?? ''); ?>"
                            placeholder="https://www.youtube.com/watch?v=..."
                        >
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
                            value="<?php echo htmlspecialchars($lesson['duration'] ?? ''); ?>"
                            min="1" 
                            max="300" 
                            placeholder="e.g., 45"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="materials">
                        <i class="fas fa-file-upload"></i>
                        Upload New Materials
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
                    </div>
                    <div id="fileList" class="file-list"></div>
                    
                    <?php if (!empty($lesson['materials'])): ?>
                        <div class="existing-materials">
                            <h4>Existing Materials:</h4>
                            <?php foreach ($lesson['materials'] as $material): ?>
                                <div class="file-item">
                                    <i class="fas fa-file"></i>
                                    <span class="file-name"><?php echo htmlspecialchars($material['file_name']); ?></span>
                                    <span class="file-size"><?php echo round($material['file_size'] / 1024, 2); ?> KB</span>
                                    <a href="/rays-of-grace/teacher/lessons/delete-material/<?php echo $material['id']; ?>" 
                                       class="remove-file" 
                                       onclick="return confirm('Delete this material?')">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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
                        <input type="checkbox" name="is_published" value="1" 
                            <?php echo ($lesson['is_published'] ?? 0) ? 'checked' : ''; ?>>
                        <span>Publish immediately</span>
                    </label>
                    <small class="input-hint">Uncheck to save as draft</small>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    Update Lesson
                </button>
                <a href="/rays-of-grace/teacher/lessons" class="btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.edit-lesson-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 30px 20px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #64748B;
    text-decoration: none;
    font-size: 0.95rem;
    margin-bottom: 15px;
    transition: color 0.3s ease;
}

.back-link:hover {
    color: #8B5CF6;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-subtitle {
    color: #64748B;
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

.section-title {
    color: #1E293B;
    font-size: 1.2rem;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: #8B5CF6;
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
    color: #1E293B;
    margin-bottom: 8px;
}

.form-group label i {
    color: #8B5CF6;
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
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #8B5CF6;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.file-upload-area {
    border: 2px dashed #E2E8F0;
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    position: relative;
    transition: all 0.3s ease;
}

.file-upload-area:hover {
    border-color: #8B5CF6;
    background: #F8FAFC;
}

.file-upload-area i {
    font-size: 3rem;
    color: #8B5CF6;
    margin-bottom: 15px;
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

.file-name {
    flex: 1;
    color: #1E293B;
}

.file-size {
    color: #64748B;
    font-size: 0.85rem;
}

.remove-file {
    color: #EF4444;
    cursor: pointer;
    text-decoration: none;
}

.remove-file:hover {
    color: #B91C1C;
}

.existing-materials {
    margin-top: 20px;
    padding: 20px;
    background: #F8FAFC;
    border-radius: 12px;
}

.existing-materials h4 {
    color: #1E293B;
    margin-bottom: 10px;
    font-size: 1rem;
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
    accent-color: #8B5CF6;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn-primary {
    flex: 1;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
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
    background: white;
    color: #64748B;
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
    background: #F1F5F9;
    border-color: #94A3B8;
    color: #1E293B;
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
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .form-card {
        background: #1E293B;
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
    
    .existing-materials {
        background: #334155;
    }
    
    .existing-materials h4 {
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
function filterSubjects() {
    const classId = document.getElementById('class_id').value;
    const subjectSelect = document.getElementById('subject_id');
    const options = subjectSelect.querySelectorAll('.subject-option');
    
    // Show/hide options based on selected class
    options.forEach(opt => {
        if (opt.getAttribute('data-class') == classId) {
            opt.style.display = 'block';
        } else {
            opt.style.display = 'none';
        }
    });
}

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
        `;
        fileList.appendChild(fileItem);
    });
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('class_id');
    if (classSelect) {
        classSelect.addEventListener('change', filterSubjects);
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>