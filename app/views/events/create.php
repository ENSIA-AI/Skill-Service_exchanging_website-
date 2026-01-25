<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Event</title>
    <link rel="stylesheet" href="<?= '/Skill-Service_exchanging_website-/public/assets/css/addevent.css' ?>">
    <link rel="stylesheet" href="<?= '/Skill-Service_exchanging_website-/public/assets/css/style.css' ?>">
</head>
</head>
<body>
    <style>
        /* Override addevent.css body style to allow valid sidebar layout */
        body {
            display: block !important;
            padding: 0 !important;
        }
        .create-event-container {
            margin: 0 auto; /* Keep the card centered horizontally */
        }
    </style>

    <!-- Header -->
    <?php include_once __DIR__ . '/../components/header.php'; ?>
    
    <!-- Sidebar -->
    <?php include_once __DIR__ . '/../components/sidebar.php'; ?>

    <!-- Main Content (Wrapper) -->
    <main class="main-content" style="margin-top: 80px; margin-left: 240px; padding: 20px;">

    <div class="create-event-container">
        <!-- Left Side - Image -->
        <div class="event-image-section">
            <img src="<?= '/Skill-Service_exchanging_website-/public/assets/images/homeinp/createeventpic.png' ?>" alt="Create Event" class="event-illustration">
        </div>

        <!-- Right Side - Form -->
        <div class="event-form-section">
            <div class="form-header">
                <h1>Create New Event</h1>
                <button type="button" class="close-btn" onclick="window.history.back()">
                    <!-- SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <form id="createEventForm" action="<?= '/Skill-Service_exchanging_website-/public/events/create' ?>" method="POST" >
                <!-- First Container: Event Details -->
                <div class="form-container">
                    <div class="form-group">
                        <label for="eventTitle">Event Title</label>
                        <input type="text" id="eventTitle" name="eventTitle" class="form-input" placeholder="e.g., Web Development Workshop" required>
                        <span class="error-message">Event title is required</span>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="eventStartDate">Start Date & Time</label>
                            <input type="datetime-local" id="eventStartDate" name="eventStartDate" class="form-input date-input" required>
                            <span class="error-message">Start date and time is required</span>
                        </div>

                        <div class="form-group">
                            <label for="eventEndDate">End Date & Time</label>
                            <input type="datetime-local" id="eventEndDate" name="eventEndDate" class="form-input date-input" required>
                            <span class="error-message">End date and time is required</span>
                        </div>
                        <input type="hidden" name="timezone" id="timezone">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="eventType">Event Type</label>
                            <select id="eventType" name="eventType" class="form-input form-select" required>
                                <option value="">Select event type</option>
                                <option value="in-person">In-Person</option>
                                <option value="online">Online</option>
                            </select>
                            <span class="error-message">Event type is required</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" class="form-input" placeholder="e.g., Community Center" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-input form-textarea" placeholder="Describe your event..." rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="maxAttendees">Max Attendees</label>
                        <input type="number" id="maxAttendees" name="maxAttendees" class="form-input" placeholder="e.g., 20" min="1" max="100">
                    </div>
                </div>

                <!-- Second Container: Skills Needed -->
                <div class="form-container">
                    <div class="skills-header">
                        <h3>Skills Needed</h3>
                        <span class="skills-count" id="skillsCount">0/5</span>
                    </div>

                    <div class="skills-inputs">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" class="form-input form-select">
                                <option value="">-- Select a category first--</option>
                                <?php if(isset($data['categories'])): ?>
                                    <?php foreach ($data['categories'] as $category): ?>
                                        <option value="<?= htmlspecialchars($category['CategoryId']) ?>"> 
                                            <?= htmlspecialchars($category['CategoryName']) ?> 
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="skill">Skill</label>
                            <select id="skill" name="skill" class="form-input form-select" disabled>
                                <option value="">Select a category first</option>
                            </select>
                        </div>

                        <button type="button" class="add-skill-btn" id="addSkillBtn">
                             Skills +
                        </button>
                    </div>

                    <!-- Hidden input for selected skills -->
                    <input type="hidden" name="selectedSkills" id="selectedSkillsInput">
                    
                    <!-- Selected Skills Display Container -->
                    <div class="selected-skills-display" id="selectedSkillsDisplay"></div>
                </div>

                <!-- Action Buttons -->
                <div class="form-actions">
                    <button type="submit" class="btn-create">Create Event</button>
                    <button type="button" class="btn-cancel" onclick="window.history.back()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    </main>

    <!-- JS: We need to make sure this path is correct AND that the JS doesn't break -->
    <!-- Legacy was: eventsJs/addevent.js -->
    <!-- I need to move it to public/assets/js/events/addevent.js if not there -->
    <script src="<?= '/Skill-Service_exchanging_website-/public/assets/js/events/addevent.js' ?>"></script>
    <script>
        //set time zone based on user browser
        const tz = document.getElementById('timezone');
        if(tz) tz.value = Intl.DateTimeFormat().resolvedOptions().timeZone;
    </script>
</body>
</html>
