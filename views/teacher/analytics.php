<?php
// File: /views/teacher/analytics.php
$pageTitle = 'Analytics | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$stats = $stats ?? [];
$quizPerformance = $quizPerformance ?? [];
$lessonViews = $lessonViews ?? [];
?>

<div class="analytics-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-chart-bar"></i>
                Analytics Dashboard
            </h1>
            <p class="page-subtitle">Track your teaching performance and student engagement</p>
        </div>
        <div class="date-range">
            <select id="timeRange" class="time-range-select" onchange="refreshAnalytics()">
                <option value="7">Last 7 days</option>
                <option value="30" selected>Last 30 days</option>
                <option value="90">Last 90 days</option>
                <option value="365">This year</option>
            </select>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f06724);">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Lessons</span>
                <span class="stat-value"><?php echo number_format($stats['total_lessons'] ?? 0); ?></span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f06724);">
                <i class="fas fa-pencil-alt"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Quizzes</span>
                <span class="stat-value"><?php echo number_format($stats['total_quizzes'] ?? 0); ?></span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f06724);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Students</span>
                <span class="stat-value"><?php echo number_format($stats['total_students'] ?? 0); ?></span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f06724);">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Avg. Score</span>
                <span class="stat-value"><?php echo $stats['avg_score'] ?? 0; ?>%</span>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
        <!-- Quiz Performance Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-line"></i> Quiz Performance</h3>
                <select class="chart-filter" onchange="filterQuizChart(this.value)">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 90 days</option>
                </select>
            </div>
            <div class="chart-body">
                <canvas id="quizPerformanceChart"></canvas>
            </div>
        </div>

        <!-- Lesson Views Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-eye"></i> Lesson Views</h3>
                <select class="chart-filter" onchange="filterLessonChart(this.value)">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 90 days</option>
                </select>
            </div>
            <div class="chart-body">
                <canvas id="lessonViewsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Quiz Performance Table -->
    <div class="performance-section">
        <h2 class="section-title">Quiz Performance</h2>
        <div class="table-responsive">
            <table class="performance-table">
                <thead>
                    <tr>
                        <th>Quiz Title</th>
                        <th>Attempts</th>
                        <th>Students</th>
                        <th>Avg. Score</th>
                        <th>Pass Rate</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($quizPerformance)): ?>
                        <tr>
                            <td colspan="6" class="empty-message">No quiz data available</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($quizPerformance as $quiz): ?>
                        <tr>
                            <td class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></td>
                            <td class="number-cell"><?php echo number_format($quiz['total_attempts']); ?></td>
                            <td class="number-cell"><?php echo number_format($quiz['unique_students']); ?></td>
                            <td class="number-cell"><?php echo round($quiz['avg_score'], 1); ?>%</td>
                            <td>
                                <?php 
                                $passRate = $quiz['total_attempts'] > 0 ? round(($quiz['passed_count'] / $quiz['total_attempts']) * 100, 1) : 0;
                                $badgeClass = $passRate >= 70 ? 'success' : ($passRate >= 50 ? 'warning' : 'danger');
                                ?>
                                <span class="badge badge-<?php echo $badgeClass; ?>">
                                    <?php echo $passRate; ?>%
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/teacher/quizzes/results/<?php echo $quiz['id']; ?>" class="btn-view">
                                    <i class="fas fa-chart-bar"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Popular Lessons -->
    <div class="lessons-section">
        <h2 class="section-title">Most Viewed Lessons</h2>
        <div class="lessons-grid">
            <?php if (empty($lessonViews)): ?>
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <p>No lesson views data available</p>
                </div>
            <?php else: ?>
                <?php foreach ($lessonViews as $lesson): ?>
                <div class="lesson-stat-card">
                    <h4><?php echo htmlspecialchars($lesson['title']); ?></h4>
                    <div class="lesson-stat-meta">
                        <span><i class="fas fa-eye"></i> <?php echo number_format($lesson['views']); ?> views</span>
                        <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($lesson['created_at'])); ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo min(100, ($lesson['views'] / 100) * 100); ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

// Chart.js
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let quizChart = null;
let lessonChart = null;
let currentQuizDays = 30;
let currentLessonDays = 30;

// Initialize charts with real data
document.addEventListener('DOMContentLoaded', function() {
    loadQuizChart(30);
    loadLessonChart(30);
});

function loadQuizChart(days) {
    currentQuizDays = days;
    const ctx = document.getElementById('quizPerformanceChart').getContext('2d');
    
    // Show loading state
    ctx.canvas.style.opacity = '0.5';
    
    // Destroy existing chart if it exists
    if (quizChart) {
        quizChart.destroy();
    }
    
    // Fetch real data from API
    fetch(`<?php echo BASE_URL; ?>/teacher/api/quiz-performance?days=${days}`)
        .then(response => response.json())
        .then(data => {
            quizChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Average Score',
                        data: data.scores,
                        borderColor: '#7f2677',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    }, {
                        label: 'Attempts',
                        data: data.attempts,
                        borderColor: '#F97316',
                        backgroundColor: 'rgba(249, 115, 22, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 6,
                                color: 'black'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'black',
                            titleColor: '#F1F5F9',
                            bodyColor: '#F1F5F9',
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    if (context.dataset.label === 'Average Score') {
                                        return context.raw + '%';
                                    }
                                    return context.raw + ' attempts';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Score (%)',
                                color: 'black'
                            },
                            grid: {
                                color: '#E2E8F0'
                            },
                            ticks: {
                                color: 'black'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Attempts',
                                color: 'black'
                            },
                            grid: {
                                drawOnChartArea: false
                            },
                            ticks: {
                                color: 'black',
                                stepSize: 1,
                                callback: function(value) {
                                    return value;
                                }
                            }
                        },
                        x: {
                            ticks: {
                                color: 'black',
                                maxRotation: 45,
                                minRotation: 45
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
            
            ctx.canvas.style.opacity = '1';
        })
        .catch(error => {
            console.error('Error loading quiz data:', error);
            ctx.canvas.style.opacity = '1';
        });
}

function loadLessonChart(days) {
    currentLessonDays = days;
    const ctx = document.getElementById('lessonViewsChart').getContext('2d');
    
    // Show loading state
    ctx.canvas.style.opacity = '0.5';
    
    // Destroy existing chart if it exists
    if (lessonChart) {
        lessonChart.destroy();
    }
    
    // Fetch real data from API
    fetch(`<?php echo BASE_URL; ?>/teacher/api/lesson-views?days=${days}`)
        .then(response => response.json())
        .then(data => {
            lessonChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Views',
                        data: data.views,
                        backgroundColor: '#F97316',
                        borderRadius: 8,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'black',
                            titleColor: '#F1F5F9',
                            bodyColor: '#F1F5F9',
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return context.raw + ' views';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#E2E8F0'
                            },
                            ticks: {
                                color: 'black',
                                stepSize: 1,
                                callback: function(value) {
                                    return value;
                                }
                            }
                        },
                        x: {
                            ticks: {
                                color: 'black',
                                maxRotation: 45,
                                minRotation: 45
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
            
            ctx.canvas.style.opacity = '1';
        })
        .catch(error => {
            console.error('Error loading lesson data:', error);
            ctx.canvas.style.opacity = '1';
        });
}

function filterQuizChart(days) {
    loadQuizChart(days);
}

function filterLessonChart(days) {
    loadLessonChart(days);
}

function refreshAnalytics() {
    const range = document.getElementById('timeRange').value;
    window.location.href = `<?php echo BASE_URL; ?>/teacher/analytics?range=${range}`;
}

// Handle window resize
window.addEventListener('resize', function() {
    if (quizChart) {
        quizChart.resize();
    }
    if (lessonChart) {
        lessonChart.resize();
    }
});
</script>

<style>
.analytics-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
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

.time-range-select {
    padding: 10px 20px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 0.95rem;
    background: white;
    cursor: pointer;
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
}

.stat-content {
    flex: 1;
}

.stat-label {
    display: block;
    color: black;
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.stat-value {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: black;
    line-height: 1.2;
}

/* Charts Row */
.charts-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.chart-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
}

.chart-header h3 {
    color: black;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chart-header h3 i {
    color: #f06724;
}

.chart-filter {
    padding: 6px 12px;
    border: 2px solid #E2E8F0;
    border-radius: 8px;
    font-size: 0.85rem;
    background: white;
    cursor: pointer;
}

.chart-body {
    height: 300px;
    position: relative;
}

/* Performance Section */
.performance-section {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 40px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.section-title {
    color: black;
    font-size: 1.3rem;
    margin-bottom: 20px;
}

.table-responsive {
    overflow-x: auto;
}

.performance-table {
    width: 100%;
    border-collapse: collapse;
}

.performance-table th {
    background: #F8FAFC;
    color: black;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 15px;
    text-align: left;
    border-bottom: 2px solid #E2E8F0;
}

.performance-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #F1F5F9;
    color: black;
}

.performance-table tr:hover td {
    background: #F8FAFC;
}

.quiz-title {
    font-weight: 600;
    color: black;
}

.number-cell {
    font-weight: 600;
    color: #f06724;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
}

.badge-success {
    background: #F0FDF4;
    color: #166534;
}

.badge-warning {
    background: #FEF3C7;
    color: #92400E;
}

.badge-danger {
    background: #FEF2F2;
    color: #B91C1C;
}

.btn-view {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    background: #7f2677;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.85rem;
    transition: all 0.3s ease;
}

.btn-view:hover {
    background: #f06724;
}

/* Lessons Section */
.lessons-section {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.lessons-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.lesson-stat-card {
    background: #F8FAFC;
    border-radius: 12px;
    padding: 20px;
}

.lesson-stat-card h4 {
    color: black;
    font-size: 1rem;
    margin-bottom: 10px;
}

.lesson-stat-meta {
    display: flex;
    justify-content: space-between;
    color: black;
    font-size: 0.85rem;
    margin-bottom: 15px;
}

.lesson-stat-meta i {
    color: #f06724;
}

.progress-bar {
    width: 100%;
    height: 6px;
    background: #E2E8F0;
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #f06724, #F97316);
    border-radius: 3px;
    transition: width 0.3s ease;
}

/* Empty States */
.empty-message {
    text-align: center;
    padding: 40px;
    color: black;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    grid-column: 1 / -1;
}

.empty-state i {
    font-size: 3rem;
    color: #CBD5E1;
    margin-bottom: 15px;
}

.empty-state p {
    color: black;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .charts-row {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .lessons-grid {
        grid-template-columns: 1fr;
    }
}

</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>