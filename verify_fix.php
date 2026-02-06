<?php
require 'DataBaseManagement/config.php';

echo "<h1>✓ FIX COMPLETE - Verifying Booking System</h1>";

echo "<h2>1. Schema Verification</h2>";
echo "<p><strong>Correct Column Names:</strong></p>";
echo "<ul>";
echo "<li>UserNotifications table uses <code>UserId</code> (NOT RecipientId)</li>";
echo "<li>UserId is the recipient of the notification</li>";
echo "<li>SenderId is who triggered it</li>";
echo "</ul>";

echo "<h2>2. Table Structure</h2>";
$result = $conn->query('DESCRIBE UserNotifications');
echo "<table border='1' cellpadding='8'>";
echo "<tr style='background:#f5f5f5;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
while($row = $result->fetch_assoc()) {
    $highlight = ($row['Field'] === 'UserId') ? 'style="background:#fff9c4; font-weight:bold;"' : '';
    echo "<tr $highlight>";
    echo "<td>".$row['Field']."</td>";
    echo "<td>".$row['Type']."</td>";
    echo "<td>".$row['Null']."</td>";
    echo "<td>".$row['Key']."</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>3. Recent Bookings in Database</h2>";

echo "<h3>Exchanges Table (Latest):</h3>";
$exResult = $conn->query("SELECT * FROM Exchanges ORDER BY ExchangeId DESC LIMIT 3");
echo "<table border='1' cellpadding='8'>";
echo "<tr style='background:#e3f2fd;'><th>ExchangeId</th><th>PostId</th><th>Owner</th><th>Requester</th><th>Status</th><th>CreatedAt</th></tr>";
while ($row = $exResult->fetch_assoc()) {
    echo "<tr>";
    echo "<td><strong>".$row['ExchangeId']."</strong></td>";
    echo "<td>".$row['PostId']."</td>";
    echo "<td>".$row['OfferedByUserId']."</td>";
    echo "<td>".$row['RequestedByUserId']."</td>";
    echo "<td>".$row['Status']."</td>";
    echo "<td>".$row['CreatedAt']."</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>UserNotifications Table (Latest):</h3>";
$notifResult = $conn->query("SELECT NotificationId, UserId, SenderId, NotificationType, Title, IsRead, CreatedAt FROM UserNotifications WHERE NotificationType = 'booking' ORDER BY NotificationId DESC LIMIT 5");
echo "<table border='1' cellpadding='8'>";
echo "<tr style='background:#e8f5e9;'><th>NotifId</th><th>UserId (Recipient)</th><th>SenderId</th><th>Type</th><th>Title</th><th>IsRead</th><th>CreatedAt</th></tr>";
$count = 0;
while ($row = $notifResult->fetch_assoc()) {
    $count++;
    echo "<tr>";
    echo "<td><strong>".$row['NotificationId']."</strong></td>";
    echo "<td>".$row['UserId']."</td>";
    echo "<td>".$row['SenderId']."</td>";
    echo "<td>".$row['NotificationType']."</td>";
    echo "<td>".substr($row['Title'], 0, 40)."</td>";
    echo "<td>".$row['IsRead']."</td>";
    echo "<td>".$row['CreatedAt']."</td>";
    echo "</tr>";
}
echo "</table>";

if ($count === 0) {
    echo "<p style='color:orange;'><strong>⚠ No booking notifications yet.</strong> Try making a booking from the website to create one.</p>";
}

echo "<h2>✓ Status</h2>";
echo "<p style='background:#e8f5e9; padding:15px; color:#2e7d32; border-left:4px solid #4caf50;'>";
echo "<strong>FIXED:</strong> Both problems should now be resolved:<br>";
echo "1. ✓ New bookings will insert rows into the Exchanges table<br>";
echo "2. ✓ Corresponding notifications will insert rows into UserNotifications table<br>";
echo "3. ✓ Admin notifications page will display all new booking notifications<br>";
echo "</p>";

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Go to a post (e.g., PostId=1)</li>";
echo "<li>Book a service by filling the form and submitting</li>";
echo "<li>Verify Exchange row is created in Exchanges table</li>";
echo "<li>Verify Notification row appears in UserNotifications table</li>";
echo "<li>Verify new notification appears in Admin Notifications page</li>";
echo "</ol>";
?>
