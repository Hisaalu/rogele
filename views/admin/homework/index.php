<?php
// File: /views/admin/homework/index.php
$pageTitle = 'Homework Management | ROGELE';
require_once __DIR__ . '/../../layouts/admin_header.php';

$homeworks = $homeworks ?? [];
$teachers = $teachers ?? [];
$classes = $classes ?? [];
$totalPages = $totalPages ?? 1;
$currentPage = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$teacherFilter = $_GET['teacher'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$classFilter = $_GET['class_id'] ?? '';
?>

<div class="admin-lessons-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-tasks"></i>
            Manage Homework
        </h1>
        <p class="page-subtitle">View and moderate all homework assignments across the platform</p>
    </div>

    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search homework title or description..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <select name="teacher">
                <option value="">All Teachers</option>
                <?php foreach ($teachers as $teacher): ?>
                    <option value="<?php echo $teacher['id']; ?>" <?php echo $teacherFilter == $teacher['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="class_id">
                <option value="">All Classes</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?php echo $class['id']; ?>" <?php echo $classFilter == $class['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($class['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="status">
                <option value="">All Status</option>
                <option value="active" <?php echo $statusFilter == 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="expired" <?php echo $statusFilter == 'expired' ? 'selected' : ''; ?>>Expired</option>
                <option value="disabled" <?php echo $statusFilter == 'disabled' ? 'selected' : ''; ?>>Disabled</option>
            </select>
            
            <button type="submit" class="btn-filter">Apply Filters</button>
            <a href="<?php echo BASE_URL; ?>/admin/homework" class="btn-clear">Clear</a>
        </form>
    </div>

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
                        <th>Submissions</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($homeworks)): ?>
                        <tr>
                            <td colspan="9" class="empty-message">No homework found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($homeworks as $hw): ?>
                            <?php 
                                $isExpired = strtotime($hw['due_date']) < time();
                                $isActive = (bool)$hw['is_active'];
                            ?>
                        <tr>
                            <td><?php echo $hw['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($hw['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($hw['teacher_name'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($hw['class_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($hw['subject_name'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge-count">
                                    <i class="fas fa-file-upload"></i> <?php echo $hw['submissions_count']; ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y h:i A', strtotime($hw['due_date'])); ?></td>
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
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/admin/homework/toggle-status/<?php echo $hw['id']; ?>" class="action-btn toggle" title="<?php echo $isActive ? 'Disable Homework' : 'Enable Homework'; ?>" onclick="return confirm('Change status for this homework?')">
                                    <i class="fas <?php echo $isActive ? 'fa-ban' : 'fa-check-circle'; ?>"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/admin/homework/delete/<?php echo $hw['id']; ?>" class="action-btn reject" title="Delete Homework" onclick="return confirm('Are you sure you want to permanently delete this homework and associated files?')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/homework?page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($search); ?>&teacher=<?php echo $teacherFilter; ?>&class_id=<?php echo $classFilter; ?>&status=<?php echo $statusFilter; ?>" class="page-link">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/homework?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&teacher=<?php echo $teacherFilter; ?>&class_id=<?php echo $classFilter; ?>&status=<?php echo $statusFilter; ?>" 
                    class="page-link <?php echo $i == $currentPage ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/homework?page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($search); ?>&teacher=<?php echo $teacherFilter; ?>&class_id=<?php echo $classFilter; ?>&status=<?php echo $statusFilter; ?>" class="page-link">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.admin-lessons-container {
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
    background-color: #7f2677;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
}

.page-subtitle {
    color: #555;
    font-size: 0.95rem;
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
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    font-size: 0.95rem;
}

.search-box input:focus {
    outline: none;
    border-color: #f06724;
    box-shadow: 0 0 0 2px rgba(240, 103, 36, 0.25);
}

.filters-form select {
    padding: 12px 20px;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    font-size: 0.95rem;
    min-width: 150px;
    background: white;
}

.filters-form select:focus {
    outline: none;
    border-color: #f06724;
    box-shadow: 0 0 0 2px rgba(240, 103, 36, 0.25);
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

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #F8FAFC;
    color: #000;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 15px;
    text-align: left;
    border-bottom: 2px solid #E2E8F0;
}

.data-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #F1F5F9;
    color: #000;
}

.data-table tr:hover td {
    background: #F8FAFC;
}

.badge-count {
    background: #F1F5F9;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
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
    color: #555;
}

.status-badge.pending {
    background: #FEF3C7;
    color: #92400E;
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

.action-btn.toggle {
    background: #FEF3C7;
    color: #D97706;
}

.action-btn.toggle:hover {
    background: #D97706;
    color: white;
}

.action-btn.reject {
    background: #FEF2F2;
    color: #DC2626;
}

.action-btn.reject:hover {
    background: #DC2626;
    color: white;
}

.empty-message {
    text-align: center;
    padding: 40px !important;
    color: #555;
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
    color: #000;
    border: 1px solid #E2E8F0;
}

.page-link.active {
    background: #7f2677;
    color: white;
    border-color: #7f2677;
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

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>