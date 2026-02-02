<?php
include 'includes/dbh.inc.php';
session_start();

$limit = isset($_POST['newPostLimit']) ? (int)$_POST['newPostLimit'] : 3;
$targetUserId = isset($_POST['targetUserId']) ? (int)$_POST['targetUserId'] : 0; 

$sql = "SELECT Posts.*, Users.FullName, Users.ProfilePicture, Users.UserName
        FROM Posts 
        JOIN Users ON Posts.UserId = Users.UserId
        WHERE Posts.UserId = :targetId
        ORDER BY Posts.PostId DESC 
        LIMIT :limit";

$stmt = $connection->prepare($sql);
$stmt->bindValue(':targetId', $targetUserId, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <section>
            <div class="short-post">
                <div class="short-post-head">
                    <div class="post-avatar">
                        <figure>
                            <img src="<?= !empty($row['ProfilePicture']) ? $row['ProfilePicture'] : '../../assets/images/Default_pfp.svg'; ?>" alt="avatar">
                        </figure>
                    </div>
                    <div class="short-post-fullname">
                        <span><?= htmlspecialchars($row['FullName']); ?></span>
                    </div>
                    <div class="short-post-username">
                        <span><?= htmlspecialchars($row['UserName']); ?></span>
                    </div>
                </div>
                <div class="post-content">
                    <div class="post-skill"><?= htmlspecialchars($row['Title']); ?></div>
                    <div class="post-description"><?= htmlspecialchars($row['Description']); ?></div>
                    <div class="post-credit">
                        <p><span class="muted"><?= htmlspecialchars($row['RequiredCredits']); ?> credits/hours</span></p>
                    </div>
                    <div class="post-details">
                        <a href="../post/postdetails.php?Postid=<?= $row['PostId']; ?>">See Details</a>
                    </div>
                </div>
            </div>
        </section>
        <hr>
        <?php
    }
} else {
    echo '<div class="no-posts-msg" style="text-align:center; padding: 30px; color: #bbb; border: 1px dashed #444; border-radius: 10px; margin: 20px;">
            <p>This user hasn’t created any posts yet.</p>
          </div>';
}

$countSql = "SELECT COUNT(*) FROM Posts WHERE UserId = :targetId";
$countStmt = $connection->prepare($countSql);
$countStmt->execute([':targetId' => $targetUserId]);
$totalPosts = $countStmt->fetchColumn();

if ($limit >= $totalPosts) {
    echo '<input type="hidden" id="no-more-posts-signal" value="1">';
}
?>