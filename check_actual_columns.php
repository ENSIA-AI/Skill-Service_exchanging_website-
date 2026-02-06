<?php
require 'DataBaseManagement/config.php';

echo "<h2>Checking Actual Database Table Structure</h2>";

echo "<h3>UserNotifications Table Columns:</h3>";
$result = $conn->query('DESCRIBE UserNotifications');

if (!$result) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . $conn->error . "</p>";
} else {
    $columns = [];
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
    echo "<tr style='background:#f5f5f5;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
        echo "<tr>";
        echo "<td><strong>".$row['Field']."</strong></td>";
        echo "<td>".$row['Type']."</td>";
        echo "<td>".$row['Null']."</td>";
        echo "<td>".$row['Key']."</td>";
        echo "<td>".$row['Default']."</td>";
        echo "<td>".$row['Extra']."</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Available Columns:</h3>";
    echo "<p>" . implode(", ", $columns) . "</p>";
}
?>
