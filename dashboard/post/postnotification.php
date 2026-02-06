<?php
/**
 * Post Notification Handler
 * Manages notifications for booking requests, acceptances, and rejections
 */

require_once '../../DataBaseManagement/config.php';

/**
 * Send booking notification when user clicks "Book This Service"
 * Notification Type: 'booking'
 * @param int $postId - The post being booked
 * @param int $postOwnerId - User who posted the service
 * @param int $requesterId - User requesting the service
 * @param string $postTitle - Title of the post
 * @param string $requesterName - Name of the user requesting
 * @param string $paymentMethod - 'exchange' or 'credit'
 * @param int $requiredCredits - Credits amount if applicable
 * @param string $timeSlotDisplay - Formatted time slot (e.g., "10:00 to 11:00 on Feb 10, 2026")
 */
function sendBookingNotification($postId, $postOwnerId, $requesterId, $postTitle, $requesterName, $paymentMethod, $requiredCredits, $timeSlotDisplay = '') {
    global $conn;
    
    // Create notification message based on payment method
    if ($paymentMethod === 'exchange') {
        if (!empty($timeSlotDisplay)) {
            $notificationMessage = "$requesterName has requested to exchange skills for your \"$postTitle\" session ($timeSlotDisplay)";
        } else {
            $notificationMessage = "$requesterName has requested to exchange skills for your \"$postTitle\" session";
        }
        $notificationTitle = 'Skill Exchange Request';
    } else {
        // Credits payment
        if (!empty($timeSlotDisplay)) {
            $notificationMessage = "$requesterName has requested to book your \"$postTitle\" session for $requiredCredits credits ($timeSlotDisplay)";
        } else {
            $notificationMessage = "$requesterName has requested to book your \"$postTitle\" session for $requiredCredits credits";
        }
        $notificationTitle = 'Booking Request';
    }
    
    $notificationType = 'booking';
    $notificationSection = 'Exchange';
    
    // Insert notification to post owner (using RecipientId per migration script)
    $sqlNotification = "INSERT INTO usernotifications (RecipientId, SenderId, NotificationType, Title, Message, NotificationSection) VALUES (?, ?, ?, ?, ?, ?)";
    $stmtNotification = $conn->prepare($sqlNotification);
    
    if (!$stmtNotification) {
        return ['success' => false, 'error' => 'Database error: ' . $conn->error];
    }
    
    $stmtNotification->bind_param('iissss', $postOwnerId, $requesterId, $notificationType, $notificationTitle, $notificationMessage, $notificationSection);
    
    if ($stmtNotification->execute()) {
        $stmtNotification->close();
        return ['success' => true];
    } else {
        $error = 'Error creating notification: ' . $stmtNotification->error;
        $stmtNotification->close();
        return ['success' => false, 'error' => $error];
    }
}

/**
 * Send acceptance notification when post owner accepts the booking
 * Notification Type: 'accepted'
 * @param int $postOwnerId - User who posted the service
 * @param int $requesterId - User whose request was accepted
 * @param int $exchangeId - The exchange ID
 * @param string $postTitle - Title of the post
 * @param string $postOwnerName - Name of the post owner
 */
function sendAcceptanceNotification($postOwnerId, $requesterId, $exchangeId, $postTitle, $postOwnerName) {
    global $conn;
    
    $notificationType = 'accepted';
    $notificationTitle = 'Booking Accepted';
    $notificationMessage = "$postOwnerName has accepted your booking request for $postTitle";
    $notificationSection = 'Exchange';
    
    // Insert notification to requester (using RecipientId per migration script)
    $sqlNotification = "INSERT INTO usernotifications (RecipientId, SenderId, NotificationType, Title, Message, NotificationSection) VALUES (?, ?, ?, ?, ?, ?)";
    $stmtNotification = $conn->prepare($sqlNotification);
    
    if (!$stmtNotification) {
        return ['success' => false, 'error' => 'Database error: ' . $conn->error];
    }
    
    $stmtNotification->bind_param('iissss', $requesterId, $postOwnerId, $notificationType, $notificationTitle, $notificationMessage, $notificationSection);
    
    if ($stmtNotification->execute()) {
        $stmtNotification->close();
        return ['success' => true];
    } else {
        $error = 'Error creating notification: ' . $stmtNotification->error;
        $stmtNotification->close();
        return ['success' => false, 'error' => $error];
    }
}

/**
 * Send rejection notification when post owner rejects the booking
 * Notification Type: 'being_refused'
 * @param int $postOwnerId - User who posted the service
 * @param int $requesterId - User whose request was rejected
 * @param string $postTitle - Title of the post
 * @param string $postOwnerName - Name of the post owner
 */
function sendRejectionNotification($postOwnerId, $requesterId, $postTitle, $postOwnerName) {
    global $conn;
    
    $notificationType = 'being_refused';
    $notificationTitle = 'Booking Rejected';
    $notificationMessage = "$postOwnerName has rejected your booking request for $postTitle";
    $notificationSection = 'Exchange';
    
    // Insert notification to requester (using RecipientId per migration script)
    $sqlNotification = "INSERT INTO usernotifications (RecipientId, SenderId, NotificationType, Title, Message, NotificationSection) VALUES (?, ?, ?, ?, ?, ?)";
    $stmtNotification = $conn->prepare($sqlNotification);
    
    if (!$stmtNotification) {
        return ['success' => false, 'error' => 'Database error: ' . $conn->error];
    }
    
    $stmtNotification->bind_param('iissss', $requesterId, $postOwnerId, $notificationType, $notificationTitle, $notificationMessage, $notificationSection);
    
    if ($stmtNotification->execute()) {
        $stmtNotification->close();
        return ['success' => true];
    } else {
        $error = 'Error creating notification: ' . $stmtNotification->error;
        $stmtNotification->close();
        return ['success' => false, 'error' => $error];
    }
}

/**
 * Get booking status for a specific exchange
 * Returns the status: 'pending', 'accepted', 'rejected'
 * @param int $exchangeId - The exchange ID
 */
function getBookingStatus($exchangeId) {
    global $conn;
    
    $sql = "SELECT Status FROM exchanges WHERE ExchangeId = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param('i', $exchangeId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['Status'];
    }
    
    $stmt->close();
    return null;
}

/**
 * Get all exchanges for a specific post and current user
 * @param int $postId - The post ID
 * @param int $userId - The user ID (logged-in user)
 */
function getUserExchangesForPost($postId, $userId) {
    global $conn;
    
    $sql = "SELECT ExchangeId, Status FROM exchanges 
            WHERE PostId = ? AND RequestedByUserId = ?
            ORDER BY CreatedAt DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param('ii', $postId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }
    
    $stmt->close();
    return null;
}

/**
 * Get button display text and state based on exchange status
 * @param string $status - Exchange status: 'pending', 'accepted', 'rejected'
 */
function getButtonState($status) {
    $states = [
        'pending' => [
            'text' => 'Request Sent',
            'class' => 'btn-pending',
            'disabled' => true
        ],
        'accepted' => [
            'text' => 'Request Accepted',
            'class' => 'btn-accepted',
            'disabled' => true
        ],
        'rejected' => [
            'text' => 'Request Refused',
            'class' => 'btn-refused',
            'disabled' => true
        ]
    ];
    
    return isset($states[$status]) ? $states[$status] : null;
}

/**
 * API ENDPOINT: Handle exchange acceptance
 * 
 * USAGE: Create a file like "acceptExchange.php" in the post folder
 * POST Parameters: exchangeId (int)
 * 
 * Example code for acceptExchange.php:
 * <?php
 * session_start();
 * require_once '../../DataBaseManagement/config.php';
 * require_once './postnotification.php';
 * 
 * if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exchangeId'])) {
 *     $exchangeId = (int)$_POST['exchangeId'];
 *     $userId = $_SESSION['userId'] ?? null;
 *     
 *     // Get exchange details
 *     $sql = "SELECT e.*, p.Title, u.FullName FROM Exchanges e
 *             JOIN Posts p ON e.PostId = p.PostId
 *             JOIN Users u ON e.OfferedByUserId = u.UserId
 *             WHERE e.ExchangeId = ?";
 *     $stmt = $conn->prepare($sql);
 *     $stmt->bind_param('i', $exchangeId);
 *     $stmt->execute();
 *     $result = $stmt->get_result();
 *     
 *     if ($result->num_rows > 0) {
 *         $exchange = $result->fetch_assoc();
 *         
 *         // Verify user is the post owner
 *         if ($userId == $exchange['OfferedByUserId']) {
 *             // Update exchange status to accepted
 *             $updateSql = "UPDATE Exchanges SET Status = 'accepted', ConfirmedDate = NOW() WHERE ExchangeId = ?";
 *             $updateStmt = $conn->prepare($updateSql);
 *             $updateStmt->bind_param('i', $exchangeId);
 *             
 *             if ($updateStmt->execute()) {
 *                 // Send acceptance notification to requester
 *                 $notifResult = sendAcceptanceNotification(
 *                     $exchange['OfferedByUserId'],
 *                     $exchange['RequestedByUserId'],
 *                     $exchangeId,
 *                     $exchange['Title'],
 *                     $exchange['FullName']
 *                 );
 *                 
 *                 echo json_encode(['success' => true, 'message' => 'Exchange accepted']);
 *             }
 *         }
 *     }
 * }
 * ?>
 */

/**
 * API ENDPOINT: Handle exchange rejection
 * 
 * USAGE: Create a file like "rejectExchange.php" in the post folder
 * POST Parameters: exchangeId (int)
 * 
 * Example code for rejectExchange.php:
 * <?php
 * session_start();
 * require_once '../../DataBaseManagement/config.php';
 * require_once './postnotification.php';
 * 
 * if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exchangeId'])) {
 *     $exchangeId = (int)$_POST['exchangeId'];
 *     $userId = $_SESSION['userId'] ?? null;
 *     
 *     // Get exchange details
 *     $sql = "SELECT e.*, p.Title, u.FullName FROM Exchanges e
 *             JOIN Posts p ON e.PostId = p.PostId
 *             JOIN Users u ON e.OfferedByUserId = u.UserId
 *             WHERE e.ExchangeId = ?";
 *     $stmt = $conn->prepare($sql);
 *     $stmt->bind_param('i', $exchangeId);
 *     $stmt->execute();
 *     $result = $stmt->get_result();
 *     
 *     if ($result->num_rows > 0) {
 *         $exchange = $result->fetch_assoc();
 *         
 *         // Verify user is the post owner
 *         if ($userId == $exchange['OfferedByUserId']) {
 *             // Update exchange status to rejected
 *             $updateSql = "UPDATE Exchanges SET Status = 'rejected' WHERE ExchangeId = ?";
 *             $updateStmt = $conn->prepare($updateSql);
 *             $updateStmt->bind_param('i', $exchangeId);
 *             
 *             if ($updateStmt->execute()) {
 *                 // Send rejection notification to requester
 *                 $notifResult = sendRejectionNotification(
 *                     $exchange['OfferedByUserId'],
 *                     $exchange['RequestedByUserId'],
 *                     $exchange['Title'],
 *                     $exchange['FullName']
 *                 );
 *                 
 *                 echo json_encode(['success' => true, 'message' => 'Exchange rejected']);
 *             }
 *         }
 *     }
 * }
 * ?>
 */
?>
