<?php
// File: /views/teacher/homework/preview.php
$pageTitle = 'Preview Homework | ROGELE';
require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="preview-container">
    <div class="page-header">
        <div>
            <a href="<?php echo BASE_URL; ?>/teacher/homework" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Homework
            </a>
            <h1 class="page-title">
                <i class="fas fa-eye"></i>
                Preview Homework
            </h1>
        </div>
        <div class="header-actions">
            <a href="<?php echo BASE_URL; ?>/teacher/homework/edit/<?php echo $homework['id']; ?>" class="btn-secondary">
                <i class="fas fa-edit"></i> Edit Homework
            </a>
            <a href="<?php echo BASE_URL; ?>/teacher/homework/submissions/<?php echo $homework['id']; ?>" class="btn-primary">
                <i class="fas fa-users"></i> View Submissions
            </a>
        </div>
    </div>

    <div class="preview-card">
        <div class="card-header-info">
            <h2 class="homework-title"><?php echo htmlspecialchars($homework['title']); ?></h2>
            <div class="homework-badges">
                <?php 
                $isExpired = strtotime($homework['due_date']) < time();
                if ($isExpired): 
                ?>
                    <span class="badge expired"><i class="fas fa-calendar-times"></i> Expired</span>
                <?php else: ?>
                    <span class="badge active"><i class="fas fa-clock"></i> Active</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="meta-grid">
            <div class="meta-item">
                <i class="fas fa-graduation-cap"></i>
                <div>
                    <label>Class</label>
                    <span><?php echo htmlspecialchars($homework['class_name'] ?? 'N/A'); ?></span>
                </div>
            </div>
            <div class="meta-item">
                <i class="fas fa-book"></i>
                <div>
                    <label>Subject</label>
                    <span><?php echo htmlspecialchars($homework['subject_name'] ?? 'N/A'); ?></span>
                </div>
            </div>
            <div class="meta-item">
                <i class="fas fa-calendar-alt"></i>
                <div>
                    <label>Due Date</label>
                    <span><?php echo date('F d, Y - h:i A', strtotime($homework['due_date'])); ?></span>
                </div>
            </div>
        </div>

        <hr class="divider">

        <div class="homework-content">
            <h3><i class="fas fa-align-left"></i> Description & Instructions</h3>
            <div class="description-text">
                <?php if (!empty($homework['description'])): ?>
                    <?php echo nl2br(htmlspecialchars($homework['description'])); ?>
                <?php else: ?>
                    <p class="no-content">No detailed instructions provided.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($homework['attachments'])): ?>
            <hr class="divider">
            <div class="homework-attachments">
                <h3><i class="fas fa-paperclip"></i> Attached Materials</h3>
                <div class="attachments-list">
                    <?php foreach ($homework['attachments'] as $file): ?>
                        <div class="attachment-item">
                            <i class="fas fa-file-alt"></i>
                            <span class="file-name"><?php echo htmlspecialchars($file['file_name']); ?></span>
                            <a href="<?php echo BASE_URL . '/' . htmlspecialchars($file['file_path']); ?>" download class="btn-download">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.preview-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 30px 20px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #555;
    text-decoration: none;
    font-size: 0.95rem;
    margin-bottom: 10px;
    transition: color 0.3s;
}

.back-link:hover {
    color: #f06724;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    gap: 15px;
    flex-wrap: wrap;
}

.page-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #7f2677;
    margin: 0;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.btn-primary, .btn-secondary {
    padding: 10px 20px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-primary {
    background-color: #7f2677;
    color: white;
}

.btn-primary:hover {
    background-color: #f06724;
}

.btn-secondary {
    background: #EFF6FF;
    color: #2563EB;
}

.btn-secondary:hover {
    background: #2563EB;
    color: white;
}

.preview-card {
    background: white;
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    border: 1px solid #E2E8F0;
}

.card-header-info {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 25px;
}

.homework-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #000;
    margin: 0;
}

.badge {
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
}

.badge.active {
    background: #F0FDF4;
    color: #166534;
}

.badge.expired {
    background: #FEF2F2;
    color: #B91C1C;
}

.meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    background: #F8FAFC;
    padding: 20px;
    border-radius: 12px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.meta-item i {
    font-size: 1.5rem;
    color: #f06724;
}

.meta-item label {
    display: block;
    font-size: 0.75rem;
    color: #555;
    text-transform: uppercase;
    font-weight: 600;
}

.meta-item span {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1E293B;
}

.divider {
    border: 0;
    height: 1px;
    background: #E2E8F0;
    margin: 30px 0;
}

.homework-content h3, .homework-attachments h3 {
    font-size: 1.1rem;
    color: #000;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.homework-content i {
    color: #f06724;
}

.homework-attachments i {
    color: #f06724;
}

.description-text {
    font-size: 1rem;
    line-height: 1.7;
    color: #555;
}

.no-content {
    color: #555;
    font-style: italic;
}

.attachments-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.attachment-item {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #F8FAFC;
    padding: 12px 18px;
    border-radius: 10px;
    border: 1px solid #E2E8F0;
}

.attachment-item i {
    color: #f06724;
}

.file-name {
    flex: 1;
    font-weight: 500;
    color: #555;
}

.btn-download {
    color: #7f2677;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-download:hover {
    color: #f06724;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .card-header-info {
        flex-direction: column;
    }
}
</style>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>