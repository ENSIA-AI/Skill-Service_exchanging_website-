<?php
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Connect to database
require_once 'DataBaseManagement/config.php';

// Validate and sanitize inputs
$first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validation errors array
$errors = [];

// Validate first name
if (empty($first_name)) {
    $errors[] = 'First name is required';
} elseif (strlen($first_name) < 2) {
    $errors[] = 'First name must be at least 2 characters';
} elseif (strlen($first_name) > 100) {
    $errors[] = 'First name cannot exceed 100 characters';
}

// Validate last name
if (empty($last_name)) {
    $errors[] = 'Last name is required';
} elseif (strlen($last_name) < 2) {
    $errors[] = 'Last name must be at least 2 characters';
} elseif (strlen($last_name) > 100) {
    $errors[] = 'Last name cannot exceed 100 characters';
}

// Validate email
if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address';
} elseif (strlen($email) > 150) {
    $errors[] = 'Email cannot exceed 150 characters';
}

// Validate message
if (empty($message)) {
    $errors[] = 'Message is required';
} elseif (strlen($message) < 10) {
    $errors[] = 'Message must be at least 10 characters';
} elseif (strlen($message) > 5000) {
    $errors[] = 'Message cannot exceed 5000 characters';
}

// Return validation errors if any
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => implode(', ', $errors)]);
    exit;
}

// Prepare and execute insert statement using prepared statements
$sql = "INSERT INTO Contacts (first_name, last_name, email, message) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    exit;
}

// Bind parameters (s = string)
$stmt->bind_param('ssss', $first_name, $last_name, $email, $message);

// Execute statement
if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Thank you for contacting us! We will get back to you soon.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error submitting form: ' . $stmt->error]);
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
