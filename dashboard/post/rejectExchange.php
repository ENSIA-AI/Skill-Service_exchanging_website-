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
require_once './postnotification.php';

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
    if (!isset($_SESSION['userId'])) {
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
    $userId = $_SESSION['userId'];
    
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
    
    // Send rejection notification to the requester
    $notifResult = sendRejectionNotification(
        $exchange['OfferedByUserId'],
        $exchange['RequestedByUserId'],
        $exchange['Title'],
        $exchange['FullName']
    );
    
    if (!$notifResult['success']) {
        // Still return success for exchange update, but log the notification error
        error_log('Notification error: ' . ($notifResult['error'] ?? 'Unknown'));
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true, 
        'message' => 'Exchange rejected successfully',
        'notificationSent' => $notifResult['success']
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
