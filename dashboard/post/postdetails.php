<?php
session_start();

// Initialize all variables with defaults
$error = '';
$success = '';
$post = [];
$user = ['FullName' => 'Unknown', 'ProfilePicture' => '../../assets/images/Default_pfp.svg', 'Rating' => 0, 'RatingCount' => 0];
$postTitle = '';
$postDescription = '';
$postType = '';
$postDuration = 0;
$postLocation = '';
$postDate = '';
$paymentMethod = '';
$requiredCredits = 0;
$prerequisites = '';
$requirements = '';
$postUserId = 0;
$datesByDay = [];
$bookedDates = [];
$weekDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

// Postid validity
// Postid validity (check both cases for robustness)
$rawPostId = $_GET['postid'] ?? $_GET['Postid'] ?? null;
if (!$rawPostId || !is_numeric($rawPostId))
{
    header('Location: posts.php');
    exit;
}

$postId = (int)$rawPostId;


//Now we can  Connect to database
require_once '../../DataBaseManagement/config.php';

$error = '';
$success = '';

$sql = "SELECT * FROM Posts WHERE PostId = ? ";
$stmt=$conn->prepare($sql);
if (!$stmt) {  die('Database error: ' . $conn->error);}

$stmt->bind_param('i',$postId);
$stmt->execute();
$stmt->bind_result($pi, $ui, $ti, $de, $pt, $ps, $ci, $du, $ml, $ad, $pm, $rc, $pr, $re, $lc, $ca);
if (!$stmt->fetch()) {
    $stmt->close();
    header('Location: posts.php');
    exit;
}

// Map fetched data to $post array for compatibility with existing code
$post = [
    'PostId' => $pi, 'UserId' => $ui, 'Title' => $ti, 'Description' => $de,
    'PostType' => $pt, 'PostStatus' => $ps, 'CategoryId' => $ci, 'Duration' => $du,
    'MeetLocation' => $ml, 'AvailableDate' => $ad, 'PaymentMethod' => $pm,
    'RequiredCredits' => $rc, 'Prerequisites' => $pr, 'Requirements' => $re,
    'LikeCount' => $lc, 'CreatedAt' => $ca
];
$stmt->close();

// Fetch user data for the post owner
$userSql = "SELECT FullName, ProfilePicture, Rating, RatingCount FROM Users WHERE UserId = ?";
$userStmt = $conn->prepare($userSql);
if (!$userStmt) {  
    die('Database error: ' . $conn->error); 
}
$userStmt->bind_param('i', $post['UserId']);
$userStmt->execute();
$userStmt->bind_result($fn, $pp, $ra, $rc);

if ($userStmt->fetch()) {
    $user['FullName'] = $fn;
    $user['ProfilePicture'] = $pp;
    $user['Rating'] = $ra;
    $user['RatingCount'] = $rc;
}
$userStmt->close();

$postTitle        = $post['Title'];
$postDescription  = $post['Description'];
$postType         = $post['PostType'];
$postDuration     = $post['Duration'];
$postLocation     = $post['MeetLocation'];
$postDate         = $post['AvailableDate'];
$paymentMethod    = $post['PaymentMethod'];
$requiredCredits  = $post['RequiredCredits'];
$prerequisites    = $post['Prerequisites'];
$requirements     = $post['Requirements'];
$postUserId       = $post['UserId'];

// Fetch skills offered by this post
$skillsSql = "SELECT s.SkillName, s.SkillId FROM PostSkills ps 
              JOIN Skills s ON ps.SkillId = s.SkillId 
              WHERE ps.PostId = ?";
$skillsStmt = $conn->prepare($skillsSql);
if (!$skillsStmt) { die('Database error: ' . $conn->error); }
$skillsStmt->bind_param('i', $postId);
$skillsStmt->execute();
$skillsStmt->bind_result($sn, $sid);
$postSkills = [];
while ($skillsStmt->fetch()) {
    $postSkills[] = $sn;
}
$skillsStmt->close();

// Fetch skills being sought (from user preferences or post data)
$seekingSkillsSql = "SELECT s.SkillName, s.SkillId FROM UserSkills us 
                     JOIN Skills s ON us.SkillId = s.SkillId 
                     WHERE us.UserId = ? LIMIT 5";
$seekingStmt = $conn->prepare($seekingSkillsSql);
if (!$seekingStmt) { die('Database error: ' . $conn->error); }
$seekingStmt->bind_param('i', $postUserId);
$seekingStmt->execute();
$seekingStmt->bind_result($ssn, $ssid);
$seekingSkills = [];
while ($seekingStmt->fetch()) {
    $seekingSkills[] = $ssn;
}
$seekingStmt->close();


//array to store the available dates of the post owner

// Fetch available dates
$dates = [];
$Datesquery = "SELECT AvailableDate FROM PostAvailableDates WHERE PostId = ? ORDER BY AvailableDate ASC";
$stmtDates = $conn->prepare($Datesquery);
if (!$stmtDates) {  die('Database error: ' . $conn->error); }
$stmtDates->bind_param('i', $postId);
$stmtDates->execute();
$stmtDates->bind_result($adate);
while ($stmtDates->fetch()) {
    $dates[] = $adate;
}
$stmtDates->close();

// NEW: Process dates into time ranges per day (like first image)
$timeSlotsByDay = [];

foreach ($dates as $datetime) {
    $dayName = date('l', strtotime($datetime)); // Day of the week
    $timeSlot = date('H:i', strtotime($datetime)); // Hour:Minute
    
    if (!isset($timeSlotsByDay[$dayName])) {
        $timeSlotsByDay[$dayName] = [];
    }
    $timeSlotsByDay[$dayName][] = $timeSlot;
}

// Find time ranges for each day
$dayRanges = [];
foreach ($timeSlotsByDay as $dayName => $times) {
    if (!empty($times)) {
        // Sort times
        sort($times);
        
        // Find earliest and latest time
        $earliest = $times[0];
        $latest = $times[count($times)-1];
        
        // Store as range
        $dayRanges[$dayName] = [
            'start' => $earliest,
            'end' => $latest,
            'has_slots' => true
        ];
    } else {
        $dayRanges[$dayName] = [
            'start' => '',
            'end' => '',
            'has_slots' => false
        ];
    }
}


// Fetch existing exchanges to know which dates are already booked/completed 
$sqlExchanges = "SELECT ProposedDate, Status FROM Exchanges WHERE PostId = ?";
$stmtEx = $conn->prepare($sqlExchanges);
if (!$stmtEx) {  die('Database error: ' . $conn->error); }
$stmtEx->bind_param('i', $postId);
$stmtEx->execute();
$stmtEx->bind_result($pdate, $estatus);
while ($stmtEx->fetch()) {
    if ($estatus === 'accepted' || $estatus === 'completed') {
        $bookedDates[] = $pdate;
    }
}
$stmtEx->close();

// Handle POST when user selects a date 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['SelectedDate'])) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $error = "Please log in to book this service.";
    } else {
        $selectedDate = $_POST['SelectedDate'];
        $userId = $_SESSION['user_id']; // current logged-in user

        // Validate user cannot book their own post
        if ($userId == $postUserId) {
            $error = "You cannot book your own post.";
        } elseif (in_array($selectedDate, $bookedDates)) {
            $error = "This date has already been booked.";
        } else {
            // Insert new exchange
            $sqlInsert = "INSERT INTO Exchanges (PostId, OfferedByUserId, RequestedByUserId, ProposedDate) VALUES (?, ?, ?, ?)";
            $stmtInsert = $conn->prepare($sqlInsert);
            if (!$stmtInsert) {
                $error = "Database error: " . $conn->error;
            } else {
                $stmtInsert->bind_param('iiis', $postId, $postUserId, $userId, $selectedDate);
                if ($stmtInsert->execute()) {
                    $success = "Your booking request has been submitted!";
                    $bookedDates[] = $selectedDate;
                } else {
                    $error = "Error submitting booking: " . $stmtInsert->error;
                }
            }
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swap</title>
    <link rel="icon" type="image/png" href="../../assets/images/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/postdetails.css">
</head>
<body>
    <?php include '../../components/header.html'; ?>
    <?php include '../../components/sidebar.html'; ?>
    <main class="php-content">
        <?php include './postdetails.view.php';?>
    </main>
        
        
</body>
</html>

<?php
// Close connection after everything is done
if (isset($stmt)) { $stmt->close(); }
if (isset($userStmt)) { $userStmt->close(); }
if (isset($stmtDates)) { $stmtDates->close(); }
if (isset($stmtEx)) { $stmtEx->close(); }
if (isset($stmtInsert)) { $stmtInsert->close(); }
if (isset($conn)) { $conn->close(); }
?>