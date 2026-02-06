<?php
session_start();
$_SESSION['user_id'] = 2;  // Simulate logged-in user

require 'DataBaseManagement/config.php';

echo "<h1>Simulating Actual Booking Flow for PostId=1</h1>";

$postId = 1;

// Step 1: Get post details
echo "<h2>Step 1: Fetch Post Details</h2>";
$postQuery = "SELECT * FROM Posts WHERE PostId = ?";
$stmt = $conn->prepare($postQuery);
$stmt->bind_param('i', $postId);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    die("Post not found!");
}

echo "<p>Post Title: <strong>".$post['Title']."</strong></p>";
echo "<p>Post Owner (UserId): <strong>".$post['UserId']."</strong></p>";
echo "<p>Duration: <strong>".$post['Duration']." minutes</strong></p>";
echo "<p>RequiredCredits: <strong>".$post['RequiredCredits']."</strong></p>";

// Step 2: Set booking variables
echo "<h2>Step 2: Set Booking Variables</h2>";

$postUserId = $post['UserId'];
$userId = $_SESSION['user_id'];
$selectedDate = date('Y-m-d H:i:s', strtotime('+5 days 14:00'));
$postTitle = $post['Title'];
$postDuration = $post['Duration'];
$requiredCredits = $post['RequiredCredits'];
$paymentMethod = 'credits';

echo "<p>PostId: $postId</p>";
echo "<p>Post Owner (postUserId): $postUserId</p>";
echo "<p>Requester (userId): $userId</p>";
echo "<p>SelectedDate: $selectedDate</p>";

// Step 3: Insert into Exchanges
echo "<h2>Step 3: Insert into Exchanges</h2>";

$creditsCost = $requiredCredits;
$sqlInsert = "INSERT INTO Exchanges (PostId, OfferedByUserId, RequestedByUserId, ProposedDate, CreditsCost) VALUES (?, ?, ?, ?, ?)";
$stmtInsert = $conn->prepare($sqlInsert);
$stmtInsert->bind_param('iiisi', $postId, $postUserId, $userId, $selectedDate, $creditsCost);

if ($stmtInsert->execute()) {
    $exchangeId = $conn->insert_id;
    echo "<p style='color:green;'><strong>✓ Exchange created: ID=$exchangeId</strong></p>";
    $stmtInsert->close();
} else {
    echo "<p style='color:red;'><strong>✗ Exchange insert failed:</strong> " . $stmtInsert->error . "</p>";
    $stmtInsert->close();
    die();
}

// Step 4: Get requester name
echo "<h2>Step 4: Get Requester Name</h2>";

$userNameSql = "SELECT FullName FROM Users WHERE UserId = ?";
$userNameStmt = $conn->prepare($userNameSql);
$userNameStmt->bind_param('i', $userId);
$userNameStmt->execute();
$userData = $userNameStmt->get_result()->fetch_assoc();
$requesterName = $userData['FullName'] ?? 'Unknown User';
$userNameStmt->close();

echo "<p>Requester Name: <strong>$requesterName</strong></p>";

// Step 5: Create notification message
echo "<h2>Step 5: Create Notification Message</h2>";

$startTime = date('H:i', strtotime($selectedDate));
$endTime = date('H:i', strtotime($selectedDate . ' + ' . $postDuration . ' minutes'));
$dateFormatted = date('M d, Y', strtotime($selectedDate));
$timeSlotDisplay = "$startTime to $endTime on $dateFormatted";

if ($paymentMethod === 'exchange') {
    $message = "$requesterName has requested to exchange skills for your \"$postTitle\" session ($timeSlotDisplay)";
    $title = "Skill Exchange Request";
} else {
    $message = "$requesterName has requested to book your \"$postTitle\" session for $requiredCredits credits ($timeSlotDisplay)";
    $title = "Booking Request";
}

echo "<p>Title: <strong>$title</strong></p>";
echo "<p>Message: <strong>".substr($message, 0, 60)."...</strong></p>";

// Step 6: Insert into UserNotifications
echo "<h2>Step 6: Insert into UserNotifications</h2>";

echo "<p>SQL Query:</p>";
echo "<pre style='background:#f0f0f0; padding:10px;'>INSERT INTO UserNotifications 
    (RecipientId, SenderId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection)
    VALUES ($postUserId, $userId, 'booking', '$title', '$message', 'no', NOW(), 'Exchange')</pre>";

echo "<p>Parameters:<br>";
echo "RecipientId: $postUserId (type: i)<br>";
echo "SenderId: $userId (type: i)<br>";
echo "Title: $title (type: s)<br>";
echo "Message: ".substr($message, 0, 40)."... (type: s)</p>";

$insertQuery = "INSERT INTO UserNotifications 
    (RecipientId, SenderId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection)
    VALUES (?, ?, 'booking', ?, ?, 'no', NOW(), 'Exchange')";

$notifStmt = $conn->prepare($insertQuery);
if (!$notifStmt) {
    echo "<p style='color:red;'><strong>✗ PREPARE ERROR:</strong> " . $conn->error . "</p>";
    die();
}

$notifStmt->bind_param('iiss', $postUserId, $userId, $title, $message);

if ($notifStmt->execute()) {
    $notificationId = $conn->insert_id;
    echo "<p style='color:green;'><strong>✓ Notification created: ID=$notificationId</strong></p>";
} else {
    echo "<p style='color:red;'><strong>✗ EXECUTE ERROR:</strong> " . $notifStmt->error . "</p>";
}

$notifStmt->close();

// Step 7: Verify in database
echo "<h2>Step 7: Verify in Database</h2>";

echo "<h3>Exchanges Table (latest):</h3>";
$exResult = $conn->query("SELECT * FROM Exchanges WHERE PostId = $postId ORDER BY ExchangeId DESC LIMIT 2");
while ($row = $exResult->fetch_assoc()) {
    echo "<p>ExchangeId: ".$row['ExchangeId']." | Owner: ".$row['OfferedByUserId']." | Requester: ".$row['RequestedByUserId']." | Status: ".$row['Status']."</p>";
}

echo "<h3>UserNotifications Table (latest):</h3>";
$notifResult = $conn->query("SELECT * FROM UserNotifications WHERE RecipientId = $postUserId ORDER BY NotificationId DESC LIMIT 3");
while ($row = $notifResult->fetch_assoc()) {
    echo "<p>NotifId: ".$row['NotificationId']." | Recipient: ".$row['RecipientId']." | Sender: ".$row['SenderId']." | Type: ".$row['NotificationType']." | Title: ".$row['Title']."</p>";
}
?>
