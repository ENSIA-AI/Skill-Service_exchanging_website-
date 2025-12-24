<?php 

require_once __DIR__ . "/../../../DataBaseManagement/config.php";

function getSkills(mysqli $conn ,$categoryid){

    if(!$categoryid){
        echo json_encode([]);
        exit;
    }

    $skills = [];
    $stmt = $conn->prepare("SELECT skillid,skillname FROM skills WHERE categoryid = ? ORDER BY skillname");
    $stmt->bind_param("i" , $categoryid);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result){
        while($row = $result->fetch_assoc()){
            $skills[] = $row;
        }
    }
    
    header("Content-Type: application/json");
    echo json_encode($skills);
    exit;
}
$categoryId = $_GET['categoryid'] ?? null;

getSkills($conn ,$categoryId);
?>