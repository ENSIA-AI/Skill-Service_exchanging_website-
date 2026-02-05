<?php
/**
 * Accept Event Join Request Endpoint
 * Location: /dashboard/events/eventsAPI/acceptEventAttendee.php
 * 
 * Handles POST requests to accept an event join request
 * - Updates EventsAttendees status to 'confirmed'
 * - Deducts credits from attendee if event has a cost
 * - Adds credits to event organizer if event has a cost
 * - Sends 'acceptedInEvent' notification to requester
 * - Sends 'earned' credit notification to organizer if applicable
 */

// Enable error reporting but capture as JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Custom error handler to return JSON errors
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

session_start();
require_once '../../../DataBaseManagement/config.php';

// Set response header
header('Content-Type: application/json');

try {
    // Check if POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit;
    }
    
    $organizerId = $_SESSION['user_id'];
    
    // Get POST data - support both JSON and form data
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input) {
        $eventId = isset($input['eventId']) ? (int)$input['eventId'] : 0;
        $attendeeId = isset($input['attendeeId']) ? (int)$input['attendeeId'] : 0;
    } else {
        // Fallback to form data (FormData from JavaScript)
        $eventId = isset($_POST['eventId']) ? (int)$_POST['eventId'] : 0;
        $attendeeId = isset($_POST['attendeeId']) ? (int)$_POST['attendeeId'] : 0;
    }
    
    // Validate parameters
    if ($eventId <= 0 || $attendeeId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid event ID or attendee ID']);
        exit;
    }
    
    // Verify organizer owns this event
    $eventCheckSql = "SELECT 
                        e.EventId, 
                        e.EventTitle, 
                        e.OrganizerId, 
                        e.EventCost,
                        e.MaxAttendees,
                        e.CurrentAttendeesNumber
                      FROM Events e
                      WHERE e.EventId = ? AND e.OrganizerId = ?";
    $eventStmt = $conn->prepare($eventCheckSql);
    
    if (!$eventStmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $eventStmt->bind_param('ii', $eventId, $organizerId);
    
    if (!$eventStmt->execute()) {
        throw new Exception('Failed to verify event ownership');
    }
    
    $eventResult = $eventStmt->get_result();
    
    if ($eventResult->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'You do not have permission to accept attendees for this event']);
        $eventStmt->close();
        exit;
    }
    
    $event = $eventResult->fetch_assoc();
    $eventStmt->close();
    
    $eventTitle = $event['EventTitle'];
    $eventCost = (int)$event['EventCost'];
    $maxAttendees = (int)$event['MaxAttendees'];
    $currentAttendees = (int)$event['CurrentAttendeesNumber'];
    
    // Check if event is full
    if ($currentAttendees >= $maxAttendees) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Event is full']);
        exit;
    }
    
    // Get attendee details
    $attendeeSql = "SELECT 
                        ea.EventId, 
                        ea.UserId, 
                        ea.Status,
                        u.FullName as AttendeeName,
                        u.CreditBalance
                    FROM EventsAttendees ea
                    LEFT JOIN Users u ON ea.UserId = u.UserId
                    WHERE ea.EventId = ? AND ea.UserId = ?";
    $attendeeStmt = $conn->prepare($attendeeSql);
    
    if (!$attendeeStmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $attendeeStmt->bind_param('ii', $eventId, $attendeeId);
    
    if (!$attendeeStmt->execute()) {
        throw new Exception('Failed to fetch attendee details');
    }
    
    $attendeeResult = $attendeeStmt->get_result();
    
    if ($attendeeResult->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Attendee not found for this event']);
        $attendeeStmt->close();
        exit;
    }
    
    $attendee = $attendeeResult->fetch_assoc();
    $attendeeStmt->close();
    
    $attendeeName = $attendee['AttendeeName'];
    $attendeeBalance = (int)$attendee['CreditBalance'];
    $currentStatus = $attendee['Status'];
    
    // Check if already confirmed
    if ($currentStatus === 'confirmed') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Attendee already confirmed']);
        exit;
    }
    
    // If event has a cost, verify attendee has sufficient balance
    if ($eventCost > 0) {
        if ($attendeeBalance < $eventCost) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'error' => 'Attendee has insufficient credits. They need ' . $eventCost . ' credits but only have ' . $attendeeBalance . '.'
            ]);
            exit;
        }
    }
    
    // Start transaction for credit transfer and status update
    $conn->begin_transaction();
    
    try {
        // Update attendee status to confirmed
        $updateStatusSql = "UPDATE EventsAttendees 
                           SET Status = 'confirmed', ConfirmedAt = NOW() 
                           WHERE EventId = ? AND UserId = ?";
        $updateStmt = $conn->prepare($updateStatusSql);
        
        if (!$updateStmt) {
            throw new Exception('Database error: ' . $conn->error);
        }
        
        $updateStmt->bind_param('ii', $eventId, $attendeeId);
        
        if (!$updateStmt->execute()) {
            throw new Exception('Failed to update attendee status');
        }
        
        $updateStmt->close();
        
        // Increment CurrentAttendeesNumber
        $incrementSql = "UPDATE Events 
                        SET CurrentAttendeesNumber = CurrentAttendeesNumber + 1 
                        WHERE EventId = ?";
        $incrementStmt = $conn->prepare($incrementSql);
        $incrementStmt->bind_param('i', $eventId);
        $incrementStmt->execute();
        $incrementStmt->close();
        
        // If event has a cost, handle credit transfer
        $attendeeNewBalance = $attendeeBalance;
        $organizerNewBalance = null;
        
        if ($eventCost > 0) {
            // Deduct credits from attendee
            $deductSql = "UPDATE Users 
                         SET CreditBalance = CreditBalance - ? 
                         WHERE UserId = ?";
            $deductStmt = $conn->prepare($deductSql);
            $deductStmt->bind_param('ii', $eventCost, $attendeeId);
            
            if (!$deductStmt->execute() || $deductStmt->affected_rows !== 1) {
                throw new Exception('Failed to deduct credits from attendee');
            }
            
            $deductStmt->close();
            
            // Get attendee's new balance
            $attendeeNewBalance = $attendeeBalance - $eventCost;
            
            // Add credits to organizer
            $addCreditsSql = "UPDATE Users 
                             SET CreditBalance = CreditBalance + ? 
                             WHERE UserId = ?";
            $addCreditsStmt = $conn->prepare($addCreditsSql);
            $addCreditsStmt->bind_param('ii', $eventCost, $organizerId);
            
            if (!$addCreditsStmt->execute() || $addCreditsStmt->affected_rows !== 1) {
                throw new Exception('Failed to add credits to organizer');
            }
            
            $addCreditsStmt->close();
            
            // Get organizer's new balance
            $balSql = "SELECT CreditBalance FROM Users WHERE UserId = ?";
            $balStmt = $conn->prepare($balSql);
            $balStmt->bind_param('i', $organizerId);
            $balStmt->execute();
            $organizerNewBalance = (int)$balStmt->get_result()->fetch_assoc()['CreditBalance'];
            $balStmt->close();
            
            // Insert CreditTransaction for attendee (spent)
            $ctSpentSql = "INSERT INTO CreditTransactions 
                          (UserId, TransactionType, Amount, BalanceAfter, RelatedEntityType, RelatedEntityId, Description) 
                          VALUES (?, 'spent', ?, ?, 'event', ?, ?)";
            $ctSpentStmt = $conn->prepare($ctSpentSql);
            $spentDesc = "Event: $eventTitle";
            $ctSpentStmt->bind_param('iiiis', $attendeeId, $eventCost, $attendeeNewBalance, $eventId, $spentDesc);
            $ctSpentStmt->execute();
            $ctSpentStmt->close();
            
            // Insert CreditTransaction for organizer (earned)
            $ctEarnedSql = "INSERT INTO CreditTransactions 
                           (UserId, TransactionType, Amount, BalanceAfter, RelatedEntityType, RelatedEntityId, Description) 
                           VALUES (?, 'earned', ?, ?, 'event', ?, ?)";
            $ctEarnedStmt = $conn->prepare($ctEarnedSql);
            $earnedDesc = "Event: $eventTitle from $attendeeName";
            $ctEarnedStmt->bind_param('iiiis', $organizerId, $eventCost, $organizerNewBalance, $eventId, $earnedDesc);
            $ctEarnedStmt->execute();
            $ctEarnedStmt->close();
            
            // Insert credit spent notification for attendee
            try {
                $notifSpentSql = "INSERT INTO UserNotifications 
                                 (RecipientId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection) 
                                 VALUES (?, 'spent', ?, ?, 'no', NOW(), 'credits')";
                $nsStmt = $conn->prepare($notifSpentSql);
                $spentTitle = "Credits Spent";
                $spentMsg = "You spent $eventCost credits for the event \"$eventTitle\".";
                $nsStmt->bind_param('iss', $attendeeId, $spentTitle, $spentMsg);
                $nsStmt->execute();
                $nsStmt->close();
            } catch (mysqli_sql_exception $e) {
                error_log("Failed to create 'spent' notification for event: " . $e->getMessage());
            }
            
            // Insert credit earned notification for organizer
            try {
                $notifEarnedSql = "INSERT INTO UserNotifications 
                                  (RecipientId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection) 
                                  VALUES (?, 'earned', ?, ?, 'no', NOW(), 'credits')";
                $neStmt = $conn->prepare($notifEarnedSql);
                $earnedTitle = "Credits Earned";
                $earnedMsg = "You earned $eventCost credits from $attendeeName for the event \"$eventTitle\".";
                $neStmt->bind_param('iss', $organizerId, $earnedTitle, $earnedMsg);
                $neStmt->execute();
                $neStmt->close();
            } catch (mysqli_sql_exception $e) {
                error_log("Failed to create 'earned' notification for event: " . $e->getMessage());
            }
        }
        
        // Update the original eventJoinRequest notification to mark it as processed
        // This prevents the Accept/Decline buttons from showing again
        $updateNotifSql = "UPDATE UserNotifications SET NotificationType = 'eventJoinAccepted' WHERE EventId = ? AND AttendeeId = ? AND NotificationType = 'eventJoinRequest'";
        $updateNotifStmt = $conn->prepare($updateNotifSql);
        if ($updateNotifStmt) {
            $updateNotifStmt->bind_param('ii', $eventId, $attendeeId);
            $updateNotifStmt->execute();
            $updateNotifStmt->close();
        }
        
        // Get organizer name for notification
        $organizerNameSql = "SELECT FullName FROM Users WHERE UserId = ?";
        $orgNameStmt = $conn->prepare($organizerNameSql);
        $orgNameStmt->bind_param('i', $organizerId);
        $orgNameStmt->execute();
        $organizerName = $orgNameStmt->get_result()->fetch_assoc()['FullName'];
        $orgNameStmt->close();
        
        // Insert acceptedInEvent notification for attendee
        try {
            $notifSql = "INSERT INTO UserNotifications 
                        (RecipientId, SenderId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection) 
                        VALUES (?, ?, 'acceptedInEvent', ?, ?, 'no', NOW(), 'events')";
            $notifStmt = $conn->prepare($notifSql);
            
            if (!$notifStmt) {
                throw new Exception('Notification prepare failed: ' . $conn->error);
            }
            
            $notifTitle = "Event Request Accepted";
            $notifMessage = "$organizerName has accepted your request to join \"$eventTitle\".";
            $notifStmt->bind_param('iiss', $attendeeId, $organizerId, $notifTitle, $notifMessage);
            
            if (!$notifStmt->execute()) {
                throw new Exception('Failed to create notification: ' . $notifStmt->error);
            }
            
            $notificationId = $conn->insert_id;
            $notifStmt->close();
        } catch (mysqli_sql_exception $e) {
            error_log("Failed to create 'acceptedInEvent' notification: " . $e->getMessage());
            $notificationId = 0;
        }
        
        // Commit transaction
        $conn->commit();
        
        // Return success response
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Event attendee accepted successfully',
            'notificationId' => $notificationId,
            'organizerBalance' => $organizerNewBalance,
            'attendeeBalance' => $attendeeNewBalance,
            'eventCost' => $eventCost
        ]);
        
    } catch (Exception $ex) {
        $conn->rollback();
        throw $ex;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
