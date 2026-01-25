<?php

class Database {
    private $host;
    private $user;
    private $pass;
    private $dbname;
    private $port;
    
    public $conn;

    public function __construct() {
        // Load env logic here or reuse existing config.php logic
        // For now, let's look for the .env.local file similar to the original config.php
        $envFilePath = __DIR__ . '/../../DataBaseManagement/.env.local';
        
        if (file_exists($envFilePath)) {
            $envVars = parse_ini_file($envFilePath);
            $this->host = $envVars['DB_HOST'] ?? 'localhost';
            $this->port = $envVars['DB_PORT'] ?? 3306;
            $this->user = $envVars['DB_USER'] ?? 'root';
            $this->pass = $envVars['DB_PASSWORD'] ?? '';
            $this->dbname = $envVars['DB_NAME'] ?? 'skill_service_exchange_db';
        } else {
            // Fallback default
             $this->host = 'localhost';
             $this->user = 'root';
             $this->pass = '';
             $this->dbname = 'skill_service_exchange_db';
             $this->port = 3306;
        }

        $this->connect();
    }

    public function connect() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname, $this->port);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8");
    }
}
