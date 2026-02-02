<?php
/**
 * Reject Exchange Endpoint
 * Location: /dashboard/post/rejectExchange.php
 * 
 * Handles POST requests to reject a booking/exchange request
 * Updates exchange status to 'rejected' and sends notification to requester
 */

session_start();
require_once '../../DataBaseManagement/config.php';

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
    
    // Validate exchangeId parameter
    if (!isset($_POST['exchangeId']) || !is_numeric($_POST['exchangeId'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid exchange ID']);
        exit;
    }
    
    $exchangeId = (int)$_POST['exchangeId'];
    $userId = $_SESSION['user_id'];
    
    // Get exchange details with post and user info
    $sql = "SELECT e.*, p.Title, u.FullName 
            FROM Exchanges e
            JOIN Posts p ON e.PostId = p.PostId
            JOIN Users u ON e.OfferedByUserId = u.UserId
            WHERE e.ExchangeId = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $stmt->bind_param('i', $exchangeId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Exchange not found']);
        $stmt->close();
        exit;
    }
    
    $exchange = $result->fetch_assoc();
    $stmt->close();
    
    // Verify user is the post owner (OfferedByUserId)
    if ($userId != $exchange['OfferedByUserId']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized - only post owner can reject']);
        exit;
    }
    
    // Check if exchange is still pending
    if ($exchange['Status'] !== 'pending') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Exchange is not in pending status']);
        exit;
    }
    
    // Update exchange status to 'rejected'
    $updateSql = "UPDATE Exchanges SET Status = 'rejected' WHERE ExchangeId = ?";
    $updateStmt = $conn->prepare($updateSql);
    
    if (!$updateStmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $updateStmt->bind_param('i', $exchangeId);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to update exchange: ' . $updateStmt->error);
    }
    
    $updateStmt->close();
    
    // Create and insert rejection notification (inline - like eventdetails)
    $message = $exchange['FullName'] . " has rejected your booking request for " . $exchange['Title'];
    $title = "Booking Rejected";
    
    $notificationSQL = "INSERT INTO UserNotifications 
                       (UserId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection)
                       VALUES (?, 'being_refused', ?, ?, 'no', NOW(), 'Exchange')";
    
    $notifStmt = $conn->prepare($notificationSQL);
    if (!$notifStmt) {
        throw new Exception('Notification prepare failed: ' . $conn->error);
    }
    
    $notifStmt->bind_param('iss', $exchange['RequestedByUserId'], $title, $message);
    
    if (!$notifStmt->execute()) {
        throw new Exception('Failed to create notification: ' . $notifStmt->error);
    }
    
    $notificationId = $conn->insert_id;
    $notifStmt->close();
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true, 
        'message' => 'Exchange rejected successfully',
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
