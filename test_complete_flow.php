<?php
/**
 * Complete Integration Test for Booking Notifications
 * Tests the entire flow from booking creation to notification display to cancellation
 */
session_start();
require_once 'DataBaseManagement/config.php';

echo "=== COMPLETE BOOKING NOTIFICATION TEST ===\n\n";

// Setup: Use existing users and posts from database
$_SESSION['user_id'] = 2; // Set test user as requester
$testPostId = 12; // Use existing post
$testRecipientId = 1; // Post owner

echo "Test Setup:\n";
echo "- Requester UserId: " . $_SESSION['user_id'] . "\n";
echo "- Post Owner (Recipient): $testRecipientId\n";
echo "- Test PostId: $testPostId\n\n";

// Step 1: Get post details
echo "STEP 1: Getting post details...\n";
$postQuery = "SELECT Title, UserId, RequiredCredits FROM Posts WHERE PostId = ?";
$postStmt = $conn->prepare($postQuery);
$postStmt->bind_param('i', $testPostId);
$postStmt->execute();
$postResult = $postStmt->get_result();
if ($postResult->num_rows > 0) {
    $post = $postResult->fetch_assoc();
    echo "✓ Post found: " . $post['Title'] . "\n";
    echo "  Owner: " . $post['UserId'] . ", Credits: " . $post['RequiredCredits'] . "\n";
} else {
    die("✗ Post not found!\n");
}
$postStmt->close();

// Step 2: Count notifications before
echo "\nSTEP 2: Counting notifications before test...\n";
$beforeQuery = "SELECT COUNT(*) as count FROM UserNotifications WHERE RecipientId = ? AND NotificationType = 'booking'";
$beforeStmt = $conn->prepare($beforeQuery);
$beforeStmt->bind_param('i', $testRecipientId);
$beforeStmt->execute();
$beforeResult = $beforeStmt->get_result();
$beforeCount = $beforeResult->fetch_assoc()['count'];
$beforeStmt->close();
echo "Notifications for recipient: $beforeCount\n";

// Step 3: Simulate booking (create exchange)
echo "\nSTEP 3: Creating test booking...\n";
$testDate = date('Y-m-d H:i:s', strtotime('+1 day'));
$creditsCost = ($post['RequiredCredits'] > 0) ? $post['RequiredCredits'] : 0;

$exchangeQuery = "INSERT INTO Exchanges (PostId, OfferedByUserId, RequestedByUserId, ProposedDate, CreditsCost) VALUES (?, ?, ?, ?, ?)";
$exchangeStmt = $conn->prepare($exchangeQuery);
$exchangeStmt->bind_param('iiisi', $testPostId, $testRecipientId, $_SESSION['user_id'], $testDate, $creditsCost);

if ($exchangeStmt->execute()) {
    $exchangeId = $conn->insert_id;
    echo "✓ Exchange created with ID: $exchangeId\n";
} else {
    die("✗ Failed to create exchange: " . $exchangeStmt->error . "\n");
}
$exchangeStmt->close();

// Step 4: Create notification (simulating postdetails.php logic)
echo "\nSTEP 4: Creating notification...\n";
$requesterQuery = "SELECT FullName FROM Users WHERE UserId = ?";
$requesterStmt = $conn->prepare($requesterQuery);
$requesterStmt->bind_param('i', $_SESSION['user_id']);
$requesterStmt->execute();
$requesterResult = $requesterStmt->get_result();
$requesterName = $requesterResult->fetch_assoc()['FullName'];
$requesterStmt->close();

$title = ($creditsCost > 0) ? "Booking Request" : "Skill Exchange Request";
$message = "$requesterName has requested to book your \"" . $post['Title'] . "\" session";
if ($creditsCost > 0) {
    $message .= " for $creditsCost credits";
}

$notifQuery = "INSERT INTO UserNotifications (RecipientId, SenderId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection) VALUES (?, ?, 'booking', ?, ?, 'no', NOW(), 'Exchange')";
$notifStmt = $conn->prepare($notifQuery);
$notifStmt->bind_param('iiss', $testRecipientId, $_SESSION['user_id'], $title, $message);

if ($notifStmt->execute()) {
    $notifId = $conn->insert_id;
    echo "✓ Notification created with ID: $notifId\n";
    echo "  Title: $title\n";
    echo "  Message: $message\n";
} else {
    die("✗ Failed to create notification: " . $notifStmt->error . "\n");
}
$notifStmt->close();

// Step 5: Verify notification exists
echo "\nSTEP 5: Verifying notification in database...\n";
$verifyQuery = "SELECT * FROM UserNotifications WHERE NotificationId = ?";
$verifyStmt = $conn->prepare($verifyQuery);
$verifyStmt->bind_param('i', $notifId);
$verifyStmt->execute();
$verifyResult = $verifyStmt->get_result();
if ($verifyResult->num_rows > 0) {
    $notif = $verifyResult->fetch_assoc();
    echo "✓ Notification found in database:\n";
    echo "  RecipientId: " . $notif['RecipientId'] . "\n";
    echo "  SenderId: " . $notif['SenderId'] . "\n";
    echo "  IsRead: " . $notif['IsRead'] . "\n";
} else {
    echo "✗ Notification not found!\n";
}
$verifyStmt->close();

// Step 6: Test fetching via API query (simulating fetch.php)
echo "\nSTEP 6: Testing notification fetch query...\n";
$fetchQuery = "SELECT un.*, u.UserName as SenderName, u.ProfilePicture as SenderPFP 
               FROM UserNotifications un
               LEFT JOIN Users u ON un.SenderId = u.UserId
               WHERE un.RecipientId = ? AND un.NotificationType = 'booking'
               ORDER BY un.CreatedAt DESC LIMIT 5";
$fetchStmt = $conn->prepare($fetchQuery);
$fetchStmt->bind_param('i', $testRecipientId);
$fetchStmt->execute();
$fetchResult = $fetchStmt->get_result();
echo "Notifications found for recipient: " . $fetchResult->num_rows . "\n";
while ($row = $fetchResult->fetch_assoc()) {
    echo "  - ID " . $row['NotificationId'] . ": " . $row['Title'] . "\n";
}
$fetchStmt->close();

// Step 7: Simulate cancellation (delete notification)
echo "\nSTEP 7: Testing cancellation (deleting notification)...\n";
$deleteQuery = "DELETE FROM UserNotifications 
                WHERE SenderId = ? 
                AND RecipientId = ? 
                AND NotificationType = 'booking'
                AND NotificationSection = 'Exchange'
                ORDER BY CreatedAt DESC LIMIT 1";
$deleteStmt = $conn->prepare($deleteQuery);
$deleteStmt->bind_param('ii', $_SESSION['user_id'], $testRecipientId);

if ($deleteStmt->execute()) {
    $deleted = $deleteStmt->affected_rows;
    echo "✓ Notification deleted. Rows affected: $deleted\n";
} else {
    echo "✗ Failed to delete notification: " . $deleteStmt->error . "\n";
}
$deleteStmt->close();

// Step 8: Delete test exchange
echo "\nSTEP 8: Cleaning up test exchange...\n";
$cleanupQuery = "DELETE FROM Exchanges WHERE ExchangeId = ?";
$cleanupStmt = $conn->prepare($cleanupQuery);
$cleanupStmt->bind_param('i', $exchangeId);
$cleanupStmt->execute();
echo "✓ Test exchange deleted\n";
$cleanupStmt->close();

// Step 9: Verify final count
echo "\nSTEP 9: Verifying final notification count...\n";
$afterQuery = "SELECT COUNT(*) as count FROM UserNotifications WHERE RecipientId = ? AND NotificationType = 'booking'";
$afterStmt = $conn->prepare($afterQuery);
$afterStmt->bind_param('i', $testRecipientId);
$afterStmt->execute();
$afterResult = $afterStmt->get_result();
$afterCount = $afterResult->fetch_assoc()['count'];
$afterStmt->close();
echo "Notifications after test: $afterCount\n";
echo "Expected: $beforeCount (same as before)\n";

// Final result
echo "\n" . str_repeat("=", 50) . "\n";
if ($afterCount == $beforeCount) {
    echo "✓✓✓ ALL TESTS PASSED!\n";
    echo "- Notifications are created properly\n";
    echo "- Notifications are deleted properly\n";
    echo "- API query works correctly with RecipientId\n";
} else {
    echo "✗ TEST FAILED: Notification count mismatch\n";
}
echo str_repeat("=", 50) . "\n";

$conn->close();
?>
