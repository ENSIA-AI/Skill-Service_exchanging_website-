<?php
/**
 * Reject Event Join Request Endpoint
 * Location: /dashboard/events/eventsAPI/rejectEventAttendee.php
 * 
 * Handles POST requests to reject an event join request
 * - Updates EventsAttendees status to 'cancelled' OR deletes the record
 * - Sends 'RejectedFromEvent' notification to requester
 */

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
                        e.OrganizerId
                      FROM events e
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
        echo json_encode(['success' => false, 'error' => 'You do not have permission to reject attendees for this event']);
        $eventStmt->close();
        exit;
    }
    
    $event = $eventResult->fetch_assoc();
    $eventStmt->close();
    
    $eventTitle = $event['EventTitle'];
    
    // Get attendee details
    $attendeeSql = "SELECT 
                        ea.EventId, 
                        ea.UserId, 
                        ea.Status,
                        u.FullName as AttendeeName
                    FROM eventsattendees ea
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
    $currentStatus = $attendee['Status'];
    
    // Check if already confirmed (cannot reject confirmed attendees)
    if ($currentStatus === 'confirmed') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Cannot reject an already confirmed attendee']);
        exit;
    }
    
    // Update attendee status to cancelled
    $updateStatusSql = "UPDATE EventsAttendees 
                       SET Status = 'cancelled' 
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
    
    // Update the original eventJoinRequest notification to mark it as declined
    // This prevents the Accept/Decline buttons from showing again
    $updateNotifSql = "UPDATE UserNotifications SET NotificationType = 'eventJoinDeclined' WHERE EventId = ? AND AttendeeId = ? AND NotificationType = 'eventJoinRequest'";
    $updateNotifStmt = $conn->prepare($updateNotifSql);
    if ($updateNotifStmt) {
        $updateNotifStmt->bind_param('ii', $eventId, $attendeeId);
        $updateNotifStmt->execute();
        $updateNotifStmt->close();
    }
    
    // Get organizer name for notification
    $organizerNameSql = "SELECT FullName FROM users WHERE UserId = ?";
    $orgNameStmt = $conn->prepare($organizerNameSql);
    $orgNameStmt->bind_param('i', $organizerId);
    $orgNameStmt->execute();
    $organizerName = $orgNameStmt->get_result()->fetch_assoc()['FullName'];
    $orgNameStmt->close();
    
    // Insert RejectedFromEvent notification for attendee
    try {
        $notifSql = "INSERT INTO UserNotifications 
                    (RecipientId, SenderId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection) 
                    VALUES (?, ?, 'RejectedFromEvent', ?, ?, 'no', NOW(), 'events')";
        $notifStmt = $conn->prepare($notifSql);
        
        if (!$notifStmt) {
            throw new Exception('Notification prepare failed: ' . $conn->error);
        }
        
        $notifTitle = "Event Request Declined";
        $notifMessage = "$organizerName has declined your request to join \"$eventTitle\".";
        $notifStmt->bind_param('iiss', $attendeeId, $organizerId, $notifTitle, $notifMessage);
        
        if (!$notifStmt->execute()) {
            throw new Exception('Failed to create notification: ' . $notifStmt->error);
        }
        
        $notificationId = $conn->insert_id;
        $notifStmt->close();
    } catch (mysqli_sql_exception $e) {
        error_log("Failed to create 'RejectedFromEvent' notification: " . $e->getMessage());
        $notificationId = 0;
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Event attendee rejected successfully',
        'notificationId' => $notificationId
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
