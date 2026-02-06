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
    error_log("DELETE NOTIFICATIONS ERROR: Database connection not established");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$notifId = $data['notificationId'] ?? null;

if (!$notifId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Notification ID is required']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM usernotifications WHERE NotificationId = ?");
if (!$stmt) {
    error_log("DELETE NOTIFICATION ERROR: Prepare failed - " . $conn->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit;
}

$stmt->bind_param("i", $notifId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Notification deleted successfully']);
} else {
    error_log("DELETE NOTIFICATION ERROR: Execute failed - " . $stmt->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
$stmt->close();
