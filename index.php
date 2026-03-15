<?php
// File: /index.php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration
require_once __DIR__ . '/config/config.php';

// Simple autoloader
spl_autoload_register(function ($class) {
    // Check in controllers directory
    $controllerPath = __DIR__ . '/controllers/' . $class . '.php';
    if (file_exists($controllerPath)) {
        require_once $controllerPath;
        return true;
    }
    
    // Check in models directory
    $modelPath = __DIR__ . '/models/' . $class . '.php';
    if (file_exists($modelPath)) {
        require_once $modelPath;
        return true;
    }
    
    return false;
});

// Simple routing
$request = $_SERVER['REQUEST_URI'];

// Remove base path from request
$basePath = '/rays-of-grace';
if (strpos($request, $basePath) === 0) {
    $request = substr($request, strlen($basePath));
}

// Remove query string
$request = strtok($request, '?');

// If request is empty, set to root
if ($request == '' || $request == '/') {
    $request = '/';
}

// Debug - you can remove this after fixing
echo "<!-- Debug: Request URI: " . $_SERVER['REQUEST_URI'] . " -->\n";
echo "<!-- Debug: Processed Request: " . $request . " -->\n";

// Define routes
$routes = [
    '/' => 'HomeController@index',
    '/login' => 'AuthController@login',
    '/register' => 'AuthController@register',
    '/logout' => 'AuthController@logout',
    '/change-password' => 'AuthController@changePassword',
    '/forgot-password' => 'AuthController@forgotPassword',
    '/reset-password' => 'AuthController@resetPassword',
    
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
    '/admin/reports/export' => 'AdminController@exportReport',
    '/admin/settings' => 'AdminController@settings',
    '/admin/api/chart-data' => 'ChartApiController@chartData',
    '/admin/update-profile' => 'AdminController@updateProfile',
    '/admin/update-profile-photo' => 'AdminController@updateProfilePhoto',
    
    // Teacher routes
    '/teacher/dashboard' => 'TeacherController@dashboard',
    '/teacher/lessons' => 'TeacherController@lessons',
    '/teacher/lessons/create' => 'TeacherController@createLesson',
    '/teacher/quizzes' => 'TeacherController@quizzes',
    '/teacher/quizzes/create' => 'TeacherController@createQuiz',
    '/teacher/analytics' => 'TeacherController@analytics',
    
    // Learner routes
    '/learner/dashboard' => 'LearnerController@dashboard',
    '/learner/materials' => 'LearnerController@materials',
    '/learner/quizzes' => 'LearnerController@quizzes',
    '/learner/bookmarks' => 'LearnerController@bookmarks',
    
    // External user routes
    '/external/dashboard' => 'ExternalController@dashboard',
    '/external/materials' => 'ExternalController@materials',
    '/external/subscription' => 'ExternalController@subscription',
    '/external/purchase' => 'ExternalController@purchase',
    '/external/quizzes' => 'ExternalController@quizzes',
    '/external/process-payment' => 'ExternalController@processPayment',
    '/external/profile' => 'ExternalController@profile',
    '/external/settings' => 'ExternalController@settings',
    '/external/update-profile' => 'ExternalController@updateProfile',
    '/external/change-password' => 'ExternalController@changePassword',
    '/external/delete-account' => 'ExternalController@deleteAccount',

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
];

// Add this temporary debug code after defining $routes
echo "<!-- Debug: Checking routes -->";
if (!isset($routes['/external/delete-account'])) {
    die("ERROR: Route /external/delete-account is not defined in routes array!");
} else {
    echo "<!-- Route exists: /external/delete-account -->";
}

// FIRST: Check for exact matches (simple routes)
if (isset($routes[$request])) {
    $controllerAction = $routes[$request];
    list($controllerName, $methodName) = explode('@', $controllerAction);
    
    $controllerFile = __DIR__ . '/controllers/' . $controllerName . '.php';
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            
            if (method_exists($controller, $methodName)) {
                $controller->$methodName();
                exit;
            } else {
                die("Method $methodName not found in $controllerName");
            }
        } else {
            die("Class $controllerName not found");
        }
    } else {
        die("Controller file not found: $controllerFile");
    }
}

// SECOND: Check for routes with parameters (like {id})
$matched = false;
foreach ($routes as $route => $controllerAction) {
    // Convert route to regex pattern
    $pattern = preg_replace('/\{[^\}]+\}/', '([^/]+)', $route);
    $pattern = '#^' . $pattern . '$#';
    
    if (preg_match($pattern, $request, $matches)) {
        array_shift($matches); // Remove full match
        
        list($controllerName, $methodName) = explode('@', $controllerAction);
        
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
        break;
    }
}

// If still no match, show 404
if (!$matched) {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 - Page Not Found</h1>";
    echo "<p>The requested URL '{$request}' was not found on this server.</p>";
    echo "<p><a href='" . BASE_URL . "'>Go to Homepage</a></p>";
    
    // Debug information (remove in production)
    echo "<h3>Debug Information:</h3>";
    echo "<pre>";
    echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
    echo "Processed Request: " . $request . "\n";
    echo "Base URL: " . BASE_URL . "\n";
    echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "\n";
    echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
    echo "Available Routes with parameters:\n";
    foreach ($routes as $route => $action) {
        if (strpos($route, '{') !== false) {
            echo "  - $route\n";
        }
    }
    echo "</pre>";
}
?>