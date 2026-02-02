<?php
require_once 'includes/dbh.inc.php'; 
session_start();

$targetUserId = isset($_POST['targetUserId']) ? (int)$_POST['targetUserId'] : 0;
$limit = isset($_POST['NewReviews']) ? (int)$_POST['NewReviews'] : 2;
function time_ago($timestamp)
{
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;

    $minutes      = round($seconds / 60);           // value 60 is seconds
    $hours        = round($seconds / 3600);         // value 3600 is 60 minutes * 60 sec
    $days         = round($seconds / 86400);        // value 86400 is 24 hours * 60 min * 60 sec
    $weeks        = round($seconds / 604800);       // value 604800 is 7 days * 24 hours * 60 min * 60 sec
    $months       = round($seconds / 2629440);      // value 2629440 is ((365+365+365+365+366)/5/12)*24*60*60
    $years        = round($seconds / 31553280);     // value 31553280 is ((365+365+365+365+366)/5)*24*60*60

    if ($seconds <= 60) {
        return "Just Now";
    } else if ($minutes <= 60) {
        return ($minutes == 1) ? "one minute ago" : "$minutes minutes ago";
    } else if ($hours <= 24) {
        return ($hours == 1) ? "an hour ago" : "$hours hours ago";
    } else if ($days <= 7) {
        return ($days == 1) ? "yesterday" : "$days days ago";
    } else if ($weeks <= 4.3) {
        return ($weeks == 1) ? "a week ago" : "$weeks weeks ago";
    } else if ($months <= 12) {
        return ($months == 1) ? "a month ago" : "$months months ago";
    } else {
        return ($years == 1) ? "one year ago" : "$years years ago";
    }
}
try {
    $sql = "SELECT r.*, u.FullName, u.UserName, u.ProfilePicture 
            FROM profile_reviews r 
            JOIN Users u ON r.ReviewerID = u.UserId 
            WHERE r.userID = :targetId 
            ORDER BY r.ReviewDate DESC 
            LIMIT :limit";

    $stmt = $connection->prepare($sql);
    $stmt->bindValue(':targetId', $targetUserId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($reviews) {
        foreach ($reviews as $row) {
    $readable_date = time_ago($row['ReviewDate']);
    $profilePic = isset($row['ProfilePicture']) ? $row['ProfilePicture'] : '../../assets/images/Default_pfp.svg';
    $fullname = isset($row['FullName']) ? $row['FullName'] : 'Anonymous';
    $username = isset($row['UserName']) ? $row['UserName'] : 'anonymous_user';
    echo '<section>
                        <div class="review">
                            <div class="reviewer-avatar">
                                <figure>
                                    <img src="' . htmlspecialchars($profilePic) . '" alt="avatar">
                                </figure>
                            </div>
                            <div class="reviewer-date">
                                <span>' . $readable_date . '</span>
                            </div>
                            <div class="reviewer-name">
                                <span>' . htmlspecialchars($fullname) . '</span>
                            </div>
                            <div class="reviewer-user-name">
                                <span>' . htmlspecialchars($username) . '</span>
                                
                                <span id="" class="review-rate">' .  str_repeat('&starf; ', (int)$row['ReviewRate']) . '</span>
                            </div>
                            <div class="review-content">
                                <div class="review-text">' . htmlspecialchars($row['ReviewText']) . '</div>
                            </div>
                        </div>
                        <hr>
                    </section>';}
    }
    else {
        echo "<p>No reviews yet for this user.</p>";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
