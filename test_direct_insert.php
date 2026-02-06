<?php
require 'DataBaseManagement/config.php';

echo "<h1>Testing UserNotifications INSERT</h1>";

// First, check the table structure
echo "<h2>Table Structure:</h2>";
$result = $conn->query('DESCRIBE UserNotifications');
echo "<table border='1' cellpadding='10'>";
echo "<tr style='background:#f5f5f5;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td><strong>".$row['Field']."</strong></td>";
    echo "<td>".$row['Type']."</td>";
    echo "<td>".$row['Null']."</td>";
    echo "<td>".$row['Key']."</td>";
    echo "<td>".$row['Default']."</td>";
    echo "</tr>";
}
echo "</table>";

// Test a direct INSERT
echo "<h2>Test INSERT (direct query):</h2>";

$testRecipientId = 1;  // Admin user
$testSenderId = 2;     // Another user
$testTitle = "Test Notification";
$testMessage = "This is a test notification message";

$query = "INSERT INTO UserNotifications 
    (RecipientId, SenderId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection)
    VALUES ($testRecipientId, $testSenderId, 'booking', '$testTitle', '$testMessage', 'no', NOW(), 'Exchange')";

echo "<p><strong>Query:</strong></p>";
echo "<pre style='background:#f0f0f0; padding:10px;'>$query</pre>";

if ($conn->query($query)) {
    $newId = $conn->insert_id;
    echo "<p style='color:green;'><strong>✓ SUCCESS:</strong> Notification inserted with ID=$newId</p>";
    
    // Verify it was inserted
    $verify = $conn->query("SELECT * FROM UserNotifications WHERE NotificationId = $newId");
    if ($row = $verify->fetch_assoc()) {
        echo "<p><strong>Verification:</strong></p>";
        echo "<pre style='background:#f0f0f0; padding:10px;'>";
        print_r($row);
        echo "</pre>";
    }
} else {
    echo "<p style='color:red;'><strong>✗ ERROR:</strong> " . $conn->error . "</p>";
}

// Test with prepared statement
echo "<h2>Test INSERT (prepared statement):</h2>";

$testRecipientId2 = 1;
$testSenderId2 = 3;
$testTitle2 = "Test Notification 2";
$testMessage2 = "This is another test";

$stmt = $conn->prepare("INSERT INTO UserNotifications 
    (RecipientId, SenderId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection)
    VALUES (?, ?, 'booking', ?, ?, 'no', NOW(), 'Exchange')");

if (!$stmt) {
    echo "<p style='color:red;'><strong>✗ PREPARE ERROR:</strong> " . $conn->error . "</p>";
} else {
    echo "<p>Binding: RecipientId=$testRecipientId2 (i), SenderId=$testSenderId2 (i), Title='$testTitle2' (s), Message='$testMessage2' (s)</p>";
    
    $stmt->bind_param('iiss', $testRecipientId2, $testSenderId2, $testTitle2, $testMessage2);
    
    if ($stmt->execute()) {
        $newId2 = $conn->insert_id;
        echo "<p style='color:green;'><strong>✓ SUCCESS:</strong> Notification inserted with ID=$newId2</p>";
    } else {
        echo "<p style='color:red;'><strong>✗ EXECUTE ERROR:</strong> " . $stmt->error . "</p>";
    }
    
    $stmt->close();
}

echo "<h2>Recent Notifications in Database:</h2>";
$recent = $conn->query("SELECT NotificationId, RecipientId, SenderId, NotificationType, Title, IsRead, CreatedAt FROM UserNotifications ORDER BY NotificationId DESC LIMIT 5");
echo "<table border='1' cellpadding='10'>";
echo "<tr style='background:#f5f5f5;'><th>NotifId</th><th>RecipientId</th><th>SenderId</th><th>Type</th><th>Title</th><th>IsRead</th><th>CreatedAt</th></tr>";
while($row = $recent->fetch_assoc()) {
    echo "<tr>";
    echo "<td>".$row['NotificationId']."</td>";
    echo "<td>".$row['RecipientId']."</td>";
    echo "<td>".$row['SenderId']."</td>";
    echo "<td>".$row['NotificationType']."</td>";
    echo "<td>".substr($row['Title'], 0, 30)."</td>";
    echo "<td>".$row['IsRead']."</td>";
    echo "<td>".$row['CreatedAt']."</td>";
    echo "</tr>";
}
echo "</table>";
?>
