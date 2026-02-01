<?php
require_once __DIR__ . '/../../DataBaseManagement/config.php';

header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$notifId = $data['notificationId'] ?? null;

if (!$notifId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Notification ID is required']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM UserNotifications WHERE NotificationId = ?");
$stmt->bind_param("i", $notifId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Notification deleted successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
$stmt->close();
