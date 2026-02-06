<?php
/**
 * Get User Skills API
 * Returns updated skills and proficiency levels for a user
 */

session_start();
require_once 'includes/dbh.inc.php';

header('Content-Type: application/json');

try {
    // Get user ID from session or query parameter
    $userId = isset($_GET['userId']) ? (int)$_GET['userId'] : 
              (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);
    
    if ($userId === 0) {
        echo json_encode(['success' => false, 'error' => 'User ID not provided']);
        exit;
    }
    
    // Fetch user skills with proficiency levels
    $stmt = $connection->prepare("
        SELECT UserSkills.ProficiencyLevel, Skills.SkillName, Skills.SkillId 
        FROM userskills 
        JOIN Skills ON UserSkills.SkillId = Skills.SkillId 
        WHERE UserSkills.UserId = :id 
        AND UserSkills.SkillType = 'teach'
        ORDER BY Skills.SkillName
    ");
    $stmt->execute([':id' => $userId]);
    
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'skills' => $skills
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
