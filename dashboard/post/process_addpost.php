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
    $exchangeMsg = htmlspecialchars(trim($_POST['exchange']), ENT_QUOTES, 'UTF-8');
    $categoryId = (int)$_POST['category'];
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

    // PaymentMethod logic (simplified: if exchange description is present, maybe 'both' or 'exchange')
    // For now let's use 'credits' as primary if credits > 0, otherwise 'exchange'.
    $paymentMethod = ($credits > 0) ? 'credits' : 'exchange';
    if (!empty($exchangeMsg) && $credits > 0) {
        $paymentMethod = 'both';
    }

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("INSERT INTO Posts (UserId, Title, Description, PostType, CategoryId, Duration, MeetLocation, RequiredCredits, PaymentMethod, PostStatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param("isssiisss", $userId, $title, $description, $postType, $categoryId, $duration, $location, $credits, $paymentMethod);
        
        if ($stmt->execute()) {
            $postId = $conn->insert_id;
            $stmt->close();

            // Handle PostSkills
            if (isset($_POST['skills']) && is_array($_POST['skills'])) {
                $skillStmt = $conn->prepare("INSERT INTO PostSkills (PostId, SkillId) VALUES (?, ?)");
                foreach ($_POST['skills'] as $skillId) {
                    $skillIdInt = (int)$skillId;
                    $skillStmt->bind_param("ii", $postId, $skillIdInt);
                    $skillStmt->execute();
                }
                $skillStmt->close();
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
