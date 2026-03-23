<?php
require_once 'config/config.php';
require_once 'models/Quiz.php';

$quizModel = new Quiz();

// Get a test attempt ID (replace with an actual attempt ID)
$attemptId = 40; // From your debug

// Test answers
$testAnswers = [
    62 => 0,
    63 => 1,
    64 => 2,
    65 => 2,
    66 => 2
];

echo "<h2>Testing Submit Attempt</h2>";
echo "<p>Attempt ID: $attemptId</p>";
echo "<p>Test Answers: " . print_r($testAnswers, true) . "</p>";

$result = $quizModel->submitAttempt($attemptId, $testAnswers);

echo "<h3>Result:</h3>";
echo "<pre>";
print_r($result);
echo "</pre>";

// Check if answers were saved
$conn = $quizModel->getConnection();
$stmt = $conn->prepare("SELECT * FROM quiz_attempt_answers WHERE attempt_id = ?");
$stmt->execute([$attemptId]);
$saved = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Saved Answers:</h3>";
echo "<pre>";
print_r($saved);
echo "</pre>";
?>