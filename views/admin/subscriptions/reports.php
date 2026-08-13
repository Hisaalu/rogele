<?php
// File: /views/admin/subscriptions/reports.php
$pageTitle = 'Subscription Reports | ROGELE';
require_once __DIR__ . '/../../layouts/admin_header.php';

$stats = $stats ?? [];
$expiring = $expiring ?? [];
$revenueByMonth = $revenueByMonth ?? [];
?>

<div class="reports-container">
    <header class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-chart-bar" aria-hidden="true"></i>
                Subscription Reports
            </h1>
            <p class="page-subtitle">Analytics and insights for all platform subscriptions</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo BASE_URL; ?>/admin/subscriptions/export?report=true" class="btn-export">
                <i class="fas fa-download" aria-hidden="true"></i>
                Export Report
            </a>
        </div>
    </header>

    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon active">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
            </div>
            <div class="stat-info">
                <h3>Active Subscriptions</h3>
                <p class="stat-number"><?php echo number_format($stats['active'] ?? 0); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon expired">
                <i class="fas fa-clock" aria-hidden="true"></i>
            </div>
            <div class="stat-info">
                <h3>Expired</h3>
                <p class="stat-number"><?php echo number_format($stats['expired'] ?? 0); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon revenue">
                <i class="fas fa-coins" aria-hidden="true"></i>
            </div>
            <div class="stat-info">
                <h3>Total Revenue</h3>
                <p class="stat-number">UGX <?php echo number_format($stats['total_revenue'] ?? 0); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon monthly">
                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
            </div>
            <div class="stat-info">
                <h3>This Month</h3>
                <p class="stat-number">UGX <?php echo number_format($stats['monthly_revenue'] ?? 0); ?></p>
            </div>
        </div>
    </section>

    <section class="charts-grid">
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-line" aria-hidden="true"></i> Revenue Overview (Last 12 Months)</h3>
            </div>
            <div class="chart-body">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-pie" aria-hidden="true"></i> Plan Distribution</h3>
            </div>
            <div class="chart-body">
                <canvas id="planChart"></canvas>
            </div>
        </div>
    </section>

    <section class="expiring-section">
        <h2 class="section-title">
            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
            Subscriptions Expiring Soon (Next 30 Days)
        </h2>
        
        <?php if (empty($expiring)): ?>
            <div class="empty-message">
                <i class="fas fa-calendar-check" aria-hidden="true"></i>
                <p>No subscriptions expiring in the next 30 days</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="expiring-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Plan</th>
                            <th>End Date</th>
                            <th>Days Left</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expiring as $sub): 
                            $endDateTimestamp = !empty($sub['end_date']) ? strtotime($sub['end_date']) : time();
                            $daysLeft = (int)floor(($endDateTimestamp - time()) / 86400);
                        ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <strong><?php echo htmlspecialchars(trim(($sub['first_name'] ?? '') . ' ' . ($sub['last_name'] ?? ''))); ?></strong>
                                    <small><?php echo htmlspecialchars($sub['email'] ?? ''); ?></small>
                                </div>
                            </td>
                            <td>
                                <span class="plan-badge <?php echo htmlspecialchars($sub['plan_type'] ?? ''); ?>">
                                    <?php echo htmlspecialchars(ucfirst($sub['plan_type'] ?? '')); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', $endDateTimestamp); ?></td>
                            <td>
                                <span class="days-badge <?php echo $daysLeft <= 7 ? 'urgent' : ''; ?>">
                                    <?php echo $daysLeft < 0 ? '0' : $daysLeft; ?> days
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/admin/subscriptions/view/<?php echo urlencode($sub['id']); ?>" class="btn-view">
                                    <i class="fas fa-eye" aria-hidden="true"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="plan-details-section">
        <h2 class="section-title">
            <i class="fas fa-cubes" aria-hidden="true"></i>
            Plan Distribution Details
        </h2>
        
        <div class="plan-details-grid">
            <?php 
            $planDistribution = $stats['plan_distribution'] ?? [];
            $planColors = [
                'monthly' => '#7f2677',
                'termly'  => '#f06724',
                'yearly'  => '#e41d59'
            ];
            
            foreach ($planDistribution as $plan): 
                $pType = strtolower($plan['plan_type'] ?? '');
                $color = $planColors[$pType] ?? '#7f2677';
            ?>
            <div class="plan-detail-card">
                <div class="plan-header" style="background: <?php echo $color; ?>;">
                    <h4><?php echo htmlspecialchars(ucfirst($plan['plan_type'] ?? '')); ?></h4>
                </div>
                <div class="plan-stats">
                    <div class="plan-stat">
                        <span class="stat-label">Active Subscribers</span>
                        <span class="stat-value"><?php echo number_format($plan['count'] ?? 0); ?></span>
                    </div>
                    <div class="plan-stat">
                        <span class="stat-label">Revenue Generated</span>
                        <span class="stat-value">UGX <?php echo number_format($plan['total'] ?? 0); ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const revenueElement = document.getElementById('revenueChart');
    if (revenueElement) {
        const revenueCtx = revenueElement.getContext('2d');
        const revenueData = <?php echo json_encode(array_reverse($revenueByMonth)); ?>;

        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueData.map(item => {
                    if (!item.month) return '';
                    const [year, month] = item.month.split('-');
                    const date = new Date(year, month - 1);
                    return date.toLocaleString('default', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Revenue (UGX)',
                    data: revenueData.map(item => item.revenue || 0),
                    borderColor: '#7f2677',
                    backgroundColor: 'rgba(127, 38, 119, 0.08)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#f06724'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' Revenue: UGX ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'UGX ' + value.toLocaleString();
                            }
                        },
                        grid: { color: '#F1F5F9' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    const planElement = document.getElementById('planChart');
    if (planElement) {
        const planCtx = planElement.getContext('2d');
        const planData = <?php echo json_encode($stats['plan_distribution'] ?? []); ?>;

        new Chart(planCtx, {
            type: 'doughnut',
            data: {
                labels: planData.map(item => ucfirst(item.plan_type || '') + ' (' + (item.count || 0) + ')'),
                datasets: [{
                    data: planData.map(item => item.count || 0),
                    backgroundColor: ['#7f2677', '#f06724', '#e41d59', '#3B82F6'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    function ucfirst(string) {
        if (!string) return '';
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
});
</script>

<style>
:root {
    --primary-purple: #7f2677;
    --accent-orange: #f06724;
    --text-dark: #000;
    --text-muted: #555;
    --bg-surface: #FFFFFF;
    --border-color: #E2E8F0;
    --radius-lg: 16px;
    --radius-md: 10px;
    --shadow-md: 0 10px 30px rgba(0, 0, 0, 0.05);
    --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.reports-container,
.reports-container * {
    box-sizing: border-box;
}

.reports-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: clamp(16px, 3vw, 32px);
    color: var(--text-dark);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: clamp(20px, 3vw, 30px);
    flex-wrap: wrap;
    gap: 16px;
}

.page-title {
    font-size: clamp(1.6rem, 3.5vw, 2.2rem);
    font-weight: 700;
    color: var(--primary-purple);
    margin: 0 0 6px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-subtitle {
    color: var(--text-muted);
    font-size: 0.95rem;
    margin: 0;
}

.header-actions {
    display: flex;
    gap: 12px;
}

.btn-back, .btn-export {
    padding: 10px 18px;
    border-radius: var(--radius-md);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: var(--transition);
}

.btn-back {
    background: #F1F5F9;
    color: var(--text-dark);
}

.btn-export {
    background: var(--primary-purple);
    color: white;
}

.btn-back:hover, .btn-export:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}

.stat-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-3px);
}

.stat-icon {
    width: 52px;
    height: 52px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}

.stat-icon.active { background: #F0FDF4; color: #10B981; }
.stat-icon.expired { background: #FEF2F2; color: #EF4444; }
.stat-icon.revenue { background: #EFF6FF; color: #3B82F6; }
.stat-icon.monthly { background: #FEF3C7; color: #F59E0B; }

.stat-info h3 {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin: 0 0 4px 0;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-number {
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--text-dark);
    margin: 0;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.chart-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    padding: 20px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
}

.chart-header {
    margin-bottom: 16px;
}

.chart-header h3 {
    color: var(--text-dark);
    font-size: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

.chart-header h3 i {
    color: var(--accent-orange);
}

.chart-body {
    height: 300px;
    position: relative;
}

.expiring-section,
.plan-details-section {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    padding: 24px;
    margin-bottom: 30px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
}

.section-title {
    color: var(--text-dark);
    font-size: 1.2rem;
    font-weight: 700;
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: var(--accent-orange);
}

.table-responsive {
    overflow-x: auto;
}

.expiring-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.expiring-table th {
    background: #F8FAFC;
    padding: 14px 18px;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-dark);
    border-bottom: 2px solid var(--border-color);
}

.expiring-table td {
    padding: 14px 18px;
    border-bottom: 1px solid #F1F5F9;
    font-size: 0.9rem;
}

.expiring-table tr:hover td {
    background: #F8FAFC;
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-info small {
    color: var(--text-muted);
    font-size: 0.8rem;
    margin-top: 2px;
}

.plan-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 600;
}

.plan-badge.monthly { background: #EFF6FF; color: #1E40AF; }
.plan-badge.termly { background: #FEF3C7; color: #92400E; }
.plan-badge.yearly { background: #F0FDF4; color: #166534; }

.days-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 600;
    background: #F1F5F9;
    color: var(--text-dark);
}

.days-badge.urgent {
    background: #FEF2F2;
    color: #B91C1C;
    animation: alertPulse 2s infinite;
}

.btn-view {
    padding: 6px 14px;
    background: #EFF6FF;
    color: var(--primary-purple);
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: var(--transition);
}

.btn-view:hover {
    background: var(--accent-orange);
    color: white;
}

.plan-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
}

.plan-detail-card {
    border-radius: var(--radius-md);
    overflow: hidden;
    border: 1px solid var(--border-color);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

.plan-header {
    padding: 16px;
    text-align: center;
}

.plan-header h4 {
    color: white;
    font-size: 1.1rem;
    margin: 0;
    font-weight: 600;
}

.plan-stats {
    padding: 16px;
    background: #F8FAFC;
}

.plan-stat {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--border-color);
}

.plan-stat:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.stat-label {
    color: var(--text-muted);
    font-size: 0.85rem;
}

.stat-value {
    font-weight: 700;
    color: var(--text-dark);
    font-size: 0.95rem;
}

.empty-message {
    text-align: center;
    padding: 40px;
    color: var(--text-muted);
}

.empty-message i {
    font-size: 2.5rem;
    margin-bottom: 12px;
    color: var(--accent-orange);
    opacity: 0.8;
}

@keyframes alertPulse {
    0% { opacity: 1; }
    50% { opacity: 0.65; }
    100% { opacity: 1; }
}

@media (max-width: 768px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .page-header .header-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>