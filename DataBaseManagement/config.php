<?php
/**
 * Database Connection Configuration
 * 
 * This file loads environment variables from .env.local (which is gitignored)
 * and establishes a connection to the database.
 */

// Path to .env.local file (contains credentials that vary per teammate)
$envFilePath = __DIR__ . '/.env.local';

// Check if .env.local exists; if not, provide helpful error
if (!file_exists($envFilePath)) {
    die("Error: .env.local file not found in DataBaseManagement/ directory. Please configure your database credentials in DataBaseManagement/.env.local");
}

// Load environment variables from .env.local
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

// Create connection
$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName, $dbPort);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

?>
