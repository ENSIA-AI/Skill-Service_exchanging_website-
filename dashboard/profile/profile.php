<?php
session_start();
require_once 'includes/dbh.inc.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

$loggedInId = $_SESSION['user_id'];
$targetUserId = isset($_GET['id']) ? (int)$_GET['id'] : $loggedInId;

$isOwner = ($targetUserId === $loggedInId);

try {
    $stmt = $connection->prepare("SELECT * FROM users WHERE UserId = :id");
    $stmt->execute([':id' => $targetUserId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $currentName     = !empty($user['FullName']) ? $user['FullName'] : 'Your Full hhhhh Name';
        $currentUsername = !empty($user['UserName']) ? $user['UserName'] : 'usehhrname';
        $currentPhoto    = !empty($user['ProfilePicture']) && $user['ProfilePicture'] !== null && $user['ProfilePicture'] !== '' ? htmlspecialchars($user['ProfilePicture']) : '../../assets/images/Default_pfp.svg';
        $currentProfessionalTitle = !empty($user['ProfessionalTitle']) ? $user['ProfessionalTitle'] : 'Your Professional Title';
        $currentLocation = !empty($user['Location']) ? $user['Location'] : 'Your Location';
        $Datestring     = !empty($user['UserSince']) ? $user['UserSince'] : 'Year';
        $UserSince      = date("Y", strtotime($Datestring));
        $currentRating  = !empty($user['Rating']) ? $user['Rating'] : '0';
        $ExchangesCount = !empty($user['ExchangeCount']) ? $user['ExchangeCount'] : '0';
        $Discription    = !empty($user['Description']) ? $user['Description'] : 'This is your profile description. Tell people more about yourself!';
    } else {
        die("User not found.");
    }
    //***************************************************************************************************************** */
    $ratingQuery = "SELECT AVG(ReviewRate) AS average, COUNT(ReviewerID) AS total 
                FROM profile_reviews 
                WHERE userID = :id";
    $stmtRating = $connection->prepare($ratingQuery);
    $stmtRating->execute([':id' => $targetUserId]);
    $ratingData = $stmtRating->fetch(PDO::FETCH_ASSOC);

    $avgRating = ($ratingData['average']) ? round($ratingData['average'], 1) : 0;
    $totalReviews = $ratingData['total'];
} catch (PDOException $e) {
    echo "Query failed: " . $e->getMessage();
}

try {
    $query = "SELECT us.SkillId, s.SkillName 
              FROM userskills us
              JOIN Skills s ON us.SkillId = s.SkillId
              WHERE us.UserId = :id AND us.SkillType = 'learn'";
    $stmt = $connection->prepare($query);
    $stmt->execute(['id' => $targetUserId]);
    $seekingSkills = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

function renderReviewForm($targetUserId, $data, $isEdit)
{
    $rating = $data ? $data['ReviewRate'] : '';
    $text = $data ? htmlspecialchars($data['ReviewText']) : '';
?>
    <form action="process_review.php" method="POST">
        <input type="hidden" name="target_user_id" value="<?= $targetUserId; ?>">
        <input type="hidden" name="is_edit" id="is_edit_flag" value="<?= $isEdit ? '1' : '0'; ?>">

        <div id="rating-label" style="font-weight: bold;text-align: center;font-size: 1.2em; color: white; margin-top: 20px; margin-bottom: 10px; height: 1.5em;">
            <?= $isEdit ? "" : "Select a rating" ?>
        </div>

        <div class="star-rating" style="font-size: 2.5rem; cursor: pointer; color: #ccc; text-align: center;">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="star" data-value="<?= $i ?>">&#9733;</span>
            <?php endfor; ?>
        </div>
        <input type="hidden" name="rating" id="ratingInput" value="<?= $rating ?>" required>

        <div class="add-review-section" style="margin-top: 15px; text-align: center;">
            <input type="text" name="review_text" id="add-review" value="<?= $text ?>" placeholder="Write your review" required>
        </div>

        <div style="display: flex; justify-content:right;width: 96%;padding:0;">
            <button type="submit" class="submit-review">
                <?= $isEdit ? "Update Review" : "Save and Post Review" ?>
            </button>
        </div>
    </form>

<?php
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $currentName ?></title>
    <link rel="icon" href="../../assets/icons/favicon_io/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/personalprofile.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>

    <script>
        $(document).ready(function() {
            var reviewLimit = 2;
            $("#show-more-reviews").click(function() {
                reviewLimit += 2;
                $.ajax({
                    url: "load-reviews.php",
                    method: "POST",
                    data: {
                        NewReviews: reviewLimit,
                        targetUserId: <?php echo $targetUserId; ?>
                    },
                    success: function(data) {
                        $(".reviews-container").html(data);

                        // Check if there are more reviews to load
                        if ($("#no-more-reviews-signal").length > 0) {
                            $("#show-more-reviews").hide();
                        }
                    },
                    error: function() {
                        console.log("Error loading more reviews");
                    }
                });
            });
        });
        $(document).ready(function() {
            var postLimit = 3;
            $("#show-more-posts").click(function() {
                postLimit += 3;
                $.ajax({
                    url: "load-posts.php",
                    method: "POST",
                    data: {
                        newPostLimit: postLimit,
                        targetUserId: <?php echo $targetUserId; ?>
                    },
                    success: function(data) {
                        $("#posts-container").html(data);

                        if ($("#no-more-posts-signal").length > 0) {
                            $("#show-more-posts").hide();
                        }
                    },
                    error: function() {
                        console.log("Error loading more posts");
                    }
                });
            });
        });
    </script>

    <style>
        @media (max-width: 700px) {
            .php-content {
                padding: 0;
            }
        }

        /* Empty posts state styling */
        .no-posts-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 300px;
            width: 100%;
        }

        .no-posts-msg {
            text-align: center;
            padding: 40px 30px;
            color: #bbb;
            border: 2px dashed #444;
            border-radius: 10px;
            max-width: 400px;
            font-size: 16px;
        }

        .no-posts-msg p {
            margin: 0;
            font-weight: 500;
        }
    </style>


</head>

<body>
    <?php include '../../components/header_t.php'; ?>
    <?php include '../../components/sidebar.html'; ?>
    <main class="php-content">
        <div class="profile-page">
            <div class="profile-head">
                <div class="user-info">
                    <div class="avatar">
                        <img id="profile-preview" src="<?= $currentPhoto; ?>" alt="Profile" data-user-id="<?= htmlspecialchars($targetUserId); ?>">
                    </div>
                    <section>
                        <div class="user-info-profile-name">
                            <h2><strong><span><?= $currentName; ?></span></strong></h2>
                        </div>
                    </section>
                    <div id="" class="user-name">
                        <span><?= $currentUsername; ?></span>
                    </div>
                    <?php if ($isOwner): ?>
                        <div class="edit-profile">
                            <a href="./editprofile.php"><b>Edit Profile</b></a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="user-meta">
    <section>
                        <div class="profile-name">
                            <h2><strong><span><?= $currentName; ?></span></strong></h2>
                        </div>
                    </section>
                    <section>
                        <div class="major"><span class="muted-text"><?= $currentProfessionalTitle ?></span></div>
                        <div class="location"><span class="muted-text">Location: <?= $currentLocation ?></span></div>
                        <div class="email"><span class="muted-text">Email: <?= htmlspecialchars($user['Email']) ?></span></div>
                    </section>
                    <section>
                        <div class="user-meta-info">
                            <div class="user-meta-card">
                                <div class="user-meta-card-title">
                                    <h5><span class="user-meta-card-title-name">Rating: <?= $avgRating ?>/5 (<?= $totalReviews ?> reviews)</span></h5>
                                </div>
                                <div class="user-meta-card-info">
                                    <div class="stars-outer">
                                        <div class="stars-inner" style="width: <?= ($avgRating / 5 * 100) ?>%;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="user-meta-card">
                                <div class="user-meta-card-title">
                                    <h5><span class="user-meta-card-title-name">Exchanges</span></h5>
                                </div>
                                <div class="user-meta-card-info"><span class="user-meta-card-info-details"><?= $ExchangesCount ?></span></div>
                            </div>
                            <div class="user-meta-card">
                                <div class="user-meta-card-title">
                                    <h5><span class="user-meta-card-title-name">Member Since</span></h5>
                                </div>
                                <div class="user-meta-card-info"><span class="user-meta-card-info-details"><?= $UserSince ?></span></div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <div class="profile-info">
                <div class="profile-nav">
                    <div class="nav-item">
                        <button>
                            <span data-target="about-content" class="active">About</span>
                        </button>
                    </div>
                    <div class="nav-item">
                        <button><span data-target="skill-content">Skills</span></button>
                    </div>
                    <div class="nav-item">
                        <button>
                            <span data-target="interests-content">Interests</span>
                        </button>
                    </div>
                    <!-- 
                    <div class="nav-item">
                        <button>
                            <span data-target="availability-content">Availability</span>
                        </button>
                    </div>
                    -->

                </div>
                <div class="profile-nav-content">
                    <div id="about-content" class="tab-content active">
                        <section>
                            <div id="about" class="section" style="background:#5e6591 ;"><!-- BACKGROUND COLOR IS INLINE-->
                                <div class="head-section">
                                    <h2><strong><span class="title">About</span></strong></h2>
                                    <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <?= nl2br(htmlspecialchars($Discription)); ?>
                                    </p>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div id="skill-content" class="tab-content">
                        <section>
                            <div id="skills" class="section">
                                <article aria-labelledby="skills-title">
                                    <div class="head-section">
                                        <h2><strong><span class="title">Skills</span></strong></h2>
                                        <p>
                                            THIS IS YOUR SKILLS THAT'S YOU ARE GOOD WITH.
                                        </p>
                                    </div>
                                    <div class="skills">
                                        <?php
                                        $stmt2 = $connection->prepare("SELECT userskills.ProficiencyLevel, Skills.SkillName FROM userskills JOIN Skills ON userskills.SkillId = Skills.SkillId WHERE userskills.UserId = :id AND userskills.SkillType = 'teach'");
                                        $stmt2->execute([':id' => $targetUserId]);

                                        $skills = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($skills as $skill) {
                                        ?>

                                            <div class="skill">
                                                <div class="donut" style="--val: <?= htmlspecialchars($skill['ProficiencyLevel']); ?>"><span><?= htmlspecialchars($skill['ProficiencyLevel']); ?>%</span></div>
                                                <p><strong><?= htmlspecialchars($skill['SkillName']); ?></strong><br><br><span class="muted">50 credits/hours</span></p>
                                            </div>


                                        <?php
                                        }
                                        ?>
                                    </div>
                                </article>
                            </div>
                        </section>
                    </div>
                    <div id="interests-content" class="tab-content">
                        <section>
                            <div id="interests" class="section">
                                <article aria-labelledby="interests-title">
                                    <div class="head-section" style="background-color: #5e6591;">
                                        <h2><strong><span class="title">Your Interests</span></strong></h2>
                                        <p>
                                            SKILLS YOU'RE SEEKING TO LEARN.
                                        </p>
                                    </div>
                                    <div class="interests-badges">
                                        <?php
                                        foreach ($seekingSkills as $skill) {
                                        ?>
                                            <div class="skill-badge"><?= htmlspecialchars($skill['SkillName']); ?> <span class="badge-remove">×</span></div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </article>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
            <!-- Upcoming Sessions Section 
            <div class="Upcoming-Sessions-Section">
                <section class="sessions">
                    <div class="Upcoming-sessions">
                        <div class="sessions-grid">
                            <h3>your upcoming sessions</h3>
                            <div class="session">
                                <div class="session-header">
                                    <h3>react advanced patterns</h3>
                                </div>
                                <div class="tutor">
                                    <p>with sarah chen</p>
                                </div>
                                <div class="session-date">
                                    Oct 12,2:00 PM
                                </div>
                            </div>
                            <div class="session">
                                <div class="session-header">
                                    <h3>Portrait Photography Basics</h3>
                                </div>
                                <div class="tutor">
                                    <p>with Michael Torres</p>
                                </div>
                                <div class="session-date">
                                    Oct 14, 10:00 AM
                                </div>
                            </div>
                            <div class="session">
                                <div class="session-header">
                                    <h3>Business Strategy Session</h3>
                                </div>
                                <div class="tutor">
                                    <p>with David Kim</p>
                                </div>
                                <div class="session-date">
                                    Oct 15, 4:00 PM
                                </div>
                            </div>
                            <button class="view-sessions">
                                <span>view all sessions</span>
                            </button>
                        </div>
                    </div>
                </section>
            </div>-->
            <div class="profile-short-posts">
                <div class="profile-short-posts-head">
                    <div class="profile-short-posts-head-lable">
                        <h3><span>My Posted Services</span></h3>
                    </div>
                    <?php if ($isOwner): ?>
                        <div class="profile-short-posts-head-create-post-button">
                            <a href="../post/addpost.php">Create Post</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="short-posts-section" id="posts-container">
                    <?php
                    // 1. Get the TOTAL number of posts for this specific user
                    $countSql = "SELECT COUNT(*) FROM posts WHERE UserId = :targetUserId";
                    $countStmt = $connection->prepare($countSql);
                    $countStmt->execute([':targetUserId' => $targetUserId]);
                    $totalPosts = $countStmt->fetchColumn();

                    // 2. Fetch the first 3 posts
                    $sql = "SELECT Posts.*, Users.FullName, Users.ProfilePicture, Users.username
                FROM posts 
                JOIN Users ON Posts.UserId = Users.UserId
                WHERE Posts.UserId = :targetUserId
                ORDER BY Posts.PostId DESC 
                LIMIT :limit";

                    $stmt = $connection->prepare($sql);
                    $stmt->bindValue(':targetUserId', $targetUserId, PDO::PARAM_INT);
                    $stmt->bindValue(':limit', 3, PDO::PARAM_INT);
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
                                            <span><?= htmlspecialchars($row['username']); ?></span>
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
                    <?php
                        }
                    }
                    ?>
                </div>

                <?php if ($totalPosts === 0): ?>
                    <div class="no-posts-container" id="no-posts-message">
                        <div class="no-posts-msg">
                            <p>This user hasn't created any posts yet.</p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($totalPosts > 3): ?>
                    <div class="more-reviews">
                        <span id="show-more-posts">show more posts</span>
                    </div>
                <?php endif; ?>
            </div>



            <div id="reviews-section" class="reviews-section section">
                <div class="reviews-head">
                    <div class="reviews-head-lable">
                        <h3><span class="reviews-section-header-lable">Reviews & Feedback</span></h3>
                    </div>
                </div>

                <div id="reviews" class="reviews-container">
                    <?php
                    function time_ago($timestamp)
                    {
                        $time_ago = strtotime($timestamp);
                        $current_time = time();
                        $time_difference = $current_time - $time_ago;
                        $seconds = $time_difference;

                        $minutes      = round($seconds / 60);
                        $hours        = round($seconds / 3600);
                        $days         = round($seconds / 86400);
                        $weeks        = round($seconds / 604800);
                        $months       = round($seconds / 2629440);
                        $years        = round($seconds / 31553280);

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

                    $query = "SELECT r.*, u.FullName, u.UserName, u.ProfilePicture 
                  FROM profile_reviews r
                  JOIN Users u ON r.ReviewerID = u.UserId
                  WHERE r.userID = :id
                  ORDER BY r.ReviewDate ASC
                  LIMIT 2;";

                    $stmt = $connection->prepare($query);
                    $stmt->execute([':id' => $targetUserId]);

                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $readable_date = time_ago($row['ReviewDate']);
                            echo '<section>
                <div class="review">
                    <div class="reviewer-avatar">
                        <figure>
                            <img src="' . htmlspecialchars($row['ProfilePicture']) . '" alt="avatar">
                        </figure>
                    </div>
                    <div class="reviewer-date">
                        <span>' . $readable_date . '</span>
                    </div>
                    <div class="reviewer-name">
                        <span>' . htmlspecialchars($row['FullName']) .  '</span>
                    </div>
                    <div class="reviewer-user-name">
                        <span>' . htmlspecialchars($row['UserName']) . '</span>
                        <span class="review-rate">' .  str_repeat('&starf; ', (int)$row['ReviewRate']) . '</span>
                    </div>
                    <div class="review-content">
                        <div class="review-text">' . htmlspecialchars($row['ReviewText']) . '</div>
                    </div>
                </div>
                <hr>
                </section>';
                        }
                    } else {
                        echo '<div style="text-align:center; padding: 20px; color: #bbb;">
                    <p>No reviews yet.</p>
                  </div>';
                    }
                    ?>
                </div>

                <div class="review-container">
                    <?php
                    $checkReview = $connection->prepare("SELECT ReviewRate, ReviewText FROM profile_reviews WHERE ReviewerID = :rev AND userID = :target");
                    $checkReview->execute([':rev' => $loggedInId, ':target' => $targetUserId]);
                    $existingReview = $checkReview->fetch(PDO::FETCH_ASSOC);

                    if ($isOwner) {
                    } elseif ($existingReview) { ?>
                        <div id="review-status-msg" style="text-align:center; color:white; margin-bottom: 20px;">
                            <p>You have already submitted a review for this user.
                                <a href="javascript:void(0);" id="edit-review-trigger" style="color: #ffa36c; text-decoration: underline; cursor: pointer;">edit review</a>
                            </p>
                        </div>

                        <div id="review-form-container" style="display: none;">
                            <form action="process_review.php" method="POST">
                                <input type="hidden" name="target_user_id" value="<?= $targetUserId; ?>">
                                <input type="hidden" name="is_edit" value="1">
                                <div id="rating-label" style="font-weight: bold;text-align: center;font-size: 1.2em; color: white; margin-top: 20px; margin-bottom: 10px; height: 1.5em;"></div>

                                <div class="star-rating" style="font-size: 2.5rem; cursor: pointer; color: #ccc; text-align: center;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star" data-value="<?= $i ?>">&#9733;</span>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="ratingInput" value="<?= $existingReview['ReviewRate'] ?>" required>

                                <div class="add-review-section" style="margin-top: 15px; text-align: center;">
                                    <input type="text" name="review_text" id="add-review" value="<?= htmlspecialchars($existingReview['ReviewText']) ?>" placeholder="Write your review" required>
                                </div>
                                <div style="display: flex; justify-content:right;width: 96%;padding:0;">
                                    <button type="submit" class="submit-review">Update Review</button>
                                </div>
                            </form>
                        </div>

                    <?php } else { ?>
                        <div id="review-form-container">
                            <form action="process_review.php" method="POST">
                                <input type="hidden" name="target_user_id" value="<?= $targetUserId; ?>">
                                <div id="rating-label" style="font-weight: bold;text-align: center;font-size: 1.2em; color: white; margin-top: 20px; margin-bottom: 10px; height: 1.5em;">Select a rating</div>
                                <div class="star-rating" style="font-size: 2.5rem; cursor: pointer; color: #ccc; text-align: center;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star" data-value="<?= $i ?>">&#9733;</span>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="ratingInput" required>
                                <div class="add-review-section" style="margin-top: 15px; text-align: center;">
                                    <input type="text" name="review_text" id="add-review" placeholder="Write your review" required>
                                </div>
                                <div style="display: flex; justify-content:right;width: 96%;padding:0;">
                                    <button type="submit" class="submit-review">Save and Post Review</button>
                                </div>
                            </form>
                        </div>
                    <?php } ?>
                </div>

                <?php if ($stmt->rowCount() > 0): ?>
                    <div class="more-reviews">
                        <span id="show-more-reviews">show more reviews</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>
    <script src="../../assets/js/profile.js"></script>
    <script>
    const stars = document.querySelectorAll('.star');
    const ratingInput = document.getElementById('ratingInput');
    const ratingLabel = document.getElementById('rating-label');
    const editTrigger = document.getElementById('edit-review-trigger');
    const statusMsg = document.getElementById('review-status-msg');
    const formContainer = document.getElementById('review-form-container');

    const labels = {
        1: "Awful, not what I expected at all",
        2: "Poor, pretty disappointed",
        3: "Average, could be better",
        4: "Good, what I expected",
        5: "Amazing, above expectations!"
    };

    function highlightStars(count, color) {
        stars.forEach((s) => {
            const val = parseInt(s.getAttribute('data-value'));
            s.style.color = (val <= count) ? color : '#ccc';
        });
    }

    const initialValue = parseInt(ratingInput.value) || 0;
    if (initialValue > 0) {
        highlightStars(initialValue, '#ffa36c');
        ratingLabel.textContent = labels[initialValue];
    }

    if (editTrigger) {
        editTrigger.addEventListener('click', function() {
            statusMsg.style.display = 'none';
            formContainer.style.display = 'block';
            document.getElementById('add-review').focus();
        });
    }

    stars.forEach(star => {
        star.addEventListener('mouseover', function() {
            const value = parseInt(this.getAttribute('data-value'));
            ratingLabel.textContent = labels[value];
            highlightStars(value, '#ffa36c');
        });

        star.addEventListener('mouseout', function() {
            const lockedValue = parseInt(ratingInput.value) || 0;
            if (lockedValue > 0) {
                ratingLabel.textContent = labels[lockedValue];
                highlightStars(lockedValue, '#ffa36c');
            } else {
                ratingLabel.textContent = "Select a rating";
                highlightStars(0, '#ccc');
            }
        });

        star.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            ratingInput.value = value;
            ratingLabel.textContent = labels[value];
            highlightStars(value, '#ffa36c');
        });
    });

    // Dynamic skill refresh when returning from edit page
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('updated') === '1') {
            // Remove the parameter from URL without page reload
            const newUrl = window.location.pathname + window.location.hash;
            window.history.replaceState({}, document.title, newUrl);
            
            // Fetch and update skills dynamically
            const userId = $('#profile-preview').data('user-id');
            if (userId) {
                refreshSkills(userId);
            }
        }
    });

    function refreshSkills(userId) {
        $.ajax({
            url: 'get_user_skills.php',
            type: 'GET',
            data: { userId: userId },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.skills) {
                    updateSkillsDisplay(response.skills);
                    
                    // Show success notification
                    showNotification('Profile updated successfully!', 'success');
                }
            },
            error: function() {
                console.error('Failed to refresh skills');
            }
        });
    }

    function updateSkillsDisplay(skills) {
        const skillsContainer = $('.skills');
        if (skillsContainer.length === 0) return;
        
        // Clear existing skills
        skillsContainer.empty();
        
        // Add updated skills
        skills.forEach(function(skill) {
            const proficiency = parseInt(skill.ProficiencyLevel);
            const skillHtml = `
                <div class="skill">
                    <div class="donut" style="--val: ${proficiency}"><span>${proficiency}%</span></div>
                    <p><strong>${escapeHtml(skill.SkillName)}</strong><br><br><span class="muted">50 credits/hours</span></p>
                </div>
            `;
            skillsContainer.append(skillHtml);
        });
        
        // Trigger animation for the donuts
        setTimeout(function() {
            $('.donut').each(function() {
                const val = $(this).css('--val');
                $(this).css('--val', '0');
                setTimeout(() => {
                    $(this).css('--val', val);
                }, 50);
            });
        }, 100);
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function showNotification(message, type) {
        const notification = $(`
            <div class="profile-notification ${type}" style="
                position: fixed;
                top: 100px;
                right: 20px;
                background: ${type === 'success' ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : '#dc3545'};
                color: white;
                padding: 15px 25px;
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
                z-index: 10000;
                animation: slideIn 0.3s ease-out;
            ">
                <strong>${message}</strong>
            </div>
        `);
        
        $('body').append(notification);
        
        setTimeout(function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
</script>
<style>
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
</style>
</body>

</html>