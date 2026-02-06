<?php
/**
 * Admin Authentication Middleware
 * 
 * This file checks if the current user is logged in AND has admin privileges.
 * Include this at the top of every admin page.
 * 
 * Usage: require_once __DIR__ . '/auth_admin.php';
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../DataBaseManagement/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: ../../auth/login.php?error=Please login first');
    exit;
}

// Get user info from database to verify admin status
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT UserId, UserName, Email, IsAdmin, IsBanned FROM Users WHERE UserId = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    session_destroy();
    header('Location: ../../auth/login.php?error=User not found');
    exit;
}

$stmt->bind_result($dbUserId, $dbUserName, $dbEmail, $isAdmin, $isBanned);
$stmt->fetch();

// Check if user is banned
if ($isBanned === 'yes') {
    session_destroy();
    header('Location: ../../auth/login.php?error=Your account has been banned');
    exit;
}

// Check if user is admin
if ($isAdmin !== 'yes') {
    header('Location: ../../dashboard/post/posts.php?error=Access denied. Admin privileges required');
    exit;
}

// Store admin status in session for current page
$_SESSION['is_admin'] = true;
$_SESSION['admin_username'] = $dbUserName;
$_SESSION['admin_email'] = $dbEmail;

?>
