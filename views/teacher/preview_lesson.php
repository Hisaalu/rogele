<?php
// File: /views/teacher/preview_lesson.php
$pageTitle = 'Preview Lesson | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$lesson = $lesson ?? [];
?>

<div class="preview-container">
    <div class="preview-header">
        <h1 class="page-title">
            <i class="fas fa-eye"></i>
            Lesson Preview
        </h1>
        <div class="header-actions">
            <?php if (!empty($lesson['id'])): ?>
                <a href="<?php echo BASE_URL; ?>/teacher/lessons/edit/<?php echo $lesson['id']; ?>" class="btn-edit-header">
                    <i class="fas fa-edit"></i> Edit Lesson
                </a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/teacher/lessons" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Lessons
            </a>
        </div>
    </div>

    <div class="lesson-preview-card">
        <div class="preview-badge">Preview Mode</div>
        
        <h2 class="lesson-title"><?php echo htmlspecialchars($lesson['title'] ?? ''); ?></h2>
        
        <div class="lesson-meta">
            <span><i class="fas fa-graduation-cap"></i> <?php echo $lesson['class_name'] ?? 'NA'; ?></span>
            <span><i class="fas fa-book"></i> <?php echo $lesson['subject_name'] ?? 'NA'; ?></span>
            <span><i class="fas fa-clock"></i> <?php echo $lesson['duration'] ?? '30'; ?> min</span>
            <span class="status-badge <?php echo ($lesson['is_published'] ?? 0) ? 'published' : 'draft'; ?>">
                <?php echo ($lesson['is_published'] ?? 0) ? 'Published' : 'Draft'; ?>
            </span>

            <?php 
            $createdAt = !empty($lesson['created_at']) ? strtotime($lesson['created_at']) : null;
            $updatedAt = !empty($lesson['updated_at']) ? strtotime($lesson['updated_at']) : null;
            $isRealEdit = ($updatedAt && $createdAt && ($updatedAt - $createdAt > 5)); 
            ?>

            <?php if ($isRealEdit): ?>
                <span title="Last Edited" style="color: #555;">
                    <i class="fas fa-edit" style="color: #f06724;"></i> 
                    <strong style="color: #2563EB;">Last Edited:</strong> <?php echo date('M d, Y', $updatedAt); ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if (!empty($lesson['video_url'])): ?>
        <div class="video-section">
            <h3><i class="fas fa-video" style="color: #f06724;"></i> Video</h3>
            <div class="video-wrapper">
                <iframe src="https://www.youtube.com/embed/<?php echo getYoutubeId($lesson['video_url']); ?>" 
                        frameborder="0" allowfullscreen></iframe>
            </div>
        </div>
        <?php endif; ?>

        <div class="content-section">
            <h3><i class="fas fa-file-alt" style="color: #f06724;"></i> Lesson Content</h3>
            <div class="lesson-content">
                <?php echo nl2br(htmlspecialchars($lesson['content'] ?? '')); ?>
            </div>
        </div>

        <?php if (!empty($lesson['materials'])): ?>
        <div class="materials-section">
            <h3><i class="fas fa-paperclip" style="color: #f06724;"></i> Materials</h3>
            <div class="materials-list">
                <?php foreach ($lesson['materials'] as $material): ?>
                
                <?php
                    $filename = basename($material['file_path']); 
                    $r2_url = "https://docs.raysofgrace.ac.ug/rogele-platform/uploads/lessons/" . $filename;
                ?>

                <a href="<?php echo htmlspecialchars($r2_url); ?>" 
                download="<?php echo htmlspecialchars($material['file_name']); ?>" 
                target="_blank"
                class="material-item">
                    <i class="fas fa-file-pdf"></i>
                    <span><?php echo htmlspecialchars($material['file_name']); ?></span>
                    <i class="fas fa-download"></i>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
function getYoutubeId($url) {
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
    return $matches[1] ?? '';
}
?>

<style>
.preview-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 30px 20px;
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    background-color: #7f2677;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.btn-edit-header {
    background-color: #2563EB;
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-edit-header:hover {
    background-color: #1D4ED8;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.back-link {
    color: #000;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 8px;
    border: 1px solid #E2E8F0;
    transition: all 0.3s ease;
}

.back-link:hover {
    background: #F1F5F9;
    color: #f06724;
}

.lesson-preview-card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    position: relative;
}

.preview-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: #f06724;
    color: white;
    padding: 5px 15px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 1px;
}

.lesson-title {
    color: #000;
    font-size: 2rem;
    margin-bottom: 20px;
    padding-right: 120px;
}

.lesson-meta {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #F1F5F9;
    flex-wrap: wrap;
}

.lesson-meta span {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #555;
}

.lesson-meta i {
    color: #f06724;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-badge.published {
    background: #F0FDF4;
    color: #166534;
}

.status-badge.draft {
    background: #F1F5F9;
    color: #64748B;
}

.video-section,
.content-section,
.materials-section {
    margin-bottom: 40px;
}

.video-section h3,
.content-section h3,
.materials-section h3 {
    color: #000;
    font-size: 1.2rem;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.video-wrapper {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
    border-radius: 12px;
}

.video-wrapper iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.lesson-content {
    background: #F8FAFC;
    padding: 25px;
    border-radius: 12px;
    line-height: 1.8;
    color: #000;
}

.materials-list {
    display: grid;
    gap: 10px;
}

.material-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #F8FAFC;
    border-radius: 10px;
    text-decoration: none;
    color: #000;
    transition: all 0.3s ease;
}

.material-item:hover {
    background: #F1F5F9;
    transform: translateX(5px);
}

.material-item i:first-child {
    color: #F97316;
    font-size: 1.2rem;
}

.material-item span {
    flex: 1;
}

.material-item i:last-child {
    color: #f06724;
}

@media (max-width: 768px) {
    .preview-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .header-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .lesson-preview-card {
        padding: 25px;
    }
    
    .lesson-title {
        font-size: 1.5rem;
        padding-right: 0;
    }
    
    .preview-badge {
        position: static;
        display: inline-block;
        margin-bottom: 15px;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>