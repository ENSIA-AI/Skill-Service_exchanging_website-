<?php
/**
 * Create a test admin account for testing the admin panel
 * 
 * This script creates a temporary admin account with known credentials.
 * You can delete this file after testing.
 */

require_once 'DataBaseManagement/config.php';

$testUsername = 'admin_test';
$testEmail = 'admin.test@skillswap.local';
$testPassword = 'TestAdmin123';  // Must meet requirements: 8+ chars, letter, number
$testFullName = 'Test Administrator';

// Hash the password
$hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);

try {
    // Check if user already exists
    $checkStmt = $conn->prepare('SELECT UserId FROM Users WHERE UserName = ? OR Email = ?');
    $checkStmt->bind_param('ss', $testUsername, $testEmail);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        echo '<h2>ℹ️ Test Account Already Exists</h2>';
        echo '<p>Username: <strong>' . htmlspecialchars($testUsername) . '</strong></p>';
        echo '<p>Email: <strong>' . htmlspecialchars($testEmail) . '</strong></p>';
        echo '<p>Password: <strong>' . htmlspecialchars($testPassword) . '</strong></p>';
        echo '<p><a href="auth/login.php">Go to Login</a></p>';
        $checkStmt->close();
        $conn->close();
        exit();
    }
    $checkStmt->close();
    
    // Insert new admin user
    $stmt = $conn->prepare('
        INSERT INTO Users 
        (UserName, Email, Password, FirstName, LastName, IsAdmin, IsBanned, ProfileCreatedAt, ProfileUpdatedAt)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ');
    
    $isAdmin = 'yes';
    $isBanned = 'no';
    $firstName = 'Test';
    $lastName = 'Admin';
    
    $stmt->bind_param(
        'sssssss',
        $testUsername,
        $testEmail,
        $hashedPassword,
        $firstName,
        $lastName,
        $isAdmin,
        $isBanned
    );
    
    if ($stmt->execute()) {
        echo '<h2 style="color: green;">✅ Test Admin Account Created Successfully!</h2>';
        echo '<div style="background: #e8f5e9; padding: 20px; border-radius: 5px; font-family: monospace;">';
        echo '<p><strong>Login Credentials:</strong></p>';
        echo '<p>Username or Email: <strong>' . htmlspecialchars($testEmail) . '</strong></p>';
        echo '<p>Password: <strong>' . htmlspecialchars($testPassword) . '</strong></p>';
        echo '</div>';
        echo '<p style="margin-top: 20px;"><a href="auth/login.php" style="background: #1976d2; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Go to Login →</a></p>';
        echo '<p style="color: #666; margin-top: 30px;"><strong>Next Steps:</strong></p>';
        echo '<ol>';
        echo '<li>Click the "Go to Login" button above</li>';
        echo '<li>Use the credentials above to log in</li>';
        echo '<li>Navigate to <code>/dashboard/admin/index.php</code></li>';
        echo '<li>Test all the admin features</li>';
        echo '<li>After testing, you can delete this file</li>';
        echo '</ol>';
    } else {
        echo '<h2 style="color: red;">❌ Error Creating Account</h2>';
        echo '<p>Database Error: ' . htmlspecialchars($conn->error) . '</p>';
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo '<h2 style="color: red;">❌ Exception Error</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Test Admin - SkillSwap</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h2 {
            margin-top: 0;
        }
        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
        }
        ol {
            line-height: 1.8;
        }
    </style>
</head>
<body>
</body>
</html>
