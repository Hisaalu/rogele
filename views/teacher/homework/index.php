<?php
// file: views/teacher/homework/index.php
$pageTitle = 'Manage Homework | ROGELE';
require_once __DIR__ . '/../../layouts/header.php';

$homeworks = $homeworks ?? [];
$classes = $classes ?? [];
$currentStatus = $_GET['status'] ?? '';
$currentSearch = $_GET['search'] ?? '';
$currentClassId = $_GET['class_id'] ?? '';
$totalPages = $totalPages ?? 1;
$currentPage = $_GET['page'] ?? 1;

function buildQueryString($extraParams = []) {
    $params = $_GET;
    unset($params['page']);
    return http_build_query(array_merge($params, $extraParams));
}
?>

<div class="homework-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-tasks"></i>
                Homework Management
            </h1>
            <p class="page-subtitle">Create and manage homework for your students</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/teacher/homework/create" class="btn-primary">
            <i class="fas fa-plus-circle"></i>
            Create New Homework
        </a>
    </div>

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

    <div class="toolbar-section">
        <form method="GET" action="<?php echo BASE_URL; ?>/teacher/homework" class="search-filter-form">
            <?php if (!empty($currentStatus)): ?>
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($currentStatus); ?>">
            <?php endif; ?>

            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" placeholder="Search title or description..." 
                       value="<?php echo htmlspecialchars($currentSearch); ?>" class="form-input">
            </div>

            <div class="filter-box">
                <select name="class_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo $currentClassId == $class['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-search"><i class="fas fa-search"></i> Search</button>

            <?php if (!empty($currentSearch) || !empty($currentClassId)): ?>
                <a href="<?php echo BASE_URL; ?>/teacher/homework<?php echo $currentStatus ? '?status=' . urlencode($currentStatus) : ''; ?>" class="btn-reset">
                    <i class="fas fa-redo"></i> Reset Filters
                </a>
            <?php endif; ?>
        </form>

        <div class="filter-tabs">
            <a href="<?php echo BASE_URL; ?>/teacher/homework?<?php echo buildQueryString(['status' => '']); ?>" class="filter-tab <?php echo !$currentStatus ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> All
            </a>
            <a href="<?php echo BASE_URL; ?>/teacher/homework?<?php echo buildQueryString(['status' => 'active']); ?>" class="filter-tab <?php echo $currentStatus === 'active' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Active
            </a>
            <a href="<?php echo BASE_URL; ?>/teacher/homework?<?php echo buildQueryString(['status' => 'expired']); ?>" class="filter-tab <?php echo $currentStatus === 'expired' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-times"></i> Expired
            </a>
        </div>
    </div>

    <?php if (empty($homeworks)): ?>
        <div class="empty-state">
            <i class="fas fa-tasks"></i>
            <h3>No Homework Found</h3>
            <p>Try refining your search terms or filters to find what you're looking for.</p>
        </div>
    <?php else: ?>
        <div class="homework-grid">
            <?php foreach ($homeworks as $homework): 
                $isExpired = strtotime($homework['due_date']) < time();
            ?>
                <div class="homework-card <?php echo $isExpired ? 'expired' : ''; ?>">
                    <div class="homework-header">
                        <div class="homework-title">
                            <i class="fas fa-file-alt"></i>
                            <?php echo htmlspecialchars($homework['title']); ?>
                        </div>
                        <div class="homework-status">
                            <?php if ($isExpired): ?>
                                <span class="badge expired">Expired</span>
                            <?php else: ?>
                                <span class="badge active">Active</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="homework-meta">
                        <span><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($homework['class_name']); ?></span>
                        <span><i class="fas fa-book"></i> <?php echo htmlspecialchars($homework['subject_name']); ?></span>
                        <span><i class="fas fa-calendar-alt"></i> Due: <?php echo date('M d, Y', strtotime($homework['due_date'])); ?></span>
                    </div>
                    
                    <p class="homework-description">
                        <?php echo substr(htmlspecialchars($homework['description'] ?? ''), 0, 100); ?>...
                    </p>
                    
                    <div class="homework-stats">
                        <div class="stat">
                            <span class="stat-value"><?php echo $homework['submissions_count']; ?></span>
                            <span class="stat-label">Submissions</span>
                        </div>
                    </div>
                    
                    <div class="homework-actions">
                        <a href="<?php echo BASE_URL; ?>/teacher/homework/submissions/<?php echo $homework['id']; ?>" class="btn-submissions">
                            <i class="fas fa-users"></i> View Submissions
                        </a>
                        <a href="<?php echo BASE_URL; ?>/teacher/homework/preview/<?php echo $homework['id']; ?>" class="btn-preview" title="Preview Homework">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/teacher/homework/edit/<?php echo $homework['id']; ?>" class="btn-edit" title="Edit Homework">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/teacher/homework/delete/<?php echo $homework['id']; ?>" class="btn-delete" title="Delete Homework"
                        onclick="return confirm('Are you sure you want to delete this homework?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?<?php echo buildQueryString(['page' => $i]); ?>"
                        class="page-link <?php echo $i == $currentPage ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.toolbar-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 30px;
}

.search-filter-form {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.search-box {
    position: relative;
    width: 280px;
}

.search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #f06724;
}

.form-input {
    width: 100%;
    padding: 10px 14px 10px 40px;
    border: 1px solid #E2E8F0;
    border-radius: 50px;
    outline: none;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.form-input:focus, .form-select:focus {
    border-color: #f06724;
    box-shadow: 0 0 0 3px rgba(240, 103, 36, 0.1);
}

.form-select {
    padding: 10px 20px;
    border: 1px solid #E2E8F0;
    border-radius: 50px;
    outline: none;
    font-size: 0.9rem;
    background: white;
    cursor: pointer;
}

.btn-search {
    background: #7f2677;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-search:hover {
    background: #f06724;
}

.btn-reset {
    color: #ef4444;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
}

.homework-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

.badge.editable {
    background: #EFF6FF;
    color: #1E40AF;
}

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
    background-color: #7f2677;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
}

.page-subtitle {
    color: #555;
    font-size: 0.95rem;
}

.btn-primary {
    background-color: #7f2677;
    color: white;
    padding: 12px 24px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(240, 103, 36, 0.3);
}

.filter-tabs {
    display: flex;
    gap: 10px;
    background: white;
    padding: 6px;
    border-radius: 60px;
    width: fit-content;
    border: 1px solid #E2E8F0;
}

.filter-tab {
    padding: 8px 20px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 400;
    color: #000;
    transition: all 0.3s;
}

.filter-tab i{
    color: #f06724;
}

.filter-tab.active {
    background-color: #7f2677;
    color: white;
}

.filter-tab.active i{
    color: white;
}

.filter-tab:hover:not(.active) {
    background: #F1F5F9;
}

.homework-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 25px;
}

.homework-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    transition: all 0.3s;
    border: 1px solid #E2E8F0;
}

.homework-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(127, 38, 119, 0.15);
    border-color: #f06724;
}

.homework-card.expired {
    opacity: 0.8;
    background: #F8FAFC;
}

.homework-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.homework-title {
    font-size: 1.2rem;
    font-weight: 500;
    color: #000;
    display: flex;
    align-items: center;
    gap: 8px;
}

.homework-title i{
    color: #f06724;
}

.badge {
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge.active {
    background: #F0FDF4;
    color: #166534;
}

.badge.expired {
    background: #FEF2F2;
    color: #B91C1C;
}

.homework-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
    font-size: 0.85rem;
    color: #555;
}

.homework-meta i {
    color: #f06724;
    margin-right: 4px;
}

.homework-description {
    color: #555;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 15px;
}

.homework-stats {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 10px;
    padding: 15px 0;
    border-top: 1px solid #E2E8F0;
    border-bottom: 1px solid #E2E8F0;
    margin-bottom: 15px;
    text-align: center;
}

.stat {
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 1.3rem;
    font-weight: 700;
    color: #7f2677;
}

.stat-label {
    font-size: 0.7rem;
    color: #555;
}

.homework-actions {
    display: flex;
    gap: 10px;
}

.btn-preview {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.3s;
    background: #F3E8FF;
    color: #7f2677;
}

.btn-preview:hover {
    background: #7f2677;
    color: white;
}

.btn-submissions {
    flex: 1;
    background: #f06724;
    color: white;
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 600;
    text-align: center;
    transition: all 0.3s;
}

.btn-submissions:hover {
    background: #f06724;
}

.btn-edit, .btn-delete {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-edit {
    background: #EFF6FF;
    color: #2563EB;
}

.btn-edit:hover {
    background: #2563EB;
    color: white;
}

.btn-delete {
    background: #FEF2F2;
    color: #EF4444;
}

.btn-delete:hover {
    background: #EF4444;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 20px;
}

.empty-state p{
    color: #555;
}

.empty-state i {
    font-size: 4rem;
    color: #f06724;
    margin-bottom: 20px;
}

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
    border-radius: 10px;
    text-decoration: none;
    color: #1E293B;
    border: 1px solid #E2E8F0;
}

.page-link.active {
    background-color: #7f2677;
    color: white;
    border: none;
}

@media (max-width: 768px) {
    .toolbar-section {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-filter-form {
        flex-direction: column;
    }
    
    .search-box {
        width: 100%;
    }

    .form-select {
        width: 100%;
    }

    .homework-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-tabs {
        width: 100%;
        justify-content: center;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>