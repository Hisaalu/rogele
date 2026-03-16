<?php
// File: /controllers/ExportController.php
// NO WHITESPACE BEFORE THIS TAG - NOT EVEN A SINGLE SPACE!

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/Settings.php';

// Include TCPDF
require_once __DIR__ . '/../vendor/tcpdf/tcpdf.php';

class ExportController {
    private $userModel;
    private $reportModel;
    private $quizModel;
    private $subscriptionModel;
    private $settingsModel;
    
    public function __construct() {
        // Check if user is logged in and is admin
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        $this->userModel = new User();
        $this->reportModel = new Report();
        $this->quizModel = new Quiz();
        $this->subscriptionModel = new Subscription();
        $this->settingsModel = new Settings();
        
        // VERY IMPORTANT: Clean output buffer to prevent any previous output
        if (ob_get_length()) ob_clean();
    }
    
    /**
     * Export report as PDF
     */
    public function exportReport() {
        // Clean all output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        $type = $_GET['type'] ?? 'overview';
        $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        $days = $_GET['days'] ?? 30;
        
        // Get data based on type
        switch ($type) {
            case 'overview':
                $this->exportOverview($start_date, $end_date, $days);
                break;
            case 'users':
                $this->exportUsers($start_date, $end_date);
                break;
            case 'quizzes':
                $this->exportQuizzes($start_date, $end_date);
                break;
            case 'payments':
                $this->exportPayments($start_date, $end_date);
                break;
            case 'activity':
                $this->exportActivity($start_date, $end_date);
                break;
            default:
                header('Location: ' . BASE_URL . '/admin/reports');
                exit;
        }
    }
    
    /**
     * Export Overview Report
     */
    private function exportOverview($start_date, $end_date, $days) {
        // Get statistics
        $totalUsers = count($this->userModel->getAllUsers(null, 0, 0));
        $totalTeachers = count($this->userModel->getAllUsers('teacher', 0, 0));
        $totalLearners = count($this->userModel->getAllUsers('learner', 0, 0));
        $totalExternal = count($this->userModel->getAllUsers('external', 0, 0));
        $totalAdmins = count($this->userModel->getAllUsers('admin', 0, 0));
        
        $recentActivity = $this->reportModel->getRecentActivity(10);
        $userGrowthData = $this->reportModel->getUserGrowthData($days);
        $revenueData = $this->reportModel->getRevenueData($days);
        
        // Get settings for site name
        $settings = $this->settingsModel->getGeneralSettings();
        $siteName = $settings['site_name'] ?? 'Rays of Grace';
        
        // Create PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('Rays of Grace');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle('Overview Report');
        $pdf->SetSubject('Platform Analytics');
        $pdf->SetKeywords('report, analytics, overview');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 11);
        
        // Logo and Title
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetTextColor(139, 92, 246); // Purple
        $pdf->Cell(0, 20, $siteName, 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(249, 115, 22); // Orange
        $pdf->Cell(0, 10, 'Overview Report', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'Date Range: ' . date('M d, Y', strtotime($start_date)) . ' - ' . date('M d, Y', strtotime($end_date)), 0, 1, 'C');
        $pdf->Ln(10);
        
        // Statistics Table
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(249, 115, 22); // Orange
        $pdf->Cell(0, 10, 'Platform Statistics', 0, 1, 'L');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        
        $html = '
        <style>
            table { border-collapse: collapse; width: 100%; }
            th { background-color: #8B5CF6; color: white; padding: 10px; text-align: left; }
            td { padding: 8px; border-bottom: 1px solid #ddd; }
            .total-row { background-color: #f0f0f0; font-weight: bold; }
        </style>
        <table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Users</td>
                    <td>' . number_format($totalUsers) . '</td>
                </tr>
                <tr>
                    <td>Administrators</td>
                    <td>' . number_format($totalAdmins) . '</td>
                </tr>
                <tr>
                    <td>Teachers</td>
                    <td>' . number_format($totalTeachers) . '</td>
                </tr>
                <tr>
                    <td>Learners</td>
                    <td>' . number_format($totalLearners) . '</td>
                </tr>
                <tr>
                    <td>External Users</td>
                    <td>' . number_format($totalExternal) . '</td>
                </tr>
            </tbody>
        </table>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Ln(10);
        
        // User Growth Data
        if (!empty($userGrowthData)) {
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->SetTextColor(249, 115, 22);
            $pdf->Cell(0, 10, 'User Growth (Last ' . $days . ' days)', 0, 1, 'L');
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 10);
            
            $html = '<table border="1" cellpadding="4">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>New Users</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($userGrowthData as $row) {
                $html .= '<tr>
                    <td>' . date('M d, Y', strtotime($row['date'])) . '</td>
                    <td>' . $row['new_users'] . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(10);
        }
        
        // Revenue Data
        if (!empty($revenueData)) {
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->SetTextColor(249, 115, 22);
            $pdf->Cell(0, 10, 'Revenue (Last ' . $days . ' days)', 0, 1, 'L');
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 10);
            
            $html = '<table border="1" cellpadding="4">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Revenue (UGX)</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($revenueData as $row) {
                $html .= '<tr>
                    <td>' . date('M d, Y', strtotime($row['date'])) . '</td>
                    <td>UGX ' . number_format($row['revenue']) . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(10);
        }
        
        // Recent Activity
        if (!empty($recentActivity)) {
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->SetTextColor(249, 115, 22);
            $pdf->Cell(0, 10, 'Recent Activity', 0, 1, 'L');
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 9);
            
            $html = '<table border="1" cellpadding="4">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($recentActivity as $activity) {
                $html .= '<tr>
                    <td>' . htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']) . '</td>
                    <td>' . htmlspecialchars($activity['description']) . '</td>
                    <td>' . date('M d, Y H:i', strtotime($activity['created_at'])) . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table>';
            $pdf->writeHTML($html, true, false, true, false, '');
        }
        
        // Footer
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, 'Generated on ' . date('F j, Y H:i:s'), 0, 1, 'C');
        
        // Output PDF - Use 'D' for download, 'I' for inline, 'F' for file
        $pdf->Output('Overview_Report_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }
    
    /**
     * Export Quizzes Report
     */
    private function exportQuizzes($start_date, $end_date) {
        $data = $this->reportModel->getQuizReport($start_date, $end_date);
        
        $settings = $this->settingsModel->getGeneralSettings();
        $siteName = $settings['site_name'] ?? 'Rays of Grace';
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        
        // Title
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetTextColor(139, 92, 246);
        $pdf->Cell(0, 20, $siteName, 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(249, 115, 22);
        $pdf->Cell(0, 10, 'Quiz Performance Report', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'Date Range: ' . date('M d, Y', strtotime($start_date)) . ' - ' . date('M d, Y', strtotime($end_date)), 0, 1, 'C');
        $pdf->Ln(10);
        
        if (!empty($data)) {
            // Calculate totals
            $totalAttempts = array_sum(array_column($data, 'total_attempts'));
            $uniqueStudents = array_sum(array_column($data, 'unique_students'));
            $avgScore = count($data) > 0 ? round(array_sum(array_column($data, 'avg_score')) / count($data), 1) : 0;
            $totalPassed = array_sum(array_column($data, 'passed_count'));
            $overallPassRate = $totalAttempts > 0 ? round(($totalPassed / $totalAttempts) * 100, 1) : 0;
            
            // Summary
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->SetTextColor(249, 115, 22);
            $pdf->Cell(0, 10, 'Summary Statistics', 0, 1, 'L');
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 11);
            
            $html = '
            <table border="1" cellpadding="6">
                <tr>
                    <td><strong>Total Attempts:</strong></td>
                    <td>' . number_format($totalAttempts) . '</td>
                </tr>
                <tr>
                    <td><strong>Unique Students:</strong></td>
                    <td>' . number_format($uniqueStudents) . '</td>
                </tr>
                <tr>
                    <td><strong>Average Score:</strong></td>
                    <td>' . $avgScore . '%</td>
                </tr>
                <tr>
                    <td><strong>Overall Pass Rate:</strong></td>
                    <td>' . $overallPassRate . '%</td>
                </tr>
            </table>';
            
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(10);
            
            // Detailed Data
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->SetTextColor(249, 115, 22);
            $pdf->Cell(0, 10, 'Quiz Details', 0, 1, 'L');
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 9);
            
            $html = '<table border="1" cellpadding="4">
                <thead>
                    <tr>
                        <th>Quiz Title</th>
                        <th>Attempts</th>
                        <th>Students</th>
                        <th>Avg Score</th>
                        <th>Highest</th>
                        <th>Lowest</th>
                        <th>Pass Rate</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($data as $row) {
                $passRate = $row['total_attempts'] > 0 ? round(($row['passed_count'] / $row['total_attempts']) * 100, 1) : 0;
                $html .= '<tr>
                    <td>' . htmlspecialchars($row['title']) . '</td>
                    <td>' . number_format($row['total_attempts']) . '</td>
                    <td>' . number_format($row['unique_students']) . '</td>
                    <td>' . round($row['avg_score'], 1) . '%</td>
                    <td>' . round($row['highest_score'], 1) . '%</td>
                    <td>' . round($row['lowest_score'], 1) . '%</td>
                    <td>' . $passRate . '%</td>
                </tr>';
            }
            
            $html .= '</tbody></table>';
            $pdf->writeHTML($html, true, false, true, false, '');
        } else {
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'No quiz data available for the selected date range.', 0, 1, 'C');
        }
        
        // Footer
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, 'Generated on ' . date('F j, Y H:i:s'), 0, 1, 'C');
        
        $pdf->Output('Quizzes_Report_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }
    
    /**
     * Export Payments Report
     */
    private function exportPayments($start_date, $end_date) {
        $data = $this->reportModel->getPaymentReport($start_date, $end_date);
        
        $settings = $this->settingsModel->getGeneralSettings();
        $siteName = $settings['site_name'] ?? 'Rays of Grace';
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        
        // Title
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetTextColor(139, 92, 246);
        $pdf->Cell(0, 20, $siteName, 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(249, 115, 22);
        $pdf->Cell(0, 10, 'Revenue Report', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'Date Range: ' . date('M d, Y', strtotime($start_date)) . ' - ' . date('M d, Y', strtotime($end_date)), 0, 1, 'C');
        $pdf->Ln(10);
        
        if (!empty($data)) {
            $totalRevenue = array_sum(array_column($data, 'total_amount'));
            $totalTransactions = array_sum(array_column($data, 'transaction_count'));
            $avgAmount = $totalTransactions > 0 ? round($totalRevenue / $totalTransactions) : 0;
            
            // Summary
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->SetTextColor(249, 115, 22);
            $pdf->Cell(0, 10, 'Revenue Summary', 0, 1, 'L');
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 11);
            
            $html = '
            <table border="1" cellpadding="6">
                <tr>
                    <td><strong>Total Revenue:</strong></td>
                    <td>UGX ' . number_format($totalRevenue) . '</td>
                </tr>
                <tr>
                    <td><strong>Total Transactions:</strong></td>
                    <td>' . number_format($totalTransactions) . '</td>
                </tr>
                <tr>
                    <td><strong>Average Transaction Value:</strong></td>
                    <td>UGX ' . number_format($avgAmount) . '</td>
                </tr>
            </table>';
            
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(10);
            
            // Detailed Data
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->SetTextColor(249, 115, 22);
            $pdf->Cell(0, 10, 'Transaction Details', 0, 1, 'L');
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 10);
            
            $html = '<table border="1" cellpadding="4">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transactions</th>
                        <th>Total Amount</th>
                        <th>Avg Amount</th>
                        <th>Payment Method</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($data as $row) {
                $html .= '<tr>
                    <td>' . date('M d, Y', strtotime($row['date'])) . '</td>
                    <td>' . number_format($row['transaction_count']) . '</td>
                    <td>UGX ' . number_format($row['total_amount']) . '</td>
                    <td>UGX ' . number_format($row['avg_amount']) . '</td>
                    <td>' . htmlspecialchars($row['payment_method']) . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table>';
            $pdf->writeHTML($html, true, false, true, false, '');
        } else {
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'No payment data available for the selected date range.', 0, 1, 'C');
        }
        
        // Footer
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, 'Generated on ' . date('F j, Y H:i:s'), 0, 1, 'C');
        
        $pdf->Output('Revenue_Report_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }
    
    /**
     * Export Activity Report
     */
    private function exportActivity($start_date, $end_date) {
        $data = $this->reportModel->getActivityReport($start_date, $end_date);
        
        $settings = $this->settingsModel->getGeneralSettings();
        $siteName = $settings['site_name'] ?? 'Rays of Grace';
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        
        // Title
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetTextColor(139, 92, 246);
        $pdf->Cell(0, 20, $siteName, 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(249, 115, 22);
        $pdf->Cell(0, 10, 'Activity Log', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'Date Range: ' . date('M d, Y', strtotime($start_date)) . ' - ' . date('M d, Y', strtotime($end_date)), 0, 1, 'C');
        $pdf->Ln(10);
        
        if (!empty($data)) {
            // Calculate totals by action type
            $actionCounts = [];
            foreach ($data as $row) {
                if (!isset($actionCounts[$row['action']])) {
                    $actionCounts[$row['action']] = 0;
                }
                $actionCounts[$row['action']] += $row['count'];
            }
            
            // Summary by action type
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->SetTextColor(249, 115, 22);
            $pdf->Cell(0, 10, 'Activity Summary', 0, 1, 'L');
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 11);
            
            $html = '<table border="1" cellpadding="6">
                <thead>
                    <tr>
                        <th>Action Type</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($actionCounts as $action => $count) {
                $html .= '<tr>
                    <td>' . str_replace('_', ' ', $action) . '</td>
                    <td>' . number_format($count) . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(10);
            
            // Detailed Timeline
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->SetTextColor(249, 115, 22);
            $pdf->Cell(0, 10, 'Activity Timeline', 0, 1, 'L');
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 10);
            
            $html = '<table border="1" cellpadding="4">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($data as $row) {
                $html .= '<tr>
                    <td>' . date('M d, Y', strtotime($row['date'])) . '</td>
                    <td>' . str_replace('_', ' ', $row['action']) . '</td>
                    <td>' . number_format($row['count']) . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table>';
            $pdf->writeHTML($html, true, false, true, false, '');
        } else {
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'No activity data available for the selected date range.', 0, 1, 'C');
        }
        
        // Footer
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, 'Generated on ' . date('F j, Y H:i:s'), 0, 1, 'C');
        
        $pdf->Output('Activity_Report_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }
}