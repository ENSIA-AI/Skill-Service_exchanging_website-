<?php
// Test database connection
header('Content-Type: application/json');

// Include config
require_once '../../DataBaseManagement/config.php';

// Check if conn exists
if (isset($conn)) {
    echo json_encode([
        'success' => true,
        'message' => 'Connection object exists',
        'conn_type' => gettype($conn),
        'conn_error' => $conn->connect_error ?? 'No error'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => '$conn is not set'
    ]);
}
?>
