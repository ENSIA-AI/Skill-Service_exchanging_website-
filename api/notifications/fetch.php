<?php
session_start();
require_once __DIR__ . '/../../DataBaseManagement/config.php';

header('Content-Type: application/json');

// Get current user from session or GET parameter for testing
$currentUserId = $_SESSION['user_id'] ?? $_GET['userId'] ?? null; 

if (!$currentUserId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID is required or not logged in']);
    exit;
}

$stmt = $conn->prepare("
    SELECT un.*, u.UserName as SenderName, u.ProfilePicture as SenderPFP
    FROM UserNotifications un
    LEFT JOIN Users u ON un.SenderId = u.UserId
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
