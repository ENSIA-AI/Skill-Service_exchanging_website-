<?php
/**
 * Accept Exchange Endpoint
 * Location: /dashboard/post/acceptExchange.php
 * 
 * Handles POST requests to accept a booking/exchange request
 * Updates exchange status to 'accepted' and sends notification to requester
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Custom error handler to return JSON errors
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

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
    $userId = $_SESSION['user_id'];  // Fixed: was userId, app uses user_id
    
    // Get exchange details with post and user info (owner + requester names for notifications)
    $sql = "SELECT e.*, p.Title, 
            owner.FullName AS OwnerName, requester.FullName AS RequesterName
            FROM Exchanges e
            JOIN Posts p ON e.PostId = p.PostId
            JOIN Users owner ON e.OfferedByUserId = owner.UserId
            JOIN Users requester ON e.RequestedByUserId = requester.UserId
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
        echo json_encode(['success' => false, 'error' => 'Unauthorized - only post owner can accept']);
        exit;
    }
    
    // Check if exchange is still pending
    if ($exchange['Status'] !== 'pending') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Exchange is not in pending status']);
        exit;
    }
    
    // Update exchange status to 'accepted'
    // Use ProposedDate as ConfirmedDate since the trigger requires ConfirmedDate >= ProposedDate
    $updateSql = "UPDATE Exchanges SET Status = 'accepted', ConfirmedDate = ProposedDate WHERE ExchangeId = ?";
    $updateStmt = $conn->prepare($updateSql);
    
    if (!$updateStmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $updateStmt->bind_param('i', $exchangeId);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to update exchange: ' . $updateStmt->error);
    }
    
    $updateStmt->close();
    
    // Transfer credits if CreditsCost > 0 (per swapdb triggers: CreditTransactions + UserNotifications earned/spent)
    $creditsCost = (int)($exchange['CreditsCost'] ?? 0);
    if ($creditsCost > 0) {
        $requesterId = (int)$exchange['RequestedByUserId'];
        $ownerId = (int)$exchange['OfferedByUserId'];
        $postTitle = $exchange['Title'];
        $ownerName = $exchange['OwnerName'] ?? 'Service provider';
        $requesterName = $exchange['RequesterName'] ?? 'User';

        // Verify requester still has sufficient balance (may have changed since booking)
        $chkSql = "SELECT CreditBalance FROM Users WHERE UserId = ?";
        $chkStmt = $conn->prepare($chkSql);
        $chkStmt->bind_param('i', $requesterId);
        $chkStmt->execute();
        $chkRow = $chkStmt->get_result()->fetch_assoc();
        $chkStmt->close();
        if (!$chkRow || (int)$chkRow['CreditBalance'] < $creditsCost) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Requester has insufficient credits. Please ask them to top up.']);
            exit;
        }

        $conn->begin_transaction();
        try {
            // Deduct from requester (booker)
            $conn->query("UPDATE Users SET CreditBalance = CreditBalance - $creditsCost WHERE UserId = $requesterId");
            if ($conn->affected_rows !== 1) {
                throw new Exception('Failed to deduct credits from requester');
            }
            $reqBalSql = "SELECT CreditBalance FROM Users WHERE UserId = ?";
            $reqBalStmt = $conn->prepare($reqBalSql);
            $reqBalStmt->bind_param('i', $requesterId);
            $reqBalStmt->execute();
            $reqBal = (int)$reqBalStmt->get_result()->fetch_assoc()['CreditBalance'];
            $reqBalStmt->close();

            // Add to owner (service provider)
            $conn->query("UPDATE Users SET CreditBalance = CreditBalance + $creditsCost WHERE UserId = $ownerId");
            if ($conn->affected_rows !== 1) {
                throw new Exception('Failed to add credits to owner');
            }
            $ownBalSql = "SELECT CreditBalance FROM Users WHERE UserId = ?";
            $ownBalStmt = $conn->prepare($ownBalSql);
            $ownBalStmt->bind_param('i', $ownerId);
            $ownBalStmt->execute();
            $ownBal = (int)$ownBalStmt->get_result()->fetch_assoc()['CreditBalance'];
            $ownBalStmt->close();

            // Insert CreditTransaction for requester (spent)
            $ctSpent = "INSERT INTO CreditTransactions (UserId, TransactionType, Amount, BalanceAfter, RelatedEntityType, RelatedEntityId, Description) 
                        VALUES (?, 'spent', ?, ?, 'exchange', ?, ?)";
            $ctSpentStmt = $conn->prepare($ctSpent);
            $spentDesc = "$postTitle with $ownerName";
            $ctSpentStmt->bind_param('iiiis', $requesterId, $creditsCost, $reqBal, $exchangeId, $spentDesc);
            $ctSpentStmt->execute();
            $ctSpentStmt->close();

            // Insert CreditTransaction for owner (earned)
            $ctEarned = "INSERT INTO CreditTransactions (UserId, TransactionType, Amount, BalanceAfter, RelatedEntityType, RelatedEntityId, Description) 
                         VALUES (?, 'earned', ?, ?, 'exchange', ?, ?)";
            $ctEarnedStmt = $conn->prepare($ctEarned);
            $earnedDesc = "$postTitle from $requesterName";
            $ctEarnedStmt->bind_param('iiiis', $ownerId, $creditsCost, $ownBal, $exchangeId, $earnedDesc);
            $ctEarnedStmt->execute();
            $ctEarnedStmt->close();

            // UserNotifications: 'spent' and 'earned' require NotificationSection = 'credits' (per trg_validate_notification_section)
            try {
                $notifSpent = "INSERT INTO UserNotifications (RecipientId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection) 
                              VALUES (?, 'spent', ?, ?, 'no', NOW(), 'credits')";
                $nsStmt = $conn->prepare($notifSpent);
                $nsTitle = "Credits Spent";
                $nsMsg = "You spent $creditsCost credits for $postTitle with $ownerName.";
                $nsStmt->bind_param('iss', $requesterId, $nsTitle, $nsMsg);
                $nsStmt->execute();
                $nsStmt->close();
            } catch (mysqli_sql_exception $e) {
                error_log("Failed to create 'spent' notification: " . $e->getMessage());
            }

            try {
                $notifEarned = "INSERT INTO UserNotifications (RecipientId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection) 
                               VALUES (?, 'earned', ?, ?, 'no', NOW(), 'credits')";
                $neStmt = $conn->prepare($notifEarned);
                $neTitle = "Credits Earned";
                $neMsg = "You earned $creditsCost credits for $postTitle from $requesterName.";
                $neStmt->bind_param('iss', $ownerId, $neTitle, $neMsg);
                $neStmt->execute();
                $neStmt->close();
            } catch (mysqli_sql_exception $e) {
                error_log("Failed to create 'earned' notification: " . $e->getMessage());
            }

            $conn->commit();
        } catch (Exception $ex) {
            $conn->rollback();
            throw $ex;
        }
    }
    
    // Update the original booking notification to mark it as processed (change type from 'booking' to 'booking_accepted')
    // This prevents the Accept/Decline buttons from showing again
    $updateNotifSql = "UPDATE UserNotifications SET NotificationType = 'booking_accepted' WHERE ExchangeId = ? AND NotificationType = 'booking'";
    $updateNotifStmt = $conn->prepare($updateNotifSql);
    if ($updateNotifStmt) {
        $updateNotifStmt->bind_param('i', $exchangeId);
        $updateNotifStmt->execute();
        $updateNotifStmt->close();
    }
    
    // Create and insert acceptance notification (inline - like eventdetails)
    try {
        $message = $exchange['OwnerName'] . " has accepted your booking request for " . $exchange['Title'];
        $title = "Booking Accepted";
        
        $notificationSQL = "INSERT INTO UserNotifications 
                           (RecipientId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection)
                           VALUES (?, 'accepted', ?, ?, 'no', NOW(), 'Exchange')";
        
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
    } catch (mysqli_sql_exception $e) {
        error_log("Failed to create 'accepted' notification: " . $e->getMessage());
        $notificationId = 0;
    }
    
    // Return success response with updated balances (if credits were transferred)
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Exchange accepted successfully',
        'notificationId' => $notificationId,
        'ownerBalance' => isset($ownBal) ? $ownBal : null,
        'requesterBalance' => isset($reqBal) ? $reqBal : null,
        'creditsCost' => isset($creditsCost) ? (int)$creditsCost : 0
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
