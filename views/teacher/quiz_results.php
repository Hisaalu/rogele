<?php
// File: /views/teacher/quiz_results.php
$pageTitle = 'Quiz Results | ROGELE';
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
        
        <!-- Download Buttons -->
        <div class="download-actions">
            <button onclick="downloadResults('csv')" class="btn-download csv">
                <i class="fas fa-file-csv"></i> Download CSV
            </button>
            <button onclick="downloadResults('excel')" class="btn-download excel">
                <i class="fas fa-file-excel"></i> Download Excel
            </button>
            <button onclick="window.print()" class="btn-download print">
                <i class="fas fa-print"></i> Print
            </button>
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
            <div class="header-actions">
                <span class="result-count"><?php echo count($results); ?> attempts</span>
                <button onclick="copyTableToClipboard()" class="btn-icon" title="Copy to clipboard">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </div>

        <?php if (empty($results)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Results Yet</h3>
                <p>No students have taken this quiz yet.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="results-table" id="resultsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Score (%)</th>
                            <th>Result</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $counter = 1; ?>
                        <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td class="student-cell">
                                <strong><?php echo htmlspecialchars($result['first_name'] . ' ' . $result['last_name']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($result['email']); ?></td>
                            <td class="score-cell <?php echo $result['score'] >= ($quiz['passing_score'] ?? 50) ? 'passed' : 'failed'; ?>">
                                <?php echo number_format($result['score'], 1); ?>%
                            </td>
                            <td>
                                <?php if ($result['score'] >= ($quiz['passing_score'] ?? 50)): ?>
                                    <span class="badge passed">Passed</span>
                                <?php else: ?>
                                    <span class="badge failed">Failed</span>
                                <?php endif; ?>
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
    max-width: 1400px;
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

/* Download Buttons */
.download-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-download {
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-download.csv {
    background: #F1F5F9;
    color: #1E293B;
    border: 1px solid #E2E8F0;
}

.btn-download.csv:hover {
    background: #E2E8F0;
    transform: translateY(-2px);
}

.btn-download.excel {
    background: #10B981;
    color: white;
}

.btn-download.excel:hover {
    background: #059669;
    transform: translateY(-2px);
}

.btn-download.print {
    background: #8B5CF6;
    color: white;
}

.btn-download.print:hover {
    background: #7C3AED;
    transform: translateY(-2px);
}

.btn-icon {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    border: 1px solid #E2E8F0;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-icon:hover {
    background: #F1F5F9;
    transform: scale(1.05);
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
    color: black;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-header h3 i {
    color: #f06724;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.result-count {
    background: #F1F5F9;
    padding: 5px 12px;
    border-radius: 30px;
    font-size: 0.85rem;
    color: black;
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
    color: black;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 16px 20px;
    text-align: left;
    border-bottom: 2px solid #E2E8F0;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
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

.time-cell {
    font-family: monospace;
    font-size: 0.9rem;
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
    .page-header {
        flex-direction: column;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .results-table th,
    .results-table td {
        padding: 10px 12px;
        font-size: 0.85rem;
    }
    
    .download-actions {
        width: 100%;
    }
    
    .btn-download {
        flex: 1;
        justify-content: center;
    }
}

@media print {
    .download-actions,
    .back-link,
    .btn-icon,
    .chart-card {
        display: none;
    }
    
    .results-container {
        padding: 0;
    }
    
    .results-table {
        border: 1px solid #ddd;
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
    
    .btn-icon {
        background: #1E293B;
        border-color: #334155;
        color: #F1F5F9;
    }
    
    .btn-icon:hover {
        background: #334155;
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

// Download results as CSV
function downloadResults(format) {
    const table = document.getElementById('resultsTable');
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
        
        cells.forEach(cell => {
            let content = cell.innerText.trim();
            // Remove any HTML tags and clean up
            content = content.replace(/<[^>]*>/g, '');
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
    const quizTitle = "<?php echo preg_replace('/[^a-zA-Z0-9]/', '_', $quiz['title'] ?? 'quiz_results'); ?>";
    const filename = `${quizTitle}_results_${new Date().toISOString().slice(0,19).replace(/:/g, '-')}.csv`;
    
    if (format === 'excel') {
        // For Excel, we use the same CSV format but with .xls extension
        link.setAttribute('href', url);
        link.setAttribute('download', filename.replace('.csv', '.xls'));
    } else {
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
    }
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    
    // Show success message
    showToast('Results downloaded successfully!', 'success');
}

// Copy table to clipboard
async function copyTableToClipboard() {
    const table = document.getElementById('resultsTable');
    const range = document.createRange();
    range.selectNode(table);
    window.getSelection().removeAllRanges();
    window.getSelection().addRange(range);
    
    try {
        await navigator.clipboard.writeText(window.getSelection().toString());
        showToast('Table copied to clipboard!', 'success');
    } catch (err) {
        document.execCommand('copy');
        showToast('Table copied to clipboard!', 'success');
    }
    
    window.getSelection().removeAllRanges();
}

// Toast notification
function showToast(message, type = 'success') {
    // Create toast element if it doesn't exist
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        document.body.appendChild(toast);
        
        // Add styles for toast
        const style = document.createElement('style');
        style.textContent = `
            #toast {
                position: fixed;
                bottom: 20px;
                right: 20px;
                padding: 12px 24px;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                z-index: 9999;
                opacity: 0;
                transition: opacity 0.3s ease;
                pointer-events: none;
            }
            #toast.success {
                background: #10B981;
            }
            #toast.error {
                background: #EF4444;
            }
            #toast.info {
                background: #3B82F6;
            }
            #toast.show {
                opacity: 1;
            }
        `;
        document.head.appendChild(style);
    }
    
    toast.className = `${type} show`;
    toast.textContent = message;
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Add keyboard shortcut (Ctrl+D for download)
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'd') {
        e.preventDefault();
        downloadResults('csv');
    } else if (e.ctrlKey && e.key === 'e') {
        e.preventDefault();
        downloadResults('excel');
    } else if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        window.print();
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>