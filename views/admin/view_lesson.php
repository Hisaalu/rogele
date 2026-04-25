<?php
// File: /views/admin/view_lesson.php
$pageTitle = 'Lesson | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$lesson = $lesson ?? [];
?>

<div class="view-lesson-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <a href="<?php echo BASE_URL; ?>/admin/lessons" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Lessons
            </a>
            <h1 class="page-title">
                <i class="fas fa-book-open"></i>
                View Lesson
            </h1>
        </div>
    </div>

    <!-- Lesson Details -->
    <div class="lesson-card">
        <div class="lesson-header">
            <h2><?php echo htmlspecialchars($lesson['title'] ?? ''); ?></h2>
            <div class="lesson-meta">
                <span class="meta-item">
                    <i class="fas fa-user"></i>
                    Teacher: <?php echo htmlspecialchars($lesson['teacher_name'] ?? 'Unknown'); ?>
                </span>
                <span class="meta-item">
                    <i class="fas fa-graduation-cap"></i>
                    Class: <?php echo htmlspecialchars($lesson['class_name'] ?? 'All Levels'); ?>
                </span>
                <span class="meta-item">
                    <i class="fas fa-book"></i>
                    Subject: <?php echo htmlspecialchars($lesson['subject_name'] ?? 'General'); ?>
                </span>
                <span class="meta-item">
                    <i class="fas fa-calendar"></i>
                    Created: <?php echo date('M d, Y H:i', strtotime($lesson['created_at'] ?? 'now')); ?>
                </span>
            </div>
            <div class="lesson-status">
                <span class="status-badge <?php echo $lesson['is_published'] ? 'published' : 'draft'; ?>">
                    <?php echo $lesson['is_published'] ? 'Published' : 'Draft'; ?>
                </span>
                <span class="status-badge <?php echo $lesson['is_approved'] ? 'approved' : 'pending'; ?>">
                    <?php echo $lesson['is_approved'] ? 'Approved' : 'Pending Approval'; ?>
                </span>
            </div>
        </div>

        <div class="lesson-content">
            <h3>Lesson Content</h3>
            <div class="content-body">
                <?php echo nl2br(htmlspecialchars($lesson['content'] ?? 'No content available.')); ?>
            </div>
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

        <?php if (!empty($lesson['materials'])): ?>
        <div class="materials-section">
            <h3>Materials</h3>
            <div class="materials-list">
                <?php foreach ($lesson['materials'] as $material): ?>
                <a href="<?php echo BASE_URL; ?>/public/<?php echo $material['file_path']; ?>" target="_blank" class="material-item">
                    <i class="fas fa-file-pdf"></i>
                    <span><?php echo htmlspecialchars($material['file_name']); ?></span>
                    <i class="fas fa-download"></i>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Admin Actions -->
        <div class="admin-actions">
            <?php if (!$lesson['is_approved']): ?>
                <a href="<?php echo BASE_URL; ?>/admin/lessons/approve/<?php echo $lesson['id']; ?>" class="btn-approve" onclick="return confirm('Approve this lesson?')">
                    <i class="fas fa-check-circle"></i> Approve Lesson
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/lessons/reject/<?php echo $lesson['id']; ?>" class="btn-reject" onclick="return confirm('Reject this lesson?')">
                    <i class="fas fa-times-circle"></i> Reject Lesson
                </a>
            <?php else: ?>
                <span class="already-approved">
                    <i class="fas fa-check-circle"></i> Lesson Already Approved
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Helper function to extract YouTube video ID
function getYoutubeId($url) {
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
    return $matches[1] ?? '';
}
?>

<style>
.view-lesson-container {
    max-width: 1000px;
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
    color: #7f2677;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #7f2677);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 30px;
}

.lesson-card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.lesson-header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #F1F5F9;
}

.lesson-header h2 {
    color: black;
    font-size: 2rem;
    margin-bottom: 15px;
}

.lesson-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 15px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: black;
    font-size: 0.95rem;
}

.meta-item i {
    color: #f06724;
}

.lesson-status {
    display: flex;
    gap: 10px;
}

.status-badge {
    display: inline-block;
    padding: 6px 15px;
    border-radius: 30px;
    font-size: 0.85rem;
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

.status-badge.approved {
    background: #F0FDF4;
    color: #166534;
}

.status-badge.pending {
    background: #FEF3C7;
    color: #92400E;
}

.lesson-content {
    margin-bottom: 30px;
}

.lesson-content h3,
.video-section h3,
.materials-section h3 {
    color: #1E293B;
    font-size: 1.2rem;
    margin-bottom: 15px;
}

.content-body {
    background: #F8FAFC;
    padding: 25px;
    border-radius: 12px;
    line-height: 1.8;
    color: #1E293B;
}

.video-wrapper {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
    border-radius: 12px;
    margin-bottom: 30px;
}

.video-wrapper iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
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

.admin-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 2px solid #F1F5F9;
}

.btn-approve,
.btn-reject {
    flex: 1;
    padding: 14px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1rem;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-approve {
    background: #10B981;
    color: white;
}

.btn-approve:hover {
    background: #059669;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
}

.btn-reject {
    background: #EF4444;
    color: white;
}

.btn-reject:hover {
    background: #DC2626;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
}

.already-approved {
    flex: 1;
    padding: 14px;
    background: #F0FDF4;
    color: #166534;
    border-radius: 10px;
    text-align: center;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

/* Responsive */
@media (max-width: 768px) {
    .lesson-card {
        padding: 25px;
    }
    
    .lesson-header h2 {
        font-size: 1.5rem;
    }
    
    .lesson-meta {
        flex-direction: column;
        gap: 10px;
    }
    
    .admin-actions {
        flex-direction: column;
    }
    
    .video-wrapper {
        margin-bottom: 20px;
    }
}

</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>