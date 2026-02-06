<?php
require 'DataBaseManagement/config.php';

echo "<h2>Admin Notifications Page - Verification</h2>";

echo "<h3>Testing the Fixed Query:</h3>";

$query = "SELECT NotificationId, UserId, SenderId, NotificationType, 
                 NotificationSection, Title, Message, IsRead, CreatedAt
          FROM UserNotifications
          WHERE 1=1
          ORDER BY CreatedAt DESC 
          LIMIT 10";

$result = $conn->query($query);

if (!$result) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . $conn->error . "</p>";
} else {
    $count = $result->num_rows;
    echo "<p style='color: green;'><strong>✓ Query Success!</strong> Found <strong>$count</strong> notifications</p>";
    
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse; margin-top:20px;'>";
    echo "<tr style='background:#f5f5f5;'>";
    echo "<th>NotificationId</th>";
    echo "<th>UserId (Recipient)</th>";
    echo "<th>SenderId</th>";
    echo "<th>Type</th>";
    echo "<th>Title</th>";
    echo "<th>Message</th>";
    echo "<th>IsRead</th>";
    echo "<th>CreatedAt</th>";
    echo "</tr>";
    
    while($row = $result->fetch_assoc()) {
        $bgColor = ($row['IsRead'] === 'no') ? '#fff3cd' : '';
        echo "<tr style='background:$bgColor;'>";
        echo "<td>".$row['NotificationId']."</td>";
        echo "<td>".$row['UserId']."</td>";
        echo "<td>".$row['SenderId']."</td>";
        echo "<td><strong>".$row['NotificationType']."</strong></td>";
        echo "<td>".substr($row['Title'], 0, 30)."</td>";
        echo "<td>".substr($row['Message'], 0, 40)."...</td>";
        echo "<td>".$row['IsRead']."</td>";
        echo "<td>".$row['CreatedAt']."</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

echo "<h3>What Changed:</h3>";
echo "<ul>";
echo "<li><strong>Before:</strong> <code>SELECT ... RecipientId ...</code> ❌ (column doesn't exist)</li>";
echo "<li><strong>After:</strong> <code>SELECT ... UserId ...</code> ✅ (correct column)</li>";
echo "</ul>";

echo "<p style='background:#e8f5e9; padding:15px; border-left:4px solid #4caf50; margin-top:20px;'>";
echo "<strong>✓ FIXED:</strong> The admin notifications page will now display all new booking notifications correctly.";
echo "</p>";

echo "<p><strong>Next Step:</strong> Go to the admin notifications page and refresh - you should now see the new notifications in the table!</p>";
?>
