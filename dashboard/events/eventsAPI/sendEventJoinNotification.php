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
    
    // Check if notification already exists (prevent duplicate requests)
    $checkQuery = "
        SELECT NotificationId FROM UserNotifications 
        WHERE SenderId = ? AND RecipientId = ? AND NotificationType = 'booking' 
        AND NotificationSection = 'Exchange' AND Message LIKE ?
    ";
    $checkStmt = $conn->prepare($checkQuery);
    $likePattern = "%join your event " . $eventTitle . "%";
    $checkStmt->bind_param('iis', $senderId, $recipientId, $likePattern);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $checkStmt->close();
        echo json_encode(['success' => true, 'message' => 'Request already sent']);
        exit;
    }
    $checkStmt->close();
    
    // Format request date
    $requestDate = date('F d, Y');
    
    // Create notification message following the exact template
    // "username has requested to join your event eventname on requestdate"
    $message = "$senderUsername has requested to join your event $eventTitle on $requestDate";
    $title = "Event Join Request";
    
    // Insert notification into database
    // Note: 'booking' type must be in 'Exchange' section per database trigger rules
    $insertQuery = "
        INSERT INTO UserNotifications 
        (SenderId, RecipientId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection)
        VALUES (?, ?, 'booking', ?, ?, 'no', NOW(), 'Exchange')
    ";
    
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param('iiss', $senderId, $recipientId, $title, $message);
    
    if ($insertStmt->execute()) {
        $notificationId = $conn->insert_id;
        $insertStmt->close();
        
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
