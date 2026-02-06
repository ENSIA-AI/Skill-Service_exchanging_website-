<?php 
session_start();
error_reporting(E_ALL);

// Detect production environment
$isProduction = !empty($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'infinityfreeapp') !== false || strpos($_SERVER['HTTP_HOST'], 'infinityfree') !== false || strpos($_SERVER['HTTP_HOST'], 'localhost') === false);
if (!$isProduction) {
    ini_set('display_errors', 1);
} else {
    ini_set('display_errors', 0);
    ini_set('log_errors', '1');
}

require_once '../../DataBaseManagement/config.php';

// Check database connection
if (!isset($conn) || !$conn) {
    error_log("EVENTS ERROR: Database connection not established");
    http_response_code(500);
    die('<h1>500 Internal Server Error</h1><p>Database connection failed. Please try again later.</p>');
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swap - Events</title>
    <link rel="icon" type="image/png" href="../../assets/images/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Load all required CSS files here -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/events.css">
</head>
<body>
    <?php include '../../components/header_t.php'; ?>
    <?php include '../../components/sidebar.html'; ?>
    <main class="php-content">
        <?php include './events.html';?>
    </main>
</body>
</html>