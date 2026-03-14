<?php
// File: /models/Quiz.php
require_once __DIR__ . '/../config/database.php';

class Quiz {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    // Create quiz
    public function create($data) {
        try {
            $query = "INSERT INTO quizzes (title, description, subject_id, class_id, teacher_id, time_limit, passing_score, max_attempts, start_date, end_date, is_published, created_at) 
                      VALUES (:title, :description, :subject_id, :class_id, :teacher_id, :time_limit, :passing_score, :max_attempts, :start_date, :end_date, :is_published, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':title' => $data['title'],
                ':description' => $data['description'] ?? null,
                ':subject_id' => $data['subject_id'] ?? null,
                ':class_id' => $data['class_id'] ?? null,
                ':teacher_id' => $data['teacher_id'],
                ':time_limit' => $data['time_limit'] ?? 30,
                ':passing_score' => $data['passing_score'] ?? 50,
                ':max_attempts' => $data['max_attempts'] ?? 3,
                ':start_date' => $data['start_date'] ?? date('Y-m-d H:i:s'),
                ':end_date' => $data['end_date'] ?? null,
                ':is_published' => $data['is_published'] ?? 0
            ]);
            
            if ($result) {
                return ['success' => true, 'quiz_id' => $this->conn->lastInsertId()];
            }
            
            return ['success' => false, 'error' => 'Failed to create quiz'];
        } catch (PDOException $e) {
            error_log("Quiz creation error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to create quiz'];
        }
    }
    
    // Add questions to quiz
    public function addQuestions($quizId, $questions) {
        try {
            $this->conn->beginTransaction();
            
            foreach ($questions as $question) {
                $query = "INSERT INTO quiz_questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer, points) 
                          VALUES (:quiz_id, :question, :option_a, :option_b, :option_c, :option_d, :correct_answer, :points)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->execute([
                    ':quiz_id' => $quizId,
                    ':question' => $question['question'],
                    ':option_a' => $question['option_a'],
                    ':option_b' => $question['option_b'],
                    ':option_c' => $question['option_c'] ?? null,
                    ':option_d' => $question['option_d'] ?? null,
                    ':correct_answer' => $question['correct_answer'],
                    ':points' => $question['points'] ?? 1
                ]);
            }
            
            $this->conn->commit();
            return ['success' => true, 'message' => 'Questions added successfully'];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Add questions error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to add questions'];
        }
    }
    
    // Get quizzes by class
    public function getByClass($classId) {
        try {
            $query = "SELECT q.*, s.name as subject_name, u.first_name as teacher_name,
                     (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count
                     FROM quizzes q
                     LEFT JOIN subjects s ON q.subject_id = s.id
                     LEFT JOIN users u ON q.teacher_id = u.id
                     WHERE q.class_id = :class_id AND q.is_published = 1
                     ORDER BY q.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':class_id' => $classId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get by class error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get quiz by ID with questions
    public function getById($quizId) {
        try {
            $query = "SELECT q.*, s.name as subject_name, u.first_name as teacher_name,
                     (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count
                     FROM quizzes q
                     LEFT JOIN subjects s ON q.subject_id = s.id
                     LEFT JOIN users u ON q.teacher_id = u.id
                     WHERE q.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $quizId]);
            
            $quiz = $stmt->fetch();
            
            if ($quiz) {
                // Get questions
                $questionQuery = "SELECT * FROM quiz_questions WHERE quiz_id = :quiz_id ORDER BY id";
                $questionStmt = $this->conn->prepare($questionQuery);
                $questionStmt->execute([':quiz_id' => $quizId]);
                $quiz['questions'] = $questionStmt->fetchAll();
            }
            
            return $quiz;
        } catch (PDOException $e) {
            error_log("Get by ID error: " . $e->getMessage());
            return null;
        }
    }
    
    // Start quiz attempt
    public function startAttempt($quizId, $userId) {
        try {
            // Check if user has reached max attempts
            $attemptQuery = "SELECT COUNT(*) as attempt_count FROM quiz_attempts 
                            WHERE quiz_id = :quiz_id AND user_id = :user_id AND status = 'completed'";
            $attemptStmt = $this->conn->prepare($attemptQuery);
            $attemptStmt->execute([
                ':quiz_id' => $quizId,
                ':user_id' => $userId
            ]);
            $attemptCount = $attemptStmt->fetch()['attempt_count'];
            
            $quizQuery = "SELECT max_attempts FROM quizzes WHERE id = :id";
            $quizStmt = $this->conn->prepare($quizQuery);
            $quizStmt->execute([':id' => $quizId]);
            $quiz = $quizStmt->fetch();
            
            if ($quiz && $attemptCount >= $quiz['max_attempts']) {
                return ['success' => false, 'error' => 'Maximum attempts reached'];
            }
            
            // Create new attempt
            $query = "INSERT INTO quiz_attempts (quiz_id, user_id, status, started_at) VALUES (:quiz_id, :user_id, 'in_progress', NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':quiz_id' => $quizId,
                ':user_id' => $userId
            ]);
            
            $attemptId = $this->conn->lastInsertId();
            
            // Get questions
            $questionsQuery = "SELECT * FROM quiz_questions WHERE quiz_id = :quiz_id ORDER BY id";
            $questionsStmt = $this->conn->prepare($questionsQuery);
            $questionsStmt->execute([':quiz_id' => $quizId]);
            $questions = $questionsStmt->fetchAll();
            
            return [
                'success' => true, 
                'attempt_id' => $attemptId,
                'questions' => $questions
            ];
        } catch (PDOException $e) {
            error_log("Start attempt error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to start quiz'];
        }
    }
    
    // Submit quiz attempt
    public function submitAttempt($attemptId, $answers) {
        try {
            $this->conn->beginTransaction();
            
            $correctAnswers = 0;
            $totalPoints = 0;
            $earnedPoints = 0;
            
            // Get attempt details
            $attemptQuery = "SELECT * FROM quiz_attempts WHERE id = :id";
            $attemptStmt = $this->conn->prepare($attemptQuery);
            $attemptStmt->execute([':id' => $attemptId]);
            $attempt = $attemptStmt->fetch();
            
            if (!$attempt) {
                return ['success' => false, 'error' => 'Attempt not found'];
            }
            
            // Save answers and calculate score
            foreach ($answers as $questionId => $selectedAnswer) {
                // Get correct answer
                $questionQuery = "SELECT correct_answer, points FROM quiz_questions WHERE id = :id";
                $questionStmt = $this->conn->prepare($questionQuery);
                $questionStmt->execute([':id' => $questionId]);
                $question = $questionStmt->fetch();
                
                $isCorrect = ($selectedAnswer === $question['correct_answer']);
                if ($isCorrect) {
                    $correctAnswers++;
                    $earnedPoints += $question['points'];
                }
                $totalPoints += $question['points'];
                
                // Save user answer
                $answerQuery = "INSERT INTO user_quiz_answers (attempt_id, question_id, selected_answer, is_correct) 
                                VALUES (:attempt_id, :question_id, :selected_answer, :is_correct)";
                $answerStmt = $this->conn->prepare($answerQuery);
                $answerStmt->execute([
                    ':attempt_id' => $attemptId,
                    ':question_id' => $questionId,
                    ':selected_answer' => $selectedAnswer,
                    ':is_correct' => $isCorrect ? 1 : 0
                ]);
            }
            
            // Calculate total questions
            $totalQuestionsQuery = "SELECT COUNT(*) as total FROM quiz_questions WHERE quiz_id = (SELECT quiz_id FROM quiz_attempts WHERE id = :id)";
            $totalQuestionsStmt = $this->conn->prepare($totalQuestionsQuery);
            $totalQuestionsStmt->execute([':id' => $attemptId]);
            $totalQuestions = $totalQuestionsStmt->fetch()['total'];
            
            // Calculate percentage score
            $score = ($totalQuestions > 0) ? round(($correctAnswers / $totalQuestions) * 100) : 0;
            
            // Calculate time taken
            $timeQuery = "SELECT TIMESTAMPDIFF(SECOND, started_at, NOW()) as time_taken FROM quiz_attempts WHERE id = :id";
            $timeStmt = $this->conn->prepare($timeQuery);
            $timeStmt->execute([':id' => $attemptId]);
            $timeTaken = $timeStmt->fetch()['time_taken'];
            
            // Update attempt
            $updateQuery = "UPDATE quiz_attempts SET 
                           status = 'completed',
                           completed_at = NOW(),
                           score = :score,
                           total_questions = :total_questions,
                           correct_answers = :correct_answers,
                           time_taken = :time_taken
                           WHERE id = :id";
            
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->execute([
                ':score' => $score,
                ':total_questions' => $totalQuestions,
                ':correct_answers' => $correctAnswers,
                ':time_taken' => $timeTaken,
                ':id' => $attemptId
            ]);
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'score' => $score,
                'correct' => $correctAnswers,
                'total' => $totalQuestions,
                'points_earned' => $earnedPoints,
                'total_points' => $totalPoints,
                'time_taken' => $timeTaken
            ];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Submit attempt error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to submit quiz'];
        }
    }
    
    // Get quiz results for user
    public function getUserResults($userId, $quizId = null) {
        try {
            $query = "SELECT qa.*, q.title as quiz_title, q.passing_score, q.time_limit as total_time,
                     s.name as subject_name
                     FROM quiz_attempts qa
                     JOIN quizzes q ON qa.quiz_id = q.id
                     LEFT JOIN subjects s ON q.subject_id = s.id
                     WHERE qa.user_id = :user_id";
            
            $params = [':user_id' => $userId];
            
            if ($quizId) {
                $query .= " AND qa.quiz_id = :quiz_id";
                $params[':quiz_id'] = $quizId;
            }
            
            $query .= " AND qa.status = 'completed'
                       ORDER BY qa.completed_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get user results error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get attempt details
    public function getAttemptDetails($attemptId) {
        try {
            $query = "SELECT qa.*, q.title as quiz_title, q.passing_score,
                     u.first_name, u.last_name
                     FROM quiz_attempts qa
                     JOIN quizzes q ON qa.quiz_id = q.id
                     JOIN users u ON qa.user_id = u.id
                     WHERE qa.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $attemptId]);
            
            $attempt = $stmt->fetch();
            
            if ($attempt) {
                // Get answers with questions
                $answersQuery = "SELECT uqa.*, qq.question, qq.correct_answer, qq.points,
                                qq.option_a, qq.option_b, qq.option_c, qq.option_d
                                FROM user_quiz_answers uqa
                                JOIN quiz_questions qq ON uqa.question_id = qq.id
                                WHERE uqa.attempt_id = :attempt_id";
                
                $answersStmt = $this->conn->prepare($answersQuery);
                $answersStmt->execute([':attempt_id' => $attemptId]);
                $attempt['answers'] = $answersStmt->fetchAll();
            }
            
            return $attempt;
        } catch (PDOException $e) {
            error_log("Get attempt details error: " . $e->getMessage());
            return null;
        }
    }
    
    // Get quiz statistics
    public function getQuizStats($quizId) {
        try {
            $stats = [];
            
            // Overall stats
            $overallQuery = "SELECT 
                            COUNT(*) as total_attempts,
                            COUNT(DISTINCT user_id) as unique_students,
                            AVG(score) as average_score,
                            MAX(score) as highest_score,
                            MIN(score) as lowest_score,
                            SUM(CASE WHEN score >= (SELECT passing_score FROM quizzes WHERE id = :quiz_id) THEN 1 ELSE 0 END) as passed_count
                            FROM quiz_attempts 
                            WHERE quiz_id = :quiz_id AND status = 'completed'";
            
            $overallStmt = $this->conn->prepare($overallQuery);
            $overallStmt->execute([':quiz_id' => $quizId]);
            $stats['overall'] = $overallStmt->fetch();
            
            // Score distribution
            $distQuery = "SELECT 
                         CASE 
                             WHEN score BETWEEN 0 AND 20 THEN '0-20'
                             WHEN score BETWEEN 21 AND 40 THEN '21-40'
                             WHEN score BETWEEN 41 AND 60 THEN '41-60'
                             WHEN score BETWEEN 61 AND 80 THEN '61-80'
                             ELSE '81-100'
                         END as score_range,
                         COUNT(*) as count
                         FROM quiz_attempts
                         WHERE quiz_id = :quiz_id AND status = 'completed'
                         GROUP BY score_range
                         ORDER BY score_range";
            
            $distStmt = $this->conn->prepare($distQuery);
            $distStmt->execute([':quiz_id' => $quizId]);
            $stats['distribution'] = $distStmt->fetchAll();
            
            // Question analysis
            $questionQuery = "SELECT 
                             qq.id,
                             qq.question,
                             COUNT(uqa.id) as times_answered,
                             SUM(CASE WHEN uqa.is_correct = 1 THEN 1 ELSE 0 END) as correct_count,
                             ROUND(AVG(CASE WHEN uqa.is_correct = 1 THEN 100 ELSE 0 END), 2) as correct_percentage
                             FROM quiz_questions qq
                             LEFT JOIN user_quiz_answers uqa ON qq.id = uqa.question_id
                             LEFT JOIN quiz_attempts qa ON uqa.attempt_id = qa.id
                             WHERE qq.quiz_id = :quiz_id AND (qa.status = 'completed' OR qa.status IS NULL)
                             GROUP BY qq.id";
            
            $questionStmt = $this->conn->prepare($questionQuery);
            $questionStmt->execute([':quiz_id' => $quizId]);
            $stats['questions'] = $questionStmt->fetchAll();
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Get quiz stats error: " . $e->getMessage());
            return null;
        }
    }
    
    // Get available quizzes for user
    public function getAvailableQuizzes($userId, $classId = null) {
        try {
            $query = "SELECT q.*, s.name as subject_name,
                     (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.id AND user_id = :user_id AND status = 'completed') as attempts_taken
                     FROM quizzes q
                     LEFT JOIN subjects s ON q.subject_id = s.id
                     WHERE q.is_published = 1";
            
            $params = [':user_id' => $userId];
            
            if ($classId) {
                $query .= " AND (q.class_id = :class_id OR q.class_id IS NULL)";
                $params[':class_id'] = $classId;
            }
            
            $query .= " ORDER BY q.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get available quizzes error: " . $e->getMessage());
            return [];
        }
    }
    
    // Delete quiz
    public function delete($quizId) {
        try {
            $query = "DELETE FROM quizzes WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([':id' => $quizId]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Quiz deleted successfully'];
            }
            
            return ['success' => false, 'error' => 'Failed to delete quiz'];
        } catch (PDOException $e) {
            error_log("Quiz deletion error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to delete quiz'];
        }
    }
}
?>