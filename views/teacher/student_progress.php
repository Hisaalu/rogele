<?php
// File: /views/teacher/student_progress.php
$pageTitle = 'Student Progress | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$student = $student ?? [];
$quizResults = $quizResults ?? [];
$quizStats = $quizStats ?? [];

// Get only last 5 quizzes for the chart
$lastFiveQuizzes = array_slice($quizResults, -5);
?>

<div class="progress-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <a href="<?php echo BASE_URL; ?>/teacher/students" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Students
            </a>
            <h1 class="page-title">
                <i class="fas fa-chart-line"></i>
                Student Performance
            </h1>
            <p class="page-subtitle">
                Quiz performance for <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
            </p>
        </div>
    </div>

    <!-- Student Info Card -->
    <div class="student-info-card">
        <div class="student-avatar-large">
            <?php if (!empty($student['profile_photo'])): ?>
                <img src="<?php echo BASE_URL; ?>/<?php echo $student['profile_photo']; ?>" alt="">
            <?php else: ?>
                <div class="avatar-placeholder-large">
                    <?php echo strtoupper(substr($student['first_name'] ?? 'S', 0, 1)); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="student-details">
            <h2><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h2>
            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($student['email']); ?></p>
            <p><i class="fas fa-graduation-cap"></i> <?php echo $student['class_name'] ?? 'No Class'; ?></p>
        </div>
        <div class="student-stats-quick">
            <div class="quick-stat">
                <span class="quick-value"><?php echo $quizStats['total_quizzes'] ?? 0; ?></span>
                <span class="quick-label">Quizzes Taken</span>
            </div>
            <div class="quick-stat">
                <span class="quick-value"><?php echo $quizStats['average_score'] ?? 0; ?>%</span>
                <span class="quick-label">Average Score</span>
            </div>
            <div class="quick-stat">
                <span class="quick-value"><?php echo $quizStats['highest_score'] ?? 0; ?>%</span>
                <span class="quick-label">Highest Score</span>
            </div>
            <div class="quick-stat">
                <span class="quick-value"><?php echo $quizStats['lowest_score'] ?? 0; ?>%</span>
                <span class="quick-label">Lowest Score</span>
            </div>
        </div>
    </div>

    <!-- Performance Chart - Last 5 Quizzes -->
    <?php if (!empty($lastFiveQuizzes)): ?>
    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-chart-line"></i> Quiz Performance Trend (Last 5 Quizzes)</h3>
        </div>
        <div class="chart-container">
            <canvas id="performanceChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- All Quiz Results Table -->
    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-pencil-alt"></i> All Quiz Results</h3>
            <?php if (!empty($quizResults)): ?>
                <button onclick="exportQuizResults()" class="btn-export">
                    <i class="fas fa-download"></i> Export Results
                </button>
            <?php endif; ?>
        </div>

        <?php if (empty($quizResults)): ?>
            <div class="empty-mini">
                <i class="fas fa-inbox"></i>
                <p>No quiz attempts yet.</p>
                <p class="empty-hint">Quizzes will appear here once the student takes them.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="progress-table" id="quizResultsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Quiz</th>
                            <th>Score</th>
                            <th>Result</th>
                            <th>Date</th>
                        </thead>
                    <tbody>
                        <?php $counter = 1; ?>
                        <?php foreach ($quizResults as $result): ?>
                            <td><?php echo $counter++; ?></td>
                            <td class="quiz-title">
                                <strong><?php echo htmlspecialchars($result['quiz_title']); ?></strong>
                            </td>
                            <td class="score <?php echo $result['score'] >= 50 ? 'passed' : 'failed'; ?>">
                                <div class="score-circle">
                                    <?php echo $result['score']; ?>%
                                </div>
                            </td>
                            <td>
                                <?php if ($result['score'] >= 50): ?>
                                    <span class="badge passed">
                                        <i class="fas fa-check-circle"></i> Passed
                                    </span>
                                <?php else: ?>
                                    <span class="badge failed">
                                        <i class="fas fa-times-circle"></i> Failed
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="date-cell">
                                <?php echo date('M d, Y', strtotime($result['completed_at'])); ?>
                                <span class="time"><?php echo date('h:i A', strtotime($result['completed_at'])); ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Performance Summary -->
    <?php if (!empty($quizResults)): ?>
    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-chart-pie"></i> Performance Summary</h3>
        </div>
        <div class="summary-stats">
            <div class="summary-stat">
                <div class="summary-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="summary-info">
                    <span class="summary-label">Best Performance</span>
                    <span class="summary-value"><?php echo $quizStats['highest_score']; ?>%</span>
                    <span class="summary-sub"><?php echo $quizStats['best_quiz'] ?? 'N/A'; ?></span>
                </div>
            </div>
            <div class="summary-stat">
                <div class="summary-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="summary-info">
                    <span class="summary-label">Average Score</span>
                    <span class="summary-value"><?php echo $quizStats['average_score']; ?>%</span>
                    <span class="summary-sub">over <?php echo count($quizResults); ?> quizzes</span>
                </div>
            </div>
            <div class="summary-stat">
                <div class="summary-icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="summary-info">
                    <span class="summary-label">Improvement Trend</span>
                    <span class="summary-value"><?php echo $quizStats['trend'] ?? 'Stable'; ?></span>
                    <span class="summary-sub"><?php echo $quizStats['trend_direction'] ?? ''; ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.progress-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 20px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: black;
    text-decoration: none;
    font-size: 0.95rem;
    margin-bottom: 15px;
    transition: color 0.3s ease;
}

.back-link:hover {
    color: #7f2677;
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
    margin-bottom: 30px;
}

/* Student Info Card */
.student-info-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 30px;
    flex-wrap: wrap;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.student-avatar-large {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f06724);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.student-avatar-large img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder-large {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: #f06724;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 700;
    color: white;
}

.student-details {
    flex: 1;
}

.student-details h2 {
    color: black;
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.student-details p {
    color: black;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.student-details i {
    color: #f06724;
    width: 20px;
}

.student-stats-quick {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.quick-stat {
    text-align: center;
    background: #F8FAFC;
    padding: 12px 20px;
    border-radius: 12px;
    min-width: 90px;
}

.quick-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: #7f2677;
    line-height: 1.2;
}

.quick-label {
    color: black;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Section Cards */
.section-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #F1F5F9;
    flex-wrap: wrap;
    gap: 15px;
}

.section-header h3 {
    color: black;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

.section-header h3 i {
    color: #f06724;
}

.btn-export {
    padding: 8px 16px;
    background: #7f2677;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
}

.btn-export:hover {
    background: #f06724;
    transform: translateY(-2px);
}

/* Chart Container */
.chart-container {
    height: 300px;
    position: relative;
    margin-bottom: 10px;
}

/* Table */
.table-responsive {
    overflow-x: auto;
}

.progress-table {
    width: 100%;
    border-collapse: collapse;
}

.progress-table th {
    background: #F8FAFC;
    color: black;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 12px 15px;
    text-align: left;
    border-bottom: 2px solid #E2E8F0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.progress-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #F1F5F9;
    color: black;
    vertical-align: middle;
}

.progress-table tr:hover td {
    background: #F8FAFC;
}

.quiz-title {
    font-weight: 500;
}

.score {
    text-align: center;
}

.score-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
    margin: 0 auto;
}

.score-circle.passed {
    background: #F0FDF4;
    color: #059669;
    border: 2px solid #86EFAC;
}

.score-circle.failed {
    background: #FEF2F2;
    color: #DC2626;
    border: 2px solid #FECACA;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.75rem;
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

.date-cell {
    font-size: 0.85rem;
}

.date-cell .time {
    display: block;
    font-size: 0.7rem;
    color: #94A3B8;
    margin-top: 2px;
}

/* Summary Stats */
.summary-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.summary-stat {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: #F8FAFC;
    border-radius: 12px;
}

.summary-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #f06724);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
}

.summary-info {
    flex: 1;
}

.summary-label {
    display: block;
    font-size: 0.7rem;
    color: black;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 3px;
}

.summary-value {
    display: block;
    font-size: 1.3rem;
    font-weight: 700;
    color: black;
}

.summary-sub {
    display: block;
    font-size: 0.7rem;
    color: #94A3B8;
    margin-top: 3px;
}

/* Empty State */
.empty-mini {
    text-align: center;
    padding: 60px 20px;
    color: black;
    background: #F8FAFC;
    border-radius: 12px;
}

.empty-mini i {
    font-size: 3rem;
    color: #CBD5E1;
    margin-bottom: 15px;
}

.empty-mini p {
    margin-bottom: 5px;
}

.empty-hint {
    font-size: 0.8rem;
    color: black;
}

/* Responsive */
@media (max-width: 768px) {
    .student-info-card {
        flex-direction: column;
        text-align: center;
    }
    
    .student-details p {
        justify-content: center;
    }
    
    .student-stats-quick {
        justify-content: center;
    }
    
    .summary-stats {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .chart-container {
        height: 250px;
    }
}
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
<?php if (!empty($lastFiveQuizzes)): ?>
// Create performance chart with last 5 quizzes
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    const quizTitles = <?php echo json_encode(array_column($lastFiveQuizzes, 'quiz_title')); ?>;
    const quizScores = <?php echo json_encode(array_column($lastFiveQuizzes, 'score')); ?>;
    const passingScore = 50;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: quizTitles,
            datasets: [
                {
                    label: 'Score (%)',
                    data: quizScores,
                    borderColor: '#f06724',
                    backgroundColor: 'rgba(240, 103, 36, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#f06724',
                    pointBorderColor: 'white',
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Passing Score (50%)',
                    data: Array(quizScores.length).fill(passingScore),
                    borderColor: '#10B981',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    fill: false,
                    tension: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 10
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y + '%';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Score (%)',
                        color: 'black',
                        font: {
                            weight: 'bold'
                        }
                    },
                    grid: {
                        color: '#E2E8F0'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Quiz',
                        color: 'black',
                        font: {
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
});
<?php endif; ?>

// Export quiz results to CSV
function exportQuizResults() {
    const table = document.getElementById('quizResultsTable');
    const rows = table.querySelectorAll('tr');
    let csvContent = [];
    
    // Get headers
    const headers = [];
    const headerCells = rows[0].querySelectorAll('th');
    headerCells.forEach(cell => {
        headers.push(cell.innerText.trim());
    });
    csvContent.push(headers.join(','));
    
    // Get data rows
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.querySelectorAll('td');
        const rowData = [];
        
        cells.forEach((cell, index) => {
            let content = cell.innerText.trim();
            // Remove special formatting for score column
            if (index === 2) { // Score column
                content = content.replace('%', '');
            }
            // Escape quotes and wrap in quotes if contains comma
            if (content.includes(',') || content.includes('"') || content.includes('\n')) {
                content = content.replace(/"/g, '""');
                content = `"${content}"`;
            }
            rowData.push(content);
        });
        csvContent.push(rowData.join(','));
    }
    
    const csvString = csvContent.join('\n');
    const blob = new Blob(["\uFEFF" + csvString], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    const studentName = "<?php echo preg_replace('/[^a-zA-Z0-9]/', '_', $student['first_name'] . '_' . $student['last_name']); ?>";
    const filename = `${studentName}_quiz_results_${new Date().toISOString().slice(0,19).replace(/:/g, '-')}.csv`;
    
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    
    // Show success message
    alert('Quiz results exported successfully!');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>