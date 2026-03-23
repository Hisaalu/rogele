<?php
// File: /views/teacher/dashboard.php
$pageTitle = 'Teacher Dashboard - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

// Get statistics from controller
$totalLessons = $totalLessons ?? 0;
$totalQuizzes = $totalQuizzes ?? 0;
$recentLessons = $recentLessons ?? [];
$recentQuizzes = $recentQuizzes ?? [];
$classPerformance = $classPerformance ?? [
    'total_students' => 0,
    'avg_score' => 0,
    'completion_rate' => 0,
    'active_classes' => 0
];
?>

<div class="teacher-dashboard">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-content">
            <h1 class="welcome-title">
                Welcome back, <span class="teacher-name">
                    <?php 
                        // Get full name from session
                        $fullName = $_SESSION['user_name'] ?? 'Teacher';
                        // Extract first name (everything before first space)
                        $firstName = explode(' ', trim($fullName))[0];
                        echo htmlspecialchars($firstName); 
                    ?>
                </span>! 👋
            </h1>
            <p class="welcome-subtitle">Here's what's happening with your classes today</p>
        </div>
        <div class="date-display">
            <i class="fas fa-calendar-alt"></i>
            <span><?php echo date('l, F j, Y'); ?></span>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #8B5CF6, #7C3AED);">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Lessons</span>
                <span class="stat-value"><?php echo number_format($totalLessons); ?></span>
                <span class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i> 3 new this month
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #F97316, #EA580C);">
                <i class="fas fa-pencil-alt"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Quizzes</span>
                <span class="stat-value"><?php echo number_format($totalQuizzes); ?></span>
                <span class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i> 2 new this month
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #10B981, #059669);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Students</span>
                <span class="stat-value"><?php echo number_format($classPerformance['total_students']); ?></span>
                <span class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i> +12% vs last month
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #EC4899, #DB2777);">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Avg. Score</span>
                <span class="stat-value"><?php echo $classPerformance['avg_score']; ?>%</span>
                <span class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i> +5% vs last month
                </span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2 class="section-title">Quick Actions</h2>
        <div class="actions-grid">
            <a href="<?php echo BASE_URL; ?>/teacher/lessons/create" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="action-content">
                    <h3>Create New Lesson</h3>
                    <p>Add new learning materials for your students</p>
                </div>
                <i class="fas fa-arrow-right"></i>
            </a>

            <a href="<?php echo BASE_URL; ?>/teacher/quizzes/create" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="action-content">
                    <h3>Create New Quiz</h3>
                    <p>Design a new quiz for your class</p>
                </div>
                <i class="fas fa-arrow-right"></i>
            </a>

            <a href="<?php echo BASE_URL; ?>/teacher/students" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="action-content">
                    <h3>View Students</h3>
                    <p>See all your students and their progress</p>
                </div>
                <i class="fas fa-arrow-right"></i>
            </a>

            <a href="<?php echo BASE_URL; ?>/teacher/analytics" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="action-content">
                    <h3>Analytics</h3>
                    <p>View detailed performance insights</p>
                </div>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Recent Activity & Upcoming -->
    <div class="activity-section">
        <!-- Recent Lessons -->
        <div class="recent-card">
            <div class="card-header">
                <h3><i class="fas fa-book-open"></i> Recent Lessons</h3>
                <a href="<?php echo BASE_URL; ?>/teacher/lessons" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="card-body">
                <?php if (empty($recentLessons)): ?>
                    <div class="empty-state">
                        <i class="fas fa-book"></i>
                        <p>No lessons created yet</p>
                        <a href="<?php echo BASE_URL; ?>/teacher/lessons/create" class="btn-create">Create Your First Lesson</a>
                    </div>
                <?php else: ?>
                    <?php 
                    // Show only the last 5 lessons
                    $displayLessons = array_slice($recentLessons, 0, 5);
                    foreach ($displayLessons as $lesson): 
                    ?>
                    <div class="activity-item">
                        <div class="activity-icon" style="background: rgba(139, 92, 246, 0.1);">
                            <i class="fas fa-book" style="color: #8B5CF6;"></i>
                        </div>
                        <div class="activity-content">
                            <h4><?php echo htmlspecialchars($lesson['title']); ?></h4>
                            <p class="activity-meta">
                                <span><i class="fas fa-clock"></i> <?php echo date('M d, Y', strtotime($lesson['created_at'])); ?></span>
                                <span><i class="fas fa-eye"></i> <?php echo $lesson['views'] ?? 0; ?> views</span>
                            </p>
                        </div>
                        <div class="activity-status <?php echo $lesson['is_published'] ? 'published' : 'draft'; ?>">
                            <?php echo $lesson['is_published'] ? 'Published' : 'Draft'; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Quizzes -->
        <div class="recent-card">
            <div class="card-header">
                <h3><i class="fas fa-pencil-alt"></i> Recent Quizzes</h3>
                <a href="<?php echo BASE_URL; ?>/teacher/quizzes" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="card-body">
                <?php if (empty($recentQuizzes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-pencil-alt"></i>
                        <p>No quizzes created yet</p>
                        <a href="<?php echo BASE_URL; ?>/teacher/quizzes/create" class="btn-create">Create Your First Quiz</a>
                    </div>
                <?php else: ?>
                    <?php 
                    // Show only the last 5 quizzes
                    $displayQuizzes = array_slice($recentQuizzes, 0, 5);
                    foreach ($displayQuizzes as $quiz): 
                    ?>
                    <div class="activity-item">
                        <div class="activity-icon" style="background: rgba(249, 115, 22, 0.1);">
                            <i class="fas fa-pencil-alt" style="color: #F97316;"></i>
                        </div>
                        <div class="activity-content">
                            <h4><?php echo htmlspecialchars($quiz['title']); ?></h4>
                            <p class="activity-meta">
                                <span><i class="fas fa-clock"></i> <?php echo date('M d, Y', strtotime($quiz['created_at'])); ?></span>
                                <span><i class="fas fa-users"></i> <?php echo $quiz['attempt_count'] ?? 0; ?> attempts</span>
                            </p>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/teacher/quizzes/results/<?php echo $quiz['id']; ?>" class="btn-view">View Results</a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.teacher-dashboard {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

/* Welcome Banner */
.welcome-banner {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
    color: white;
    box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);
}

.welcome-title {
    font-size: clamp(1.5rem, 4vw, 2rem);
    font-weight: 700;
    margin-bottom: 5px;
}

.teacher-name {
    background: rgba(255, 255, 255, 0.2);
    padding: 5px 15px;
    border-radius: 50px;
    display: inline-block;
}

.welcome-subtitle {
    font-size: 1rem;
    opacity: 0.9;
}

.date-display {
    background: rgba(255, 255, 255, 0.2);
    padding: 12px 25px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
    backdrop-filter: blur(10px);
}

.date-display i {
    font-size: 1.2rem;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(139, 92, 246, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: white;
    flex-shrink: 0;
}

.stat-content {
    flex: 1;
    min-width: 0;
}

.stat-label {
    display: block;
    color: #64748B;
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.stat-value {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: #1E293B;
    line-height: 1.2;
    margin-bottom: 5px;
}

.stat-trend {
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 4px;
}

.stat-trend.positive {
    color: #10B981;
}

/* Section Title */
.section-title {
    color: #1E293B;
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 20px;
}

/* Quick Actions */
.quick-actions {
    margin-bottom: 40px;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.action-card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.action-card:hover {
    transform: translateX(5px);
    border-color: #8B5CF6;
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.15);
}

.action-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(249, 115, 22, 0.1));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #8B5CF6;
}

.action-content {
    flex: 1;
}

.action-content h3 {
    color: #1E293B;
    font-size: 1rem;
    margin-bottom: 3px;
}

.action-content p {
    color: #64748B;
    font-size: 0.8rem;
}

.action-card i:last-child {
    color: #8B5CF6;
    opacity: 0;
    transition: all 0.3s ease;
}

.action-card:hover i:last-child {
    opacity: 1;
    transform: translateX(5px);
}

/* Performance Section */
.performance-section {
    margin-bottom: 40px;
}

.performance-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 25px;
}

.performance-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.performance-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
}

.performance-header h3 {
    color: #1E293B;
    font-size: 1.1rem;
    font-weight: 600;
}

.performance-filter {
    padding: 8px 15px;
    border: 2px solid #E2E8F0;
    border-radius: 30px;
    font-size: 0.85rem;
    color: #1E293B;
    background: white;
    cursor: pointer;
}

.performance-body {
    height: 250px;
    position: relative;
}

/* Activity Section */
.activity-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 25px;
}

.recent-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.card-header {
    padding: 20px 25px;
    border-bottom: 2px solid #F1F5F9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.card-header h3 {
    color: #1E293B;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-header h3 i {
    color: #8B5CF6;
}

.view-all {
    color: #8B5CF6;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: color 0.3s ease;
}

.view-all:hover {
    color: #F97316;
}

.card-body {
    padding: 20px 25px;
}

/* Activity Items */
.activity-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border-radius: 12px;
    transition: background 0.3s ease;
    margin-bottom: 10px;
}

.activity-item:last-child {
    margin-bottom: 0;
}

.activity-item:hover {
    background: #F8FAFC;
}

.activity-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
    min-width: 0;
}

.activity-content h4 {
    color: #1E293B;
    font-size: 1rem;
    margin-bottom: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.activity-meta {
    display: flex;
    gap: 15px;
    font-size: 0.8rem;
    color: #64748B;
    flex-wrap: wrap;
}

.activity-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.activity-status {
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.activity-status.published {
    background: #F0FDF4;
    color: #166534;
}

.activity-status.draft {
    background: #F1F5F9;
    color: #64748B;
}

.btn-view {
    padding: 6px 15px;
    background: #8B5CF6;
    color: white;
    text-decoration: none;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.btn-view:hover {
    background: #7C3AED;
    transform: translateY(-2px);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-state i {
    font-size: 3rem;
    color: #CBD5E1;
    margin-bottom: 15px;
}

.empty-state p {
    color: #64748B;
    margin-bottom: 20px;
}

.btn-create {
    display: inline-block;
    padding: 10px 25px;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
    text-decoration: none;
    border-radius: 30px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.btn-create:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
    .welcome-banner {
        flex-direction: column;
        text-align: center;
    }
    
    .date-display {
        width: 100%;
        justify-content: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
    
    .performance-grid {
        grid-template-columns: 1fr;
    }
    
    .activity-section {
        grid-template-columns: 1fr;
    }
    
    .performance-body {
        height: 200px;
    }
}

@media (max-width: 480px) {
    .teacher-dashboard {
        padding: 20px 15px;
    }
    
    .stat-card {
        padding: 20px 15px;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
    
    .activity-item {
        flex-wrap: wrap;
    }
    
    .btn-view {
        width: 100%;
        text-align: center;
    }
    
    .activity-meta {
        flex-direction: column;
        gap: 5px;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .stat-card,
    .action-card,
    .performance-card,
    .recent-card {
        background: #1E293B;
    }
    
    .stat-value,
    .action-content h3,
    .performance-header h3,
    .card-header h3,
    .activity-content h4 {
        color: #F1F5F9;
    }
    
    .stat-label,
    .action-content p,
    .activity-meta {
        color: #94A3B8;
    }
    
    .card-header {
        border-bottom-color: #334155;
    }
    
    .activity-item:hover {
        background: #334155;
    }
    
    .performance-filter {
        background: #0F172A;
        border-color: #334155;
        color: #F1F5F9;
    }
    
    .empty-state i {
        color: #475569;
    }
}
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sample data - replace with actual data from controller
    const scoresCtx = document.getElementById('scoresChart').getContext('2d');
    new Chart(scoresCtx, {
        type: 'bar',
        data: {
            labels: ['Primary 4', 'Primary 5', 'Primary 6', 'Primary 7'],
            datasets: [{
                label: 'Average Score (%)',
                data: [78, 82, 75, 88],
                backgroundColor: [
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(249, 115, 22, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(236, 72, 153, 0.8)'
                ],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        color: '#E2E8F0'
                    }
                }
            }
        }
    });

    const completionCtx = document.getElementById('completionChart').getContext('2d');
    new Chart(completionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'In Progress', 'Not Started'],
            datasets: [{
                data: [65, 25, 10],
                backgroundColor: [
                    '#10B981',
                    '#F97316',
                    '#94A3B8'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#64748B',
                        font: {
                            size: 11
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>