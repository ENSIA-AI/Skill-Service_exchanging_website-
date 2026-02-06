<?php
session_start();
require_once '../../DataBaseManagement/config.php';

// Simple test to verify the notification table and query work
echo "<h2>Notification Management - Database Test</h2>";

// Test 1: Check if UserNotifications table exists
echo "<h3>Test 1: Table Structure</h3>";
$tableCheck = $conn->query("DESCRIBE UserNotifications");
if ($tableCheck) {
    echo "<p style='color: green;'>✓ UserNotifications table exists</p>";
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $tableCheck->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ Table error: " . $conn->error . "</p>";
}

// Test 2: Check notification count
echo "<h3>Test 2: Notification Count</h3>";
$countResult = $conn->query("SELECT COUNT(*) as total FROM UserNotifications");
if ($countResult) {
    $row = $countResult->fetch_assoc();
    echo "<p style='color: green;'>✓ Total notifications: " . $row['total'] . "</p>";
} else {
    echo "<p style='color: red;'>✗ Query error: " . $conn->error . "</p>";
}

// Test 3: Test the main query
echo "<h3>Test 3: Main Query (with JOINs)</h3>";
$testQuery = "SELECT n.NotificationId, n.UserId, n.SenderId, n.NotificationType, 
                 n.NotificationSection, n.Title, n.Message, n.IsRead, n.CreatedAt,
                 u.UserName as RecipientName, u.ProfilePicture as RecipientPFP,
                 s.UserName as SenderName, s.ProfilePicture as SenderPFP
          FROM UserNotifications n
          LEFT JOIN Users u ON n.UserId = u.UserId
          LEFT JOIN Users s ON n.SenderId = s.UserId
          LIMIT 5";

$result = $conn->query($testQuery);
if ($result) {
    echo "<p style='color: green;'>✓ Query executed successfully</p>";
    echo "<p>Found " . $result->num_rows . " notifications</p>";
    
    if ($result->num_rows > 0) {
        echo "<table border='1'><tr><th>ID</th><th>Type</th><th>Title</th><th>Message</th><th>Read</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['NotificationId'] . "</td>";
            echo "<td>" . $row['NotificationType'] . "</td>";
            echo "<td>" . htmlspecialchars($row['Title']) . "</td>";
            echo "<td>" . substr(htmlspecialchars($row['Message']), 0, 50) . "...</td>";
            echo "<td>" . $row['IsRead'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: red;'>✗ Query error: " . $conn->error . "</p>";
}

// Test 4: Check users exist for JOIN
echo "<h3>Test 4: Users Count</h3>";
$usersCount = $conn->query("SELECT COUNT(*) as total FROM Users");
if ($usersCount) {
    $row = $usersCount->fetch_assoc();
    echo "<p style='color: green;'>✓ Total users: " . $row['total'] . "</p>";
} else {
    echo "<p style='color: red;'>✗ Query error: " . $conn->error . "</p>";
}

$conn->close();
?>
