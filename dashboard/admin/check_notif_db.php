<?php
// Direct database test
session_start();
require_once '../../DataBaseManagement/config.php';

echo "Testing notification retrieval...<br>";

// Count total
$result = $conn->query('SELECT COUNT(*) as cnt FROM UserNotifications');
if (!$result) {
    die('Count query error: ' . $conn->error);
}
$data = $result->fetch_assoc();
echo "Total notifications in database: " . $data['cnt'] . "<br><br>";

// Retrieve first 5
$query = "SELECT NotificationId, UserId, SenderId, NotificationType, 
                 NotificationSection, Title, Message, IsRead, CreatedAt
          FROM UserNotifications
          LIMIT 5";

$result = $conn->query($query);
if (!$result) {
    die('Query error: ' . $conn->error);
}

echo "First 5 notifications:<br>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Title</th><th>Type</th><th>Section</th><th>Read</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['NotificationId'] . "</td>";
    echo "<td>" . htmlspecialchars($row['Title']) . "</td>";
    echo "<td>" . $row['NotificationType'] . "</td>";
    echo "<td>" . $row['NotificationSection'] . "</td>";
    echo "<td>" . $row['IsRead'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
