<?php
session_start();
require_once 'includes/dbh.inc.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reviewer_id = $_SESSION['user_id'];    
    $target_id = $_POST['target_user_id'];  
    $rating = $_POST['rating'];
    $text = trim($_POST['review_text']);
    $is_edit = isset($_POST['is_edit']) && $_POST['is_edit'] === '1' ? true : false;

    if (empty($rating) || empty($text)) {
        header("Location: profile.php?id=" . $target_id . "&error=emptyfields");
        exit();
    }

    if ($is_edit) {
        // Update existing review
        $sql = "UPDATE profile_reviews 
                SET ReviewText = :text, ReviewRate = :rate, ReviewDate = NOW()
                WHERE ReviewerID = :reviewer AND userID = :target";
        
        $stmt = $connection->prepare($sql);
        $stmt->execute([
            ':reviewer' => $reviewer_id,
            ':text'     => $text,
            ':rate'     => $rating,
            ':target'   => $target_id
        ]);

        header("Location: profile.php?id=" . $target_id . "&success=reviewupdated");
        exit();
    } else {
        // Insert new review
        $sql = "INSERT INTO profile_reviews (ReviewerID, ReviewText, ReviewRate, userID, ReviewDate) 
                VALUES (:reviewer, :text, :rate, :target, NOW())";
        
        $stmt = $connection->prepare($sql);
        $stmt->execute([
            ':reviewer' => $reviewer_id,
            ':text'     => $text,
            ':rate'     => $rating,
            ':target'   => $target_id
        ]);

        header("Location: profile.php?id=" . $target_id . "&success=reviewposted");
        exit();
    }
}