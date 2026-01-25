<?php

class Event {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllEvents() {
        // Fetch Events with Organizer Name
        $query = "
            SELECT e.*, u.FullName as OrganizerName, u.UserName,
            (SELECT GROUP_CONCAT(s.SkillName SEPARATOR ', ') 
             FROM EventSkills es 
             JOIN Skills s ON es.SkillId = s.SkillId 
             WHERE es.EventId = e.EventId) as Skills
            FROM Events e
            JOIN Users u ON e.OrganizerId = u.UserId
            ORDER BY e.EventStartDate ASC
        ";
        
        $result = $this->db->conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }
    
    public function getCategories() {
        $result = $this->db->conn->query("SELECT CategoryId, CategoryName FROM Category ORDER BY CategoryName");
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    public function getSkillsByCategory($categoryId) {
        $stmt = $this->db->conn->prepare("SELECT SkillId as skillid, SkillName as skillname FROM Skills WHERE CategoryId = ? ORDER BY SkillName");
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    public function createEvent($data) {
        $this->db->conn->begin_transaction();

        try {
            $stmt = $this->db->conn->prepare("INSERT INTO Events (OrganizerId, EventTitle, EventDescription, EventLocation, EventType, EventStartDate, EventEndDate, MaxAttendees) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->conn->error);
            }

            $stmt->bind_param("issssssi", 
                $data['organizer_id'], 
                $data['title'], 
                $data['description'], 
                $data['location'], 
                $data['type'], 
                $data['start_date'], 
                $data['end_date'], 
                $data['max_attendees']
            );

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $eventId = $this->db->conn->insert_id;

            // Handle Skills
            if (!empty($data['skills'])) {
                // skills is a comma separated string of IDs
                $skillIds = explode(',', $data['skills']);
                $skillStmt = $this->db->conn->prepare("INSERT INTO EventSkills (EventId, SkillId) VALUES (?, ?)");

                foreach ($skillIds as $skillId) {
                    $skillId = trim($skillId);
                    if (is_numeric($skillId)) {
                        $skillStmt->bind_param("ii", $eventId, $skillId);
                        $skillStmt->execute();
                    }
                }
            }

            $this->db->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->db->conn->rollback();
            // Log error?
            // echo $e->getMessage();
            return false;
        }
    }
}
