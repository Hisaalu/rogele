<?php
// File: /views/teacher/quiz_results.php
$pageTitle = 'Quiz Results - Teacher - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

$quiz = $quiz ?? [];
$results = $results ?? [];
$stats = $stats ?? [];
?>

<div class="results-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <a href="<?php echo BASE_URL; ?>/teacher/quizzes" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Quizzes
            </a>
            <h1 class="page-title">
                <i class="fas fa-chart-bar"></i>
                Quiz Results: <?php echo htmlspecialchars($quiz['title'] ?? ''); ?>
            </h1>
            <p class="page-subtitle">View student performance and statistics</p>
        </div>
    </div>

    <!-- Stats Overview -->
    <?php if (!empty($stats)): ?>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #8B5CF6, #7C3AED);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Attempts</span>
                <span class="stat-value"><?php echo $stats['overall']['total_attempts'] ?? 0; ?></span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #10B981, #059669);">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Unique Students</span>
                <span class="stat-value"><?php echo $stats['overall']['unique_students'] ?? 0; ?></span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #F97316, #EA580C);">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Average Score</span>
                <span class="stat-value"><?php echo round($stats['overall']['average_score'] ?? 0, 1); ?>%</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #EC4899, #DB2777);">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Passing Rate</span>
                <span class="stat-value"><?php echo round($stats['overall']['pass_rate'] ?? 0, 1); ?>%</span>
            </div>
        </div>
    </div>

    <!-- Score Distribution -->
    <?php if (!empty($stats['distribution'])): ?>
    <div class="chart-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie"></i> Score Distribution</h3>
        </div>
        <div class="card-body">
            <canvas id="distributionChart"></canvas>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Results Table -->
    <div class="results-card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Student Attempts</h3>
            <span class="result-count"><?php echo count($results); ?> attempts</span>
        </div>

        <?php if (empty($results)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Results Yet</h3>
                <p>No students have taken this quiz yet.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Score</th>
                            <th>Result</th>
                            <th>Time Taken</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): ?>
                        <tr>
                            <td class="student-cell">
                                <strong><?php echo htmlspecialchars($result['first_name'] . ' ' . $result['last_name']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($result['email']); ?></td>
                            <td class="score-cell <?php echo $result['score'] >= ($quiz['passing_score'] ?? 50) ? 'passed' : 'failed'; ?>">
                                <?php echo $result['score']; ?>%
                            </td>
                            <td>
                                <?php if ($result['score'] >= ($quiz['passing_score'] ?? 50)): ?>
                                    <span class="badge passed">Passed</span>
                                <?php else: ?>
                                    <span class="badge failed">Failed</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $minutes = floor($result['time_taken'] / 60);
                                $seconds = $result['time_taken'] % 60;
                                echo $minutes . 'm ' . $seconds . 's';
                                ?>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($result['completed_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.results-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 20px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #64748B;
    text-decoration: none;
    font-size: 0.95rem;
    margin-bottom: 15px;
    transition: color 0.3s ease;
}

.back-link:hover {
    color: #8B5CF6;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-subtitle {
    color: #64748B;
    font-size: 1rem;
    margin-bottom: 30px;
}

/* Stats Grid */
.stats-grid {
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
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
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

.stat-content {
    flex: 1;
}

.stat-label {
    display: block;
    color: #64748B;
    font-size: 0.8rem;
    margin-bottom: 3px;
}

.stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1E293B;
    line-height: 1.2;
}

/* Chart Card */
.chart-card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #F1F5F9;
}

.card-header h3 {
    color: #1E293B;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-header h3 i {
    color: #8B5CF6;
}

.result-count {
    background: #F1F5F9;
    padding: 5px 12px;
    border-radius: 30px;
    font-size: 0.85rem;
    color: #1E293B;
    font-weight: 600;
}

.card-body {
    height: 300px;
    position: relative;
}

/* Results Card */
.results-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.table-responsive {
    overflow-x: auto;
}

.results-table {
    width: 100%;
    border-collapse: collapse;
}

.results-table th {
    background: #F8FAFC;
    color: #1E293B;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 16px 20px;
    text-align: left;
    border-bottom: 2px solid #E2E8F0;
}

.results-table td {
    padding: 14px 20px;
    border-bottom: 1px solid #F1F5F9;
    color: #1E293B;
}

.results-table tr:hover td {
    background: #F8FAFC;
}

.student-cell strong {
    color: #1E293B;
}

.score-cell {
    font-weight: 600;
}

.score-cell.passed {
    color: #10B981;
}

.score-cell.failed {
    color: #EF4444;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
}

.badge.passed {
    background: #F0FDF4;
    color: #166534;
}

.badge.failed {
    background: #FEF2F2;
    color: #B91C1C;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 3rem;
    color: #CBD5E1;
    margin-bottom: 15px;
}

.empty-state h3 {
    color: #1E293B;
    font-size: 1.2rem;
    margin-bottom: 8px;
}

.empty-state p {
    color: #64748B;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .results-table th,
    .results-table td {
        padding: 12px 15px;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .stat-card,
    .chart-card,
    .results-card {
        background: #1E293B;
    }
    
    .stat-value {
        color: #F1F5F9;
    }
    
    .card-header {
        border-bottom-color: #334155;
    }
    
    .card-header h3 {
        color: #F1F5F9;
    }
    
    .results-table th {
        background: #334155;
        color: #F1F5F9;
    }
    
    .results-table td {
        color: #F1F5F9;
        border-bottom-color: #334155;
    }
    
    .results-table tr:hover td {
        background: #334155;
    }
    
    .student-cell strong {
        color: #F1F5F9;
    }
}
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
<?php if (!empty($stats['distribution'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('distributionChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($stats['distribution'], 'score_range')); ?>,
            datasets: [{
                label: 'Number of Students',
                data: <?php echo json_encode(array_column($stats['distribution'], 'count')); ?>,
                backgroundColor: '#8B5CF6',
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
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>