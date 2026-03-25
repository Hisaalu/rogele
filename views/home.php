<!-- File: /views/home.php -->
<?php 
$pageTitle = 'Home | ROGELE';
require_once __DIR__ . '/layouts/header.php'; 
?>

<style>
/* Home Page Specific Styles - Using ROGELE Brand Colors */
:root {
    --primary-orange: #f06724;
    --primary-orange-dark: #e05a1a;
    --primary-orange-light: #f27d43;
    --secondary-purple: #7f2677;
    --secondary-purple-dark: #6b1f64;
    --secondary-purple-light: #943a8b;
    --gradient-primary: linear-gradient(135deg, #7f2677);
    --gradient-soft: linear-gradient(135deg, rgba(240, 103, 36, 0.1), rgba(127, 38, 119, 0.1));
}

/* Reset to prevent horizontal scroll */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    overflow-x: hidden;
    width: 100%;
    position: relative;
}

.home-page {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
}

.container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Hero Section */
.hero-section {
    background: var(--gradient-primary);
    padding: 60px 0;
    width: 100%;
    position: relative;
    overflow: hidden;
}

.hero-wrapper {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    align-items: center;
}

.hero-content {
    max-width: 100%;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 20px;
    border-radius: 50px;
    margin-bottom: 30px;
    width: fit-content;
}

.badge-pulse {
    width: 8px;
    height: 8px;
    background: green;
    border-radius: 50%;
    position: relative;
}

.badge-pulse::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    background: #FFD700;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    100% { transform: scale(3); opacity: 0; }
}

.badge-text {
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
}

.hero-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 20px;
    color: white;
}

.hero-title span {
    display: block;
}

.hero-title .title-gradient {
    background: linear-gradient(135deg, #f06724);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.hero-description {
    font-size: clamp(1rem, 2vw, 1.2rem);
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 40px;
    max-width: 500px;
}

.hero-cta {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 50px;
}

.btn-primary {
    background: white;
    color: var(--primary-orange);
    padding: 14px 32px;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.btn-outline {
    background: transparent;
    color: white;
    border: 2px solid white;
    padding: 14px 32px;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-outline:hover {
    background: white;
    color: var(--primary-orange);
}

.hero-stats {
    display: flex;
    gap: 40px;
    background: rgba(255, 255, 255, 0.1);
    padding: 20px 30px;
    border-radius: 20px;
    width: fit-content;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: 800;
    color: white;
}

.stat-label {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.8);
}

.stat-divider {
    width: 2px;
    height: 40px;
    background: rgba(255, 255, 255, 0.3);
}

.hero-image {
    position: relative;
    display: flex;
    justify-content: center;
}

.hero-image img {
    max-width: 100%;
    height: auto;
    animation: float 6s ease infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

/* How It Works Section */
.how-it-works {
    padding: 80px 0;
    background: white;
    width: 100%;
}

.section-header {
    text-align: center;
    margin-bottom: 50px;
}

.section-subtitle {
    display: inline-block;
    background: var(--gradient-soft);
    color: var(--primary-orange);
    padding: 5px 20px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.section-title {
    font-size: clamp(1.8rem, 4vw, 2.5rem);
    font-weight: 800;
    margin-bottom: 15px;
}

.section-title span {
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.section-description {
    color: black;
    max-width: 600px;
    margin: 0 auto;
}

.steps-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    margin-top: 50px;
}

.step-card {
    background: #F8FAFC;
    padding: 40px 30px;
    border-radius: 20px;
    text-align: center;
    position: relative;
    transition: all 0.3s ease;
    border: 1px solid #E2E8F0;
}

.step-card:hover {
    transform: translateY(-10px);
    border-color: var(--primary-orange);
    box-shadow: 0 20px 40px rgba(240, 103, 36, 0.1);
}

.step-number {
    width: 60px;
    height: 60px;
    background: var(--gradient-primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0 auto 25px;
}

.step-icon {
    font-size: 2.5rem;
    margin-bottom: 20px;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.step-card h3 {
    font-size: 1.3rem;
    margin-bottom: 15px;
    color: #1E293B;
}

.step-card p {
    color: black;
    line-height: 1.6;
}

/* Features Section */
.features-section {
    padding: 80px 0;
    background: #F8FAFC;
    width: 100%;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    margin-top: 50px;
}

.feature-card {
    background: white;
    padding: 40px 30px;
    border-radius: 20px;
    text-align: center;
    transition: all 0.3s ease;
    border: 1px solid #E2E8F0;
}

.feature-card:hover {
    transform: translateY(-10px);
    border-color: var(--primary-orange);
    box-shadow: 0 20px 40px rgba(240, 103, 36, 0.1);
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: var(--gradient-soft);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    font-size: 2rem;
    color: var(--primary-orange);
    transition: all 0.3s ease;
}

.feature-card:hover .feature-icon {
    background: var(--gradient-primary);
    color: white;
}

.feature-card h3 {
    font-size: 1.3rem;
    margin-bottom: 15px;
    color: #1E293B;
}

.feature-card p {
    color: black;
    line-height: 1.6;
}

/* Classes Section */
.classes-section {
    padding: 80px 0;
    background: white;
    width: 100%;
}

.classes-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-top: 50px;
}

.class-card {
    background: #F8FAFC;
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid #E2E8F0;
}

.class-card:hover {
    transform: translateY(-5px);
    border-color: var(--primary-orange);
    box-shadow: 0 10px 30px rgba(240, 103, 36, 0.1);
}

.class-image {
    height: 100px;
    background: var(--gradient-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.class-level {
    font-size: 3rem;
    font-weight: 800;
    color: rgba(255, 255, 255, 0.2);
}

.class-content {
    padding: 20px;
}

.class-content h3 {
    font-size: 1.1rem;
    margin-bottom: 8px;
    color: #1E293B;
}

.class-content p {
    font-size: 0.9rem;
    color: black;
    margin-bottom: 15px;
}

.class-features {
    list-style: none;
    margin-bottom: 15px;
}

.class-features li {
    font-size: 0.85rem;
    color: black;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.class-features i {
    color: var(--primary-orange);
    font-size: 0.8rem;
}

.btn-secondary {
    background: var(--gradient-soft);
    color: var(--primary-orange);
    padding: 8px 16px;
    border-radius: 50px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 600;
    display: inline-block;
    transition: all 0.3s ease;
    border: 1px solid rgba(240, 103, 36, 0.3);
}

.btn-secondary:hover {
    background: var(--gradient-primary);
    color: white;
}

/* CTA Section */
.cta-section {
    padding: 80px 0;
    background: var(--gradient-primary);
    width: 100%;
    text-align: center;
}

.cta-content h2 {
    font-size: clamp(1.8rem, 4vw, 2.5rem);
    color: white;
    margin-bottom: 15px;
}

.cta-content p {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 30px;
}

.cta-content .btn-primary {
    background: white;
    color: var(--primary-orange);
}

.cta-content .btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

/* Responsive Breakpoints */
@media (max-width: 1024px) {
    .classes-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .hero-wrapper {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .hero-badge {
        margin-left: auto;
        margin-right: auto;
    }
    
    .hero-description {
        margin-left: auto;
        margin-right: auto;
    }
    
    .hero-cta {
        justify-content: center;
    }
    
    .hero-stats {
        margin: 0 auto;
    }
    
    .steps-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .features-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .classes-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .container {
        padding: 0 15px;
    }
    
    .hero-cta {
        flex-direction: column;
        gap: 10px;
    }
    
    .hero-cta a {
        width: 100%;
        justify-content: center;
    }
    
    .hero-stats {
        flex-direction: column;
        gap: 20px;
        width: 100%;
    }
    
    .stat-divider {
        width: 100%;
        height: 2px;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .classes-grid {
        grid-template-columns: 1fr;
    }
    
    .step-card {
        padding: 30px 20px;
    }
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeInUp 0.6s ease forwards;
}

.delay-1 { animation-delay: 0.2s; }
.delay-2 { animation-delay: 0.4s; }
.delay-3 { animation-delay: 0.6s; }
</style>

<div class="home-page">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-wrapper">
                <div class="hero-content animate-fade-in">
                    <div class="hero-badge">
                        <span class="badge-pulse"></span>
                        <span class="badge-text">Welcome to ROGELE</span>
                    </div>
                    
                    <h1 class="hero-title">
                        <span>Quality Education</span>
                        <span class="title-gradient">For Every Child</span>
                    </h1>
                    
                    <p class="hero-description">
                        Join our innovative e-learning platform from Primary 1 to Primary 7. 
                        Interactive lessons, smart quizzes, and progress tracking all in one place.
                    </p>
                    
                    <div class="hero-cta">
                        <a href="<?php echo BASE_URL; ?>/register" class="btn-primary">
                            <span>Start Free Trial</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="#how-it-works" class="btn-outline">
                            <i class="fas fa-play"></i>
                            <span>See How It Works</span>
                        </a>
                    </div>
                    
                    <div class="hero-stats">
                        <div class="stat-item">
                            <span class="stat-number">500+</span>
                            <span class="stat-label">Students</span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <span class="stat-number">50+</span>
                            <span class="stat-label">Teachers</span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <span class="stat-number">1000+</span>
                            <span class="stat-label">Lessons</span>
                        </div>
                    </div>
                </div>
                
                <div class="hero-image animate-fade-in delay-2">
                    <img src="<?php echo BASE_URL; ?>/public/images/logo.jpg" 
                        alt="ROGELE Logo" 
                        style="width: 300px; height: auto; max-width: 100%;">
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-header">
                <span class="section-subtitle">Simple Process</span>
                <h2 class="section-title">Start Learning in <span>3 Easy Steps</span></h2>
                <p class="section-description">
                    Get started with our platform in just a few minutes
                </p>
            </div>
            
            <div class="steps-container">
                <div class="step-card animate-fade-in">
                    <div class="step-number">1</div>
                    <div class="step-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h3>Create Account</h3>
                    <p>Sign up for free and get 2 months trial access to all features</p>
                </div>
                
                <div class="step-card animate-fade-in delay-1">
                    <div class="step-number">2</div>
                    <div class="step-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>Choose Your Class</h3>
                    <p>Select your class and start exploring lessons and materials</p>
                </div>
                
                <div class="step-card animate-fade-in delay-2">
                    <div class="step-number">3</div>
                    <div class="step-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3>Start Learning</h3>
                    <p>Access lessons, take quizzes, and track your progress</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="about">
        <div class="container">
            <div class="section-header">
                <span class="section-subtitle">Why Choose Us</span>
                <h2 class="section-title">Everything You Need For <span>Quality Education</span></h2>
                <p class="section-description">
                    Our platform provides comprehensive learning tools for students, teachers, and parents
                </p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card animate-fade-in">
                    <div class="feature-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3>Expert Teachers</h3>
                    <p>Learn from qualified teachers who provide personalized guidance and support</p>
                </div>
                
                <div class="feature-card animate-fade-in delay-1">
                    <div class="feature-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3>Interactive Lessons</h3>
                    <p>Engaging video lessons and downloadable materials for better understanding</p>
                </div>
                
                <div class="feature-card animate-fade-in delay-2">
                    <div class="feature-icon">
                        <i class="fas fa-puzzle-piece"></i>
                    </div>
                    <h3>Smart Quizzes</h3>
                    <p>Automatically graded quizzes with instant feedback and analytics</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Classes Section -->
    <section class="classes-section">
        <div class="container">
            <div class="section-header">
                <span class="section-subtitle">Our Classes</span>
                <h2 class="section-title">From <span>Primary 1 to 7</span></h2>
                <p class="section-description">
                    Comprehensive curriculum for every grade level
                </p>
            </div>
            
            <div class="classes-grid">
                <?php for($i = 1; $i <= 7; $i++): ?>
                <div class="class-card animate-fade-in delay-<?php echo $i % 3; ?>">
                    <div class="class-image">
                        <span class="class-level">P<?php echo $i; ?></span>
                    </div>
                    <div class="class-content">
                        <h3>Primary <?php echo $i; ?></h3>
                        <p>Complete curriculum</p>
                        <ul class="class-features">
                            <li><i class="fas fa-check"></i> All subjects</li>
                            <li><i class="fas fa-check"></i> Practice quizzes</li>
                        </ul>
                        <a href="#" class="btn-secondary">Explore</a>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Start Learning?</h2>
                <p>Join thousands of students already learning on our platform</p>
                <a href="<?php echo BASE_URL; ?>/register" class="btn-primary">
                    Create Free Account
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Intersection Observer for animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.animate-fade-in').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        observer.observe(el);
    });
});
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>