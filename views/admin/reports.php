<?php
// File: /views/admin/reports.php
$pageTitle = 'Reports | ROGELE';
require_once __DIR__ . '/../layouts/admin_header.php';

$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'overview';
$start_date = filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_SPECIAL_CHARS) ?? date('Y-m-d', strtotime('-30 days'));
$end_date = filter_input(INPUT_GET, 'end_date', FILTER_SANITIZE_SPECIAL_CHARS) ?? date('Y-m-d');
$days = filter_input(INPUT_GET, 'days', FILTER_VALIDATE_INT) ?: 30;

$totalUsers = (int)($totalUsers ?? 0);
$totalTeachers = (int)($totalTeachers ?? 0);
$totalLearners = (int)($totalLearners ?? 0);
$totalExternal = (int)($totalExternal ?? 0);
$recentUsers = $recentUsers ?? [];
$recentActivity = $recentActivity ?? [];
$userGrowthData = $userGrowthData ?? [];
$revenueData = $revenueData ?? [];
$data = $data ?? [];
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root {
    --primary-purple: #7f2677;
    --primary-orange: #f06724;
    --success-green: #10B981;
    --danger-red: #EF4444;
    --warning-yellow: #F59E0B;
    --text-dark: #000;
    --text-light: #555;
    --bg-light: #F8FAFC;
    --border-color: #E2E8F0;
    --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
}

.reports-dashboard {
    max-width: 1400px;
    margin: 0 auto;
    padding: clamp(15px, 3vw, 30px) clamp(10px, 2vw, 20px);
    width: 100%;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: clamp(20px, 4vw, 30px);
    flex-wrap: wrap;
    gap: 15px;
}

.gradient-text {
    font-size: clamp(1.5rem, 5vw, 2.2rem);
    font-weight: 700;
    color: var(--primary-purple);
    line-height: 1.2;
}

.welcome-subtitle {
    color: var(--text-dark);
    font-size: clamp(0.85rem, 2vw, 1rem);
    margin-top: 5px;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    width: 100%;
    max-width: max-content;
}

.date-range-indicator {
    background: var(--primary-purple);
    padding: 10px 15px;
    border-radius: 30px;
    box-shadow: var(--card-shadow);
    display: flex;
    align-items: center;
    gap: 8px;
    color: white;
    font-weight: 500;
    font-size: 0.9rem;
}

.date-range-indicator i { color: var(--primary-orange); }

.btn-refresh {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: none;
    background: var(--primary-purple);
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: var(--card-shadow);
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-refresh:hover {
    background: var(--primary-orange);
    transform: rotate(180deg);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(200px, 100%), 1fr));
    gap: 15px;
    margin-bottom: 25px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 20px 15px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: var(--card-shadow);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px -10px rgba(127, 38, 119, 0.2);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--primary-purple);
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
    flex-shrink: 0;
    background-color: var(--primary-orange);
}

.stat-content {
    flex: 1;
    min-width: 0;
}

.stat-label {
    display: block;
    color: var(--text-light);
    font-size: 0.8rem;
    margin-bottom: 3px;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
}

.stat-value {
    display: block;
    font-size: clamp(1.3rem, 4vw, 1.8rem);
    font-weight: 700;
    color: var(--text-dark);
    line-height: 1.2;
    margin-bottom: 3px;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(350px, 100%), 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.chart-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: var(--card-shadow);
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    gap: 10px;
}

.chart-title {
    display: flex;
    align-items: center;
    gap: 8px;
}

.chart-title i { color: var(--primary-orange); }

.chart-select {
    padding: 6px 12px;
    border: 2px solid var(--primary-orange);
    border-radius: 20px;
    font-size: 0.85rem;
    color: var(--text-dark);
    background: white;
    cursor: pointer;
}

.chart-body {
    height: 250px;
    position: relative;
}

.activity-feed {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: var(--card-shadow);
}

.feed-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--border-color);
}

.feed-header h3 i { color: var(--primary-orange); }

.view-all {
    color: var(--primary-purple);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border-radius: 20px;
    transition: all 0.3s ease;
}

.view-all:hover {
    background: var(--primary-orange);
    color: white;
}

.feed-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.feed-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px;
    border-radius: 12px;
    transition: background 0.3s ease;
}

.feed-item:hover { background: var(--bg-light); }

.feed-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    color: white;
    flex-shrink: 0;
    background-color: var(--primary-orange);
}

.feed-content {
    flex: 1;
    min-width: 0;
}

.feed-text {
    color: var(--text-dark);
    margin-bottom: 4px;
    line-height: 1.4;
    font-size: 0.9rem;
}

.feed-time {
    font-size: 0.75rem;
    color: var(--text-light);
    display: flex;
    align-items: center;
    gap: 4px;
}

.report-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: var(--card-shadow);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--border-color);
    flex-wrap: wrap;
    gap: 10px;
}

.card-header h2 i { color: var(--primary-purple); }

.date-badge {
    background: var(--bg-light);
    padding: 5px 12px;
    border-radius: 30px;
    font-size: 0.8rem;
    color: var(--text-dark);
    font-weight: 500;
}

.stats-mini-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

.stat-mini {
    background: var(--bg-light);
    border-radius: 12px;
    padding: 15px 10px;
    text-align: center;
}

.stat-mini.highlight { background-color: var(--primary-orange); }

.stat-mini.highlight .stat-mini-label,
.stat-mini.highlight .stat-mini-value { color: white; }

.stat-mini-label {
    display: block;
    color: var(--text-light);
    font-size: 0.75rem;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-mini-value {
    display: block;
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-dark);
}

.table-responsive {
    overflow-x: auto;
    border-radius: 12px;
    border: 1px solid var(--border-color);
    margin-top: 15px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    min-width: 500px;
}

.data-table th {
    background: var(--bg-light);
    color: var(--text-dark);
    font-weight: 600;
    font-size: 0.8rem;
    padding: 12px 10px;
    text-align: left;
    border-bottom: 2px solid var(--border-color);
}

.data-table td {
    padding: 10px;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-dark);
    font-size: 0.85rem;
}

.data-table tr:hover td { background: var(--bg-light); }

.number-cell {
    font-weight: 600;
    color: var(--primary-purple);
    text-align: right;
}

.badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
}

.badge-success { background: #F0FDF4; color: #166534; }
.badge-warning { background: #FEF3C7; color: #92400E; }
.badge-danger { background: #FEF2F2; color: #B91C1C; }
.badge-info { background: #EFF6FF; color: #1E40AF; }

.empty-state {
    text-align: center;
    padding: 40px 20px;
    background: var(--bg-light);
    border-radius: 16px;
}

.empty-state i {
    font-size: 3rem;
    color: var(--text-light);
    margin-bottom: 15px;
    opacity: 0.5;
}

.btn-reset {
    padding: 10px 25px;
    background: var(--primary-purple);
    color: white;
    border: none;
    border-radius: 30px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-reset:hover {
    background: var(--primary-orange);
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .dashboard-header { flex-direction: column; align-items: flex-start; }
    .header-actions, .stats-mini-grid { width: 100%; grid-template-columns: 1fr; }
    .chart-body { height: 200px; }
}
</style>

<div class="reports-dashboard">
    <div class="dashboard-header">
        <div class="header-welcome">
            <h1 class="welcome-title"><span class="gradient-text">Analytics Report</span></h1>
            <p class="welcome-subtitle">Track ROGELE's performance in real-time!</p>
        </div>
        <div class="header-actions">
            <div class="date-range-indicator">
                <i class="fas fa-calendar-check"></i>
                <span><?= date('M d, Y', strtotime($start_date)); ?> - <?= date('M d, Y', strtotime($end_date)); ?></span>
            </div>
            <button class="btn-refresh" onclick="location.reload()" title="Refresh Data">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-content">
                <span class="stat-label">Total Users</span>
                <span class="stat-value"><?= number_format($totalUsers); ?></span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="stat-content">
                <span class="stat-label">Teachers</span>
                <span class="stat-value"><?= number_format($totalTeachers); ?></span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
            <div class="stat-content">
                <span class="stat-label">Learners</span>
                <span class="stat-value"><?= number_format($totalLearners); ?></span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-globe"></i></div>
            <div class="stat-content">
                <span class="stat-label">External</span>
                <span class="stat-value"><?= number_format($totalExternal); ?></span>
            </div>
        </div>
    </div>

    <div class="report-content">
        <?php if ($type === 'overview'): ?>
            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title"><i class="fas fa-chart-line"></i><h3>User Growth</h3></div>
                        <select class="chart-select" onchange="updateChartDays(this.value)">
                            <option value="7" <?= $days === 7 ? 'selected' : ''; ?>>7 days</option>
                            <option value="30" <?= $days === 30 ? 'selected' : ''; ?>>30 days</option>
                            <option value="90" <?= $days === 90 ? 'selected' : ''; ?>>90 days</option>
                        </select>
                    </div>
                    <div class="chart-body"><canvas id="userGrowthChart"></canvas></div>
                </div>

                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title"><i class="fas fa-chart-bar"></i><h3>Revenue Trend</h3></div>
                        <select class="chart-select" onchange="updateChartDays(this.value)">
                            <option value="7" <?= $days === 7 ? 'selected' : ''; ?>>7 days</option>
                            <option value="30" <?= $days === 30 ? 'selected' : ''; ?>>30 days</option>
                            <option value="90" <?= $days === 90 ? 'selected' : ''; ?>>90 days</option>
                        </select>
                    </div>
                    <div class="chart-body"><canvas id="revenueChart"></canvas></div>
                </div>
            </div>

            <div class="activity-feed">
                <div class="feed-header">
                    <h3><i class="fas fa-clock"></i> Recent Activity</h3>
                    <a href="<?= htmlspecialchars($baseUrl); ?>/admin/reports?type=activity" class="view-all"><span>View All</span><i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="feed-list">
                    <?php if (empty($recentActivity)): ?>
                        <div class="empty-feed"><i class="fas fa-inbox"></i><p>No recent activity</p></div>
                    <?php else: ?>
                        <?php 
                        $sliceActivity = array_slice($recentActivity, 0, 5);
                        $actionIcons = [
                            'LOGIN' => 'sign-in-alt',
                            'REGISTRATION' => 'user-plus',
                            'QUIZ_ATTEMPT' => 'pencil-alt'
                        ];
                        foreach ($sliceActivity as $activity): 
                            $icon = $actionIcons[$activity['action'] ?? ''] ?? 'bell';
                        ?>
                        <div class="feed-item">
                            <div class="feed-icon">
                                <i class="fas fa-<?= $icon; ?>"></i>
                            </div>
                            <div class="feed-content">
                                <p class="feed-text">
                                    <strong><?= htmlspecialchars(($activity['first_name'] ?? '') . ' ' . ($activity['last_name'] ?? '')); ?></strong>
                                    <?= htmlspecialchars($activity['description'] ?? ''); ?>
                                </p>
                                <span class="feed-time"><i class="far fa-clock"></i><?= date('M d, H:i', strtotime($activity['created_at'] ?? 'now')); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($type === 'users'): ?>
            <div class="report-card">
                <div class="card-header">
                    <h2><i class="fas fa-users"></i> User Registration Report</h2>
                    <span class="date-badge"><?= date('M d', strtotime($start_date)); ?> - <?= date('M d', strtotime($end_date)); ?></span>
                </div>
                
                <?php if (empty($data)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users-slash"></i><h3>No Data Available</h3>
                        <p>There are no user registrations in the selected date range.</p>
                        <button class="btn-reset" onclick="resetFilters()">Reset Filters</button>
                    </div>
                <?php else: 
                    $totals = array_column($data, 'total');
                    $sumTotals = array_sum($totals);
                    $rowCount = count($data);
                    $dailyAvg = $rowCount > 0 ? round($sumTotals / $rowCount, 1) : 0;
                    $peak = !empty($totals) ? max($totals) : 0;
                ?>
                    <div class="stats-mini-grid">
                        <div class="stat-mini"><span class="stat-mini-label">Total</span><span class="stat-mini-value"><?= number_format($sumTotals); ?></span></div>
                        <div class="stat-mini"><span class="stat-mini-label">Daily Avg</span><span class="stat-mini-value"><?= $dailyAvg; ?></span></div>
                        <div class="stat-mini"><span class="stat-mini-label">Peak</span><span class="stat-mini-value"><?= number_format($peak); ?></span></div>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr><th>Date</th><th>Total</th><th>Admin</th><th>Teach</th><th>Learn</th><th>Ext</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row): ?>
                                <tr>
                                    <td><strong><?= date('M j', strtotime($row['date'] ?? 'now')); ?></strong></td>
                                    <td class="number-cell"><?= number_format($row['total'] ?? 0); ?></td>
                                    <td><?= number_format($row['admins'] ?? 0); ?></td>
                                    <td><?= number_format($row['teachers'] ?? 0); ?></td>
                                    <td><?= number_format($row['learners'] ?? 0); ?></td>
                                    <td><?= number_format($row['external'] ?? 0); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($type === 'quizzes'): ?>
            <div class="report-card">
                <div class="card-header">
                    <h2><i class="fas fa-pencil-alt"></i> Quiz Performance</h2>
                    <span class="date-badge"><?= date('M d', strtotime($start_date)); ?> - <?= date('M d', strtotime($end_date)); ?></span>
                </div>
                
                <?php if (empty($data)): ?>
                    <div class="empty-state">
                        <i class="fas fa-pencil-alt"></i><h3>No Quiz Data Available</h3>
                        <button class="btn-reset" onclick="resetFilters()">Reset Filters</button>
                    </div>
                <?php else: 
                    $attempts = array_column($data, 'total_attempts');
                    $scores = array_column($data, 'avg_score');
                    $totalAttempts = array_sum($attempts);
                    $rowCount = count($data);
                    $avgScore = $rowCount > 0 ? round(array_sum($scores) / $rowCount, 1) : 0;
                ?>
                    <div class="stats-mini-grid">
                        <div class="stat-mini"><span class="stat-mini-label">Attempts</span><span class="stat-mini-value"><?= number_format($totalAttempts); ?></span></div>
                        <div class="stat-mini"><span class="stat-mini-label">Avg Score</span><span class="stat-mini-value"><?= $avgScore; ?>%</span></div>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr><th>Quiz Title</th><th>Attempts</th><th>Unique Students</th><th>Avg Score</th><th>Pass Rate</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row): 
                                    $title = $row['title'] ?? '';
                                    $truncatedTitle = (mb_strlen($title) > 25) ? mb_substr($title, 0, 25) . '...' : $title;
                                    $rowAttempts = (int)($row['total_attempts'] ?? 0);
                                    $passedCount = (int)($row['passed_count'] ?? 0);
                                    $passRate = $rowAttempts > 0 ? round(($passedCount / $rowAttempts) * 100, 1) : 0;
                                    $badgeClass = $passRate >= 70 ? 'badge-success' : ($passRate >= 50 ? 'badge-warning' : 'badge-danger');
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($truncatedTitle); ?></td>
                                    <td class="number-cell"><?= number_format($rowAttempts); ?></td>
                                    <td><?= number_format($row['unique_students'] ?? 0); ?></td>
                                    <td><?= round($row['avg_score'] ?? 0, 1); ?>%</td>
                                    <td><span class="badge <?= $badgeClass; ?>"><?= $passRate; ?>%</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($type === 'payments'): ?>
            <div class="report-card">
                <div class="card-header">
                    <h2><i class="fas fa-credit-card"></i> Revenue Report</h2>
                    <span class="date-badge"><?= date('M d', strtotime($start_date)); ?> - <?= date('M d', strtotime($end_date)); ?></span>
                </div>
                
                <?php if (empty($data)): ?>
                    <div class="empty-state">
                        <i class="fas fa-credit-card"></i><h3>No Payment Data</h3>
                        <button class="btn-reset" onclick="resetFilters()">Reset Filters</button>
                    </div>
                <?php else: ?>
                    <div class="stats-mini-grid">
                        <div class="stat-mini highlight"><span class="stat-mini-label">Total Revenue</span><span class="stat-mini-value">UGX <?= number_format(array_sum(array_column($data, 'total_amount'))); ?></span></div>
                        <div class="stat-mini"><span class="stat-mini-label">Transactions</span><span class="stat-mini-value"><?= number_format(array_sum(array_column($data, 'transaction_count'))); ?></span></div>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr><th>Date</th><th>Transactions</th><th>Amount</th><th>Method</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row): ?>
                                <tr>
                                    <td><?= date('M j, Y', strtotime($row['date'] ?? 'now')); ?></td>
                                    <td class="number-cell"><?= number_format($row['transaction_count'] ?? 0); ?></td>
                                    <td class="number-cell">UGX <?= number_format($row['total_amount'] ?? 0); ?></td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars($row['payment_method'] ?? 'N/A'); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
<?php if ($type === 'overview'): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx1 = document.getElementById('userGrowthChart');
    if (ctx1) {
        <?php
        $growthLabels = []; 
        $growthValues = [];
        if (!empty($userGrowthData)) {
            foreach ($userGrowthData as $row) {
                $growthLabels[] = date('M d', strtotime($row['date'] ?? 'now'));
                $growthValues[] = (int)($row['new_users'] ?? 0);
            }
        } else { 
            $growthLabels = ['No Data']; 
            $growthValues = [0]; 
        }
        ?>
        
        new Chart(ctx1.getContext('2d'), {
            type: 'line',
            data: {
                labels: <?= json_encode($growthLabels, JSON_HEX_TAG | JSON_HEX_AMP); ?>,
                datasets: [{
                    data: <?= json_encode($growthValues); ?>,
                    borderColor: '#f06724',
                    backgroundColor: 'rgba(127, 38, 119, 0.05)',
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#7f2677'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    }

    const ctx2 = document.getElementById('revenueChart');
    if (ctx2) {
        <?php
        $revenueLabels = []; 
        $revenueValues = [];
        if (!empty($revenueData)) {
            foreach ($revenueData as $row) {
                $revenueLabels[] = date('M d', strtotime($row['date'] ?? 'now'));
                $revenueValues[] = (int)($row['revenue'] ?? 0);
            }
        } else { 
            $revenueLabels = ['No Data']; 
            $revenueValues = [0]; 
        }
        ?>
        
        new Chart(ctx2.getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($revenueLabels, JSON_HEX_TAG | JSON_HEX_AMP); ?>,
                datasets: [{
                    data: <?= json_encode($revenueValues); ?>,
                    backgroundColor: '#f06724',
                    borderRadius: 4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    }
});
<?php endif; ?>

function updateChartDays(days) {
    window.location.href = `<?= htmlspecialchars($baseUrl); ?>/admin/reports?type=overview&days=${encodeURIComponent(days)}`;
}

function resetFilters() {
    window.location.href = `<?= htmlspecialchars($baseUrl); ?>/admin/reports?type=<?= htmlspecialchars($type); ?>`;
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>