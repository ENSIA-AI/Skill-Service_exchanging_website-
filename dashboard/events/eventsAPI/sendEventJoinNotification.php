<?php
/**
 * Send Event Join Request Notification API
 * Creates a notification for the event organizer when someone requests to join their event
 * Endpoint: sendEventJoinNotification.php
 * Method: POST
 * Required POST data: eventId
 */

// Start session to get current user
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'You must be logged in to join an event',
        'redirect' => '../../auth/login.php'
    ]);
    exit;
}

$senderId = $_SESSION['user_id'];
$senderUsername = $_SESSION['username'] ?? 'A user';

// Include database configuration
$configPath = __DIR__ . '/../../../DataBaseManagement/config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Config file not found']);
    exit;
}

require_once $configPath;

// Check database connection
if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$eventId = isset($input['eventId']) ? (int)$input['eventId'] : 0;

if ($eventId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid event ID']);
    exit;
}

try {
    // Get event details and organizer info
    $eventQuery = "
        SELECT 
            e.EventId,
            e.EventTitle,
            e.OrganizerId,
            u.UserName as organizerUsername
        FROM Events e
        LEFT JOIN Users u ON e.OrganizerId = u.UserId
        WHERE e.EventId = ?
    ";
    
    $stmt = $conn->prepare($eventQuery);
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Event not found']);
        $stmt->close();
        exit;
    }
    
    $event = $result->fetch_assoc();
    $stmt->close();
    
    $recipientId = $event['OrganizerId'];
    $eventTitle = $event['EventTitle'];
    
    // Check if user is trying to join their own event
    if ($senderId == $recipientId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'You cannot join your own event']);
        exit;
    }
    
    // Check if user already has an attendee record (registered/pending/confirmed)
    $checkAttendeeQuery = "
        SELECT Status FROM EventsAttendees 
        WHERE EventId = ? AND UserId = ?
    ";
    $checkAttendeeStmt = $conn->prepare($checkAttendeeQuery);
    $checkAttendeeStmt->bind_param('ii', $eventId, $senderId);
    $checkAttendeeStmt->execute();
    $checkAttendeeResult = $checkAttendeeStmt->get_result();
    
    if ($checkAttendeeResult->num_rows > 0) {
        $existingStatus = $checkAttendeeResult->fetch_assoc()['Status'];
        $checkAttendeeStmt->close();
        
        if ($existingStatus === 'confirmed') {
            echo json_encode(['success' => false, 'error' => 'You are already confirmed for this event']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Request already sent']);
        }
        exit;
    }
    $checkAttendeeStmt->close();
    
    // Format request date
    $requestDate = date('F d, Y');
    
    // Create notification message following the exact template
    // "username has requested to join your event eventname on requestdate"
    $message = "$senderUsername has requested to join your event $eventTitle on $requestDate";
    $title = "Event Join Request";
    
    // Insert notification into database
    // Using 'eventJoinRequest' type which auto-corrects to 'events' section
    $insertQuery = "
        INSERT INTO UserNotifications 
        (SenderId, RecipientId, EventId, AttendeeId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection)
        VALUES (?, ?, ?, ?, 'eventJoinRequest', ?, ?, 'no', NOW(), 'events')
    ";
    
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param('iiiiss', $senderId, $recipientId, $eventId, $senderId, $title, $message);
    
    if ($insertStmt->execute()) {
        $notificationId = $conn->insert_id;
        $insertStmt->close();
        
        // Also insert into EventsAttendees with 'registered' status (awaiting organizer approval)
        $attendeeInsert = "INSERT INTO EventsAttendees (EventId, UserId, Status, RegisteredAt) VALUES (?, ?, 'registered', NOW())";
        $attendeeStmt = $conn->prepare($attendeeInsert);
        $attendeeStmt->bind_param('ii', $eventId, $senderId);
        
        if (!$attendeeStmt->execute()) {
            error_log("Failed to create EventsAttendees record: " . $attendeeStmt->error);
        }
        $attendeeStmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Join request sent successfully',
            'notificationId' => $notificationId
        ]);
    } else {
        throw new Exception("Failed to create notification: " . $insertStmt->error);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}

// Close connection
if (isset($conn) && !is_null($conn)) {
    $conn->close();
}
?>
