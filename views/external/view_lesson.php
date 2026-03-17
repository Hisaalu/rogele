<?php
// File: /views/external/view_lesson.php
$pageTitle = 'View Lesson - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

// This assumes $lesson is passed from the controller
if (!isset($lesson)) {
    header('Location: /rays-of-grace/external/materials');
    exit;
}
?>

<div style="padding: 40px 20px; max-width: 1000px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <a href="/rays-of-grace/external/materials" style="color: #8B5CF6; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Materials
        </a>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <button class="bookmark-btn <?php echo isset($lesson['is_bookmarked']) && $lesson['is_bookmarked'] ? 'bookmarked' : ''; ?>" 
                    onclick="toggleBookmark(<?php echo $lesson['id']; ?>)"
                    title="<?php echo isset($lesson['is_bookmarked']) && $lesson['is_bookmarked'] ? 'Remove from bookmarks' : 'Add to bookmarks'; ?>">
                <i class="fas fa-bookmark"></i>
            </button>
        <?php endif; ?>
    </div>

    <h1 style="font-size: 2.5rem; margin-bottom: 20px; color: #1E293B;"><?php echo htmlspecialchars($lesson['title']); ?></h1>
    
    <div style="display: flex; gap: 20px; margin-bottom: 30px; color: #64748B; flex-wrap: wrap;">
        <span><i class="fas fa-book" style="color: #8B5CF6;"></i> <?php echo htmlspecialchars($lesson['subject_name'] ?? 'General'); ?></span>
        <span><i class="fas fa-user" style="color: #F97316;"></i> <?php echo htmlspecialchars($lesson['teacher_name'] ?? 'Rays of Grace'); ?></span>
        <span><i class="fas fa-eye" style="color: #8B5CF6;"></i> <?php echo $lesson['views']; ?> views</span>
        <span><i class="fas fa-calendar" style="color: #F97316;"></i> <?php echo date('M d, Y', strtotime($lesson['created_at'])); ?></span>
    </div>
    
    <?php if (!empty($lesson['video_url'])): ?>
        <div style="margin-bottom: 40px; position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            <iframe src="https://www.youtube.com/embed/<?php echo getYoutubeId($lesson['video_url']); ?>" 
                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" 
                    frameborder="0" allowfullscreen></iframe>
        </div>
    <?php endif; ?>
    
    <div style="background: white; border-radius: 20px; padding: 40px; margin-bottom: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
        <h2 style="color: #1E293B; margin-bottom: 20px;">Lesson Content</h2>
        <div style="color: #64748B; line-height: 1.8;">
            <?php echo nl2br(htmlspecialchars($lesson['content'] ?? 'No content available.')); ?>
        </div>
    </div>
    
    <?php if (!empty($lesson['materials'])): ?>
        <div style="background: white; border-radius: 20px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            <h2 style="color: #1E293B; margin-bottom: 20px;">Downloadable Materials</h2>
            <div style="display: grid; gap: 15px;">
                <?php foreach ($lesson['materials'] as $material): ?>
                    <a href="/rays-of-grace/<?php echo $material['file_path']; ?>" download 
                       style="display: flex; align-items: center; gap: 15px; padding: 15px; background: #F8FAFC; border-radius: 10px; text-decoration: none; color: #1E293B; transition: background 0.3s ease;">
                        <i class="fas fa-file-pdf" style="color: #F97316; font-size: 1.5rem;"></i>
                        <span style="flex: 1;"><?php echo htmlspecialchars($material['file_name']); ?></span>
                        <span style="color: #64748B; font-size: 0.9rem;"><?php echo round($material['file_size'] / 1024, 2); ?> KB</span>
                        <i class="fas fa-download" style="color: #8B5CF6;"></i>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
function getYoutubeId($url) {
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
    return $matches[1] ?? '';
}
?>

<style>
    .material-item:hover {
        background: #F1F5F9;
    }

    /* Bookmark Button */
    .bookmark-btn {
        background: white;
        border: 2px solid #E2E8F0;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        color: #94A3B8;
        font-size: 1.2rem;
    }

    .bookmark-btn:hover {
        border-color: #8B5CF6;
        color: #8B5CF6;
        transform: scale(1.1);
    }

    .bookmark-btn.bookmarked {
        background: #8B5CF6;
        border-color: #8B5CF6;
        color: white;
    }

    .bookmark-btn.bookmarked:hover {
        background: #7C3AED;
        border-color: #7C3AED;
    }
</style>

<script>
function toggleBookmark(lessonId) {
    fetch(`/rays-of-grace/external/toggle-bookmark/${lessonId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const btn = event.currentTarget;
            btn.classList.toggle('bookmarked');
            btn.title = btn.classList.contains('bookmarked') ? 'Remove from bookmarks' : 'Add to bookmarks';
            
            // Show notification
            showNotification(data.message, 'success');
        } else {
            showNotification(data.error || 'Failed to update bookmark', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        background: type === 'success' ? '#10B981' : '#EF4444',
        color: 'white',
        padding: '12px 20px',
        borderRadius: '8px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        zIndex: '9999',
        display: 'flex',
        alignItems: 'center',
        gap: '10px',
        animation: 'slideIn 0.3s ease'
    });
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add animation styles
const style = document.createElement('style');
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
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>