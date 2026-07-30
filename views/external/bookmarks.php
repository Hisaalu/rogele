<?php
// File: /views/external/bookmarks.php
$pageTitle = 'Bookmarks | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$bookmarks = $bookmarks ?? [];
?>

<div class="bookmarks-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-bookmark"></i>
                Bookmarks
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
        <div class="bookmarks-grid">
            <?php foreach ($bookmarks as $bookmark): ?>
                <div class="bookmark-card" data-lesson-id="<?php echo $bookmark['id']; ?>">
                    <div class="bookmark-header">
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
                            echo htmlspecialchars(substr($description, 0, 110)) . (strlen($description) > 110 ? '...' : ''); 
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
                        <button class="btn-share" onclick="shareLesson('<?php echo htmlspecialchars($bookmark['title'], ENT_QUOTES); ?>', <?php echo $bookmark['id']; ?>)" title="Share Lesson">
                            <i class="fas fa-share-alt"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="removeModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Remove Bookmark</h3>
            <span class="modal-close">&times;</span>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to remove this lesson from your bookmarks?</p>
            <div class="warning-text">You can always bookmark it again later if needed.</div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel">Cancel</button>
            <button id="confirmRemoveBtn" class="btn-confirm-remove">Remove Bookmark</button>
        </div>
    </div>
</div>

<style>
:root {
    --color-purple: #7f2677;
    --color-purple-hover: #661e5f;
    --color-orange: #f06724;
    --color-orange-hover: #d65319;
    --color-text-dark: #000;
    --color-text-muted: #555;
    --color-bg-light: #f8fafc;
    --border-radius-lg: 16px;
    --border-radius-md: 12px;
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
    --shadow-md: 0 10px 25px rgba(127, 38, 119, 0.05);
    --shadow-hover: 0 20px 35px rgba(127, 38, 119, 0.12);
    --transition-smooth: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.bookmarks-container {
    max-width: 1300px;
    margin: 0 auto;
    padding: 40px 24px;
    min-height: calc(100vh - 250px);
    font-family: 'Inter', sans-serif;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 36px;
    flex-wrap: wrap;
    gap: 20px;
}

.page-title {
    font-size: 2.25rem;
    font-weight: 800;
    color: var(--color-purple);
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 14px;
    letter-spacing: -0.02em;
}

.page-title i {
    color: var(--color-purple);
}

.page-subtitle {
    color: var(--color-text-muted);
    font-size: 0.95rem;
    margin: 0;
}

.header-stats .stat-badge {
    background: #white;
    padding: 12px 24px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    color: var(--color-purple);
    box-shadow: var(--shadow-sm);
    border: 1px solid #e2e8f0;
}

.header-stats .stat-badge i {
    color: var(--color-orange);
}

.empty-state {
    text-align: center;
    padding: 80px 40px;
    background: white;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-md);
    border: 1px solid #e2e8f0;
    max-width: 600px;
    margin: 40px auto;
}

.empty-icon {
    width: 90px;
    height: 90px;
    margin: 0 auto 24px;
    background: #fffaf0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px dashed #fcd34d;
}

.empty-icon i {
    font-size: 2.5rem;
    color: var(--color-orange);
}

.empty-state h3 {
    color: var(--color-text-dark);
    font-size: 1.6rem;
    font-weight: 700;
    margin: 0 0 12px 0;
}

.empty-state p {
    color: var(--color-text-muted);
    margin: 0 0 30px 0;
    font-size: 0.95rem;
    line-height: 1.5;
}

.btn-explore {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 32px;
    background-color: var(--color-purple);
    color: white;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 600;
    transition: var(--transition-smooth);
}

.btn-explore:hover {
    background-color: var(--color-purple-hover);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(127, 38, 119, 0.25);
}

.bookmarks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 28px;
}

.bookmark-card {
    background: white;
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    transition: var(--transition-smooth);
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    border: 1px solid #e2e8f0;
}

.bookmark-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-hover);
    border-color: var(--color-purple);
}

.bookmark-header {
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(to bottom, #f8fafc, white);
    border-bottom: 1px solid #f1f5f9;
}

.lesson-type {
    background: #f1f5f9;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--color-purple);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.btn-remove-bookmark {
    background: #f1f5f9;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition-smooth);
    color: var(--color-text-muted);
}

.btn-remove-bookmark:hover {
    background: #fee2e2;
    color: #ef4444;
    transform: rotate(90deg);
}

.bookmark-content {
    padding: 24px 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.lesson-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--color-text-dark);
    margin: 0 0 12px 0;
    line-height: 1.4;
}

.lesson-description {
    color: var(--color-text-muted);
    font-size: 0.95rem;
    line-height: 1.6;
    margin: 0 0 20px 0;
}

.lesson-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: auto;
}

.meta-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    background: var(--color-bg-light);
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--color-text-dark);
    border: 1px solid #f1f5f9;
}

.meta-tag i {
    color: var(--color-orange);
}

.bookmark-footer {
    display: flex;
    gap: 12px;
    padding: 16px 20px 24px;
    background-color: white;
}

.btn-view-lesson {
    flex: 1;
    background-color: var(--color-purple);
    color: white;
    text-decoration: none;
    padding: 12px 20px;
    border-radius: var(--border-radius-md);
    font-weight: 600;
    font-size: 0.9rem;
    text-align: center;
    transition: var(--transition-smooth);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-view-lesson:hover {
    background-color: var(--color-purple-hover);
    box-shadow: 0 4px 12px rgba(127, 38, 119, 0.2);
}

.btn-share {
    background: #f1f5f9;
    border: none;
    width: 45px;
    border-radius: var(--border-radius-md);
    cursor: pointer;
    transition: var(--transition-smooth);
    color: var(--color-purple);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
}

.btn-share:hover {
    background: var(--color-orange);
    color: white;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.4);
    backdrop-filter: blur(6px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: var(--border-radius-lg);
    max-width: 480px;
    width: 90%;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: modalSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-header {
    padding: 20px 24px;
    background: #fdf2f2;
    border-bottom: 1px solid #fee2e2;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #991b1b;
    font-size: 1.2rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-close {
    font-size: 1.5rem;
    cursor: pointer;
    color: #991b1b;
    opacity: 0.6;
    transition: var(--transition-smooth);
}

.modal-close:hover {
    opacity: 1;
}

.modal-body {
    padding: 24px;
}

.modal-body p {
    color: var(--color-text-dark);
    margin: 0 0 16px 0;
    font-size: 0.95rem;
    line-height: 1.5;
}

.warning-text {
    background: #fffbeb;
    padding: 14px;
    border-radius: var(--border-radius-md);
    color: #92400e;
    font-size: 0.88rem;
    border: 1px solid #fef3c7;
    font-weight: 500;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 24px;
    background-color: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.btn-cancel {
    padding: 12px 24px;
    background: white;
    color: var(--color-text-muted);
    border: 1px solid #e2e8f0;
    border-radius: var(--border-radius-md);
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition-smooth);
}

.btn-cancel:hover {
    background: #f1f5f9;
    color: var(--color-text-dark);
}

.btn-confirm-remove {
    padding: 12px 24px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: var(--border-radius-md);
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition-smooth);
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-confirm-remove:hover {
    background: #dc2626;
}

/* Animations */
@keyframes modalSlideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Responsive Styles */
@media (max-width: 768px) {
    .bookmarks-container { padding: 24px 16px; }
    .page-header { flex-direction: column; align-items: flex-start; gap: 16px; }
    .page-title { font-size: 1.85rem; }
    .bookmarks-grid { grid-template-columns: 1fr; gap: 20px; }
    .bookmark-footer { padding: 12px 16px 20px; }
}
</style>

<script>
let lessonToRemove = null;
let removeButtonElement = null;

function removeBookmark(lessonId, buttonElement) {
    lessonToRemove = lessonId;
    removeButtonElement = buttonElement;
    const modal = document.getElementById('removeModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function shareLesson(lessonTitle, lessonId) {
    const url = '<?php echo BASE_URL; ?>/external/view-lesson/' + lessonId;
    
    if (navigator.share) {
        navigator.share({
            title: lessonTitle,
            text: `Check out this lesson on ROGELE: ${lessonTitle}`,
            url: url
        }).catch(() => {
            copyToClipboard(url, lessonTitle);
        });
    } else {
        copyToClipboard(url, lessonTitle);
    }
}

function copyToClipboard(text, lessonTitle) {
    navigator.clipboard.writeText(text).then(() => {
        showToast(`Link to "${lessonTitle}" copied to clipboard!`, 'success');
    }).catch(() => {
        showToast('Failed to copy link', 'error');
    });
}

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
                bottom: 24px;
                right: 24px;
                padding: 14px 24px;
                border-radius: 12px;
                background: #10b981;
                color: white;
                font-weight: 600;
                box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.3);
                z-index: 9999;
                opacity: 0;
                transform: translateY(10px);
                transition: opacity 0.25s, transform 0.25s;
                pointer-events: none;
                font-family: system-ui, sans-serif;
                font-size: 0.95rem;
            }
            #customToast.error { 
                background: #ef4444; 
                box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.3);
            }
            #customToast.show { opacity: 1; transform: translateY(0); }
        `;
        document.head.appendChild(style);
    }
    
    toast.className = type === 'error' ? 'error show' : 'show';
    toast.textContent = message;
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3500);
}

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
            if (!lessonToRemove) return;

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
                    showToast('Removed from your Bookmarks!', 'success');
                    
                    if (removeButtonElement) {
                        const bookmarkCard = removeButtonElement.closest('.bookmark-card');
                        if (bookmarkCard) {
                            bookmarkCard.style.opacity = '0';
                            bookmarkCard.style.transform = 'scale(0.95)';
                            setTimeout(() => {
                                bookmarkCard.remove();
                                
                                const remainingCards = document.querySelectorAll('.bookmark-card').length;
                                if (remainingCards === 0) {
                                    location.reload();
                                }
                            }, 250);
                        }
                    }
                    
                    const statBadge = document.querySelector('.stat-badge span');
                    if (statBadge) {
                        const currentCount = parseInt(statBadge.textContent);
                        statBadge.textContent = (currentCount - 1) + ' Bookmarked Lessons';
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
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>