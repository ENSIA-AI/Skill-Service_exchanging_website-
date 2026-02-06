<?php
/**
 * Cancel Event Join Request API
 * Deletes the notification when user unsends their join request
 * Endpoint: cancelEventJoinRequest.php
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
        'error' => 'You must be logged in to cancel a request',
        'redirect' => '../../auth/login.php'
    ]);
    exit;
}

$senderId = $_SESSION['user_id'];

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
    // Get event details to find the organizer (recipient of the notification)
    $eventQuery = "SELECT EventId, EventTitle, OrganizerId FROM events WHERE EventId = ?";
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
    
    // Delete the notification for this event join request
    // Match: SenderId = current user, RecipientId = organizer, Message contains event title
    $deleteQuery = "
        DELETE FROM UserNotifications 
        WHERE SenderId = ? 
        AND RecipientId = ? 
        AND NotificationType = 'booking' 
        AND (Message LIKE ? OR Message LIKE ?)
    ";
    
    $deleteStmt = $conn->prepare($deleteQuery);
    $likePattern1 = "%join your event $eventTitle%";
    $likePattern2 = "%join your event \"$eventTitle\"%";
    $deleteStmt->bind_param('iiss', $senderId, $recipientId, $likePattern1, $likePattern2);
    
    if ($deleteStmt->execute()) {
        $affectedRows = $deleteStmt->affected_rows;
        $deleteStmt->close();
        
        if ($affectedRows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Join request cancelled successfully',
                'deletedCount' => $affectedRows
            ]);
        } else {
            // No notification found, but still return success (request was already cancelled or never sent)
            echo json_encode([
                'success' => true,
                'message' => 'No pending request found',
                'deletedCount' => 0
            ]);
        }
    } else {
        throw new Exception("Failed to cancel request: " . $deleteStmt->error);
    }
    
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
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
