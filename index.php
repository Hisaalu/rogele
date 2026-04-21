<?php
// File: /index.php
require_once __DIR__ . '/config/env.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';

spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/controllers/' . $class . '.php',
        __DIR__ . '/models/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return true;
        }
    }
    return false;
});

$request = $_SERVER['REQUEST_URI'];

$request = strtok($request, '?');
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($scriptName);

if ($basePath != '/' && $basePath != '\\' && $basePath != '.') {
    $basePath = rtrim($basePath, '/');
    if (strpos($request, $basePath) === 0) {
        $request = substr($request, strlen($basePath));
    }
}

if (empty($request) || $request == '') {
    $request = '/';
}

// Define routes
$routes = [
    '/' => 'HomeController@index',
    '/login' => 'AuthController@login',
    '/register' => 'AuthController@register',
    '/logout' => 'AuthController@logout',
    '/forgot-password' => 'AuthController@forgotPassword',
    '/reset-password' => 'AuthController@resetPassword',
    '/change-password' => 'AuthController@changePassword',
    '/auth/process-forgot-password' => 'AuthController@processForgotPassword',
    '/auth/process-reset-password' => 'AuthController@processResetPassword',

    //public routes
    '/privacy-policy' => 'PageController@privacyPolicy',
    '/terms-of-service' => 'PageController@termsOfService',
    '/contact' => 'HomeController@contact',
    
    // Admin routes
    '/admin/dashboard' => 'AdminController@dashboard',
    '/admin/profile' => 'AdminController@profile',
    '/admin/users' => 'AdminController@users',
    '/admin/users/create' => 'AdminController@createUser',
    '/admin/users/edit/{id}' => 'AdminController@editUser',
    '/admin/users/suspend/{id}' => 'AdminController@suspendUser',
    '/admin/users/activate/{id}' => 'AdminController@activateUser',
    '/admin/users/delete/{id}' => 'AdminController@deleteUser',
    '/admin/reports' => 'AdminController@reports',
    '/admin/settings' => 'AdminController@settings',
    '/admin/lessons' => 'AdminController@lessons',
    '/admin/quizzes' => 'AdminController@quizzes',
    '/admin/subscriptions' => 'AdminSubscriptionController@index',
    '/admin/subscriptions/view/{id}' => 'AdminSubscriptionController@view',
    '/admin/reports/export' => 'ExportController@export',
    
    // Teacher routes
    '/teacher/dashboard' => 'TeacherController@dashboard',
    '/teacher/profile' => 'TeacherController@profile',
    '/teacher/update-profile' => 'TeacherController@updateProfile',
    '/teacher/settings' => 'TeacherController@settings',
    '/teacher/change-password' => 'TeacherController@changePassword',
    '/teacher/lessons' => 'TeacherController@lessons',
    '/teacher/lessons/create' => 'TeacherController@createLesson',
    '/teacher/lessons/edit/{id}' => 'TeacherController@editLesson',
    '/teacher/lessons/delete/{id}' => 'TeacherController@deleteLesson',
    '/teacher/lessons/preview/{id}' => 'TeacherController@previewLesson',
    '/teacher/quizzes' => 'TeacherController@quizzes',
    '/teacher/quizzes/create' => 'TeacherController@createQuiz',
    '/teacher/quizzes/add-questions/{id}' => 'TeacherController@addQuestions',
    '/teacher/quizzes/edit/{id}' => 'TeacherController@editQuiz',
    '/teacher/quizzes/delete/{id}' => 'TeacherController@deleteQuiz',
    '/teacher/quizzes/results/{id}' => 'TeacherController@quizResults',
    '/teacher/quizzes/preview/{id}' => 'TeacherController@previewQuiz',
    '/teacher/students' => 'TeacherController@students',
    '/teacher/students/progress/{id}' => 'TeacherController@studentProgress',
    '/teacher/analytics' => 'TeacherController@analytics',
    '/teacher/lessons/delete-material/{id}' => 'TeacherController@deleteMaterial',
    '/teacher/quizzes/publish/{id}' => 'TeacherController@publishQuiz',
    '/teacher/quizzes/unpublish/{id}' => 'TeacherController@unpublishQuiz',
    '/teacher/quizzes/edit-question/{id}' => 'TeacherController@editQuestion',
    
     // Teacher API routes
    '/teacher/api/quiz-performance' => 'TeacherApiController@quizPerformance',
    '/teacher/api/lesson-views' => 'TeacherApiController@lessonViews',
    
    // Learner routes
    '/learner/dashboard' => 'LearnerController@dashboard',
    '/learner/materials' => 'LearnerController@materials',
    '/learner/quizzes' => 'LearnerController@quizzes',
    '/learner/bookmarks' => 'LearnerController@bookmarks',
    
    // External user routes
    '/external/pesapal-ipn' => 'ExternalController@pesapalIpn',
    '/external/pesapal-callback' => 'ExternalController@pesapalCallback',
    '/external/pesapal-test' => 'ExternalController@pesapalTest',
    '/external/process-pesapal-payment' => 'ExternalController@processPesapalPayment',
    '/external/dashboard' => 'ExternalController@dashboard',
    '/external/materials' => 'ExternalController@materials',
    '/external/view-lesson/{id}' => 'ExternalController@viewLesson',
    '/external/subscription' => 'ExternalController@subscription',
    '/external/purchase' => 'ExternalController@purchase',
    '/external/lessons' => 'ExternalController@materials',
    '/external/quizzes' => 'ExternalController@quizzes',
    '/external/take-quiz/{id}' => 'ExternalController@takeQuiz',
    '/external/quiz-result/{id}' => 'ExternalController@quizResult',
    '/external/process-payment' => 'ExternalController@processPayment',
    '/external/profile' => 'ExternalController@profile',
    '/external/settings' => 'ExternalController@settings',
    '/external/update-profile' => 'ExternalController@updateProfile',
    '/external/change-password' => 'ExternalController@changePassword',
    '/external/delete-account' => 'ExternalController@deleteAccount',
    '/external/upgrade-confirmation' => 'ExternalController@upgradeConfirmation',
    '/external/process-upgrade' => 'ExternalController@processUpgrade',
    '/external/upgrade-success' => 'ExternalController@upgradeSuccess',
    '/external/process-payment' => 'ExternalController@processPayment',
    '/external/payment-callback' => 'ExternalController@paymentCallback',
    '/external/payment-success' => 'ExternalController@paymentSuccess',
    '/external/payment-cancelled' => 'ExternalController@paymentCancelled',
    
     // Settings routes
    '/admin/settings' => 'AdminController@settings',
    '/admin/settings/general' => 'AdminController@saveGeneralSettings',
    '/admin/settings/subscription' => 'AdminController@saveSubscriptionSettings',
    '/admin/settings/email' => 'AdminController@saveEmailSettings',
    '/admin/settings/security' => 'AdminController@saveSecuritySettings',
    '/admin/settings/appearance' => 'AdminController@saveAppearanceSettings',
    '/admin/settings/save-all' => 'AdminController@saveAllSettings',
    '/admin/settings/test-email' => 'AdminController@testEmailConfig',
    '/admin/settings/clear-cache' => 'AdminController@clearCache',
    '/admin/settings/reset-defaults' => 'AdminController@resetToDefaults',
    '/admin/lessons' => 'AdminController@lessons',
    '/admin/lessons/view/{id}' => 'AdminController@viewLesson',
    '/admin/lessons/approve/{id}' => 'AdminController@approveLesson',
    '/admin/lessons/reject/{id}' => 'AdminController@rejectLesson',
    '/admin/quizzes' => 'AdminController@quizzes',
    '/admin/quizzes/view/{id}' => 'AdminController@viewQuiz',
    '/admin/quizzes/approve/{id}' => 'AdminController@approveQuiz',
    '/admin/quizzes/reject/{id}' => 'AdminController@rejectQuiz',
    '/admin/quizzes/delete/{id}' => 'AdminController@deleteQuiz',
    '/admin/subscriptions' => 'AdminSubscriptionController@index',
    '/admin/subscriptions/view/{id}' => 'AdminSubscriptionController@view',
    '/admin/subscriptions/update-status' => 'AdminSubscriptionController@updateStatus',
    '/admin/subscriptions/cancel/{id}' => 'AdminSubscriptionController@cancel',
    '/admin/subscriptions/export' => 'AdminSubscriptionController@export',
    '/admin/subscriptions/reports' => 'AdminSubscriptionController@reports',
];

// Route matching
$matched = false;

// Check for exact match
if (isset($routes[$request])) {
    $action = $routes[$request];
    list($controllerName, $methodName) = explode('@', $action);
    
    $controllerFile = __DIR__ . '/controllers/' . $controllerName . '.php';
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            
            if (method_exists($controller, $methodName)) {
                $controller->$methodName();
                $matched = true;
                exit;
            }
        }
    }
}

// Check for parameterized routes
if (!$matched) {
    foreach ($routes as $route => $action) {
        if (strpos($route, '{') !== false) {
            $pattern = preg_replace('/\{[^\}]+\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $request, $matches)) {
                array_shift($matches);
                list($controllerName, $methodName) = explode('@', $action);
                
                $controllerFile = __DIR__ . '/controllers/' . $controllerName . '.php';
                
                if (file_exists($controllerFile)) {
                    require_once $controllerFile;
                    
                    if (class_exists($controllerName)) {
                        $controller = new $controllerName();
                        
                        if (method_exists($controller, $methodName)) {
                            call_user_func_array([$controller, $methodName], $matches);
                            $matched = true;
                            exit;
                        }
                    }
                }
            }
        }
    }
}

// If no route matched
if (!$matched) {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 - Page Not Found</h1>";
    echo "<p>The requested page was not found.</p>";
    echo "<p><a href='" . BASE_URL . "'>Go to Homepage</a></p>";
}
?>