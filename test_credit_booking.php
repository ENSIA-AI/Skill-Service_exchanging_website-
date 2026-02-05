<?php
/**
 * Test actual booking with credits
 */
session_start();
$_SESSION['user_id'] = 2; // Requester
require_once 'DataBaseManagement/config.php';

echo "=== LIVE CREDIT BOOKING TEST ===\n\n";

// Simulate actual POST data as it would come from the form
$_POST['SelectedDate'] = date('Y-m-d H:i:s', strtotime('+3 days 15:00'));
$_POST['selectedPaymentMethod'] = 'credits';

$postId = 10; // Use a post that has credits
$userId = $_SESSION['user_id'];

// Get post details
$postQuery = "SELECT * FROM Posts WHERE PostId = ?";
$stmt = $conn->prepare($postQuery);
$stmt->bind_param('i', $postId);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    die("Post not found!\n");
}

echo "Post Details:\n";
echo "- PostId: $postId\n";
echo "- Title: " . $post['Title'] . "\n";
echo "- Owner UserId: " . $post['UserId'] . "\n";
echo "- RequiredCredits: " . $post['RequiredCredits'] . "\n";
echo "- PaymentMethod: " . $post['PaymentMethod'] . "\n\n";

echo "Form Data Received:\n";
echo "- SelectedDate: " . $_POST['SelectedDate'] . "\n";
echo "- selectedPaymentMethod: " . $_POST['selectedPaymentMethod'] . "\n";
echo "- Requester UserId: $userId\n\n";

// Process like postdetails.php
$paymentMethod = $_POST['selectedPaymentMethod'];
$isCreditPayment = ($paymentMethod === 'credits' || $paymentMethod === 'credit');
$creditsCost = $isCreditPayment ? (int)$post['RequiredCredits'] : 0;

echo "Processing:\n";
echo "- paymentMethod: '$paymentMethod'\n";
echo "- isCreditPayment: " . ($isCreditPayment ? 'true' : 'false') . "\n";
echo "- creditsCost: $creditsCost\n\n";

// Get requester name
$userQuery = "SELECT FullName FROM Users WHERE UserId = ?";
$userStmt = $conn->prepare($userQuery);
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$userData = $userStmt->get_result()->fetch_assoc();
$requesterName = $userData['FullName'];
$userStmt->close();

echo "Requester: $requesterName\n\n";

// Count notifications before
$beforeQuery = "SELECT COUNT(*) as count FROM UserNotifications WHERE RecipientId = ? AND NotificationType = 'booking'";
$beforeStmt = $conn->prepare($beforeQuery);
$beforeStmt->bind_param('i', $post['UserId']);
$beforeStmt->execute();
$beforeCount = $beforeStmt->get_result()->fetch_assoc()['count'];
$beforeStmt->close();
echo "Notifications BEFORE: $beforeCount\n\n";

// Insert exchange
echo "STEP 1: Creating Exchange...\n";
$exchangeQuery = "INSERT INTO Exchanges (PostId, OfferedByUserId, RequestedByUserId, ProposedDate, CreditsCost) VALUES (?, ?, ?, ?, ?)";
$exchangeStmt = $conn->prepare($exchangeQuery);
$exchangeStmt->bind_param('iiisi', $postId, $post['UserId'], $userId, $_POST['SelectedDate'], $creditsCost);

if ($exchangeStmt->execute()) {
    $exchangeId = $conn->insert_id;
    echo "✓ Exchange created: ID = $exchangeId\n";
    $exchangeStmt->close();
} else {
    die("✗ Exchange failed: " . $exchangeStmt->error . "\n");
}

// Create notification
echo "\nSTEP 2: Creating Notification...\n";
$startTime = date('H:i', strtotime($_POST['SelectedDate']));
$endTime = date('H:i', strtotime($_POST['SelectedDate'] . ' + 60 minutes'));
$dateFormatted = date('M d, Y', strtotime($_POST['SelectedDate']));
$timeSlotDisplay = "$startTime to $endTime on $dateFormatted";

if ($paymentMethod === 'exchange') {
    $message = "$requesterName has requested to exchange skills for your \"" . $post['Title'] . "\" session ($timeSlotDisplay)";
    $title = "Skill Exchange Request";
} else {
    $message = "$requesterName has requested to book your \"" . $post['Title'] . "\" session for " . $post['RequiredCredits'] . " credits ($timeSlotDisplay)";
    $title = "Booking Request";
}

echo "Notification details:\n";
echo "- Title: $title\n";
echo "- Message: $message\n";
echo "- RecipientId: " . $post['UserId'] . "\n";
echo "- SenderId: $userId\n\n";

$notifQuery = "INSERT INTO UserNotifications 
    (RecipientId, SenderId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection)
    VALUES (?, ?, 'booking', ?, ?, 'no', NOW(), 'Exchange')";

$notifStmt = $conn->prepare($notifQuery);
if ($notifStmt) {
    $notifStmt->bind_param('iiss', $post['UserId'], $userId, $title, $message);
    
    if ($notifStmt->execute()) {
        $notifId = $conn->insert_id;
        echo "✓ Notification created: ID = $notifId\n";
        $notifStmt->close();
    } else {
        echo "✗ Notification execute failed: " . $notifStmt->error . "\n";
        $notifStmt->close();
    }
} else {
    echo "✗ Notification prepare failed: " . $conn->error . "\n";
}

// Verify notification was created
echo "\nSTEP 3: Verifying Notification...\n";
$verifyQuery = "SELECT * FROM UserNotifications WHERE NotificationId = ?";
$verifyStmt = $conn->prepare($verifyQuery);
$verifyStmt->bind_param('i', $notifId);
$verifyStmt->execute();
$verifyResult = $verifyStmt->get_result();

if ($verifyResult->num_rows > 0) {
    $notif = $verifyResult->fetch_assoc();
    echo "✓ Notification exists in database:\n";
    echo "  - NotificationId: " . $notif['NotificationId'] . "\n";
    echo "  - RecipientId: " . $notif['RecipientId'] . "\n";
    echo "  - SenderId: " . $notif['SenderId'] . "\n";
    echo "  - Title: " . $notif['Title'] . "\n";
    echo "  - Message: " . $notif['Message'] . "\n";
} else {
    echo "✗ Notification NOT found in database!\n";
}
$verifyStmt->close();

// Count after
$afterQuery = "SELECT COUNT(*) as count FROM UserNotifications WHERE RecipientId = ? AND NotificationType = 'booking'";
$afterStmt = $conn->prepare($afterQuery);
$afterStmt->bind_param('i', $post['UserId']);
$afterStmt->execute();
$afterCount = $afterStmt->get_result()->fetch_assoc()['count'];
$afterStmt->close();
echo "\nNotifications AFTER: $afterCount\n";
echo "Difference: " . ($afterCount - $beforeCount) . "\n";

// Cleanup
echo "\nCleaning up test data...\n";
$conn->query("DELETE FROM UserNotifications WHERE NotificationId = $notifId");
$conn->query("DELETE FROM Exchanges WHERE ExchangeId = $exchangeId");
echo "✓ Cleanup complete\n";

echo "\n" . str_repeat("=", 50) . "\n";
if ($afterCount > $beforeCount) {
    echo "✓✓✓ TEST PASSED! Notification was created successfully.\n";
} else {
    echo "✗ TEST FAILED! Notification was NOT created.\n";
}

$conn->close();
?>
