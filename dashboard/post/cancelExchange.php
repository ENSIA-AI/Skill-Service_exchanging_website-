<?php
/**
 * Cancel Exchange Request Handler
 * Deletes a pending exchange request and its associated notification
 */

session_start();
require_once '../../DataBaseManagement/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Check if this is a POST request with an exchangeId
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['exchangeId'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

$exchangeId = (int)$_POST['exchangeId'];
$userId = $_SESSION['user_id'];

try {
    // First, verify that this exchange belongs to the current user and is still pending
    $checkSql = "SELECT e.ExchangeId, e.PostId, e.RequestedByUserId, e.OfferedByUserId, e.Status, e.ProposedDate, p.Title
                 FROM exchanges e
                 JOIN Posts p ON e.PostId = p.PostId
                 WHERE e.ExchangeId = ? AND e.RequestedByUserId = ? AND e.Status = 'pending'";
    $checkStmt = $conn->prepare($checkSql);
    
    if (!$checkStmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $checkStmt->bind_param('ii', $exchangeId, $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        $checkStmt->close();
        echo json_encode(['success' => false, 'error' => 'Exchange not found or cannot be cancelled']);
        exit();
    }
    
    $exchange = $result->fetch_assoc();
    $postOwnerId = $exchange['OfferedByUserId'];
    $postTitle = $exchange['Title'];
    $checkStmt->close();
    
    // Start transaction
    $conn->begin_transaction();
    
    // Delete the notification associated with this booking request
    // Find notification by matching the sender (requester), recipient (post owner), and type
    $deleteNotifSql = "DELETE FROM usernotifications 
                       WHERE SenderId = ? 
                       AND RecipientId = ? 
                       AND NotificationType = 'booking'
                       AND NotificationSection = 'Exchange'
                       ORDER BY CreatedAt DESC
                       LIMIT 1";
    $deleteNotifStmt = $conn->prepare($deleteNotifSql);
    
    if (!$deleteNotifStmt) {
        throw new Exception('Failed to prepare notification delete: ' . $conn->error);
    }
    
    $deleteNotifStmt->bind_param('ii', $userId, $postOwnerId);
    if (!$deleteNotifStmt->execute()) {
        throw new Exception('Failed to delete notification: ' . $deleteNotifStmt->error);
    }
    $notifDeleted = $deleteNotifStmt->affected_rows;
    $deleteNotifStmt->close();
    
    // Delete the exchange record
    $deleteExchangeSql = "DELETE FROM exchanges WHERE ExchangeId = ?";
    $deleteExchangeStmt = $conn->prepare($deleteExchangeSql);
    
    if (!$deleteExchangeStmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $deleteExchangeStmt->bind_param('i', $exchangeId);
    
    if ($deleteExchangeStmt->execute()) {
        $exchangeDeleted = $deleteExchangeStmt->affected_rows;
        $deleteExchangeStmt->close();
        $conn->commit();
        echo json_encode([
            'success' => true, 
            'message' => 'Booking request cancelled successfully',
            'debug' => [
                'notificationDeleted' => $notifDeleted,
                'exchangeDeleted' => $exchangeDeleted
            ]
        ]);
    } else {
        throw new Exception('Failed to delete exchange: ' . $deleteExchangeStmt->error);
    }
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>
