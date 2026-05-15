<?php
// File: /views/external/bookmarks.php
$pageTitle = 'My Bookmarks | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$bookmarks = $bookmarks ?? [];
?>

<div class="bookmarks-container">
    <!-- Header Section -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-bookmark"></i>
                My Bookmarks
            </h1>
            <p class="page-subtitle">Your saved lessons for quick access</p>
        </div>
        <div class="header-stats">
            <div class="stat-badge">
                <i class="fas fa-book-open"></i>
                <span><?php echo count($bookmarks); ?> Bookmarked Lessons</span>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <?php if (empty($bookmarks)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-bookmark"></i>
            </div>
            <h3>No Bookmarks Yet</h3>
            <p>Save your favorite lessons by clicking the bookmark icon on any lesson.</p>
            <a href="<?php echo BASE_URL; ?>/external/materials" class="btn-explore">
                <i class="fas fa-search"></i> Explore Lessons
            </a>
        </div>
    <?php else: ?>
        <!-- Bookmarks Grid -->
        <div class="bookmarks-grid">
            <?php foreach ($bookmarks as $bookmark): ?>
                <div class="bookmark-card" data-lesson-id="<?php echo $bookmark['id']; ?>">
                    <div class="bookmark-header">
                        <div class="bookmark-icon">
                            <i class="fas fa-bookmark"></i>
                        </div>
                        <div class="lesson-type">
                            <i class="fas fa-file-alt"></i>
                            Lesson
                        </div>
                        <button class="btn-remove-bookmark" onclick="removeBookmark(<?php echo $bookmark['id']; ?>, this)" title="Remove Bookmark">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="bookmark-content">
                        <h3 class="lesson-title">
                            <?php echo htmlspecialchars($bookmark['title']); ?>
                        </h3>
                        
                        <p class="lesson-description">
                            <?php 
                            $description = strip_tags($bookmark['content'] ?? '');
                            echo htmlspecialchars(substr($description, 0, 120)) . (strlen($description) > 120 ? '...' : ''); 
                            ?>
                        </p>
                        
                        <div class="lesson-meta">
                            <?php if (!empty($bookmark['subject_name'])): ?>
                                <span class="meta-tag subject">
                                    <i class="fas fa-book"></i>
                                    <?php echo htmlspecialchars($bookmark['subject_name']); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($bookmark['class_name'])): ?>
                                <span class="meta-tag class">
                                    <i class="fas fa-graduation-cap"></i>
                                    <?php echo htmlspecialchars($bookmark['class_name']); ?>
                                </span>
                            <?php endif; ?>
                            
                            <span class="meta-tag date">
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo date('M d, Y', strtotime($bookmark['bookmarked_at'])); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="bookmark-footer">
                        <a href="<?php echo BASE_URL; ?>/external/view-lesson/<?php echo $bookmark['id']; ?>" class="btn-view-lesson">
                            <i class="fas fa-eye"></i> View Lesson
                        </a>
                        <button class="btn-share" onclick="shareLesson('<?php echo htmlspecialchars($bookmark['title']); ?>', <?php echo $bookmark['id']; ?>)">
                            <i class="fas fa-share-alt"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Optional: Recently Added Section -->
        <div class="recent-section">
            <h3 class="section-title">
                <i class="fas fa-clock"></i>
                Recently Added
            </h3>
            <div class="recent-list">
                <?php 
                $recentBookmarks = array_slice($bookmarks, 0, 5);
                foreach ($recentBookmarks as $bookmark): 
                ?>
                    <div class="recent-item">
                        <div class="recent-icon">
                            <i class="fas fa-bookmark"></i>
                        </div>
                        <div class="recent-info">
                            <a href="<?php echo BASE_URL; ?>/external/view-lesson/<?php echo $bookmark['id']; ?>">
                                <?php echo htmlspecialchars($bookmark['title']); ?>
                            </a>
                            <span class="recent-date">Added <?php echo date('M d, Y', strtotime($bookmark['bookmarked_at'])); ?></span>
                        </div>
                        <button class="recent-remove" onclick="removeBookmark(<?php echo $bookmark['id']; ?>, this)">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Remove Bookmark Confirmation Modal -->
<div id="removeModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-question-circle"></i> Remove Bookmark</h3>
            <span class="modal-close">&times;</span>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to remove this lesson from your bookmarks?</p>
            <p class="warning-text">You can always bookmark it again later.</p>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel">Cancel</button>
            <button id="confirmRemoveBtn" class="btn-confirm-remove">Remove Bookmark</button>
        </div>
    </div>
</div>

<style>
/* Main Container */
.bookmarks-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 20px;
    min-height: calc(100vh - 200px);
}

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    background-color: #7f2677;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-title i {
    background-color: #7f2677;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.page-subtitle {
    color: #000;
    font-size: 1rem;
}

.header-stats .stat-badge {
    background: linear-gradient(135deg, #F8FAFC, #FFFFFF);
    padding: 10px 20px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    color: #7f2677;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border: 1px solid #E2E8F0;
}

.header-stats .stat-badge i {
    color: #f06724;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 24px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.05);
}

.empty-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, #FEF3C7, #FFFAF0);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-icon i {
    font-size: 3rem;
    color: #f06724;
}

.empty-state h3 {
    color: #1E293B;
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.empty-state p {
    color: #000;
    margin-bottom: 25px;
    font-size: 1rem;
}

.btn-explore {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    background-color: #7f2677;
    color: white;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-explore:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(240, 103, 36, 0.3);
}

/* Bookmarks Grid */
.bookmarks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 25px;
    margin-bottom: 50px;
}

.bookmark-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    position: relative;
    border: 1px solid #E2E8F0;
}

.bookmark-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 35px rgba(127, 38, 119, 0.15);
    border-color: #f06724;
}

.bookmark-header {
    background-color: #7f2677;
    padding: 15px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
}

.bookmark-icon {
    width: 35px;
    height: 35px;
    background-color: #f06724;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bookmark-icon i {
    color: white;
    font-size: 1rem;
}

.lesson-type {
    background: rgba(255,255,255,0.15);
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-remove-bookmark {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,255,255,0.2);
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: white;
}

.btn-remove-bookmark:hover {
    background: #EF4444;
    transform: translateY(-50%) scale(1.1);
}

.bookmark-content {
    padding: 20px;
}

.lesson-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1E293B;
    margin-bottom: 12px;
    line-height: 1.4;
}

.lesson-description {
    color: #000;
    font-size: 0.9rem;
    line-height: 1.6;
    margin-bottom: 15px;
}

.lesson-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.meta-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    background: #F8FAFC;
    border-radius: 20px;
    font-size: 0.75rem;
    color: #000;
}

.meta-tag i {
    color: #f06724;
    font-size: 0.7rem;
}

.bookmark-footer {
    display: flex;
    gap: 10px;
    padding: 15px 20px 20px;
    border-top: 1px solid #F1F5F9;
}

.btn-view-lesson {
    flex: 1;
    background-color: #7f2677;
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 40px;
    font-weight: 600;
    font-size: 0.85rem;
    text-align: center;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-view-lesson:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(240, 103, 36, 0.3);
}

.btn-share {
    background: #F1F5F9;
    border: none;
    padding: 10px 20px;
    border-radius: 40px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #000;
    font-weight: 600;
}

.btn-share:hover {
    background: #E2E8F0;
    transform: translateY(-2px);
}

/* Recent Section */
.recent-section {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    border: 1px solid #E2E8F0;
}

.section-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #1E293B;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-title i {
    color: #f06724;
}

.recent-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.recent-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px;
    background: #F8FAFC;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.recent-item:hover {
    background: #F1F5F9;
    transform: translateX(5px);
}

.recent-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #FEF3C7, #FFFAF0);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.recent-icon i {
    color: #f06724;
    font-size: 1rem;
}

.recent-info {
    flex: 1;
}

.recent-info a {
    text-decoration: none;
    font-weight: 600;
    color: #1E293B;
    font-size: 0.95rem;
    display: block;
    margin-bottom: 4px;
}

.recent-info a:hover {
    color: #f06724;
}

.recent-date {
    font-size: 0.7rem;
    color: #000;
}

.recent-remove {
    background: none;
    border: none;
    cursor: pointer;
    color: #000;
    transition: all 0.3s ease;
    padding: 8px;
}

.recent-remove:hover {
    color: #EF4444;
    transform: scale(1.1);
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
    z-index: 1000;
    display: none;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 20px;
    max-width: 450px;
    width: 90%;
    overflow: hidden;
    animation: modalSlideUp 0.3s ease;
}

.modal-header {
    padding: 20px 24px;
    background: #FEF2F2;
    border-bottom: 2px solid #FECACA;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #B91C1C;
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal-close {
    font-size: 1.5rem;
    cursor: pointer;
    color: #000;
    transition: color 0.2s;
}

.modal-close:hover {
    color: #1E293B;
}

.modal-body {
    padding: 24px;
}

.modal-body p {
    color: #1E293B;
    margin-bottom: 16px;
}

.warning-text {
    background: #FEF3C7;
    padding: 12px;
    border-radius: 8px;
    color: #92400E;
    font-size: 0.85rem;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 20px 24px;
    border-top: 1px solid #E2E8F0;
}

.btn-cancel {
    padding: 10px 20px;
    background: #F1F5F9;
    color: #000;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel:hover {
    background: #E2E8F0;
}

.btn-confirm-remove {
    padding: 10px 20px;
    background: #EF4444;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-confirm-remove:hover {
    background: #DC2626;
    transform: translateY(-1px);
}

/* Animations */
@keyframes modalSlideUp {
    from {
        transform: translateY(30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .bookmarks-container {
        padding: 20px 15px;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .bookmarks-grid {
        grid-template-columns: 1fr;
    }
    
    .bookmark-footer {
        flex-direction: column;
    }
    
    .btn-share {
        justify-content: center;
    }
    
    .recent-list {
        gap: 10px;
    }
    
    .recent-item {
        padding: 10px;
    }
    
    .recent-info a {
        font-size: 0.85rem;
    }
}

</style>

<script>
let lessonToRemove = null;
let removeButtonElement = null;

/**
 * Remove bookmark function
 */
function removeBookmark(lessonId, buttonElement) {
    lessonToRemove = lessonId;
    removeButtonElement = buttonElement;
    const modal = document.getElementById('removeModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Share lesson function
 */
function shareLesson(lessonTitle, lessonId) {
    const url = '<?php echo BASE_URL; ?>/external/view-lesson/' + lessonId;
    
    if (navigator.share) {
        navigator.share({
            title: lessonTitle,
            text: 'Check out this lesson on ROGELE!',
            url: url
        }).catch(() => {
            copyToClipboard(url, lessonTitle);
        });
    } else {
        copyToClipboard(url, lessonTitle);
    }
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text, lessonTitle) {
    navigator.clipboard.writeText(text).then(() => {
        showToast(`Link to "${lessonTitle}" copied to clipboard!`, 'success');
    }).catch(() => {
        showToast('Failed to copy link', 'error');
    });
}

/**
 * Show toast notification
 */
function showToast(message, type = 'success') {
    let toast = document.getElementById('customToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'customToast';
        document.body.appendChild(toast);
        
        const style = document.createElement('style');
        style.textContent = `
            #customToast {
                position: fixed;
                bottom: 20px;
                right: 20px;
                padding: 12px 24px;
                border-radius: 8px;
                background: #10B981;
                color: white;
                font-weight: 500;
                z-index: 9999;
                opacity: 0;
                transition: opacity 0.3s ease;
                pointer-events: none;
            }
            #customToast.error { background: #EF4444; }
            #customToast.show { opacity: 1; }
        `;
        document.head.appendChild(style);
    }
    
    toast.className = type === 'error' ? 'error show' : 'show';
    toast.textContent = message;
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('removeModal');
    const closeModal = document.querySelector('.modal-close');
    const cancelBtn = document.querySelector('.btn-cancel');
    const confirmBtn = document.getElementById('confirmRemoveBtn');
    
    function closeModalFunction() {
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
            lessonToRemove = null;
            removeButtonElement = null;
        }
    }
    
    if (closeModal) closeModal.addEventListener('click', closeModalFunction);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModalFunction);
    
    window.addEventListener('click', function(e) {
        if (e.target === modal) closeModalFunction();
    });
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (lessonToRemove) {
                // Show loading state
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Removing...';
                
                fetch('<?php echo BASE_URL; ?>/external/toggle-bookmark/' + lessonToRemove, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Bookmark removed successfully!', 'success');
                        
                        // Remove the bookmark card from DOM
                        if (removeButtonElement) {
                            const bookmarkCard = removeButtonElement.closest('.bookmark-card');
                            if (bookmarkCard) {
                                bookmarkCard.remove();
                            }
                            
                            // Also remove from recent list
                            const recentItems = document.querySelectorAll('.recent-item');
                            recentItems.forEach(item => {
                                const removeBtn = item.querySelector('.recent-remove');
                                if (removeBtn && removeBtn.onclick && removeBtn.onclick.toString().includes(lessonToRemove)) {
                                    item.remove();
                                }
                            });
                        }
                        
                        // Update bookmark count
                        const statBadge = document.querySelector('.stat-badge span');
                        if (statBadge) {
                            const currentCount = parseInt(statBadge.textContent);
                            statBadge.textContent = (currentCount - 1) + ' Bookmarked Lessons';
                        }
                        
                        // If no bookmarks left, reload to show empty state
                        const remainingCards = document.querySelectorAll('.bookmark-card').length;
                        if (remainingCards === 0) {
                            location.reload();
                        }
                    } else {
                        showToast(data.message || 'Failed to remove bookmark', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred. Please try again.', 'error');
                })
                .finally(() => {
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = 'Remove Bookmark';
                    closeModalFunction();
                });
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>