<?php
// File: /test-materials.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';
require_once 'models/Lesson.php';
require_once 'models/Subject.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set a test user (if not logged in)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 25; // Use an existing external user ID
    $_SESSION['user_role'] = 'external';
    echo "<p style='color:orange'>⚠️ No user logged in. Using test user ID: 25</p>";
}

echo "<h1>🔍 Materials Search & Filter Debug Tool</h1>";

// ============================================
// SECTION 1: Check Database Connection
// ============================================
echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
echo "<h2>1. Database Connection</h2>";

try {
    $lessonModel = new Lesson();
    $conn = $lessonModel->getConnection();
    echo "<p style='color:green'>✅ Database connected successfully</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}
echo "</div>";

// ============================================
// SECTION 2: Check Lessons Table
// ============================================
echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
echo "<h2>2. Lessons Table Check</h2>";

// Check if lessons table exists
$stmt = $conn->query("SHOW TABLES LIKE 'lessons'");
if ($stmt->rowCount() == 0) {
    echo "<p style='color:red'>❌ Lessons table does not exist!</p>";
} else {
    echo "<p style='color:green'>✅ Lessons table exists</p>";
    
    // Get total lessons count
    $stmt = $conn->query("SELECT COUNT(*) as total FROM lessons");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalLessons = $row['total'];
    echo "<p>Total lessons in database: <strong>$totalLessons</strong></p>";
    
    // Get published lessons count
    $stmt = $conn->query("SELECT COUNT(*) as total FROM lessons WHERE is_published = 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $publishedLessons = $row['total'];
    echo "<p>Published lessons: <strong>$publishedLessons</strong></p>";
    
    // Get sample lessons
    echo "<h3>Sample Lessons (first 5):</h3>";
    $stmt = $conn->query("SELECT id, title, is_published, subject_id, class_id, created_at FROM lessons LIMIT 5");
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($lessons)) {
        echo "<p style='color:red'>❌ No lessons found in database!</p>";
    } else {
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #ddd;'><th>ID</th><th>Title</th><th>Published</th><th>Subject ID</th><th>Class ID</th><th>Created</th> </tr>";
        foreach ($lessons as $lesson) {
            echo "<tr>";
            echo "<td>{$lesson['id']}</td>";
            echo "<td>" . htmlspecialchars($lesson['title']) . "</td>";
            echo "<td>" . ($lesson['is_published'] ? '✅ Yes' : '❌ No') . "</td>";
            echo "<td>{$lesson['subject_id']}</td>";
            echo "<td>{$lesson['class_id']}</td>";
            echo "<td>{$lesson['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}
echo "</div>";

// ============================================
// SECTION 3: Check Subjects Table
// ============================================
echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
echo "<h2>3. Subjects Table Check</h2>";

$stmt = $conn->query("SHOW TABLES LIKE 'subjects'");
if ($stmt->rowCount() == 0) {
    echo "<p style='color:red'>❌ Subjects table does not exist!</p>";
} else {
    echo "<p style='color:green'>✅ Subjects table exists</p>";
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM subjects");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalSubjects = $row['total'];
    echo "<p>Total subjects: <strong>$totalSubjects</strong></p>";
    
    echo "<h3>Sample Subjects:</h3>";
    $stmt = $conn->query("SELECT id, name, class_id FROM subjects LIMIT 10");
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($subjects)) {
        echo "<p style='color:red'>❌ No subjects found!</p>";
    } else {
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #ddd;'><th>ID</th><th>Name</th><th>Class ID</th></tr>";
        foreach ($subjects as $subject) {
            echo "<tr>";
            echo "<td>{$subject['id']}</td>";
            echo "<td>" . htmlspecialchars($subject['name']) . "</td>";
            echo "<td>{$subject['class_id']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}
echo "</div>";

// ============================================
// SECTION 4: Test getPublishedLessons Method
// ============================================
echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
echo "<h2>4. Test getPublishedLessons() Method</h2>";

$subjectId = isset($_GET['subject']) ? (int)$_GET['subject'] : null;
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : null;

// Create form for testing
echo "<form method='GET' style='margin-bottom: 20px; padding: 15px; background: white; border-radius: 8px;'>";
echo "<h3>Test Filters:</h3>";
echo "<div style='display: flex; gap: 10px; flex-wrap: wrap;'>";
$searchValue = isset($searchTerm) ? htmlspecialchars($searchTerm) : '';
echo "<input type='text' name='search' placeholder='Search term...' value='" . $searchValue . "' style='padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
echo "<select name='subject' style='padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
echo "<option value=''>All Subjects</option>";
foreach ($subjects as $subject) {
    $selected = (isset($subjectId) && $subjectId == $subject['id']) ? 'selected' : '';
    echo "<option value='{$subject['id']}' $selected>" . htmlspecialchars($subject['name']) . "</option>";
}
echo "</select>";
echo "<button type='submit' style='padding: 8px 20px; background: #f06724; color: white; border: none; border-radius: 4px; cursor: pointer;'>Test</button>";
echo "<a href='?'>Clear</a>";
echo "</div>";
echo "</form>";

// Test getPublishedLessons
if (isset($searchTerm) && !empty($searchTerm)) {
    echo "<h3>Testing searchPublished('$searchTerm', " . ($subjectId ? $subjectId : 'null') . "):</h3>";
    $lessons = $lessonModel->searchPublished($searchTerm, $subjectId);
} else {
    echo "<h3>Testing getPublishedLessons(" . ($subjectId ? $subjectId : 'null') . "):</h3>";
    $lessons = $lessonModel->getPublishedLessons($subjectId);
}

echo "<p>Results found: <strong>" . count($lessons) . "</strong></p>";

if (empty($lessons)) {
    echo "<p style='color:red'>❌ No lessons found with current filters!</p>";
    
    // Show why no results
    echo "<h4>Possible reasons:</h4>";
    echo "<ul>";
    echo "<li>No published lessons exist</li>";
    echo "<li>The search term doesn't match any lesson titles or descriptions</li>";
    echo "<li>The selected subject has no lessons</li>";
    echo "<li>Lessons exist but are not published (is_published = 0)</li>";
    echo "</ul>";
    
    // Check for unpublished lessons
    $stmt = $conn->query("SELECT COUNT(*) as total FROM lessons WHERE is_published = 0");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $unpublished = $row['total'];
    if ($unpublished > 0) {
        echo "<p style='color:orange'>⚠️ There are $unpublished unpublished lessons that are not showing.</p>";
    }
} else {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #ddd;'>
            <th>ID</th>
            <th>Title</th>
            <th>Subject</th>
            <th>Class</th>
            <th>Teacher</th>
            <th>Materials</th>
            <th>Action</th>
           </tr>";
    foreach ($lessons as $lesson) {
        $subjectName = isset($lesson['subject_name']) ? $lesson['subject_name'] : 'N/A';
        $className = isset($lesson['class_name']) ? $lesson['class_name'] : 'N/A';
        $teacherName = isset($lesson['teacher_name']) ? $lesson['teacher_name'] : '';
        $teacherLastName = isset($lesson['teacher_last_name']) ? $lesson['teacher_last_name'] : '';
        $materialCount = isset($lesson['material_count']) ? $lesson['material_count'] : 0;
        
        echo "<tr>";
        echo "<td>{$lesson['id']}</td>";
        echo "<td><strong>" . htmlspecialchars($lesson['title']) . "</strong><br><small>" . htmlspecialchars(substr(isset($lesson['description']) ? $lesson['description'] : '', 0, 50)) . "...</small></td>";
        echo "<td>" . htmlspecialchars($subjectName) . "</td>";
        echo "<td>" . htmlspecialchars($className) . "</td>";
        echo "<td>" . htmlspecialchars($teacherName) . " " . htmlspecialchars($teacherLastName) . "</td>";
        echo "<td>$materialCount</td>";
        echo "<td><a href='" . BASE_URL . "/external/view-lesson/{$lesson['id']}' target='_blank'>View</a></td>";
        echo "</tr>";
    }
    echo "</table>";
}
echo "</div>";

// ============================================
// SECTION 5: Test Direct SQL Queries
// ============================================
echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
echo "<h2>5. Direct SQL Query Tests</h2>";

// Test 1: Get all published lessons
echo "<h3>Test 1: All published lessons</h3>";
$stmt = $conn->query("SELECT id, title, is_published FROM lessons WHERE is_published = 1 LIMIT 5");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<p>Found: " . count($results) . " published lessons</p>";

// Test 2: Search in titles
if (isset($searchTerm) && !empty($searchTerm)) {
    echo "<h3>Test 2: Direct SQL search for '$searchTerm' in titles</h3>";
    $stmt = $conn->prepare("SELECT id, title FROM lessons WHERE title LIKE :search AND is_published = 1 LIMIT 5");
    $stmt->execute(array(':search' => '%' . $searchTerm . '%'));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Found: " . count($results) . " lessons with '$searchTerm' in title</p>";
    foreach ($results as $r) {
        echo "- ID: {$r['id']}, Title: {$r['title']}<br>";
    }
}

// Test 3: Check lessons by subject
if (isset($subjectId) && $subjectId) {
    echo "<h3>Test 3: Lessons for subject ID: $subjectId</h3>";
    $stmt = $conn->prepare("SELECT l.id, l.title, s.name as subject_name 
                            FROM lessons l 
                            LEFT JOIN subjects s ON l.subject_id = s.id 
                            WHERE l.subject_id = :subject_id AND l.is_published = 1 
                            LIMIT 5");
    $stmt->execute(array(':subject_id' => $subjectId));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Found: " . count($results) . " lessons for this subject</p>";
    foreach ($results as $r) {
        echo "- ID: {$r['id']}, Title: {$r['title']}, Subject: {$r['subject_name']}<br>";
    }
}
echo "</div>";

// ============================================
// SECTION 6: Summary
// ============================================
echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
echo "<h2>6. Summary & Recommendations</h2>";

// Check if there are any published lessons
$stmt = $conn->query("SELECT COUNT(*) as total FROM lessons WHERE is_published = 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$publishedCount = $row['total'];

if ($publishedCount == 0) {
    echo "<p style='color:red'>❌ No published lessons found! Students won't see any lessons.</p>";
    echo "<p>Run this SQL to publish lessons:</p>";
    echo "<pre>UPDATE lessons SET is_published = 1 WHERE id IN (SELECT id FROM lessons LIMIT 5);</pre>";
} else {
    echo "<p style='color:green'>✅ There are $publishedCount published lessons.</p>";
}

// Check if there are lessons with subjects
$stmt = $conn->query("SELECT COUNT(*) as total FROM lessons WHERE subject_id IS NOT NULL AND is_published = 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$subjectLessons = $row['total'];
echo "<p>Lessons with subjects: $subjectLessons out of $publishedCount</p>";

if ($subjectLessons == 0 && $publishedCount > 0) {
    echo "<p style='color:orange'>⚠️ Published lessons exist but none have subjects assigned. Filtering by subject will show no results.</p>";
}

// Create test link
echo "<h3>Test Links:</h3>";
echo "<ul>";
echo "<li><a href='?'>Show all lessons</a></li>";
echo "<li><a href='?search=math'>Search for 'math'</a></li>";
if (!empty($subjects)) {
    $firstSubject = $subjects[0]['id'];
    echo "<li><a href='?subject=$firstSubject'>Filter by subject: " . htmlspecialchars($subjects[0]['name']) . "</a></li>";
}
echo "<li><a href='" . BASE_URL . "/external/materials'>Go to Materials Page</a></li>";
echo "</ul>";

echo "</div>";
?>