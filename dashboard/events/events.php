<?php 
session_start();
require_once '../../DataBaseManagement/config.php'; 

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