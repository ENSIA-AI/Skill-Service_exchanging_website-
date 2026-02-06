<?php
require 'DataBaseManagement/config.php';

echo "<h2>Latest Booking Records</h2>";

echo "<h3>Recent Exchanges:</h3>";
$exResult = $conn->query("SELECT ExchangeId, PostId, OfferedByUserId, RequestedByUserId, ProposedDate, Status, CreatedAt FROM Exchanges ORDER BY CreatedAt DESC LIMIT 5");
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ExchangeId</th><th>PostId</th><th>Owner</th><th>Requester</th><th>ProposedDate</th><th>Status</th><th>CreatedAt</th></tr>";
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

echo "<h3>Recent Notifications:</h3>";
$notifResult = $conn->query("SELECT NotificationId, UserId, SenderId, NotificationType, Title, IsRead, CreatedAt FROM UserNotifications ORDER BY CreatedAt DESC LIMIT 5");
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>NotifId</th><th>UserId</th><th>SenderId</th><th>Type</th><th>Title</th><th>IsRead</th><th>CreatedAt</th></tr>";
while($row = $notifResult->fetch_assoc()) {
    echo "<tr>";
    echo "<td>".$row['NotificationId']."</td>";
    echo "<td>".$row['UserId']."</td>";
    echo "<td>".$row['SenderId']."</td>";
    echo "<td>".$row['NotificationType']."</td>";
    echo "<td>".$row['Title']."</td>";
    echo "<td>".$row['IsRead']."</td>";
    echo "<td>".$row['CreatedAt']."</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Latest Debug Log:</h3>";
if (file_exists('dashboard/post/booking_debug.log')) {
    $log = file_get_contents('dashboard/post/booking_debug.log');
    $lines = explode("\n", $log);
    $recent = array_slice($lines, -50);
    echo "<pre style='background:#f0f0f0; padding:10px; max-height:400px; overflow:auto;'>";
    echo implode("\n", $recent);
    echo "</pre>";
} else {
    echo "<p>Debug log not found</p>";
}
?>
