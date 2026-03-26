<?php
// File: /views/admin/quizzes.php
$pageTitle = 'Manage Quizzes - Admin - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

$quizzes = $quizzes ?? [];
$teachers = $teachers ?? [];
$totalPages = $totalPages ?? 1;
$currentPage = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$teacherFilter = $_GET['teacher'] ?? '';
$statusFilter = $_GET['status'] ?? '';
?>

<div class="admin-quizzes-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-pencil-alt"></i>
            Manage Quizzes
        </h1>
        <p class="page-subtitle">View and moderate all quizzes on the platform</p>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search quizzes..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <select name="teacher">
                <option value="">All Teachers</option>
                <?php foreach ($teachers as $teacher): ?>
                    <option value="<?php echo $teacher['id']; ?>" <?php echo $teacherFilter == $teacher['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="status">
                <option value="">All Status</option>
                <option value="published" <?php echo $statusFilter == 'published' ? 'selected' : ''; ?>>Published</option>
                <option value="draft" <?php echo $statusFilter == 'draft' ? 'selected' : ''; ?>>Draft</option>
            </select>
            
            <button type="submit" class="btn-filter">Apply Filters</button>
            <a href="<?php echo BASE_URL; ?>/admin/quizzes" class="btn-clear">Clear</a>
        </form>
    </div>

    <!-- Quizzes Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Teacher</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Questions</th>
                        <th>Attempts</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($quizzes)): ?>
                        <tr>
                            <td colspan="10" class="empty-message">No quizzes found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($quizzes as $quiz): ?>
                        <tr>
                            <td><?php echo $quiz['id']; ?></td>
                            <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                            <td><?php echo htmlspecialchars($quiz['teacher_name'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($quiz['class_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($quiz['subject_name'] ?? 'N/A'); ?></td>
                            <td class="number-cell"><?php echo $quiz['question_count'] ?? 0; ?></td>
                            <td class="number-cell"><?php echo $quiz['attempt_count'] ?? 0; ?></td>
                            <td>
                                <span class="status-badge <?php echo $quiz['is_published'] ? 'published' : 'draft'; ?>">
                                    <?php echo $quiz['is_published'] ? 'Published' : 'Draft'; ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($quiz['created_at'])); ?></td>
                            <td class="actions-cell">
                                <a href="<?php echo BASE_URL; ?>/admin/quizzes/view/<?php echo $quiz['id']; ?>" class="action-btn view" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/admin/quizzes/delete/<?php echo $quiz['id']; ?>" class="action-btn delete" title="Delete" onclick="return confirm('Are you sure you want to delete this quiz? All associated questions and results will be lost.')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($search); ?>&teacher=<?php echo $teacherFilter; ?>&status=<?php echo $statusFilter; ?>" class="page-link">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&teacher=<?php echo $teacherFilter; ?>&status=<?php echo $statusFilter; ?>" 
                   class="page-link <?php echo $i == $currentPage ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($search); ?>&teacher=<?php echo $teacherFilter; ?>&status=<?php echo $statusFilter; ?>" class="page-link">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.admin-quizzes-container {
    padding: 30px 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 30px;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #7f2677);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
}

.page-subtitle {
    color: black;
    font-size: 1rem;
}

.filters-section {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.filters-form {
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
    padding: 12px 15px 12px 45px;
    border: 2px solid #E2E8F0;
    border-radius: 8px;
    font-size: 0.95rem;
}

.filters-form select {
    padding: 12px 20px;
    border: 2px solid #f06724;
    border-radius: 8px;
    font-size: 0.95rem;
    min-width: 150px;
    background: white;
}

.btn-filter {
    padding: 12px 25px;
    background: #7f2677;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.btn-filter:hover {
    background: #f06724;
}

.btn-clear {
    padding: 12px 25px;
    background: #7f2677;
    color: white;
    border: 2px solid #E2E8F0;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
}

.btn-clear:hover {
    background: #f06724;
}

.table-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #F8FAFC;
    color: black;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 15px;
    text-align: left;
    border-bottom: 2px solid #E2E8F0;
}

.data-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #F1F5F9;
    color: black;
}

.data-table tr:hover td {
    background: #F8FAFC;
}

.number-cell {
    font-weight: 600;
    color: #7f2677;
}

.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 30px;
    font-size: 0.8rem;
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

.actions-cell {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
}

.action-btn.view {
    background: #EFF6FF;
    color: #7f2677;
}

.action-btn.view:hover {
    background: #f06724;
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

.empty-message {
    text-align: center;
    padding: 40px !important;
    color: black;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    padding: 20px;
    border-top: 1px solid #E2E8F0;
}

.page-link {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    text-decoration: none;
    color: black;
    border: 1px solid #E2E8F0;
}

.page-link:hover {
    background: #F1F5F9;
    border-color: #f06724;
}

.page-link.active {
    background: #f06724;
    color: white;
    border-color: #f06724;
}

@media (max-width: 768px) {
    .filters-form {
        flex-direction: column;
    }
    
    .search-box,
    .filters-form select,
    .btn-filter,
    .btn-clear {
        width: 100%;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>