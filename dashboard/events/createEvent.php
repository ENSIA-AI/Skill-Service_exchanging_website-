<?php 

require_once __DIR__ . "/../../DataBaseManagement/config.php";

function getCategories(mysqli $conn){ 
    $categories = []; 
    $result = $conn->query("SELECT categoryId, categoryName FROM category ORDER BY categoryName"); 
    if($result){ 
        while($row = $result->fetch_assoc()) { 
            $categories[] = $row;
        }
    } 
    return $categories; 
}

$categories = getCategories($conn);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Event</title>
    <link rel="stylesheet" href="../../assets/css/addevent.css">
</head>
<body>
    <div class="create-event-container">
        <!-- Left Side - Image -->
        <div class="event-image-section">
            <img src="../../assets/images/homeinp/createeventpic.png" alt="Create Event" class="event-illustration">
        </div>

        <!-- Right Side - Form -->
        <div class="event-form-section">
            <div class="form-header">
                <h1>Create New Event</h1>
                <button type="button" class="close-btn" onclick="window.history.back()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <form id="createEventForm" action="addevent.php" method="POST" >
                <!-- First Container: Event Details -->
                <div class="form-container">
                    <div class="form-group">
                        <label for="eventTitle">Event Title</label>
                        <input 
                            type="text" 
                            id="eventTitle" 
                            name="eventTitle"
                            class="form-input" 
                            placeholder="e.g., Web Development Workshop"
                            required
                        >
                        <span class="error-message">Event title is required</span>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="eventStartDate">Start Date & Time</label>
                            <input 
                                type="datetime-local" 
                                id="eventStartDate" 
                                name="eventStartDate"
                                class="form-input date-input" 
                                required
                            >
                            <span class="error-message">Start date and time is required</span>
                        </div>

                        <div class="form-group">
                            <label for="eventEndDate">End Date & Time</label>
                            <input 
                                type="datetime-local" 
                                id="eventEndDate" 
                                name="eventEndDate"
                                class="form-input date-input" 
                                required
                            >
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
                        <input 
                            type="text" 
                            id="location" 
                            name="location"
                            class="form-input" 
                            placeholder="e.g., Community Center"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea 
                            id="description" 
                            name="description"
                            class="form-input form-textarea" 
                            placeholder="Describe your event..."
                            rows="4"
                        ></textarea>
                    </div>

                    <div class="form-group">
                        <label for="maxAttendees">Max Attendees</label>
                        <input 
                            type="number" 
                            id="maxAttendees" 
                            name="maxAttendees"
                            class="form-input" 
                            placeholder="e.g., 20"
                            min="1" max="100"
                        >
                    </div>

                    <div class="form-group">
                        <label for="credit">Credits Required <span class="optional">(Optional)</span></label>
                        <input 
                            type="number" 
                            id="credit" 
                            name="credit"
                            class="form-input" 
                            placeholder="e.g., 10"
                            min="0" max="100"
                            value="0"
                        >
                        <span class="error-message">Credits must be between 0 and 100</span>
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
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category['categoryId']) ?>"> 
                                        <?= htmlspecialchars($category['categoryName']) ?> 
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="skill">Skill</label>
                            <select id="skill" name="skill" class="form-input form-select" disabled>
                                <option value="">Select a category first</option>
                            </select>
                        </div>

                        <button type="button" class="add-skill-btn" id="addSkillBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Add Skill
                        </button>
                    </div>

                    <!-- Hidden input for selected skills -->
                    <input type="hidden" name="selectedSkills" id="selectedSkillsInput">
                    
                    <!-- Selected Skills Display Container -->
                    <div class="selected-skills-display" id="selectedSkillsDisplay">
                        <!-- Skills will be added here dynamically -->
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-actions">
                    <button type="submit" class="btn-create">Create Event</button>
                    <button type="button" class="btn-cancel" onclick="window.history.back()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <script src="eventsJs/addevent.js"></script>
    <script>
        //set time zone based on user browser
        document.getElementById('timezone').value =
        Intl.DateTimeFormat().resolvedOptions().timeZone;
    </script>
</body>
</html>