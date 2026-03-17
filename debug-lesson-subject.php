<?php
// File: /debug-lesson-subjects.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Lesson.php';

echo "<h1>Lesson Subject Debug</h1>";

try {
    $conn = new PDO("mysql:host=localhost;dbname=rays_of_grace_elearning", "root", "");
    
    // Get all lessons with their subject_id
    $stmt = $conn->query("SELECT l.id, l.title, l.subject_id, s.name as subject_name 
                          FROM lessons l
                          LEFT JOIN subjects s ON l.subject_id = s.id
                          ORDER BY l.id");
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>All Lessons (" . count($lessons) . ")</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Lesson ID</th><th>Title</th><th>subject_id</th><th>Subject Name</th></tr>";
    
    $lessonsWithSubjects = 0;
    $lessonsWithoutSubjects = 0;
    
    foreach ($lessons as $lesson) {
        $hasSubject = !empty($lesson['subject_id']) && $lesson['subject_id'] > 0;
        if ($hasSubject) {
            $lessonsWithSubjects++;
        } else {
            $lessonsWithoutSubjects++;
        }
        
        echo "<tr>";
        echo "<td>{$lesson['id']}</td>";
        echo "<td>{$lesson['title']}</td>";
        echo "<td>" . ($lesson['subject_id'] ?? 'NULL') . "</td>";
        echo "<td>" . ($lesson['subject_name'] ?? 'No matching subject') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p>Lessons with subjects: $lessonsWithSubjects</p>";
    echo "<p>Lessons without subjects: $lessonsWithoutSubjects</p>";
    
    // Get counts by subject
    echo "<h2>Lessons by Subject</h2>";
    $stmt = $conn->query("SELECT s.id, s.name, COUNT(l.id) as lesson_count 
                          FROM subjects s
                          LEFT JOIN lessons l ON s.id = l.subject_id AND l.is_published = 1
                          GROUP BY s.id
                          ORDER BY s.name");
    $subjectCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Subject ID</th><th>Subject Name</th><th>Lesson Count</th></tr>";
    foreach ($subjectCounts as $subject) {
        echo "<tr>";
        echo "<td>{$subject['id']}</td>";
        echo "<td>{$subject['name']}</td>";
        echo "<td>{$subject['lesson_count']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
}
?>