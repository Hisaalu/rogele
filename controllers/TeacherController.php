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
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        if ($_SESSION['user_role'] !== 'teacher') {
            $this->redirectToRoleDashboard();
            exit;
        }
        
        $this->userModel = new User();
        $this->lessonModel = new Lesson();
        $this->quizModel = new Quiz();
        $this->subjectModel = new Subject();
        $this->classModel = new Classes(); 
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
     * Teacher Dashboard - Simplified version
     */
    public function dashboard() {
        $hideFooter = true;
        
        $teacherId = $_SESSION['user_id'];
        
        $totalLessons = count($this->lessonModel->getByTeacher($teacherId));
        $totalQuizzes = count($this->quizModel->getByTeacher($teacherId));
        $totalStudents = $this->userModel->countTotalStudents();
        
        $recentLessons = $this->lessonModel->getByTeacher($teacherId, 10, 0);
        $recentQuizzes = $this->quizModel->getByTeacher($teacherId, 10, 0);
        
        $classPerformance = $this->getClassPerformance();
        
        require_once __DIR__ . '/../views/teacher/dashboard.php';
    }
    
    /**
     * Get class performance statistics including both learners and external users
     */
    private function getClassPerformance() {
        $teacherId = $_SESSION['user_id'];
        $totalStudents = $this->userModel->countStudentsByTeacher($teacherId);
        $avgScore = $this->quizModel->getAverageScoreByTeacher($teacherId);
        $studentsWithAttempts = $this->quizModel->countStudentsWithAttemptsByTeacher($teacherId);
        $completionRate = $totalStudents > 0 ? round(($studentsWithAttempts / $totalStudents) * 100) : 0;
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
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        if ($search) {
            $lessons = $this->lessonModel->searchByTeacher($teacherId, $search);
            $totalLessons = count($lessons);
            $totalPages = 1;
        } else {
            $lessons = $this->lessonModel->getByTeacher($teacherId, $limit, $offset);
            $totalLessons = count($this->lessonModel->getByTeacher($teacherId));
            $totalPages = ceil($totalLessons / $limit);
        }
        
        require_once __DIR__ . '/../views/teacher/lessons.php';
    }
    
   /**
     * Create Lesson Form
     */
    public function createLesson() {
        
        $hideFooter = true;
        
        $classes = $this->classModel->getActive();
        
        $allSubjects = $this->subjectModel->getAll();
        
        $subjectsByClass = [];
        foreach ($allSubjects as $subject) {
            $classId = $subject['class_id'];
            if (!isset($subjectsByClass[$classId])) {
                $subjectsByClass[$classId] = [];
            }
            $subjectsByClass[$classId][] = $subject;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $errors = [];
            
            if (empty($_POST['title'])) {
                $errors[] = 'Title is required';
            }
            
            if (empty($_POST['class_id'])) {
                $errors[] = 'Class is required';
            }
            
            if (empty($_POST['subject_id'])) {
                $errors[] = 'Subject is required';
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
                
                
                $files = $_FILES['materials'] ?? null;
                
                $result = $this->lessonModel->create($data, $files);
                
                
                if ($result['success']) {
                    $_SESSION['success'] = 'Lesson created successfully!';
                    header('Location: ' . BASE_URL . '/teacher/lessons');
                    exit;
                } else {
                    $_SESSION['error'] = $result['error'] ?? 'Failed to create lesson.';
                }
            } else {
                $_SESSION['error'] = implode('<br>', $errors);
            }
        }
        
        $subjects = $allSubjects;
        require_once __DIR__ . '/../views/teacher/create_lesson.php';
    }
    
    /**
     * Edit Lesson
     */
    public function editLesson($lessonId) {
        $hideFooter = true;
        
        $lesson = $this->lessonModel->getById($lessonId);
        
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
        
        $classes = $this->classModel->getAll();
        $subjects = $this->subjectModel->getAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        
        $quiz = $this->quizModel->getById($quizId);
        
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
            
            if (isset($_POST['questions']) && is_array($_POST['questions'])) {
                foreach ($_POST['questions'] as $index => $q) {
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
     * Show all students (teacher view)
     */
    public function students() {
        $hideFooter = true;
        $teacherId = $_SESSION['user_id'];
        
        $classId = $_GET['class_id'] ?? null;
        $search = $_GET['search'] ?? null;
        
        $students = $this->userModel->getStudentsWithStats($teacherId, $classId, $search);
        
        $classes = $this->classModel->getAllClasses();
        
        require_once __DIR__ . '/../views/teacher/students.php';
    }
    
    /**
     * View Student Progress
     */
    public function studentProgress($studentId) {
        $hideFooter = true;
        
        $student = $this->userModel->getById($studentId);
        
        if (!$student || !in_array($student['role'], ['learner', 'external'])) {
            $_SESSION['error'] = 'Student not found.';
            header('Location: ' . BASE_URL . '/teacher/students');
            exit;
        }
        
        if ($student['class_id']) {
            $class = $this->classModel->getById($student['class_id']);
            $student['class_name'] = $class['name'] ?? null;
        }
        
        $quizResults = $this->quizModel->getUserResults($studentId);
        
        $quizStats = [
            'total_quizzes' => count($quizResults),
            'average_score' => 0,
            'highest_score' => 0,
            'lowest_score' => 'N/A',
            'best_quiz' => null,
            'trend' => 'Stable',
            'trend_direction' => ''
        ];
        
        if (!empty($quizResults)) {
            $scores = array_column($quizResults, 'score');
            $quizStats['average_score'] = round(array_sum($scores) / count($scores), 1);
            $quizStats['highest_score'] = max($scores);
            $quizStats['lowest_score'] = min($scores);
            
            $bestIndex = array_search($quizStats['highest_score'], $scores);
            if ($bestIndex !== false) {
                $quizStats['best_quiz'] = $quizResults[$bestIndex]['quiz_title'];
            }
            
            if (count($scores) >= 2) {
                $firstScore = $scores[0];
                $lastScore = $scores[count($scores) - 1];
                $difference = $lastScore - $firstScore;
                
                if ($difference > 10) {
                    $quizStats['trend'] = 'Improving';
                    $quizStats['trend_direction'] = "↑ +{$difference}%";
                } elseif ($difference < -10) {
                    $quizStats['trend'] = 'Declining';
                    $quizStats['trend_direction'] = "↓ " . abs($difference) . "%";
                } else {
                    $quizStats['trend'] = 'Consistent';
                    $quizStats['trend_direction'] = 'Steady performance';
                }
            }
        }
        
        require_once __DIR__ . '/../views/teacher/student_progress.php';
    }
    
    /**
     * Analytics Dashboard
     */
    public function analytics() {
        $hideFooter = true;
        
        $teacherId = $_SESSION['user_id'];
        $range = $_GET['range'] ?? 30;
        
        $stats = [
            'total_lessons' => count($this->lessonModel->getByTeacher($teacherId)),
            'total_quizzes' => count($this->quizModel->getByTeacher($teacherId)),
            'total_students' => $this->userModel->countTotalStudents(),
            'avg_score' => $this->quizModel->getAverageScoreByTeacher($teacherId)
        ];
        
        $quizPerformance = $this->quizModel->getPerformanceByTeacher($teacherId);
        
        $lessonViews = $this->lessonModel->getViewsByTeacher($teacherId, $range);
        
        require_once __DIR__ . '/../views/teacher/analytics.php';
    }
    
    /**
     * Teacher Profile
     */
    public function profile() {
        $hideFooter = true;
        
        $teacherId = $_SESSION['user_id']; 
        
        $totalLessons = 0;
        if (method_exists($this->lessonModel, 'getTotalLessonsByTeacher')) {
            $totalLessons = $this->lessonModel->getTotalLessonsByTeacher($teacherId);
        } elseif (method_exists($this->lessonModel, 'getByTeacher')) {
            $lessons = $this->lessonModel->getByTeacher($teacherId);
            $totalLessons = is_array($lessons) ? count($lessons) : 0;
        }
        
        $publishedLessons = [];
        if (method_exists($this->lessonModel, 'getPublishedLessonsByTeacher')) {
            $publishedLessons = $this->lessonModel->getPublishedLessonsByTeacher($teacherId, 10);
        }
        
        $profile = $this->userModel->getProfile($teacherId);
        
        if (!$profile) {
            $nameParts = explode(' ', $_SESSION['user_name'] ?? 'Teacher');
            $profile = [
                'id' => $teacherId,
                'first_name' => $nameParts[0] ?? '',
                'last_name' => $nameParts[1] ?? '',
                'email' => $_SESSION['user_email'] ?? '',
                'phone' => '',
                'role' => 'teacher',
                'bio' => '',
                'qualification' => '',
                'specialization' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'last_login' => date('Y-m-d H:i:s'),
                'profile_photo' => null
            ];
        }
        
        $studentsCount = 0;
        if (method_exists($this->userModel, 'countStudentsByTeacher')) {
            $studentsCount = $this->userModel->countStudentsByTeacher($teacherId);
        }
        $profile['students_count'] = $studentsCount;
        
        $classesCount = 0;
        if (isset($this->classModel) && method_exists($this->classModel, 'getByTeacher')) {
            $classes = $this->classModel->getByTeacher($teacherId);
            $classesCount = is_array($classes) ? count($classes) : 0;
        }
        $profile['classes_count'] = $classesCount;
        
        require_once __DIR__ . '/../views/teacher/profile.php';
    }
    
    /**
     * Update teacher profile
     */
    public function updateProfile() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/teacher/profile');
            exit;
        }
        
        $teacherId = $_SESSION['user_id'] ?? null;
        
        if (!$teacherId) {
            $_SESSION['error'] = 'Please login to update your profile';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $qualification = trim($_POST['qualification'] ?? '');
        $specialization = trim($_POST['specialization'] ?? '');
        
        if (empty($firstName) || empty($lastName) || empty($email)) {
            $_SESSION['error'] = 'Please fill in all required fields';
            header('Location: ' . BASE_URL . '/teacher/profile');
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid email address';
            header('Location: ' . BASE_URL . '/teacher/profile');
            exit;
        }
        
        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'bio' => $bio,
            'qualification' => $qualification,
            'specialization' => $specialization
        ];
        
        $result = $this->userModel->updateProfile($teacherId, $data);
        
        
        if ($result['success']) {
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            $_SESSION['user_email'] = $email;
            
            $_SESSION['success'] = 'Profile updated successfully!';
        } else {
            $_SESSION['error'] = $result['error'] ?? 'Failed to update profile. Please try again.';
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
        
        $lesson = $this->lessonModel->getById($lessonId);
        
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
     * Create a new quiz
     */
    public function createQuiz() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $subject_id = $_POST['subject_id'] ?? null;
            $class_id = $_POST['class_id'] ?? null;
            $time_limit = (int)($_POST['time_limit'] ?? 30);
            $passing_score = (int)($_POST['passing_score'] ?? 70);
            $max_attempts = (int)($_POST['max_attempts'] ?? 3);
            
            $is_published = isset($_POST['is_published']) ? 1 : 0;
            
            $errors = [];
            if (empty($title)) {
                $errors[] = 'Quiz title is required';
            }
            
            if (empty($class_id)) {
                $errors[] = 'Please select a class';
            }
            
            if (empty($subject_id)) {
                $errors[] = 'Please select a subject';
            }
            
            if (empty($errors)) {
                $data = [
                    'title' => $title,
                    'description' => $description,
                    'subject_id' => $subject_id,
                    'class_id' => $class_id,
                    'time_limit' => $time_limit,
                    'passing_score' => $passing_score,
                    'max_attempts' => $max_attempts,
                    'is_published' => $is_published,
                    'teacher_id' => $_SESSION['user_id']
                ];
                
                $result = $this->quizModel->createQuiz($data);
                
                if ($result['success']) {
                    $_SESSION['success'] = $result['message'];
                    header('Location: ' . BASE_URL . '/teacher/quizzes/add-questions/' . $result['quiz_id']);
                    exit;
                } else {
                    $_SESSION['error'] = $result['error'];
                }
            } else {
                $_SESSION['error'] = implode('<br>', $errors);
            }
            
            header('Location: ' . BASE_URL . '/teacher/quizzes/create');
            exit;
        }
        
        $subjects = $this->subjectModel->getAll();
        $classes = $this->classModel->getAll();
        
        require_once __DIR__ . '/../views/teacher/create_quiz.php';
    }


    /**
     * Edit quiz
     */
    public function editQuiz($quizId) {
        $hideFooter = true;
        
        $quiz = $this->quizModel->getById($quizId);
        
        if (!$quiz || $quiz['teacher_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Quiz not found or you do not have permission to edit it.';
            header('Location: ' . BASE_URL . '/teacher/quizzes');
            exit;
        }
        
        $classes = $this->classModel->getAll();
        
        $subjects = $this->subjectModel->getAll();
        
        $questions = $this->quizModel->getQuestions($quizId);
        $quiz['questions'] = $questions;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $class_id = $_POST['class_id'] ?? null;
            $subject_id = $_POST['subject_id'] ?? null;
            $time_limit = (int)($_POST['time_limit'] ?? 30);
            $passing_score = (int)($_POST['passing_score'] ?? 70);
            $max_attempts = (int)($_POST['max_attempts'] ?? 3);
            
            $is_published = isset($_POST['is_published']) && $_POST['is_published'] == '1' ? 1 : 0;
            
            $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
            
            $errors = [];
            if (empty($title)) {
                $errors[] = 'Quiz title is required';
            }
            
            if (empty($class_id)) {
                $errors[] = 'Please select a class';
            }
            
            if (empty($subject_id)) {
                $errors[] = 'Please select a subject';
            }
            
            if (empty($errors)) {
                $data = [
                    'title' => $title,
                    'description' => $description,
                    'class_id' => $class_id,
                    'subject_id' => $subject_id,
                    'time_limit' => $time_limit,
                    'passing_score' => $passing_score,
                    'max_attempts' => $max_attempts,
                    'is_published' => $is_published,
                    'end_date' => $end_date
                ];
                
                
                $result = $this->quizModel->updateQuiz($quizId, $data);
                
                if ($result['success']) {
                    $_SESSION['success'] = $result['message'];
                    header('Location: ' . BASE_URL . '/teacher/quizzes/edit/' . $quizId);
                    exit;
                } else {
                    $_SESSION['error'] = $result['error'];
                }
            } else {
                $_SESSION['error'] = implode('<br>', $errors);
            }
            
            header('Location: ' . BASE_URL . '/teacher/quizzes/edit/' . $quizId);
            exit;
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

        $questions = $this->quizModel->getQuestions($quizId);
        
        
        if (empty($questions)) {
            $_SESSION['error'] = 'This quiz has no questions yet. Please add questions before previewing.';
            header('Location: ' . BASE_URL . '/teacher/quizzes/edit/' . $quizId);
            exit;
        }
        
        require_once __DIR__ . '/../views/teacher/preview_quiz.php';
    }

    /**
     * Delete lesson material
     */
    public function deleteMaterial($materialId) {
        $hideFooter = true;
        
        $material = $this->lessonModel->getMaterialById($materialId);
        
        if (!$material) {
            $_SESSION['error'] = 'Material not found.';
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '<?php echo BASE_URL; ?>/teacher/lessons');
            exit;
        }
        
        $lesson = $this->lessonModel->getById($material['lesson_id']);
        
        if (!$lesson || $lesson['teacher_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'You do not have permission to delete this material.';
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '<?php echo BASE_URL; ?>/teacher/lessons');
            exit;
        }
        
        $result = $this->lessonModel->deleteMaterial($materialId);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Material deleted successfully.';
        } else {
            $_SESSION['error'] = $result['error'] ?? 'Failed to delete material.';
        }
        
        header('Location: ' . BASE_URL . '/teacher/lessons/edit/' . $material['lesson_id']);
        exit;
    }

    /**
     * Publish a quiz
     */
    public function publishQuiz($quizId) {
        $quiz = $this->quizModel->getById($quizId);
        
        if (!$quiz || $quiz['teacher_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Quiz not found or you do not have permission.';
            header('Location: ' . BASE_URL . '/teacher/quizzes');
            exit;
        }
        
        $questionCount = $this->quizModel->getQuestionCount($quizId);
        
        if ($questionCount == 0) {
            $_SESSION['error'] = 'Cannot publish a quiz with no questions. Please add questions first.';
            header('Location: ' . BASE_URL . '/teacher/quizzes/edit/' . $quizId);
            exit;
        }
        
        $result = $this->quizModel->updateQuizStatus($quizId, 'published');
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['error'];
        }
        
        header('Location: ' . BASE_URL . '/teacher/quizzes');
        exit;
    }

    /**
     * Unpublish a quiz
     */
    public function unpublishQuiz($quizId) {
        $quiz = $this->quizModel->getById($quizId);
        
        if (!$quiz || $quiz['teacher_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Quiz not found or you do not have permission.';
            header('Location: ' . BASE_URL . '/teacher/quizzes');
            exit;
        }
        
        $result = $this->quizModel->updateQuizStatus($quizId, 'draft');
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['error'];
        }
        
        header('Location: ' . BASE_URL . '/teacher/quizzes');
        exit;
    }

    /**
     * Edit a single quiz question
     */
    public function editQuestion($questionId) {
        $hideFooter = true;
        
        $question = $this->quizModel->getQuestionById($questionId);
        
        if (!$question) {
            $_SESSION['error'] = 'Question not found.';
            header('Location: ' . BASE_URL . '/teacher/quizzes');
            exit;
        }
        
        $quiz = $this->quizModel->getById($question['quiz_id']);
        
        if (!$quiz || $quiz['teacher_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'You do not have permission to edit this question.';
            header('Location: ' . BASE_URL . '/teacher/quizzes');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'question' => $_POST['question'] ?? '',
                'option_a' => $_POST['option_a'] ?? '',
                'option_b' => $_POST['option_b'] ?? '',
                'option_c' => $_POST['option_c'] ?? '',
                'option_d' => $_POST['option_d'] ?? '',
                'correct_answer' => $_POST['correct_answer'] ?? '',
                'points' => (int)($_POST['points'] ?? 1),
                'explanation' => $_POST['explanation'] ?? ''
            ];
            
            $errors = [];
            if (empty($data['question'])) {
                $errors[] = 'Question text is required';
            }
            if (empty($data['option_a'])) {
                $errors[] = 'Option A is required';
            }
            if (empty($data['option_b'])) {
                $errors[] = 'Option B is required';
            }
            if (empty($data['correct_answer'])) {
                $errors[] = 'Correct answer is required';
            }
            
            if (empty($errors)) {
                $result = $this->quizModel->updateQuestion($questionId, $data);
                
                if ($result['success']) {
                    $_SESSION['success'] = 'Question updated successfully!';
                    header('Location: ' . BASE_URL . '/teacher/quizzes/edit/' . $quiz['id']);
                    exit;
                } else {
                    $_SESSION['error'] = $result['error'];
                }
            } else {
                $_SESSION['error'] = implode('<br>', $errors);
            }
        }
        
        $quiz = $this->quizModel->getById($question['quiz_id']);
        
        require_once __DIR__ . '/../views/teacher/edit_question.php';
    }
}
?>