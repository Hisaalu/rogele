<?php
// File: /views/admin/homework/index.php
$pageTitle = 'Homework Management | ROGELE';
require_once __DIR__ . '/../../layouts/admin_header.php';

$homeworks   = $homeworks ?? [];
$teachers    = $teachers ?? [];
$classes     = $classes ?? [];
$totalPages  = $totalPages ?? 1;
$currentPage = $_GET['page'] ?? 1;
$search      = $_GET['search'] ?? '';
$teacherFilter = $_GET['teacher'] ?? '';
$statusFilter  = $_GET['status'] ?? '';
$classFilter   = $_GET['class_id'] ?? '';
?>

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

.admin-homework-container, 
.admin-homework-container * {
    box-sizing: border-box;
}

.admin-homework-container {
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
    flex: 0 1 160px;
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

.data-table th.text-center,
.data-table td.text-center {
    text-align: center;
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

.homework-title {
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

.count-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 2px 10px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.825rem;
}

.count-badge.submissions {
    background: #EFF6FF;
    color: var(--primary-purple);
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

.action-btn.toggle { background: #FEF3C7; color: #D97706; }
.action-btn.toggle:hover { background: #D97706; color: white; transform: translateY(-2px); }

.action-btn.delete { background: #FEF2F2; color: #DC2626; }
.action-btn.delete:hover { background: #DC2626; color: white; transform: translateY(-2px); }

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

<div class="admin-homework-container">
    <header class="page-header">
        <div class="header-text">
            <h1 class="page-title">
                <i class="fas fa-tasks" aria-hidden="true"></i>
                <span>Manage Homework</span>
            </h1>
            <p class="page-subtitle">View, review, and moderate all homework assignments across the platform</p>
        </div>
    </header>

    <section class="filters-section">
        <form method="GET" class="filters-form" id="filterForm">
            <div class="search-box">
                <i class="fas fa-search" aria-hidden="true"></i>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search homework title or description..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                    aria-label="Search homework"
                >
            </div>
            
            <div class="filter-group">
                <select name="teacher" aria-label="Filter by teacher">
                    <option value="">All Teachers</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?php echo htmlspecialchars($teacher['id'] ?? ''); ?>" <?php echo $teacherFilter == ($teacher['id'] ?? '') ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(explode(' ', trim($teacher['first_name'] ?? ''))[0]); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <select name="class_id" aria-label="Filter by class">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo htmlspecialchars($class['id'] ?? ''); ?>" <?php echo $classFilter == ($class['id'] ?? '') ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['name'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <select name="status" aria-label="Filter by status">
                    <option value="">All Statuses</option>
                    <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="expired" <?php echo $statusFilter === 'expired' ? 'selected' : ''; ?>>Expired</option>
                    <option value="disabled" <?php echo $statusFilter === 'disabled' ? 'selected' : ''; ?>>Disabled</option>
                </select>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn-filter">Apply Filters</button>
                <a href="<?php echo BASE_URL; ?>/admin/homework" class="btn-clear">Reset</a>
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
                        <th class="text-center">Submissions</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($homeworks)): ?>
                        <tr>
                            <td colspan="8" class="empty-message">
                                <i class="fas fa-tasks" aria-hidden="true"></i>
                                <p>No homework found</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($homeworks as $hw): ?>
                            <?php 
                                $dueDate   = !empty($hw['due_date']) ? strtotime($hw['due_date']) : 0;
                                $isExpired = $dueDate < time();
                                $isActive  = !empty($hw['is_active']);
                                
                                $rawName    = $hw['teacher_name'] ?? 'Unknown';
                                $firstName  = explode(' ', trim($rawName))[0];
                            ?>
                        <tr>
                            <td class="title-cell">
                                <span class="homework-title">
                                    <?php echo htmlspecialchars($hw['title'] ?? 'Untitled Homework'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($firstName); ?></td>
                            <td><span class="meta-tag"><?php echo htmlspecialchars($hw['class_name'] ?? 'N/A'); ?></span></td>
                            <td><span class="meta-tag"><?php echo htmlspecialchars($hw['subject_name'] ?? 'N/A'); ?></span></td>
                            <td class="text-center">
                                <span class="count-badge submissions">
                                    <i class="fas fa-file-upload" aria-hidden="true"></i> <?php echo htmlspecialchars((string)($hw['submissions_count'] ?? 0)); ?>
                                </span>
                            </td>
                            <td class="date-cell">
                                <?php echo $dueDate ? date('M d, Y h:i A', $dueDate) : 'N/A'; ?>
                            </td>
                            <td>
                                <?php if (!$isActive): ?>
                                    <span class="status-badge draft">Disabled</span>
                                <?php elseif ($isExpired): ?>
                                    <span class="status-badge pending">Expired</span>
                                <?php else: ?>
                                    <span class="status-badge published">Active</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions-cell">
                                <a href="<?php echo BASE_URL; ?>/admin/homework/view/<?php echo $hw['id']; ?>" class="action-btn view" title="View Submissions & Details">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/admin/homework/toggle-status/<?php echo $hw['id']; ?>" class="action-btn toggle" title="<?php echo $isActive ? 'Disable Homework' : 'Enable Homework'; ?>" onclick="return confirm('Change status for this homework?')">
                                    <i class="fas <?php echo $isActive ? 'fa-ban' : 'fa-check-circle'; ?>" aria-hidden="true"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/admin/homework/delete/<?php echo $hw['id']; ?>" class="action-btn delete" title="Delete Homework" onclick="return confirm('Are you sure you want to permanently delete this homework and associated files?')">
                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($homeworks) && $totalPages > 1): ?>
            <nav class="pagination" aria-label="Homework pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/homework?page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($search); ?>&teacher=<?php echo urlencode($teacherFilter); ?>&class_id=<?php echo urlencode($classFilter); ?>&status=<?php echo urlencode($statusFilter); ?>" class="page-link" aria-label="Previous Page">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/homework?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&teacher=<?php echo urlencode($teacherFilter); ?>&class_id=<?php echo urlencode($classFilter); ?>&status=<?php echo urlencode($statusFilter); ?>" 
                       class="page-link <?php echo $i == $currentPage ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/homework?page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($search); ?>&teacher=<?php echo urlencode($teacherFilter); ?>&class_id=<?php echo urlencode($classFilter); ?>&status=<?php echo urlencode($statusFilter); ?>" class="page-link" aria-label="Next Page">
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>