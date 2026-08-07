<?php
// File: /views/admin/lessons.php
$pageTitle = 'Lessons | ROGELE';
require_once __DIR__ . '/../layouts/admin_header.php';

$lessons       = $lessons ?? [];
$teachers      = $teachers ?? [];
$totalPages    = $totalPages ?? 1;
$currentPage   = $_GET['page'] ?? 1;
$search        = $_GET['search'] ?? '';
$teacherFilter = $_GET['teacher'] ?? '';
$statusFilter  = $_GET['status'] ?? '';
?>

<div class="admin-lessons-container">
    <header class="page-header">
        <div class="header-text">
            <h1 class="page-title">
                <i class="fas fa-book-open" aria-hidden="true"></i>
                <span>Manage Lessons</span>
            </h1>
            <p class="page-subtitle">View, moderate, and manage all lessons on the platform</p>
        </div>
    </header>

    <section class="filters-section">
        <form method="GET" class="filters-form" id="filterForm">
            <div class="search-box">
                <i class="fas fa-search" aria-hidden="true"></i>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search lessons by title..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                    aria-label="Search lessons"
                >
            </div>
            
            <div class="filter-group">
                <select name="teacher" aria-label="Filter by teacher">
                    <option value="">All Teachers</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?php echo htmlspecialchars($teacher['id'] ?? ''); ?>" <?php echo $teacherFilter == ($teacher['id'] ?? '') ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(($teacher['first_name'] ?? '') . ' ' . ($teacher['last_name'] ?? '')); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <select name="status" aria-label="Filter by status">
                    <option value="">All Statuses</option>
                    <option value="published" <?php echo $statusFilter === 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="draft" <?php echo $statusFilter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending Approval</option>
                </select>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn-filter">Apply Filters</button>
                <a href="<?php echo BASE_URL; ?>/admin/lessons" class="btn-clear">Reset</a>
            </div>
        </form>
    </section>

    <main class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Teacher</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Visibility</th>
                        <th>Approval</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lessons)): ?>
                        <tr>
                            <td colspan="8" class="empty-message">
                                <i class="fas fa-book-reader" aria-hidden="true"></i>
                                <p>No lessons found</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($lessons as $lesson): ?>
                        <tr>
                            <td class="title-cell">
                                <span class="lesson-title">
                                    <?php echo htmlspecialchars($lesson['title'] ?? 'Untitled Lesson'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($lesson['teacher_name'] ?? 'Unknown'); ?></td>
                            <td><span class="meta-tag"><?php echo htmlspecialchars($lesson['class_name'] ?? 'N/A'); ?></span></td>
                            <td><span class="meta-tag"><?php echo htmlspecialchars($lesson['subject_name'] ?? 'N/A'); ?></span></td>
                            <td>
                                <span class="status-badge <?php echo !empty($lesson['is_published']) ? 'published' : 'draft'; ?>">
                                    <?php echo !empty($lesson['is_published']) ? 'Published' : 'Draft'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?php echo !empty($lesson['is_approved']) ? 'approved' : 'pending'; ?>">
                                    <?php echo !empty($lesson['is_approved']) ? 'Approved' : 'Pending'; ?>
                                </span>
                            </td>
                            <td class="date-cell">
                                <?php echo !empty($lesson['created_at']) ? date('M d, Y', strtotime($lesson['created_at'])) : 'N/A'; ?>
                            </td>
                            <td class="actions-cell">
                                <a href="<?php echo BASE_URL; ?>/admin/lessons/view/<?php echo $lesson['id']; ?>" class="action-btn view" title="View Lesson">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </a>
                                <?php if (empty($lesson['is_approved'])): ?>
                                    <a href="<?php echo BASE_URL; ?>/admin/lessons/approve/<?php echo $lesson['id']; ?>" class="action-btn approve" title="Approve Lesson" onclick="return confirm('Approve this lesson?')">
                                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/admin/lessons/reject/<?php echo $lesson['id']; ?>" class="action-btn reject" title="Reject Lesson" onclick="return confirm('Reject this lesson?')">
                                        <i class="fas fa-times-circle" aria-hidden="true"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($lessons) && $totalPages > 1): ?>
            <nav class="pagination" aria-label="Lessons pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/lessons?page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($search); ?>&teacher=<?php echo urlencode($teacherFilter); ?>&status=<?php echo urlencode($statusFilter); ?>" class="page-link" aria-label="Previous Page">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/lessons?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&teacher=<?php echo urlencode($teacherFilter); ?>&status=<?php echo urlencode($statusFilter); ?>" 
                    class="page-link <?php echo $i == $currentPage ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/lessons?page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($search); ?>&teacher=<?php echo urlencode($teacherFilter); ?>&status=<?php echo urlencode($statusFilter); ?>" class="page-link" aria-label="Next Page">
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </main>
</div>

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
    --shadow-sm: 0 4px 12px rgba(0,0,0,0.03);
    --shadow-md: 0 10px 30px rgba(0,0,0,0.05);
    --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.admin-lessons-container, 
.admin-lessons-container * {
    box-sizing: border-box;
}

.admin-lessons-container {
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
    padding: clamp(16px, 3vw, 32px);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: clamp(20px, 3vw, 30px);
    flex-wrap: wrap;
    gap: 12px;
}

.header-text {
    flex: 0 1 auto;
}

.page-title {
    font-size: clamp(1.4rem, 3.5vw, 2.2rem);
    font-weight: 700;
    color: var(--primary-purple);
    margin: 0 0 6px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-subtitle {
    color: var(--text-muted);
    font-size: clamp(0.85rem, 2vw, 0.95rem);
    margin: 0;
}

.filters-section {
    background: var(--bg-surface);
    border-radius: var(--radius-md);
    padding: clamp(14px, 2.5vw, 20px);
    margin-bottom: clamp(20px, 3vw, 25px);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
}

.filters-form {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}

.search-box {
    flex: 1 1 240px;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
}

.search-box input {
    width: 100%;
    padding: 10px 14px 10px 40px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: 0.9rem;
    transition: var(--transition);
}

.search-box input:focus {
    outline: none;
    border-color: var(--accent-orange);
    box-shadow: 0 0 0 3px rgba(240, 103, 36, 0.15);
}

.filter-group {
    flex: 0 1 180px;
}

.filters-form select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: 0.9rem;
    background: var(--bg-surface);
    cursor: pointer;
    transition: var(--transition);
}

.filters-form select:focus {
    outline: none;
    border-color: var(--accent-orange);
    box-shadow: 0 0 0 3px rgba(240, 103, 36, 0.15);
}

.filter-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.btn-filter, .btn-clear {
    padding: 10px 20px;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
}

.btn-filter {
    background: var(--primary-purple);
    color: white;
    border: none;
}

.btn-filter:hover {
    background: var(--accent-orange);
}

.btn-clear {
    background: #F1F5F9;
    color: var(--text-dark);
    border: 1px solid var(--border-color);
}

.btn-clear:hover {
    background: #E2E8F0;
}

.table-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #F8FAFC;
    color: var(--text-dark);
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 18px;
    text-align: left;
    border-bottom: 2px solid var(--border-color);
    white-space: nowrap;
}

.data-table th.text-right {
    text-align: right;
}

.data-table td {
    padding: 14px 18px;
    border-bottom: 1px solid #F1F5F9;
    color: var(--text-dark);
    vertical-align: middle;
    white-space: normal;
    word-break: break-word;
}

.data-table tr:hover td {
    background: #F8FAFC;
}

.title-cell {
    min-width: 200px;
}

.lesson-title {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-dark);
    display: inline-block;
}

.meta-tag {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    background: #F1F5F9;
    font-size: 0.8rem;
    color: var(--text-dark);
    white-space: nowrap;
}

.date-cell {
    font-size: 0.85rem;
    color: var(--text-muted);
    white-space: nowrap;
}

.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 50px;
    font-size: 0.775rem;
    font-weight: 600;
    white-space: nowrap;
}

.status-badge.published { background: #F0FDF4; color: #166534; }
.status-badge.draft { background: #F1F5F9; color: var(--text-muted); }
.status-badge.approved { background: #F0FDF4; color: #166534; }
.status-badge.pending { background: #FEF3C7; color: #92400E; }

.actions-cell {
    display: flex;
    gap: 6px;
    justify-content: flex-end;
    align-items: center;
}

.action-btn {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: var(--transition);
    font-size: 0.875rem;
}

.action-btn.view { background: #EFF6FF; color: var(--primary-purple); }
.action-btn.view:hover { background: var(--accent-orange); color: white; transform: translateY(-2px); }

.action-btn.approve { background: #F0FDF4; color: #059669; }
.action-btn.approve:hover { background: #059669; color: white; transform: translateY(-2px); }

.action-btn.reject { background: #FEF2F2; color: #DC2626; }
.action-btn.reject:hover { background: #DC2626; color: white; transform: translateY(-2px); }

.empty-message {
    text-align: center;
    padding: 48px 20px !important;
    color: var(--text-muted);
}

.empty-message i {
    font-size: 2.5rem;
    margin-bottom: 12px;
    opacity: 0.5;
}

.empty-message p {
    font-size: 0.9rem;
    margin: 0;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 6px;
    padding: 16px 20px;
    border-top: 1px solid var(--border-color);
    flex-wrap: wrap;
}

.page-link {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    text-decoration: none;
    color: var(--text-dark);
    font-size: 0.875rem;
    font-weight: 500;
    transition: var(--transition);
    border: 1px solid transparent;
}

.page-link:hover {
    background: #F1F5F9;
}

.page-link.active {
    background: var(--primary-purple);
    color: white;
}

@media (max-width: 576px) {
    .filters-form {
        flex-direction: column;
        align-items: stretch;
    }

    .search-box, .filter-group {
        flex: 1 1 100%;
    }

    .filter-actions {
        width: 100%;
    }

    .btn-filter, .btn-clear {
        flex: 1;
        text-align: center;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>