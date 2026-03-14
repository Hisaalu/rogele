// File: /routes/web.php
<?php
// Simple routing system

// Get the requested URI
$request = $_SERVER['REQUEST_URI'];
$request = strtok($request, '?'); // Remove query string

// Define routes
$routes = [
    // Public routes
    '/' => 'AuthController@login',
    '/login' => 'AuthController@login',
    '/register' => 'AuthController@register',
    '/logout' => 'AuthController@logout',
    '/change-password' => 'AuthController@changePassword',
    
    // Admin routes
    '/admin/dashboard' => 'AdminController@dashboard',
    '/admin/users' => 'AdminController@users',
    '/admin/users/create' => 'AdminController@createUser',
    '/admin/reports' => 'AdminController@reports',
    '/admin/reports/export' => 'AdminController@exportReport',
    '/admin/settings' => 'AdminController@settings',
    
    // Teacher routes
    '/teacher/dashboard' => 'TeacherController@dashboard',
    '/teacher/lessons' => 'TeacherController@lessons',
    '/teacher/lessons/create' => 'TeacherController@createLesson',
    '/teacher/quizzes' => 'TeacherController@quizzes',
    '/teacher/quizzes/create' => 'TeacherController@createQuiz',
    '/teacher/quiz-results/{id}' => 'TeacherController@quizResults',
    '/teacher/analytics' => 'TeacherController@analytics',
    
    // Learner routes
    '/learner/dashboard' => 'LearnerController@dashboard',
    '/learner/materials' => 'LearnerController@materials',
    '/learner/view-lesson/{id}' => 'LearnerController@viewLesson',
    '/learner/quizzes' => 'LearnerController@quizzes',
    '/learner/take-quiz/{id}' => 'LearnerController@takeQuiz',
    '/learner/quiz-result/{id}' => 'LearnerController@quizResult',
    '/learner/bookmarks' => 'LearnerController@bookmarks',
    '/learner/bookmark/{id}' => 'LearnerController@addBookmark',
    
    // External user routes
    '/external/dashboard' => 'ExternalController@dashboard',
    '/external/materials' => 'ExternalController@materials',
    '/external/view-lesson/{id}' => 'ExternalController@viewLesson',
    '/external/subscription' => 'ExternalController@subscription',
    '/external/purchase' => 'ExternalController@purchase',
    '/external/trial-status' => 'ExternalController@trialStatus',
];

// Match route
$matched = false;
foreach ($routes as $route => $controllerAction) {
    // Convert route to regex pattern
    $pattern = preg_replace('/\{[^\}]+\}/', '([^/]+)', $route);
    $pattern = '#^' . $pattern . '$#';
    
    if (preg_match($pattern, $request, $matches)) {
        array_shift($matches); // Remove full match
        
        list($controller, $method) = explode('@', $controllerAction);
        
        // Load controller
        $controllerFile = __DIR__ . '/../controllers/' . $controller . '.php';
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controllerInstance = new $controller();
            
            // Call method with parameters
            call_user_func_array([$controllerInstance, $method], $matches);
            $matched = true;
            break;
        }
    }
}

// 404 if no route matched
if (!$matched) {
    header('HTTP/1.0 404 Not Found');
    echo '404 - Page Not Found';
    exit;
}
?>