<?php
require 'DataBaseManagement/config.php';

echo "<h2>UserNotifications Table Structure:</h2>";
$result = $conn->query('DESCRIBE UserNotifications');
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>".$row['Field']."</td>";
    echo "<td>".$row['Type']."</td>";
    echo "<td>".$row['Null']."</td>";
    echo "<td>".$row['Key']."</td>";
    echo "<td>".$row['Default']."</td>";
    echo "<td>".$row['Extra']."</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Exchanges Table Structure:</h2>";
$result2 = $conn->query('DESCRIBE Exchanges');
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while($row = $result2->fetch_assoc()) {
    echo "<tr>";
    echo "<td>".$row['Field']."</td>";
    echo "<td>".$row['Type']."</td>";
    echo "<td>".$row['Null']."</td>";
    echo "<td>".$row['Key']."</td>";
    echo "<td>".$row['Default']."</td>";
    echo "<td>".$row['Extra']."</td>";
    echo "</tr>";
}
echo "</table>";
?>
