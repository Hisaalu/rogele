<?php
// File: /views/external/quizzes.php
$pageTitle = 'Practice Quizzes - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';
?>

<div style="padding: 40px 20px; max-width: 1200px; margin: 0 auto;">
    <h1 style="font-size: 2rem; margin-bottom: 30px; background: linear-gradient(135deg, #8B5CF6, #F97316); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        Practice Quizzes
    </h1>
    
    <div style="background: white; border-radius: 20px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 40px;">
            <i class="fas fa-pencil-alt" style="font-size: 4rem; color: #CBD5E1; margin-bottom: 20px;"></i>
            <h3 style="color: #1E293B; margin-bottom: 10px;">Quizzes Coming Soon!</h3>
            <p style="color: #64748B; max-width: 500px; margin: 0 auto;">
                We're preparing interactive quizzes to help you test your knowledge. Check back soon!
            </p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
            <div style="background: #F8FAFC; padding: 30px; border-radius: 15px; text-align: center;">
                <i class="fas fa-clock" style="font-size: 2rem; color: #8B5CF6; margin-bottom: 15px;"></i>
                <h4 style="margin-bottom: 10px;">Timed Quizzes</h4>
                <p style="color: #64748B; font-size: 0.9rem;">Practice under real exam conditions</p>
            </div>
            
            <div style="background: #F8FAFC; padding: 30px; border-radius: 15px; text-align: center;">
                <i class="fas fa-chart-line" style="font-size: 2rem; color: #F97316; margin-bottom: 15px;"></i>
                <h4 style="margin-bottom: 10px;">Track Progress</h4>
                <p style="color: #64748B; font-size: 0.9rem;">See your improvement over time</p>
            </div>
            
            <div style="background: #F8FAFC; padding: 30px; border-radius: 15px; text-align: center;">
                <i class="fas fa-trophy" style="font-size: 2rem; color: #8B5CF6; margin-bottom: 15px;"></i>
                <h4 style="margin-bottom: 10px;">Earn Badges</h4>
                <p style="color: #64748B; font-size: 0.9rem;">Get rewarded for your achievements</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>