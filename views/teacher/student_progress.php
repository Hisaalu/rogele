<?php
// File: /views/teacher/student_progress.php
$pageTitle = 'Student Progress | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

$student = $student ?? [];
$quizResults = $quizResults ?? [];
$lessonProgress = $lessonProgress ?? [];
?>

<div class="progress-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <a href="<?php echo BASE_URL; ?>/teacher/students" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Students
            </a>
            <h1 class="page-title">
                <i class="fas fa-chart-line"></i>
                Student Progress
            </h1>
            <p class="page-subtitle">
                Tracking: <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
            </p>
        </div>
    </div>

    <!-- Student Info Card -->
    <div class="student-info-card">
        <div class="student-avatar-large">
            <?php if (!empty($student['profile_photo'])): ?>
                <img src="<?php echo BASE_URL; ?>/<?php echo $student['profile_photo']; ?>" alt="">
            <?php else: ?>
                <div class="avatar-placeholder-large">
                    <?php echo strtoupper(substr($student['first_name'] ?? 'S', 0, 1)); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="student-details">
            <h2><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h2>
            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($student['email']); ?></p>
            <p><i class="fas fa-graduation-cap"></i> <?php echo $student['class_name'] ?? 'No Class'; ?></p>
        </div>
        <div class="student-stats-quick">
            <div class="quick-stat">
                <span class="quick-value"><?php echo count($quizResults); ?></span>
                <span class="quick-label">Quizzes Taken</span>
            </div>
            <div class="quick-stat">
                <span class="quick-value"><?php echo count($lessonProgress); ?></span>
                <span class="quick-label">Lessons Viewed</span>
            </div>
        </div>
    </div>

    <!-- Quiz Results -->
    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-pencil-alt"></i> Quiz Performance</h3>
        </div>

        <?php if (empty($quizResults)): ?>
            <div class="empty-mini">
                <p>No quiz attempts yet.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="progress-table">
                    <thead>
                        <tr>
                            <th>Quiz</th>
                            <th>Score</th>
                            <th>Result</th>
                            <th>Time Taken</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quizResults as $result): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($result['quiz_title']); ?></strong></td>
                            <td class="score <?php echo $result['score'] >= 50 ? 'passed' : 'failed'; ?>">
                                <?php echo $result['score']; ?>%
                            </td>
                            <td>
                                <?php if ($result['score'] >= 50): ?>
                                    <span class="badge passed">Passed</span>
                                <?php else: ?>
                                    <span class="badge failed">Failed</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $minutes = floor($result['time_taken'] / 60);
                                $seconds = $result['time_taken'] % 60;
                                echo $minutes . 'm ' . $seconds . 's';
                                ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($result['completed_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Lesson Progress -->
    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-book-open"></i> Lesson Progress</h3>
        </div>

        <?php if (empty($lessonProgress)): ?>
            <div class="empty-mini">
                <p>No lessons viewed yet.</p>
            </div>
        <?php else: ?>
            <div class="lessons-list">
                <?php foreach ($lessonProgress as $lesson): ?>
                <div class="lesson-progress-item">
                    <div class="lesson-info">
                        <h4><?php echo htmlspecialchars($lesson['title']); ?></h4>
                        <p><i class="fas fa-clock"></i> Viewed on <?php echo date('M d, Y', strtotime($lesson['viewed_at'])); ?></p>
                    </div>
                    <span class="status completed">Completed</span>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.progress-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 30px 20px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #64748B;
    text-decoration: none;
    font-size: 0.95rem;
    margin-bottom: 15px;
    transition: color 0.3s ease;
}

.back-link:hover {
    color: #8B5CF6;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-subtitle {
    color: #64748B;
    font-size: 1rem;
    margin-bottom: 30px;
}

/* Student Info Card */
.student-info-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 30px;
    flex-wrap: wrap;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.student-avatar-large {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #8B5CF6, #F97316);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.student-avatar-large img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder-large {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 700;
    color: #8B5CF6;
}

.student-details {
    flex: 1;
}

.student-details h2 {
    color: #1E293B;
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.student-details p {
    color: #64748B;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.student-details i {
    color: #8B5CF6;
    width: 20px;
}

.student-stats-quick {
    display: flex;
    gap: 20px;
}

.quick-stat {
    text-align: center;
    background: #F8FAFC;
    padding: 15px 20px;
    border-radius: 12px;
    min-width: 100px;
}

.quick-value {
    display: block;
    font-size: 1.8rem;
    font-weight: 700;
    color: #8B5CF6;
    line-height: 1.2;
}

.quick-label {
    color: #64748B;
    font-size: 0.8rem;
}

/* Section Cards */
.section-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.section-header {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #F1F5F9;
}

.section-header h3 {
    color: #1E293B;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-header h3 i {
    color: #8B5CF6;
}

/* Table */
.table-responsive {
    overflow-x: auto;
}

.progress-table {
    width: 100%;
    border-collapse: collapse;
}

.progress-table th {
    background: #F8FAFC;
    color: #1E293B;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 12px 15px;
    text-align: left;
    border-bottom: 2px solid #E2E8F0;
}

.progress-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #F1F5F9;
    color: #1E293B;
}

.progress-table tr:hover td {
    background: #F8FAFC;
}

.score {
    font-weight: 600;
}

.score.passed {
    color: #10B981;
}

.score.failed {
    color: #EF4444;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
}

.badge.passed {
    background: #F0FDF4;
    color: #166534;
}

.badge.failed {
    background: #FEF2F2;
    color: #B91C1C;
}

/* Lessons List */
.lessons-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.lesson-progress-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #F8FAFC;
    border-radius: 12px;
}

.lesson-info h4 {
    color: #1E293B;
    font-size: 1rem;
    margin-bottom: 5px;
}

.lesson-info p {
    color: #64748B;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.status {
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status.completed {
    background: #F0FDF4;
    color: #166534;
}

/* Empty States */
.empty-mini {
    text-align: center;
    padding: 40px;
    color: #64748B;
    background: #F8FAFC;
    border-radius: 12px;
}

/* Responsive */
@media (max-width: 768px) {
    .student-info-card {
        flex-direction: column;
        text-align: center;
    }
    
    .student-details p {
        justify-content: center;
    }
    
    .student-stats-quick {
        width: 100%;
        justify-content: center;
    }
    
    .lesson-progress-item {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
}

/* Dark Mode */
/* @media (prefers-color-scheme: dark) {
    .student-info-card,
    .section-card {
        background: #1E293B;
    }
    
    .student-details h2 {
        color: #F1F5F9;
    }
    
    .quick-stat {
        background: #334155;
    }
    
    .quick-label {
        color: #94A3B8;
    }
    
    .section-header {
        border-bottom-color: #334155;
    }
    
    .section-header h3 {
        color: #F1F5F9;
    }
    
    .progress-table th {
        background: #334155;
        color: #F1F5F9;
    }
    
    .progress-table td {
        color: #F1F5F9;
        border-bottom-color: #334155;
    }
    
    .progress-table tr:hover td {
        background: #334155;
    }
    
    .lesson-progress-item {
        background: #334155;
    }
    
    .lesson-info h4 {
        color: #F1F5F9;
    }
    
    .empty-mini {
        background: #334155;
        color: #94A3B8;
    }
} */
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>