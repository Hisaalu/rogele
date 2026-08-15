<?php
// File: /views/admin/reports.php
$pageTitle = 'Data Analytics & Reports | ROGELE';
require_once __DIR__ . '/../layouts/admin_header.php';

$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'overview';
$start_date = filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_SPECIAL_CHARS) ?? date('Y-m-d', strtotime('-30 days'));
$end_date = filter_input(INPUT_GET, 'end_date', FILTER_SANITIZE_SPECIAL_CHARS) ?? date('Y-m-d');
$days = filter_input(INPUT_GET, 'days', FILTER_VALIDATE_INT) ?: 30;

$totalUsers = (int)($totalUsers ?? 0);
$totalTeachers = (int)($totalTeachers ?? 0);
$totalAdmins = (int)($totalAdmins ?? 0);
$totalExternal = (int)($totalExternal ?? 0);
$recentUsers = $recentUsers ?? [];
$recentActivity = $recentActivity ?? [];
$userGrowthData = $userGrowthData ?? [];
$revenueData = $revenueData ?? [];
$data = $data ?? [];
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root {
    --primary-purple: #7f2677;
    --primary-purple-dark: #5c1a57;
    --primary-purple-light: rgba(127, 38, 119, 0.08);
    --primary-orange: #f06724;
    --primary-orange-hover: #d95413;
    --bg-body: #f8fafc;
    --surface-card: #ffffff;
    --text-main: #000;
    --text-muted: #555;
    --border-color: #e2e8f0;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
    --shadow-md: 0 4px 12px -2px rgba(0,0,0,0.08), 0 2px 6px -1px rgba(0,0,0,0.04);
    --shadow-lg: 0 10px 25px -5px rgba(127, 38, 119, 0.12);
}

body {
    background-color: var(--bg-body);
}

.analytics-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 24px 16px;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

.analytics-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--primary-purple);
    margin: 0;
    letter-spacing: -0.5px;
}

.page-subtitle {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-top: 4px;
    margin-bottom: 0;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 9px 16px;
    background: var(--surface-card);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-main);
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: var(--shadow-sm);
    text-decoration: none;
    white-space: nowrap;
}

.action-btn:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: var(--primary-purple);
}

.action-btn.btn-primary {
    background: var(--primary-purple);
    color: white;
    border: none;
}

.action-btn.btn-primary:hover {
    background: var(--primary-purple-dark);
}

/* Enhanced Responsive Control Bar */
.control-bar {
    background: var(--surface-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 16px 20px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    box-shadow: var(--shadow-sm);
}

.analytics-nav {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #f1f5f9;
    padding: 4px;
    border-radius: 10px;
    overflow-x: auto;
    max-width: 100%;
    -webkit-overflow-scrolling: touch;
}

.nav-tab {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.nav-tab:hover {
    color: var(--primary-purple);
}

.nav-tab.active {
    background: var(--surface-card);
    color: var(--primary-purple);
    box-shadow: var(--shadow-sm);
}

.date-filter-form {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.form-group-inline {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.form-control-custom {
    padding: 7px 12px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 0.85rem;
    color: var(--text-main);
    background-color: #fff;
    outline: none;
    transition: border-color 0.2s ease;
}

.form-control-custom:focus {
    border-color: var(--primary-purple);
}

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 18px;
    margin-bottom: 24px;
}

.kpi-card {
    background: var(--surface-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 20px;
    box-shadow: var(--shadow-sm);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative;
}

.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.kpi-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.kpi-title {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
}

.kpi-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    color: white;
}

.kpi-icon.purple { background: var(--primary-purple); }
.kpi-icon.orange { background: var(--primary-orange); }
.kpi-icon.blue { background: #0284c7; }
.kpi-icon.green { background: #10b981; }

.kpi-value {
    font-size: 1.85rem;
    font-weight: 800;
    color: var(--text-main);
    line-height: 1;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.card-box {
    background: var(--surface-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 20px;
    box-shadow: var(--shadow-sm);
}

.card-box-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border-color);
}

.card-box-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-main);
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

.card-box-title i { color: var(--primary-orange); }

.chart-wrapper {
    height: 280px;
    position: relative;
    width: 100%;
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
    border-radius: 10px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
}

.feed-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: var(--primary-purple-light);
    color: var(--primary-purple);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.feed-content {
    flex: 1;
}

.feed-text {
    margin: 0 0 4px 0;
    font-size: 0.875rem;
    color: var(--text-main);
}

.feed-time {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.table-responsive {
    overflow-x: auto;
    border-radius: 10px;
    border: 1px solid var(--border-color);
    -webkit-overflow-scrolling: touch;
}

.analytics-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
    text-align: left;
    min-width: 600px;
}

.analytics-table th {
    background: #f8fafc;
    color: var(--text-muted);
    font-weight: 700;
    padding: 12px 16px;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    border-bottom: 1px solid var(--border-color);
}

.analytics-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-main);
}

.analytics-table tr:last-child td {
    border-bottom: none;
}

.analytics-table tr:hover td {
    background: #f8fafc;
}

.badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    display: inline-block;
}

.badge-success { background: #dcfce7; color: #166534; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-danger { background: #fee2e2; color: #991b1b; }
.badge-info { background: #e0f2fe; color: #075985; }

.text-right { text-align: right; }
.fw-bold { font-weight: 700; }

/* Dedicated Media Queries for Fine-Tuned Responsiveness */
@media (max-width: 992px) {
    .control-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .analytics-nav {
        width: 100%;
    }

    .date-filter-form {
        width: 100%;
        justify-content: space-between;
    }
}

@media (max-width: 768px) {
    .analytics-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-actions {
        width: 100%;
    }

    .header-actions .action-btn {
        flex: 1;
    }

    .date-filter-form {
        flex-direction: column;
        align-items: stretch;
    }

    .form-group-inline {
        width: 100%;
    }

    .form-group-inline .form-control-custom {
        flex: 1;
    }

    .date-filter-form select,
    .date-filter-form button {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .analytics-container {
        padding: 16px 12px;
    }

    .kpi-grid {
        grid-template-columns: 1fr;
    }

    .chart-wrapper {
        height: 220px;
    }

    .header-actions {
        flex-direction: column;
    }

    .header-actions .action-btn {
        width: 100%;
    }
}

@media print {
    .control-bar, .header-actions { display: none !important; }
    .analytics-container { padding: 0; }
}
</style>

<div class="analytics-container">
    <div class="analytics-header">
        <div>
            <h1 class="page-title"><i class="fas fa-chart-line anchor-icon"></i> Executive Data Analytics</h1>
            <p class="page-subtitle">Real-time performance metrics and institutional intelligence</p>
        </div>
        <div class="header-actions">
            <button onclick="window.print()" class="action-btn"><i class="fas fa-print"></i> Print Report</button>
            <button onclick="exportCSV()" class="action-btn"><i class="fas fa-download"></i> Export Data</button>
            <button onclick="location.reload()" class="action-btn btn-primary"><i class="fas fa-sync-alt"></i> Refresh</button>
        </div>
    </div>

    <div class="control-bar">
        <nav class="analytics-nav">
            <a href="<?= htmlspecialchars($baseUrl); ?>/admin/reports?type=overview" class="nav-tab <?= $type === 'overview' ? 'active' : ''; ?>">Overview</a>
            <a href="<?= htmlspecialchars($baseUrl); ?>/admin/reports?type=users" class="nav-tab <?= $type === 'users' ? 'active' : ''; ?>">Users Growth</a>
            <a href="<?= htmlspecialchars($baseUrl); ?>/admin/reports?type=quizzes" class="nav-tab <?= $type === 'quizzes' ? 'active' : ''; ?>">Quiz Analytics</a>
            <a href="<?= htmlspecialchars($baseUrl); ?>/admin/reports?type=payments" class="nav-tab <?= $type === 'payments' ? 'active' : ''; ?>">Revenue & Financials</a>
        </nav>

        <form method="GET" action="<?= htmlspecialchars($baseUrl); ?>/admin/reports" class="date-filter-form">
            <input type="hidden" name="type" value="<?= htmlspecialchars($type); ?>">
            <div class="form-group-inline">
                <input type="date" name="start_date" class="form-control-custom" value="<?= htmlspecialchars($start_date); ?>">
                <span style="color: var(--text-muted); font-size: 0.8rem;">to</span>
                <input type="date" name="end_date" class="form-control-custom" value="<?= htmlspecialchars($end_date); ?>">
            </div>
            <select class="form-control-custom" onchange="quickPreset(this.value)">
                <option value="">Presets...</option>
                <option value="7">Last 7 Days</option>
                <option value="30">Last 30 Days</option>
                <option value="90">Last 90 Days</option>
            </select>
            <button type="submit" class="action-btn" style="padding: 7px 14px;">Apply Filter</button>
        </form>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-title">Total Platform Users</span>
                <div class="kpi-icon purple"><i class="fas fa-users"></i></div>
            </div>
            <div class="kpi-value"><?= number_format($totalUsers); ?></div>
        </div>

        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-title">Teachers / Faculty</span>
                <div class="kpi-icon orange"><i class="fas fa-chalkboard-teacher"></i></div>
            </div>
            <div class="kpi-value"><?= number_format($totalTeachers); ?></div>
        </div>

        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-title">Admins</span>
                <div class="kpi-icon blue"><i class="fas fa-user-graduate"></i></div>
            </div>
            <div class="kpi-value"><?= number_format($totalAdmins); ?></div>
        </div>

        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-title">External / Guest</span>
                <div class="kpi-icon green"><i class="fas fa-globe"></i></div>
            </div>
            <div class="kpi-value"><?= number_format($totalExternal); ?></div>
        </div>
    </div>

    <?php if ($type === 'overview'): ?>
        <div class="charts-grid">
            <div class="card-box">
                <div class="card-box-header">
                    <h3 class="card-box-title"><i class="fas fa-chart-line"></i> User Acquisition Trend</h3>
                </div>
                <div class="chart-wrapper">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>

            <div class="card-box">
                <div class="card-box-header">
                    <h3 class="card-box-title"><i class="fas fa-chart-bar"></i> Revenue Trajectory (UGX)</h3>
                </div>
                <div class="chart-wrapper">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card-box">
            <div class="card-box-header">
                <h3 class="card-box-title"><i class="fas fa-history"></i> Live Activity Audit Log</h3>
                <a href="<?= htmlspecialchars($baseUrl); ?>/admin/reports?type=activity" class="action-btn" style="padding: 4px 10px; font-size: 0.75rem;">View Full Log</a>
            </div>
            <div class="feed-list">
                <?php if (empty($recentActivity)): ?>
                    <p style="color: var(--text-muted); font-size: 0.875rem;">No recent platform activity recorded in this period.</p>
                <?php else: ?>
                    <?php 
                    $sliceActivity = array_slice($recentActivity, 0, 5);
                    $actionIcons = [
                        'LOGIN' => 'sign-in-alt',
                        'REGISTRATION' => 'user-plus',
                        'QUIZ_ATTEMPT' => 'pen-to-square'
                    ];
                    foreach ($sliceActivity as $activity): 
                        $icon = $actionIcons[$activity['action'] ?? ''] ?? 'bell';
                    ?>
                        <div class="feed-item">
                            <div class="feed-icon"><i class="fas fa-<?= $icon; ?>"></i></div>
                            <div class="feed-content">
                                <p class="feed-text">
                                    <strong><?= htmlspecialchars(($activity['first_name'] ?? '') . ' ' . ($activity['last_name'] ?? '')); ?></strong>
                                    <?= htmlspecialchars($activity['description'] ?? ''); ?>
                                </p>
                                <span class="feed-time"><i class="far fa-clock"></i> <?= date('M d, Y H:i', strtotime($activity['created_at'] ?? 'now')); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($type === 'users'): ?>
        <div class="card-box">
            <div class="card-box-header">
                <h3 class="card-box-title"><i class="fas fa-users"></i> Registration Analytics</h3>
            </div>
            <div class="table-responsive">
                <table class="analytics-table" id="exportTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-right">Total Registrations</th>
                            <th class="text-right">Admins</th>
                            <th class="text-right">Teachers</th>
                            <th class="text-right">Learners</th>
                            <th class="text-right">External</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data)): ?>
                            <tr><td colspan="6" style="text-align:center; color: var(--text-muted);">No records found for the selected range.</td></tr>
                        <?php else: ?>
                            <?php foreach ($data as $row): ?>
                                <tr>
                                    <td><strong><?= date('M d, Y', strtotime($row['date'] ?? 'now')); ?></strong></td>
                                    <td class="text-right fw-bold" style="color: var(--primary-purple);"><?= number_format($row['total'] ?? 0); ?></td>
                                    <td class="text-right"><?= number_format($row['admins'] ?? 0); ?></td>
                                    <td class="text-right"><?= number_format($row['teachers'] ?? 0); ?></td>
                                    <td class="text-right"><?= number_format($row['learners'] ?? 0); ?></td>
                                    <td class="text-right"><?= number_format($row['external'] ?? 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($type === 'quizzes'): ?>
        <div class="card-box">
            <div class="card-box-header">
                <h3 class="card-box-title"><i class="fas fa-pen-to-square"></i> Assessment & Quiz Performance</h3>
            </div>
            <div class="table-responsive">
                <table class="analytics-table" id="exportTable">
                    <thead>
                        <tr>
                            <th>Quiz Module</th>
                            <th class="text-right">Total Attempts</th>
                            <th class="text-right">Unique Candidates</th>
                            <th class="text-right">Average Score</th>
                            <th class="text-right">Pass Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data)): ?>
                            <tr><td colspan="5" style="text-align:center; color: var(--text-muted);">No assessment records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($data as $row): 
                                $rowAttempts = (int)($row['total_attempts'] ?? 0);
                                $passedCount = (int)($row['passed_count'] ?? 0);
                                $passRate = $rowAttempts > 0 ? round(($passedCount / $rowAttempts) * 100, 1) : 0;
                                $badgeClass = $passRate >= 70 ? 'badge-success' : ($passRate >= 50 ? 'badge-warning' : 'badge-danger');
                            ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['title'] ?? 'Untitled Quiz'); ?></strong></td>
                                    <td class="text-right"><?= number_format($rowAttempts); ?></td>
                                    <td class="text-right"><?= number_format($row['unique_students'] ?? 0); ?></td>
                                    <td class="text-right fw-bold"><?= round($row['avg_score'] ?? 0, 1); ?>%</td>
                                    <td class="text-right"><span class="badge <?= $badgeClass; ?>"><?= $passRate; ?>%</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($type === 'payments'): ?>
        <div class="card-box">
            <div class="card-box-header">
                <h3 class="card-box-title"><i class="fas fa-wallet"></i> Financial Transactions & Revenue Breakdown</h3>
            </div>
            <div class="table-responsive">
                <table class="analytics-table" id="exportTable">
                    <thead>
                        <tr>
                            <th>Transaction Date</th>
                            <th class="text-right">Volume</th>
                            <th class="text-right">Total Revenue</th>
                            <th>Payment Channel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data)): ?>
                            <tr><td colspan="4" style="text-align:center; color: var(--text-muted);">No financial records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($data as $row): ?>
                                <tr>
                                    <td><strong><?= date('M d, Y', strtotime($row['date'] ?? 'now')); ?></strong></td>
                                    <td class="text-right"><?= number_format($row['transaction_count'] ?? 0); ?></td>
                                    <td class="text-right fw-bold" style="color: #10b981;">UGX <?= number_format($row['total_amount'] ?? 0); ?></td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars(strtoupper($row['payment_method'] ?? 'N/A')); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
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
        }
        ?>
        new Chart(ctx1.getContext('2d'), {
            type: 'line',
            data: {
                labels: <?= json_encode($growthLabels); ?>,
                datasets: [{
                    label: 'New Registrations',
                    data: <?= json_encode($growthValues); ?>,
                    borderColor: '#7f2677',
                    backgroundColor: 'rgba(127, 38, 119, 0.08)',
                    borderWidth: 2,
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#f06724'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { border: { dash: [4, 4] }, beginAtZero: true }
                }
            }
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
        }
        ?>
        new Chart(ctx2.getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($revenueLabels); ?>,
                datasets: [{
                    label: 'Revenue (UGX)',
                    data: <?= json_encode($revenueValues); ?>,
                    backgroundColor: '#f06724',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { border: { dash: [4, 4] }, beginAtZero: true }
                }
            }
        });
    }
});
<?php endif; ?>

function quickPreset(days) {
    if (!days) return;
    const end = new Date();
    const start = new Date();
    start.setDate(end.getDate() - parseInt(days));

    const formatDate = (d) => d.toISOString().split('T')[0];
    
    document.querySelector('input[name="start_date"]').value = formatDate(start);
    document.querySelector('input[name="end_date"]').value = formatDate(end);
}

function exportCSV() {
    const table = document.getElementById("exportTable");
    if (!table) {
        alert("No tabular data available on this view to export.");
        return;
    }
    
    let csv = [];
    for (let i = 0; i < table.rows.length; i++) {
        let row = [], cols = table.rows[i].querySelectorAll("td, th");
        for (let j = 0; j < cols.length; j++) {
            row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        }
        csv.push(row.join(","));
    }

    const csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
    const downloadLink = document.createElement("a");
    downloadLink.download = "analytics_export_<?= $type; ?>_<?= date('Y-m-d'); ?>.csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>