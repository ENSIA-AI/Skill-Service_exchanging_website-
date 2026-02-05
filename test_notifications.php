<?php
require_once 'DataBaseManagement/config.php';

echo "=== UserNotifications Table Structure ===\n";
$result = $conn->query('DESCRIBE UserNotifications');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
} else {
    echo 'Error: ' . $conn->error . "\n";
}

echo "\n=== Recent Notifications ===\n";
$result = $conn->query('SELECT * FROM UserNotifications ORDER BY CreatedAt DESC LIMIT 5');
if ($result) {
    echo "Found " . $result->num_rows . " notifications\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['NotificationId'] . ", Type: " . $row['NotificationType'] . ", Title: " . $row['Title'] . "\n";
    }
} else {
    echo 'Error: ' . $conn->error . "\n";
}

echo "\n=== Exchanges Table ===\n";
$result = $conn->query('SELECT ExchangeId, PostId, Status FROM Exchanges ORDER BY ExchangeId DESC LIMIT 5');
if ($result) {
    echo "Found " . $result->num_rows . " exchanges\n";
    while ($row = $result->fetch_assoc()) {
        echo "ExchangeId: " . $row['ExchangeId'] . ", PostId: " . $row['PostId'] . ", Status: " . $row['Status'] . "\n";
    }
} else {
    echo 'Error: ' . $conn->error . "\n";
}

$conn->close();
?>
