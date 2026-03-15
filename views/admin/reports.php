<?php
// File: /views/admin/reports.php
$pageTitle = 'Reports - Admin - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';

// Get filter parameters
$type = $_GET['type'] ?? 'overview';
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$days = $_GET['days'] ?? 30;

// Get stats from controller
$totalUsers = $totalUsers ?? 0;
$totalTeachers = $totalTeachers ?? 0;
$totalLearners = $totalLearners ?? 0;
$totalExternal = $totalExternal ?? 0;
$recentUsers = $recentUsers ?? [];
$recentActivity = $recentActivity ?? [];
$userGrowthData = $userGrowthData ?? [];
$revenueData = $revenueData ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="reports-dashboard">
    <!-- Header Section with Welcome Message -->
    <div class="dashboard-header">
        <div class="header-welcome">
            <h1 class="welcome-title">
                <span class="gradient-text">Analytics Report</span>
            </h1>
            <p class="welcome-subtitle">Track your platform's performance in real-time</p>
        </div>
        <div class="header-actions">
            <div class="date-range-indicator">
                <i class="fas fa-calendar-check"></i>
                <span><?php echo date('M d, Y', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?></span>
            </div>
            <button class="btn-refresh" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-card-purple">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Users</span>
                <span class="stat-value"><?php echo number_format($totalUsers); ?></span>
                <span class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i> 12% vs last month
                </span>
            </div>
        </div>

        <div class="stat-card stat-card-orange">
            <div class="stat-icon">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Teachers</span>
                <span class="stat-value"><?php echo number_format($totalTeachers); ?></span>
                <span class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i> 5 new this month
                </span>
            </div>
        </div>

        <div class="stat-card stat-card-green">
            <div class="stat-icon">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Learners</span>
                <span class="stat-value"><?php echo number_format($totalLearners); ?></span>
                <span class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i> 24 new this month
                </span>
            </div>
        </div>

        <div class="stat-card stat-card-pink">
            <div class="stat-icon">
                <i class="fas fa-globe"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">External</span>
                <span class="stat-value"><?php echo number_format($totalExternal); ?></span>
                <span class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i> 8 new this month
                </span>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="filter-header">
            <h3><i class="fas fa-sliders-h"></i> Customize Your View</h3>
            <p>Select date range and report type</p>
        </div>
        
        <div class="filter-controls">
            <!-- Date Range Picker -->
            <div class="date-picker">
                <div class="date-input">
                    <label>From</label>
                    <div class="input-wrapper">
                        <i class="fas fa-calendar-alt"></i>
                        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <div class="date-input">
                    <label>To</label>
                    <div class="input-wrapper">
                        <i class="fas fa-calendar-alt"></i>
                        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
            </div>

            <!-- Quick Filters -->
            <div class="quick-filters">
                <button class="quick-filter" onclick="setQuickRange(7)">Last 7 days</button>
                <button class="quick-filter active" onclick="setQuickRange(30)">Last 30 days</button>
                <button class="quick-filter" onclick="setQuickRange(90)">Last 90 days</button>
            </div>

            <!-- Action Buttons -->
            <div class="filter-actions">
                <button class="btn-apply" onclick="applyFilters()">
                    <i class="fas fa-check"></i> Apply
                </button>
                <button class="btn-export" onclick="exportReport()">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="report-tabs">
        <a href="/rays-of-grace/admin/reports" 
           class="tab-item <?php echo $type === 'overview' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i>
            <span>Overview</span>
        </a>
        <a href="/rays-of-grace/admin/users" 
           class="tab-item <?php echo $type === 'users' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>
        <a href="?type=quizzes&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" 
           class="tab-item <?php echo $type === 'quizzes' ? 'active' : ''; ?>">
            <i class="fas fa-pencil-alt"></i>
            <span>Quizzes</span>
        </a>
        <a href="?type=payments&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" 
           class="tab-item <?php echo $type === 'payments' ? 'active' : ''; ?>">
            <i class="fas fa-credit-card"></i>
            <span>Revenue</span>
        </a>
        <a href="/rays-of-grace/admin/reports?type=activity" 
           class="tab-item <?php echo $type === 'activity' ? 'active' : ''; ?>">
            <i class="fas fa-history"></i>
            <span>Activity</span>
        </a>
    </div>

    <!-- Report Content -->
    <div class="report-content">
        <?php if ($type === 'overview'): ?>
            <!-- Overview Section -->
            <div class="charts-grid">
                <!-- User Growth Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">
                            <i class="fas fa-chart-line"></i>
                            <h3>User Growth</h3>
                        </div>
                        <div class="chart-controls">
                            <select class="chart-select" onchange="updateChartDays(this.value)">
                                <option value="7" <?php echo $days == 7 ? 'selected' : ''; ?>>Last 7 days</option>
                                <option value="30" <?php echo $days == 30 ? 'selected' : ''; ?>>Last 30 days</option>
                                <option value="90" <?php echo $days == 90 ? 'selected' : ''; ?>>Last 90 days</option>
                            </select>
                        </div>
                    </div>
                    <div class="chart-body">
                        <canvas id="userGrowthChart"></canvas>
                    </div>
                </div>

                <!-- Revenue Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">
                            <i class="fas fa-chart-bar"></i>
                            <h3>Revenue Trend</h3>
                        </div>
                        <div class="chart-controls">
                            <select class="chart-select" onchange="updateChartDays(this.value)">
                                <option value="7" <?php echo $days == 7 ? 'selected' : ''; ?>>Last 7 days</option>
                                <option value="30" <?php echo $days == 30 ? 'selected' : ''; ?>>Last 30 days</option>
                                <option value="90" <?php echo $days == 90 ? 'selected' : ''; ?>>Last 90 days</option>
                            </select>
                        </div>
                    </div>
                    <div class="chart-body">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Feed -->
            <div class="activity-feed">
                <div class="feed-header">
                    <h3><i class="fas fa-clock"></i> Recent Activity</h3>
                    <a href="?type=activity" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="feed-list">
                    <?php if (empty($recentActivity)): ?>
                        <div class="empty-feed">
                            <i class="fas fa-inbox"></i>
                            <p>No recent activity</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentActivity as $activity): ?>
                        <div class="feed-item">
                            <div class="feed-icon <?php echo strtolower($activity['action']); ?>">
                                <i class="fas fa-<?php 
                                    echo $activity['action'] === 'LOGIN' ? 'sign-in-alt' : 
                                        ($activity['action'] === 'REGISTRATION' ? 'user-plus' : 
                                        ($activity['action'] === 'QUIZ_ATTEMPT' ? 'pencil-alt' : 'bell')); 
                                ?>"></i>
                            </div>
                            <div class="feed-content">
                                <p class="feed-text">
                                    <strong><?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?></strong>
                                    <?php echo htmlspecialchars($activity['description']); ?>
                                </p>
                                <span class="feed-time">
                                    <i class="far fa-clock"></i>
                                    <?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($type === 'users'): ?>
            <!-- Users Report -->
            <div class="report-card">
                <div class="card-header">
                    <h2><i class="fas fa-users"></i> User Registration Report</h2>
                    <span class="date-badge"><?php echo date('M d, Y', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?></span>
                </div>
                
                <?php if (empty($data)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users-slash"></i>
                        <h3>No Data Available</h3>
                        <p>There are no user registrations in the selected date range.</p>
                        <button class="btn-reset" onclick="resetFilters()">Reset Filters</button>
                    </div>
                <?php else: ?>
                    <!-- Summary Stats -->
                    <div class="stats-mini-grid">
                        <div class="stat-mini">
                            <span class="stat-mini-label">Total Registrations</span>
                            <span class="stat-mini-value"><?php echo number_format(array_sum(array_column($data, 'total'))); ?></span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-label">Avg. Daily</span>
                            <span class="stat-mini-value"><?php echo round(array_sum(array_column($data, 'total')) / count($data), 1); ?></span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-label">Peak Day</span>
                            <span class="stat-mini-value"><?php echo max(array_column($data, 'total')); ?></span>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Admins</th>
                                    <th>Teachers</th>
                                    <th>Learners</th>
                                    <th>External</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row): ?>
                                <tr>
                                    <td class="date-cell"><?php echo date('M j, Y', strtotime($row['date'])); ?></td>
                                    <td class="number-cell"><?php echo number_format($row['total']); ?></td>
                                    <td class="number-cell"><?php echo number_format($row['admins']); ?></td>
                                    <td class="number-cell"><?php echo number_format($row['teachers']); ?></td>
                                    <td class="number-cell"><?php echo number_format($row['learners']); ?></td>
                                    <td class="number-cell"><?php echo number_format($row['external']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($type === 'quizzes'): ?>
            <!-- Quizzes Report -->
            <div class="report-card">
                <div class="card-header">
                    <h2><i class="fas fa-pencil-alt"></i> Quiz Performance Report</h2>
                    <span class="date-badge"><?php echo date('M d, Y', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?></span>
                </div>
                
                <?php if (empty($data)): ?>
                    <div class="empty-state">
                        <i class="fas fa-pencil-alt"></i>
                        <h3>No Quiz Data Available</h3>
                        <p>There are no quiz attempts in the selected date range.</p>
                        <button class="btn-reset" onclick="resetFilters()">Reset Filters</button>
                    </div>
                <?php else: ?>
                    <!-- Summary Stats -->
                    <div class="stats-mini-grid">
                        <div class="stat-mini">
                            <span class="stat-mini-label">Total Attempts</span>
                            <span class="stat-mini-value"><?php echo number_format(array_sum(array_column($data, 'total_attempts'))); ?></span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-label">Unique Students</span>
                            <span class="stat-mini-value"><?php echo number_format(array_sum(array_column($data, 'unique_students'))); ?></span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-label">Avg. Score</span>
                            <span class="stat-mini-value"><?php echo round(array_sum(array_column($data, 'avg_score')) / count($data), 1); ?>%</span>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Quiz</th>
                                    <th>Attempts</th>
                                    <th>Students</th>
                                    <th>Avg. Score</th>
                                    <th>Pass Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                                    <td class="number-cell"><?php echo number_format($row['total_attempts']); ?></td>
                                    <td class="number-cell"><?php echo number_format($row['unique_students']); ?></td>
                                    <td class="number-cell"><?php echo round($row['avg_score'], 1); ?>%</td>
                                    <td>
                                        <?php 
                                        $passRate = $row['total_attempts'] > 0 ? round(($row['passed_count'] / $row['total_attempts']) * 100, 1) : 0;
                                        ?>
                                        <span class="badge <?php echo $passRate >= 70 ? 'badge-success' : ($passRate >= 50 ? 'badge-warning' : 'badge-danger'); ?>">
                                            <?php echo $passRate; ?>%
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($type === 'payments'): ?>
            <!-- Payments Report -->
            <div class="report-card">
                <div class="card-header">
                    <h2><i class="fas fa-credit-card"></i> Revenue Report</h2>
                    <span class="date-badge"><?php echo date('M d, Y', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?></span>
                </div>
                
                <?php if (empty($data)): ?>
                    <div class="empty-state">
                        <i class="fas fa-credit-card"></i>
                        <h3>No Payment Data Available</h3>
                        <p>There are no payments in the selected date range.</p>
                        <button class="btn-reset" onclick="resetFilters()">Reset Filters</button>
                    </div>
                <?php else: ?>
                    <!-- Summary Stats -->
                    <div class="stats-mini-grid">
                        <div class="stat-mini highlight">
                            <span class="stat-mini-label">Total Revenue</span>
                            <span class="stat-mini-value">UGX <?php echo number_format(array_sum(array_column($data, 'total_amount'))); ?></span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-label">Transactions</span>
                            <span class="stat-mini-value"><?php echo number_format(array_sum(array_column($data, 'transaction_count'))); ?></span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-label">Avg. Value</span>
                            <span class="stat-mini-value">UGX <?php echo number_format(round(array_sum(array_column($data, 'total_amount')) / array_sum(array_column($data, 'transaction_count')))); ?></span>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Transactions</th>
                                    <th>Total Amount</th>
                                    <th>Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row): ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($row['date'])); ?></td>
                                    <td class="number-cell"><?php echo number_format($row['transaction_count']); ?></td>
                                    <td class="number-cell">UGX <?php echo number_format($row['total_amount']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($row['payment_method']); ?>">
                                            <?php echo htmlspecialchars($row['payment_method']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($type === 'activity'): ?>
            <!-- Activity Report -->
            <div class="report-card">
                <div class="card-header">
                    <h2><i class="fas fa-history"></i> Activity Log</h2>
                    <span class="date-badge"><?php echo date('M d, Y', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?></span>
                </div>
                
                <?php if (empty($data)): ?>
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <h3>No Activity Data Available</h3>
                        <p>There are no user activities in the selected date range.</p>
                        <button class="btn-reset" onclick="resetFilters()">Reset Filters</button>
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($data as $row): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker" style="background: <?php 
                                echo $row['action'] === 'LOGIN' ? '#8B5CF6' : 
                                    ($row['action'] === 'REGISTRATION' ? '#10B981' : 
                                    ($row['action'] === 'QUIZ_ATTEMPT' ? '#F97316' : '#64748B')); 
                            ?>;"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong><?php echo date('M d, Y', strtotime($row['date'])); ?></strong>
                                    <span class="badge badge-<?php 
                                        echo $row['action'] === 'LOGIN' ? 'info' : 
                                            ($row['action'] === 'REGISTRATION' ? 'success' : 
                                            ($row['action'] === 'QUIZ_ATTEMPT' ? 'warning' : 'secondary')); 
                                    ?>">
                                        <?php echo str_replace('_', ' ', $row['action']); ?>
                                    </span>
                                </div>
                                <p><?php echo number_format($row['count']); ?> activities recorded</p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
:root {
    --primary-purple: #8B5CF6;
    --primary-orange: #F97316;
    --success-green: #10B981;
    --danger-red: #EF4444;
    --warning-yellow: #F59E0B;
    --text-dark: #1E293B;
    --text-light: #64748B;
    --bg-light: #F8FAFC;
    --border-color: #E2E8F0;
    --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background: #F1F5F9;
}

.reports-dashboard {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

/* Header Styles */
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.gradient-text {
    font-size: 2.2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.welcome-subtitle {
    color: var(--text-light);
    font-size: 1rem;
    margin-top: 5px;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.date-range-indicator {
    background: white;
    padding: 12px 20px;
    border-radius: 12px;
    box-shadow: var(--card-shadow);
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-dark);
    font-weight: 500;
}

.date-range-indicator i {
    color: var(--primary-orange);
}

.btn-refresh {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    border: none;
    background: white;
    color: var(--text-light);
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: var(--card-shadow);
}

.btn-refresh:hover {
    background: var(--primary-purple);
    color: white;
    transform: rotate(180deg);
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
    border-radius: 20px;
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: var(--card-shadow);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 30px -10px rgba(139, 92, 246, 0.2);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
}

.stat-card-purple::before { background: var(--primary-purple); }
.stat-card-orange::before { background: var(--primary-orange); }
.stat-card-green::before { background: var(--success-green); }
.stat-card-pink::before { background: #EC4899; }

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

.stat-card-purple .stat-icon { background: linear-gradient(135deg, #8B5CF6, #7C3AED); }
.stat-card-orange .stat-icon { background: linear-gradient(135deg, #F97316, #EA580C); }
.stat-card-green .stat-icon { background: linear-gradient(135deg, #10B981, #059669); }
.stat-card-pink .stat-icon { background: linear-gradient(135deg, #EC4899, #DB2777); }

.stat-content {
    flex: 1;
}

.stat-label {
    display: block;
    color: var(--text-light);
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.stat-value {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-dark);
    line-height: 1.2;
    margin-bottom: 5px;
}

.stat-trend {
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 4px;
}

.stat-trend.positive { color: var(--success-green); }
.stat-trend.negative { color: var(--danger-red); }

/* Filter Section */
.filter-section {
    background: white;
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: var(--card-shadow);
}

.filter-header {
    margin-bottom: 20px;
}

.filter-header h3 {
    color: var(--text-dark);
    font-size: 1.1rem;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-header h3 i {
    color: var(--primary-purple);
}

.filter-header p {
    color: var(--text-light);
    font-size: 0.9rem;
}

.filter-controls {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: flex-end;
}

.date-picker {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    flex: 2;
}

.date-input {
    flex: 1;
    min-width: 200px;
}

.date-input label {
    display: block;
    font-size: 0.85rem;
    color: var(--text-light);
    margin-bottom: 5px;
    font-weight: 500;
}

.input-wrapper {
    position: relative;
}

.input-wrapper i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-light);
}

.input-wrapper input {
    width: 100%;
    padding: 12px 12px 12px 40px;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.input-wrapper input:focus {
    outline: none;
    border-color: var(--primary-purple);
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.quick-filters {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.quick-filter {
    padding: 10px 20px;
    border: 2px solid var(--border-color);
    border-radius: 30px;
    background: white;
    color: var(--text-light);
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.quick-filter:hover {
    border-color: var(--primary-purple);
    color: var(--primary-purple);
}

.quick-filter.active {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    border-color: transparent;
    color: white;
}

.filter-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-left: auto;
}

.btn-apply {
    padding: 12px 30px;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
    border: none;
    border-radius: 30px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-apply:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -5px rgba(139, 92, 246, 0.4);
}

.btn-export {
    padding: 12px 30px;
    background: white;
    color: var(--text-dark);
    border: 2px solid var(--border-color);
    border-radius: 30px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-export:hover {
    border-color: var(--primary-purple);
    color: var(--primary-purple);
}

/* Report Tabs */
.report-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    flex-wrap: wrap;
    background: white;
    padding: 10px;
    border-radius: 50px;
    box-shadow: var(--card-shadow);
}

.tab-item {
    flex: 1;
    min-width: 100px;
    padding: 12px 20px;
    border-radius: 40px;
    text-decoration: none;
    color: var(--text-light);
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.tab-item i {
    font-size: 1.1rem;
}

.tab-item:hover {
    color: var(--primary-purple);
    background: rgba(139, 92, 246, 0.05);
}

.tab-item.active {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    color: white;
}

/* Charts Grid */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.chart-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: var(--card-shadow);
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.chart-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.chart-title i {
    color: var(--primary-purple);
    font-size: 1.2rem;
}

.chart-title h3 {
    color: var(--text-dark);
    font-size: 1.1rem;
    font-weight: 600;
}

.chart-select {
    padding: 8px 15px;
    border: 2px solid var(--border-color);
    border-radius: 30px;
    font-size: 0.9rem;
    color: var(--text-dark);
    background: white;
    cursor: pointer;
}

.chart-body {
    height: 300px;
    position: relative;
}

/* Activity Feed */
.activity-feed {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: var(--card-shadow);
}

.feed-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--border-color);
}

.feed-header h3 {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-dark);
    font-size: 1.1rem;
}

.feed-header h3 i {
    color: var(--primary-purple);
}

.view-all {
    color: var(--primary-purple);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.view-all:hover {
    color: var(--primary-orange);
}

.feed-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.feed-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border-radius: 12px;
    transition: background 0.3s ease;
}

.feed-item:hover {
    background: var(--bg-light);
}

.feed-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: white;
}

.feed-icon.login { background: linear-gradient(135deg, #8B5CF6, #7C3AED); }
.feed-icon.registration { background: linear-gradient(135deg, #10B981, #059669); }
.feed-icon.quiz_attempt { background: linear-gradient(135deg, #F97316, #EA580C); }

.feed-content {
    flex: 1;
}

.feed-text {
    color: var(--text-dark);
    margin-bottom: 5px;
    line-height: 1.5;
}

.feed-time {
    font-size: 0.8rem;
    color: var(--text-light);
    display: flex;
    align-items: center;
    gap: 5px;
}

.empty-feed {
    text-align: center;
    padding: 40px;
    color: var(--text-light);
}

.empty-feed i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.3;
}

/* Report Card */
.report-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: var(--card-shadow);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--border-color);
    flex-wrap: wrap;
    gap: 15px;
}

.card-header h2 {
    color: var(--text-dark);
    font-size: 1.3rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-header h2 i {
    color: var(--primary-purple);
}

.date-badge {
    background: var(--bg-light);
    padding: 8px 16px;
    border-radius: 30px;
    font-size: 0.9rem;
    color: var(--text-dark);
    font-weight: 500;
}

/* Stats Mini Grid */
.stats-mini-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-mini {
    background: var(--bg-light);
    border-radius: 16px;
    padding: 20px;
    text-align: center;
}

.stat-mini.highlight {
    background: linear-gradient(135deg, #8B5CF6, #F97316);
}

.stat-mini.highlight .stat-mini-label,
.stat-mini.highlight .stat-mini-value {
    color: white;
}

.stat-mini-label {
    display: block;
    color: var(--text-light);
    font-size: 0.85rem;
    margin-bottom: 8px;
}

.stat-mini-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-dark);
}

/* Table Styles */
.table-responsive {
    overflow-x: auto;
    border-radius: 12px;
    border: 1px solid var(--border-color);
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.data-table th {
    background: var(--bg-light);
    color: var(--text-dark);
    font-weight: 600;
    font-size: 0.9rem;
    padding: 16px 20px;
    text-align: left;
    border-bottom: 2px solid var(--border-color);
}

.data-table td {
    padding: 14px 20px;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-dark);
}

.data-table tr:hover td {
    background: var(--bg-light);
}

.date-cell {
    font-weight: 600;
    color: var(--text-dark);
}

.number-cell {
    font-weight: 600;
    color: var(--primary-purple);
}

/* Badges */
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

.badge-info {
    background: #EFF6FF;
    color: #1E40AF;
}

.badge-mtn {
    background: #FEF3C7;
    color: #92400E;
}

.badge-airtel {
    background: #F0FDF4;
    color: #166534;
}

/* Timeline */
.timeline {
    padding: 20px;
}

.timeline-item {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
    position: relative;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 9px;
    top: 35px;
    bottom: -25px;
    width: 2px;
    background: var(--border-color);
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-marker {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 3px;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.2);
}

.timeline-content {
    flex: 1;
    background: var(--bg-light);
    padding: 15px 20px;
    border-radius: 12px;
}

.timeline-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 8px;
    flex-wrap: wrap;
}

.timeline-header strong {
    color: var(--text-dark);
    font-size: 1rem;
}

.timeline-content p {
    color: var(--text-light);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--bg-light);
    border-radius: 20px;
}

.empty-state i {
    font-size: 4rem;
    color: var(--text-light);
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state h3 {
    color: var(--text-dark);
    font-size: 1.3rem;
    margin-bottom: 10px;
}

.empty-state p {
    color: var(--text-light);
    margin-bottom: 25px;
}

.btn-reset {
    padding: 12px 30px;
    background: var(--primary-purple);
    color: white;
    border: none;
    border-radius: 30px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-reset:hover {
    background: #7C3AED;
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 1024px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .filter-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .date-picker {
        flex-direction: column;
    }
    
    .quick-filters {
        width: 100%;
    }
    
    .filter-actions {
        width: 100%;
        margin-left: 0;
    }
    
    .btn-apply, .btn-export {
        flex: 1;
    }
    
    .report-tabs {
        flex-direction: column;
        border-radius: 20px;
        padding: 10px;
    }
    
    .tab-item {
        width: 100%;
    }
    
    .stats-mini-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .gradient-text {
        font-size: 1.8rem;
    }
    
    .stat-card {
        flex-direction: column;
        text-align: center;
    }
    
    .stat-card::before {
        width: 100%;
        height: 4px;
    }
    
    .timeline-item {
        flex-direction: column;
    }
    
    .timeline-item::before {
        display: none;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    body {
        background: #0F172A;
    }
    
    .filter-section,
    .chart-card,
    .activity-feed,
    .report-card,
    .stat-card,
    .date-range-indicator,
    .btn-refresh,
    .report-tabs {
        background: #1E293B;
    }
    
    .stat-value,
    .feed-text,
    .card-header h2,
    .chart-title h3,
    .filter-header h3 {
        color: #F1F5F9;
    }
    
    .stat-label,
    .feed-time,
    .date-badge,
    .filter-header p {
        color: #94A3B8;
    }
    
    .input-wrapper input {
        background: #0F172A;
        border-color: #334155;
        color: #F1F5F9;
    }
    
    .data-table {
        background: #1E293B;
    }
    
    .data-table th {
        background: #334155;
        color: #F1F5F9;
    }
    
    .data-table td {
        color: #F1F5F9;
        border-bottom-color: #334155;
    }
    
    .data-table tr:hover td {
        background: #334155;
    }
    
    .stat-mini {
        background: #334155;
    }
    
    .stat-mini-value {
        color: #F1F5F9;
    }
    
    .timeline-content {
        background: #334155;
    }
    
    .btn-export {
        background: transparent;
        color: #94A3B8;
        border-color: #334155;
    }
    
    .btn-export:hover {
        border-color: #8B5CF6;
        color: #8B5CF6;
    }
}
</style>

<script>
// Chart initialization
<?php if ($type === 'overview'): ?>
document.addEventListener('DOMContentLoaded', function() {
    // User Growth Chart
    const ctx1 = document.getElementById('userGrowthChart').getContext('2d');
    
    <?php
    $growthLabels = [];
    $growthValues = [];
    if (!empty($userGrowthData)) {
        foreach ($userGrowthData as $row) {
            $growthLabels[] = date('M d', strtotime($row['date']));
            $growthValues[] = (int)$row['new_users'];
        }
    } else {
        $growthLabels = ['No Data'];
        $growthValues = [0];
    }
    ?>
    
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($growthLabels); ?>,
            datasets: [{
                label: 'New Users',
                data: <?php echo json_encode($growthValues); ?>,
                borderColor: '#8B5CF6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#8B5CF6',
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1E293B',
                    titleColor: '#F1F5F9',
                    bodyColor: '#F1F5F9',
                    padding: 12,
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#E2E8F0' },
                    ticks: { stepSize: 1 }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Revenue Chart
    const ctx2 = document.getElementById('revenueChart').getContext('2d');
    
    <?php
    $revenueLabels = [];
    $revenueValues = [];
    if (!empty($revenueData)) {
        foreach ($revenueData as $row) {
            $revenueLabels[] = date('M d', strtotime($row['date']));
            $revenueValues[] = (int)$row['revenue'];
        }
    } else {
        $revenueLabels = ['No Data'];
        $revenueValues = [0];
    }
    ?>
    
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($revenueLabels); ?>,
            datasets: [{
                label: 'Revenue (UGX)',
                data: <?php echo json_encode($revenueValues); ?>,
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
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1E293B',
                    titleColor: '#F1F5F9',
                    bodyColor: '#F1F5F9',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return 'UGX ' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#E2E8F0' },
                    ticks: {
                        callback: function(value) {
                            return 'UGX ' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
<?php endif; ?>

// Helper functions
function updateChartDays(days) {
    window.location.href = `/rays-of-grace/admin/reports?type=overview&days=${days}`;
}

function setQuickRange(days) {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - days);
    
    document.getElementById('start_date').value = startDate.toISOString().split('T')[0];
    document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
}

function applyFilters() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const type = '<?php echo $type; ?>';
    
    if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
        alert('Start date cannot be after end date');
        return;
    }
    
    window.location.href = `/rays-of-grace/admin/reports?type=${type}&start_date=${startDate}&end_date=${endDate}`;
}

function resetFilters() {
    window.location.href = `/rays-of-grace/admin/reports?type=<?php echo $type; ?>`;
}

function exportReport() {
    const type = '<?php echo $type; ?>';
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    window.location.href = `/rays-of-grace/admin/reports/export?type=${type}&start_date=${startDate}&end_date=${endDate}`;
}

// Quick filter buttons
document.querySelectorAll('.quick-filter').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.quick-filter').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>