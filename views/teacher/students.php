<?php
// File: /views/teacher/students.php
$pageTitle = 'Students | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$students = $students ?? [];
$classes = $classes ?? [];
$selectedClass = $_GET['class_id'] ?? '';
$search = $_GET['search'] ?? '';
?>

<div class="students-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-users"></i>
                My Students
            </h1>
            <p class="page-subtitle">View and track your students' progress</p>
        </div>
        
        <!-- Quick Stats -->
        <?php if (!empty($students)): ?>
        <div class="quick-stats">
            <div class="quick-stat-card">
                <div class="quick-stat-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="quick-stat-info">
                    <span class="quick-stat-label">Total Students</span>
                    <span class="quick-stat-value"><?php echo count($students); ?></span>
                </div>
            </div>
            <div class="quick-stat-card">
                <div class="quick-stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="quick-stat-info">
                    <span class="quick-stat-label">Avg. Performance</span>
                    <span class="quick-stat-value">
                        <?php 
                        $avgScores = array_filter(array_column($students, 'avg_score'));
                        echo !empty($avgScores) ? round(array_sum($avgScores) / count($avgScores), 1) . '%' : '0%';
                        ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endif; ?>
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

    <!-- Filters Section -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search students by name or email..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                >
            </div>

            <div class="filter-group">
                <select name="class_id">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" 
                            <?php echo ($selectedClass == $class['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-filter">
                <i class="fas fa-filter"></i> Filter
            </button>
            
            <?php if ($search || $selectedClass): ?>
                <a href="<?php echo BASE_URL; ?>/teacher/students" class="btn-clear">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Students Grid -->
    <?php if (empty($students)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-users-slash"></i>
            </div>
            <h3>No Students Found</h3>
            <p>You don't have any students assigned to your classes yet.</p>
            <p class="empty-hint">Students will appear here once they register and select your class.</p>
        </div>
    <?php else: ?>
        <div class="students-grid">
            <?php foreach ($students as $student): 
                $quizzesTaken = $student['quizzes_taken'] ?? 0;
                $avgScore = $student['avg_score'] ?? 0;
                $lessonsViewed = $student['lessons_viewed'] ?? 0;
                $scoreClass = $avgScore >= 70 ? 'high' : ($avgScore >= 50 ? 'medium' : 'low');
                $performanceLevel = $avgScore >= 80 ? 'Excellent' : ($avgScore >= 60 ? 'Good' : ($avgScore >= 40 ? 'Average' : 'Needs Improvement'));
            ?>
                <div class="student-card">
                    <div class="student-avatar">
                        <?php if (!empty($student['profile_photo'])): ?>
                            <img src="<?php echo BASE_URL; ?>/<?php echo $student['profile_photo']; ?>" alt="<?php echo $student['first_name']; ?>">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <?php 
                                $initial = strtoupper(substr($student['first_name'] ?? 'S', 0, 1));
                                echo $initial;
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($avgScore >= 80): ?>
                            <div class="student-badge top-performer" title="Top Performer">
                                <i class="fas fa-crown"></i>
                            </div>
                        <?php elseif ($avgScore >= 60): ?>
                            <div class="student-badge good-performer" title="Good Performer">
                                <i class="fas fa-star"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="student-info">
                        <h3 class="student-name">
                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                        </h3>
                        
                        <div class="student-role">
                            <?php if (($student['role'] ?? '') == 'learner'): ?>
                                <span class="role-badge learner">
                                    <i class="fas fa-user-graduate"></i> Student
                                </span>
                            <?php else: ?>
                                <span class="role-badge external">
                                    <i class="fas fa-globe"></i> External
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <p class="student-class">
                            <i class="fas fa-graduation-cap"></i>
                            <?php echo $student['class_name'] ?? 'No Class'; ?>
                        </p>
                        <p class="student-email">
                            <i class="fas fa-envelope"></i>
                            <?php echo htmlspecialchars($student['email']); ?>
                        </p>
                        
                        <!-- Performance Bar -->
                        <div class="performance-bar">
                            <div class="performance-label">
                                <span>Performance: <?php echo $performanceLevel; ?></span>
                                <span><?php echo number_format($avgScore, 1); ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $avgScore; ?>%; background: <?php echo $avgScore >= 70 ? '#10B981' : ($avgScore >= 50 ? '#F59E0B' : '#EF4444'); ?>"></div>
                            </div>
                        </div>
                        
                        <div class="student-stats">
                            <div class="stat">
                                <span class="stat-label">
                                    <i class="fas fa-pencil-alt"></i> Quizzes Taken
                                </span>
                                <span class="stat-value"><?php echo number_format($quizzesTaken); ?></span>
                            </div>
                            <div class="stat">
                                <span class="stat-label">
                                    <i class="fas fa-chart-line"></i> Avg. Score
                                </span>
                                <span class="stat-value <?php echo $scoreClass; ?>">
                                    <?php echo number_format($avgScore, 1); ?>%
                                </span>
                            </div>
                            <div class="stat">
                                <span class="stat-label">
                                    <i class="fas fa-book-open"></i> Lessons
                                </span>
                                <span class="stat-value">N/A</span>
                            </div>
                        </div>
                        
                        <div class="student-actions">
                            <a href="<?php echo BASE_URL; ?>/teacher/students/progress/<?php echo $student['id']; ?>" class="btn-view">
                                <i class="fas fa-chart-line"></i> View Progress
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.students-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
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

/* Quick Stats */
.quick-stats {
    display: flex;
    gap: 15px;
}

.quick-stat-card {
    background: white;
    border-radius: 12px;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.quick-stat-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #f06724);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.quick-stat-info {
    display: flex;
    flex-direction: column;
}

.quick-stat-label {
    font-size: 0.7rem;
    color: black;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.quick-stat-value {
    font-size: 1.3rem;
    font-weight: 700;
    color: black;
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

/* Filters Section */
.filters-section {
    background: white;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.filters-form {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
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
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: #f06724;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.filter-group select {
    padding: 12px 20px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 0.95rem;
    background: white;
    min-width: 150px;
    cursor: pointer;
}

.filter-group select:focus {
    outline: none;
    border-color: #f06724;
}

.btn-filter {
    padding: 12px 30px;
    background: #7f2677;
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-filter:hover {
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
}

/* Students Grid */
.students-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 25px;
}

.student-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.student-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(139, 92, 246, 0.15);
}

.student-avatar {
    height: 120px;
    background: linear-gradient(135deg,#f06724);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.student-avatar img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 4px solid white;
    object-fit: cover;
    position: absolute;
    bottom: -40px;
}

.avatar-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 700;
    color: #7f2677;
    border: 4px solid white;
    position: absolute;
    bottom: -40px;
}

.student-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.student-badge.top-performer {
    background: linear-gradient(135deg, #FFD700, #FFA500);
}

.student-badge.good-performer {
    background: linear-gradient(135deg, #C0C0C0, #A0A0A0);
}

.student-info {
    padding: 50px 25px 25px;
    text-align: center;
}

.student-name {
    color: #1E293B;
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.student-role {
    margin-bottom: 10px;
}

.role-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.75rem;
    font-weight: 600;
}

.role-badge.learner {
    background: #F0FDF4;
    color: #166534;
}

.role-badge.external {
    background: #EFF6FF;
    color: #1E40AF;
}

.student-class,
.student-email {
    color: black;
    font-size: 0.9rem;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.student-class i,
.student-email i {
    color: #f06724;
}

/* Performance Bar */
.performance-bar {
    margin: 15px 0;
    padding: 10px;
    background: #F8FAFC;
    border-radius: 8px;
}

.performance-label {
    display: flex;
    justify-content: space-between;
    font-size: 0.7rem;
    margin-bottom: 5px;
    color: black;
}

.progress-bar {
    height: 6px;
    background: #E2E8F0;
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.3s ease;
}

/* Student Stats */
.student-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin: 20px 0;
    padding: 15px 0;
    border-top: 1px solid #E2E8F0;
    border-bottom: 1px solid #E2E8F0;
}

.stat {
    text-align: center;
}

.stat-label {
    display: block;
    color: black;
    font-size: 0.7rem;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-label i {
    color: #f06724;
    margin-right: 3px;
}

.stat-value {
    display: block;
    font-size: 1.2rem;
    font-weight: 700;
    color: black;
}

.stat-value.high {
    color: #10B981;
}

.stat-value.medium {
    color: #F59E0B;
}

.stat-value.low {
    color: #EF4444;
}

.student-actions {
    display: flex;
    justify-content: center;
}

.btn-view {
    background: #7f2677;
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-view:hover {
    background: #f06724;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(139, 92, 246, 0.3);
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
    font-size: 1.3rem;
    margin-bottom: 10px;
}

.empty-state p {
    color: black;
    margin-bottom: 5px;
}

.empty-hint {
    font-size: 0.85rem;
    color: #94A3B8;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
    }
    
    .quick-stats {
        width: 100%;
    }
    
    .quick-stat-card {
        flex: 1;
    }
    
    .filters-form {
        flex-direction: column;
    }
    
    .search-box,
    .filter-group select,
    .btn-filter,
    .btn-clear {
        width: 100%;
    }
    
    .students-grid {
        grid-template-columns: 1fr;
    }
    
    .student-stats {
        grid-template-columns: 1fr;
        gap: 12px;
    }
}

</style>

<script>
// Live search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    const classSelect = document.querySelector('select[name="class_id"]');
    const filterForm = document.querySelector('.filters-form');
    
    let searchTimeout;
    
    // Live search with debounce
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterForm.submit();
            }, 500);
        });
    }
    
    // Auto-submit on class change
    if (classSelect) {
        classSelect.addEventListener('change', function() {
            filterForm.submit();
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>