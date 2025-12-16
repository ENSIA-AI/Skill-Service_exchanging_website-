<?php

 require_once '../../DataBaseManagement/config.php';

$errors = [];
$data = [];

$event_title = $_POST['eventTitle'];
$event_start_date = $_POST['eventStartDate'];
$event_end_date = $_POST['eventEndDate'];
$event_type = $_POST['eventType'];
$event_location = $_POST['location'];
$event_description = $_POST['description'];
$event_max_attendees = $_POST['maxAttendees'];
$event_category = $_POST['category'];
$event_skill = $_POST['skill'];

$data['title'] = validateRequired(
    $errors,
    'Event Title',
    $_POST['eventTitle'],
    5
);

$data['description'] = validateRequired(
    $errors,
    'Description',
    $_POST['description'],
    20
);

$startDate = validateDate(
    $errors,
    'Start Date',
    $_POST['eventStartDate'],
    "d-m-Y H:i"
);

$endDate = validateDate(
    $errors,
    'End Date',
    $_POST['eventEndDate'],
    "d-m-Y H:i"
);


if ($startDate['success'] && $endDate['success']) {
    $data['event_dates'] = validateStartEndDates(
        $errors,
        "d-m-Y H:i",
        $startDate['valid_field_value'],
        $endDate['valid_field_value']
    );
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

$data['max_attendees'] = validateNumber(
    $errors,
    'Max Attendees',
    $_POST['maxAttendees'],
    100,
    1
);

$category = validateCategory(
    $errors,
    $conn,
    $_POST['category']
);

$data['category'] = $category;

$data['skills'] = validateSkill(
    $errors,
    $conn,
    $_POST['skill'],
    $category['category_id'] ?? null
);

if(!empty($errors)){
    //ajax code
}
else {
    saveToDataBase($data , $conn);
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
function validateDate(&$errors, $field_name , $field_value, $format = "d-m-Y H:i"){

    $field_value = trim($field_value);
    if(empty($_POST['timezone']) || !in_array($_POST['timezone'], timezone_identifiers_list(),true)){
        $errors[] = "error: {$_POST['timezone']}  is not a valid time zone";
        return ['success' =>  false , 'valid_field_value' => ''];
    }
    else $user_time_zone = new DateTimeZone($_POST['timezone']);

    $date = DateTime::createFromFormat($format , $field_value , $user_time_zone);
    $date->setTimezone( new DateTimeZone('UTC'));
    $current = new DateTime('now' , new DateTimeZone('UTC'));

    if(empty(trim($field_value))){
        $errors[] = "error: $field_name is required";
        return ['success' => false , 'valid_field_value' => ''];
    }

    elseif( !$date || $date->format($format) !== $field_value){
        $errors[] = "error: date is not in correct format";
        return ['success' =>  false , 'valid_field_value' => ''];
    }

    elseif($date <= $current){
        $errors[] = "error: $field_name can't be in the past";
        return ['success' =>  false , 'valid_field_value' => ''];
    }

    elseif($current->diff($date)->days > 365){
        $errors[] = "error: $field_name can't be more than one year later";
        return ['success' =>  false , 'valid_field_value' => ''];
    }

    else{
        $cleaned = $date->format($format);
        return ['success' =>  true , 'valid_field_value' => $cleaned];
    } 

}

//To ensure end date after start date with less than one year duration 
function validateStartEndDates(&$errors , $format , $start_date_value , $finish_date_value ){

    $user_time_zone = new DateTimeZone($_POST['timezone']);
    $start_date = DateTime::createFromFormat($format , $start_date_value , $user_time_zone);
    $finish_date = DateTime::createFromFormat($format , $finish_date_value , $user_time_zone);

    if($finish_date <= $start_date ){
        $errors[] = "error: finish date can't be before start date";
        return ['success' =>  false , 'valid_start_value' => '' , 'valid_finish_value' => ''];
    }
    elseif($finish_date->diff($start_date)->days > 365){
        $errors[] = "error: event can't last for more than a year";
        return ['success' =>  false , 'valid_start_value' => '' , 'valid_finish_value' => ''];
    }
    else{
        $cleaned_start = $start_date->format($format);
        $cleaned_finish = $finish_date->format($format);
        return ['success' =>  true , 'valid_start_value' => $cleaned_start , 'valid_finish_value' => $cleaned_finish];
    }
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

function validateCategory(&$errors , mysqli $conn , $field_value){

    $field_value = trim($field_value);
    $stmt = $conn->prepare("SELECT categoryid FROM category WHERE categoryname = ?");
    $stmt->bind_param("s" , $field_value);
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

function validateSkill(&$errors , mysqli $conn , array $skills , $category_id){

    if($category_id === null){
        $errors[] = "error: select category first";
        return ['success' => false , 'skill_id' => null];
        }
    $skills = array_map('trim', $skills);
    $count = count($skills);
    if ($count < 1 || $count > 5) {
        $errors[] = "error: select between 1 and 5 skills";
        return ['success' => false, 'skill_ids' => []];
    }

    $skill_ids = [];
    $category_id = trim($category_id);
    $stmt = $conn->prepare("SELECT skillid FROM skills WHERE skillname = ? AND categoryid = ?");

    foreach ($skills as $skill_name) {
    $stmt->bind_param("si" , $skill_name , $category_id);
    $stmt->execute();
    $stmt->store_result();

    $skill_id = NULL;
    if($stmt->num_rows === 0){
        $errors[] = "error: skill does not exist or skill is not from selected category";
        continue;
    }
    
        $stmt->bind_result($skill_id);
        $stmt->fetch();
        $skill_ids[] = $skill_id;
    }
    $stmt->close();
    
    if (!empty($errors)) {
        return ['success' => false, 'skill_ids' => []];
    }

    $skill_ids = array_unique($skill_ids);

    return ['success' => true, 'skill_ids' => $skill_ids];
}

function saveToDataBase($data, mysqli $conn) {

    $conn->begin_transaction();

    try {
        // Insert event
        $stmt = $conn->prepare("
            INSERT INTO events
            (EventTitle, EventDescription, EventLocation, EventType, EventStartDate, EventEndDate, MaxAttendees)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssssssi",
            $data['title']['valid_field_value'],
            $data['description']['valid_field_value'],
            $data['location']['valid_field_value'],
            $data['type']['valid_field_value'],
            $data['event_dates']['valid_start_value'],
            $data['event_dates']['valid_finish_value'],
            $data['max_attendees']['valid_field_value']
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