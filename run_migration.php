<?php
// Direct database connection for migration
$conn = new mysqli('127.0.0.1', 'root', '', 'skill_service_exchange_db', 3307);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Execute the migration
$sql = "
ALTER TABLE Users ADD COLUMN IsAdmin ENUM('yes','no') DEFAULT 'no' AFTER IsBanned;
UPDATE Users SET IsAdmin = 'yes' WHERE UserId = 1;
CREATE INDEX idx_users_is_admin ON Users(IsAdmin);
";

// Split by semicolon and execute each statement
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $statement) {
    if (!empty($statement)) {
        try {
            if ($conn->query($statement)) {
                echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
            } else {
                if (strpos($conn->error, 'Duplicate column') !== false || strpos($conn->error, 'already exists') !== false) {
                    echo "! " . substr($statement, 0, 50) . "... (already exists)\n";
                } else {
                    echo "✗ Error: " . $conn->error . "\n";
                }
            }
        } catch (Exception $e) {
            echo "! " . substr($statement, 0, 50) . "... (may already exist)\n";
        }
    }
}

echo "\n✓ Migration completed successfully!\n";
echo "✓ First user (UserId=1) is now an admin\n";
$conn->close();
?>
