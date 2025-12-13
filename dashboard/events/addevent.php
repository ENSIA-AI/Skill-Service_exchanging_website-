<?php

 require_once '../../DataBaseManagement/config.php';

$errors = [];

$event_title = $_POST['eventTitle'];
$event_start_date = $_POST['eventStartDate'];
$event_end_date = $_POST['eventEndDate'];
$event_type = $_POST['eventType'];
$event_location = $_POST['location'];
$event_description = $_POST['description'];
$event_max_attendees = $_POST['maxAttendees'];
$event_category = $_POST['category'];
$event_skill = $_POST['skill'];

//To validate required fields
function validateRequired(&$errors, $field_name , $field_value, $required_chars){
    if(empty(trim($field_value))){
        $errors[] = "error: $field_name is required";
        return ['success' => false , 'valid_field_value' => ''];
    }
    elseif(preg_match('/[^a-zA-Z0-9 \-\,.&]/' , $field_value)){
        $errors[] = "error: $field_name mustn't have special charachters";
        return ['success' => false , 'valid_field_value' => ''];
    }
    elseif(strlen($field_value) < $required_chars ){
        $errors[] = "error: $field_name must be at least $required_chars charachters long";
        return ['success' => false , 'valid_field_value' => ''];
    }
    else{
        $cleaned = trim($field_value);
        return ['success' => true , 'valid_field_value' => $cleaned];
    } 
}

//To validate the max number of attendees
function validateNumber(&$errors , $field_name , $field_value , $max , $min){
    if(empty(trim($field_value))){
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

    if($_POST['timezone'] || !in_array($_POST['timezone'], timezone_identifiers_list(),true)){
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
        $error[] = "error: $field_name can't be more than one year later";
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
    elseif($finish_date->diff($start_date) > 365){
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
    }
    $stmt->close();
    return ['success' => true , 'category_id' => $category_id];
    
}

function validateSkill(&$errors , mysqli $conn , $skill_field_value , $category_id){

    $skill_field_value = trim($skill_field_value);
    $category_id = trim($category_id);
    $stmt = $conn->prepare("SELECT skillid FROM skills WHERE skillname = ? AND categoryid = ?");
    $stmt->bind_param("si" , $skill_field_value , $category_id);
    $stmt->execute();
    $stmt->store_result();

    $skill_id = NULL;
    if($stmt->num_rows === 0){
        $errors[] = "error: skill does not exist or skill is not from selected category";
        $stmt->close();
        return ['success' => false , 'skill_id' => $skill_id];
    }

    else{
        $stmt->bind_result($skill_id);
        $stmt->fetch();
        $stmt->close();
        return ['success' => true , 'skill_id' => $skill_id];
    }
}
?>