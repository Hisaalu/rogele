<?php
// File: /views/teacher/quizzes.php
$pageTitle = 'My Quizzes | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$quizzes = $quizzes ?? [];
$totalPages = $totalPages ?? 1;
$currentPage = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
?>

<div class="quizzes-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-pencil-alt"></i>
                My Quizzes
            </h1>
            <p class="page-subtitle">Create and manage quizzes for your students</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/teacher/quizzes/create" class="btn-primary">
            <i class="fas fa-plus-circle"></i>
            Create New Quiz
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
                    placeholder="Search quizzes by title..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                >
            </div>
            <button type="submit" class="btn-search">
                <i class="fas fa-search"></i> Search
            </button>
            <?php if ($search): ?>
                <a href="<?php echo BASE_URL; ?>/teacher/quizzes" class="btn-clear">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Quizzes Grid -->
    <?php if (empty($quizzes)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-pencil-alt"></i>
            </div>
            <h3>No Quizzes Yet</h3>
            <p>You haven't created any quizzes. Start by creating your first quiz!</p>
            <a href="<?php echo BASE_URL; ?>/teacher/quizzes/create" class="btn-primary">
                <i class="fas fa-plus-circle"></i>
                Create Your First Quiz
            </a>
        </div>
    <?php else: ?>
        <div class="quizzes-grid">
            <?php foreach ($quizzes as $quiz): ?>
                <div class="quiz-card">
                    <div class="quiz-header">
                        <div class="quiz-status <?php echo $quiz['is_published'] ? 'published' : 'draft'; ?>">
                            <?php echo $quiz['is_published'] ? 'Published' : 'Draft'; ?>
                        </div>
                        <h3 class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></h3>
                    </div>

                    <div class="quiz-meta">
                        <span>
                            <i class="fas fa-graduation-cap"></i>
                            <?php echo $quiz['class_name'] ?? 'All Classes'; ?>
                        </span>
                        <span>
                            <i class="fas fa-book"></i>
                            <?php echo $quiz['subject_name'] ?? 'General'; ?>
                        </span>
                        <span>
                            <i class="fas fa-clock"></i>
                            <?php echo $quiz['time_limit'] ?? 30; ?> min
                        </span>
                    </div>

                    <p class="quiz-description">
                        <?php echo substr(htmlspecialchars($quiz['description'] ?? ''), 0, 100); ?>...
                    </p>

                    <div class="quiz-stats">
                        <span title="Questions">
                            <i class="fas fa-question-circle"></i> 
                            <?php echo $quiz['question_count'] ?? 0; ?> questions
                        </span>
                        <span title="Attempts">
                            <i class="fas fa-users"></i> 
                            <?php echo $quiz['attempt_count'] ?? 0; ?> attempts
                        </span>
                        <span title="Passing Score">
                            <i class="fas fa-trophy"></i> 
                            <?php echo $quiz['passing_score'] ?? 50; ?>% to pass
                        </span>
                    </div>

                    <div class="quiz-actions">
                        <a href="<?php echo BASE_URL; ?>/teacher/quizzes/add-questions/<?php echo $quiz['id']; ?>" class="action-btn questions" title="Add Questions">
                            <i class="fas fa-plus-circle"></i>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/teacher/quizzes/edit/<?php echo $quiz['id']; ?>" class="action-btn edit" title="Edit Quiz">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/teacher/quizzes/results/<?php echo $quiz['id']; ?>" class="action-btn results" title="View Results">
                            <i class="fas fa-chart-bar"></i>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/teacher/quizzes/preview/<?php echo $quiz['id']; ?>" class="action-btn preview" title="Preview Quiz" target="_blank">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/teacher/quizzes/delete/<?php echo $quiz['id']; ?>" class="action-btn delete" title="Delete Quiz" onclick="return confirm('Are you sure you want to delete this quiz? All associated questions and results will be lost.')">
                            <i class="fas fa-trash"></i>
                        </a>
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

<style>
.quizzes-container {
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

/* Quizzes Grid */
.quizzes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.quiz-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    position: relative;
    border: 1px solid #E2E8F0;
}

.quiz-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(139, 92, 246, 0.15);
    border-color: #7f2677;
}

.quiz-header {
    position: relative;
    margin-bottom: 15px;
}

.quiz-status {
    position: absolute;
    top: 0;
    right: 0;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.quiz-status.published {
    background: #10B981;
    color: white;
}

.quiz-status.draft {
    background: #f06724;
    color: white;
}

.quiz-title {
    color: black;
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 10px;
    padding-right: 80px;
}

.quiz-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    font-size: 0.85rem;
    color: black;
    flex-wrap: wrap;
}

.quiz-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.quiz-meta i {
    color: #f06724;
}

.quiz-description {
    color: black;
    font-size: 0.9rem;
    line-height: 1.6;
    margin-bottom: 20px;
    min-height: 60px;
}

.quiz-stats {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    padding: 15px 0;
    border-top: 1px solid #E2E8F0;
    border-bottom: 1px solid #E2E8F0;
    font-size: 0.85rem;
    color: black;
    flex-wrap: wrap;
}

.quiz-stats span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.quiz-stats i {
    color: #f06724;
}

.quiz-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
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

.action-btn.questions {
    background: #7f2677;
    color: white;
}

.action-btn.questions:hover {
    background: #7C3AED;
    transform: scale(1.1);
}

.action-btn.edit {
    background: #EFF6FF;
    color: #2563EB;
}

.action-btn.edit:hover {
    background: #2563EB;
    color: white;
}

.action-btn.results {
    background: #F0FDF4;
    color: #059669;
}

.action-btn.results:hover {
    background: #059669;
    color: white;
}

.action-btn.preview {
    background: #FEF3C7;
    color: #D97706;
}

.action-btn.preview:hover {
    background: #D97706;
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
    color: #1E293B;
    transition: all 0.3s ease;
    border: 1px solid #E2E8F0;
}

.page-link:hover {
    background: #F1F5F9;
    border-color: #7f2677;
}

.page-link.active {
    background: #7f2677;
    color: white;
    border-color: #7f2677;
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
    
    .quizzes-grid {
        grid-template-columns: 1fr;
    }
    
    .quiz-actions {
        justify-content: center;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .quiz-card {
        background: #1E293B;
        border-color: #334155;
    }
    
    .quiz-title {
        color: #F1F5F9;
    }
    
    .quiz-description {
        color: #94A3B8;
    }
    
    .empty-state {
        background: #1E293B;
    }
    
    .empty-state h3 {
        color: #F1F5F9;
    }
    
    .page-link {
        background: #1E293B;
        border-color: #334155;
        color: #94A3B8;
    }
    
    .page-link:hover {
        background: #334155;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>