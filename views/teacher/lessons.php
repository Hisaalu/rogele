<?php
// File: /views/teacher/lessons.php
$pageTitle = 'Lessons | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$lessons = $lessons ?? [];
$totalPages = $totalPages ?? 1;
$currentPage = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
?>

<div class="lessons-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-book-open"></i>
                My Lessons
            </h1>
            <p class="page-subtitle">Manage and organize your teaching materials</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/teacher/lessons/create" class="btn-primary">
            <i class="fas fa-plus-circle"></i>
            Create New Lesson
        </a>
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

    <!-- Search Bar -->
    <div class="search-section">
        <form method="GET" class="search-form">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search lessons by title..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                >
            </div>
            <button type="submit" class="btn-search">
                <i class="fas fa-search"></i> Search
            </button>
            <?php if ($search): ?>
                <a href="<?php echo BASE_URL; ?>/teacher/lessons" class="btn-clear">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Lessons Grid -->
    <?php if (empty($lessons)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <h3>No Lessons Yet</h3>
            <p>You haven't created any lessons. Start by creating your first lesson!</p>
            <a href="<?php echo BASE_URL; ?>/teacher/lessons/create" class="btn-primary">
                <i class="fas fa-plus-circle"></i>
                Create Your First Lesson
            </a>
        </div>
    <?php else: ?>
        <div class="lessons-grid">
            <?php foreach ($lessons as $lesson): ?>
                <div class="lesson-card">
                    <!-- Lesson Thumbnail -->
                    <div class="lesson-thumbnail">
                        <?php if (!empty($lesson['video_url'])): ?>
                            <img src="https://img.youtube.com/vi/<?php echo getYoutubeId($lesson['video_url']); ?>/0.jpg" alt="Thumbnail">
                            <span class="duration-badge">
                                <i class="fas fa-clock"></i> <?php echo $lesson['duration'] ?? '30'; ?> min
                            </span>
                        <?php else: ?>
                            <div class="thumbnail-placeholder">
                                <i class="fas fa-book"></i>
                            </div>
                        <?php endif; ?>
                        <div class="status-badge <?php echo $lesson['is_published'] ? 'published' : 'draft'; ?>">
                            <?php echo $lesson['is_published'] ? 'Published' : 'Draft'; ?>
                        </div>
                    </div>

                    <!-- Lesson Content -->
                    <div class="lesson-content">
                        <h3 class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></h3>
                        
                        <div class="lesson-meta">
                            <span>
                                <i class="fas fa-graduation-cap"></i>
                                <?php echo $lesson['class_name'] ?? 'All Classes'; ?>
                            </span>
                            <span>
                                <i class="fas fa-book"></i>
                                <?php echo $lesson['subject_name'] ?? 'General'; ?>
                            </span>
                        </div>

                        <p class="lesson-description">
                            <?php echo substr(htmlspecialchars($lesson['content'] ?? ''), 0, 120); ?>...
                        </p>

                        <div class="lesson-stats">
                            <span title="Views">
                                <i class="fas fa-eye"></i> <?php echo $lesson['views'] ?? 0; ?>
                            </span>
                            <span title="Materials">
                                <i class="fas fa-paperclip"></i> <?php echo $lesson['materials_count'] ?? 0; ?>
                            </span>
                            <span title="Created">
                                <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($lesson['created_at'])); ?>
                            </span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="lesson-actions">
                            <a href="<?php echo BASE_URL; ?>/teacher/lessons/edit/<?php echo $lesson['id']; ?>" class="action-btn edit" title="Edit Lesson">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?php echo BASE_URL; ?>/teacher/lessons/delete/<?php echo $lesson['id']; ?>" class="action-btn delete" title="Delete Lesson" onclick="return confirm('Are you sure you want to delete this lesson? This action cannot be undone.')">
                                <i class="fas fa-trash"></i>
                            </a>
                            <a href="<?php echo BASE_URL; ?>/teacher/lessons/preview/<?php echo $lesson['id']; ?>" class="action-btn preview" title="Preview Lesson" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?php echo $currentPage - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="page-link">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                       class="page-link <?php echo $i == $currentPage ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?php echo $currentPage + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="page-link">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
// Helper function to extract YouTube video ID
function getYoutubeId($url) {
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
    return $matches[1] ?? '';
}
?>

<style>
.lessons-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
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
}

.btn-primary {
    background: linear-gradient(135deg, #7f2677);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);
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

/* Search Section */
.search-section {
    margin-bottom: 30px;
}

.search-form {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    min-width: 250px;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #94A3B8;
}

.search-box input {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: #f06724;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.btn-search {
    padding: 12px 30px;
    background: #7f2677;
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-search:hover {
    background: #f06724;
}

.btn-clear {
    padding: 12px 30px;
    background: #7f2677;
    color: white;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-clear:hover {
    background: #f06724;
    border-color: #f06724;
}

/* Lessons Grid */
.lessons-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.lesson-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.lesson-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(139, 92, 246, 0.15);
}

/* Lesson Thumbnail */
.lesson-thumbnail {
    height: 180px;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #f06724);
}

.lesson-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.thumbnail-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.thumbnail-placeholder i {
    font-size: 4rem;
    color: white;
    opacity: 0.5;
}

.duration-badge {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
}

.status-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-badge.published {
    background: #10B981;
    color: white;
}

.status-badge.draft {
    background: #f06724;
    color: white;
}

/* Lesson Content */
.lesson-content {
    padding: 20px;
}

.lesson-title {
    color: black;
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.lesson-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 12px;
    font-size: 0.85rem;
    color: black;
}

.lesson-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.lesson-meta i {
    color: #f06724;
}

.lesson-description {
    color: black;
    font-size: 0.9rem;
    line-height: 1.6;
    margin-bottom: 15px;
}

.lesson-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
    padding: 10px 0;
    border-top: 1px solid #E2E8F0;
    border-bottom: 1px solid #E2E8F0;
    font-size: 0.85rem;
    color: #f06724;
}

.lesson-stats span {
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Action Buttons */
.lesson-actions {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 1.1rem;
}

.action-btn.edit {
    background: #EFF6FF;
    color: #2563EB;
}

.action-btn.edit:hover {
    background: #2563EB;
    color: white;
}

.action-btn.delete {
    background: #FEF2F2;
    color: #DC2626;
}

.action-btn.delete:hover {
    background: #DC2626;
    color: white;
}

.action-btn.preview {
    background: #F0FDF4;
    color: #059669;
}

.action-btn.preview:hover {
    background: #059669;
    color: white;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 20px;
}

.empty-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, #f06724);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-icon i {
    font-size: 3rem;
    color: white;
}

.empty-state h3 {
    color: black;
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.empty-state p {
    color: black;
    margin-bottom: 25px;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 30px;
}

.page-link {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    text-decoration: none;
    color: black;
    transition: all 0.3s ease;
    border: 1px solid #E2E8F0;
}

.page-link:hover {
    background: #F1F5F9;
    border-color: #8B5CF6;
}

.page-link.active {
    background: #8B5CF6;
    color: white;
    border-color: #8B5CF6;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .search-form {
        flex-direction: column;
    }
    
    .btn-search, .btn-clear {
        width: 100%;
    }
    
    .lessons-grid {
        grid-template-columns: 1fr;
    }
}

/* Dark Mode */
/* @media (prefers-color-scheme: dark) {
    .lesson-card {
        background: black;
    }
    
    .lesson-title {
        color: #F1F5F9;
    }
    
    .lesson-description {
        color: #94A3B8;
    }
    
    .empty-state {
        background: black;
    }
    
    .empty-state h3 {
        color: #F1F5F9;
    }
    
    .page-link {
        background: black;
        border-color: #334155;
        color: #94A3B8;
    }
    
    .page-link:hover {
        background: #334155;
    }
} */
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>