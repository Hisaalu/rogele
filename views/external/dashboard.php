<?php
// File: /views/external/dashboard.php
$pageTitle = 'Dashboard | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

// Use variables passed from controller instead of calling model directly
$trialDays = $trialDays ?? 60;
$remainingTrialDays = $remainingTrialDays ?? 0;
$isInTrial = $isInTrial ?? false;
$hasActiveSubscription = $hasActiveSubscription ?? false;
$trialEndDate = $trialEndDate ?? null;
$trialPercentage = $trialPercentage ?? 0;
$currentPlan = $currentPlan ?? null;
$subscriptionEndDate = $subscriptionEndDate ?? null;

$hasAccess = $hasActiveSubscription || $isInTrial;

$events = $events ?? [];
?>

<div style="padding: 40px 20px; max-width: 1200px; margin: 0 auto;">
    <h1 style="font-size: 2rem; margin-bottom: 20px;">
        <span style="background-color: #f06724; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Welcome back, <?php 
                $fullName = $_SESSION['user_name'] ?? 'User';
                $firstName = explode(' ', trim($fullName))[0];
                echo htmlspecialchars($firstName); 
            ?>!
        </span>
    </h1>
    
    <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
        <h2 style="color: #000; margin-bottom: 20px;">Dashboard</h2>
        
        <!-- Access Status Banner -->
        <?php if ($hasActiveSubscription): ?>
            <div style="background: #F0FDF4; border-left: 4px solid #10B981; padding: 20px; margin-bottom: 30px; border-radius: 12px; display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <div style="background: #10B981; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-crown" style="color: white; font-size: 1.5rem;"></i>
                </div>
                <div style="flex: 1;">
                    <p style="color: #065F46; font-weight: 700; margin-bottom: 5px;">Active Subscription</p>
                    <p style="color: #047857;">You have full access to all premium features.</p>
                    <?php if ($subscriptionEndDate): ?>
                        <p style="color: #047857; font-size: 0.85rem;">Valid until: <?php echo date('F j, Y', strtotime($subscriptionEndDate)); ?></p>
                    <?php endif; ?>
                </div>
                <a href="<?php echo BASE_URL; ?>/external/subscription" style="background: #10B981; color: white; padding: 10px 24px; border-radius: 50px; text-decoration: none; font-weight: 600;">
                    Manage Plan
                </a>
            </div>
            
        <?php elseif ($isInTrial): ?>
            <div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 20px; margin-bottom: 30px; border-radius: 12px;">
                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <div style="background: #F59E0B; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-hourglass-half" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <div style="flex: 1;">
                        <p style="color: #92400E; font-weight: 700; font-size: 1.1rem; margin-bottom: 5px;">
                            Trial Period: <strong><?php echo $remainingTrialDays; ?> days remaining</strong>
                        </p>
                        <p style="color: #B45309; font-size: 0.95rem;">
                            You have <?php echo $remainingTrialDays; ?> days left to explore all features.
                            <?php if ($trialEndDate): ?>
                                Your trial ends on <strong><?php echo date('F j, Y', strtotime($trialEndDate)); ?></strong>.
                            <?php endif; ?>
                        </p>
                        <?php $usedDays = $trialDays - $remainingTrialDays; ?>
                        <div style="background: #FFEDD5; height: 8px; border-radius: 10px; margin-top: 10px; max-width: 400px;">
                            <div style="background: #f06724; width: <?php echo $trialPercentage; ?>%; height: 100%; border-radius: 10px;"></div>
                        </div>
                        <p style="color: #B45309; font-size: 0.75rem; margin-top: 5px;">Day <?php echo $usedDays; ?> of <?php echo $trialDays; ?></p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/external/subscription" style="background: #f06724; color: white; padding: 10px 24px; border-radius: 50px; text-decoration: none; font-weight: 600;">
                        Subscribe Now
                    </a>
                </div>
            </div>
            
        <?php else: ?>
            <div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 20px; margin-bottom: 30px; border-radius: 12px;">
                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <div style="background: #EF4444; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-clock" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <div style="flex: 1;">
                        <p style="color: #B91C1C; font-weight: 700; margin-bottom: 5px;">Trial or Subscription Ended!</p>
                        <p style="color: #B91C1C;">Kindly subscribe now to continue accessing lessons, quizzes & more!</p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/external/subscription" style="background: #10B981; color: white; padding: 10px 24px; border-radius: 50px; text-decoration: none; font-weight: 600;">
                        Subscribe Now
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Navigation Grid Elements -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
            <a href="<?php echo BASE_URL; ?>/external/materials" style="background: <?php echo $hasAccess ? 'linear-gradient(135deg, #7f2677, #7f2677)' : '#E2E8F0'; ?>; color: <?php echo $hasAccess ? 'white' : '#000'; ?>; padding: 30px; border-radius: 15px; text-decoration: none; text-align: center; transition: transform 0.3s ease; <?php echo !$hasAccess ? 'cursor: not-allowed;' : ''; ?>">
                <i class="fas fa-book-open" style="font-size: 2rem; margin-bottom: 15px; color: <?php echo $hasAccess ? '#f06724' : '#94A3B8'; ?>;"></i>
                <h3 style="margin-bottom: 10px;">Learning Materials</h3>
                <p style="opacity: 0.9; font-size: 0.9rem;">
                    <?php echo $hasAccess ? 'Access all lessons and resources' : 'Subscribe to access lessons'; ?>
                </p>
                <?php if (!$hasAccess): ?>
                    <div style="margin-top: 10px; font-size: 0.8rem; color: #B91C1C;">Locked</div>
                <?php endif; ?>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/external/quizzes" style="background: <?php echo $hasAccess ? 'linear-gradient(135deg, #7f2677, #7f2677)' : '#E2E8F0'; ?>; color: <?php echo $hasAccess ? 'white' : '#000'; ?>; padding: 30px; border-radius: 15px; text-decoration: none; text-align: center; transition: transform 0.3s ease; <?php echo !$hasAccess ? 'cursor: not-allowed;' : ''; ?>">
                <i class="fas fa-pencil-alt" style="font-size: 2rem; margin-bottom: 15px; color: <?php echo $hasAccess ? '#f06724' : '#94A3B8'; ?>;"></i>
                <h3 style="margin-bottom: 10px;">Practice Quizzes</h3>
                <p style="opacity: 0.9; font-size: 0.9rem;">
                    <?php echo $hasAccess ? 'Test your knowledge' : 'Subscribe to access quizzes'; ?>
                </p>
                <?php if (!$hasAccess): ?>
                    <div style="margin-top: 10px; font-size: 0.8rem; color: #B91C1C;">Locked</div>
                <?php endif; ?>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/external/subscription" style="background: white; color: #000; padding: 30px; border-radius: 15px; text-decoration: none; text-align: center; border: 2px solid #E2E8F0; transition: transform 0.3s ease;">
                <i class="fas fa-credit-card" style="font-size: 2rem; margin-bottom: 15px; color: #f06724;"></i>
                <h3 style="margin-bottom: 10px;">Subscription</h3>
                <p style="color: #555; font-size: 0.9rem;">
                    <?php echo $hasActiveSubscription ? 'Manage your subscription' : ($isInTrial ? 'Upgrade to premium' : 'Subscribe to continue'); ?>
                </p>
            </a>
        </div>

        <h3 style="font-size: 1.4rem; margin-bottom: 20px; color: #000; border-bottom: 2px solid #F3F4F6; padding-bottom: 10px;">
            <i class="far fa-calendar-alt" style="color: #f06724; margin-right: 8px;"></i> Institutional Calendar & Events
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; background: #FAFAFA; padding: 25px; border-radius: 16px; border: 1px solid #E5E7EB;">
            
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h4 id="calendar-month-year" style="margin: 0; font-size: 1.1rem; color: #7f2677; font-weight: 700;"></h4>
                    <div style="display: flex; gap: 5px;">
                        <button onclick="changeMonth(-1)" style="background: white; border: 1px solid #E5E7EB; border-radius: 6px; padding: 5px 10px; cursor: pointer; color: #555;"><i class="fas fa-chevron-left"></i></button>
                        <button onclick="changeMonth(1)" style="background: white; border: 1px solid #E5E7EB; border-radius: 6px; padding: 5px 10px; cursor: pointer; color: #555;"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; font-weight: 600; font-size: 0.8rem; color: #000; margin-bottom: 8px;">
                    <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
                </div>
                <div id="calendar-days" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; text-align: center; font-size: 0.9rem;"></div>
            </div>

            <!-- Events Feed -->
            <div style="display: flex; flex-direction: column; justify-content: flex-start;">
                <h4 style="margin: 0 0 15px 0; font-size: 1.1rem; color: #000;">Upcoming Deadlines & Events</h4>
                <div style="display: flex; flex-direction: column; gap: 12px; max-height: 280px; overflow-y: auto; padding-right: 5px;">
                    <?php if (!empty($events)): ?>
                        <?php foreach ($events as $event): ?>
                            <div style="display: flex; gap: 15px; align-items: flex-start; padding: 12px; background: white; border-radius: 10px; border-left: 4px solid #f06724; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                                <div style="background: #FFF7ED; padding: 8px 12px; border-radius: 8px; text-align: center; min-width: 50px;">
                                    <span style="display: block; font-size: 0.75rem; text-transform: uppercase; color: #f06724; font-weight: 700;"><?php echo date('M', strtotime($event['date'])); ?></span>
                                    <span style="display: block; font-size: 1.1rem; font-weight: 700; color: #7f2677; line-height: 1.1;"><?php echo date('d', strtotime($event['date'])); ?></span>
                                </div>
                                <div style="flex: 1;">
                                    <h5 style="margin: 0 0 4px 0; font-size: 0.95rem; color: #555; font-weight: 600;"><?php echo htmlspecialchars($event['title']); ?></h5>
                                    <span style="font-size: 0.8rem; color: #555;"><i class="far fa-clock" style="margin-right: 4px;"></i> <?php echo htmlspecialchars($event['time']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #555; font-size: 0.9rem; font-style: italic;">No scheduled events or deadlines at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        
        <?php if (!$hasAccess && !$hasActiveSubscription): ?>
        <div style="margin-top: 30px; padding: 20px; background: #FEF2F2; border-radius: 12px; text-align: center;">
            <p style="color: #B91C1C; margin-bottom: 15px;">
                Your free trial or subscription has ended. To continue learning, please choose a subscription plan!
            </p>
            <a href="<?php echo BASE_URL; ?>/external/subscription" class="btn-subscribe" style="background: #10B981; color: white; padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: 600; display: inline-block;">
                View Plans
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    const scheduledEvents = <?php echo json_encode($events); ?>;
    
    let currentDate = new Date();

    function renderCalendar() {
        const monthYearLabel = document.getElementById('calendar-month-year');
        const daysContainer = document.getElementById('calendar-days');
        
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        
        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        monthYearLabel.innerText = `${monthNames[month]} ${year}`;
        
        daysContainer.innerHTML = '';
        
        const firstDayIndex = new Date(year, month, 1).getDay();
        const lastDay = new Date(year, month + 1, 0).getDate();
        
        for (let i = 0; i < firstDayIndex; i++) {
            const emptyDiv = document.createElement('div');
            daysContainer.appendChild(emptyDiv);
        }
        
        for (let day = 1; day <= lastDay; day++) {
            const dayDiv = document.createElement('div');
            dayDiv.innerText = day;
            dayDiv.style.padding = '8px 0';
            dayDiv.style.borderRadius = '6px';
            dayDiv.style.color = '#555';
            dayDiv.style.position = 'relative';
            
            const currentStringDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            
            const dayEvents = scheduledEvents.filter(e => e.date === currentStringDate);
            
            if (dayEvents.length > 0) {
                dayDiv.style.background = '#FFEDD5';
                dayDiv.style.color = '#f06724';
                dayDiv.style.fontWeight = '700';
                dayDiv.style.border = '1px solid #f06724';
                dayDiv.style.cursor = 'pointer';
                dayDiv.title = `Click to view ${dayEvents.length} event(s)`;
                dayDiv.addEventListener('click', (e) => {
                    if (dayEvents.length === 1) {
                        window.location.href = dayEvents[0].url;
                    } else {
                        let message = "Multiple deadlines on this day:\n\n";
                        dayEvents.forEach((ev, idx) => {
                            message += `${idx + 1}. ${ev.title}\n`;
                        });
                        message += "\nType the number (1, 2, etc.) of the task you want to open:";
                        
                        const choice = prompt(message);
                        const selectedIdx = parseInt(choice) - 1;
                        if (!isNaN(selectedIdx) && dayEvents[selectedIdx]) {
                            window.location.href = dayEvents[selectedIdx].url;
                        }
                    }
                });
            }
            
            const today = new Date();
            if (day === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                dayDiv.style.background = '#7f2677';
                dayDiv.style.color = 'white';
                dayDiv.style.fontWeight = '700';
            }
            
            daysContainer.appendChild(dayDiv);
        }
    }

    function changeMonth(direction) {
        currentDate.setMonth(currentDate.getMonth() + direction);
        renderCalendar();
    }

    // Run structural assembly setup initialization
    document.addEventListener('DOMContentLoaded', renderCalendar);
</script>

<style>
    a:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(139, 92, 246, 0.2);
    }
    
    .btn-subscribe:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3);
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>