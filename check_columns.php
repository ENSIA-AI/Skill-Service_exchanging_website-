<?php
require_once 'DataBaseManagement/config.php';
$result = $conn->query('DESCRIBE Users');
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}
?>
