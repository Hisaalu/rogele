<?php
// File: /controllers/TeacherController.php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Lesson.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../models/Classes.php'; 

class TeacherController {
    private $userModel;
    private $lessonModel;
    private $quizModel;
    private $subjectModel;
    private $classModel;
    
    public function __construct() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        // Check if user has teacher role
        if ($_SESSION['user_role'] !== 'teacher') {
            // Redirect non-teacher users to their respective dashboards
            $this->redirectToRoleDashboard();
            exit;
        }
        
        $this->userModel = new User();
        $this->lessonModel = new Lesson();
        $this->quizModel = new Quiz();
        $this->subjectModel = new Subject();
        $this->classModel = new Classes(); // Updated class name
    }
    
    private function redirectToRoleDashboard() {
        switch ($_SESSION['user_role']) {
            case 'admin':
                header('Location: ' . BASE_URL . '/admin/dashboard');
                break;
            case 'learner':
                header('Location: ' . BASE_URL . '/learner/dashboard');
                break;
            case 'external':
                header('Location: ' . BASE_URL . '/external/dashboard');
                break;
            default:
                header('Location: ' . BASE_URL . '/login');
        }
        exit;
    }
    
    /**
     * Teacher Dashboard
     */
    public function dashboard() {
        $hideFooter = true;
        
        // Get teacher's statistics
        $teacherId = $_SESSION['user_id'];
        $totalLessons = count($this->lessonModel->getByTeacher($teacherId));
        $totalQuizzes = count($this->quizModel->getByTeacher($teacherId));
        
        // Get recent lessons (ordered by created_at DESC, limit 10)
        $recentLessons = $this->lessonModel->getByTeacher($teacherId, 10, 0);
        
        // Get recent quizzes (ordered by created_at DESC, limit 10)
        $recentQuizzes = $this->quizModel->getByTeacher($teacherId, 10, 0);
        
        // Get class performance stats
        $classPerformance = $this->getClassPerformance();
        
        require_once __DIR__ . '/../views/teacher/dashboard.php';
    }
    
    /**
     * Get class performance statistics including both learners and external users
     */
    private function getClassPerformance() {
        $teacherId = $_SESSION['user_id'];
        
        // Get total students (learners + external) taught by this teacher
        $totalStudents = $this->userModel->countStudentsByTeacher($teacherId);
        
        // Get average quiz score for students who have taken quizzes
        $avgScore = $this->quizModel->getAverageScoreByTeacher($teacherId);
        
        // Get completion rate (students who have taken at least one quiz vs total students)
        $studentsWithAttempts = $this->quizModel->countStudentsWithAttemptsByTeacher($teacherId);
        $completionRate = $totalStudents > 0 ? round(($studentsWithAttempts / $totalStudents) * 100) : 0;
        
        // Get active classes count
        $activeClasses = count($this->classModel->getByTeacher($teacherId));
        
        return [
            'total_students' => $totalStudents,
            'avg_score' => $avgScore,
            'completion_rate' => $completionRate,
            'active_classes' => $activeClasses
        ];
    }
    
    /**
     * Lessons Management - View all lessons
     */
    public function lessons() {
        $hideFooter = true;
        
        $teacherId = $_SESSION['user_id'];
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? null;
        
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        // Debug - log what's happening
        error_log("TeacherController@lessons called for teacher ID: " . $teacherId);
        
        // Get lessons for this teacher
        if ($search) {
            $lessons = $this->lessonModel->searchByTeacher($teacherId, $search);
            $totalLessons = count($lessons);
            $totalPages = 1;
            error_log("Search results: " . count($lessons) . " lessons found for search: " . $search);
        } else {
            $lessons = $this->lessonModel->getByTeacher($teacherId, $limit, $offset);
            $totalLessons = count($this->lessonModel->getByTeacher($teacherId));
            $totalPages = ceil($totalLessons / $limit);
            error_log("Total lessons for teacher: " . $totalLessons);
            error_log("Current page lessons count: " . count($lessons));
        }
        
        // If no lessons found, log that too
        if (empty($lessons)) {
            error_log("No lessons found for teacher ID: " . $teacherId);
        } else {
            error_log("First lesson data: " . print_r($lessons[0], true));
        }
        
        require_once __DIR__ . '/../views/teacher/lessons.php';
    }
    
   /**
     * Create Lesson Form
     */
    public function createLesson() {
        // Add debug at the VERY TOP
        error_log("========== CREATE LESSON METHOD CALLED ==========");
        error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
        error_log("Session user_role: " . ($_SESSION['user_role'] ?? 'NOT SET'));
        
        $hideFooter = true;
        
        // Get all active classes
        $classes = $this->classModel->getActive();
        error_log("Classes found: " . count($classes));
        
        // Get all subjects
        $allSubjects = $this->subjectModel->getAll();
        error_log("Subjects found: " . count($allSubjects));
        
        // Organize subjects by class
        $subjectsByClass = [];
        foreach ($allSubjects as $subject) {
            $classId = $subject['class_id'];
            if (!isset($subjectsByClass[$classId])) {
                $subjectsByClass[$classId] = [];
            }
            $subjectsByClass[$classId][] = $subject;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("========== PROCESSING POST REQUEST ==========");
            error_log("POST data: " . print_r($_POST, true));
            error_log("FILES data: " . print_r($_FILES, true));
            
            // Validate required fields
            $errors = [];
            
            if (empty($_POST['title'])) {
                $errors[] = 'Title is required';
                error_log("ERROR: Title is empty");
            }
            
            if (empty($_POST['class_id'])) {
                $errors[] = 'Class is required';
                error_log("ERROR: Class ID is empty");
            }
            
            if (empty($_POST['subject_id'])) {
                $errors[] = 'Subject is required';
                error_log("ERROR: Subject ID is empty");
            }
            
            if (empty($errors)) {
                $data = [
                    'title' => $_POST['title'],
                    'content' => $_POST['content'] ?? '',
                    'subject_id' => $_POST['subject_id'],
                    'class_id' => $_POST['class_id'],
                    'teacher_id' => $_SESSION['user_id'],
                    'video_url' => $_POST['video_url'] ?? null,
                    'duration' => $_POST['duration'] ?? null,
                    'is_published' => isset($_POST['is_published']) ? 1 : 0
                ];
                
                error_log("Data prepared for lesson creation: " . print_r($data, true));
                
                // Handle file uploads
                $files = $_FILES['materials'] ?? null;
                
                // Call the model to create lesson
                error_log("Calling lessonModel->create()");
                $result = $this->lessonModel->create($data, $files);
                
                error_log("Create lesson result: " . print_r($result, true));
                
                if ($result['success']) {
                    $_SESSION['success'] = 'Lesson created successfully!';
                    error_log("SUCCESS: Lesson created with ID: " . $result['lesson_id']);
                    header('Location: ' . BASE_URL . '/teacher/lessons');
                    exit;
                } else {
                    $_SESSION['error'] = $result['error'] ?? 'Failed to create lesson.';
                    error_log("ERROR: " . ($result['error'] ?? 'Unknown error'));
                }
            } else {
                $_SESSION['error'] = implode('<br>', $errors);
                error_log("Validation errors: " . implode(', ', $errors));
            }
        }
        
        // Pass data to view
        $subjects = $allSubjects;
        require_once __DIR__ . '/../views/teacher/create_lesson.php';
    }
    
    /**
     * Edit Lesson
     */
    public function editLesson($lessonId) {
        $hideFooter = true;
        
        // Get the lesson
        $lesson = $this->lessonModel->getById($lessonId);
        
        // Check if lesson exists and belongs to this teacher
        if (!$lesson) {
            $_SESSION['error'] = 'Lesson not found.';
            header('Location: ' . BASE_URL . '/teacher/lessons');
            exit;
        }
        
        if ($lesson['teacher_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'You do not have permission to edit this lesson.';
            header('Location: ' . BASE_URL . '/teacher/lessons');
            exit;
        }
        
        // Get classes and subjects for dropdowns
        $classes = $this->classModel->getAll();
        $subjects = $this->subjectModel->getAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Update lesson
            $data = [
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
                'subject_id' => $_POST['subject_id'] ?? null,
                'class_id' => $_POST['class_id'] ?? null,
                'video_url' => $_POST['video_url'] ?? null,
                'duration' => $_POST['duration'] ?? null,
                'is_published' => isset($_POST['is_published']) ? 1 : 0
            ];
            
            $result = $this->lessonModel->update($lessonId, $data);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Lesson updated successfully!';
                
                // Handle new file uploads if any
                if (!empty($_FILES['materials']['name'][0])) {
                    $this->lessonModel->uploadMaterials($lessonId, $_FILES['materials']);
                }
                
                header('Location: ' . BASE_URL . '/teacher/lessons');
                exit;
            } else {
                $_SESSION['error'] = $result['error'] ?? 'Failed to update lesson.';
            }
        }
        
        require_once __DIR__ . '/../views/teacher/edit_lesson.php';
    }
    
    /**
     * Delete Lesson
     */
    public function deleteLesson($lessonId) {
        $lesson = $this->lessonModel->getById($lessonId);
        
        // Check if lesson exists and belongs to this teacher
        if (!$lesson || $lesson['teacher_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Lesson not found or you do not have permission to delete it.';
            header('Location: ' . BASE_URL . '/teacher/lessons');
            exit;
        }
        
        $result = $this->lessonModel->delete($lessonId);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Lesson deleted successfully!';
        } else {
            $_SESSION['error'] = $result['error'] ?? 'Failed to delete lesson.';
        }
        
        header('Location: ' . BASE_URL . '/teacher/lessons');
        exit;
    }

    /**
     * Add Questions to Quiz
     */
    public function addQuestions($quizId) {
        $hideFooter = true;
        
        // Get the quiz
        $quiz = $this->quizModel->getById($quizId);
        
        // Check if quiz exists and belongs to this teacher
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz not found.';
            header('Location: ' . BASE_URL . '/teacher/quizzes');
            exit;
        }
        
        if ($quiz['teacher_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'You do not have permission to modify this quiz.';
            header('Location: ' . BASE_URL . '/teacher/quizzes');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $questions = [];
            
            // Process questions from form
            if (isset($_POST['questions']) && is_array($_POST['questions'])) {
                foreach ($_POST['questions'] as $index => $q) {
                    // Only add if question text and required options are provided
                    if (!empty($q['question']) && !empty($q['option_a']) && !empty($q['option_b']) && !empty($q['correct_answer'])) {
                        $questions[] = [
                            'question' => $q['question'],
                            'option_a' => $q['option_a'],
                            'option_b' => $q['option_b'],
                            'option_c' => $q['option_c'] ?? null,
                            'option_d' => $q['option_d'] ?? null,
                            'correct_answer' => $q['correct_answer'],
                            'points' => $q['points'] ?? 1
                        ];
                    }
                }
            }
            
            if (empty($questions)) {
                $_SESSION['error'] = 'Please add at least one question.';
            } else {
                $result = $this->quizModel->addQuestions($quizId, $questions);
                
                if ($result['success']) {
                    $_SESSION['success'] = count($questions) . ' questions added successfully!';
                    header('Location: ' . BASE_URL . '/teacher/quizzes');
                    exit;
                } else {
                    $_SESSION['error'] = $result['error'] ?? 'Failed to add questions.';
                }
            }
        }
        
        require_once __DIR__ . '/../views/teacher/add_questions.php';
    }
    
    /**
     * Delete Quiz
     */
    public function deleteQuiz($quizId) {
        $quiz = $this->quizModel->getById($quizId);
        
        // Check if quiz exists and belongs to this teacher
        if (!$quiz || $quiz['teacher_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Quiz not found or you do not have permission to delete it.';
            header('Location: ' . BASE_URL . '/teacher/quizzes');
            exit;
        }
        
        $result = $this->quizModel->delete($quizId);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Quiz deleted successfully!';
        } else {
            $_SESSION['error'] = $result['error'] ?? 'Failed to delete quiz.';
        }
        
        header('Location: ' . BASE_URL . '/teacher/quizzes');
        exit;
    }
    
    /**
     * Students Management
     */
    public function students() {
        $hideFooter = true;
        
        $teacherId = $_SESSION['user_id'];
        $classId = $_GET['class_id'] ?? null;
        $search = $_GET['search'] ?? null;
        
        // Get classes taught by this teacher
        $classes = $this->classModel->getByTeacher($teacherId);
        
        // Get students based on filters (learners and external users)
        $students = $this->userModel->getStudentsByTeacher($teacherId, $classId, $search);
        
        // Debug: Log what we found
        error_log("Found " . count($students) . " students for teacher ID: " . $teacherId);
        
        require_once __DIR__ . '/../views/teacher/students.php';
    }
    
    /**
     * View Student Progress
     */
    public function studentProgress($studentId) {
        $hideFooter = true;
        
        $student = $this->userModel->getById($studentId);
        
        if (!$student || $student['role'] !== 'learner') {
            $_SESSION['error'] = 'Student not found.';
            header('Location: ' . BASE_URL . '/teacher/students');
            exit;
        }
        
        // Get student's quiz results
        $quizResults = $this->quizModel->getUserResults($studentId);
        
        // Get student's lesson progress
        $lessonProgress = $this->lessonModel->getUserProgress($studentId);
        
        require_once __DIR__ . '/../views/teacher/student_progress.php';
    }
    
    /**
     * Analytics Dashboard
     */
    public function analytics() {
        $hideFooter = true;
        
        $teacherId = $_SESSION['user_id'];
        $range = $_GET['range'] ?? 30;
        
        // Get overall statistics
        $stats = [
            'total_lessons' => count($this->lessonModel->getByTeacher($teacherId)),
            'total_quizzes' => count($this->quizModel->getByTeacher($teacherId)),
            'total_students' => $this->userModel->countStudentsByTeacher($teacherId),
            'avg_score' => $this->quizModel->getAverageScoreByTeacher($teacherId)
        ];
        
        // Get quiz performance data
        $quizPerformance = $this->quizModel->getPerformanceByTeacher($teacherId);
        
        // Get lesson views data
        $lessonViews = $this->lessonModel->getViewsByTeacher($teacherId, $range);
        
        require_once __DIR__ . '/../views/teacher/analytics.php';
    }
    
    /**
     * Teacher Profile
     */
    public function profile() {
        $hideFooter = true;
        
        $userId = $_SESSION['user_id'];
        
        // Get user profile
        $profile = $this->userModel->getProfile($userId);
        
        if (!$profile) {
            $nameParts = explode(' ', $_SESSION['user_name'] ?? 'Teacher');
            $profile = [
                'id' => $userId,
                'first_name' => $nameParts[0] ?? '',
                'last_name' => $nameParts[1] ?? '',
                'email' => $_SESSION['user_email'] ?? '',
                'phone' => '',
                'role' => 'teacher',
                'created_at' => date('Y-m-d H:i:s'),
                'profile_photo' => null
            ];
        }
        
        // Get actual students count (learners and external users)
        $profile['students_count'] = $this->userModel->countStudentsByTeacher($userId);
        
        // Get classes count
        $profile['classes_count'] = count($this->classModel->getByTeacher($userId));
        
        require_once __DIR__ . '/../views/teacher/profile.php';
    }
    
    /**
     * Update Profile
     */
    public function updateProfile() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/teacher/profile');
            exit;
        }
        
        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? ''
        ];
        
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
            $_SESSION['error'] = 'Please fill in all required fields';
            header('Location: ' . BASE_URL . '/teacher/profile');
            exit;
        }
        
        $result = $this->userModel->updateProfile($_SESSION['user_id'], $data);
        
        if ($result['success']) {
            $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
            $_SESSION['user_email'] = $data['email'];
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['error'];
        }
        
        header('Location: ' . BASE_URL . '/teacher/profile');
        exit;
    }
    
    /**
     * Settings Page
     */
    public function settings() {
        $hideFooter = true;
        
        $activeTab = $_GET['tab'] ?? 'password';
        
        require_once __DIR__ . '/../views/teacher/settings.php';
    }
    
    /**
     * Change Password
     */
    public function changePassword() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/teacher/settings?tab=password');
            exit;
        }
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error'] = 'Please fill in all password fields';
            header('Location: ' . BASE_URL . '/teacher/settings?tab=password');
            exit;
        }
        
        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'New passwords do not match';
            header('Location: ' . BASE_URL . '/teacher/settings?tab=password');
            exit;
        }
        
        if (strlen($newPassword) < 8) {
            $_SESSION['error'] = 'Password must be at least 8 characters long';
            header('Location: ' . BASE_URL . '/teacher/settings?tab=password');
            exit;
        }
        
        $result = $this->userModel->changePassword($_SESSION['user_id'], $currentPassword, $newPassword);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['error'];
        }
        
        header('Location: ' . BASE_URL . '/teacher/settings?tab=password');
        exit;
    }

    /**
     * Preview Lesson
     */
    public function previewLesson($lessonId) {
        $hideFooter = true;
        
        // Get the lesson
        $lesson = $this->lessonModel->getById($lessonId);
        
        // Check if lesson exists and belongs to this teacher
        if (!$lesson || $lesson['teacher_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Lesson not found or you do not have permission to preview it.';
            header('Location: ' . BASE_URL . '/teacher/lessons');
            exit;
        }
        
        require_once __DIR__ . '/../views/teacher/preview_lesson.php';
    }

    /**
     * Quizzes Management - View all quizzes
     */
    public function quizzes() {
        $hideFooter = true;
        
        $teacherId = $_SESSION['user_id'];
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? null;
        
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        if ($search) {
            $quizzes = $this->quizModel->searchByTeacher($teacherId, $search);
            $totalQuizzes = count($quizzes);
            $totalPages = 1;
        } else {
            $quizzes = $this->quizModel->getByTeacher($teacherId, $limit, $offset);
            $totalQuizzes = count($this->quizModel->getByTeacher($teacherId));
            $totalPages = ceil($totalQuizzes / $limit);
        }
        
        require_once __DIR__ . '/../views/teacher/quizzes.php';
    }

    /**
     * Create Quiz Form
     */
    public function createQuiz() {
        $hideFooter = true;
        
        $classes = $this->classModel->getActive();
        $subjects = $this->subjectModel->getAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'subject_id' => $_POST['subject_id'] ?? null,
                'class_id' => $_POST['class_id'] ?? null,
                'teacher_id' => $_SESSION['user_id'],
                'time_limit' => $_POST['time_limit'] ?? 30,
                'passing_score' => $_POST['passing_score'] ?? 50,
                'max_attempts' => $_POST['max_attempts'] ?? 3,
                'start_date' => date('Y-m-d H:i:s'),
                'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                'is_published' => isset($_POST['is_published']) ? 1 : 0
            ];
            
            $result = $this->quizModel->create($data);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Quiz created successfully! Now add questions.';
                header('Location: ' . BASE_URL . '/teacher/quizzes/add-questions/' . $result['quiz_id']);
                exit;
            } else {
                $_SESSION['error'] = $result['error'] ?? 'Failed to create quiz.';
            }
        }
        
        require_once __DIR__ . '/../views/teacher/create_quiz.php';
    }

    /**
     * Edit Quiz
     */
    public function editQuiz($quizId) {
        $hideFooter = true;
        
        $quiz = $this->quizModel->getById($quizId);
        
        if (!$quiz || $quiz['teacher_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Quiz not found or you do not have permission to edit it.';
            header('Location: ' . BASE_URL . '/teacher/quizzes');
            exit;
        }
        
        $classes = $this->classModel->getActive();
        $subjects = $this->subjectModel->getAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'subject_id' => $_POST['subject_id'] ?? null,
                'class_id' => $_POST['class_id'] ?? null,
                'time_limit' => $_POST['time_limit'] ?? 30,
                'passing_score' => $_POST['passing_score'] ?? 50,
                'max_attempts' => $_POST['max_attempts'] ?? 3,
                'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                'is_published' => isset($_POST['is_published']) ? 1 : 0
            ];
            
            $result = $this->quizModel->update($quizId, $data);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Quiz updated successfully!';
                header('Location: ' . BASE_URL . '/teacher/quizzes');
                exit;
            } else {
                $_SESSION['error'] = $result['error'] ?? 'Failed to update quiz.';
            }
        }
        
        require_once __DIR__ . '/../views/teacher/edit_quiz.php';
    }

    /**
     * Quiz Results
     */
    public function quizResults($quizId) {
        $hideFooter = true;
        
        $quiz = $this->quizModel->getById($quizId);
        
        if (!$quiz || $quiz['teacher_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Quiz not found or you do not have permission to view results.';
            header('Location: ' . BASE_URL . '/teacher/quizzes');
            exit;
        }
        
        $results = $this->quizModel->getResults($quizId);
        $stats = $this->quizModel->getQuizStats($quizId);
        
        require_once __DIR__ . '/../views/teacher/quiz_results.php';
    }

    /**
     * Preview Quiz
     */
    public function previewQuiz($quizId) {
        $hideFooter = true;
        
        $quiz = $this->quizModel->getById($quizId);
        
        if (!$quiz || $quiz['teacher_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Quiz not found or you do not have permission to preview it.';
            header('Location: ' . BASE_URL . '/teacher/quizzes');
            exit;
        }
        
        require_once __DIR__ . '/../views/teacher/preview_quiz.php';
    }

    /**
     * Delete lesson material
     */
    public function deleteMaterial($materialId) {
        $hideFooter = true;
        
        // Get the material to check ownership
        $material = $this->lessonModel->getMaterialById($materialId);
        
        if (!$material) {
            $_SESSION['error'] = 'Material not found.';
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/rays-of-grace/teacher/lessons');
            exit;
        }
        
        // Get the lesson to check if it belongs to this teacher
        $lesson = $this->lessonModel->getById($material['lesson_id']);
        
        if (!$lesson || $lesson['teacher_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'You do not have permission to delete this material.';
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/rays-of-grace/teacher/lessons');
            exit;
        }
        
        // Delete the material
        $result = $this->lessonModel->deleteMaterial($materialId);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Material deleted successfully.';
        } else {
            $_SESSION['error'] = $result['error'] ?? 'Failed to delete material.';
        }
        
        // Redirect back to the edit page
        header('Location: ' . BASE_URL . '/teacher/lessons/edit/' . $material['lesson_id']);
        exit;
    }
}
?>