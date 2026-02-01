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
if (!isset($_GET['Postid'])||!is_numeric($_GET['Postid']))
{
      
    header('Location: ./posts.php');
    exit;
}

// 2. Validate Postid format
$postId = (int)$_GET['Postid'];
if ($postId <= 0) {
    header('Location: ./postdetails.php');
    exit;
}


//Now we can  Connect to database
require_once '../../DataBaseManagement/config.php';

$error = '';
$success = '';

$sql = "SELECT * FROM Posts WHERE PostId = ? ";
$stmt=$conn->prepare($sql);
if (!$stmt) {  die('Database error: ' . $conn->error);}

$stmt->bind_param('i',$postId);
$stmt->execute();
$result=$stmt->get_result();

if($result->num_rows===0)
{
    header('Location:/posts.php');
    exit;
}

//fetch post data 
$post=mysqli_fetch_assoc($result);

// Fetch user data for the post owner
$userSql = "SELECT FullName, ProfilePicture, Rating, RatingCount FROM Users WHERE UserId = ?";
$userStmt = $conn->prepare($userSql);
if (!$userStmt) {  
    die('Database error: ' . $conn->error); 
}
$userStmt->bind_param('i', $post['UserId']);
$userStmt->execute();
$userResult = $userStmt->get_result();

// Initialize $user with default values FIRST
$user = [
    'FullName' => 'Unknown', 
    'ProfilePicture' => '../../assets/images/Default_pfp.svg', 
    'Rating' => 0, 
    'RatingCount' => 0
];

// Then try to fetch from database
if ($userResult && $userResult->num_rows > 0) {
    $fetchedUser = mysqli_fetch_assoc($userResult);
    if ($fetchedUser) {
        $user = array_merge($user, $fetchedUser); // Merge with defaults
    }
}

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
$skillsResult = $skillsStmt->get_result();
$postSkills = [];
while ($skill = $skillsResult->fetch_assoc()) {
    $postSkills[] = $skill['SkillName'];
}

// Fetch skills being sought (from user preferences or post data)
$seekingSkillsSql = "SELECT s.SkillName, s.SkillId FROM UserSkills us 
                     JOIN Skills s ON us.SkillId = s.SkillId 
                     WHERE us.UserId = ? LIMIT 5";
$seekingStmt = $conn->prepare($seekingSkillsSql);
if (!$seekingStmt) { die('Database error: ' . $conn->error); }
$seekingStmt->bind_param('i', $postUserId);
$seekingStmt->execute();
$seekingResult = $seekingStmt->get_result();
$seekingSkills = [];
while ($skill = $seekingResult->fetch_assoc()) {
    $seekingSkills[] = $skill['SkillName'];
}


//array to store the available dates of the post owner

// Fetch available dates
$dates = [];
$Datesquery = "SELECT AvailableDate FROM PostAvailableDates WHERE PostId = ? ORDER BY AvailableDate ASC";
$stmtDates = $conn->prepare($Datesquery);
if (!$stmtDates) {  die('Database error: ' . $conn->error); }
$stmtDates->bind_param('i', $postId);
$stmtDates->execute();
$resultDates = $stmtDates->get_result();

while ($row = $resultDates->fetch_assoc()) {
    $dates[] = $row['AvailableDate'];
}

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
$resultEx = $stmtEx->get_result();
while ($ex = $resultEx->fetch_assoc()) {
    if ($ex['Status'] === 'accepted' || $ex['Status'] === 'completed') {
        $bookedDates[] = $ex['ProposedDate'];
    }
}

// Handle POST when user selects a date 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['SelectedDate'])) {
    // Check if user is logged in
    if (!isset($_SESSION['userId'])) {
        $error = "Please log in to book this service.";
    } else {
        $selectedDate = $_POST['SelectedDate'];
        $userId = $_SESSION['userId']; // current logged-in user

        // Validate user cannot book their own post
        if ($userId == $postUserId) {
            $error = "You cannot book your own post.";
        } elseif (in_array($selectedDate, $bookedDates)) {
            $error = "This date has already been booked.";
        } else {
            // Get current user's name for notification
            $userNameSql = "SELECT FullName FROM Users WHERE UserId = ?";
            $userNameStmt = $conn->prepare($userNameSql);
            if (!$userNameStmt) {
                $error = "Database error: " . $conn->error;
            } else {
                $userNameStmt->bind_param('i', $userId);
                $userNameStmt->execute();
                $userNameResult = $userNameStmt->get_result();
                $currentUserData = $userNameResult->fetch_assoc();
                $currentUserName = $currentUserData['FullName'] ?? 'Unknown User';
                $userNameStmt->close();
                
                // Get payment method from POST
                $paymentMethod = isset($_POST['selectedPaymentMethod']) ? $_POST['selectedPaymentMethod'] : 'credits';
                
                // Create notification message based on payment method
                if ($paymentMethod === 'exchange') {
                    $notificationMessage = "$currentUserName is requesting to exchange skills for your $postTitle course";
                    $notificationType = 'booking';
                    $notificationSection = 'Exchange';
                } else {
                    // Credits payment
                    $notificationMessage = "$currentUserName is requesting to pay $requiredCredits for your $postTitle course";
                    $notificationType = 'booking';
                    $notificationSection = 'Exchange';
                }
                
                // Insert new exchange
                $sqlInsert = "INSERT INTO Exchanges (PostId, OfferedByUserId, RequestedByUserId, ProposedDate) VALUES (?, ?, ?, ?)";
                $stmtInsert = $conn->prepare($sqlInsert);
                if (!$stmtInsert) {
                    $error = "Database error: " . $conn->error;
                } else {
                    $stmtInsert->bind_param('iiis', $postId, $postUserId, $userId, $selectedDate);
                    if ($stmtInsert->execute()) {
                        // Insert notification to post owner
                        $notificationTitle = $paymentMethod === 'exchange' ? 'Skill Exchange Request' : 'Booking Request';
                        $sqlNotification = "INSERT INTO UserNotifications (UserId, NotificationType, Title, Message, NotificationSection) VALUES (?, ?, ?, ?, ?)";
                        $stmtNotification = $conn->prepare($sqlNotification);
                        
                        if (!$stmtNotification) {
                            $error = "Notification error: " . $conn->error;
                        } else {
                            $stmtNotification->bind_param('issss', $postUserId, $notificationType, $notificationTitle, $notificationMessage, $notificationSection);
                            if ($stmtNotification->execute()) {
                                $success = "Your booking request has been submitted!";
                                $bookedDates[] = $selectedDate;
                            } else {
                                $error = "Error sending notification: " . $stmtNotification->error;
                            }
                            $stmtNotification->close();
                        }
                    } else {
                        $error = "Error submitting booking: " . $stmtInsert->error;
                    }
                    $stmtInsert->close();
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