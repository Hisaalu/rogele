<?php
// File: /views/admin/view_lesson.php
$pageTitle = 'Lesson | ROGELE';
require_once __DIR__ . '/../layouts/admin_header.php';

$lesson = $lesson ?? [];

if (!function_exists('getYoutubeId')) {
    function getYoutubeId($url) {
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
        return $matches[1] ?? '';
    }
}
?>

<style>
:root {
    --primary-purple: #7f2677;
    --accent-orange: #f06724;
    --text-dark: #000;
    --text-muted: #555;
    --bg-surface: #FFFFFF;
    --border-color: #E2E8F0;
    --radius-lg: 20px;
    --radius-md: 12px;
    --shadow-sm: 0 4px 12px rgba(0, 0, 0, 0.03);
    --shadow-md: 0 10px 30px rgba(0, 0, 0, 0.05);
    --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.view-lesson-container,
.view-lesson-container * {
    box-sizing: border-box;
}

.view-lesson-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: clamp(16px, 3vw, 32px);
    color: var(--text-dark);
}

.page-header {
    margin-bottom: clamp(20px, 3vw, 30px);
}

.page-title {
    font-size: clamp(1.5rem, 3.5vw, 2.2rem);
    font-weight: 700;
    color: var(--primary-purple);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.lesson-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    padding: clamp(20px, 4vw, 40px);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
}

.lesson-header {
    margin-bottom: clamp(20px, 3vw, 30px);
    padding-bottom: clamp(16px, 2.5vw, 24px);
    border-bottom: 2px solid #F1F5F9;
}

.lesson-header h2 {
    color: var(--text-dark);
    font-size: clamp(1.3rem, 3vw, 1.8rem);
    font-weight: 700;
    margin: 0 0 16px 0;
    line-height: 1.3;
}

.lesson-meta {
    display: flex;
    flex-wrap: wrap;
    gap: clamp(12px, 2vw, 20px);
    margin-bottom: 16px;
}

.meta-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--text-dark);
    font-size: 0.9rem;
}

.meta-item i {
    color: var(--accent-orange);
}

.meta-item strong {
    color: var(--text-muted);
    font-weight: 600;
}

.lesson-status {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
}

.status-badge.published { background: #F0FDF4; color: #166534; }
.status-badge.draft { background: #F1F5F9; color: var(--text-muted); }
.status-badge.approved { background: #F0FDF4; color: #166534; }
.status-badge.pending { background: #FEF3C7; color: #92400E; }

.lesson-section {
    margin-bottom: clamp(20px, 3vw, 30px);
}

.section-title {
    color: var(--text-dark);
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0 0 14px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-title i {
    color: var(--accent-orange);
}

.content-body {
    background: #F8FAFC;
    padding: clamp(16px, 2.5vw, 24px);
    border-radius: var(--radius-md);
    line-height: 1.75;
    color: var(--text-dark);
    border: 1px solid #F1F5F9;
    font-size: 0.95rem;
    word-break: break-word;
}

.video-wrapper {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
    border-radius: var(--radius-md);
    background: #000;
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
    gap: 14px;
    padding: 14px 18px;
    background: #F8FAFC;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--text-dark);
    transition: var(--transition);
    font-weight: 500;
    font-size: 0.9rem;
}

.material-item:hover {
    background: #F1F5F9;
    border-color: var(--accent-orange);
    transform: translateX(4px);
}

.material-item i:first-child {
    color: var(--accent-orange);
    font-size: 1.1rem;
}

.material-item span {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.material-item .download-icon {
    color: var(--text-muted);
    font-size: 0.9rem;
}

.admin-actions {
    display: flex;
    gap: 14px;
    margin-top: clamp(24px, 3vw, 32px);
    padding-top: clamp(20px, 3vw, 28px);
    border-top: 2px solid #F1F5F9;
    flex-wrap: wrap;
}

.btn-action {
    flex: 1 1 200px;
    padding: 12px 20px;
    border-radius: var(--radius-md);
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: var(--transition);
    border: none;
    cursor: pointer;
    white-space: nowrap;
}

.btn-approve {
    background: #10B981;
    color: white;
}

.btn-approve:hover {
    background: #059669;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25);
}

.btn-reject {
    background: #EF4444;
    color: white;
}

.btn-reject:hover {
    background: #DC2626;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(239, 68, 68, 0.25);
}

.already-approved {
    flex: 1;
    padding: 12px 20px;
    background: #F0FDF4;
    color: #166534;
    border-radius: var(--radius-md);
    border: 1px solid #DCFCE7;
    text-align: center;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

@media (max-width: 600px) {
    .lesson-meta {
        flex-direction: column;
        gap: 8px;
    }

    .admin-actions {
        flex-direction: column;
    }

    .btn-action {
        width: 100%;
    }
}
</style>

<div class="view-lesson-container">
    <header class="page-header">
        <div class="header-text">
            <h1 class="page-title">
                <i class="fas fa-book-open" aria-hidden="true"></i>
                <span>View Lesson</span>
            </h1>
        </div>
    </header>

    <main class="lesson-card">
        <div class="lesson-header">
            <h2><?php echo htmlspecialchars($lesson['title'] ?? 'Untitled Lesson'); ?></h2>
            
            <div class="lesson-meta">
                <span class="meta-item">
                    <i class="fas fa-user" aria-hidden="true"></i>
                    <strong>Teacher:</strong> <?php echo htmlspecialchars($lesson['teacher_name'] ?? 'Unknown'); ?>
                </span>
                <span class="meta-item">
                    <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                    <strong>Class:</strong> <?php echo htmlspecialchars($lesson['class_name'] ?? 'All Levels'); ?>
                </span>
                <span class="meta-item">
                    <i class="fas fa-book" aria-hidden="true"></i>
                    <strong>Subject:</strong> <?php echo htmlspecialchars($lesson['subject_name'] ?? 'General'); ?>
                </span>
                <span class="meta-item">
                    <i class="fas fa-calendar" aria-hidden="true"></i>
                    <strong>Created:</strong> <?php echo !empty($lesson['created_at']) ? date('M d, Y h:i A', strtotime($lesson['created_at'])) : 'N/A'; ?>
                </span>
            </div>

            <div class="lesson-status">
                <span class="status-badge <?php echo !empty($lesson['is_published']) ? 'published' : 'draft'; ?>">
                    <i class="fas <?php echo !empty($lesson['is_published']) ? 'fa-globe' : 'fa-pencil-alt'; ?>" aria-hidden="true"></i>
                    <?php echo !empty($lesson['is_published']) ? 'Published' : 'Draft'; ?>
                </span>
                <span class="status-badge <?php echo !empty($lesson['is_approved']) ? 'approved' : 'pending'; ?>">
                    <i class="fas <?php echo !empty($lesson['is_approved']) ? 'fa-check-circle' : 'fa-clock'; ?>" aria-hidden="true"></i>
                    <?php echo !empty($lesson['is_approved']) ? 'Approved' : 'Pending Approval'; ?>
                </span>
            </div>
        </div>

        <section class="lesson-section">
            <h3 class="section-title"><i class="fas fa-align-left" aria-hidden="true"></i> Lesson Content</h3>
            <div class="content-body">
                <?php echo nl2br(htmlspecialchars($lesson['content'] ?? 'No content available.')); ?>
            </div>
        </section>

        <?php if (!empty($lesson['video_url'])): ?>
            <?php $youtubeId = getYoutubeId($lesson['video_url']); ?>
            <?php if (!empty($youtubeId)): ?>
                <section class="lesson-section">
                    <h3 class="section-title"><i class="fas fa-video" aria-hidden="true"></i> Video Lesson</h3>
                    <div class="video-wrapper">
                        <iframe 
                            src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtubeId); ?>" 
                            title="Lesson Video"
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($lesson['materials']) && is_array($lesson['materials'])): ?>
            <section class="lesson-section">
                <h3 class="section-title"><i class="fas fa-paperclip" aria-hidden="true"></i> Learning Materials</h3>
                <div class="materials-list">
                    <?php foreach ($lesson['materials'] as $material): ?>
                        <a href="<?php echo BASE_URL; ?>/public/<?php echo htmlspecialchars(ltrim($material['file_path'] ?? '', '/')); ?>" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="material-item">
                            <i class="fas fa-file-alt" aria-hidden="true"></i>
                            <span><?php echo htmlspecialchars($material['file_name'] ?? 'Download Material'); ?></span>
                            <i class="fas fa-download download-icon" aria-hidden="true"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <div class="admin-actions">
            <?php if (empty($lesson['is_approved'])): ?>
                <a href="<?php echo BASE_URL; ?>/admin/lessons/approve/<?php echo urlencode($lesson['id'] ?? ''); ?>" 
                   class="btn-action btn-approve" 
                   onclick="return confirm('Approve this lesson?')">
                    <i class="fas fa-check-circle" aria-hidden="true"></i> Approve Lesson
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/lessons/reject/<?php echo urlencode($lesson['id'] ?? ''); ?>" 
                   class="btn-action btn-reject" 
                   onclick="return confirm('Reject this lesson?')">
                    <i class="fas fa-times-circle" aria-hidden="true"></i> Reject Lesson
                </a>
            <?php else: ?>
                <div class="already-approved">
                    <i class="fas fa-check-circle" aria-hidden="true"></i> Lesson Already Approved
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>