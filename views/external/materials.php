<?php
// File: /views/external/materials.php
$pageTitle = 'Learning Materials - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';
?>

<div style="padding: 40px 20px; max-width: 1200px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 20px;">
        <h1 style="font-size: 2rem; background: linear-gradient(135deg, #8B5CF6, #F97316); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Learning Materials
        </h1>
        
        <!-- Search Bar -->
        <form method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="search" placeholder="Search lessons..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" 
                   style="padding: 12px 20px; border: 2px solid #E2E8F0; border-radius: 50px; width: 300px; font-size: 1rem;">
            <button type="submit" style="background: linear-gradient(135deg, #8B5CF6, #F97316); color: white; border: none; padding: 12px 30px; border-radius: 50px; cursor: pointer; font-weight: 600;">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>
    
    <?php if (empty($lessons)): ?>
        <div style="background: white; border-radius: 20px; padding: 60px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            <i class="fas fa-book-open" style="font-size: 4rem; color: #CBD5E1; margin-bottom: 20px;"></i>
            <h3 style="color: #1E293B; margin-bottom: 10px;">No Lessons Found</h3>
            <p style="color: #64748B;">Check back later for new learning materials.</p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px;">
            <?php foreach ($lessons as $lesson): ?>
                <div style="background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1); transition: transform 0.3s ease;">
                    <?php if (!empty($lesson['video_url'])): ?>
                        <div style="height: 200px; background: #1E293B; position: relative;">
                            <img src="https://img.youtube.com/vi/<?php echo getYoutubeId($lesson['video_url']); ?>/0.jpg" 
                                 alt="Thumbnail" style="width: 100%; height: 100%; object-fit: cover;">
                            <span style="position: absolute; bottom: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 5px 10px; border-radius: 5px; font-size: 0.8rem;">
                                <i class="fas fa-clock"></i> <?php echo $lesson['duration'] ?? '30'; ?> min
                            </span>
                        </div>
                    <?php else: ?>
                        <div style="height: 150px; background: linear-gradient(135deg, #8B5CF6, #F97316); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-book-open" style="font-size: 3rem; color: white; opacity: 0.5;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div style="padding: 25px;">
                        <h3 style="margin-bottom: 10px; color: #1E293B;"><?php echo htmlspecialchars($lesson['title']); ?></h3>
                        <p style="color: #64748B; margin-bottom: 15px; line-height: 1.6;">
                            <?php echo substr(htmlspecialchars($lesson['content'] ?? ''), 0, 150); ?>...
                        </p>
                        
                        <div style="display: flex; gap: 15px; margin-bottom: 20px; color: #64748B; font-size: 0.9rem;">
                            <span><i class="fas fa-eye" style="color: #8B5CF6;"></i> <?php echo $lesson['views'] ?? 0; ?> views</span>
                            <span><i class="fas fa-download" style="color: #F97316;"></i> <?php echo $lesson['materials_count'] ?? 0; ?> files</span>
                        </div>
                        
                        <a href="/rays-of-grace/external/view-lesson/<?php echo $lesson['id']; ?>" 
                           style="display: inline-block; background: linear-gradient(135deg, #8B5CF6, #F97316); color: white; text-decoration: none; padding: 12px 25px; border-radius: 50px; font-weight: 600; transition: transform 0.3s ease;">
                            Start Learning <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
function getYoutubeId($url) {
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
    return $matches[1] ?? '';
}
?>

<style>
    .lesson-card:hover {
        transform: translateY(-5px);
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>