<?php
/**
 * Database Connection Configuration
 * 
 * This file loads environment variables from .env.local (which is gitignored)
 * and establishes a connection to the database.
 * 
 * IMPORTANT FOR HOSTING DEPLOYMENT:
 * Make sure .env.local file is uploaded to the server with production credentials
 */

// Detect environment (production vs local)
$isProduction = !empty($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'infinityfreeapp') !== false || strpos($_SERVER['HTTP_HOST'], 'infinityfree') !== false || strpos($_SERVER['HTTP_HOST'], 'localhost') === false);

// Enable error logging for debugging
if (!$isProduction) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/db_errors.log');
}

// Path to .env.local file (contains credentials that vary per teammate)
$envFilePath = __DIR__ . '/.env.local';

// Detailed error logging for debugging
$debugInfo = "ENV DEBUG - Path: $envFilePath | File Exists: " . (file_exists($envFilePath) ? 'YES' : 'NO') . "\n";
if (file_exists($envFilePath)) {
    $debugInfo .= "ENV DEBUG - File Size: " . filesize($envFilePath) . " bytes | Readable: " . (is_readable($envFilePath) ? 'YES' : 'NO') . "\n";
}
error_log($debugInfo);

// Check if .env.local exists
if (!file_exists($envFilePath)) {
    $errorMsg = "CRITICAL ERROR: .env.local file not found in DataBaseManagement/ directory.\n";
    $errorMsg .= "Expected path: " . $envFilePath . "\n";
    $errorMsg .= "Current working directory: " . getcwd() . "\n";
    $errorMsg .= "Script directory: " . __DIR__ . "\n";
    
    error_log($errorMsg);
    
    if (!$isProduction) {
        die($errorMsg);
    } else {
        // In production, show user-friendly error
        http_response_code(500);
        die('<html><head><title>Database Configuration Error</title></head><body><h1>500 Internal Server Error</h1><p>Database configuration is not properly set up on this server.</p></body></html>');
    }
}

// Load environment variables from .env.local
$envVars = parse_ini_file($envFilePath);

if ($envVars === false) {
    $errorMsg = "ERROR: Unable to parse .env.local file at: " . $envFilePath . "\n";
    $errorMsg .= "File contents could not be parsed as INI format.\n";
    $errorMsg .= "This may be due to file encoding, line endings, or format issues.\n";
    $errorMsg .= "File size: " . filesize($envFilePath) . " bytes\n";
    $errorMsg .= "Readable by PHP: " . (is_readable($envFilePath) ? 'YES' : 'NO') . "\n";
    
    error_log($errorMsg);
    
    if (!$isProduction) {
        die($errorMsg);
    } else {
        http_response_code(500);
        die('<html><head><title>Configuration Error</title></head><body><h1>500 Internal Server Error</h1><p>Database configuration could not be loaded.</p></body></html>');
    }
}

// Log successful load
error_log("ENV DEBUG - Successfully parsed .env.local. Keys found: " . implode(", ", array_keys($envVars)));

// Extract database credentials with fallbacks
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

// Create connection
$conn = @new mysqli($dbHost, $dbUser, $dbPassword, $dbName, $dbPort);

// Check connection
if ($conn->connect_error) {
    $errorMsg = "DATABASE CONNECTION FAILED\n";
    $errorMsg .= "Error Code: " . $conn->connect_errno . "\n";
    $errorMsg .= "Error Message: " . $conn->connect_error . "\n";
    $errorMsg .= "Host: $dbHost\n";
    $errorMsg .= "Database: $dbName\n";
    $errorMsg .= "User: $dbUser\n";
    $errorMsg .= "Port: $dbPort\n";
    $errorMsg .= "Environment: " . ($isProduction ? 'PRODUCTION' : 'LOCAL') . "\n";
    
    error_log($errorMsg);
    
    if (!$isProduction) {
        die("Connection failed: " . $conn->connect_error . "\nHost: $dbHost, DB: $dbName, Port: $dbPort, User: $dbUser");
    } else {
        http_response_code(500);
        die('<html><head><title>Database Error</title></head><body><h1>500 Internal Server Error</h1><p>Unable to connect to the database. Please try again later.</p></body></html>');
    }
}

// Log successful connection
error_log("DB CONNECTION SUCCESS - Host: $dbHost, DB: $dbName, Charset: utf8mb4");


// Set charset
if (!$conn->set_charset("utf8mb4")) {
    // Fallback to utf8 if utf8mb4 is not available
    $conn->set_charset("utf8");
}

// Store connection status for later checking
define('DB_CONNECTED', true);

?>
