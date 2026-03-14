// File: /public/js/main.js
$(document).ready(function() {
    // Mobile menu toggle
    $('#mobileToggle').click(function() {
        $('#navMenu').toggleClass('active');
        $(this).find('i').toggleClass('fa-bars fa-times');
    });
    
    // Close mobile menu when clicking outside
    $(document).click(function(event) {
        if (!$(event.target).closest('.nav-menu, .mobile-toggle').length) {
            $('#navMenu').removeClass('active');
            $('#mobileToggle i').removeClass('fa-times').addClass('fa-bars');
        }
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Form validation
    $('form').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('error');
                isValid = false;
            } else {
                $(this).removeClass('error');
            }
        });
        
        // Email validation
        $(this).find('input[type="email"]').each(function() {
            const email = $(this).val();
            if (email && !isValidEmail(email)) {
                $(this).addClass('error');
                showNotification('Please enter a valid email address', 'error');
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showNotification('Please fill in all required fields correctly', 'error');
        }
    });
    
    // File upload preview
    $('input[type="file"]').on('change', function() {
        const files = this.files;
        const preview = $(this).siblings('.file-preview');
        
        if (preview.length) {
            preview.empty();
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    if (file.type.startsWith('image/')) {
                        preview.append(`<img src="${e.target.result}" alt="Preview">`);
                    } else {
                        preview.append(`<div class="file-info">${file.name} (${formatBytes(file.size)})</div>`);
                    }
                };
                
                reader.readAsDataURL(file);
            }
        }
    });
    
    // Search functionality
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();
        
        if (query.length >= 3) {
            searchTimeout = setTimeout(function() {
                performSearch(query);
            }, 500);
        }
    });
    
    // Load more functionality
    let page = 1;
    $('#loadMore').click(function() {
        page++;
        loadMoreContent(page);
    });
    
    // Bookmark lesson
    $('.bookmark-btn').click(function() {
        const lessonId = $(this).data('lesson-id');
        bookmarkLesson(lessonId);
    });
});

// Email validation
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Format bytes
function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = $(`
        <div class="notification notification-${type}">
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 
                                 type === 'error' ? 'exclamation-circle' : 
                                 type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close"><i class="fas fa-times"></i></button>
        </div>
    `);
    
    $('body').append(notification);
    
    setTimeout(function() {
        notification.fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
    
    notification.find('.notification-close').click(function() {
        notification.fadeOut(function() {
            $(this).remove();
        });
    });
}

// Perform search
function performSearch(query) {
    $.ajax({
        url: '/api/search',
        method: 'GET',
        data: { q: query },
        success: function(response) {
            displaySearchResults(response);
        },
        error: function(xhr, status, error) {
            console.error('Search failed:', error);
        }
    });
}

// Load more content
function loadMoreContent(page) {
    $.ajax({
        url: window.location.href,
        method: 'GET',
        data: { page: page },
        success: function(response) {
            // Append new content
            $('#contentContainer').append(response);
        }
    });
}

// Bookmark lesson
function bookmarkLesson(lessonId) {
    $.ajax({
        url: `/learner/bookmark/${lessonId}`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCsrfToken()
        },
        success: function(response) {
            if (response.success) {
                showNotification('Lesson bookmarked successfully', 'success');
                updateBookmarkButton(lessonId, true);
            }
        },
        error: function() {
            showNotification('Failed to bookmark lesson', 'error');
        }
    });
}

// Get CSRF token
function getCsrfToken() {
    return $('meta[name="csrf-token"]').attr('content');
}

// Update bookmark button
function updateBookmarkButton(lessonId, isBookmarked) {
    const button = $(`.bookmark-btn[data-lesson-id="${lessonId}"]`);
    if (isBookmarked) {
        button.addClass('bookmarked');
        button.find('i').removeClass('far').addClass('fas');
    } else {
        button.removeClass('bookmarked');
        button.find('i').removeClass('fas').addClass('far');
    }
}