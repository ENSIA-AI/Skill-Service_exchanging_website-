<?php

class Post {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllPosts() {
        // Fetch Posts with User Info, Category Name, and Skills
        $query = "
            SELECT p.*, u.FullName as UserName, u.ProfilePicture, c.CategoryName,
            (SELECT GROUP_CONCAT(s.SkillName SEPARATOR ', ') 
             FROM PostSkills ps 
             JOIN Skills s ON ps.SkillId = s.SkillId 
             WHERE ps.PostId = p.PostId) as Skills
            FROM Posts p
            JOIN Users u ON p.UserId = u.UserId
            JOIN Category c ON p.CategoryId = c.CategoryId
            ORDER BY p.CreatedAt DESC
        ";
        
        $result = $this->db->conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }
    
    public function getPostById($id) {
        $stmt = $this->db->conn->prepare("
            SELECT p.*, u.FullName as UserName, u.ProfilePicture, u.ProfessionalTitle, u.Rating, u.RatingCount, c.CategoryName,
            (SELECT GROUP_CONCAT(s.SkillName SEPARATOR ', ') 
             FROM PostSkills ps 
             JOIN Skills s ON ps.SkillId = s.SkillId 
             WHERE ps.PostId = p.PostId) as Skills
            FROM Posts p
            JOIN Users u ON p.UserId = u.UserId
            JOIN Category c ON p.CategoryId = c.CategoryId
            WHERE p.PostId = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function createPost($data) {
        // Posts Table: UserId, Title, Description, PostType, CategoryId, Duration, MeetLocation, AvailableDate, PaymentMethod, RequiredCredits
        
        $stmt = $this->db->conn->prepare("INSERT INTO Posts (UserId, Title, Description, PostType, CategoryId, Duration, MeetLocation, AvailableDate, PaymentMethod, RequiredCredits) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
             die("Prepare failed: " . $this->db->conn->error);
        }

        $paymentMethod = 'credit'; // Defaulting to credit based on form design 'Credits per Hour'

        $stmt->bind_param("issisisssi", 
            $data['user_id'], 
            $data['title'], 
            $data['description'], 
            $data['post_type'], 
            $data['category_id'],
            $data['duration'],
            $data['location'],
            $data['available_date'],
            $paymentMethod,
            $data['credits']
        );

        return $stmt->execute();
    }
}
