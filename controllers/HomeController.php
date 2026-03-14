<?php
// File: /controllers/HomeController.php
class HomeController {
    public function index() {
        // If user is logged in, redirect to their dashboard
        // if (isset($_SESSION['user_id'])) {
        //     switch ($_SESSION['user_role']) {
        //         case 'admin':
        //             header('Location: ' . BASE_URL . '/admin/dashboard');
        //             break;
        //         case 'teacher':
        //             header('Location: ' . BASE_URL . '/teacher/dashboard');
        //             break;
        //         case 'learner':
        //             header('Location: ' . BASE_URL . '/learner/dashboard');
        //             break;
        //         case 'external':
        //             header('Location: ' . BASE_URL . '/external/dashboard');
        //             break;
        //     }
        //     exit;
        // }
        
        // Show landing page
        require_once __DIR__ . '/../views/home.php';
    }
}
?>