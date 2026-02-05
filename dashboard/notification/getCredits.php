<?php
/**
 * Get Credits Endpoint
 * Location: /dashboard/notification/getCredits.php
 * 
 * Returns the current credit balance for the logged-in user
 */

session_start();
header('Content-Type: application/json');

require_once '../../DataBaseManagement/config.php';

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Not logged in', 'balance' => 0]);
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    
    // Fetch current credit balance
    $stmt = $conn->prepare("SELECT CreditBalance FROM Users WHERE UserId = ?");
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'User not found', 'balance' => 0]);
        $stmt->close();
        exit;
    }
    
    $user = $result->fetch_assoc();
    $balance = (int)($user['CreditBalance'] ?? 0);
    
    $stmt->close();
    $conn->close();
    
    // Return success with balance
    echo json_encode([
        'success' => true,
        'balance' => $balance
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'balance' => 0
    ]);
}
?>
