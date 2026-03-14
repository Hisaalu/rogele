<?php
// File: /views/learner/dashboard.php
$pageTitle = 'Learner Dashboard - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';
?>

<div style="padding: 40px 20px; max-width: 1200px; margin: 0 auto;">
    <h1 style="font-size: 2rem; margin-bottom: 20px; background: linear-gradient(135deg, #8B5CF6, #F97316); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Student'); ?>! 📚
    </h1>
    
    <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
        <h2 style="color: #1E293B; margin-bottom: 20px;">Student Dashboard</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <a href="/rays-of-grace/learner/materials" style="background: linear-gradient(135deg, #8B5CF6, #F97316); color: white; padding: 30px; border-radius: 15px; text-decoration: none; text-align: center; transition: transform 0.3s ease;">
                <i class="fas fa-book-open" style="font-size: 2rem; margin-bottom: 15px;"></i>
                <h3 style="margin-bottom: 10px;">My Lessons</h3>
                <p style="opacity: 0.9; font-size: 0.9rem;">Access your class materials</p>
            </a>
            
            <a href="/rays-of-grace/learner/quizzes" style="background: white; color: #1E293B; padding: 30px; border-radius: 15px; text-decoration: none; text-align: center; border: 2px solid #E2E8F0; transition: all 0.3s ease;">
                <i class="fas fa-pencil-alt" style="font-size: 2rem; margin-bottom: 15px; color: #F97316;"></i>
                <h3 style="margin-bottom: 10px;">Take Quizzes</h3>
                <p style="color: #64748B; font-size: 0.9rem;">Test your knowledge</p>
            </a>
            
            <a href="/rays-of-grace/learner/progress" style="background: white; color: #1E293B; padding: 30px; border-radius: 15px; text-decoration: none; text-align: center; border: 2px solid #E2E8F0; transition: all 0.3s ease;">
                <i class="fas fa-chart-line" style="font-size: 2rem; margin-bottom: 15px; color: #8B5CF6;"></i>
                <h3 style="margin-bottom: 10px;">My Progress</h3>
                <p style="color: #64748B; font-size: 0.9rem;">Track your learning</p>
            </a>
        </div>
    </div>
</div>

<style>
    a:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(139, 92, 246, 0.2);
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>