<?php
session_start();
require_once 'includes/dbh.inc.php'; 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] :
                (isset($_GET['id']) ? (int)$_GET['id'] : 0);
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $professional_title = $_POST['professional_title'];
    $location = $_POST['location'];
    $aboutme = $_POST['about_me'];
    $phone = $_POST['phone'];
    

    try {
        $connection->beginTransaction();

        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
            $file = $_FILES['profile_picture'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFileName = "profile_" . $userId . "_" . time() . "." . $ext;
            
            $uploadDir = "../../assets/uploads/profile_pics/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $sql = "UPDATE Users SET ProfilePicture = :img WHERE UserId = :id";
                $stmt = $connection->prepare($sql);
                $stmt->execute([':img' => $destination, ':id' => $userId]);
            }
        }

        //update
        $stmt = $connection->prepare("UPDATE Users SET UserName = ?, FullName = ?, Email = ?, ProfessionalTitle = ?, Location = ?, Description = ?, PhoneNumber = ? WHERE UserId = ?");
        $stmt->execute([$_POST['username'], $_POST['fullname'], $_POST['email'], $_POST['professional_title'], $_POST['location'], $_POST['about_me'], $_POST['phone'], $userId]);

        //delete teaching skills
        $delOff = $connection->prepare("DELETE FROM userskills WHERE UserId = ? AND SkillType = 'teach'");
        $delOff->execute([$userId]);

        if (isset($_POST['skill_proficiency'])) {
            $insOff = $connection->prepare("INSERT INTO userskills (UserId, SkillId, ProficiencyLevel, SkillType) VALUES (?, ?, ?, 'teach')");
            foreach ($_POST['skill_proficiency'] as $skillId => $proficiency) {
                $insOff->execute([$userId, $skillId, $proficiency]);
            }
        }
        
        $delSeek = $connection->prepare("DELETE FROM user_seeking_skills WHERE UserId = ?");
        $delSeek->execute([$userId]);

        if (isset($_POST['seeking_skills'])) {
            $insSeek = $connection->prepare("INSERT INTO user_seeking_skills (UserId, SkillId) VALUES (?, ?)");
            foreach ($_POST['seeking_skills'] as $skillId) {
                $insSeek->execute([$userId, $skillId]);
            }
        }

        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'Profile and skills updated successfully!']);

    } catch (Exception $e) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}