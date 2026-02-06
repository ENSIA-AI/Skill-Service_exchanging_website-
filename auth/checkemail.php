<?php
include_once '../DataBaseManagement/config.php';

$email = $_GET['email'];

$stmt = $conn->prepare("SELECT UserId FROM users WHERE Email=?");
$stmt->bind_param('s', $email);
$stmt->execute();

$result = $stmt->get_result();

echo json_encode([
    "exists" => $result->num_rows > 0
]);
?>
