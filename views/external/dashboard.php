<?php
// File: /views/external/dashboard.php
$pageTitle = 'Dashboard | ROGELE';
require_once __DIR__ . '/../layouts/header.php';

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

<style>
    .dashboard-container {
        padding: 40px 20px; 
        max-width: 1200px; 
        margin: 0 auto;
        box-sizing: border-box;
    }
    .dashboard-card {
        background: white; 
        border-radius: 20px; 
        padding: 30px; 
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        box-sizing: border-box;
    }
    
    .nav-grid {
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); 
        gap: 20px; 
        margin-bottom: 40px;
    }
    .nav-card {
        padding: 30px; 
        border-radius: 15px; 
        text-decoration: none; 
        text-align: center; 
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-sizing: border-box;
    }
    .nav-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(127, 38, 119, 0.2);
    }
    
    .calendar-widget-grid {
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
        gap: 25px; 
        background: #FAFAFA; 
        padding: 20px; 
        border-radius: 16px; 
        border: 1px solid #E5E7EB;
        box-sizing: border-box;
    }
    
    .event-feed-item {
        display: flex; 
        gap: 15px; 
        align-items: flex-start; 
        padding: 12px; 
        background: white; 
        border-radius: 10px; 
        border-left: 4px solid #f06724; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        text-decoration: none;
        transition: transform 0.2s ease, background-color 0.2s ease;
    }
    .event-feed-item:hover {
        transform: translateX(4px);
        background: #FFF7ED;
    }

    .btn-subscribe {
        background: #10B981; 
        color: white; 
        padding: 12px 30px; 
        border-radius: 50px; 
        text-decoration: none; 
        font-weight: 600; 
        display: inline-block;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .btn-subscribe:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 20px 12px;
        }
        .dashboard-card {
            padding: 20px 15px;
        }
        .calendar-widget-grid {
            grid-template-columns: 1fr;
            gap: 30px;
            padding: 15px;
        }
        .nav-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
    }
</style>

<div class="dashboard-container">
    <h1 style="font-size: 2rem; margin-bottom: 20px;">
        <span style="background-color: #f06724; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Welcome back, <?php 
                $fullName = $_SESSION['user_name'] ?? 'User';
                $firstName = explode(' ', trim($fullName))[0];
                echo htmlspecialchars($firstName); 
            ?>!
        </span>
    </h1>
    
    <div class="dashboard-card">
        <h2 style="color: #000; margin-bottom: 20px; font-family: Roobert,sans-serif;">Overview</h2>
        
        <?php if ($hasActiveSubscription): ?>
            <div style="background: #F0FDF4; border-left: 4px solid #10B981; padding: 20px; margin-bottom: 30px; border-radius: 12px; display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <div style="background: #10B981; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-crown" style="color: white; font-size: 1.5rem;"></i>
                </div>
                <div style="flex: 1; min-width: 200px;">
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
                    <div style="background: #F59E0B; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-hourglass-half" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <p style="color: #92400E; font-weight: 700; font-size: 0.95rem; margin-bottom: 5px;">
                            Trial Period: <strong>Unlimited days remaining</strong>
                        </p>
                        <p style="color: #B45309; font-size: 0.95rem;">
                            You have Unlimited days to explore all features!
                            <?php if ($trialEndDate): ?>
                                Make good use of this Trial Period and consider Subscribing to support the system.
                            <?php endif; ?>
                        </p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/external/subscription" style="background: #f06724; color: white; padding: 10px 24px; border-radius: 50px; text-decoration: none; font-weight: 600;">
                        Subscribe Now
                    </a>
                </div>
            </div>
            
        <?php else: ?>
            <div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 20px; margin-bottom: 30px; border-radius: 12px;">
                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <div style="background: #EF4444; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-clock" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <p style="color: #B91C1C; font-weight: 700; margin-bottom: 5px;">Trial or Subscription Ended!</p>
                        <p style="color: #B91C1C;">Kindly subscribe now to continue accessing lessons, quizzes & more!</p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/external/subscription" style="background: #10B981; color: white; padding: 10px 24px; border-radius: 50px; text-decoration: none; font-weight: 600;">
                        Subscribe Now
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="nav-grid">
            <a href="<?php echo $hasAccess ? BASE_URL . '/external/materials' : '#'; ?>" class="nav-card" style="background: <?php echo $hasAccess ? 'linear-gradient(135deg, #7f2677, #7f2677)' : '#E2E8F0'; ?>; color: <?php echo $hasAccess ? 'white' : '#000'; ?>; <?php echo !$hasAccess ? 'cursor: not-allowed;' : ''; ?>">
                <i class="fas fa-book-open" style="font-size: 2rem; margin-bottom: 15px; color: <?php echo $hasAccess ? '#f06724' : '#94A3B8'; ?>;"></i>
                <h3 style="margin-bottom: 10px;">Learning Materials</h3>
                <p style="opacity: 0.9; font-size: 0.9rem;">
                    <?php echo $hasAccess ? 'Access all lessons and resources' : 'Subscribe to access lessons'; ?>
                </p>
                <?php if (!$hasAccess): ?>
                    <div style="margin-top: 10px; font-size: 0.8rem; color: #B91C1C;">Locked</div>
                <?php endif; ?>
            </a>
            
            <a href="<?php echo $hasAccess ? BASE_URL . '/external/quizzes' : '#'; ?>" class="nav-card" style="background: <?php echo $hasAccess ? 'linear-gradient(135deg, #7f2677, #7f2677)' : '#E2E8F0'; ?>; color: <?php echo $hasAccess ? 'white' : '#000'; ?>; <?php echo !$hasAccess ? 'cursor: not-allowed;' : ''; ?>">
                <i class="fas fa-pencil-alt" style="font-size: 2rem; margin-bottom: 15px; color: <?php echo $hasAccess ? '#f06724' : '#94A3B8'; ?>;"></i>
                <h3 style="margin-bottom: 10px;">Practice Quizzes</h3>
                <p style="opacity: 0.9; font-size: 0.9rem;">
                    <?php echo $hasAccess ? 'Test your knowledge' : 'Subscribe to access quizzes'; ?>
                </p>
                <?php if (!$hasAccess): ?>
                    <div style="margin-top: 10px; font-size: 0.8rem; color: #B91C1C;">Locked</div>
                <?php endif; ?>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/external/subscription" class="nav-card" style="background: white; color: #000; border: 1px solid #E2E8F0;">
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

        <div class="calendar-widget-grid">
            
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h4 id="calendar-month-year" style="margin: 0; font-size: 0.95rem; color: #7f2677; font-weight: 700;"></h4>
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

            <div style="display: flex; flex-direction: column; justify-content: flex-start;">
                <h4 style="margin: 0 0 15px 0; font-size: 0.95rem; color: #000;">Upcoming Deadlines & Events</h4>
                <div style="display: flex; flex-direction: column; gap: 12px; max-height: 280px; overflow-y: auto; padding-right: 5px;">
                    <?php if (!empty($events)): ?>
                        <?php foreach ($events as $event): ?>
                            <a href="<?php echo htmlspecialchars($event['url'] ?? '#'); ?>" class="event-feed-item">
                                <div style="background: #FFF7ED; padding: 8px 12px; border-radius: 8px; text-align: center; min-width: 50px; box-sizing: border-box;">
                                    <span style="display: block; font-size: 0.75rem; text-transform: uppercase; color: #f06724; font-weight: 700;"><?php echo date('M', strtotime($event['date'])); ?></span>
                                    <span style="display: block; font-size: 0.95rem; font-weight: 700; color: #7f2677; line-height: 1.1;"><?php echo date('d', strtotime($event['date'])); ?></span>
                                </div>
                                <div style="flex: 1;">
                                    <h5 style="margin: 0 0 4px 0; font-size: 0.95rem; color: #555; font-weight: 600;"><?php echo htmlspecialchars($event['title']); ?></h5>
                                    <span style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 2px;">
                                        <i class="far fa-clock" style="margin-right: 4px;"></i> <?php echo htmlspecialchars($event['time']); ?>
                                    </span>
                                    <span style="font-size: 0.75rem; background: #F3F4F6; color: #555; padding: 2px 6px; border-radius: 4px; display: inline-block;">
                                        Subject: <?php echo htmlspecialchars($event['subject'] ?? 'General'); ?>
                                    </span>
                                </div>
                            </a>
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
            <a href="<?php echo BASE_URL; ?>/external/subscription" class="btn-subscribe">
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

    document.addEventListener('DOMContentLoaded', renderCalendar);
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>