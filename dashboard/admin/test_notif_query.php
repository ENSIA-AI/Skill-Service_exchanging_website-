<?php
// Test script to verify notification data retrieval
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../DataBaseManagement/config.php';

// Check if logged in
if (!isset($_SESSION['userId'])) {
    die('Not logged in');
}

try {
    // Test 1: Count total notifications
    $countQuery = "SELECT COUNT(*) as total FROM UserNotifications";
    $result = $conn->query($countQuery);
    $countData = $result->fetch_assoc();
    echo "Total notifications in DB: " . $countData['total'] . "<br>";

    // Test 2: Retrieve all notifications
    $query = "SELECT NotificationId, UserId, SenderId, NotificationType, 
                     NotificationSection, Title, Message, IsRead, CreatedAt
              FROM UserNotifications
              LIMIT 5";
    
    $result = $conn->query($query);
    echo "Query result type: " . gettype($result) . "<br>";
    echo "Number of rows: " . $result->num_rows . "<br>";
    
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['NotificationId'] . " | Title: " . $row['Title'] . " | IsRead: " . $row['IsRead'] . "<br>";
    }

    // Test 3: Check if we can use prepared statements
    $stmt = $conn->prepare("SELECT NotificationId, Title FROM UserNotifications LIMIT 3");
    if (!$stmt) {
        die('Prepare failed: ' . $conn->error);
    }
    if (!$stmt->execute()) {
        die('Execute failed: ' . $stmt->error);
    }
    $result = $stmt->get_result();
    echo "<br>Prepared statement test - rows: " . $result->num_rows . "<br>";
    
    $stmt->close();
    echo "All tests passed!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
