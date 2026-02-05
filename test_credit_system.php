<?php
/**
 * TEST CREDIT TRANSACTIONS & NOTIFICATIONS
 * 
 * This script helps you test that:
 * 1. CreditTransactions are recorded when credits go up or down
 * 2. UserNotifications are created with 'spent' or 'earned' type
 * 
 * HOW TO TEST:
 * 
 * SCENARIO 1: Service Booking (Post Exchange)
 * ------------------------------------------
 * 1. Login as User A (who has credits)
 * 2. Go to a post by User B that requires credits
 * 3. Book the service using credits
 * 4. Login as User B (post owner)
 * 5. Go to your received requests and ACCEPT the booking
 * 6. Run this script to verify the records were created
 * 
 * SCENARIO 2: Event Booking
 * -------------------------
 * 1. Login as User A (who has credits)
 * 2. Go to an event by User B that has a cost
 * 3. Send join request
 * 4. Login as User B (event organizer)
 * 5. Go to event management and ACCEPT the attendee
 * 6. Run this script to verify the records were created
 */

require 'DataBaseManagement/config.php';

echo "╔════════════════════════════════════════════════════════════════════════╗\n";
echo "║           CREDIT TRANSACTIONS & NOTIFICATIONS TEST                     ║\n";
echo "╚════════════════════════════════════════════════════════════════════════╝\n\n";

// 1. Check CreditTransactions table
echo "📊 CREDIT TRANSACTIONS (Latest 15):\n";
echo str_repeat("-", 100) . "\n";

$txQuery = "SELECT ct.TransactionId, ct.UserId, u.FullName, ct.TransactionType, 
                   ct.Amount, ct.BalanceAfter, ct.RelatedEntityType, ct.Description, ct.CreatedAt
            FROM CreditTransactions ct
            LEFT JOIN Users u ON ct.UserId = u.UserId
            ORDER BY ct.TransactionId DESC
            LIMIT 15";
$txResult = $conn->query($txQuery);

if ($txResult && $txResult->num_rows > 0) {
    printf("%-4s | %-15s | %-8s | %-6s | %-8s | %-8s | %-30s | %s\n", 
           "ID", "User", "Type", "Amount", "Balance", "Entity", "Description", "Date");
    echo str_repeat("-", 100) . "\n";
    
    while ($row = $txResult->fetch_assoc()) {
        $typeIcon = $row['TransactionType'] === 'earned' ? '💰' : '💸';
        printf("%-4s | %-15s | %s %-6s | %-6s | %-8s | %-8s | %-30s | %s\n",
               $row['TransactionId'],
               substr($row['FullName'] ?? 'User '.$row['UserId'], 0, 15),
               $typeIcon,
               $row['TransactionType'],
               $row['Amount'],
               $row['BalanceAfter'],
               $row['RelatedEntityType'],
               substr($row['Description'], 0, 30),
               date('M d H:i', strtotime($row['CreatedAt']))
        );
    }
} else {
    echo "   (No credit transactions found)\n";
}

echo "\n";

// 2. Check UserNotifications for credit-related notifications
echo "🔔 CREDIT NOTIFICATIONS (Latest 15):\n";
echo str_repeat("-", 100) . "\n";

$notifQuery = "SELECT un.NotificationId, un.RecipientId, u.FullName, un.NotificationType, 
                      un.Title, un.Message, un.CreatedAt, un.IsRead
               FROM UserNotifications un
               LEFT JOIN Users u ON un.RecipientId = u.UserId
               WHERE un.NotificationSection = 'credits'
               ORDER BY un.NotificationId DESC
               LIMIT 15";
$notifResult = $conn->query($notifQuery);

if ($notifResult && $notifResult->num_rows > 0) {
    printf("%-4s | %-15s | %-8s | %-20s | %-40s | %s\n",
           "ID", "Recipient", "Type", "Title", "Message", "Date");
    echo str_repeat("-", 100) . "\n";
    
    while ($row = $notifResult->fetch_assoc()) {
        $typeIcon = $row['NotificationType'] === 'earned' ? '💰' : '💸';
        printf("%-4s | %-15s | %s %-6s | %-20s | %-40s | %s\n",
               $row['NotificationId'],
               substr($row['FullName'] ?? 'User '.$row['RecipientId'], 0, 15),
               $typeIcon,
               $row['NotificationType'],
               substr($row['Title'], 0, 20),
               substr($row['Message'], 0, 40),
               date('M d H:i', strtotime($row['CreatedAt']))
        );
    }
} else {
    echo "   (No credit notifications found)\n";
}

echo "\n";

// 3. Summary stats
echo "📈 SUMMARY STATISTICS:\n";
echo str_repeat("-", 50) . "\n";

$totalTx = $conn->query("SELECT COUNT(*) as cnt FROM CreditTransactions")->fetch_assoc()['cnt'];
$earnedTx = $conn->query("SELECT COUNT(*) as cnt FROM CreditTransactions WHERE TransactionType = 'earned'")->fetch_assoc()['cnt'];
$spentTx = $conn->query("SELECT COUNT(*) as cnt FROM CreditTransactions WHERE TransactionType = 'spent'")->fetch_assoc()['cnt'];

echo "   Credit Transactions: $totalTx total ($earnedTx earned, $spentTx spent)\n";

$creditNotifs = $conn->query("SELECT COUNT(*) as cnt FROM UserNotifications WHERE NotificationSection = 'credits'")->fetch_assoc()['cnt'];
$earnedNotifs = $conn->query("SELECT COUNT(*) as cnt FROM UserNotifications WHERE NotificationType = 'earned'")->fetch_assoc()['cnt'];
$spentNotifs = $conn->query("SELECT COUNT(*) as cnt FROM UserNotifications WHERE NotificationType = 'spent'")->fetch_assoc()['cnt'];

echo "   Credit Notifications: $creditNotifs total ($earnedNotifs earned, $spentNotifs spent)\n";

// 4. Check today's activity
$today = date('Y-m-d');
$todayTx = $conn->query("SELECT COUNT(*) as cnt FROM CreditTransactions WHERE DATE(CreatedAt) = '$today'")->fetch_assoc()['cnt'];
$todayNotifs = $conn->query("SELECT COUNT(*) as cnt FROM UserNotifications WHERE NotificationSection = 'credits' AND DATE(CreatedAt) = '$today'")->fetch_assoc()['cnt'];

echo "\n   Today's Activity:\n";
echo "   - Credit Transactions: $todayTx\n";
echo "   - Credit Notifications: $todayNotifs\n";

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════╗\n";
echo "║                         HOW TO TEST                                    ║\n";
echo "╚════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "SCENARIO 1: Service Booking with Credits\n";
echo "-----------------------------------------\n";
echo "1. Login as a user with credits (e.g., user with 200 credits)\n";
echo "2. Find a post that requires credits (e.g., 40 credits)\n";
echo "3. Book the service selecting 'Credits' payment option\n";
echo "4. Login as the post owner\n";
echo "5. Go to received requests and ACCEPT the booking\n";
echo "6. Run this script again - you should see:\n";
echo "   • 2 new CreditTransactions (spent by requester, earned by owner)\n";
echo "   • 2 new UserNotifications (spent and earned)\n";
echo "\n";
echo "SCENARIO 2: Event with Credit Cost\n";
echo "-----------------------------------\n";
echo "1. Login as a user with credits\n";
echo "2. Find an event that has a credit cost\n";
echo "3. Send a join request\n";
echo "4. Login as the event organizer\n";
echo "5. Accept the attendee\n";
echo "6. Run this script again - same results as above\n";
echo "\n";

$conn->close();
?>
