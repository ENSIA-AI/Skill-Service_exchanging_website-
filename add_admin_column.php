<?php
require_once 'DataBaseManagement/config.php';

echo "Checking for IsAdmin column...\n";
$result = $conn->query('DESCRIBE Users');
$hasIsAdmin = false;

while ($row = $result->fetch_assoc()) {
    if ($row['Field'] === 'IsAdmin') {
        $hasIsAdmin = true;
        break;
    }
}

echo "IsAdmin column exists: " . ($hasIsAdmin ? "YES" : "NO") . "\n";

if (!$hasIsAdmin) {
    echo "\nNeed to add IsAdmin column!\n";
    echo "Adding IsAdmin column...\n";
    
    $conn->query("ALTER TABLE Users ADD COLUMN IsAdmin ENUM('yes','no') DEFAULT 'no' AFTER IsBanned");
    if ($conn->error) {
        echo "Error: " . $conn->error . "\n";
    } else {
        echo "✓ IsAdmin column added successfully\n";
    }
    
    // Set first user as admin
    echo "Setting first user as admin...\n";
    $conn->query("UPDATE Users SET IsAdmin = 'yes' WHERE UserId = 1");
    if ($conn->error) {
        echo "Error: " . $conn->error . "\n";
    } else {
        echo "✓ First user set as admin\n";
    }
}
?>
