<?php
// File: /controllers/ExportController.php - Optimized Export Controller
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/Settings.php';
require_once __DIR__ . '/../vendor/tcpdf/tcpdf.php';

class ExportController {
    private $userModel;
    private $reportModel;
    private $quizModel;
    private $subscriptionModel;
    private $settingsModel;
    private $cachedTotalUsers = null;
    private $cachedTotalTeachers = null;
    private $cachedTotalLearners = null;
    private $cachedTotalExternal = null;
    private $cachedTotalAdmins = null;
    
    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect(BASE_URL . '/login');
        }
        
        $this->userModel = new User();
        $this->reportModel = new Report();
        $this->quizModel = new Quiz();
        $this->subscriptionModel = new Subscription();
        $this->settingsModel = new Settings();
        
        if (ob_get_length()) ob_clean();
    }
    
    // ==================== HELPER METHODS ====================  
    private function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    private function getSiteName() {
        $settings = $this->settingsModel->getGeneralSettings();
        return $settings['site_name'] ?? 'ROGELE';
    }
    
    private function calculateDaysDifference($start_date, $end_date) {
        $date1 = new DateTime($start_date);
        $date2 = new DateTime($end_date);
        return $date1->diff($date2)->days + 1;
    }
    
    private function formatDateRange($start_date, $end_date) {
        return date('M d, Y', strtotime($start_date)) . ' - ' . date('M d, Y', strtotime($end_date));
    }
    
    private function getCachedUserStats() {
        if ($this->cachedTotalUsers === null) {
            $this->cachedTotalUsers = count($this->userModel->getAllUsers(null, 0, 0));
            $this->cachedTotalTeachers = count($this->userModel->getAllUsers('teacher', 0, 0));
            $this->cachedTotalLearners = count($this->userModel->getAllUsers('learner', 0, 0));
            $this->cachedTotalExternal = count($this->userModel->getAllUsers('external', 0, 0));
            $this->cachedTotalAdmins = count($this->userModel->getAllUsers('admin', 0, 0));
        }
        
        return [
            'total_users' => $this->cachedTotalUsers,
            'total_teachers' => $this->cachedTotalTeachers,
            'total_learners' => $this->cachedTotalLearners,
            'total_external' => $this->cachedTotalExternal,
            'total_admins' => $this->cachedTotalAdmins
        ];
    }
    
    private function initializePDF($title, $subtitle = null) {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('ROGELE');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle($title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        
        return $pdf;
    }
    
    private function addPDFHeader($pdf, $reportTitle, $dateRange) {
        $siteName = $this->getSiteName();
        
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetTextColor(139, 92, 246);
        $pdf->Cell(0, 20, $siteName, 0, 1, 'C');
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(249, 115, 22);
        $pdf->Cell(0, 10, $reportTitle, 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'Date Range: ' . $dateRange, 0, 1, 'C');
        $pdf->Ln(10);
    }
    
    private function addPDFFooter($pdf) {
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, 'Generated on ' . date('F j, Y H:i:s'), 0, 1, 'C');
    }
    
    private function createUserStatsHTML() {
        $stats = $this->getCachedUserStats();
        
        return '
        <style>
            table { border-collapse: collapse; width: 100%; }
            th { background-color: #7f2677; color: white; padding: 10px; text-align: left; }
            td { padding: 8px; border-bottom: 1px solid #ddd; }
        </style>
        <table border="1" cellpadding="5">
            <thead><tr><th>Metric</th><th>Count</th></tr></thead>
            <tbody>
                <tr><td>Total Users</td><td>' . number_format($stats['total_users']) . '</td></tr>
                <tr><td>Administrators</td><td>' . number_format($stats['total_admins']) . '</td></tr>
                <tr><td>Teachers</td><td>' . number_format($stats['total_teachers']) . '</td></tr>
                <tr><td>Learners</td><td>' . number_format($stats['total_learners']) . '</td></tr>
                <tr><td>External Users</td><td>' . number_format($stats['total_external']) . '</td></tr>
            </tbody>
        </table>';
    }
    
    private function createDataTableHTML($data, $headers, $formatCallbacks = []) {
        if (empty($data)) {
            return '<p>No data available for the selected date range.</p>';
        }
        
        $html = '<table border="1" cellpadding="4">
            <thead><tr>';
        
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        
        $html .= '</tr></thead><tbody>';
        
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($headers as $index => $header) {
                $field = strtolower(str_replace(' ', '_', $header));
                $value = $row[$field] ?? $row[array_search($header, array_keys($row))] ?? '';
                
                if (isset($formatCallbacks[$index])) {
                    $value = $formatCallbacks[$index]($value, $row);
                }
                
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        return $html;
    }
    
    private function addSectionHeader($pdf, $title) {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(249, 115, 22);
        $pdf->Cell(0, 10, $title, 0, 1, 'L');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 11);
    }
    
    // ==================== EXPORT METHODS ====================
    private function exportOverview($start_date, $end_date, $days) {
        $stats = $this->getCachedUserStats();
        $recentActivity = $this->reportModel->getRecentActivity(10);
        $userGrowthData = $this->reportModel->getUserGrowthData($days);
        $revenueData = $this->reportModel->getRevenueData($days);
        
        $pdf = $this->initializePDF('Overview Report');
        $this->addPDFHeader($pdf, 'Overview Report', $this->formatDateRange($start_date, $end_date));
        
        $this->addSectionHeader($pdf, 'Platform Statistics');
        $pdf->writeHTML($this->createUserStatsHTML(), true, false, true, false, '');
        $pdf->Ln(10);
        
        if (!empty($userGrowthData)) {
            $this->addSectionHeader($pdf, 'User Growth (Last ' . $days . ' days)');
            $pdf->SetFont('helvetica', '', 10);
            
            $growthHtml = '<table border="1" cellpadding="4">
                <thead><tr><th>Date</th><th>New Users</th></tr></thead>
                <tbody>';
            
            foreach ($userGrowthData as $row) {
                $growthHtml .= '<tr>
                    <td>' . date('M d, Y', strtotime($row['date'])) . '</td>
                    <td>' . $row['new_users'] . '</td>
                </tr>';
            }
            
            $growthHtml .= '</tbody></table>';
            $pdf->writeHTML($growthHtml, true, false, true, false, '');
            $pdf->Ln(10);
        }
        
        if (!empty($revenueData)) {
            $this->addSectionHeader($pdf, 'Revenue (Last ' . $days . ' days)');
            $pdf->SetFont('helvetica', '', 10);
            
            $revenueHtml = '<table border="1" cellpadding="4">
                <thead><tr><th>Date</th><th>Revenue (UGX)</th></tr></thead>
                <tbody>';
            
            foreach ($revenueData as $row) {
                $revenueHtml .= '<tr>
                    <td>' . date('M d, Y', strtotime($row['date'])) . '</td>
                    <td>UGX ' . number_format($row['revenue']) . '</td>
                </tr>';
            }
            
            $revenueHtml .= '</tbody></table>';
            $pdf->writeHTML($revenueHtml, true, false, true, false, '');
            $pdf->Ln(10);
        }
        
        if (!empty($recentActivity)) {
            $this->addSectionHeader($pdf, 'Recent Activity');
            $pdf->SetFont('helvetica', '', 9);
            
            $activityHtml = '<table border="1" cellpadding="4">
                <thead><tr><th>User</th><th>Action</th><th>Time</th></tr></thead>
                <tbody>';
            
            foreach ($recentActivity as $activity) {
                $activityHtml .= '<tr>
                    <td>' . htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']) . '</td>
                    <td>' . htmlspecialchars($activity['description']) . '</td>
                    <td>' . date('M d, Y H:i', strtotime($activity['created_at'])) . '</td>
                </tr>';
            }
            
            $activityHtml .= '</tbody></table>';
            $pdf->writeHTML($activityHtml, true, false, true, false, '');
        }
        
        $this->addPDFFooter($pdf);
        $pdf->Output('Overview_Report_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }
    
    private function exportPayments($start_date, $end_date) {
        $data = $this->reportModel->getPaymentReport($start_date, $end_date);
        
        $pdf = $this->initializePDF('Revenue Report');
        $this->addPDFHeader($pdf, 'Revenue Report', $this->formatDateRange($start_date, $end_date));
        
        if (!empty($data)) {
            $totalRevenue = array_sum(array_column($data, 'total_amount'));
            $totalTransactions = array_sum(array_column($data, 'transaction_count'));
            $avgAmount = $totalTransactions > 0 ? round($totalRevenue / $totalTransactions) : 0;
            
            $this->addSectionHeader($pdf, 'Revenue Summary');
            
            $summaryHtml = '
            <table border="1" cellpadding="6">
                <tr><td><strong>Total Revenue:</strong></td><td>UGX ' . number_format($totalRevenue) . '</td></tr>
                <tr><td><strong>Total Transactions:</strong></td><td>' . number_format($totalTransactions) . '</td></tr>
                <tr><td><strong>Average Transaction Value:</strong></td><td>UGX ' . number_format($avgAmount) . '</td></tr>
            </table>';
            
            $pdf->writeHTML($summaryHtml, true, false, true, false, '');
            $pdf->Ln(10);
            
            $this->addSectionHeader($pdf, 'Transaction Details');
            $pdf->SetFont('helvetica', '', 10);
            
            $detailsHtml = '<table border="1" cellpadding="4">
                <thead><tr><th>Date</th><th>Transactions</th><th>Total Amount</th><th>Avg Amount</th><th>Payment Method</th></tr></thead>
                <tbody>';
            
            foreach ($data as $row) {
                $detailsHtml .= '<tr>
                    <td>' . date('M d, Y', strtotime($row['date'])) . '</td>
                    <td>' . number_format($row['transaction_count']) . '</td>
                    <td>UGX ' . number_format($row['total_amount']) . '</td>
                    <td>UGX ' . number_format($row['avg_amount']) . '</td>
                    <td>' . htmlspecialchars($row['payment_method']) . '</td>
                </tr>';
            }
            
            $detailsHtml .= '</tbody></table>';
            $pdf->writeHTML($detailsHtml, true, false, true, false, '');
        } else {
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'No payment data available for the selected date range.', 0, 1, 'C');
        }
        
        $this->addPDFFooter($pdf);
        $pdf->Output('Revenue_Report_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }
    
    private function exportActivity($start_date, $end_date) {
        $data = $this->reportModel->getActivityReport($start_date, $end_date);
        
        $pdf = $this->initializePDF('Activity Log');
        $this->addPDFHeader($pdf, 'Activity Log', $this->formatDateRange($start_date, $end_date));
        
        if (!empty($data)) {
            $actionCounts = [];
            foreach ($data as $row) {
                $action = $row['action'];
                $actionCounts[$action] = ($actionCounts[$action] ?? 0) + $row['count'];
            }
            
            $this->addSectionHeader($pdf, 'Activity Summary');
            
            $summaryHtml = '<table border="1" cellpadding="6">
                <thead><tr><th>Action Type</th><th>Count</th></tr></thead>
                <tbody>';
            
            foreach ($actionCounts as $action => $count) {
                $summaryHtml .= '<tr>
                    <td>' . str_replace('_', ' ', $action) . '</td>
                    <td>' . number_format($count) . '</td>
                </tr>';
            }
            
            $summaryHtml .= '</tbody></table>';
            $pdf->writeHTML($summaryHtml, true, false, true, false, '');
            $pdf->Ln(10);
            
            $this->addSectionHeader($pdf, 'Activity Timeline');
            $pdf->SetFont('helvetica', '', 10);
            
            $timelineHtml = '<table border="1" cellpadding="4">
                <thead><tr><th>Date</th><th>Action</th><th>Count</th></tr></thead>
                <tbody>';
            
            foreach ($data as $row) {
                $timelineHtml .= '<tr>
                    <td>' . date('M d, Y', strtotime($row['date'])) . '</td>
                    <td>' . str_replace('_', ' ', $row['action']) . '</td>
                    <td>' . number_format($row['count']) . '</td>
                </tr>';
            }
            
            $timelineHtml .= '</tbody></table>';
            $pdf->writeHTML($timelineHtml, true, false, true, false, '');
        } else {
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'No activity data available for the selected date range.', 0, 1, 'C');
        }
        
        $this->addPDFFooter($pdf);
        $pdf->Output('Activity_Report_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }
    
    public function export() {
        $type = $_GET['type'] ?? 'overview';
        $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        $days = $this->calculateDaysDifference($start_date, $end_date);
        
        $exportMethods = [
            'payments' => 'exportPayments',
            'activity' => 'exportActivity',
            'overview' => 'exportOverview'
        ];
        
        $method = $exportMethods[$type] ?? 'exportOverview';
        $this->$method($start_date, $end_date, $days);
    }
}
?>