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
// $postDate         = $post['AvailableDate']; // Column missing in some DB versions, handled by PostAvailableDates
$paymentMethod    = $post['PaymentMethod'];
$requiredCredits  = $post['RequiredCredits'];
$prerequisites    = $post['Prerequisites'];
$requirements     = $post['Requirements'];
$postUserId       = $post['UserId'];

// Fetch skills offered by this post
$skillsSql = "SELECT s.SkillName, s.SkillId FROM PostSkills ps 
              JOIN Skills s ON ps.SkillId = s.SkillId 
              WHERE ps.PostId = ? AND ps.SkillType = 'offered'";
$skillsStmt = $conn->prepare($skillsSql);
if (!$skillsStmt) { die('Database error: ' . $conn->error); }
$skillsStmt->bind_param('i', $postId);
$skillsStmt->execute();
$skillsResult = $skillsStmt->get_result();
$offeredSkills = [];
while ($skill = $skillsResult->fetch_assoc()) {
    $offeredSkills[] = $skill['SkillName'];
}

// Fetch skills being sought (Targeted Skills)
$seekingSkillsSql = "SELECT s.SkillName, s.SkillId FROM PostSkills ps 
                     JOIN Skills s ON ps.SkillId = s.SkillId 
                     WHERE ps.PostId = ? AND ps.SkillType = 'requested'";
$seekingStmt = $conn->prepare($seekingSkillsSql);
if (!$seekingStmt) { die('Database error: ' . $conn->error); }
$seekingStmt->bind_param('i', $postId);
$seekingStmt->execute();
$seekingResult = $seekingStmt->get_result();
$requestedSkills = [];
while ($skill = $seekingResult->fetch_assoc()) {
    $requestedSkills[] = $skill['SkillName'];
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
$postDate = !empty($dates) ? $dates[0] : '';

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
        
        // If only one time slot or start equals end, add the post duration to create end time
        if ($earliest === $latest && isset($postDuration) && $postDuration > 0) {
            // Add duration to the start time to get end time
            $endDateTime = strtotime($earliest) + ($postDuration * 60); // duration is in minutes
            $latest = date('H:i', $endDateTime);
        }
        
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
    // Debug logging - write to a file we can check
    $debugLog = "\n=== BOOKING POST DEBUG " . date('Y-m-d H:i:s') . " ===\n";
    $debugLog .= "POST Data: " . print_r($_POST, true) . "\n";
    $debugLog .= "Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
    file_put_contents(__DIR__ . '/booking_debug.log', $debugLog, FILE_APPEND);
    
    error_log("POST received - SelectedDate: " . $_POST['SelectedDate']);
    error_log("POST selectedPaymentMethod: " . ($_POST['selectedPaymentMethod'] ?? 'NOT SET'));
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        $error = "Please log in to book this service.";
        file_put_contents(__DIR__ . '/booking_debug.log', "ERROR: User not logged in\n", FILE_APPEND);
    } else {
        $selectedDate = $_POST['SelectedDate'];
        $userId = $_SESSION['user_id'];  // Use user_id, not userId
        
        file_put_contents(__DIR__ . '/booking_debug.log', "User logged in: UserId=$userId, PostOwner=$postUserId\n", FILE_APPEND);

        // Validate user cannot book their own post
        if ($userId == $postUserId) {
            $error = "You cannot book your own post.";
            file_put_contents(__DIR__ . '/booking_debug.log', "ERROR: User trying to book own post (UserId=$userId, PostOwner=$postUserId)\n", FILE_APPEND);
        } elseif (in_array($selectedDate, $bookedDates)) {
            $error = "This date has already been booked.";
            file_put_contents(__DIR__ . '/booking_debug.log', "ERROR: Date already booked: $selectedDate\n", FILE_APPEND);
        } else {
            // Get payment method (form uses 'credits' or 'exchange')
            $paymentMethod = isset($_POST['selectedPaymentMethod']) ? $_POST['selectedPaymentMethod'] : 'credits';
            $isCreditPayment = ($paymentMethod === 'credits' || $paymentMethod === 'credit');
            $creditsCost = $isCreditPayment ? (int)$requiredCredits : 0;
            
            file_put_contents(__DIR__ . '/booking_debug.log', "Payment: method=$paymentMethod, isCreditPayment=" . ($isCreditPayment?'yes':'no') . ", creditsCost=$creditsCost, requiredCredits=$requiredCredits\n", FILE_APPEND);

            // If credit payment: validate requester has sufficient balance
            if ($creditsCost > 0) {
                $balSql = "SELECT CreditBalance FROM Users WHERE UserId = ?";
                $balStmt = $conn->prepare($balSql);
                $balStmt->bind_param('i', $userId);
                $balStmt->execute();
                $balResult = $balStmt->get_result()->fetch_assoc();
                $balStmt->close();
                
                $userBalance = (int)($balResult['CreditBalance'] ?? 0);
                file_put_contents(__DIR__ . '/booking_debug.log', "Credit check: User balance=$userBalance, Required=$creditsCost\n", FILE_APPEND);
                
                if (!$balResult || $userBalance < $creditsCost) {
                    $error = "Insufficient credits. You need $creditsCost credits to book this service.";
                    file_put_contents(__DIR__ . '/booking_debug.log', "ERROR: Insufficient credits (has $userBalance, needs $creditsCost)\n", FILE_APPEND);
                }
            }

            if (empty($error)) {
            // Log: entering success path
            file_put_contents(__DIR__ . '/booking_debug.log', "No errors, proceeding with booking...\n", FILE_APPEND);
            
            // Get requester's name
            $userNameSql = "SELECT FullName FROM Users WHERE UserId = ?";
            $userNameStmt = $conn->prepare($userNameSql);
            $userNameStmt->bind_param('i', $userId);
            $userNameStmt->execute();
            $currentUserData = $userNameStmt->get_result()->fetch_assoc();
            $requesterName = $currentUserData['FullName'] ?? 'Unknown User';
            $userNameStmt->close();
            
            file_put_contents(__DIR__ . '/booking_debug.log', "Requester: $requesterName\n", FILE_APPEND);
            
            // Insert new exchange (store CreditsCost for credit transfers when accepted)
            $sqlInsert = "INSERT INTO Exchanges (PostId, OfferedByUserId, RequestedByUserId, ProposedDate, CreditsCost) VALUES (?, ?, ?, ?, ?)";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param('iiisi', $postId, $postUserId, $userId, $selectedDate, $creditsCost);
            
            file_put_contents(__DIR__ . '/booking_debug.log', "About to insert Exchange... PostId=$postId, Owner=$postUserId, Requester=$userId, Credits=$creditsCost\n", FILE_APPEND);
            
            if ($stmtInsert->execute()) {
                $exchangeId = $conn->insert_id;
                $stmtInsert->close();
                
                file_put_contents(__DIR__ . '/booking_debug.log', "Exchange created: ID=$exchangeId\n", FILE_APPEND);
                
                // Format time slot display (from time to time based on duration)
                $startTime = date('H:i', strtotime($selectedDate));
                $endTime = date('H:i', strtotime($selectedDate . ' + ' . $postDuration . ' minutes'));
                $dateFormatted = date('M d, Y', strtotime($selectedDate));
                $timeSlotDisplay = "$startTime to $endTime on $dateFormatted";
                
                // Create notification message based on payment method
                if ($paymentMethod === 'exchange') {
                    $message = "$requesterName has requested to exchange skills for your \"$postTitle\" session ($timeSlotDisplay)";
                    $title = "Skill Exchange Request";
                } else {
                    $message = "$requesterName has requested to book your \"$postTitle\" session for $requiredCredits credits ($timeSlotDisplay)";
                    $title = "Booking Request";
                }
                
                file_put_contents(__DIR__ . '/booking_debug.log', "Payment method: $paymentMethod\nNotification title: $title\nNotification message: $message\nRecipient: $postUserId, Sender: $userId\n", FILE_APPEND);
                
                // Insert notification with exception handling for trigger validation
                try {
                    $insertQuery = "INSERT INTO UserNotifications 
                        (RecipientId, SenderId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection)
                        VALUES (?, ?, 'booking', ?, ?, 'no', NOW(), 'Exchange')";
                    
                    file_put_contents(__DIR__ . '/booking_debug.log', "Preparing notification insert...\n", FILE_APPEND);
                    
                    $notifStmt = $conn->prepare($insertQuery);
                    if ($notifStmt) {
                        file_put_contents(__DIR__ . '/booking_debug.log', "Binding params: RecipientId=$postUserId, SenderId=$userId, Title=".substr($title, 0, 30).", Message=".substr($message, 0, 30)."\n", FILE_APPEND);
                        $notifStmt->bind_param('iiss', $postUserId, $userId, $title, $message);
                        
                        if ($notifStmt->execute()) {
                            $notificationId = $conn->insert_id;
                            if ($notificationId > 0) {
                                file_put_contents(__DIR__ . '/booking_debug.log', "✓ Notification created: ID=$notificationId\n", FILE_APPEND);
                            } else {
                                file_put_contents(__DIR__ . '/booking_debug.log', "⚠ Execute succeeded but no insert_id returned\n", FILE_APPEND);
                            }
                        } else {
                            $errorMsg = "✗ EXECUTE FAILED: " . $notifStmt->error . "\n";
                            error_log($errorMsg);
                            file_put_contents(__DIR__ . '/booking_debug.log', $errorMsg, FILE_APPEND);
                        }
                        $notifStmt->close();
                    } else {
                        $errorMsg = "✗ Failed to prepare notification: " . $conn->error . "\n";
                        error_log($errorMsg);
                        file_put_contents(__DIR__ . '/booking_debug.log', $errorMsg, FILE_APPEND);
                    }
                } catch (mysqli_sql_exception $e) {
                    // Handle database trigger rejections gracefully
                    $errorMsg = "✗ Notification insert failed (trigger rejection):\n";
                    $errorMsg .= "   Message: " . $e->getMessage() . "\n";
                    $errorMsg .= "   Code: " . $e->getCode() . "\n";
                    error_log($errorMsg);
                    file_put_contents(__DIR__ . '/booking_debug.log', $errorMsg, FILE_APPEND);
                }
                
                $success = "Your booking request has been submitted!";
                
                // Store success message in session and redirect to prevent form resubmission
                $_SESSION['booking_success'] = true;
                header("Location: postdetails.php?Postid=$postId");
                exit();
            } else {
                $error = "Error submitting booking: " . $stmtInsert->error;
            }
            // Close the exchange insert statement
            if (isset($stmtInsert) && $stmtInsert) {
                $stmtInsert->close();
                unset($stmtInsert); // Prevent double-close at end of file
            }
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
    <link rel="stylesheet" href="../../assets/css/postdetails.css?v=<?php echo time(); ?>">
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
// Note: Only close statements that haven't been closed elsewhere
if (isset($stmt) && $stmt instanceof mysqli_stmt) { @$stmt->close(); }
if (isset($userStmt) && $userStmt instanceof mysqli_stmt) { @$userStmt->close(); }
if (isset($stmtDates) && $stmtDates instanceof mysqli_stmt) { @$stmtDates->close(); }
if (isset($stmtEx) && $stmtEx instanceof mysqli_stmt) { @$stmtEx->close(); }
if (isset($skillsStmt) && $skillsStmt instanceof mysqli_stmt) { @$skillsStmt->close(); }
if (isset($seekingStmt) && $seekingStmt instanceof mysqli_stmt) { @$seekingStmt->close(); }
if (isset($conn) && $conn instanceof mysqli) { @$conn->close(); }
?>