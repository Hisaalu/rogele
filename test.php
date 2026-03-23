<?php
// File: /force-reset-quiz.php
require_once 'config/config.php';
require_once 'models/Quiz.php';

session_start();

echo "<h2>Force Reset Quiz Attempts</h2>";

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo "<p>Please log in first.</p>";
    echo "<a href='" . BASE_URL . "/login'>Login</a>";
    exit;
}

echo "<p>Logged in as User ID: <strong>$userId</strong></p>";

$quizModel = new Quiz();
$conn = $quizModel->getConnection();

// Show current attempts
$stmt = $conn->prepare("
    SELECT a.id, a.quiz_id, q.title, a.status, a.score, a.started_at
    FROM quiz_attempts a
    JOIN quizzes q ON a.quiz_id = q.id
    WHERE a.user_id = :user_id
    ORDER BY a.id DESC
");
$stmt->execute([':user_id' => $userId]);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Your Current Attempts:</h3>";
if (empty($attempts)) {
    echo "<p style='color:green'>✅ No attempts found! You can take quizzes normally.</p>";
} else {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr style='background:#f1f5f9;'><th>ID</th><th>Quiz ID</th><th>Quiz Title</th><th>Status</th><th>Score</th><th>Started</th><th>Action</th></tr>";
    foreach ($attempts as $attempt) {
        echo "<tr>";
        echo "<td>{$attempt['id']}</td>";
        echo "<td>{$attempt['quiz_id']}</td>";
        echo "<td>" . htmlspecialchars($attempt['title']) . "</td>";
        echo "<td>{$attempt['status']}</td>";
        echo "<td>{$attempt['score']}%</td>";
        echo "<td>{$attempt['started_at']}</td>";
        echo "<td><a href='?delete={$attempt['id']}' onclick='return confirm(\"Delete this attempt?\")' style='color:red;'>Delete</a></td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Handle single delete
if (isset($_GET['delete'])) {
    $attemptId = (int)$_GET['delete'];
    
    // Delete answers
    $stmt = $conn->prepare("DELETE FROM quiz_attempt_answers WHERE attempt_id = ?");
    $stmt->execute([$attemptId]);
    
    // Delete attempt
    $stmt = $conn->prepare("DELETE FROM quiz_attempts WHERE id = ?");
    $stmt->execute([$attemptId]);
    
    echo "<p style='color:green'>✅ Attempt #$attemptId deleted!</p>";
    echo "<meta http-equiv='refresh' content='2'>";
}

// Bulk delete
echo "<h3>Bulk Actions:</h3>";
echo "<ul>";
echo "<li><a href='?delete_all=1' onclick='return confirm(\"Delete ALL your quiz attempts?\")' style='color:red;'>Delete ALL My Attempts</a></li>";
echo "<li><a href='?delete_quiz=27' onclick='return confirm(\"Delete attempts for Quiz 27?\")'>Delete Attempts for Quiz 27 (Mathematics 001)</a></li>";
echo "<li><a href='?delete_quiz=28'>Delete Attempts for Quiz 28 (Science)</a></li>";
echo "<li><a href='?delete_quiz=29'>Delete Attempts for Quiz 29 (English)</a></li>";
echo "<li><a href='?delete_quiz=30'>Delete Attempts for Quiz 30 (Social Studies)</a></li>";
echo "</ul>";

if (isset($_GET['delete_all'])) {
    // Delete answers
    $stmt = $conn->prepare("DELETE FROM quiz_attempt_answers WHERE attempt_id IN (SELECT id FROM quiz_attempts WHERE user_id = ?)");
    $stmt->execute([$userId]);
    
    // Delete attempts
    $stmt = $conn->prepare("DELETE FROM quiz_attempts WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    echo "<p style='color:green'>✅ All attempts deleted!</p>";
    echo "<meta http-equiv='refresh' content='2'>";
}

if (isset($_GET['delete_quiz'])) {
    $quizId = (int)$_GET['delete_quiz'];
    
    // Delete answers
    $stmt = $conn->prepare("DELETE FROM quiz_attempt_answers WHERE attempt_id IN (SELECT id FROM quiz_attempts WHERE user_id = ? AND quiz_id = ?)");
    $stmt->execute([$userId, $quizId]);
    
    // Delete attempts
    $stmt = $conn->prepare("DELETE FROM quiz_attempts WHERE user_id = ? AND quiz_id = ?");
    $stmt->execute([$userId, $quizId]);
    
    echo "<p style='color:green'>✅ Attempts for quiz $quizId deleted!</p>";
    echo "<meta http-equiv='refresh' content='2'>";
}

echo "<p><a href='" . BASE_URL . "/external/quizzes' class='btn-primary'>Go to Quizzes Page</a></p>";
?>