<?php


// Postid validity
if (!isset($_GET['Postid'])||!is_numeric($_GET['Postid']))
{
      
    header('Location:/posts.php');
    exit;
}

// 2. Validate Postid format
$postId = (int)$_GET['Postid'];
if ($postId <= 0) {
    header('Location:/posts.php');
    exit;
}


//Now we can  Connect to database
require_once '../../DataBaseManagement/config.php';

$sql = "SELECT * FROM Posts WHERE PostId = ? ";
$stmt=$conn->prepare($sql);
if (!$stmt) {  die('Database error');}

//bind parameters 
$stmt->bind_param('i',$postId);

//Execute
$stmt->execute();

//getting the result 
$result=$stmt->get_result();

if($result->num_rows===0)
{
    header('Location:/posts.php');
    exit;
}

//fetch post data 
$post=mysqli_fetch_assoc($result);


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

//array to store the available dates of the post owner
$dates = [];
$Datesquery= "SELECT AvailableDate FROM PostAvailableDates WHERE PostId = ? ORDER BY AvailableDate ASC";

$stmtDates = $conn->prepare($Datesquery);
$stmtDates->bind_param('i', $postId);
$stmtDates->execute();
$resultDates = $stmtDates->get_result();

while ($row = $resultDates->fetch_assoc())
 {
    $dates[] = $row['AvailableDate'];
}

$weekDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

// Initialize array for all 7 days
$datesByDay = [];
foreach ($weekDays as $day) {
    $datesByDay[$day] = [];
}

// Fill the array
foreach ($dates as $datetime) {
    $dayName = date('l', strtotime($datetime)); // Day of the week
    $timeSlot = date('H:i', strtotime($datetime)); // Hour:Minute
    
    // Store both the full datetime and the time for later use
    $datesByDay[$dayName][] = [
        'datetime' => $datetime, // full datetime (needed for booking & comparison)
        'time' => $timeSlot      // display to user
    ];
}




// Fetch existing exchanges to know which dates are already booked/completed 
$sqlExchanges = "SELECT ProposedDate, Status FROM Exchanges WHERE PostId = ?";
$stmtEx = $conn->prepare($sqlExchanges);
$stmtEx->bind_param('i', $postId);
$stmtEx->execute();
$resultEx = $stmtEx->get_result();
$bookedDates = [];
while ($ex = $resultEx->fetch_assoc()) {
    if ($ex['Status'] === 'accepted' || $ex['Status'] === 'completed') {
        $bookedDates[] = $ex['ProposedDate'];
    }
}

// Handle POST when user selects a date 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['SelectedDate'])) {
    $selectedDate = $_POST['SelectedDate'];
    $userId = $_SESSION['userId']; // current logged-in user


    // Validate user cannot book their own post
    if ($userId == $postUserId) {
        $error = "You cannot book your own post.";
    } elseif (in_array($selectedDate, $bookedDates)) {
        $error = "This date has already been booked.";
    } else {
        // Insert new exchange
        $sqlInsert = "INSERT INTO Exchanges (PostId, OfferedByUserId, RequestedByUserId, ProposedDate) VALUES (?, ?, ?, ?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param('iiis', $postId, $postUserId, $userId, $selectedDate);
        $stmtInsert->execute();

        $success = "Your booking request has been submitted!";
        // Optionally refresh $bookedDates to disable clicked date
        $bookedDates[] = $selectedDate;
    }
}




$stmt->close();
$conn->close();



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swap</title>
    <link rel="icon" type="image/png" href="../../assets/images/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php include '../../components/header.html'; ?>
    <?php include '../../components/sidebar.html'; ?>
    <main class="php-content">
        <?php include './postdetails.view.php';?>
    </main>
        
        
</body>
</html>