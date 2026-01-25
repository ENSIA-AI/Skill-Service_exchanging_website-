<?php

class User {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function login($email, $password) {
        $stmt = $this->db->conn->prepare("SELECT * FROM Users WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Verify password (assuming password_hash was used during registration)
            // If raw password for now:
            // if ($password === $user['Password']) return $user;
            
            // Using password_verify
            if (password_verify($password, $user['Password'])) {
                return $user;
            }
        }
        return false;
    }

    public function register($data) {
        // Prepare SQL Statement
        // Users: UserName, FullName, Email, Password, Location, PhoneNumber, BirthDate, Gender
        // We need to generate a unique UserName (maybe from email or name?) or use email as username if allowed.
        // The schema says UserName is UNIQUE and NOT NULL.
        // Let's generate a username from FullName + random number or use the one if provided (form didn't have username field explicitly, only Full Name & Email).
        // Let's derive UserName from Email (part before @).
        
        $emailParts = explode('@', $data['email']);
        $userName = $emailParts[0] . rand(100, 999);
        
        $fullName = $data['fullName'];
        $email = $data['email'];
        $password = password_hash($data['password'], PASSWORD_DEFAULT); // Hash the password
        $location = $data['location'];
        $phone = $data['phone'];
        $birthDate = $data['birthdate'];
        $gender = ($data['gender'] === 'male') ? 'M' : 'F'; // Map to ENUM

        $stmt = $this->db->conn->prepare("INSERT INTO Users (UserName, FullName, Email, Password, Location, PhoneNumber, BirthDate, Gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
             die("Prepare failed: " . $this->db->conn->error);
        }

        $stmt->bind_param("ssssssss", $userName, $fullName, $email, $password, $location, $phone, $birthDate, $gender);

        if ($stmt->execute()) {
            return true;
        } else {
            // Handle error (e.g. duplicate email)
            return false;
        }
    }
}
