<?php
// file: views/teacher/homework/index.php
$pageTitle = 'Manage Homework | ROGELE';
require_once __DIR__ . '/../../layouts/header.php';

$homeworks = $homeworks ?? [];
$currentStatus = $_GET['status'] ?? '';
$totalPages = $totalPages ?? 1;
$currentPage = $_GET['page'] ?? 1;
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

    <div class="filter-tabs">
        <a href="<?php echo BASE_URL; ?>/teacher/homework" class="filter-tab <?php echo !$currentStatus ? 'active' : ''; ?>">
            <i class="fas fa-list"></i> All
        </a>
        <a href="<?php echo BASE_URL; ?>/teacher/homework?status=active" class="filter-tab <?php echo $currentStatus === 'active' ? 'active' : ''; ?>">
            <i class="fas fa-clock"></i> Active
        </a>
        <a href="<?php echo BASE_URL; ?>/teacher/homework?status=expired" class="filter-tab <?php echo $currentStatus === 'expired' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-times"></i> Expired
        </a>
    </div>

    <?php if (empty($homeworks)): ?>
        <div class="empty-state">
            <i class="fas fa-tasks"></i>
            <h3>No Homework Created Yet</h3>
            <p>Click the "Create New Homework" button to assign homework to your students.</p>
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
                        <a href="<?php echo BASE_URL; ?>/teacher/homework/edit/<?php echo $homework['id']; ?>" class="btn-edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/teacher/homework/delete/<?php echo $homework['id']; ?>" class="btn-delete" 
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
                    <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($currentStatus); ?>"
                        class="page-link <?php echo $i == $currentPage ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
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
    font-size: 1rem;
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
    margin-bottom: 30px;
    background: white;
    padding: 10px;
    border-radius: 60px;
    width: fit-content;
}

.filter-tab {
    padding: 8px 24px;
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