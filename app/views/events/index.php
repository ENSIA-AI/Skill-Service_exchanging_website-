<!DOCTYPE html>
<html lang="en">
<head>   
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?= '/Skill-Service_exchanging_website-/public/assets/images/favicon.png' ?>">
    <link rel="stylesheet" href="<?= '/Skill-Service_exchanging_website-/public/assets/css/style.css' ?>">
    <link rel="stylesheet" href="<?= '/Skill-Service_exchanging_website-/public/assets/css/events.css' ?>">
    <link rel="stylesheet" href="<?= '/Skill-Service_exchanging_website-/public/assets/css/header.css' ?>">
    <link rel="stylesheet" href="<?= '/Skill-Service_exchanging_website-/public/assets/css/sidebar.css' ?>">
</head>
<body>
    <!-- Header -->
    <?php include_once '../app/views/components/header.php'; ?>
    
    <!-- Sidebar -->
    <?php include_once '../app/views/components/sidebar.php'; ?>

    <!-- Main Content -->
    <!-- Adjust layout: CSS should handle the sidebar position (fixed/absolute). 
         We might need a wrapper or margin-left on main-content. 
         Based on sidebar.css: .container is fixed, width 230px, top 76px.
         So main content needs top margin 76px and left margin 230px. 
    -->
    <main class="event-main-content" style="margin-top: 80px; margin-left: 240px; padding: 20px;">

        <div class="event-header">    
            <p class="event-text">Upcoming events</p>
            <button class="add-event-button js-add-event-button" onclick="window.location.href='<?= '/Skill-Service_exchanging_website-/public/events/create' ?>'">
                +&nbsp;&nbsp;&nbsp;create event
            </button>
        </div>

        <div class="events-list js-events-list">
            <?php if (!empty($data['events'])): ?>
                <?php foreach ($data['events'] as $event): ?>
                    <?php 
                        $isFull = $event['CurrentAttendeesNumber'] >= $event['MaxAttendees'];
                        $skillsHTML = $event['Skills'] ? implode(' ', array_map(function($s){ return "<span class='skill-tag'>$s</span>"; }, explode(', ', $event['Skills']))) : "<span class='skill-tag'>No specific skills required</span>";
                    ?>
                    <div class="event-card">
                        <div class="event-title"><?= htmlspecialchars($event['EventTitle']) ?></div>
                        <div class="details-text">
                            <div class="detail-item">
                                <div class="event-date">
                                    <i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($event['EventStartDate']) ?>
                                </div>
                            </div>
                            <div class="detail-item">    
                                <div class="event-location">
                                    <i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($event['EventLocation']) ?>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="event-attendees">
                                    <i class="fas fa-users"></i><?= $event['CurrentAttendeesNumber'] ?>/<?= $event['MaxAttendees'] ?>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="event-organizer">
                                    Organized by: <?= htmlspecialchars($event['OrganizerName']) ?>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="event-skills">
                                    Skills needed: <?= $skillsHTML ?>
                                </div>
                            </div>
                        </div>
                        <div class="join-event-div">
                            <button class="<?= $isFull ? 'full-event-button' : 'view-details-button' ?> js-view-details-button" 
                                    onclick="window.location.href='<?= '/Skill-Service_exchanging_website-/public/events/details/' . $event['EventId'] ?>'"
                                    <?= $isFull ? 'disabled' : '' ?>>
                                View Details
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No events found.</p>
            <?php endif; ?>
        </div>
    
    </main>

    <!-- Switched to PHP rendering, so we don't need renderEvents.js to render the list anymore -->
    <!-- But we might need other JS logic. For now commenting it out or replacing it. -->
    <!-- <script type="module" src="<?= '/Skill-Service_exchanging_website-/public/assets/js/events/renderEvents.js' ?>"></script> -->
</body>
</html>
