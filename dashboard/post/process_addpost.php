<?php
// Suppress PHP error output to prevent breaking JSON response
error_reporting(0);
ini_set('display_errors', 0);

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
    
    // Get CategoryId from the first offered skill
    $categoryId = 1; // Default fallback
    if (isset($_POST['skills']) && is_array($_POST['skills']) && count($_POST['skills']) > 0) {
        $firstSkillId = (int)$_POST['skills'][0];
        $catQuery = $conn->prepare("SELECT CategoryId FROM skills WHERE SkillId = ?");
        $catQuery->bind_param("i", $firstSkillId);
        $catQuery->execute();
        $catResult = $catQuery->get_result();
        if ($catRow = $catResult->fetch_assoc()) {
            $categoryId = (int)$catRow['CategoryId'];
        }
        $catQuery->close();
    }
    
    $credits = (int)($_POST['credits'] ?? 0);
    $durationStr = $_POST['duration'] ?? '60 minutes';
    $location = htmlspecialchars(trim($_POST['location'] ?? ''), ENT_QUOTES, 'UTF-8');
    
    // Parse duration - default to 60 minutes (1 hour) if not specified
    $duration = 60; // default 1 hour
    if (strpos($durationStr, '30') !== false) {
        $duration = 30;
    } elseif (strpos($durationStr, '90') !== false) {
        $duration = 90;
    } elseif (strpos($durationStr, '120') !== false || strpos($durationStr, '2 hour') !== false) {
        $duration = 120;
    } elseif (strpos($durationStr, '60') !== false) {
        $duration = 60;
    }

    // PostType logic - using new radio button
    $postType = $_POST['delivery_type'] ?? 'online';

    // Availability
    $availableTimes = isset($_POST['available_times']) ? $_POST['available_times'] : [];
    $firstAvailableDate = count($availableTimes) > 0 ? $availableTimes[0] : null;

    // PaymentMethod Inference:
    $hasTargetSkills = isset($_POST['target_skills']) && count($_POST['target_skills']) > 0;
    
    if ($hasTargetSkills) {
        $paymentMethod = 'exchange';
        // Note: credits might still be stored if provided, but method is 'exchange'
    } elseif ($credits > 0) {
        $paymentMethod = 'credit';
    } else {
        $paymentMethod = 'exchange'; // Default fallback
    }

    try {
        $conn->begin_transaction();

        // Prepare first slot date for the main Posts table (required field)
        $availDate = $firstAvailableDate ? $firstAvailableDate : date('Y-m-d H:i:s', strtotime('+1 day'));

        $stmt = $conn->prepare("INSERT INTO posts (UserId, Title, Description, PostType, CategoryId, Duration, MeetLocation, RequiredCredits, PaymentMethod, PostStatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("isssiisis", $userId, $title, $description, $postType, $categoryId, $duration, $location, $credits, $paymentMethod);
        
        if ($stmt->execute()) {
            $postId = $conn->insert_id;
            $stmt->close();

            // Handle Offered Skills
            if (isset($_POST['skills']) && is_array($_POST['skills'])) {
                $skillStmt = $conn->prepare("INSERT INTO postskills (PostId, SkillId, SkillType) VALUES (?, ?, 'offered')");
                foreach ($_POST['skills'] as $skillId) {
                    $skillIdInt = (int)$skillId;
                    $skillStmt->bind_param("ii", $postId, $skillIdInt);
                    $skillStmt->execute();
                }
                $skillStmt->close();
            }

            // Handle Targeted Skills (Requested)
            if (isset($_POST['target_skills']) && is_array($_POST['target_skills'])) {
                $targetSkillStmt = $conn->prepare("INSERT INTO postskills (PostId, SkillId, SkillType) VALUES (?, ?, 'requested')");
                foreach ($_POST['target_skills'] as $skillId) {
                    $skillIdInt = (int)$skillId;
                    $targetSkillStmt->bind_param("ii", $postId, $skillIdInt);
                    $targetSkillStmt->execute();
                }
                $targetSkillStmt->close();
            }

            // Handle Availability Slots
            if (count($availableTimes) > 0) {
                $availStmt = $conn->prepare("INSERT INTO postavailabledates (PostId, AvailableDate) VALUES (?, ?)");
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
