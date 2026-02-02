<?php 

require_once __DIR__ . "/../../../DataBaseManagement/config.php";

function getSkills(mysqli $conn ,$categoryid){

    if(!$categoryid){
        echo json_encode([]);
        exit;
    }

    $skills = [];
    $stmt = $conn->prepare("SELECT SkillId, SkillName FROM Skills WHERE CategoryId = ? ORDER BY SkillName");
    
    if (!$stmt) {
        // Log error if needed, but return empty array to prevent JS crash
        // For debugging, we can return the error
        header("Content-Type: application/json");
        echo json_encode(["status" => "error", "message" => $conn->error]);
        exit;
    }

    $stmt->bind_param("i" , $categoryid);
    $stmt->execute();
    $stmt->bind_result($skillId, $skillName);

    while ($stmt->fetch()) {
        $skills[] = [
            'skillid' => $skillId,
            'skillname' => $skillName
        ];
    }
    
    $stmt->close();
    
    header("Content-Type: application/json");
    echo json_encode($skills);
    exit;
}
$categoryId = $_GET['categoryid'] ?? null;

getSkills($conn ,$categoryId);
?>