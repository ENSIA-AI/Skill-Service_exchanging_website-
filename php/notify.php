<?php
require_once __DIR__ . '/../DataBaseManagement/config.php';

/**
 * Creates a notification for a user.
 * 
 * @param mysqli $conn The database connection.
 * @param int $userId The ID of the user to notify.
 * @param string $type The type of notification (must match ENUM in DB).
 * @param string $title The title of the notification (min 5 chars).
 * @param string $message The notification message.
 * @param string $section The section of the notification (Reviews, Exchange, events, credits).
 * @param int|null $senderId The ID of the user who triggered the notification.
 * @return int|false The ID of the created notification or false on failure.
 */
function create_notification($conn, $userId, $type, $title, $message, $section, $senderId = null) {
    $stmt = $conn->prepare("INSERT INTO usernotifications (RecipientId, SenderId, NotificationType, Title, Message, NotificationSection) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("iissss", $userId, $senderId, $type, $title, $message, $section);
    
    if ($stmt->execute()) {
        $notifId = $stmt->insert_id;
        $stmt->close();
        return $notifId;
    }
    
    $stmt->close();
    return false;
}
?>
