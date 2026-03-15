<?php
// File: /controllers/ChartApiController.php
require_once __DIR__ . '/../models/Report.php';

class ChartApiController {
    private $reportModel;
    
    public function __construct() {
        // Check if user is logged in and is admin
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $this->reportModel = new Report();
    }
    
    public function chartData() {
        $type = $_GET['type'] ?? 'growth';
        $days = intval($_GET['days'] ?? 30);
        
        header('Content-Type: application/json');
        
        try {
            if ($type === 'growth') {
                $data = $this->reportModel->getUserGrowthData($days);
                
                $labels = [];
                $values = [];
                
                foreach ($data as $row) {
                    $labels[] = date('M d', strtotime($row['date']));
                    $values[] = intval($row['new_users']);
                }
                
                echo json_encode([
                    'labels' => $labels,
                    'values' => $values
                ]);
            } 
            else if ($type === 'revenue') {
                $data = $this->reportModel->getRevenueData($days);
                
                $labels = [];
                $values = [];
                
                foreach ($data as $row) {
                    $labels[] = date('M d', strtotime($row['date']));
                    $values[] = intval($row['revenue']);
                }
                
                echo json_encode([
                    'labels' => $labels,
                    'values' => $values
                ]);
            }
            else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid chart type']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}