<?php require_once '../../DataBaseManagement/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Details - Skill Swap</title>
    <link rel="icon" type="image/png" href="../../assets/images/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/eventdetails.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
</head>
<body>
    <?php include '../../components/header.html'; ?>
    <?php include '../../components/sidebar.html'; ?>
    <main class="php-content event-details-main-content">
        <div class="event-details-header">    
            <button class="back-button js-back-button">
                <i class="fas fa-arrow-left"></i>
            </button>
            <h1>Back to Events</h1>
        </div>

        <div class="event-details-container js-event-details-container">
            <!-- Event details will be rendered here by JavaScript -->
        </div>
    </main>
    <script src="eventsJs/eventdetails.js?v=2"></script>
</body>
</html>
