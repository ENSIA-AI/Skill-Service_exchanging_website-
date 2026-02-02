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
$currentUserExchange = null;

/**
 * Get user's exchange for a specific post
 */
function getUserExchangesForPost($postId, $userId) {
    global $conn;
    $sql = "SELECT ExchangeId, Status FROM Exchanges 
            WHERE PostId = ? AND RequestedByUserId = ?
            ORDER BY CreatedAt DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $postId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->num_rows > 0 ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row;
}

/**
 * Get button state based on exchange status
 */
function getButtonState($status) {
    $states = [
        'pending' => ['text' => 'Request Sent', 'class' => 'btn-pending', 'disabled' => true],
        'accepted' => ['text' => 'Request Accepted', 'class' => 'btn-accepted', 'disabled' => true],
        'rejected' => ['text' => 'Request Refused', 'class' => 'btn-refused', 'disabled' => true]
    ];
    return isset($states[$status]) ? $states[$status] : null;
}

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
            // Get payment method (form uses 'credits' or 'exchange')
            $paymentMethod = isset($_POST['selectedPaymentMethod']) ? $_POST['selectedPaymentMethod'] : 'credits';
            $isCreditPayment = ($paymentMethod === 'credits' || $paymentMethod === 'credit');
            $creditsCost = $isCreditPayment ? (int)$requiredCredits : 0;

            // If credit payment: validate requester has sufficient balance
            if ($creditsCost > 0) {
                $balSql = "SELECT CreditBalance FROM Users WHERE UserId = ?";
                $balStmt = $conn->prepare($balSql);
                $balStmt->bind_param('i', $userId);
                $balStmt->execute();
                $balResult = $balStmt->get_result()->fetch_assoc();
                $balStmt->close();
                if (!$balResult || (int)($balResult['CreditBalance'] ?? 0) < $creditsCost) {
                    $error = "Insufficient credits. You need $creditsCost credits to book this service.";
                }
            }

            if (empty($error)) {
            // Get requester's name
            $userNameSql = "SELECT FullName FROM Users WHERE UserId = ?";
            $userNameStmt = $conn->prepare($userNameSql);
            $userNameStmt->bind_param('i', $userId);
            $userNameStmt->execute();
            $currentUserData = $userNameStmt->get_result()->fetch_assoc();
            $requesterName = $currentUserData['FullName'] ?? 'Unknown User';
            $userNameStmt->close();
            
            // Insert new exchange (store CreditsCost for credit transfers when accepted)
            $sqlInsert = "INSERT INTO Exchanges (PostId, OfferedByUserId, RequestedByUserId, ProposedDate, CreditsCost) VALUES (?, ?, ?, ?, ?)";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param('iiisi', $postId, $postUserId, $userId, $selectedDate, $creditsCost);
            
            if ($stmtInsert->execute()) {
                try {
                    // Create notification message (exact approach as eventdetails)
                    if ($paymentMethod === 'exchange') {
                        $message = "$requesterName has requested to exchange skills for your $postTitle on " . date('M d, Y', strtotime($selectedDate));
                        $title = "Skill Exchange Request";
                    } else {
                        $message = "$requesterName has requested to pay $requiredCredits credits for your $postTitle on " . date('M d, Y', strtotime($selectedDate));
                        $title = "Booking Request";
                    }
                    
                    // Insert notification (exact pattern as teammate's eventdetails)
                    $insertQuery = "
                        INSERT INTO UserNotifications 
                        (UserId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection)
                        VALUES (?, 'booking', ?, ?, 'no', NOW(), 'Exchange')
                    ";
                    
                    $insertStmt = $conn->prepare($insertQuery);
                    if (!$insertStmt) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }
                    
                    $insertStmt->bind_param('iss', $postUserId, $title, $message);
                    
                    if ($insertStmt->execute()) {
                        $notificationId = $conn->insert_id;
                        $insertStmt->close();
                        
                        $success = "Your booking request has been submitted!";
                        $bookedDates[] = $selectedDate;
                        $currentUserExchange = getUserExchangesForPost($postId, $userId);
                    } else {
                        throw new Exception("Failed to create notification: " . $insertStmt->error);
                    }
                } catch (Exception $e) {
                    $error = "Server error: " . $e->getMessage();
                    if (isset($insertStmt) && $insertStmt) {
                        $insertStmt->close();
                    }
                }
            } else {
                $error = "Error submitting booking: " . $stmtInsert->error;
            }
            $stmtInsert->close();
            } // end if (empty($error))
        }
    }
}

// Check if current user has an existing exchange for this post
if (isset($_SESSION['user_id'])) {
    $currentUserExchange = getUserExchangesForPost($postId, $_SESSION['user_id']);
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