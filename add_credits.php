<?php
require_once 'DataBaseManagement/config.php';

$userId = 26;
$newBalance = 100;

$conn->query("UPDATE Users SET CreditBalance = $newBalance WHERE UserId = $userId");
echo "Updated user $userId credit balance to $newBalance\n";

$result = $conn->query("SELECT UserId, CreditBalance FROM Users WHERE UserId = $userId");
$user = $result->fetch_assoc();
echo "User {$user['UserId']} now has {$user['CreditBalance']} credits\n";

$conn->close();
?>
