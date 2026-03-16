<?php
// File: /views/teacher/preview_lesson.php
$pageTitle = 'Preview Lesson - Teacher - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

$lesson = $lesson ?? [];
?>

<div class="preview-container">
    <div class="preview-header">
        <h1 class="page-title">
            <i class="fas fa-eye"></i>
            Lesson Preview
        </h1>
        <a href="/rays-of-grace/teacher/lessons" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Lessons
        </a>
    </div>

    <div class="lesson-preview-card">
        <div class="preview-badge">Preview Mode</div>
        
        <h2 class="lesson-title"><?php echo htmlspecialchars($lesson['title'] ?? ''); ?></h2>
        
        <div class="lesson-meta">
            <span><i class="fas fa-graduation-cap"></i> <?php echo $lesson['class_name'] ?? 'N/A'; ?></span>
            <span><i class="fas fa-book"></i> <?php echo $lesson['subject_name'] ?? 'N/A'; ?></span>
            <span><i class="fas fa-clock"></i> <?php echo $lesson['duration'] ?? '30'; ?> min</span>
            <span class="status-badge <?php echo ($lesson['is_published'] ?? 0) ? 'published' : 'draft'; ?>">
                <?php echo ($lesson['is_published'] ?? 0) ? 'Published' : 'Draft'; ?>
            </span>
        </div>

        <?php if (!empty($lesson['video_url'])): ?>
        <div class="video-section">
            <h3>Video</h3>
            <div class="video-wrapper">
                <iframe src="https://www.youtube.com/embed/<?php echo getYoutubeId($lesson['video_url']); ?>" 
                        frameborder="0" allowfullscreen></iframe>
            </div>
        </div>
        <?php endif; ?>

        <div class="content-section">
            <h3>Lesson Content</h3>
            <div class="lesson-content">
                <?php echo nl2br(htmlspecialchars($lesson['content'] ?? '')); ?>
            </div>
        </div>

        <?php if (!empty($lesson['materials'])): ?>
        <div class="materials-section">
            <h3>Materials</h3>
            <div class="materials-list">
                <?php foreach ($lesson['materials'] as $material): ?>
                <a href="/rays-of-grace/<?php echo $material['file_path']; ?>" target="_blank" class="material-item">
                    <i class="fas fa-file-pdf"></i>
                    <span><?php echo htmlspecialchars($material['file_name']); ?></span>
                    <!-- <i class="fas fa-download"></i> -->
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

.page-title {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.back-link {
    color: #64748B;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.back-link:hover {
    background: #F1F5F9;
    color: #8B5CF6;
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
    background: #F97316;
    color: white;
    padding: 5px 15px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.lesson-title {
    color: #1E293B;
    font-size: 2rem;
    margin-bottom: 20px;
    padding-right: 100px;
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
    color: #64748B;
}

.lesson-meta i {
    color: #8B5CF6;
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
    color: #1E293B;
    font-size: 1.2rem;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.video-section h3 i,
.content-section h3 i,
.materials-section h3 i {
    color: #8B5CF6;
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
    color: #1E293B;
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
    color: #1E293B;
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
    color: #8B5CF6;
}

@media (max-width: 768px) {
    .preview-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
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

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .lesson-preview-card {
        background: #1E293B;
    }
    
    .lesson-title {
        color: #F1F5F9;
    }
    
    .lesson-content {
        background: #334155;
        color: #F1F5F9;
    }
    
    .material-item {
        background: #334155;
        color: #F1F5F9;
    }
    
    .material-item:hover {
        background: #475569;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>