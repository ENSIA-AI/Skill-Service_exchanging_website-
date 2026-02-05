<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../../DataBaseManagement/config.php';

header('Content-Type: application/json');

echo json_encode([
    'debug' => true,
    'session_user_id' => $_SESSION['user_id'] ?? 'NOT SET',
    'post_data' => $_POST,
    'request_method' => $_SERVER['REQUEST_METHOD']
]);
