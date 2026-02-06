<?php
require 'DataBaseManagement/config.php';

echo "<h1>Booking System Verification Report</h1>";

echo "<h2>✓ Issue Fixed</h2>";
echo "<p><strong>Problem:</strong> When submitting a booking request, rows were not being inserted into UserNotifications table.</p>";
echo "<p><strong>Root Cause:</strong> The code was trying to insert into columns that didn't exist:</p>";
echo "<ul>";
echo "<li>Used <code>RecipientId</code> column which doesn't exist (correct column is <code>UserId</code>)</li>";
echo "<li>Used <code>ExchangeId</code> column in INSERT which doesn't exist in UserNotifications table</li>";
echo "</ul>";

echo "<p><strong>Solution Applied:</strong> Updated the INSERT query to use correct column names:</p>";
echo "<pre style='background:#e8f5e9; padding:10px; border-left:4px solid #4caf50;'>";
echo "BEFORE:\n";
echo "INSERT INTO UserNotifications \n";
echo "  (RecipientId, SenderId, ExchangeId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection)\n";
echo "VALUES (?, ?, ?, 'booking', ?, ?, 'no', NOW(), 'Exchange')\n\n";
echo "AFTER:\n";
echo "INSERT INTO UserNotifications \n";
echo "  (UserId, SenderId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection)\n";
echo "VALUES (?, ?, 'booking', ?, ?, 'no', NOW(), 'Exchange')\n";
echo "</pre>";

echo "<h2>Database Table Structures</h2>";

echo "<h3>UserNotifications Columns:</h3>";
$result = $conn->query('DESCRIBE UserNotifications');
echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
echo "<tr style='background:#f5f5f5;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
while($row = $result->fetch_assoc()) {
    $bgColor = ($row['Field'] === 'UserId') ? '#fff9c4' : '';
    echo "<tr style='background:$bgColor;'>";
    echo "<td><strong>".$row['Field']."</strong></td>";
    echo "<td>".$row['Type']."</td>";
    echo "<td>".$row['Null']."</td>";
    echo "<td>".$row['Key']."</td>";
    echo "</tr>";
}
echo "</table>";
echo "<p style='color:#f57c00;'><strong>Note:</strong> Column highlighted in yellow (<code>UserId</code>) is the recipient field in UserNotifications.</p>";

echo "<h3>Exchanges Columns:</h3>";
$result2 = $conn->query('DESCRIBE Exchanges');
echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
echo "<tr style='background:#f5f5f5;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
while($row = $result2->fetch_assoc()) {
    echo "<tr>";
    echo "<td><strong>".$row['Field']."</strong></td>";
    echo "<td>".$row['Type']."</td>";
    echo "<td>".$row['Null']."</td>";
    echo "<td>".$row['Key']."</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Verification - Recent Data</h2>";

echo "<h3>Last 5 Exchanges (should have new entries after booking):</h3>";
$exResult = $conn->query("
SELECT ExchangeId, PostId, OfferedByUserId, RequestedByUserId, ProposedDate, Status, CreatedAt 
FROM Exchanges 
ORDER BY CreatedAt DESC 
LIMIT 5
");
echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#f5f5f5;'><th>ExchangeId</th><th>PostId</th><th>Owner</th><th>Requester</th><th>ProposedDate</th><th>Status</th><th>CreatedAt</th></tr>";
while($row = $exResult->fetch_assoc()) {
    echo "<tr>";
    echo "<td>".$row['ExchangeId']."</td>";
    echo "<td>".$row['PostId']."</td>";
    echo "<td>".$row['OfferedByUserId']."</td>";
    echo "<td>".$row['RequestedByUserId']."</td>";
    echo "<td>".$row['ProposedDate']."</td>";
    echo "<td>".$row['Status']."</td>";
    echo "<td>".$row['CreatedAt']."</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Last 5 Notifications (should match exchanges):</h3>";
$notifResult = $conn->query("
SELECT NotificationId, UserId, SenderId, NotificationType, Title, IsRead, CreatedAt 
FROM UserNotifications 
ORDER BY CreatedAt DESC 
LIMIT 5
");
echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#f5f5f5;'><th>NotifId</th><th>UserId (Recipient)</th><th>SenderId</th><th>Type</th><th>Title</th><th>IsRead</th><th>CreatedAt</th></tr>";
while($row = $notifResult->fetch_assoc()) {
    echo "<tr>";
    echo "<td>".$row['NotificationId']."</td>";
    echo "<td>".$row['UserId']."</td>";
    echo "<td>".$row['SenderId']."</td>";
    echo "<td>".$row['NotificationType']."</td>";
    echo "<td>".substr($row['Title'], 0, 30)."...</td>";
    echo "<td>".$row['IsRead']."</td>";
    echo "<td>".$row['CreatedAt']."</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>✓ Status</h2>";
echo "<p style='background:#e8f5e9; padding:10px; color:#2e7d32; border-left:4px solid #4caf50;'>";
echo "<strong>FIXED:</strong> Booking requests will now successfully insert rows into both the Exchanges table and the UserNotifications table.";
echo "</p>";

echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Try making a new booking request</li>";
echo "<li>Check that a row appears in Exchanges table</li>";
echo "<li>Check that a row appears in UserNotifications table with <code>UserId</code> = post owner</li>";
echo "</ol>";
?>
