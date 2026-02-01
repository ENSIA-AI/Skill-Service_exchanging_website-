<?php

$dsn = "mysql:host=localhost;dbname=skill_service_exchange_db;";
$username = "root";
$password = "";

try{
    $connection = new PDO($dsn, $username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}