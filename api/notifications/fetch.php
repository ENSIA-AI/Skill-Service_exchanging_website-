<?php
require_once __DIR__ . '/../../DataBaseManagement/config.php';

header('Content-Type: application/json');

// session_start();
// For now use GET parameter for testing as session is not yet implemented
$currentUserId = $_GET['userId'] ?? 1; 

if (!$currentUserId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

$stmt = $conn->prepare("
    SELECT un.*, u.UserName as SenderName, u.ProfilePicture as SenderPFP
    FROM UserNotifications un
    LEFT JOIN Users u ON un.SenderId = u.UserId
    WHERE un.UserId = ? 
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
