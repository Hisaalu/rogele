<?php
$pageTitle = 'My Homework | ROGELE';
require_once __DIR__ . '/../../layouts/header.php';

$homeworks = $homeworks ?? [];
$stats = $stats ?? [];
$currentStatus = $_GET['status'] ?? '';
?>

<style>
.student-homework-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
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

.stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-info {
    flex: 1;
}

.stat-value {
    display: block;
    font-size: 1.8rem;
    font-weight: 700;
    color: #000;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.8rem;
    color: #555;
}

.filter-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    background: white;
    padding: 10px;
    border-radius: 60px;
    width: fit-content;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 8px 24px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    color: #555;
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

.status-pending { border-left: 4px solid #F59E0B; }
.status-graded { border-left: 4px solid #10B981; }
.status-late { border-left: 4px solid #EF4444; }

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
    font-size: 0.7rem;
    font-weight: 600;
}

.badge.pending { background: #FEF3C7; color: #92400E; }
.badge.graded { background: #F0FDF4; color: #166534; }
.badge.late { background: #FEF2F2; color: #B91C1C; }

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

.grade-info {
    margin: 15px 0;
    padding: 10px;
    background: #F0FDF4;
    border-radius: 10px;
    text-align: center;
}

.grade-value {
    font-weight: 700;
    color: #10B981;
    font-size: 1.2rem;
}

.btn-view {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background-color: #7f2677;
    color: white;
    text-decoration: none;
    padding: 12px;
    border-radius: 50px;
    font-weight: 600;
    margin-top: 15px;
    transition: all 0.3s;
}

.btn-view:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(240, 103, 36, 0.3);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 20px;
}

.empty-state p{
    color: #555;
    font-size: 0.95rem;
}

.empty-state i {
    font-size: 4rem;
    color: #f06724;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .stats-overview {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filter-tabs {
        width: 100%;
        justify-content: center;
    }
    
    .homework-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .stats-overview {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="student-homework-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-tasks"></i>
            My Homework
        </h1>
        <p class="page-subtitle">View and submit your assignments</p>
    </div>

    <div class="stats-overview">
        <div class="stat-card">
            <div class="stat-icon" style="background: #f06724;">
                <i class="fas fa-list"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $stats['total'] ?? 0; ?></span>
                <span class="stat-label">Total Assignments</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #f06724;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $stats['pending'] ?? 0; ?></span>
                <span class="stat-label">Pending</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #f06724;">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $stats['graded'] ?? 0; ?></span>
                <span class="stat-label">Graded</span>
            </div>
        </div>
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
        <a href="<?php echo BASE_URL; ?>/external/homework" class="filter-tab <?php echo empty($currentStatus) ? 'active' : ''; ?>">
            <i class="fas fa-list"></i> All
        </a>
        <a href="<?php echo BASE_URL; ?>/external/homework?status=pending" class="filter-tab <?php echo $currentStatus === 'pending' ? 'active' : ''; ?>">
            <i class="fas fa-clock"></i> Pending
        </a>
        <a href="<?php echo BASE_URL; ?>/external/homework?status=graded" class="filter-tab <?php echo $currentStatus === 'graded' ? 'active' : ''; ?>">
            <i class="fas fa-star"></i> Graded
        </a>
    </div>

    <?php if (empty($homeworks)): ?>
        <div class="empty-state">
            <i class="fas fa-tasks"></i>
            <h3>No Homework Assigned</h3>
            <p>You don't have any homework at the moment. Check back later!</p>
        </div>
    <?php else: ?>
        <div class="homework-grid">
            <?php foreach ($homeworks as $homework): 
                $isLate = !$homework['submission_id'] && strtotime($homework['due_date']) < time();
                $submissionStatus = $homework['submission_status'] ?? 'pending';
                $statusClass = $submissionStatus === 'graded' ? 'graded' : ($submissionStatus === 'submitted' ? 'submitted' : ($isLate ? 'late' : 'pending'));
                $isExpired = strtotime($homework['due_date']) < time();
            ?>
                <div class="homework-card status-<?php echo $statusClass; ?>">
                    <div class="homework-header">
                        <div class="homework-title">
                            <i class="fas fa-file-alt"></i>
                            <?php echo htmlspecialchars($homework['title']); ?>
                        </div>
                        <div class="homework-status">
                            <?php if ($submissionStatus === 'graded'): ?>
                                <span class="badge graded">Graded</span>
                            <?php elseif ($submissionStatus === 'submitted'): ?>
                                <span class="badge submitted">Submitted</span>
                            <?php elseif ($isLate): ?>
                                <span class="badge late">Late</span>
                            <?php else: ?>
                                <span class="badge pending">Pending</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="homework-meta">
                        <span><i class="fas fa-book"></i> <?php echo htmlspecialchars($homework['subject_name']); ?></span>
                        <span><i class="fas fa-calendar-alt"></i> Due: <?php echo date('M d, Y h:i A', strtotime($homework['due_date'])); ?></span>
                    </div>
                    
                    <p class="homework-description">
                        <?php echo substr(htmlspecialchars($homework['description'] ?? ''), 0, 120); ?>
                    </p>
                    
                    <?php if ($submissionStatus === 'graded' && isset($homework['grade'])): ?>
                        <div class="grade-info">
                            <span class="grade-label">Grade:</span>
                            <span class="grade-value"><?php echo $homework['grade']; ?>%</span>
                        </div>
                    <?php endif; ?>
                    
                    <a href="<?php echo BASE_URL; ?>/external/homework/view/<?php echo $homework['id']; ?>" class="btn-view">
                        <?php echo $submissionStatus === 'graded' ? 'View Feedback' : ($submissionStatus === 'submitted' ? 'View Submission' : 'View / Submit Homework'); ?>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>