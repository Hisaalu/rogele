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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title><?php echo $pageTitle; ?></title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Additional responsive fixes */
        * {
            box-sizing: border-box;
        }
        body {
            overflow-x: hidden;
            width: 100%;
        }
        img {
            max-width: 100%;
            height: auto;
        }
    </style>
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
                <span class="date-range-text"><?php echo date('M d, Y', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?></span>
            </div>
            <button class="btn-refresh" onclick="location.reload()" title="Refresh Data">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards - Mobile optimized -->
    <div class="stats-grid">
        <div class="stat-card stat-card-purple">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Users</span>
                <span class="stat-value"><?php echo number_format($totalUsers); ?></span>
                <span class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i> 12%
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
                    <i class="fas fa-arrow-up"></i> +5
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
                    <i class="fas fa-arrow-up"></i> +24
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
                    <i class="fas fa-arrow-up"></i> +8
                </span>
            </div>
        </div>
    </div>

    <!-- Filter Section - Mobile optimized -->
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
                <button class="quick-filter" data-days="7">7 days</button>
                <button class="quick-filter active" data-days="30">30 days</button>
                <button class="quick-filter" data-days="90">90 days</button>
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

    <!-- Navigation Tabs - Mobile optimized -->
    <div class="report-tabs">
        <a href="<?php echo BASE_URL; ?>/admin/reports" class="tab-item <?php echo $type === 'overview' ? 'active' : ''; ?>" data-tab="overview">
            <i class="fas fa-chart-line"></i>
            <span class="tab-label">Overview</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/users" class="tab-item <?php echo $type === 'users' ? 'active' : ''; ?>" data-tab="users">
            <i class="fas fa-users"></i>
            <span class="tab-label">Users</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/quizzes" class="tab-item <?php echo $type === 'quizzes' ? 'active' : ''; ?>" data-tab="quizzes">
            <i class="fas fa-pencil-alt"></i>
            <span class="tab-label">Quizzes</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/subscriptions" class="tab-item <?php echo $type === 'payments' ? 'active' : ''; ?>" data-tab="payments">
            <i class="fas fa-credit-card"></i>
            <span class="tab-label">Revenue</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/reports?type=activity" class="tab-item <?php echo $type === 'activity' ? 'active' : ''; ?>" data-tab="activity">
            <i class="fas fa-history"></i>
            <span class="tab-label">Activity</span>
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
                                <option value="7" <?php echo $days == 7 ? 'selected' : ''; ?>>7 days</option>
                                <option value="30" <?php echo $days == 30 ? 'selected' : ''; ?>>30 days</option>
                                <option value="90" <?php echo $days == 90 ? 'selected' : ''; ?>>90 days</option>
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
                                <option value="7" <?php echo $days == 7 ? 'selected' : ''; ?>>7 days</option>
                                <option value="30" <?php echo $days == 30 ? 'selected' : ''; ?>>30 days</option>
                                <option value="90" <?php echo $days == 90 ? 'selected' : ''; ?>>90 days</option>
                            </select>
                        </div>
                    </div>
                    <div class="chart-body">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Feed - Only 5 items -->
            <div class="activity-feed">
                <div class="feed-header">
                    <h3><i class="fas fa-clock"></i> Recent Activity</h3>
                    <a href="?type=activity" class="view-all">
                        <span class="view-all-text">View All</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="feed-list">
                    <?php if (empty($recentActivity)): ?>
                        <div class="empty-feed">
                            <i class="fas fa-inbox"></i>
                            <p>No recent activity</p>
                        </div>
                    <?php else: ?>
                        <?php 
                        $count = 0;
                        foreach ($recentActivity as $activity): 
                            if ($count >= 5) break;
                            $count++;
                        ?>
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
                                    <?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($recentActivity) > 5): ?>
                        <div class="view-more-container">
                            <a href="?type=activity" class="view-more-link">
                                <i class="fas fa-arrow-right"></i> View all <?php echo count($recentActivity); ?> activities
                            </a>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($type === 'users'): ?>
            <!-- Users Report -->
            <div class="report-card">
                <div class="card-header">
                    <h2><i class="fas fa-users"></i> User Registration Report</h2>
                    <span class="date-badge"><?php echo date('M d', strtotime($start_date)); ?> - <?php echo date('M d', strtotime($end_date)); ?></span>
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
                            <span class="stat-mini-label">Total</span>
                            <span class="stat-mini-value"><?php echo number_format(array_sum(array_column($data, 'total'))); ?></span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-label">Daily Avg</span>
                            <span class="stat-mini-value"><?php echo round(array_sum(array_column($data, 'total')) / count($data), 1); ?></span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-label">Peak</span>
                            <span class="stat-mini-value"><?php echo max(array_column($data, 'total')); ?></span>
                        </div>
                    </div>

                    <!-- Data Table - Mobile optimized -->
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>A</th>
                                    <th>T</th>
                                    <th>L</th>
                                    <th>E</th>
                                </tr>
                                <tr class="table-subhead">
                                    <th></th>
                                    <th></th>
                                    <th>Admin</th>
                                    <th>Teach</th>
                                    <th>Learn</th>
                                    <th>Ext</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row): ?>
                                <tr>
                                    <td class="date-cell"><?php echo date('M j', strtotime($row['date'])); ?></td>
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
                    <h2><i class="fas fa-pencil-alt"></i> Quiz Performance</h2>
                    <span class="date-badge"><?php echo date('M d', strtotime($start_date)); ?> - <?php echo date('M d', strtotime($end_date)); ?></span>
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
                            <span class="stat-mini-label">Attempts</span>
                            <span class="stat-mini-value"><?php echo number_format(array_sum(array_column($data, 'total_attempts'))); ?></span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-label">Students</span>
                            <span class="stat-mini-value"><?php echo number_format(array_sum(array_column($data, 'unique_students'))); ?></span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-label">Avg Score</span>
                            <span class="stat-mini-value"><?php echo round(array_sum(array_column($data, 'avg_score')) / count($data), 1); ?>%</span>
                        </div>
                    </div>

                    <!-- Data Table - Mobile optimized -->
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Quiz</th>
                                    <th>Att</th>
                                    <th>Std</th>
                                    <th>Score</th>
                                    <th>Pass</th>
                                </tr>
                                <tr class="table-subhead">
                                    <th></th>
                                    <th>empts</th>
                                    <th>ents</th>
                                    <th>Avg</th>
                                    <th>Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row): ?>
                                <tr>
                                    <td class="quiz-title"><?php echo htmlspecialchars(substr($row['title'], 0, 20)) . (strlen($row['title']) > 20 ? '...' : ''); ?></td>
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
                    <span class="date-badge"><?php echo date('M d', strtotime($start_date)); ?> - <?php echo date('M d', strtotime($end_date)); ?></span>
                </div>
                
                <?php if (empty($data)): ?>
                    <div class="empty-state">
                        <i class="fas fa-credit-card"></i>
                        <h3>No Payment Data</h3>
                        <p>There are no payments in the selected date range.</p>
                        <button class="btn-reset" onclick="resetFilters()">Reset Filters</button>
                    </div>
                <?php else: ?>
                    <!-- Summary Stats -->
                    <div class="stats-mini-grid">
                        <div class="stat-mini highlight">
                            <span class="stat-mini-label">Revenue</span>
                            <span class="stat-mini-value">UGX <?php echo number_format(array_sum(array_column($data, 'total_amount')) / 1000, 1); ?>k</span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-label">Transactions</span>
                            <span class="stat-mini-value"><?php echo number_format(array_sum(array_column($data, 'transaction_count'))); ?></span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-label">Avg</span>
                            <span class="stat-mini-value">UGX <?php echo number_format(round(array_sum(array_column($data, 'total_amount')) / array_sum(array_column($data, 'transaction_count')) / 1000, 1)); ?>k</span>
                        </div>
                    </div>

                    <!-- Data Table - Mobile optimized -->
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Trans</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row): ?>
                                <tr>
                                    <td><?php echo date('M j', strtotime($row['date'])); ?></td>
                                    <td class="number-cell"><?php echo number_format($row['transaction_count']); ?></td>
                                    <td class="number-cell">UGX <?php echo number_format($row['total_amount'] / 1000, 1); ?>k</td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($row['payment_method']); ?>">
                                            <?php echo substr($row['payment_method'], 0, 4); ?>
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
                    <span class="date-badge"><?php echo date('M d', strtotime($start_date)); ?> - <?php echo date('M d', strtotime($end_date)); ?></span>
                </div>
                
                <?php if (empty($data)): ?>
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <h3>No Activity Data</h3>
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
                                    <strong><?php echo date('M d', strtotime($row['date'])); ?></strong>
                                    <span class="badge badge-<?php 
                                        echo $row['action'] === 'LOGIN' ? 'info' : 
                                            ($row['action'] === 'REGISTRATION' ? 'success' : 
                                            ($row['action'] === 'QUIZ_ATTEMPT' ? 'warning' : 'secondary')); 
                                    ?>">
                                        <?php echo str_replace('_', ' ', substr($row['action'], 0, 8)) . (strlen($row['action']) > 8 ? '...' : ''); ?>
                                    </span>
                                </div>
                                <p><?php echo number_format($row['count']); ?> activities</p>
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
    --primary-purple: #7f2677;
    --primary-orange: #f06724;
    --success-green: #10B981;
    --danger-red: #EF4444;
    --warning-yellow: #F59E0B;
    --text-dark: black;
    --text-light: black;
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
    line-height: 1.5;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.reports-dashboard {
    max-width: 1400px;
    margin: 0 auto;
    padding: clamp(15px, 3vw, 30px) clamp(10px, 2vw, 20px);
    width: 100%;
}

/* Header Styles */
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
    background: linear-gradient(135deg, #7f2677);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1.2;
}

.welcome-subtitle {
    color: var(--text-light);
    font-size: clamp(0.85rem, 2vw, 1rem);
    margin-top: 5px;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    width: 100%;
    max-width: 100%;
}

.date-range-indicator {
    background: white;
    padding: 10px 15px;
    border-radius: 30px;
    box-shadow: var(--card-shadow);
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--text-dark);
    font-weight: 500;
    font-size: 0.9rem;
    flex: 1;
    min-width: 200px;
}

.date-range-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.date-range-indicator i {
    color: var(--primary-orange);
    flex-shrink: 0;
}

.btn-refresh {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: none;
    background: white;
    color: var(--text-light);
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: var(--card-shadow);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-refresh:hover {
    background: var(--primary-purple);
    color: white;
    transform: rotate(180deg);
}

/* Stats Grid */
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
    box-shadow: 0 15px 30px -10px rgba(139, 92, 246, 0.2);
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
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}

.stat-card-purple .stat-icon { background: linear-gradient(135deg, #f06724); }
.stat-card-orange .stat-icon { background: linear-gradient(135deg, #f06724); }
.stat-card-green .stat-icon { background: linear-gradient(135deg, #f06724); }
.stat-card-pink .stat-icon { background: linear-gradient(135deg, #f06724); }

.stat-content {
    flex: 1;
    min-width: 0;
}

.stat-label {
    display: block;
    color: var(--text-light);
    font-size: 0.8rem;
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.stat-value {
    display: block;
    font-size: clamp(1.3rem, 4vw, 1.8rem);
    font-weight: 700;
    color: var(--text-dark);
    line-height: 1.2;
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.stat-trend {
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    gap: 3px;
}

.stat-trend.positive { color: var(--success-green); }
.stat-trend.negative { color: var(--danger-red); }

/* Filter Section */
.filter-section {
    background: white;
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: var(--card-shadow);
}

.filter-header {
    margin-bottom: 15px;
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
    color: var(--primary-orange);
}

.filter-header p {
    color: var(--text-light);
    font-size: 0.85rem;
}

.filter-controls {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
}

.date-picker {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    flex: 2;
    width: 100%;
}

.date-input {
    flex: 1;
    min-width: 150px;
}

.date-input label {
    display: block;
    font-size: 0.8rem;
    color: var(--text-light);
    margin-bottom: 5px;
    font-weight: 500;
}

.input-wrapper {
    position: relative;
}

.input-wrapper i {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-light);
    font-size: 0.9rem;
    pointer-events: none;
}

.input-wrapper input {
    width: 100%;
    padding: 10px 10px 10px 35px;
    border: 2px solid var(--border-color);
    border-radius: 10px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    -webkit-appearance: none;
}

.input-wrapper input:focus {
    outline: none;
    border-color: var(--primary-purple);
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.quick-filters {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    width: 100%;
}

.quick-filter {
    padding: 8px 16px;
    border: 2px solid var(--border-color);
    border-radius: 30px;
    background: white;
    color: var(--text-light);
    font-weight: 500;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s ease;
    flex: 1;
    min-width: 70px;
}

.quick-filter:hover {
    border-color: var(--primary-orange);
    color: var(--primary-purple);
}

.quick-filter.active {
    background: linear-gradient(135deg, #7f2677);
    border-color: transparent;
    color: white;
}

.filter-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    width: 100%;
}

.btn-apply, .btn-export {
    padding: 10px 20px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.3s ease;
    flex: 1;
    min-width: 120px;
}

.btn-apply {
    background: linear-gradient(135deg, #7f2677);
    color: white;
    border: none;
}

.btn-apply:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px -5px rgba(139, 92, 246, 0.4);
}

.btn-export {
    background: white;
    color: var(--text-dark);
    border: 2px solid var(--border-color);
}

.btn-export:hover {
    border-color: var(--primary-orange);
    color: var(--primary-purple);
}

/* Report Tabs */
.report-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 25px;
    flex-wrap: wrap;
    background: white;
    padding: 10px;
    border-radius: 40px;
    box-shadow: var(--card-shadow);
}

.tab-item {
    flex: 1;
    min-width: 70px;
    padding: 10px 8px;
    border-radius: 30px;
    text-decoration: none;
    color: var(--text-light);
    font-weight: 500;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    transition: all 0.3s ease;
    text-align: center;
}

.tab-item i {
    font-size: 1rem;
    color: #f06724;
}

.tab-label {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.tab-item:hover {
    color: var(--primary-orange);
    background: rgba(139, 92, 246, 0.05);
}

.tab-item.active {
    background: linear-gradient(135deg, #7f2677);
    color: white;
}

/* Charts Grid */
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
    flex-wrap: wrap;
    gap: 10px;
}

.chart-title {
    display: flex;
    align-items: center;
    gap: 8px;
}

.chart-title i {
    color: var(--primary-orange);
    font-size: 1.1rem;
}

.chart-title h3 {
    color: var(--text-dark);
    font-size: 1rem;
    font-weight: 600;
}

.chart-select {
    padding: 6px 12px;
    border: 2px solid var(--border-color);
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

/* Activity Feed */
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
    flex-wrap: wrap;
    gap: 10px;
}

.feed-header h3 {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--text-dark);
    font-size: 1rem;
    font-weight: 600;
}

.feed-header h3 i {
    color: var(--primary-orange);
}

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
    background: rgba(139, 92, 246, 0.1);
    color: var(--primary-orange);
}

.view-all-text {
    display: inline-block;
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

.feed-item:hover {
    background: var(--bg-light);
}

.feed-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    color: white;
    flex-shrink: 0;
}

.feed-icon.login { background: linear-gradient(135deg, #f06724); }
.feed-icon.registration { background: linear-gradient(135deg, #f06724); }
.feed-icon.quiz_attempt { background: linear-gradient(135deg, #f06724); }

.feed-content {
    flex: 1;
    min-width: 0;
}

.feed-text {
    color: var(--text-dark);
    margin-bottom: 4px;
    line-height: 1.4;
    font-size: 0.9rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.feed-time {
    font-size: 0.75rem;
    color: var(--text-light);
    display: flex;
    align-items: center;
    gap: 4px;
}

.view-more-container {
    text-align: center;
    margin-top: 12px;
    padding-top: 10px;
    border-top: 1px solid var(--border-color);
}

.view-more-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--primary-purple);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 8px 16px;
    border-radius: 30px;
    transition: all 0.3s ease;
}

.view-more-link:hover {
    background: #f06724;
    transform: translateX(3px);
}

.empty-feed {
    text-align: center;
    padding: 30px;
    color: var(--text-light);
}

.empty-feed i {
    font-size: 2.5rem;
    margin-bottom: 10px;
    opacity: 0.3;
}

/* Report Card */
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

.card-header h2 {
    color: var(--text-dark);
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-header h2 i {
    color: var(--primary-purple);
}

.date-badge {
    background: var(--bg-light);
    padding: 5px 12px;
    border-radius: 30px;
    font-size: 0.8rem;
    color: var(--text-dark);
    font-weight: 500;
    white-space: nowrap;
}

/* Stats Mini Grid */
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
    font-size: 0.75rem;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-mini-value {
    display: block;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-dark);
    line-height: 1.2;
}

/* Table Styles */
.table-responsive {
    overflow-x: auto;
    border-radius: 12px;
    border: 1px solid var(--border-color);
    margin-top: 15px;
    -webkit-overflow-scrolling: touch;
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
    white-space: nowrap;
}

.table-subhead th {
    background: var(--bg-light);
    color: var(--text-light);
    font-weight: 400;
    font-size: 0.7rem;
    padding: 4px 10px 8px;
    border-bottom: none;
}

.data-table td {
    padding: 10px;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-dark);
    font-size: 0.85rem;
}

.data-table tr:hover td {
    background: var(--bg-light);
}

.date-cell {
    font-weight: 600;
    white-space: nowrap;
}

.number-cell {
    font-weight: 600;
    color: var(--primary-purple);
    text-align: right;
}

.quiz-title {
    max-width: 150px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Badges */
.badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    white-space: nowrap;
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
    padding: 10px;
}

.timeline-item {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    position: relative;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 9px;
    top: 25px;
    bottom: -20px;
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
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
}

.timeline-content {
    flex: 1;
    background: var(--bg-light);
    padding: 12px 15px;
    border-radius: 12px;
}

.timeline-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 5px;
    flex-wrap: wrap;
}

.timeline-header strong {
    color: var(--text-dark);
    font-size: 0.9rem;
}

.timeline-content p {
    color: var(--text-light);
    font-size: 0.85rem;
}

/* Empty State */
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

.empty-state h3 {
    color: var(--text-dark);
    font-size: 1.1rem;
    margin-bottom: 8px;
}

.empty-state p {
    color: var(--text-light);
    font-size: 0.9rem;
    margin-bottom: 20px;
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
    background: #7C3AED;
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .header-actions {
        width: 100%;
    }
    
    .date-range-indicator {
        width: 100%;
    }
    
    .filter-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .date-picker {
        flex-direction: column;
    }
    
    .date-input {
        width: 100%;
    }
    
    .quick-filters {
        width: 100%;
    }
    
    .filter-actions {
        width: 100%;
        margin-left: 0;
    }
    
    .btn-apply, .btn-export {
        width: 100%;
    }
    
    .report-tabs {
        flex-direction: row;
        overflow-x: auto;
        padding: 8px;
        gap: 5px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        white-space: nowrap;
        flex-wrap: nowrap;
    }
    
    .report-tabs::-webkit-scrollbar {
        display: none;
    }
    
    .tab-item {
        min-width: 90px;
        padding: 8px 5px;
        font-size: 0.75rem;
    }
    
    .stats-mini-grid {
        grid-template-columns: 1fr;
    }
    
    .feed-item {
        flex-wrap: wrap;
    }
    
    .view-all-text {
        display: none;
    }
    
    .view-all {
        padding: 5px;
    }
    
    .chart-body {
        height: 200px;
    }
}

@media (max-width: 480px) {
    .gradient-text {
        font-size: 1.5rem;
    }
    
    .stat-card {
        padding: 15px 10px;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }
    
    .stat-value {
        font-size: 1.3rem;
    }
    
    .timeline-item {
        flex-direction: column;
        gap: 8px;
    }
    
    .timeline-item::before {
        display: none;
    }
    
    .timeline-marker {
        margin-left: 0;
    }
    
    .card-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .date-badge {
        align-self: flex-start;
    }
    
    .feed-icon {
        width: 35px;
        height: 35px;
        font-size: 1rem;
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
    .filter-header h3,
    .timeline-header strong {
        color: #F1F5F9;
    }
    
    .stat-label,
    .feed-time,
    .date-badge,
    .filter-header p,
    .timeline-content p {
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
    
    .empty-state {
        background: #334155;
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
                pointRadius: 3,
                pointHoverRadius: 5
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
                    padding: 10,
                    cornerRadius: 6
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#E2E8F0' },
                    ticks: { 
                        stepSize: 1,
                        font: { size: 10 }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { 
                        maxRotation: 45,
                        minRotation: 45,
                        font: { size: 9 }
                    }
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
                borderRadius: 6,
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
                    padding: 10,
                    cornerRadius: 6,
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
                            if (value >= 1000000) {
                                return 'UGX ' + (value / 1000000).toFixed(1) + 'M';
                            } else if (value >= 1000) {
                                return 'UGX ' + (value / 1000).toFixed(0) + 'k';
                            }
                            return 'UGX ' + value;
                        },
                        font: { size: 9 }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { 
                        maxRotation: 45,
                        minRotation: 45,
                        font: { size: 9 }
                    }
                }
            }
        }
    });
});
<?php endif; ?>

// Helper functions
function updateChartDays(days) {
    window.location.href = `<?php echo BASE_URL; ?>/admin/reports?type=overview&days=${days}`;
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
    
    window.location.href = `<?php echo BASE_URL; ?>/admin/reports?type=${type}&start_date=${startDate}&end_date=${endDate}`;
}

function resetFilters() {
    window.location.href = `<?php echo BASE_URL; ?>/admin/reports?type=<?php echo $type; ?>`;
}

function exportReport() {
    const type = '<?php echo $type; ?>';
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    window.location.href = `<?php echo BASE_URL; ?>/admin/reports/export?type=${type}&start_date=${startDate}&end_date=${endDate}`;
}

// Quick filter buttons
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.quick-filter').forEach(btn => {
        btn.addEventListener('click', function() {
            const days = this.getAttribute('data-days') || 30;
            document.querySelectorAll('.quick-filter').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            setQuickRange(parseInt(days));
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>