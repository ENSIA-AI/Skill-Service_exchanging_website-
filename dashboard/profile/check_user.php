<?php
require_once 'includes/dbh.inc.php';

$username = $_GET['username'];


if (!empty($username)) {
    $stmt = $connection->prepare("SELECT COUNT(*) FROM users WHERE UserName = ?");
    $stmt->execute([$username]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo "this username is already taken";
    } else {
        echo "username is available";
    }
}