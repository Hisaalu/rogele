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
    <!-- Header -->
    <div class="materials-header">
        <h1 class="page-title">
            <i class="fas fa-book-open"></i>
            Learning Materials
        </h1>
        <p class="page-subtitle">Explore lessons and resources to enhance your knowledge</p>
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

    <!-- Search and Filter Section -->
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

    <!-- Lessons Grid -->
    <?php if (empty($lessons)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <h3>No Lessons Found</h3>
            <p>We couldn't find any lessons matching your criteria. Try adjusting your search or check back later!</p>
        </div>
    <?php else: ?>
        <div class="lessons-grid">
            <?php foreach ($lessons as $lesson): ?>
                <div class="lesson-card">
                    <!-- Lesson Thumbnail -->
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
                    </div>

                    <!-- Lesson Content -->
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
                                <?php echo htmlspecialchars($lesson['teacher_name'] ?? 'Rays of Grace'); ?>
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
                                <i class="fas fa-paperclip"></i> <?php echo $lesson['materials_count'] ?? 0; ?> files
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

.materials-header {
    text-align: center;
    margin-bottom: 40px;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    background: linear-gradient(135deg, #7f2677);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
}

.page-subtitle {
    color: black;
    font-size: 1.1rem;
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
    color: #94A3B8;
}

.search-box input {
    width: 100%;
    padding: 14px 15px 14px 45px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: #7f2677;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.filter-group select {
    padding: 14px 20px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
    background: white;
    min-width: 180px;
    cursor: pointer;
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
    background: white;
    color: #64748B;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.btn-clear:hover {
    background: #F1F5F9;
    border-color: #94A3B8;
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
    background: linear-gradient(135deg, #f06724);
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

/* Lesson Content */
.lesson-content {
    padding: 25px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.lesson-title {
    color: #1E293B;
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
    color: black;
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
    background: linear-gradient(135deg, #7f2677 );
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
    background: linear-gradient(135deg, #8B5CF6, #F97316);
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
    color: #1E293B;
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.empty-state p {
    color: #64748B;
    font-size: 1rem;
    max-width: 400px;
    margin: 0 auto;
}

/* Responsive Design */
@media (max-width: 768px) {
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
}

@media (max-width: 480px) {
    .page-title {
        font-size: 2rem;
    }
    
    .lesson-stats {
        flex-wrap: wrap;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .search-section,
    .lesson-card,
    .empty-state {
        background: #1E293B;
    }
    
    .lesson-title {
        color: #F1F5F9;
    }
    
    .lesson-description {
        color: #94A3B8;
    }
    
    .filter-group select,
    .search-box input {
        background: #0F172A;
        border-color: #334155;
        color: #F1F5F9;
    }
    
    .btn-clear {
        background: transparent;
        color: #94A3B8;
        border-color: #334155;
    }
    
    .btn-clear:hover {
        background: #334155;
        color: #F1F5F9;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>