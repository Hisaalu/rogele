<?php
// File: /views/admin/subscriptions/reports.php
$pageTitle = 'Subscription Reports - Admin - Rays of Grace';
require_once __DIR__ . '/../../layouts/header.php';

// Get data from controller
$stats = $stats ?? [];
$expiring = $expiring ?? [];
$revenueByMonth = $revenueByMonth ?? [];
?>

<div class="reports-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-chart-bar"></i>
                Subscription Reports
            </h1>
            <p class="page-subtitle">Analytics and insights for all subscriptions</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo BASE_URL; ?>/admin/subscriptions" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Back to Subscriptions
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/subscriptions/export?report=true" class="btn-export">
                <i class="fas fa-download"></i>
                Export Report
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon active">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3>Active Subscriptions</h3>
                <p class="stat-number"><?php echo $stats['active'] ?? 0; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon expired">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3>Expired</h3>
                <p class="stat-number"><?php echo $stats['expired'] ?? 0; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon revenue">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-info">
                <h3>Total Revenue</h3>
                <p class="stat-number">UGX <?php echo number_format($stats['total_revenue'] ?? 0); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon monthly">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-info">
                <h3>This Month</h3>
                <p class="stat-number">UGX <?php echo number_format($stats['monthly_revenue'] ?? 0); ?></p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
        <!-- Revenue Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-line"></i> Revenue Overview (Last 12 Months)</h3>
            </div>
            <div class="chart-body">
                <canvas id="revenueChart" style="width:100%; height:300px;"></canvas>
            </div>
        </div>

        <!-- Plan Distribution Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-pie-chart"></i> Plan Distribution</h3>
            </div>
            <div class="chart-body">
                <canvas id="planChart" style="width:100%; height:300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Expiring Soon -->
    <div class="expiring-section">
        <h2 class="section-title">
            <i class="fas fa-exclamation-triangle"></i>
            Subscriptions Expiring Soon (Next 30 Days)
        </h2>
        
        <?php if (empty($expiring)): ?>
            <div class="empty-message">
                <i class="fas fa-calendar-check"></i>
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
                            $daysLeft = floor((strtotime($sub['end_date']) - time()) / 86400);
                        ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <strong><?php echo htmlspecialchars($sub['first_name'] . ' ' . $sub['last_name']); ?></strong>
                                    <small><?php echo htmlspecialchars($sub['email']); ?></small>
                                </div>
                            </td>
                            <td>
                                <span class="plan-badge <?php echo $sub['plan_type']; ?>">
                                    <?php echo ucfirst($sub['plan_type']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($sub['end_date'])); ?></td>
                            <td>
                                <span class="days-badge <?php echo $daysLeft <= 7 ? 'urgent' : ''; ?>">
                                    <?php echo $daysLeft; ?> days
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/admin/subscriptions/view/<?php echo $sub['id']; ?>" class="btn-view">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Plan Distribution Details -->
    <div class="plan-details-section">
        <h2 class="section-title">
            <i class="fas fa-chart-pie"></i>
            Plan Distribution Details
        </h2>
        
        <div class="plan-details-grid">
            <?php 
            $planDistribution = $stats['plan_distribution'] ?? [];
            $planColors = [
                'monthly' => '#3B82F6',
                'termly' => '#F59E0B',
                'yearly' => '#10B981'
            ];
            
            foreach ($planDistribution as $plan): 
                $color = $planColors[$plan['plan_type']] ?? '#64748B';
            ?>
            <div class="plan-detail-card">
                <div class="plan-header" style="background: <?php echo $color; ?>">
                    <h4><?php echo ucfirst($plan['plan_type']); ?></h4>
                </div>
                <div class="plan-stats">
                    <div class="plan-stat">
                        <span class="stat-label">Active Subscribers:</span>
                        <span class="stat-value"><?php echo $plan['count']; ?></span>
                    </div>
                    <div class="plan-stat">
                        <span class="stat-label">Revenue:</span>
                        <span class="stat-value">UGX <?php echo number_format($plan['total']); ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueData = <?php echo json_encode(array_reverse($revenueByMonth)); ?>;

new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: revenueData.map(item => {
            const [year, month] = item.month.split('-');
            const date = new Date(year, month - 1);
            return date.toLocaleString('default', { month: 'short', year: 'numeric' });
        }),
        datasets: [{
            label: 'Revenue (UGX)',
            data: revenueData.map(item => item.revenue),
            borderColor: '#8B5CF6',
            backgroundColor: 'rgba(139, 92, 246, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
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
                    callback: function(value) {
                        return 'UGX ' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Plan Distribution Chart
const planCtx = document.getElementById('planChart').getContext('2d');
const planData = <?php echo json_encode($stats['plan_distribution'] ?? []); ?>;

new Chart(planCtx, {
    type: 'doughnut',
    data: {
        labels: planData.map(item => ucfirst(item.plan_type) + ' (' + item.count + ')'),
        datasets: [{
            data: planData.map(item => item.count),
            backgroundColor: ['#3B82F6', '#F59E0B', '#10B981'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Helper function
function ucfirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}
</script>

<style>
.reports-container {
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
    font-size: 2.2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
}

.page-subtitle {
    color: #64748B;
    font-size: 1rem;
}

.header-actions {
    display: flex;
    gap: 15px;
}

.btn-back, .btn-export {
    padding: 12px 25px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-back {
    background: #F1F5F9;
    color: #1E293B;
}

.btn-export {
    background: #8B5CF6;
    color: white;
}

.btn-back:hover, .btn-export:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}

.stat-icon.active {
    background: #F0FDF4;
    color: #10B981;
}

.stat-icon.expired {
    background: #FEF2F2;
    color: #EF4444;
}

.stat-icon.revenue {
    background: #EFF6FF;
    color: #3B82F6;
}

.stat-icon.monthly {
    background: #FEF3C7;
    color: #F59E0B;
}

.stat-info h3 {
    font-size: 0.9rem;
    color: #64748B;
    margin-bottom: 5px;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1E293B;
}

/* Charts */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.chart-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.chart-header {
    margin-bottom: 20px;
}

.chart-header h3 {
    color: #1E293B;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chart-header h3 i {
    color: #8B5CF6;
}

.chart-body {
    height: 300px;
    position: relative;
}

/* Expiring Section */
.expiring-section {
    background: white;
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 40px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.section-title {
    color: #1E293B;
    font-size: 1.3rem;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: #F59E0B;
}

.expiring-table {
    width: 100%;
    border-collapse: collapse;
}

.expiring-table th {
    background: #F8FAFC;
    padding: 15px 20px;
    text-align: left;
    font-weight: 600;
    color: #1E293B;
    border-bottom: 2px solid #E2E8F0;
}

.expiring-table td {
    padding: 15px 20px;
    border-bottom: 1px solid #F1F5F9;
}

.expiring-table tr:hover td {
    background: #F8FAFC;
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-info small {
    color: #64748B;
    font-size: 0.8rem;
    margin-top: 3px;
}

.plan-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
}

.plan-badge.monthly {
    background: #EFF6FF;
    color: #1E40AF;
}

.plan-badge.termly {
    background: #FEF3C7;
    color: #92400E;
}

.plan-badge.yearly {
    background: #F0FDF4;
    color: #166534;
}

.days-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    background: #F1F5F9;
    color: #1E293B;
}

.days-badge.urgent {
    background: #FEF2F2;
    color: #B91C1C;
    animation: pulse 2s infinite;
}

.btn-view {
    padding: 6px 15px;
    background: #EFF6FF;
    color: #2563EB;
    text-decoration: none;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-view:hover {
    background: #2563EB;
    color: white;
}

/* Plan Details */
.plan-details-section {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.plan-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.plan-detail-card {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.plan-header {
    padding: 20px;
    text-align: center;
}

.plan-header h4 {
    color: white;
    font-size: 1.2rem;
    margin: 0;
}

.plan-stats {
    padding: 20px;
    background: #F8FAFC;
}

.plan-stat {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #E2E8F0;
}

.plan-stat:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.stat-label {
    color: #64748B;
    font-size: 0.9rem;
}

.stat-value {
    font-weight: 700;
    color: #1E293B;
}

/* Empty Message */
.empty-message {
    text-align: center;
    padding: 50px;
    color: #94A3B8;
}

.empty-message i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

/* Animations */
@keyframes pulse {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
    100% {
        opacity: 1;
    }
}

/* Responsive */
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
    
    .header-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .expiring-table {
        font-size: 0.85rem;
    }
    
    .expiring-table th,
    .expiring-table td {
        padding: 10px;
    }
}

/* Dark Mode */
/* @media (prefers-color-scheme: dark) {
    .stat-card,
    .chart-card,
    .expiring-section,
    .plan-details-section {
        background: #1E293B;
    }
    
    .stat-number,
    .chart-header h3,
    .section-title {
        color: #F1F5F9;
    }
    
    .expiring-table th {
        background: #334155;
        color: #F1F5F9;
    }
    
    .expiring-table td {
        color: #F1F5F9;
        border-bottom-color: #334155;
    }
    
    .expiring-table tr:hover td {
        background: #334155;
    }
    
    .plan-stats {
        background: #334155;
    }
    
    .stat-label {
        color: #94A3B8;
    }
    
    .stat-value {
        color: #F1F5F9;
    }
} */
</style>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>