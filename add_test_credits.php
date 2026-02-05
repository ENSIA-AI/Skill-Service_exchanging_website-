<?php
session_start();
require_once 'DataBaseManagement/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Please log in first to add credits.");
}

$userId = $_SESSION['user_id'];
$creditsToAdd = isset($_GET['amount']) ? (int)$_GET['amount'] : 100; // Default 100 credits

try {
    // Get current balance
    $stmt = $conn->prepare("SELECT UserName, CreditBalance FROM Users WHERE UserId = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        die("User not found.");
    }
    
    $currentBalance = $user['CreditBalance'];
    $newBalance = $currentBalance + $creditsToAdd;
    
    // Update credits
    $updateStmt = $conn->prepare("UPDATE Users SET CreditBalance = ? WHERE UserId = ?");
    $updateStmt->bind_param("ii", $newBalance, $userId);
    
    if ($updateStmt->execute()) {
        echo "<!DOCTYPE html>";
        echo "<html><head><title>Credits Added</title>";
        echo "<style>
            body { 
                font-family: Arial, sans-serif; 
                max-width: 600px; 
                margin: 50px auto; 
                padding: 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                text-align: center;
            }
            .success-box {
                background: rgba(255, 255, 255, 0.1);
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            }
            h1 { color: #fff; margin-bottom: 20px; }
            .credit-info {
                background: rgba(255, 255, 255, 0.2);
                padding: 20px;
                border-radius: 10px;
                margin: 20px 0;
                font-size: 18px;
            }
            .amount { 
                font-size: 36px; 
                font-weight: bold; 
                color: #ffc107;
                margin: 10px 0;
            }
            a {
                display: inline-block;
                margin-top: 20px;
                padding: 12px 30px;
                background: white;
                color: #667eea;
                text-decoration: none;
                border-radius: 25px;
                font-weight: bold;
                transition: transform 0.2s;
            }
            a:hover {
                transform: scale(1.05);
            }
        </style>";
        echo "</head><body>";
        echo "<div class='success-box'>";
        echo "<h1>✅ Credits Added Successfully!</h1>";
        echo "<div class='credit-info'>";
        echo "<p><strong>User:</strong> " . htmlspecialchars($user['UserName']) . "</p>";
        echo "<p><strong>Credits Added:</strong> <span class='amount'>+" . $creditsToAdd . "</span></p>";
        echo "<p><strong>Previous Balance:</strong> " . $currentBalance . "</p>";
        echo "<p><strong>New Balance:</strong> <span class='amount'>" . $newBalance . "</span></p>";
        echo "</div>";
        echo "<a href='dashboard/post/posts.php'>Go to Posts</a>";
        echo "</div>";
        echo "</body></html>";
    } else {
        echo "Error updating credits: " . $updateStmt->error;
    }
    
    $stmt->close();
    $updateStmt->close();
    $conn->close();
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
