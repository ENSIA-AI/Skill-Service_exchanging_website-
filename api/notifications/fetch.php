<?php
error_reporting(E_ALL);
ini_set('log_errors', '1');

session_start();
require_once __DIR__ . '/../../DataBaseManagement/config.php';

header('Content-Type: application/json');

// Detect production environment
$isProduction = !empty($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'infinityfreeapp') !== false || strpos($_SERVER['HTTP_HOST'], 'infinityfree') !== false || strpos($_SERVER['HTTP_HOST'], 'localhost') === false);
if (!$isProduction) {
    ini_set('display_errors', 0);
}

// Check database connection
if (!isset($conn) || !$conn) {
    error_log("FETCH NOTIFICATIONS ERROR: Database connection not established");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit;
}

// Get current user from session or GET parameter for testing
$currentUserId = $_SESSION['user_id'] ?? $_GET['userId'] ?? null; 

if (!$currentUserId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID is required or not logged in']);
    exit;
}

$stmt = $conn->prepare("
    SELECT un.*, u.UserName as SenderName, u.ProfilePicture as SenderPFP
    FROM usernotifications un
    LEFT JOIN users u ON un.SenderId = u.UserId
    WHERE un.RecipientId = ? 
    ORDER BY un.CreatedAt DESC
");
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

echo json_encode(['success' => true, 'notifications' => $notifications]);
