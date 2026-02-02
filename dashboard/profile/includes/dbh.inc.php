<?php

// Load environment variables from .env.local
$envFilePath = __DIR__ . '/../../../DataBaseManagement/.env.local';


$envVars = parse_ini_file($envFilePath);

if (!$envVars) {
    die("Error: Unable to parse .env.local file.");
}

// Extract database credentials
$dbHost = $envVars['DB_HOST'] ?? 'localhost';
$dbPort = $envVars['DB_PORT'] ?? 3306;
$dbUser = $envVars['DB_USER'] ?? 'root';
$dbPassword = $envVars['DB_PASSWORD'] ?? '';
$dbName = $envVars['DB_NAME'] ?? 'skill_service_exchange_db';

if (file_exists($envFilePath)) {
    $envVars = parse_ini_file($envFilePath);
    if ($envVars) {
        $dbHost = $envVars['DB_HOST'] ?? $dbHost;
        $dbPort = $envVars['DB_PORT'] ?? $dbPort;
        $dbName = $envVars['DB_NAME'] ?? $dbName;
        $dbUser = $envVars['DB_USER'] ?? $dbUser;
        $dbPassword = $envVars['DB_PASSWORD'] ?? $dbPassword;
    }
}

$dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;";

try{
    $connection = new PDO($dsn, $dbUser, $dbPassword);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}