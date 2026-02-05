<?php
session_start();
$_SESSION['user_id'] = 2;
require_once 'DataBaseManagement/config.php';

echo "=== Testing Credit Payment Booking ===\n\n";

// Simulate POST data for credit payment
$_POST['selectedDate'] = date('Y-m-d H:i:s', strtotime('+2 days 14:00'));
$_POST['selectedPaymentMethod'] = 'credits';
$_POST['submit'] = '1';

$postId = 19; // Use a post with credits
$userId = $_SESSION['user_id'];

echo "Test Parameters:\n";
echo "- PostId: $postId\n";
echo "- UserId: $userId\n";
echo "- Payment Method: " . $_POST['selectedPaymentMethod'] . "\n";
echo "- Selected Date: " . $_POST['selectedDate'] . "\n\n";

// Get post details
$postQuery = "SELECT * FROM Posts WHERE PostId = ?";
$postStmt = $conn->prepare($postQuery);
$postStmt->bind_param('i', $postId);
$postStmt->execute();
$post = $postStmt->get_result()->fetch_assoc();
$postStmt->close();

echo "Post Details:\n";
echo "- Title: " . $post['Title'] . "\n";
echo "- Owner: " . $post['UserId'] . "\n";
echo "- RequiredCredits: " . $post['RequiredCredits'] . "\n";
echo "- PaymentMethod: " . $post['PaymentMethod'] . "\n\n";

$paymentMethod = $_POST['selectedPaymentMethod'];
$isCreditPayment = ($paymentMethod === 'credits' || $paymentMethod === 'credit');
$creditsCost = $isCreditPayment ? (int)$post['RequiredCredits'] : 0;

echo "Processing:\n";
echo "- paymentMethod variable: '$paymentMethod'\n";
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

// Create notification message
if ($paymentMethod === 'exchange') {
    $message = "$requesterName has requested to exchange skills for \"" . $post['Title'] . "\" session";
    $title = "Skill Exchange Request";
    echo "Notification Type: EXCHANGE\n";
} else {
    $message = "$requesterName has requested to book \"" . $post['Title'] . "\" session for " . $post['RequiredCredits'] . " credits";
    $title = "Booking Request";
    echo "Notification Type: CREDITS\n";
}

echo "- Title: $title\n";
echo "- Message: $message\n\n";

// Test the condition
echo "Condition Tests:\n";
echo "- (\$paymentMethod === 'exchange'): " . (($paymentMethod === 'exchange') ? 'TRUE' : 'FALSE') . "\n";
echo "- (\$paymentMethod === 'credits'): " . (($paymentMethod === 'credits') ? 'TRUE' : 'FALSE') . "\n";
echo "- else branch taken: " . (($paymentMethod !== 'exchange') ? 'YES' : 'NO') . "\n";

$conn->close();
?>
