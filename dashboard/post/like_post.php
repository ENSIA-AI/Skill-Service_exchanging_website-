<?php
session_start();
require_once '../../DataBaseManagement/config.php';

// Get the current user ID (using fixed ID for now)
$currentUserId = 1;

// Get post ID from request
$postId = isset($_POST['postId']) ? (int) $_POST['postId'] : 0;

if ($postId <= 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
  exit();
}

try {
  // Check current like status
  $checkStmt = $conn->prepare('SELECT LikeId FROM PostLikes WHERE PostId = ? AND UserId = ?');
  $checkStmt->bind_param('ii', $postId, $currentUserId);
  $checkStmt->execute();
  $result = $checkStmt->get_result();
  $isCurrentlyLiked = $result->num_rows > 0;
  $checkStmt->close();

  $success = false;
  $action = 'no change';

  if ($isCurrentlyLiked) {
    // Remove like
    $deleteStmt = $conn->prepare('DELETE FROM PostLikes WHERE PostId = ? AND UserId = ?');
    $deleteStmt->bind_param('ii', $postId, $currentUserId);
    if ($deleteStmt->execute() && $conn->affected_rows > 0) {
      // Update like count
      $updateStmt = $conn->prepare('UPDATE Posts SET LikeCount = LikeCount - 1 WHERE PostId = ?');
      $updateStmt->bind_param('i', $postId);
      $updateStmt->execute();
      $updateStmt->close();
      $success = true;
      $action = 'unliked';
    }
    $deleteStmt->close();
  } else {
    // Add like
    $insertStmt = $conn->prepare('INSERT INTO PostLikes (PostId, UserId) VALUES (?, ?)');
    $insertStmt->bind_param('ii', $postId, $currentUserId);
    if ($insertStmt->execute()) {
      // Update like count
      $updateStmt = $conn->prepare('UPDATE Posts SET LikeCount = LikeCount + 1 WHERE PostId = ?');
      $updateStmt->bind_param('i', $postId);
      $updateStmt->execute();
      $updateStmt->close();
      $success = true;
      $action = 'liked';
    }
    $insertStmt->close();
  }

  // Get updated like count
  $countStmt = $conn->prepare('SELECT LikeCount FROM Posts WHERE PostId = ?');
  if (!$countStmt) {
    echo json_encode(['success' => false, 'message' => 'Count prepare failed: ' . $conn->error]);
    exit();
  }
  $countStmt->bind_param('i', $postId);
  $countStmt->execute();
  $countResult = $countStmt->get_result();
  $post = $countResult->fetch_assoc();
  $countStmt->close();

  echo json_encode([
    'success' => $success,
    'likeCount' => (int) ($post['LikeCount'] ?? 0),
    'action' => $action
  ]);

} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Exception: ' . $e->getMessage()]);
}

$conn->close();
?>