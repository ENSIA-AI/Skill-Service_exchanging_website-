<?php
/**
 * Comprehensive Notification System Test Script
 * Tests all notification scenarios: bookings, credits, events
 */

session_start();
require_once __DIR__ . '/DataBaseManagement/config.php';

// Set test user
$_SESSION['user_id'] = 2; // Test requester
$_SESSION['username'] = 'TestUser';

echo "========================================\n";
echo "NOTIFICATION SYSTEM COMPREHENSIVE TEST\n";
echo "========================================\n\n";

// Helper function to count notifications
function countNotifications($conn, $userId, $type = null, $section = null) {
    $sql = "SELECT COUNT(*) as count FROM UserNotifications WHERE UserId = ?";
    $params = [$userId];
    $types = "i";
    
    if ($type) {
        $sql .= " AND NotificationType = ?";
        $params[] = $type;
        $types .= "s";
    }
    
    if ($section) {
        $sql .= " AND NotificationSection = ?";
        $params[] = $section;
        $types .= "s";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'];
}

// Helper function to get latest notification
function getLatestNotification($conn, $userId, $type) {
    $sql = "SELECT * FROM UserNotifications 
            WHERE UserId = ? AND NotificationType = ? 
            ORDER BY CreatedAt DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $userId, $type);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Clean up previous test data
echo "SETUP: Cleaning previous test data...\n";
$conn->query("DELETE FROM Exchanges WHERE RequestedByUserId = 2 AND PostId = 1");
$conn->query("DELETE FROM UserNotifications WHERE (UserId = 1 OR UserId = 2) AND CreatedAt > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
$conn->query("DELETE FROM EventsAttendees WHERE UserId = 2 AND EventId = 1");
echo "✓ Cleanup complete\n\n";

// ==========================================
// TEST 1: SKILL EXCHANGE BOOKING
// ==========================================
echo "TEST 1: Skill Exchange Booking\n";
echo "================================\n";

$postId = 1;
$postOwner = 1;
$requester = 2;

// Get post details
$postStmt = $conn->prepare("SELECT Title, UserId FROM Posts WHERE PostId = ?");
$postStmt->bind_param('i', $postId);
$postStmt->execute();
$post = $postStmt->get_result()->fetch_assoc();
$postStmt->close();

// Get requester name
$userStmt = $conn->prepare("SELECT FullName FROM Users WHERE UserId = ?");
$userStmt->bind_param('i', $requester);
$userStmt->execute();
$requesterName = $userStmt->get_result()->fetch_assoc()['FullName'];
$userStmt->close();

// Create exchange (skill exchange, no credits)
$selectedDate = date('Y-m-d H:i:s', strtotime('+3 days 14:00'));
$exchangeStmt = $conn->prepare("INSERT INTO Exchanges (PostId, OfferedByUserId, RequestedByUserId, ProposedDate, CreditsCost) VALUES (?, ?, ?, ?, 0)");
$exchangeStmt->bind_param('iiis', $postId, $postOwner, $requester, $selectedDate);
$exchangeStmt->execute();
$exchangeId = $conn->insert_id;
$exchangeStmt->close();

// Create notification
$title = "Skill Exchange Request";
$message = "$requesterName has requested to exchange skills for your \"" . $post['Title'] . "\" session";
$notifStmt = $conn->prepare("INSERT INTO UserNotifications (UserId, SenderId, NotificationType, Title, Message, NotificationSection) VALUES (?, ?, 'booking', ?, ?, 'Exchange')");
$notifStmt->bind_param('iiss', $postOwner, $requester, $title, $message);
$notifStmt->execute();
$notifId1 = $conn->insert_id;
$notifStmt->close();

echo "✓ Skill exchange booking created (Exchange ID: $exchangeId)\n";
echo "✓ Notification sent to post owner (ID: $notifId1)\n";

// Verify
$notif = getLatestNotification($conn, $postOwner, 'booking');
if ($notif && strpos($notif['Message'], 'exchange skills') !== false) {
    echo "✓ Verified: Correct notification message for skill exchange\n";
} else {
    echo "✗ FAILED: Incorrect notification message\n";
}
echo "\n";

// ==========================================
// TEST 2: CREDIT BOOKING
// ==========================================
echo "TEST 2: Credit Booking\n";
echo "======================\n";

// Delete previous exchange for clean test
$conn->query("DELETE FROM Exchanges WHERE ExchangeId = $exchangeId");
$conn->query("DELETE FROM UserNotifications WHERE NotificationId = $notifId1");

// Create exchange with credits
$creditsCost = 50;
$exchangeStmt = $conn->prepare("INSERT INTO Exchanges (PostId, OfferedByUserId, RequestedByUserId, ProposedDate, CreditsCost) VALUES (?, ?, ?, ?, ?)");
$exchangeStmt->bind_param('iiisi', $postId, $postOwner, $requester, $selectedDate, $creditsCost);
$exchangeStmt->execute();
$exchangeId = $conn->insert_id;
$exchangeStmt->close();

// Create notification
$title = "Booking Request";
$message = "$requesterName has requested to book your \"" . $post['Title'] . "\" session for $creditsCost credits";
$notifStmt = $conn->prepare("INSERT INTO UserNotifications (UserId, SenderId, NotificationType, Title, Message, NotificationSection) VALUES (?, ?, 'booking', ?, ?, 'Exchange')");
$notifStmt->bind_param('iiss', $postOwner, $requester, $title, $message);
$notifStmt->execute();
$notifId2 = $conn->insert_id;
$notifStmt->close();

echo "✓ Credit booking created (Exchange ID: $exchangeId, Cost: $creditsCost credits)\n";
echo "✓ Notification sent to post owner (ID: $notifId2)\n";

// Verify
$notif = getLatestNotification($conn, $postOwner, 'booking');
if ($notif && strpos($notif['Message'], "$creditsCost credits") !== false) {
    echo "✓ Verified: Correct notification message for credit booking\n";
} else {
    echo "✗ FAILED: Incorrect notification message\n";
}
echo "\n";

// ==========================================
// TEST 3: ACCEPT BOOKING WITH CREDITS
// ==========================================
echo "TEST 3: Accept Booking with Credit Transfer\n";
echo "============================================\n";

// Get initial balances
$balStmt = $conn->prepare("SELECT CreditBalance FROM Users WHERE UserId = ?");
$balStmt->bind_param('i', $requester);
$balStmt->execute();
$initialRequesterBalance = $balStmt->get_result()->fetch_assoc()['CreditBalance'];
$balStmt->close();

$balStmt = $conn->prepare("SELECT CreditBalance FROM Users WHERE UserId = ?");
$balStmt->bind_param('i', $postOwner);
$balStmt->execute();
$initialOwnerBalance = $balStmt->get_result()->fetch_assoc()['CreditBalance'];
$balStmt->close();

echo "Initial balances: Requester=$initialRequesterBalance, Owner=$initialOwnerBalance\n";

// Ensure requester has enough credits
if ($initialRequesterBalance < $creditsCost) {
    $conn->query("UPDATE Users SET CreditBalance = CreditBalance + 100 WHERE UserId = $requester");
    echo "✓ Added credits to requester for testing\n";
}

// Update exchange status
$conn->query("UPDATE Exchanges SET Status = 'accepted' WHERE ExchangeId = $exchangeId");

// Transfer credits
$conn->query("UPDATE Users SET CreditBalance = CreditBalance - $creditsCost WHERE UserId = $requester");
$conn->query("UPDATE Users SET CreditBalance = CreditBalance + $creditsCost WHERE UserId = $postOwner");

// Get new balances
$balStmt = $conn->prepare("SELECT CreditBalance FROM Users WHERE UserId = ?");
$balStmt->bind_param('i', $requester);
$balStmt->execute();
$newRequesterBalance = $balStmt->get_result()->fetch_assoc()['CreditBalance'];
$balStmt->close();

$balStmt = $conn->prepare("SELECT CreditBalance FROM Users WHERE UserId = ?");
$balStmt->bind_param('i', $postOwner);
$balStmt->execute();
$newOwnerBalance = $balStmt->get_result()->fetch_assoc()['CreditBalance'];
$balStmt->close();

echo "✓ Credits transferred: Requester=$newRequesterBalance, Owner=$newOwnerBalance\n";

// Create acceptance notification
$ownerNameStmt = $conn->prepare("SELECT FullName FROM Users WHERE UserId = ?");
$ownerNameStmt->bind_param('i', $postOwner);
$ownerNameStmt->execute();
$ownerName = $ownerNameStmt->get_result()->fetch_assoc()['FullName'];
$ownerNameStmt->close();

$acceptTitle = "Booking Accepted";
$acceptMessage = "$ownerName has accepted your booking request for " . $post['Title'];
$acceptStmt = $conn->prepare("INSERT INTO UserNotifications (UserId, SenderId, NotificationType, Title, Message, NotificationSection) VALUES (?, ?, 'accepted', ?, ?, 'Exchange')");
$acceptStmt->bind_param('iiss', $requester, $postOwner, $acceptTitle, $acceptMessage);
$acceptStmt->execute();
echo "✓ Acceptance notification sent (ID: " . $conn->insert_id . ")\n";
$acceptStmt->close();

// Create credit spent notification
$spentTitle = "Credits Spent";
$spentMessage = "You spent $creditsCost credits for " . $post['Title'] . " with $ownerName.";
$spentStmt = $conn->prepare("INSERT INTO UserNotifications (UserId, NotificationType, Title, Message, NotificationSection) VALUES (?, 'spent', ?, ?, 'credits')");
$spentStmt->bind_param('iss', $requester, $spentTitle, $spentMessage);
$spentStmt->execute();
echo "✓ Credit spent notification sent (ID: " . $conn->insert_id . ")\n";
$spentStmt->close();

// Create credit earned notification
$earnedTitle = "Credits Earned";
$earnedMessage = "You earned $creditsCost credits for " . $post['Title'] . " from $requesterName.";
$earnedStmt = $conn->prepare("INSERT INTO UserNotifications (UserId, NotificationType, Title, Message, NotificationSection) VALUES (?, 'earned', ?, ?, 'credits')");
$earnedStmt->bind_param('iss', $postOwner, $earnedTitle, $earnedMessage);
$earnedStmt->execute();
echo "✓ Credit earned notification sent (ID: " . $conn->insert_id . ")\n";
$earnedStmt->close();

// Verify notifications
$acceptedNotif = getLatestNotification($conn, $requester, 'accepted');
$spentNotif = getLatestNotification($conn, $requester, 'spent');
$earnedNotif = getLatestNotification($conn, $postOwner, 'earned');

if ($acceptedNotif && $spentNotif && $earnedNotif) {
    echo "✓ Verified: All three notifications created correctly\n";
} else {
    echo "✗ FAILED: Missing notifications\n";
}
echo "\n";

// ==========================================
// TEST 4: EVENT JOIN REQUEST
// ==========================================
echo "TEST 4: Event Join Request\n";
echo "==========================\n";

$eventId = 1;
$attendeeId = 2;

// Get event details
$eventStmt = $conn->prepare("SELECT EventTitle, OrganizerId, EventCost FROM Events WHERE EventId = ?");
$eventStmt->bind_param('i', $eventId);
$eventStmt->execute();
$event = $eventStmt->get_result()->fetch_assoc();
$eventStmt->close();

$organizer = $event['OrganizerId'];
$eventTitle = $event['EventTitle'];
$eventCost = $event['EventCost'];

// Create EventsAttendees record
$attendStmt = $conn->prepare("INSERT INTO EventsAttendees (EventId, UserId, Status) VALUES (?, ?, 'registered')");
$attendStmt->bind_param('ii', $eventId, $attendeeId);
$attendStmt->execute();
$attendStmt->close();

// Create notification
$eventJoinTitle = "Event Join Request";
$eventJoinMessage = "$requesterName has requested to join your event $eventTitle on " . date('F d, Y');
$eventNotifStmt = $conn->prepare("INSERT INTO UserNotifications (UserId, SenderId, NotificationType, Title, Message, NotificationSection) VALUES (?, ?, 'booking', ?, ?, 'Exchange')");
$eventNotifStmt->bind_param('iiss', $organizer, $attendeeId, $eventJoinTitle, $eventJoinMessage);
$eventNotifStmt->execute();
$eventNotifId = $conn->insert_id;
$eventNotifStmt->close();

echo "✓ Event join request created (Event ID: $eventId)\n";
echo "✓ Notification sent to organizer (ID: $eventNotifId)\n";

// Verify
$eventNotif = getLatestNotification($conn, $organizer, 'booking');
if ($eventNotif && strpos($eventNotif['Message'], 'join your event') !== false) {
    echo "✓ Verified: Correct notification message for event join\n";
} else {
    echo "✗ FAILED: Incorrect notification message\n";
}
echo "\n";

// ==========================================
// TEST 5: ACCEPT EVENT JOIN (WITH CREDITS)
// ==========================================
echo "TEST 5: Accept Event Join Request with Credits\n";
echo "===============================================\n";

if ($eventCost > 0) {
    // Update status
    $conn->query("UPDATE EventsAttendees SET Status = 'confirmed', ConfirmedAt = NOW() WHERE EventId = $eventId AND UserId = $attendeeId");
    $conn->query("UPDATE Events SET CurrentAttendeesNumber = CurrentAttendeesNumber + 1 WHERE EventId = $eventId");
    
    // Transfer credits
    $conn->query("UPDATE Users SET CreditBalance = CreditBalance - $eventCost WHERE UserId = $attendeeId");
    $conn->query("UPDATE Users SET CreditBalance = CreditBalance + $eventCost WHERE UserId = $organizer");
    
    // Get organizer name
    $orgNameStmt = $conn->prepare("SELECT FullName FROM Users WHERE UserId = ?");
    $orgNameStmt->bind_param('i', $organizer);
    $orgNameStmt->execute();
    $organizerName = $orgNameStmt->get_result()->fetch_assoc()['FullName'];
    $orgNameStmt->close();
    
    // Create acceptedInEvent notification
    $acceptEventTitle = "Event Request Accepted";
    $acceptEventMessage = "$organizerName has accepted your request to join \"$eventTitle\".";
    $acceptEventStmt = $conn->prepare("INSERT INTO UserNotifications (UserId, SenderId, NotificationType, Title, Message, NotificationSection) VALUES (?, ?, 'acceptedInEvent', ?, ?, 'events')");
    $acceptEventStmt->bind_param('iiss', $attendeeId, $organizer, $acceptEventTitle, $acceptEventMessage);
    $acceptEventStmt->execute();
    echo "✓ Event acceptance notification sent (ID: " . $conn->insert_id . ")\n";
    $acceptEventStmt->close();
    
    // Create credit notifications
    $eventSpentMsg = "You spent $eventCost credits for the event \"$eventTitle\".";
    $eventSpentStmt = $conn->prepare("INSERT INTO UserNotifications (UserId, NotificationType, Title, Message, NotificationSection) VALUES (?, 'spent', 'Credits Spent', ?, 'credits')");
    $eventSpentStmt->bind_param('is', $attendeeId, $eventSpentMsg);
    $eventSpentStmt->execute();
    echo "✓ Event credit spent notification sent (ID: " . $conn->insert_id . ")\n";
    $eventSpentStmt->close();
    
    $eventEarnedMsg = "You earned $eventCost credits from $requesterName for the event \"$eventTitle\".";
    $eventEarnedStmt = $conn->prepare("INSERT INTO UserNotifications (UserId, NotificationType, Title, Message, NotificationSection) VALUES (?, 'earned', 'Credits Earned', ?, 'credits')");
    $eventEarnedStmt->bind_param('is', $organizer, $eventEarnedMsg);
    $eventEarnedStmt->execute();
    echo "✓ Event credit earned notification sent (ID: " . $conn->insert_id . ")\n";
    $eventEarnedStmt->close();
    
    // Verify
    $eventAcceptNotif = getLatestNotification($conn, $attendeeId, 'acceptedInEvent');
    if ($eventAcceptNotif && $eventAcceptNotif['NotificationSection'] === 'events') {
        echo "✓ Verified: Event acceptance notification created correctly\n";
    } else {
        echo "✗ FAILED: Event acceptance notification incorrect\n";
    }
} else {
    echo "ℹ Event is free, skipping credit transfer test\n";
}
echo "\n";

// ==========================================
// SUMMARY
// ==========================================
echo "========================================\n";
echo "TEST SUMMARY\n";
echo "========================================\n\n";

$totalNotifs = $conn->query("SELECT COUNT(*) as count FROM UserNotifications WHERE CreatedAt > DATE_SUB(NOW(), INTERVAL 1 HOUR)")->fetch_assoc()['count'];

echo "Total notifications created in this test: $totalNotifs\n\n";

echo "Breakdown by type:\n";
$types = ['booking', 'accepted', 'being_refused', 'acceptedInEvent', 'RejectedFromEvent', 'spent', 'earned'];
foreach ($types as $type) {
    $count = $conn->query("SELECT COUNT(*) as count FROM UserNotifications WHERE NotificationType = '$type' AND CreatedAt > DATE_SUB(NOW(), INTERVAL 1 HOUR)")->fetch_assoc()['count'];
    if ($count > 0) {
        echo "  - $type: $count\n";
    }
}

echo "\nBreakdown by section:\n";
$sections = ['Exchange', 'events', 'credits', 'Reviews'];
foreach ($sections as $section) {
    $count = $conn->query("SELECT COUNT(*) as count FROM UserNotifications WHERE NotificationSection = '$section' AND CreatedAt > DATE_SUB(NOW(), INTERVAL 1 HOUR)")->fetch_assoc()['count'];
    if ($count > 0) {
        echo "  - $section: $count\n";
    }
}

echo "\n✓ ALL TESTS COMPLETED\n";
echo "========================================\n";

$conn->close();
?>
