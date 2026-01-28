<?php
/**
 * Test API Endpoint
 * Navigate to: http://localhost/yourproject/dashboard/events/test_api.php
 */

require_once '../../DataBaseManagement/config.php';

echo "<h1>Events API Test</h1>";
echo "<hr>";

// Test 1: Check database connection
echo "<h2>1. Database Connection</h2>";
if ($conn->connect_error) {
    echo "<p style='color:red;'>❌ Connection failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color:green;'>✅ Connected successfully</p>";
}

// Test 2: Check if Events table exists and has data
echo "<h2>2. Events Table Check</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM Events");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p style='color:green;'>✅ Events table found - Total events: <strong>" . $row['count'] . "</strong></p>";
    
    if ($row['count'] == 0) {
        echo "<p style='color:orange;'>⚠️ WARNING: No events in the database yet. Please import the SQL script.</p>";
    }
} else {
    echo "<p style='color:red;'>❌ Error querying Events table: " . $conn->error . "</p>";
}

// Test 3: Check Users table
echo "<h2>3. Users Table Check</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM Users");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p style='color:green;'>✅ Users table found - Total users: <strong>" . $row['count'] . "</strong></p>";
} else {
    echo "<p style='color:red;'>❌ Error querying Users table: " . $conn->error . "</p>";
}

// Test 4: Test the API query
echo "<h2>4. API Query Test</h2>";
$query = "SELECT e.EventId, e.EventTitle, e.EventLocation, u.UserName as organizer 
          FROM Events e
          LEFT JOIN Users u ON e.OrganizerId = u.UserId
          WHERE e.EventStatus IN ('upcoming', 'ongoing')
          ORDER BY e.EventStartDate ASC
          LIMIT 5";

$result = $conn->query($query);
if ($result) {
    $count = $result->num_rows;
    echo "<p style='color:green;'>✅ Query successful - Found <strong>" . $count . "</strong> events</p>";
    
    if ($count > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>EventId</th><th>Title</th><th>Location</th><th>Organizer</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['EventId'] . "</td>";
            echo "<td>" . $row['EventTitle'] . "</td>";
            echo "<td>" . $row['EventLocation'] . "</td>";
            echo "<td>" . ($row['organizer'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color:red;'>❌ Query failed: " . $conn->error . "</p>";
}

// Test 5: Call the actual API
echo "<h2>5. Actual API Call Test</h2>";
echo "<p>Testing: <code>getEventsAPI.php?page=1&limit=5</code></p>";
$api_url = 'getEventsAPI.php?page=1&limit=5';
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode == 200) {
    echo "<p style='color:green;'>✅ API returned status 200</p>";
    $data = json_decode($response, true);
    if ($data && isset($data['success'])) {
        if ($data['success']) {
            echo "<p style='color:green;'>✅ API returned success=true</p>";
            echo "<p>Events returned: <strong>" . count($data['data']) . "</strong></p>";
            echo "<p>Total events in DB: <strong>" . $data['pagination']['totalEvents'] . "</strong></p>";
        } else {
            echo "<p style='color:red;'>❌ API returned success=false</p>";
            echo "<p>Error: " . ($data['error'] ?? 'Unknown error') . "</p>";
        }
    } else {
        echo "<p style='color:red;'>❌ Invalid JSON response</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
} else {
    echo "<p style='color:red;'>❌ API returned status " . $httpcode . "</p>";
}

$conn->close();
?>
