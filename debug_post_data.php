<?php
/**
 * Debug Post Data
 * Check what values are actually in the database for a specific post
 */

require_once 'DataBaseManagement/config.php';

// Get post ID from URL
$postId = isset($_GET['postid']) ? (int)$_GET['postid'] : 1;

$sql = "SELECT PostId, Title, MeetLocation, RequiredCredits, Prerequisites, Requirements 
        FROM Posts WHERE PostId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $postId);
$stmt->execute();
$result = $stmt->get_result();

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Post Data Debug</title>";
echo "<style>
    body { 
        font-family: 'Segoe UI', Arial, sans-serif; 
        max-width: 900px; 
        margin: 50px auto; 
        padding: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .debug-box {
        background: rgba(255, 255, 255, 0.1);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
    }
    h1 { margin-top: 0; }
    table {
        width: 100%;
        border-collapse: collapse;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
        overflow: hidden;
    }
    th, td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    th {
        background: rgba(0, 0, 0, 0.2);
        font-weight: bold;
    }
    .null-value { 
        color: #ff6b6b; 
        font-style: italic;
    }
    .empty-string { 
        color: #ffd93d; 
        font-style: italic;
    }
    .has-value { 
        color: #51cf66; 
    }
    .form-box {
        margin-top: 20px;
        background: rgba(255, 255, 255, 0.05);
        padding: 20px;
        border-radius: 10px;
    }
    input {
        padding: 10px;
        border-radius: 5px;
        border: none;
        width: 150px;
        margin-right: 10px;
    }
    button {
        padding: 10px 20px;
        background: white;
        color: #667eea;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
    }
    button:hover {
        transform: scale(1.05);
    }
    a {
        display: inline-block;
        margin-top: 20px;
        padding: 12px 25px;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        text-decoration: none;
        border-radius: 25px;
        font-weight: bold;
    }
</style>";
echo "</head><body>";
echo "<div class='debug-box'>";
echo "<h1>🔍 Post Data Debug</h1>";

if ($result->num_rows > 0) {
    $post = $result->fetch_assoc();
    
    echo "<table>";
    echo "<tr><th>Field</th><th>Value</th><th>Type</th></tr>";
    
    foreach ($post as $field => $value) {
        $displayValue = '';
        $cssClass = '';
        $typeInfo = '';
        
        if ($value === null) {
            $displayValue = 'NULL';
            $cssClass = 'null-value';
            $typeInfo = '(Database NULL)';
        } elseif ($value === '') {
            $displayValue = '(Empty String)';
            $cssClass = 'empty-string';
            $typeInfo = '(Empty but not NULL)';
        } else {
            $displayValue = htmlspecialchars($value);
            $cssClass = 'has-value';
            $typeInfo = '(Has value)';
        }
        
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($field) . "</strong></td>";
        echo "<td class='$cssClass'>$displayValue</td>";
        echo "<td class='$cssClass'>$typeInfo</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p class='null-value'>❌ Post with ID $postId not found!</p>";
}

echo "<div class='form-box'>";
echo "<h3>Check Another Post</h3>";
echo "<form method='GET'>";
echo "<input type='number' name='postid' value='$postId' placeholder='Post ID'>";
echo "<button type='submit'>Check Post</button>";
echo "</form>";
echo "</div>";

echo "<a href='dashboard/post/postdetails.php?Postid=$postId'>View Post Details Page</a>";
echo "</div>";
echo "</body></html>";

$stmt->close();
$conn->close();
?>
