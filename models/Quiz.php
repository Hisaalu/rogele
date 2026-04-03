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
            return ['success' => false, 'error' => 'Failed to create quiz'];
        }
    }
    
    /**
     * Add questions to quiz
     */
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
            
            $updateQuery = "UPDATE quizzes SET updated_at = NOW() WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->execute([':id' => $quizId]);
            
            $this->conn->commit();
            return ['success' => true, 'message' => 'Questions added successfully'];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
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
            return [];
        }
    }
    
    /**
     * Get quiz by ID
     */
    public function getById($quizId) {
        try {
            $sql = "SELECT q.*, 
                        c.name as class_name,
                        s.name as subject_name,
                        (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count
                    FROM quizzes q
                    LEFT JOIN classes c ON q.class_id = c.id
                    LEFT JOIN subjects s ON q.subject_id = s.id
                    WHERE q.id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $quizId, PDO::PARAM_INT);
            $stmt->execute();
            
            $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($quiz) {
                $quiz['status'] = $quiz['is_published'] ? 'published' : 'draft';
                $quiz['time_limit'] = $quiz['time_limit'] ?? 30;
                $quiz['passing_score'] = $quiz['passing_score'] ?? 70;
                $quiz['max_attempts'] = $quiz['max_attempts'] ?? 3;
            }
            
            return $quiz;
            
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Start a new quiz attempt
     */
    public function startAttempt($quizId, $userId) {
        try {
            if ($this->hasReachedMaxAttempts($userId, $quizId)) {
                return ['success' => false, 'error' => 'You have used all your attempts for this quiz'];
            }
            
            $existingAttempt = $this->getInProgressAttempt($userId, $quizId);
            
            if ($existingAttempt) {
                $questions = $this->getQuestions($quizId);
                return [
                    'success' => true,
                    'attempt_id' => $existingAttempt['id'],
                    'questions' => $questions,
                    'message' => 'Resuming existing attempt'
                ];
            }
            
            $sql = "INSERT INTO quiz_attempts (quiz_id, user_id, status, started_at) 
                    VALUES (:quiz_id, :user_id, 'in_progress', NOW())";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $attemptId = $this->conn->lastInsertId();
            
            $questions = $this->getQuestions($quizId);
            
            return [
                'success' => true,
                'attempt_id' => $attemptId,
                'questions' => $questions
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Failed to start quiz'];
        }
    }
    
    /**
     * Submit quiz attempt
     */
    public function submitAttempt($attemptId, $answers) {
        try {
            
            $this->conn->beginTransaction();
            
            $sql = "SELECT * FROM quiz_attempts WHERE id = :attempt_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':attempt_id', $attemptId, PDO::PARAM_INT);
            $stmt->execute();
            $attempt = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$attempt) {
                return ['success' => false, 'error' => 'Attempt not found'];
            }
            
            if ($attempt['status'] == 'completed') {
                error_log("Attempt already completed - returning existing result");
                return [
                    'success' => true,
                    'score' => $attempt['score'],
                    'correct' => $attempt['correct_answers'],
                    'total' => $attempt['total_questions'],
                    'attempt_id' => $attemptId,
                    'already_submitted' => true
                ];
            }
            
            $quizId = $attempt['quiz_id'];
            $questions = $this->getQuestions($quizId);
            
            $correctAnswers = 0;
            $totalQuestions = count($questions);
            
            $deleteSql = "DELETE FROM quiz_attempt_answers WHERE attempt_id = :attempt_id";
            $deleteStmt = $this->conn->prepare($deleteSql);
            $deleteStmt->bindValue(':attempt_id', $attemptId, PDO::PARAM_INT);
            $deleteStmt->execute();
            
            $answerSql = "INSERT INTO quiz_attempt_answers (attempt_id, question_id, selected_answer, is_correct) 
                        VALUES (:attempt_id, :question_id, :selected_answer, :is_correct)";
            $answerStmt = $this->conn->prepare($answerSql);
            
            foreach ($questions as $question) {
                $correctOption = $question['correct_option'];
                $userAnswer = isset($answers[$question['id']]) ? $answers[$question['id']] : null;
                
                if (is_numeric($userAnswer)) {
                    $userAnswer = (int)$userAnswer;
                }

                if (is_string($userAnswer) && in_array(strtoupper($userAnswer), ['A', 'B', 'C', 'D'])) {
                    $letterToIndex = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3];
                    $userAnswer = $letterToIndex[strtoupper($userAnswer)];
                }
                
                $isCorrect = ($userAnswer !== null && $userAnswer == $correctOption) ? 1 : 0;
                
                if ($isCorrect) {
                    $correctAnswers++;
                }
                
                $answerStmt->bindValue(':attempt_id', $attemptId, PDO::PARAM_INT);
                $answerStmt->bindValue(':question_id', $question['id'], PDO::PARAM_INT);
                $answerStmt->bindValue(':selected_answer', $userAnswer);
                $answerStmt->bindValue(':is_correct', $isCorrect, PDO::PARAM_INT);
                $answerStmt->execute();
            }
            
            $score = ($totalQuestions > 0) ? round(($correctAnswers / $totalQuestions) * 100) : 0;
            
            $sql = "UPDATE quiz_attempts 
                    SET score = :score, 
                        correct_answers = :correct_answers,
                        total_questions = :total_questions,
                        status = 'completed',
                        completed_at = NOW()
                    WHERE id = :attempt_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':score', $score);
            $stmt->bindValue(':correct_answers', $correctAnswers);
            $stmt->bindValue(':total_questions', $totalQuestions);
            $stmt->bindValue(':attempt_id', $attemptId, PDO::PARAM_INT);
            $stmt->execute();
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'score' => $score,
                'correct' => $correctAnswers,
                'total' => $totalQuestions,
                'attempt_id' => $attemptId
            ];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => 'Failed to submit quiz: ' . $e->getMessage()];
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
            return [];
        }
    }
    
    /**
     * Get attempt details with quiz information
     */
    public function getAttemptDetails($attemptId) {
        try {
            $sql = "SELECT a.*, 
                        q.title as quiz_title, 
                        q.description as quiz_description,
                        q.passing_score,
                        q.time_limit
                    FROM quiz_attempts a
                    LEFT JOIN quizzes q ON a.quiz_id = q.id
                    WHERE a.id = :attempt_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':attempt_id', $attemptId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && !isset($result['passing_score'])) {
                $result['passing_score'] = 70; 
            }
            
            return $result;
            
        } catch (PDOException $e) {
            return null;
        }
    }
    
    // Get quiz statistics
    public function getQuizStats($quizId) {
        try {
            $stats = [];
            
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
            return ['success' => false, 'error' => 'Failed to delete quiz'];
        }
    }

    /**
     * Get total number of quizzes
     */
    public function getTotalQuizzes() {
        try {
            $query = "SELECT COUNT(*) as count FROM quizzes";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Get total quiz attempts
     */
    public function getTotalAttempts() {
        try {
            $query = "SELECT COUNT(*) as count FROM quiz_attempts WHERE status = 'completed'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Get average score across all quizzes
     */
    public function getAverageScore() {
        try {
            $query = "SELECT AVG(score) as avg_score FROM quiz_attempts WHERE status = 'completed'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return round($result['avg_score'] ?? 0, 1);
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Get quizzes by teacher
     */
    public function getByTeacher($teacherId, $limit = null, $offset = 0) {
        try {
            $query = "SELECT q.*, 
                    COUNT(DISTINCT qa.id) as attempt_count,
                    (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count
                    FROM quizzes q
                    LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id
                    WHERE q.teacher_id = :teacher_id
                    GROUP BY q.id
                    ORDER BY q.created_at DESC";
            
            if ($limit) {
                $query .= " LIMIT :limit OFFSET :offset";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':teacher_id', $teacherId);
            
            if ($limit) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Search quizzes by teacher
     */
    public function searchByTeacher($teacherId, $keyword) {
        try {
            $searchPattern = '%' . $keyword . '%';
            
            $query = "SELECT q.*, 
                        s.name as subject_name,
                        c.name as class_name,
                        (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count
                    FROM quizzes q
                    LEFT JOIN subjects s ON q.subject_id = s.id
                    LEFT JOIN classes c ON q.class_id = c.id
                    WHERE q.teacher_id = ? 
                    AND (q.title LIKE ? OR q.description LIKE ?)
                    ORDER BY q.created_at DESC
                    LIMIT 50";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(array($teacherId, $searchPattern, $searchPattern));
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return array();
        }
    }

    /**
     * Get quiz results
     */
    public function getResults($quizId) {
        try {
            $query = "SELECT qa.*, u.first_name, u.last_name, u.email
                    FROM quiz_attempts qa
                    JOIN users u ON qa.user_id = u.id
                    WHERE qa.quiz_id = :quiz_id AND qa.status = 'completed'
                    ORDER BY qa.score DESC, qa.completed_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':quiz_id' => $quizId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get average score by teacher
     */
    public function getAverageScoreByTeacher($teacherId) {
        try {
            $query = "SELECT AVG(qa.score) as avg_score
                    FROM quiz_attempts qa
                    JOIN quizzes q ON qa.quiz_id = q.id
                    WHERE q.teacher_id = :teacher_id AND qa.status = 'completed'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':teacher_id' => $teacherId]);
            $result = $stmt->fetch();
            
            return round($result['avg_score'] ?? 0, 1);
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Get performance by teacher
     */
    public function getPerformanceByTeacher($teacherId) {
        try {
            $query = "SELECT 
                        q.id,
                        q.title,
                        COUNT(DISTINCT qa.id) as total_attempts,
                        COUNT(DISTINCT qa.user_id) as unique_students,
                        AVG(qa.score) as avg_score,
                        MAX(qa.score) as highest_score,
                        MIN(qa.score) as lowest_score,
                        SUM(CASE WHEN qa.score >= q.passing_score THEN 1 ELSE 0 END) as passed_count
                    FROM quizzes q
                    LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.status = 'completed'
                    WHERE q.teacher_id = :teacher_id
                    GROUP BY q.id
                    ORDER BY total_attempts DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':teacher_id' => $teacherId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Update quiz
     */
    public function update($quizId, $data) {
        try {
            $query = "UPDATE quizzes SET 
                    title = :title,
                    description = :description,
                    subject_id = :subject_id,
                    class_id = :class_id,
                    time_limit = :time_limit,
                    passing_score = :passing_score,
                    max_attempts = :max_attempts,
                    start_date = :start_date,
                    end_date = :end_date,
                    is_published = :is_published,
                    updated_at = NOW()
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':title' => $data['title'],
                ':description' => $data['description'] ?? null,
                ':subject_id' => $data['subject_id'] ?? null,
                ':class_id' => $data['class_id'] ?? null,
                ':time_limit' => $data['time_limit'] ?? 30,
                ':passing_score' => $data['passing_score'] ?? 50,
                ':max_attempts' => $data['max_attempts'] ?? 3,
                ':start_date' => $data['start_date'] ?? date('Y-m-d H:i:s'),
                ':end_date' => $data['end_date'] ?? null,
                ':is_published' => $data['is_published'] ?? 0,
                ':id' => $quizId
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Quiz updated successfully'];
            }
            
            return ['success' => false, 'error' => 'Failed to update quiz'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error'];
        }
    }

    /**
     * Count students who have attempted quizzes for a specific teacher
     */
    public function countStudentsWithAttemptsByTeacher($teacherId) {
        try {
            $query = "SELECT COUNT(DISTINCT qa.user_id) as total
                    FROM quiz_attempts qa
                    JOIN quizzes q ON qa.quiz_id = q.id
                    WHERE q.teacher_id = :teacher_id
                    AND qa.status = 'completed'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':teacher_id' => $teacherId]);
            $result = $stmt->fetch();
            
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Get daily quiz performance for teacher
     */
    public function getDailyPerformance($teacherId, $days = 30) {
        try {
            $query = "SELECT 
                        DATE(qa.completed_at) as date,
                        AVG(qa.score) as avg_score,
                        COUNT(qa.id) as attempts
                    FROM quiz_attempts qa
                    JOIN quizzes q ON qa.quiz_id = q.id
                    WHERE q.teacher_id = :teacher_id
                        AND qa.status = 'completed'
                        AND qa.completed_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                    GROUP BY DATE(qa.completed_at)
                    ORDER BY date ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':teacher_id' => $teacherId,
                ':days' => $days
            ]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get all published quizzes for external users
     */
    public function getPublishedQuizzes() {
        try {
            $query = "SELECT q.*, 
                    s.name as subject_name,
                    c.name as class_name,
                    u.first_name as teacher_name,
                    u.last_name as teacher_last_name,
                    (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count
                    FROM quizzes q
                    LEFT JOIN subjects s ON q.subject_id = s.id
                    LEFT JOIN classes c ON q.class_id = c.id
                    LEFT JOIN users u ON q.teacher_id = u.id
                    WHERE q.is_published = 1
                    ORDER BY q.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get all published quizzes for external users
     */
    public function getAllQuizzes($userId = null) {
        try {
            error_log("=== getAllQuizzes START ===");
            
            $simpleQuery = "SELECT id, title FROM quizzes WHERE is_published = 1 ORDER BY id";
            $simpleStmt = $this->conn->query($simpleQuery);
            $simpleResult = $simpleStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($simpleResult as $q) {
                error_log("  ID: {$q['id']} - {$q['title']}");
            }
            
            $sql = "SELECT DISTINCT q.id, q.*, 
                            (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count,
                            (SELECT name FROM subjects WHERE id = q.subject_id) as subject_name,
                            (SELECT name FROM classes WHERE id = q.class_id) as class_name
                        FROM quizzes q
                        WHERE q.is_published = 1 
                        AND (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) > 0
                        GROUP BY q.id
                        ORDER BY q.id ASC";
            
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($quizzes as $q) {
                error_log("  SQL Result: ID={$q['id']}, Title={$q['title']}, QCount={$q['question_count']}");
            }
            
            // Check if quiz 30 is in the result
            $found30 = false;
            foreach ($quizzes as $q) {
                if ($q['id'] == 30) {
                    $found30 = true;
                    break;
                }
            }
            
            if (!$found30) {
                
                $checkQuestions = $this->conn->prepare("SELECT COUNT(*) as cnt FROM quiz_questions WHERE quiz_id = 30");
                $checkQuestions->execute();
                $qCount = $checkQuestions->fetch(PDO::FETCH_ASSOC);
                
                $checkPublished = $this->conn->prepare("SELECT is_published FROM quizzes WHERE id = 30");
                $checkPublished->execute();
                $published = $checkPublished->fetch(PDO::FETCH_ASSOC);
            }
            
            $uniqueQuizzes = [];
            $seenIds = [];
            foreach ($quizzes as $quiz) {
                if (!in_array($quiz['id'], $seenIds)) {
                    $seenIds[] = $quiz['id'];
                    $uniqueQuizzes[] = $quiz;
                }
            }

            if ($userId && !empty($uniqueQuizzes)) {
                foreach ($uniqueQuizzes as &$quiz) {
                    $quiz['completed'] = $this->hasCompletedQuiz($userId, $quiz['id']);
                    $quiz['in_progress'] = $this->getInProgressAttempt($userId, $quiz['id']) !== null;
                    $quiz['in_progress_attempt'] = $this->getInProgressAttempt($userId, $quiz['id']);
                    $quiz['best_score'] = $this->getBestScore($userId, $quiz['id']);
                    $quiz['attempt_count'] = $this->getAttemptCount($userId, $quiz['id']);
                    $quiz['status'] = 'published';
                }
            }
            
            
            return $uniqueQuizzes;
            
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Count all quizzes with filters (for admin)
     */
    public function countAllQuizzes($search = null, $teacherId = null, $status = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM quizzes q 
                    LEFT JOIN subjects s ON q.subject_id = s.id
                    WHERE 1=1";
            $params = array();
            
            if ($search && !empty($search)) {
                $searchPattern = '%' . $search . '%';
                $sql .= " AND (q.title LIKE ? OR q.description LIKE ? OR s.name LIKE ?)";
                $params[] = $searchPattern;
                $params[] = $searchPattern;
                $params[] = $searchPattern;
            }
            
            if ($teacherId && !empty($teacherId)) {
                $sql .= " AND q.teacher_id = ?";
                $params[] = $teacherId;
            }
            
            if ($status === 'published') {
                $sql .= " AND q.is_published = 1";
            } elseif ($status === 'draft') {
                $sql .= " AND q.is_published = 0";
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            
            return $result['total'] ?? 0;
            
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Approve quiz
     */
    public function approve($quizId) {
        try {
            $query = "UPDATE quizzes SET is_approved = 1 WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([':id' => $quizId]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Quiz approved'];
            }
            
            return ['success' => false, 'error' => 'Failed to approve quiz'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error'];
        }
    }

    /**
     * Reject quiz
     */
    public function reject($quizId) {
        try {
            $query = "UPDATE quizzes SET is_approved = 0 WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([':id' => $quizId]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Quiz rejected'];
            }
            
            return ['success' => false, 'error' => 'Failed to reject quiz'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error'];
        }
    }

    /**
     * Check if user has completed a quiz
     */
    public function hasCompletedQuiz($userId, $quizId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM quiz_attempts 
                    WHERE user_id = :user_id AND quiz_id = :quiz_id 
                    AND status = 'completed'";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $result['count'] ?? 0;
            
            return $count > 0;
            
        } catch (PDOException $e) {
            return false;
        }
    }


    /**
     * Get in-progress attempt for a user
     */
    public function getInProgressAttempt($userId, $quizId) {
        try {
            $sql = "SELECT * FROM quiz_attempts 
                    WHERE user_id = :user_id AND quiz_id = :quiz_id 
                    AND status = 'in_progress'
                    ORDER BY started_at DESC LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result : null;
            
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Start a new quiz attempt
     */
    public function startQuizAttempt($userId, $quizId) {
        try {
            if ($this->hasCompletedQuiz($userId, $quizId)) {
                return ['success' => false, 'error' => 'You have already completed this quiz'];
            }
            
            $inProgress = $this->getInProgressAttempt($userId, $quizId);
            if ($inProgress) {
                return [
                    'success' => true, 
                    'attempt_id' => $inProgress['id'],
                    'message' => 'Resuming existing attempt'
                ];
            }
            
            $sql = "INSERT INTO quiz_attempts (quiz_id, user_id, status, started_at) 
                    VALUES (:quiz_id, :user_id, 'in_progress', NOW())";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $attemptId = $this->conn->lastInsertId();
            
            return [
                'success' => true,
                'attempt_id' => $attemptId
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Failed to start quiz'];
        }
    }

    /**
     * Complete a quiz attempt
     */
    public function completeQuizAttempt($attemptId, $score, $correctAnswers, $timeTaken) {
        try {
            $sql = "UPDATE quiz_attempts 
                    SET score = :score, 
                        correct_answers = :correct_answers,
                        time_taken = :time_taken,
                        status = 'completed',
                        completed_at = NOW()
                    WHERE id = :attempt_id AND status = 'in_progress'";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':score', $score);
            $stmt->bindValue(':correct_answers', $correctAnswers);
            $stmt->bindValue(':time_taken', $timeTaken);
            $stmt->bindValue(':attempt_id', $attemptId, PDO::PARAM_INT);
            $stmt->execute();
            
            return ['success' => true];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Failed to complete quiz'];
        }
    }

    /**
     * Get total number of questions for a quiz
     */
    public function getTotalQuestions($quizId) {
        try {
            $sql = "SELECT COUNT(*) as total FROM quiz_questions WHERE quiz_id = :quiz_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
            
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Get all questions for a quiz
     * 
     * @param int $quizId Quiz ID
     * @return array Array of questions
     */
    public function getQuestions($quizId) {
        try {
            $sql = "SELECT * FROM quiz_questions WHERE quiz_id = :quiz_id ORDER BY id ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmt->execute();
            
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($questions as &$question) {
                $options = [];
                if (!empty($question['option_a'])) $options[] = $question['option_a'];
                if (!empty($question['option_b'])) $options[] = $question['option_b'];
                if (!empty($question['option_c'])) $options[] = $question['option_c'];
                if (!empty($question['option_d'])) $options[] = $question['option_d'];
                
                $question['options'] = $options;
                
                $correctMap = [
                    'A' => 0,
                    'B' => 1,
                    'C' => 2,
                    'D' => 3
                ];
                $correctAnswer = strtoupper(trim($question['correct_answer']));
                $question['correct_option'] = $correctMap[$correctAnswer] ?? 0;
                
                $question['question_text'] = $question['question'];
            }
            
            return $questions;
            
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get user's quiz results (all completed attempts)
     * 
     * @param int $userId User ID
     * @return array Array of quiz results
     */
    public function getUserQuizResults($userId) {
        try {
            $sql = "SELECT a.*, 
                        q.title as quiz_title,
                        q.description as quiz_description,
                        q.passing_score,
                        q.time_limit
                    FROM quiz_attempts a
                    LEFT JOIN quizzes q ON a.quiz_id = q.id
                    WHERE a.user_id = :user_id 
                    AND a.status = 'completed'
                    ORDER BY a.completed_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get best score for a specific quiz
     * 
     * @param int $userId User ID
     * @param int $quizId Quiz ID
     * @return int Best score percentage
     */
    public function getBestScore($userId, $quizId) {
        try {
            $sql = "SELECT MAX(score) as best_score 
                    FROM quiz_attempts 
                    WHERE user_id = :user_id 
                    AND quiz_id = :quiz_id 
                    AND status = 'completed'";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['best_score'] ?? 0;
            
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Get attempt count for a specific quiz
     * 
     * @param int $userId User ID
     * @param int $quizId Quiz ID
     * @return int Number of attempts
     */
    public function getAttemptCount($userId, $quizId) {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM quiz_attempts 
                    WHERE user_id = :user_id 
                    AND quiz_id = :quiz_id 
                    AND status = 'completed'";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
            
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Get database connection
     * 
     * @return PDO Database connection
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Get user's completed attempts with attempt IDs
     */
    public function getUserCompletedAttempts($userId) {
        try {
            $sql = "SELECT id, quiz_id, score, correct_answers, total_questions, completed_at 
                    FROM quiz_attempts 
                    WHERE user_id = :user_id AND status = 'completed'
                    ORDER BY completed_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get user's answers for a specific attempt
     * 
     * @param int $attemptId The attempt ID
     * @return array Array of answers with question_id as key
     */
    public function getUserAnswers($attemptId) {
        try {
            $sql = "SELECT question_id, selected_answer, is_correct 
                    FROM quiz_attempt_answers 
                    WHERE attempt_id = :attempt_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':attempt_id', $attemptId, PDO::PARAM_INT);
            $stmt->execute();
            
            $answers = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $answers[$row['question_id']] = $row['selected_answer'];
            }
            
            return $answers;
            
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Create a new quiz
     */
    public function createQuiz($data) {
        try {
            $sql = "INSERT INTO quizzes (title, description, subject_id, class_id, time_limit, passing_score, max_attempts, is_published, status, teacher_id, created_at) 
                    VALUES (:title, :description, :subject_id, :class_id, :time_limit, :passing_score, :max_attempts, :is_published, :status, :teacher_id, NOW())";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':title', $data['title']);
            $stmt->bindValue(':description', $data['description']);
            $stmt->bindValue(':subject_id', $data['subject_id']);
            $stmt->bindValue(':class_id', $data['class_id']);
            $stmt->bindValue(':time_limit', $data['time_limit'], PDO::PARAM_INT);
            $stmt->bindValue(':passing_score', $data['passing_score'], PDO::PARAM_INT);
            $stmt->bindValue(':max_attempts', $data['max_attempts'], PDO::PARAM_INT);
            $stmt->bindValue(':is_published', $data['is_published'], PDO::PARAM_INT);
            $stmt->bindValue(':status', $data['is_published'] ? 'published' : 'draft');
            $stmt->bindValue(':teacher_id', $data['teacher_id'], PDO::PARAM_INT);
            $stmt->execute();
            
            $quizId = $this->conn->lastInsertId();
            
            return [
                'success' => true,
                'message' => 'Quiz created successfully!',
                'quiz_id' => $quizId
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Failed to create quiz'];
        }
    }

    /**
     * Update quiz details
     */
    public function updateQuiz($quizId, $data) {
        try {
            $sql = "UPDATE quizzes SET 
                        title = :title,
                        description = :description,
                        class_id = :class_id,
                        subject_id = :subject_id,
                        time_limit = :time_limit,
                        passing_score = :passing_score,
                        max_attempts = :max_attempts,
                        is_published = :is_published,
                        end_date = :end_date,
                        updated_at = NOW()
                    WHERE id = :id AND teacher_id = :teacher_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':title', $data['title']);
            $stmt->bindValue(':description', $data['description']);
            $stmt->bindValue(':class_id', $data['class_id'], PDO::PARAM_INT);
            $stmt->bindValue(':subject_id', $data['subject_id'], PDO::PARAM_INT);
            $stmt->bindValue(':time_limit', $data['time_limit'], PDO::PARAM_INT);
            $stmt->bindValue(':passing_score', $data['passing_score'], PDO::PARAM_INT);
            $stmt->bindValue(':max_attempts', $data['max_attempts'], PDO::PARAM_INT);
            $stmt->bindValue(':is_published', $data['is_published'], PDO::PARAM_INT);
            $stmt->bindValue(':end_date', !empty($data['end_date']) ? $data['end_date'] : null);
            $stmt->bindValue(':id', $quizId, PDO::PARAM_INT);
            $stmt->bindValue(':teacher_id', $_SESSION['user_id'], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $statusSql = "UPDATE quizzes SET status = :status WHERE id = :id";
                $statusStmt = $this->conn->prepare($statusSql);
                $statusStmt->bindValue(':status', $data['is_published'] ? 'published' : 'draft');
                $statusStmt->bindValue(':id', $quizId, PDO::PARAM_INT);
                $statusStmt->execute();
                
                return [
                    'success' => true,
                    'message' => 'Quiz updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to update quiz'
                ];
            }
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if quiz is available (not expired)
     */
    public function isQuizAvailable($quizId) {
        try {
            $sql = "SELECT end_date, is_published FROM quizzes WHERE id = :quiz_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmt->execute();
            
            $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$quiz) {
                return false;
            }
            
            if ($quiz['is_published'] != 1) {
                return false;
            }
            
            if (empty($quiz['end_date'])) {
                return true;
            }
            
            $now = new DateTime();
            $endDate = new DateTime($quiz['end_date']);
            
            return $now <= $endDate;
            
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get quiz availability status with details
     * 
     * @param int $quizId Quiz ID
     * @return array Status details
     */
    public function getQuizAvailabilityStatus($quizId) {
        try {
            $sql = "SELECT end_date, is_published FROM quizzes WHERE id = :quiz_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmt->execute();
            
            $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$quiz) {
                return [
                    'available' => false,
                    'reason' => 'not_found',
                    'message' => 'Quiz not found'
                ];
            }
            
            if ($quiz['is_published'] != 1) {
                return [
                    'available' => false,
                    'reason' => 'not_published',
                    'message' => 'This quiz is not published yet.'
                ];
            }
            
            if (!empty($quiz['end_date'])) {
                $now = new DateTime();
                $endDate = new DateTime($quiz['end_date']);
                
                if ($now > $endDate) {
                    return [
                        'available' => false,
                        'reason' => 'expired',
                        'end_date' => $quiz['end_date'],
                        'message' => 'This quiz expired on ' . date('F j, Y', strtotime($quiz['end_date']))
                    ];
                }
                
                $daysRemaining = $now->diff($endDate)->days;
                return [
                    'available' => true,
                    'reason' => 'active',
                    'end_date' => $quiz['end_date'],
                    'days_remaining' => $daysRemaining,
                    'message' => 'Quiz expires in ' . $daysRemaining . ' days'
                ];
            }
            
            return [
                'available' => true,
                'reason' => 'active',
                'message' => 'Quiz is available'
            ];
            
        } catch (PDOException $e) {
            return [
                'available' => false,
                'reason' => 'error',
                'message' => 'Unable to verify quiz availability'
            ];
        }
    }

    /**
     * Get the number of questions for a quiz
     * 
     * @param int $quizId Quiz ID
     * @return int Number of questions
     */
    public function getQuestionCount($quizId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM quiz_questions WHERE quiz_id = :quiz_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
            
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Update quiz status (publish/unpublish)
     */
    public function updateQuizStatus($quizId, $status) {
        try {
            $isPublished = ($status == 'published') ? 1 : 0;
            
            $sql = "UPDATE quizzes 
                    SET is_published = :is_published, 
                        status = :status,
                        updated_at = NOW()
                    WHERE id = :id AND teacher_id = :teacher_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':is_published', $isPublished, PDO::PARAM_INT);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $quizId, PDO::PARAM_INT);
            $stmt->bindValue(':teacher_id', $_SESSION['user_id'], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => $status == 'published' ? 'Quiz published successfully' : 'Quiz unpublished successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to update quiz status'
                ];
            }
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test database connection and quiz data
     */
    public function testQuizData() {
        try {
            $result = [];
            
            $result['connection'] = $this->conn ? 'Connected' : 'Not connected';
            
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM quizzes");
            $result['total_quizzes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM quizzes WHERE is_published = 1");
            $result['published_quizzes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stmt = $this->conn->query("SELECT COUNT(DISTINCT quiz_id) as total FROM quiz_questions");
            $result['quizzes_with_questions'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stmt = $this->conn->query("
                SELECT q.id, q.title, q.is_published, COUNT(qq.id) as question_count
                FROM quizzes q
                LEFT JOIN quiz_questions qq ON q.id = qq.quiz_id
                WHERE q.is_published = 1
                GROUP BY q.id, q.title, q.is_published
            ");
            $result['published_quiz_details'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $result;
            
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get a single question by ID
     */
    public function getQuestionById($questionId) {
        try {
            $sql = "SELECT * FROM quiz_questions WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $questionId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Update a quiz question
     */
    public function updateQuestion($questionId, $data) {
        try {
            $sql = "UPDATE quiz_questions SET 
                        question = :question,
                        option_a = :option_a,
                        option_b = :option_b,
                        option_c = :option_c,
                        option_d = :option_d,
                        correct_answer = :correct_answer,
                        points = :points,
                        explanation = :explanation
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':question', $data['question']);
            $stmt->bindValue(':option_a', $data['option_a']);
            $stmt->bindValue(':option_b', $data['option_b']);
            $stmt->bindValue(':option_c', $data['option_c']);
            $stmt->bindValue(':option_d', $data['option_d']);
            $stmt->bindValue(':correct_answer', $data['correct_answer']);
            $stmt->bindValue(':points', $data['points'], PDO::PARAM_INT);
            $stmt->bindValue(':explanation', $data['explanation']);
            $stmt->bindValue(':id', $questionId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Question updated successfully'];
            }
            
            $error = $stmt->errorInfo();
            return ['success' => false, 'error' => 'Failed to update question: ' . $error[2]];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Check if user has reached maximum attempts for a quiz
     */
    public function hasReachedMaxAttempts($userId, $quizId) {
        try {
            $quizSql = "SELECT max_attempts FROM quizzes WHERE id = :quiz_id";
            $quizStmt = $this->conn->prepare($quizSql);
            $quizStmt->bindValue(':quiz_id', $quizId, PDO::PARAM_INT);
            $quizStmt->execute();
            $quiz = $quizStmt->fetch(PDO::FETCH_ASSOC);
            
            $maxAttempts = $quiz['max_attempts'] ?? 3;
            
            $sql = "SELECT COUNT(*) as count FROM quiz_attempts 
                    WHERE user_id = :user_id AND quiz_id = :quiz_id 
                    AND status = 'completed'";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $attemptCount = $result['count'] ?? 0;
            
            return $attemptCount >= $maxAttempts;
            
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get remaining attempts for a user
     */
    public function getRemainingAttempts($userId, $quizId) {
        try {
            $quizSql = "SELECT max_attempts FROM quizzes WHERE id = :quiz_id";
            $quizStmt = $this->conn->prepare($quizSql);
            $quizStmt->bindValue(':quiz_id', $quizId, PDO::PARAM_INT);
            $quizStmt->execute();
            $quiz = $quizStmt->fetch(PDO::FETCH_ASSOC);
            
            $maxAttempts = $quiz['max_attempts'] ?? 3;
            
            $sql = "SELECT COUNT(*) as count FROM quiz_attempts 
                    WHERE user_id = :user_id AND quiz_id = :quiz_id 
                    AND status = 'completed'";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $completedAttempts = $result['count'] ?? 0;
            
            return max(0, $maxAttempts - $completedAttempts);
            
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
 * Get quizzes by class ID
 */
public function getQuizzesByClass($classId) {
    try {
        $sql = "SELECT q.*, 
                    s.name as subject_name, 
                    c.name as class_name,
                    (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count
                FROM quizzes q
                LEFT JOIN subjects s ON q.subject_id = s.id
                LEFT JOIN classes c ON q.class_id = c.id
                WHERE q.class_id = :class_id
                AND q.is_published = 1
                AND (q.end_date IS NULL OR q.end_date > NOW())
                AND (q.start_date IS NULL OR q.start_date <= NOW())
                ORDER BY q.created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':class_id' => $classId]);
        
        $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($quizzes)) {
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId) {
                foreach ($quizzes as &$quiz) {
                    $inProgress = $this->getInProgressAttempt($userId, $quiz['id']);
                    if ($inProgress) {
                        $quiz['in_progress'] = true;
                        $quiz['attempt_id'] = $inProgress['id'];
                    }
                }
            }
        }
        
        return $quizzes;
        
    } catch (PDOException $e) {
        return [];
    }
}

    /**
     * Get all quizzes for a specific class with user progress
     */
    public function getQuizzesByClassWithProgress($classId, $userId) {
        try {
            $sql = "SELECT q.*, 
                        s.name as subject_name, 
                        c.name as class_name,
                        (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count,
                        (SELECT MAX(score) FROM quiz_attempts WHERE quiz_id = q.id AND user_id = :user_id) as best_score,
                        (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.id AND user_id = :user_id) as attempt_count,
                        (SELECT id FROM quiz_attempts WHERE quiz_id = q.id AND user_id = :user_id AND status = 'in_progress' LIMIT 1) as in_progress_attempt_id
                    FROM quizzes q
                    LEFT JOIN subjects s ON q.subject_id = s.id
                    LEFT JOIN classes c ON q.class_id = c.id
                    WHERE q.class_id = :class_id
                    AND q.is_published = 1
                    AND (q.end_date IS NULL OR q.end_date > NOW())
                    AND (q.start_date IS NULL OR q.start_date <= NOW())
                    ORDER BY q.created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':class_id' => $classId,
                ':user_id' => $userId
            ]);
            
            $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($quizzes as &$quiz) {
                $quiz['best_score'] = $quiz['best_score'] ?? 0;
                $quiz['attempt_count'] = $quiz['attempt_count'] ?? 0;
                $quiz['has_in_progress'] = !empty($quiz['in_progress_attempt_id']);
                $quiz['attempt_id'] = $quiz['in_progress_attempt_id'];
            }
            
            return $quizzes;
            
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>