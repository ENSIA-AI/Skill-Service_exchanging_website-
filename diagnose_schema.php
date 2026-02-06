<?php
require 'DataBaseManagement/config.php';

echo "<h1>Database Schema Diagnostic</h1>";

// Method 1: Using DESCRIBE
echo "<h2>Method 1: DESCRIBE UserNotifications</h2>";
$result = $conn->query('DESCRIBE UserNotifications');
if ($result) {
    $columns = [];
    while($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    echo "<p>Columns found: <strong>" . implode(", ", $columns) . "</strong></p>";
} else {
    echo "<p style='color:red;'><strong>ERROR:</strong> " . $conn->error . "</p>";
}

// Method 2: Try a simple SELECT to infer columns
echo "<h2>Method 2: SELECT LIMIT 1 (infer from result set)</h2>";
$result2 = $conn->query('SELECT * FROM UserNotifications LIMIT 1');
if ($result2) {
    $finfo = $result2->fetch_fields();
    $cols = [];
    foreach ($finfo as $field) {
        $cols[] = $field->name;
    }
    echo "<p>Columns found: <strong>" . implode(", ", $cols) . "</strong></p>";
    
    // Show actual data
    $result2->data_seek(0);
    if ($row = $result2->fetch_assoc()) {
        echo "<p>Sample row keys: <strong>" . implode(", ", array_keys($row)) . "</strong></p>";
    }
} else {
    echo "<p style='color:red;'><strong>ERROR:</strong> " . $conn->error . "</p>";
}

// Method 3: Use INFORMATION_SCHEMA
echo "<h2>Method 3: INFORMATION_SCHEMA</h2>";
$result3 = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'UserNotifications' AND TABLE_SCHEMA = 'skill_service_exchange_db'");
if ($result3) {
    $cols = [];
    while ($row = $result3->fetch_assoc()) {
        $cols[] = $row['COLUMN_NAME'];
    }
    echo "<p>Columns found: <strong>" . implode(", ", $cols) . "</strong></p>";
} else {
    echo "<p style='color:red;'><strong>ERROR:</strong> " . $conn->error . "</p>";
}
?>
