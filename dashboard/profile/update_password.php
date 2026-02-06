<?php
session_start();
require_once 'includes/dbh.inc.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User session not found.']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($newPassword) || empty($confirmPassword)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    if (strlen($newPassword) < 8) {
        echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters']);
        exit;
    }

    if (!preg_match('/[A-Za-z]/', $newPassword)) {
        echo json_encode(['status' => 'error', 'message' => 'Password must contain at least one letter']);
        exit;
    }

    if (!preg_match('/[0-9]/', $newPassword)) {
        echo json_encode(['status' => 'error', 'message' => 'Password must contain at least one number']);
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        echo json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
        exit;
    }

    try {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $connection->prepare("UPDATE users SET Password = :pass WHERE UserId = :id");
        $stmt->execute([
            ':pass' => $hashedPassword,
            ':id'   => $userId
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Password updated successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}