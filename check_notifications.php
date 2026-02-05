<?php
require_once 'DataBaseManagement/config.php';

echo "=== Checking Recent Notifications ===\n\n";

$result = $conn->query('SELECT * FROM UserNotifications WHERE NotificationId >= 64 ORDER BY NotificationId DESC LIMIT 5');
if ($result->num_rows > 0) {
    while ($n = $result->fetch_assoc()) {
        echo "ID: {$n['NotificationId']}\n";
        echo "  Type: {$n['NotificationType']}\n";
        echo "  Title: {$n['Title']}\n";
        echo "  Message: {$n['Message']}\n";
        echo "  RecipientId: {$n['RecipientId']}\n";
        echo "  SenderId: {$n['SenderId']}\n";
        echo "  IsRead: {$n['IsRead']}\n";
        echo "  Section: {$n['NotificationSection']}\n";
        echo "  Created: {$n['CreatedAt']}\n\n";
    }
} else {
    echo "No notifications found\n";
}

$conn->close();
?>
