<?php

// Detect production environment
$isProduction = !empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'infinityfree') !== false;

// Error logging setup
error_reporting(E_ALL);
if (!$isProduction) {
    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../../../DataBaseManagement/pdo_errors.log');
}

// Load environment variables from .env.local
$envFilePath = __DIR__ . '/../../../DataBaseManagement/.env.local';

if (!file_exists($envFilePath)) {
    $errorMsg = "CRITICAL ERROR: .env.local file not found. Expected location: " . $envFilePath;
    error_log($errorMsg);
    
    if (!$isProduction) {
        die($errorMsg);
    } else {
        http_response_code(500);
        die('<html><head><title>Database Configuration Error</title></head><body><h1>500 Internal Server Error</h1><p>Database configuration is not properly set up.</p></body></html>');
    }
}

$envVars = parse_ini_file($envFilePath);

if (!$envVars) {
    $errorMsg = "ERROR: Unable to parse .env.local file at: " . $envFilePath;
    error_log($errorMsg);
    
    if (!$isProduction) {
        die($errorMsg);
    } else {
        http_response_code(500);
        die('<html><head><title>Configuration Error</title></head><body><h1>500 Internal Server Error</h1><p>Database configuration could not be loaded.</p></body></html>');
    }
}

// Extract database credentials
$dbHost = $envVars['DB_HOST'] ?? 'localhost';
$dbPort = (int)($envVars['DB_PORT'] ?? 3306);
$dbUser = $envVars['DB_USER'] ?? 'root';
$dbPassword = $envVars['DB_PASSWORD'] ?? '';
$dbName = $envVars['DB_NAME'] ?? 'skill_service_exchange_db';

// Validate required credentials
if (empty($dbHost) || empty($dbName) || empty($dbUser)) {
    $errorMsg = "ERROR: Missing required database credentials in .env.local\n";
    $errorMsg .= "Required: DB_HOST, DB_NAME, DB_USER\n";
    $errorMsg .= "Found: DB_HOST=$dbHost, DB_NAME=$dbName, DB_USER=$dbUser";
    error_log($errorMsg);
    
    if (!$isProduction) {
        die($errorMsg);
    } else {
        http_response_code(500);
        die('<html><head><title>Configuration Error</title></head><body><h1>500 Internal Server Error</h1><p>Invalid database configuration.</p></body></html>');
    }
}

$dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";

try{
    $connection = new PDO($dsn, $dbUser, $dbPassword);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $errorMsg = "DATABASE CONNECTION FAILED (PDO)\n";
    $errorMsg .= "Error: " . $e->getMessage() . "\n";
    $errorMsg .= "Host: $dbHost, Database: $dbName, Port: $dbPort\n";
    
    error_log($errorMsg);
    
    if (!$isProduction) {
        die("Connection failed: " . $e->getMessage());
    } else {
        http_response_code(500);
        die('<html><head><title>Database Error</title></head><body><h1>500 Internal Server Error</h1><p>Unable to connect to the database. Please try again later.</p></body></html>');
    }
}

?>