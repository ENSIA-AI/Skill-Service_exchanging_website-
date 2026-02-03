<?php
require_once '../../DataBaseManagement/config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
    $categoryId = (int)$_POST['category']; // Usually from offered category, but hidden in addpost.html? No, it's just 'category'.
    // Wait, addpost.html has 'category' in the section above.
    
    $credits = (int)$_POST['credits'];
    $durationStr = $_POST['duration'];
    $location = htmlspecialchars(trim($_POST['location']), ENT_QUOTES, 'UTF-8');
    
    // Parse duration
    $duration = 60; // default
    if (strpos($durationStr, '30') !== false) {
        $duration = 30;
    } elseif (strpos($durationStr, '60') !== false) {
        $duration = 60;
    }

    // PostType logic
    $isOnline = isset($_POST['online']);
    $isInPerson = isset($_POST['inperson']);
    $postType = 'online';
    if ($isOnline && $isInPerson) {
        $postType = 'both';
    } elseif ($isInPerson) {
        $postType = 'in-person';
    }

    // Availability
    $availableTimes = isset($_POST['available_times']) ? $_POST['available_times'] : [];
    $firstAvailableDate = count($availableTimes) > 0 ? $availableTimes[0] : null;

    // PaymentMethod: If target_skills are provided, it's 'exchange' or 'both'
    $hasTargetSkills = isset($_POST['target_skills']) && count($_POST['target_skills']) > 0;
    $paymentMethod = $credits > 0 ? ($hasTargetSkills ? 'both' : 'credit') : 'exchange';

    try {
        $conn->begin_transaction();

        // Prepare first slot date for the main Posts table (required field)
        $availDate = $firstAvailableDate ? $firstAvailableDate : date('Y-m-d H:i:s', strtotime('+1 day'));

        $stmt = $conn->prepare("INSERT INTO Posts (UserId, Title, Description, PostType, CategoryId, Duration, MeetLocation, RequiredCredits, PaymentMethod, PostStatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param("isssiisss", $userId, $title, $description, $postType, $categoryId, $duration, $location, $credits, $paymentMethod);
        
        if ($stmt->execute()) {
            $postId = $conn->insert_id;
            $stmt->close();

            // Handle Offered Skills
            if (isset($_POST['skills']) && is_array($_POST['skills'])) {
                $skillStmt = $conn->prepare("INSERT INTO PostSkills (PostId, SkillId, SkillType) VALUES (?, ?, 'offered')");
                foreach ($_POST['skills'] as $skillId) {
                    $skillIdInt = (int)$skillId;
                    $skillStmt->bind_param("ii", $postId, $skillIdInt);
                    $skillStmt->execute();
                }
                $skillStmt->close();
            }

            // Handle Targeted Skills (Requested)
            if (isset($_POST['target_skills']) && is_array($_POST['target_skills'])) {
                $targetSkillStmt = $conn->prepare("INSERT INTO PostSkills (PostId, SkillId, SkillType) VALUES (?, ?, 'requested')");
                foreach ($_POST['target_skills'] as $skillId) {
                    $skillIdInt = (int)$skillId;
                    $targetSkillStmt->bind_param("ii", $postId, $skillIdInt);
                    $targetSkillStmt->execute();
                }
                $targetSkillStmt->close();
            }

            // Handle Availability Slots
            if (count($availableTimes) > 0) {
                $availStmt = $conn->prepare("INSERT INTO PostAvailableDates (PostId, AvailableDate) VALUES (?, ?)");
                foreach ($availableTimes as $dateTime) {
                    $availStmt->bind_param("is", $postId, $dateTime);
                    $availStmt->execute();
                }
                $availStmt->close();
            }

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Post created successfully', 'postId' => $postId]);
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    
    $conn->close();
    exit();
}
