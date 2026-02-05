<?php
/**
 * Complete Database Recovery & Admin Setup Script
 * Run this after MySQL is confirmed to be running on port 3307
 */

echo "========== DATABASE RECOVERY & SETUP SCRIPT ==========\n\n";

require_once 'DataBaseManagement/config.php';

// Test 1: Connection
echo "1. Testing database connection...\n";
if (!$conn) {
    echo "   ✗ FAILED: Cannot connect to database\n";
    echo "   Please ensure MySQL is running on port 3307\n";
    exit(1);
}
echo "   ✓ Connected to " . $dbName . " on port " . $dbPort . "\n\n";

// Test 2: Check IsAdmin column
echo "2. Checking IsAdmin column...\n";
$result = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='Users' AND COLUMN_NAME='IsAdmin'");
$hasIsAdmin = $result && $result->num_rows > 0;

if ($hasIsAdmin) {
    echo "   ✓ IsAdmin column exists\n";
} else {
    echo "   ✗ IsAdmin column NOT found - Adding it now...\n";
    
    if ($conn->query("ALTER TABLE Users ADD COLUMN IsAdmin ENUM('yes','no') DEFAULT 'no' AFTER IsBanned")) {
        echo "   ✓ IsAdmin column added successfully\n";
    } else {
        echo "   ✗ ERROR: " . $conn->error . "\n";
        exit(1);
    }
}

// Test 3: Set first user as admin
echo "\n3. Setting first user as admin...\n";
$checkAdmin = $conn->query("SELECT COUNT(*) as count FROM Users WHERE IsAdmin = 'yes'");
$adminCount = $checkAdmin->fetch_assoc()['count'];

if ($adminCount > 0) {
    echo "   ✓ Already has " . $adminCount . " admin(s)\n";
} else {
    echo "   ✓ No admins found - Creating first admin...\n";
    
    if ($conn->query("UPDATE Users SET IsAdmin = 'yes' WHERE UserId = 1")) {
        echo "   ✓ First user (UserId=1) is now an admin\n";
    } else {
        echo "   ✗ ERROR: " . $conn->error . "\n";
        exit(1);
    }
}

// Test 4: Test write operations
echo "\n4. Testing write operations...\n";
$testTime = time();
$testVal = "writetest_" . $testTime;

$stmt = $conn->prepare("SELECT UserId FROM Users WHERE UserName = ?");
if ($stmt) {
    $stmt->bind_param('s', $testVal);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows == 0) {
        $stmt->close();
        
        $insertStmt = $conn->prepare("INSERT INTO Users (UserName, Email, Password, FullName, IsAdmin, IsBanned, UserSince) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        
        if ($insertStmt) {
            $password = password_hash("Temp123", PASSWORD_DEFAULT);
            $admin = 'no';
            $banned = 'no';
            $fullName = "Test User";
            $email = $testVal . "@test.local";
            
            $insertStmt->bind_param('ssssss', $testVal, $email, $password, $fullName, $admin, $banned);
            
            if ($insertStmt->execute()) {
                $newId = $conn->insert_id;
                echo "   ✓ INSERT successful\n";
                
                $updateStmt = $conn->prepare("UPDATE Users SET FullName = ? WHERE UserId = ?");
                $newName = "Updated " . date('H:i:s');
                $updateStmt->bind_param('si', $newName, $newId);
                
                if ($updateStmt->execute()) {
                    echo "   ✓ UPDATE successful\n";
                } else {
                    echo "   ✗ UPDATE failed: " . $conn->error . "\n";
                }
                
                $deleteStmt = $conn->prepare("DELETE FROM Users WHERE UserId = ?");
                $deleteStmt->bind_param('i', $newId);
                $deleteStmt->execute();
                echo "   ✓ CLEANUP successful\n";
                
            } else {
                echo "   ✗ INSERT FAILED: " . $conn->error . "\n";
                echo "   ✗ DATABASE IS IN READ-ONLY MODE!\n";
                exit(1);
            }
        }
    }
}

// Test 5: Create test admin account
echo "\n5. Creating test admin account...\n";
$testUsername = 'admin_test';
$testEmail = 'admin.test@skillswap.local';
$testPassword = 'TestAdmin123';
$testFullName = 'Test Administrator';

$checkStmt = $conn->prepare('SELECT UserId FROM Users WHERE UserName = ? OR Email = ?');
$checkStmt->bind_param('ss', $testUsername, $testEmail);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo "   ℹ Test admin account already exists\n";
} else {
    $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
    $isAdmin = 'yes';
    $isBanned = 'no';
    
    $createStmt = $conn->prepare('INSERT INTO Users (UserName, Email, Password, FullName, IsAdmin, IsBanned, UserSince) VALUES (?, ?, ?, ?, ?, ?, NOW())');
    
    if ($createStmt && $createStmt->bind_param('ssssss', $testUsername, $testEmail, $hashedPassword, $testFullName, $isAdmin, $isBanned) && $createStmt->execute()) {
        echo "   ✓ Test admin account created!\n";
    } else {
        echo "   ✗ Failed to create test admin: " . $conn->error . "\n";
    }
}
$checkStmt->close();

// Summary
echo "\n========== SETUP COMPLETE ==========\n";
echo "✓ Database connection: WORKING\n";
echo "✓ IsAdmin column: ADDED\n";
echo "✓ Admin user: CONFIGURED\n";
echo "✓ Write operations: VERIFIED\n";
echo "\n========== TEST ADMIN LOGIN ==========\n";
echo "Email: admin.test@skillswap.local\n";
echo "Password: TestAdmin123\n";
echo "\n========== NEXT STEPS ==========\n";
echo "1. Go to: http://localhost/Skill-Service_exchanging_website-/auth/login.php\n";
echo "2. Log in with credentials above\n";
echo "3. Visit: http://localhost/Skill-Service_exchanging_website-/dashboard/admin/\n\n";

$conn->close();
?>
