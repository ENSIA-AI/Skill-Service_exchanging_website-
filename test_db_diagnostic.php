<?php
/**
 * Database Connection Test & Diagnostic Script
 * This script tests the database connection and identifies read-only issues
 */

echo "=== Database Connection Diagnostic ===\n\n";

// Load config
require_once 'DataBaseManagement/config.php';

// Test 1: Connection Status
echo "1. CONNECTION TEST\n";
if ($conn) {
    echo "   ✓ Connected to " . $dbHost . ":" . $dbPort . "\n";
    echo "   ✓ Database: " . $dbName . "\n";
} else {
    echo "   ✗ Connection failed!\n";
    exit(1);
}

// Test 2: Read Test
echo "\n2. READ TEST\n";
$result = $conn->query("SELECT COUNT(*) as count FROM Users");
if ($result) {
    $row = $result->fetch_assoc();
    echo "   ✓ Can read from database\n";
    echo "   ✓ User count: " . $row['count'] . "\n";
} else {
    echo "   ✗ Read failed: " . $conn->error . "\n";
}

// Test 3: Write Test (Insert)
echo "\n3. WRITE TEST (INSERT)\n";
$testData = "test_" . time();
$insertStmt = $conn->prepare("INSERT INTO Users (UserName, Email, Password, FirstName, LastName, IsAdmin, IsBanned, ProfileCreatedAt, ProfileUpdatedAt) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

if ($insertStmt) {
    $password = password_hash("Test123", PASSWORD_DEFAULT);
    $admin = 'no';
    $banned = 'no';
    
    $insertStmt->bind_param(
        'sssssss',
        $testData,
        $testData . '@test.com',
        $password,
        'Test',
        'User',
        $admin,
        $banned
    );
    
    if ($insertStmt->execute()) {
        echo "   ✓ Write successful (INSERT)\n";
        echo "   ✓ New User ID: " . $conn->insert_id . "\n";
        
        // Test 4: Update Test
        echo "\n4. WRITE TEST (UPDATE)\n";
        $updateStmt = $conn->prepare("UPDATE Users SET FirstName = ? WHERE UserId = ?");
        $newName = "Updated_" . time();
        $userId = $conn->insert_id;
        $updateStmt->bind_param('si', $newName, $userId);
        
        if ($updateStmt->execute()) {
            echo "   ✓ Write successful (UPDATE)\n";
        } else {
            echo "   ✗ Update failed: " . $conn->error . "\n";
        }
        
        // Clean up - Delete test record
        echo "\n5. CLEANUP TEST (DELETE)\n";
        $deleteStmt = $conn->prepare("DELETE FROM Users WHERE UserId = ?");
        $deleteStmt->bind_param('i', $userId);
        
        if ($deleteStmt->execute()) {
            echo "   ✓ Cleanup successful (DELETE)\n";
        } else {
            echo "   ✗ Delete failed: " . $conn->error . "\n";
        }
        
    } else {
        echo "   ✗ Insert failed: " . $conn->error . "\n";
        echo "   ✗ This indicates a READ-ONLY database!\n";
    }
} else {
    echo "   ✗ Prepare failed: " . $conn->error . "\n";
}

// Test 5: Table Status
echo "\n6. TABLE STATUS\n";
$statusResult = $conn->query("SELECT TABLE_NAME, ENGINE FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $dbName . "' LIMIT 5");
if ($statusResult) {
    echo "   ✓ Sample tables:\n";
    while ($row = $statusResult->fetch_assoc()) {
        echo "      - " . $row['TABLE_NAME'] . " (" . $row['ENGINE'] . ")\n";
    }
} else {
    echo "   ✗ Status query failed\n";
}

// Test 6: InnoDB Status
echo "\n7. INNODB STATUS\n";
$innodbResult = $conn->query("SHOW ENGINE INNODB STATUS");
if ($innodbResult) {
    $row = $innodbResult->fetch_assoc();
    if (strpos($row['Status'], 'read only') !== false) {
        echo "   ✗ WARNING: Database is in READ-ONLY mode!\n";
        echo "   This is likely due to disk space or permissions issue.\n";
    } else {
        echo "   ✓ InnoDB is operating normally\n";
    }
} else {
    echo "   ℹ InnoDB status check skipped\n";
}

// Test 7: Disk Space
echo "\n8. DISK SPACE CHECK\n";
$dataPath = "C:\\xampp2\\mysql\\data\\" . $dbName;
if (is_dir($dataPath)) {
    $diskSpace = disk_free_space("C:\\xampp2\\mysql\\");
    $diskTotal = disk_total_space("C:\\xampp2\\mysql\\");
    $diskUsed = $diskTotal - $diskSpace;
    $diskPercent = ($diskUsed / $diskTotal) * 100;
    
    echo "   Disk Free: " . round($diskSpace / 1024 / 1024 / 1024, 2) . " GB\n";
    echo "   Disk Total: " . round($diskTotal / 1024 / 1024 / 1024, 2) . " GB\n";
    echo "   Usage: " . round($diskPercent, 2) . "%\n";
    
    if ($diskPercent > 90) {
        echo "   ✗ WARNING: Disk is almost full! This causes read-only mode.\n";
    } else {
        echo "   ✓ Disk space is healthy\n";
    }
} else {
    echo "   ℹ Database path not found\n";
}

echo "\n=== Diagnostic Complete ===\n";
$conn->close();
?>
