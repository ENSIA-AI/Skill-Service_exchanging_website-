<?php

// Start session to get current user
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in, otherwise use test user 
if (!isset($_SESSION['userId']) || empty($_SESSION['userId'])) {
    // Development/Testing: Use default test user ID (1)
    // TODO: Remove this later after implementing it
    $organizerId = 1; // Default test user
} else {
    $organizerId = $_SESSION['userId'];
}

 require_once __DIR__  . "/../../DataBaseManagement/config.php";

$errors = [];
$data = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$event_title = $_POST['eventTitle'];
$event_start_date = $_POST['eventStartDate'];
$event_end_date = $_POST['eventEndDate'];
$event_type = $_POST['eventType'];
$event_location = $_POST['location'];
$event_description = $_POST['description'];
$event_max_attendees = $_POST['maxAttendees'];
$event_category = $_POST['category'];
$event_skills = isset($_POST['skills']) ? $_POST['skills'] : [];
$event_credit = isset($_POST['credit']) ? $_POST['credit'] : 0;

$data['title'] = validateRequired(
    $errors,
    'Event Title',
    $_POST['eventTitle'],
    5
);

// Description is optional - only validate if provided
$description_value = isset($_POST['description']) ? $_POST['description'] : '';
if (!empty(trim($description_value))) {
    $data['description'] = validateRequired(
        $errors,
        'Description',
        $description_value,
        20
    );
} else {
    $data['description'] = ['success' => true, 'valid_field_value' => ''];
}

$startDate = validateDate(
    $errors,
    'Start Date',
    $_POST['eventStartDate'],
    "Y-m-d\TH:i"
);

$endDate = validateDate(
    $errors,
    'End Date',
    $_POST['eventEndDate'],
    "Y-m-d\TH:i"
);


if ($startDate['success'] && $endDate['success']) {
    $data['event_dates'] = validateStartEndDates(
        $errors,
        "",
        $startDate['valid_field_value'],
        $endDate['valid_field_value']
    );
} else {
    $data['event_dates'] = ['success' => false, 'valid_start_value' => '', 'valid_finish_value' => ''];
}

$data['location'] = validateRequired(
    $errors,
    'Location',
    $_POST['location'],
    3
);


$data['type'] = validateEventType(
    $errors,
    $_POST['eventType']
);

// Max attendees is optional - only validate if provided
$max_attendees_value = isset($_POST['maxAttendees']) ? $_POST['maxAttendees'] : '';
if (!empty(trim($max_attendees_value))) {
    $data['max_attendees'] = validateNumber(
        $errors,
        'Max Attendees',
        $max_attendees_value,
        100,
        1
    );
} else {
    $data['max_attendees'] = ['success' => true, 'valid_field_value' => 100]; // Default value
}

// Validate category is selected
if (!isset($_POST['category']) || empty(trim($_POST['category']))) {
    $errors[] = "error: select category first";
    $category = ['success' => false, 'category_id' => null];
} else {
    $category = validateCategory(
        $errors,
        $conn,
        $_POST['category']
    );
}

$data['category'] = $category;

// Credit validation (optional - defaults to 0 if not provided)
$data['credit'] = validateCredit(
    $errors,
    isset($_POST['credit']) ? $_POST['credit'] : '0'
);

$event_skills = isset($_POST['skills']) ? $_POST['skills'] : [];


$data['skills'] = validateSkill(
    $errors,
    $conn,
    $event_skills,
    $category['category_id'] ?? null
);

if(!empty($errors)){
    header('Content-Type: application/json');
    http_response_code(400); // Bad Request
    echo json_encode([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $errors
    ]);
    exit;
}
else {
    try {
        $event_id = saveToDataBase($data, $conn, $organizerId);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Event created successfully',
            'event_id' => $event_id
        ]);
        exit;
        
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
        exit;
    }
}

//To validate required input fields
function validateRequired(&$errors, $field_name , $field_value, $required_chars){
    $field_value = trim($field_value);
    if(empty($field_value)){
        $errors[] = "error: $field_name is required";
        return ['success' => false , 'valid_field_value' => ''];
    }
    elseif(!preg_match('/[a-zA-Z0-9]/' , $field_value)){
        $errors[] = "error: $field_name must contain meaningful text";
        return ['success' => false , 'valid_field_value' => ''];
    }
    elseif(strlen($field_value) < $required_chars ){
        $errors[] = "error: $field_name must be at least $required_chars charachters long";
        return ['success' => false , 'valid_field_value' => ''];
    }
    else{
        return ['success' => true , 'valid_field_value' => $field_value];
    } 
}

//To validate the max number of attendees
function validateNumber(&$errors , $field_name , $field_value , $max , $min){
    $field_value = trim($field_value);
    if(empty($field_value)){
        $errors[] = "error: $field_name is required";
        return ['success' => false , 'valid_field_value' => ''];
    }
    elseif(!filter_var($field_value, FILTER_VALIDATE_INT ,['options' => ['min_range' => $min ,  'max_range'  => $max ]])){
        $errors[] = "error: $field_value is not in proper number format or is out of bound";
        return ['success' => false , 'valid_field_value' => ''];
    }
    else {
        $cleaned = filter_var($field_value , FILTER_SANITIZE_NUMBER_INT);
        return ['success' => true , 'valid_field_value' => $cleaned];
    }
}

//To validate the start date and end date of the events input
function validateDate(&$errors, $field_name, $field_value, $format = "Y-m-d\TH:i") {
    $field_value = trim($field_value);
    
    // Debug: Check what format we're getting
    error_log("Validating date: $field_value with format: $format");
    
    if (empty($field_value)) {
        $errors[] = "error: $field_name is required";
        return ['success' => false, 'valid_field_value' => ''];
    }
    
    // Handle timezone - with better error checking
    $timezone = 'UTC'; // default
    if (isset($_POST['timezone']) && in_array($_POST['timezone'], timezone_identifiers_list(), true)) {
        $timezone = $_POST['timezone'];
    }
    
    $user_time_zone = new DateTimeZone($timezone);
    
    // Create DateTime object
    $date = DateTime::createFromFormat($format, $field_value, $user_time_zone);
    
    // Check if creation was successful
    if (!$date) {
        $errors[] = "error: $field_name is not in correct format. Expected: $format";
        return ['success' => false, 'valid_field_value' => ''];
    }
    
    // Now it's safe to call setTimezone
    $date->setTimezone(new DateTimeZone('UTC'));
    $current = new DateTime('now', new DateTimeZone('UTC'));
    
    // Check if date is in the past
    if ($date <= $current) {
        $errors[] = "error: $field_name can't be in the past";
        return ['success' => false, 'valid_field_value' => ''];
    }
    
    // Check if date is more than 1 year in the future
    $one_year_later = clone $current;
    $one_year_later->modify('+1 year');
    
    if ($date > $one_year_later) {
        $errors[] = "error: $field_name can't be more than one year later";
        return ['success' => false, 'valid_field_value' => ''];
    }
    
    // Format for storage (MySQL DATETIME format)
    $cleaned = $date->format('Y-m-d H:i:s');
    return ['success' => true, 'valid_field_value' => $cleaned];
}

//To ensure end date after start date with less than one year duration and at least 15 minutes apart
function validateStartEndDates(&$errors , $format , $start_date_value , $finish_date_value ){

    // Parse dates from MySQL format (Y-m-d H:i:s)
    $start_date = DateTime::createFromFormat('Y-m-d H:i:s', $start_date_value);
    $finish_date = DateTime::createFromFormat('Y-m-d H:i:s', $finish_date_value);

    // Check if end date is after start date
    if($finish_date <= $start_date ){
        $errors[] = "error: end date must be after start date";
        return ['success' =>  false , 'valid_start_value' => '' , 'valid_finish_value' => ''];
    }
    
    // Check minimum 15 minutes apart
    $interval = $start_date->diff($finish_date);
    $minutes_apart = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
    
    if ($minutes_apart < 15) {
        $errors[] = "error: end date must be at least 15 minutes after start date";
        return ['success' =>  false , 'valid_start_value' => '' , 'valid_finish_value' => ''];
    }
    
    // Check if event lasts more than one year
    if($interval->days > 365){
        $errors[] = "error: event can't last for more than a year";
        return ['success' =>  false , 'valid_start_value' => '' , 'valid_finish_value' => ''];
    }
    
    // Return in MySQL format
    $cleaned_start = $start_date->format('Y-m-d H:i:s');
    $cleaned_finish = $finish_date->format('Y-m-d H:i:s');
    return ['success' =>  true , 'valid_start_value' => $cleaned_start , 'valid_finish_value' => $cleaned_finish];
}

function validateEventType(&$errors, $field_value){
    $field_value = trim($field_value);
    $allowed = ['in-person' , 'online'];
    if(!in_array($field_value , $allowed , true)){
        $errors[] = "error: event type is not one of the options";
        return ['success' =>  false , 'valid_field_value' => '' ];
    }
    else{
        return  ['success' =>  true , 'valid_field_value' => $field_value ];
    }
}

//To validate the credit field (optional, defaults to 0, range 0-100)
function validateCredit(&$errors, $field_value){
    $field_value = trim($field_value);
    
    // If empty, default to 0
    if(empty($field_value)){
        return ['success' => true , 'valid_field_value' => 0];
    }
    
    // Check if it's a valid integer
    if(!filter_var($field_value, FILTER_VALIDATE_INT)){
        $errors[] = "error: credits must be a whole number";
        return ['success' => false , 'valid_field_value' => 0];
    }
    
    $credit_int = (int)$field_value;
    
    // Check range 0-100
    if($credit_int < 0 || $credit_int > 100){
        $errors[] = "error: credits must be between 0 and 100";
        return ['success' => false , 'valid_field_value' => 0];
    }
    
    return ['success' => true , 'valid_field_value' => $credit_int];
}

function validateCategory(&$errors , mysqli $conn , $field_value){

    $field_value = trim($field_value);
    
    // Check if field_value is numeric (categoryid from form)
    if(!is_numeric($field_value)){
        $errors[] = "error: select category first";
        return ['success' => false , 'category_id' => null];
    }
    
    $category_id_int = (int)$field_value;
    
    $stmt = $conn->prepare("SELECT categoryid FROM category WHERE categoryid = ?");
    $stmt->bind_param("i" , $category_id_int);
    $stmt->execute();
    $stmt->store_result(); 

    $category_id = null;
    if($stmt->num_rows === 0){
        $errors[] = "error: selected category does not exist";
        $stmt->close();
        return ['success' => false , 'category_id' => null];
    }
    else{
        $stmt->bind_result($category_id); //store the found id in $category_id
        $stmt->fetch();
        $stmt->close();
        return ['success' => true , 'category_id' => $category_id];
    }   
}

function validateSkill(&$errors, mysqli $conn, array $skills, $category_id) {

    if ($category_id === null) {
        $errors[] = "error: select category first";
        return ['success' => false, 'skill_ids' => []];
    }
    
    // Clean and validate skills array
    $skills = array_filter(array_map('trim', $skills));
    
    // Check count
    $count = count($skills);
    if ($count < 1 || $count > 5) {
        $errors[] = "error: select between 1 and 5 skills";
        return ['success' => false, 'skill_ids' => []];
    }
    
    $skill_ids = [];
    $category_id = (int)$category_id;
    
    // Prepare statement - check by skill ID (not name)
    $stmt = $conn->prepare("SELECT skillid FROM skills WHERE skillid = ? AND categoryid = ?");
    
    foreach ($skills as $skill_id) {
        // Skip if not numeric
        if (!is_numeric($skill_id)) {
            $errors[] = "error: invalid skill ID: $skill_id";
            continue;
        }
        
        $skill_id_int = (int)$skill_id;
        $stmt->bind_param("ii", $skill_id_int, $category_id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows === 0) {
            $errors[] = "error: skill ID $skill_id does not exist or is not from selected category";
            continue;
        }
        
        $stmt->bind_result($valid_skill_id);
        $stmt->fetch();
        $skill_ids[] = $valid_skill_id;
    }
    $stmt->close();
    
    if (empty($skill_ids)) {
        $errors[] = "error: no valid skills selected";
        return ['success' => false, 'skill_ids' => []];
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'skill_ids' => []];
    }
    
    $skill_ids = array_unique($skill_ids);
    
    return ['success' => true, 'skill_ids' => $skill_ids];
}

function saveToDataBase($data, mysqli $conn, $organizerId) {

    $conn->begin_transaction();

    try {
        // Insert event
        $stmt = $conn->prepare("
            INSERT INTO events
            (OrganizerId, EventTitle, EventDescription, EventLocation, EventType, EventStartDate, EventEndDate, MaxAttendees, EventCost)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "issssssii",
            $organizerId,
            $data['title']['valid_field_value'],
            $data['description']['valid_field_value'],
            $data['location']['valid_field_value'],
            $data['type']['valid_field_value'],
            $data['event_dates']['valid_start_value'],
            $data['event_dates']['valid_finish_value'],
            $data['max_attendees']['valid_field_value'],
            $data['credit']['valid_field_value']
        );

        $stmt->execute();

        if ($stmt->affected_rows !== 1) {
            throw new Exception('Event insertion failed');
        }

        $event_id = $stmt->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("
            INSERT INTO eventskills (EventId, SkillId)
            VALUES (?, ?)
        ");

         foreach ($data['skills']['skill_ids'] as $skill_id) {
            $stmt->bind_param("ii", $event_id, $skill_id);
            $stmt->execute();

            if ($stmt->affected_rows !== 1) {
                throw new Exception("Event-skill insert failed for skill ID: $skill_id");
            }
        }

        $stmt->close();
        $conn->commit();

        return $event_id;

    } catch (Throwable $e) {
        $conn->rollback();
        throw $e;
    }
}
?>