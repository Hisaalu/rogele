<?php
// File: /views/external/materials.php
$pageTitle = 'Learning Materials | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$lessons = $lessons ?? [];
$subjects = $subjects ?? [];
$selectedSubject = $_GET['subject'] ?? '';
$search = $_GET['search'] ?? '';
?>

<div class="materials-container">
    <div class="materials-header">
        <div class="header-left">
            <h1 class="page-title">
                <i class="fas fa-book-open"></i>
                Learning Materials
            </h1>
            <p class="page-subtitle">Explore lessons and resources to enhance your knowledge</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo BASE_URL; ?>/external/bookmarks" class="bookmark-link">
                <i class="fas fa-bookmark"></i>
                <span>Bookmarks</span>
                <span class="bookmark-count" id="bookmarkCount">0</span>
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>

    <div class="search-section">
        <form method="GET" class="search-form">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search lessons by title or description..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                >
            </div>
            
            <div class="filter-group">
                <select name="subject" onchange="this.form.submit()">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['id']; ?>" <?php echo $selectedSubject == $subject['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subject['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn-search">
                <i class="fas fa-search"></i> Search
            </button>
            
            <?php if ($search || $selectedSubject): ?>
                <a href="<?php echo BASE_URL; ?>/external/materials" class="btn-clear">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($lessons)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <h3>No Lessons Found</h3>
            <p>We couldn't find any lessons matching your criteria. Try adjusting your search or check back later!</p>
            <a href="<?php echo BASE_URL; ?>/external/bookmarks" class="btn-view-bookmarks">
                <i class="fas fa-bookmark"></i> View Bookmarks
            </a>
        </div>
    <?php else: ?>
        <div class="lessons-grid">
            <?php foreach ($lessons as $lesson): ?>
                <div class="lesson-card" data-lesson-id="<?php echo $lesson['id']; ?>">
                    <div class="lesson-thumbnail">
                        <?php if (!empty($lesson['video_url'])): ?>
                            <img src="https://img.youtube.com/vi/<?php echo getYoutubeId($lesson['video_url']); ?>/0.jpg" alt="Lesson thumbnail">
                            <span class="duration-badge">
                                <i class="fas fa-clock"></i> <?php echo $lesson['duration'] ?? '30'; ?> min
                            </span>
                        <?php else: ?>
                            <div class="thumbnail-placeholder">
                                <i class="fas fa-book-open"></i>
                            </div>
                        <?php endif; ?>
                        
                        <button type="button" class="card-bookmark-btn <?php echo isset($lesson['is_bookmarked']) && $lesson['is_bookmarked'] ? 'bookmarked' : ''; ?>" 
                                onclick="toggleCardBookmark(<?php echo $lesson['id']; ?>, this)"
                                title="<?php echo isset($lesson['is_bookmarked']) && $lesson['is_bookmarked'] ? 'Remove from bookmarks' : 'Add to bookmarks'; ?>">
                            <i class="fas fa-bookmark"></i>
                        </button>
                    </div>

                    <div class="lesson-content">
                        <h3 class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></h3>
                        
                        <div class="lesson-meta">
                            <span>
                                <i class="fas fa-graduation-cap"></i>
                                <?php echo htmlspecialchars($lesson['class_name'] ?? 'All Levels'); ?>
                            </span>
                            <span>
                                <i class="fas fa-book"></i>
                                <?php echo htmlspecialchars($lesson['subject_name'] ?? 'General'); ?>
                            </span>
                            <span>
                                <i class="fas fa-user"></i>
                                Tr. <?php echo htmlspecialchars($lesson['teacher_name'] ?? 'Rays of Grace'); ?>
                            </span>
                        </div>

                        <p class="lesson-description">
                            <?php echo substr(htmlspecialchars($lesson['content'] ?? ''), 0, 150); ?>...
                        </p>

                        <div class="lesson-stats">
                            <span title="Views">
                                <i class="fas fa-eye"></i> <?php echo number_format($lesson['views'] ?? 0); ?>
                            </span>
                            <span title="Materials">
                                <i class="fas fa-paperclip"></i> <?php echo $lesson['materials_count'] ?? 0; ?> file(s)
                            </span>
                        </div>

                        <a href="<?php echo BASE_URL; ?>/external/view-lesson/<?php echo $lesson['id']; ?>" class="btn-view">
                            <span>Start Learning</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
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
.materials-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 20px;
}

/* Header with Bookmark Link */
.materials-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.header-left {
    flex: 1;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    background-color: #7f2677;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
}

.page-subtitle {
    color: #555;
    font-size: 1.1rem;
}

.header-actions {
    display: flex;
    align-items: center;
}

.bookmark-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #FEF3C7, #FFFAF0);
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    color: #7f2677;
    transition: all 0.3s ease;
    border: 1px solid #FDE68A;
}

.bookmark-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(240, 103, 36, 0.2);
    background: #f06724;
    color: white;
}

.bookmark-link i {
    font-size: 1.1rem;
}

.bookmark-count {
    background: #f06724;
    color: white;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 700;
}

.bookmark-link:hover .bookmark-count {
    background: white;
    color: #7f2677;
}

/* Alert Messages */
.alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideDown 0.3s ease;
    position: relative;
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

.alert-close {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    color: currentColor;
    opacity: 0.7;
    margin-left: auto;
    padding: 0 5px;
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
    background: white;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 40px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.search-form {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
}

.search-box {
    flex: 2;
    min-width: 250px;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #555;
}

.search-box input {
    width: 100%;
    padding: 14px 15px 14px 45px;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: #f06724;
    box-shadow: 0 0 0 4px rgba(240, 103, 36, 0.25);
}

.filter-group select {
    padding: 14px 20px;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
    background: white;
    min-width: 180px;
    cursor: pointer;
}

.filter-group select:focus {
    outline: none;
    border-color: #f06724;
}

.btn-search {
    padding: 14px 30px;
    background: #7f2677;
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.btn-search:hover {
    background: #f06724;
}

.btn-clear {
    padding: 14px 30px;
    background: #F1F5F9;
    color: #475569;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.btn-clear:hover {
    background: #E2E8F0;
}

/* Lessons Grid */
.lessons-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
}

.lesson-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    position: relative;
}

.lesson-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 60px rgba(139, 92, 246, 0.2);
}

/* Lesson Thumbnail */
.lesson-thumbnail {
    height: 180px;
    position: relative;
    overflow: hidden;
    background-color: #7f2677;
}

.lesson-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.lesson-card:hover .lesson-thumbnail img {
    transform: scale(1.05);
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
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 5px 10px;
    border-radius: 30px;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Card Bookmark Button */
.card-bookmark-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 35px;
    height: 35px;
    background: white;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #777;
    font-size: 1rem;
    z-index: 10;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.card-bookmark-btn:hover {
    transform: scale(1.1);
    background: #f06724;
    color: white;
}

.card-bookmark-btn.bookmarked {
    background: #f06724;
    color: white;
}

.card-bookmark-btn.bookmarked:hover {
    background: #e05a1a;
}

/* Lesson Content */
.lesson-content {
    padding: 25px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.lesson-title {
    color: #000;
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 15px;
    line-height: 1.4;
}

.lesson-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
    font-size: 0.85rem;
    color: #555;
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
    color: #000;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 20px;
    flex: 1;
}

.lesson-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    padding: 15px 0;
    border-top: 1px solid #E2E8F0;
    border-bottom: 1px solid #E2E8F0;
    font-size: 0.9rem;
    color: #555;
}

.lesson-stats span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.lesson-stats i {
    color: #f06724;
}

.btn-view {
    background-color: #7f2677;
    color: white;
    text-decoration: none;
    padding: 14px 20px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
    margin-top: auto;
}

.btn-view:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);
}

.btn-view i {
    transition: transform 0.3s ease;
}

.btn-view:hover i {
    transform: translateX(5px);
}

.btn-view-bookmarks {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 20px;
    padding: 12px 24px;
    background-color: #7f2677;
    color: white;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-view-bookmarks:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.empty-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 20px;
    background-color: #7f2677;
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
    color: #000;
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.empty-state p {
    color: #555;
    font-size: 1rem;
    max-width: 400px;
    margin: 0 auto;
}

/* Responsive Design */
@media (max-width: 768px) {
    .materials-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .search-form {
        flex-direction: column;
    }
    
    .search-box,
    .filter-group select,
    .btn-search,
    .btn-clear {
        width: 100%;
    }
    
    .lessons-grid {
        grid-template-columns: 1fr;
    }
    
    .lesson-meta {
        flex-direction: column;
        gap: 8px;
    }
    
    .bookmark-link {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .page-title {
        font-size: 2rem;
    }
    
    .lesson-stats {
        flex-wrap: wrap;
    }
}
</style>

<script>
// Function to toggle bookmark from card
function toggleCardBookmark(lessonId, buttonElement) {
    if (typeof event !== 'undefined') {
        event.preventDefault();
        event.stopPropagation();
    }
    
    buttonElement.disabled = true;
    const originalIcon = buttonElement.innerHTML;
    buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    showNotification('Processing...', 'info');
    
    fetch(`<?php echo BASE_URL; ?>/external/toggle-bookmark/${lessonId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (data.bookmarked) {
                buttonElement.classList.add('bookmarked');
                buttonElement.title = 'Remove from bookmarks';
            } else {
                buttonElement.classList.remove('bookmarked');
                buttonElement.title = 'Add to bookmarks';
            }
            buttonElement.innerHTML = '<i class="fas fa-bookmark"></i>';
            
            updateBookmarkCount();
            
            const notificationType = data.bookmarked ? 'success' : 'plain';
            showNotification(data.message, notificationType);
        } else {
            buttonElement.innerHTML = originalIcon;
            showNotification(data.error || 'Failed to update bookmark', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        buttonElement.innerHTML = originalIcon;
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        buttonElement.disabled = false;
    });
}

// Function to update bookmark count in header
function updateBookmarkCount() {
    fetch('<?php echo BASE_URL; ?>/external/get-bookmark-count', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const bookmarkCountSpan = document.getElementById('bookmarkCount');
            if (bookmarkCountSpan) {
                bookmarkCountSpan.textContent = data.count;
            }
        }
    })
    .catch(error => {
        console.error('Error fetching bookmark count:', error);
    });
}

// Show notification function (No icons and no close buttons)
function showNotification(message, type = 'info') {
    const existingNotification = document.querySelector('.notification-toast');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    if (type === 'info' && message === 'Processing...') {
        return;
    }
    
    const notification = document.createElement('div');
    notification.className = `notification-toast notification-${type}`;
    
    // Completely stripped of all icon tags and closing buttons
    notification.innerHTML = `<span>${message}</span>`;
    
    Object.assign(notification.style, {
        position: 'fixed',
        top: '80px',
        right: '20px',
        background: type === 'success' ? '#10B981' : (type === 'error' ? '#EF4444' : '#10B981'),
        color: 'white',
        padding: '12px 24px',
        borderRadius: '8px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        zIndex: '9999',
        display: 'flex',
        alignItems: 'center',
        animation: 'slideIn 0.3s ease'
    });
    
    document.body.appendChild(notification);
    
    // Auto dismiss stays active since there is no click-to-close option
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }
    }, 3000);
}

// Add animation styles (Stripped out close button styles)
if (!document.querySelector('#notification-styles')) {
    const style = document.createElement('style');
    style.id = 'notification-styles';
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.parentElement) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    });
}, 1000);

document.addEventListener('DOMContentLoaded', function() {
    updateBookmarkCount();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>