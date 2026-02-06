<?php
session_start();
require_once '../../DataBaseManagement/config.php';
require_once './auth_admin.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['notificationId']) || !isset($data['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$notificationId = (int)$data['notificationId'];
$action = $data['action']; // 'accept' or 'reject'

try {
    // Get notification details
    // NOTE: UserNotifications schema uses RecipientId (NOT UserId)
    $notifQuery = "SELECT NotificationId, RecipientId, SenderId, NotificationType, 
                          NotificationSection, Title, Message
                   FROM UserNotifications
                   WHERE NotificationId = ?";
    
    $stmt = $conn->prepare($notifQuery);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $notificationId);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $notification = $result->fetch_assoc();
    $stmt->close();

    if (!$notification) {
        throw new Exception('Notification not found');
    }

    // RecipientId = user who received the original notification (typically the post owner/admin)
    $recipientId = $notification['RecipientId'];
    $senderId = $notification['SenderId'];
    $notificationType = $notification['NotificationType'];
    $notificationSection = $notification['NotificationSection'];

    // Create appropriate response notification
    $responseMessage = '';
    $responseTitle = '';
    $responseType = '';

    if ($action === 'accept') {
        $responseType = 'accepted';
        $responseTitle = 'Request Accepted';
        
        if ($notificationType === 'booking') {
            $responseMessage = 'Your booking request has been accepted! You can now proceed with the skill exchange.';
        } elseif ($notificationType === 'acceptedInEvent') {
            $responseMessage = 'Your event attendance has been approved!';
        }
    } elseif ($action === 'reject') {
        $responseType = 'being_refused';
        $responseTitle = 'Request Rejected';
        
        if ($notificationType === 'booking') {
            $responseMessage = 'Your booking request has been rejected by the admin.';
        } elseif ($notificationType === 'acceptedInEvent') {
            $responseMessage = 'Your event attendance has been rejected by the admin.';
        }
    } else {
        throw new Exception('Invalid action');
    }

    // Mark original notification as read
    $markReadQuery = "UPDATE UserNotifications SET IsRead = 'yes' WHERE NotificationId = ?";
    $markReadStmt = $conn->prepare($markReadQuery);
    if (!$markReadStmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $markReadStmt->bind_param("i", $notificationId);
    if (!$markReadStmt->execute()) {
        throw new Exception('Mark read failed: ' . $markReadStmt->error);
    }
    $markReadStmt->close();

    // Send response notification to the person who made the original request (the booker)
    if ($senderId) {
        $adminId = $_SESSION['user_id'] ?? 1;
        
        // IMPORTANT: UserNotifications now uses RecipientId as the recipient column
        $insertQuery = "INSERT INTO UserNotifications (RecipientId, SenderId, NotificationType, NotificationSection, Title, Message, IsRead, CreatedAt) 
                       VALUES (?, ?, ?, ?, ?, ?, 'no', NOW())";
        
        $insertStmt = $conn->prepare($insertQuery);
        if (!$insertStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $insertStmt->bind_param(
            "iisiss",
            $senderId,
            $adminId,
            $responseType,
            $notificationSection,
            $responseTitle,
            $responseMessage
        );
        
        if (!$insertStmt->execute()) {
            throw new Exception('Insert failed: ' . $insertStmt->error);
        }
        $insertStmt->close();
    }

    // Log admin action
    $logMessage = 'Admin ' . $action . 'ed notification #' . $notificationId . ' (' . $notificationType . ')';
    error_log($logMessage);

    echo json_encode([
        'success' => true,
        'message' => 'Notification ' . $action . 'ed successfully. Response notification sent to user.',
        'action' => $action,
        'notificationId' => $notificationId
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
