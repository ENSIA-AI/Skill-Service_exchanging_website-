<?php
/**
 * Test Notification Flow
 * Tests that notifications are created and deleted properly
 */
require_once 'DataBaseManagement/config.php';

echo "=== Testing Notification System ===\n\n";

// Test 1: Check if table structure is correct
echo "1. Checking UserNotifications table structure...\n";
$result = $conn->query('DESCRIBE UserNotifications');
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}
echo "Columns: " . implode(', ', $columns) . "\n";
$hasRecipientId = in_array('RecipientId', $columns);
$hasSenderId = in_array('SenderId', $columns);
echo "✓ Has RecipientId: " . ($hasRecipientId ? 'YES' : 'NO') . "\n";
echo "✓ Has SenderId: " . ($hasSenderId ? 'YES' : 'NO') . "\n\n";

// Test 2: Count current notifications
echo "2. Counting existing notifications...\n";
$result = $conn->query("SELECT COUNT(*) as count FROM UserNotifications WHERE NotificationType = 'booking'");
$row = $result->fetch_assoc();
$beforeCount = $row['count'];
echo "Current booking notifications: $beforeCount\n\n";

// Test 3: Simulate creating a notification
echo "3. Testing notification creation...\n";
$testRecipient = 1; // Post owner
$testSender = 2; // Requester
$testTitle = "TEST: Booking Request";
$testMessage = "Test User has requested to book your session";

$insertQuery = "INSERT INTO UserNotifications 
    (RecipientId, SenderId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection)
    VALUES (?, ?, 'booking', ?, ?, 'no', NOW(), 'Exchange')";

$stmt = $conn->prepare($insertQuery);
$stmt->bind_param('iiss', $testRecipient, $testSender, $testTitle, $testMessage);

if ($stmt->execute()) {
    $newId = $conn->insert_id;
    echo "✓ Notification created successfully! ID: $newId\n";
    $stmt->close();
    
    // Test 4: Verify it was created
    echo "\n4. Verifying notification exists...\n";
    $result = $conn->query("SELECT * FROM UserNotifications WHERE NotificationId = $newId");
    if ($result->num_rows > 0) {
        $notif = $result->fetch_assoc();
        echo "✓ Found notification:\n";
        echo "  - RecipientId: " . $notif['RecipientId'] . "\n";
        echo "  - SenderId: " . $notif['SenderId'] . "\n";
        echo "  - Title: " . $notif['Title'] . "\n";
        echo "  - Message: " . $notif['Message'] . "\n";
    } else {
        echo "✗ Notification not found!\n";
    }
    
    // Test 5: Test deletion
    echo "\n5. Testing notification deletion...\n";
    $deleteSql = "DELETE FROM UserNotifications 
                  WHERE SenderId = ? 
                  AND RecipientId = ? 
                  AND NotificationType = 'booking'
                  AND NotificationSection = 'Exchange'
                  ORDER BY CreatedAt DESC
                  LIMIT 1";
    $delStmt = $conn->prepare($deleteSql);
    $delStmt->bind_param('ii', $testSender, $testRecipient);
    
    if ($delStmt->execute()) {
        $deleted = $delStmt->affected_rows;
        echo "✓ Delete executed. Rows affected: $deleted\n";
        $delStmt->close();
        
        // Verify deletion
        $result = $conn->query("SELECT * FROM UserNotifications WHERE NotificationId = $newId");
        if ($result->num_rows == 0) {
            echo "✓ Notification successfully deleted!\n";
        } else {
            echo "✗ Notification still exists after deletion!\n";
        }
    } else {
        echo "✗ Delete failed: " . $delStmt->error . "\n";
        $delStmt->close();
    }
    
} else {
    echo "✗ Failed to create notification: " . $stmt->error . "\n";
    $stmt->close();
}

// Test 6: Final count
echo "\n6. Final notification count...\n";
$result = $conn->query("SELECT COUNT(*) as count FROM UserNotifications WHERE NotificationType = 'booking'");
$row = $result->fetch_assoc();
$afterCount = $row['count'];
echo "Booking notifications after test: $afterCount\n";
echo "Expected: $beforeCount (should be same as before)\n";

if ($afterCount == $beforeCount) {
    echo "\n✓✓✓ ALL TESTS PASSED! Notification system is working correctly.\n";
} else {
    echo "\n✗ Warning: Notification count changed unexpectedly.\n";
}

$conn->close();
?>
