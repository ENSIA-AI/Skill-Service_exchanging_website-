<?php
session_start();
error_reporting(E_ALL);

// Detect production environment
$isProduction = !empty($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'infinityfreeapp') !== false || strpos($_SERVER['HTTP_HOST'], 'infinityfree') !== false || strpos($_SERVER['HTTP_HOST'], 'localhost') === false);
if (!$isProduction) {
    ini_set('display_errors', 1);
} else {
    ini_set('display_errors', 0);
    ini_set('log_errors', '1');
}

require_once '../../DataBaseManagement/config.php';

// Check database connection
if (!isset($conn) || !$conn) {
    error_log("POSTS ERROR: Database connection not established");
    http_response_code(500);
    die('<h1>500 Internal Server Error</h1><p>Database connection failed. Please try again later.</p>');
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

$currentUserId = $_SESSION['user_id'];

$posts = [];
$search_query = '';
$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
$limit = 12;
$isSearch = false;
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';

// Fetch posts from database (filtering based only on posts.php: search, category, or pagination)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search']) && $_GET['search'] !== '') {
    $isSearch = true;
    $search_query = htmlspecialchars(trim($_GET['search']), ENT_QUOTES, 'UTF-8');
    $sql = 'SELECT p.PostId, p.UserId, p.Title, p.Description, p.LikeCount, 
               c.CategoryName, u.UserName, u.Rating, u.ProfilePicture,
        (SELECT COUNT(*) FROM postlikes WHERE PostId = p.PostId AND UserId = ?) as UserLiked
        FROM posts p
        JOIN users u ON p.UserId = u.UserId 
        JOIN category c ON p.CategoryId = c.CategoryId 
        WHERE p.PostStatus = "active" AND (p.Title LIKE ? OR p.Description LIKE ? OR u.UserName LIKE ?)';
    if ($category_filter !== '') {
        $sql .= ' AND c.CategoryName = ?';
    }
    $sql .= ' ORDER BY p.CreatedAt DESC';
    $stmt = $conn->prepare($sql);
    if ($category_filter !== '') {
        $search_param = "%$search_query%";
        $stmt->bind_param('issss', $currentUserId, $search_param, $search_param, $search_param, $category_filter);
    } else {
        $search_param = "%$search_query%";
        $stmt->bind_param('isss', $currentUserId, $search_param, $search_param, $search_param);
    }
    $stmt->execute();
    $stmt->bind_result($postId, $userId, $title, $description, $likeCount, $categoryName, $userName, $rating, $profilePicture, $userLiked);
    while ($stmt->fetch()) {
        $posts[] = [
            'PostId' => $postId,
            'UserId' => $userId,
            'Title' => $title,
            'Description' => $description,
            'LikeCount' => $likeCount,
            'CategoryName' => $categoryName,
            'UserName' => $userName,
            'Rating' => $rating,
            'UserLiked' => $userLiked,
            'ProfilePicture' => $profilePicture
        ];
    }
    $stmt->close();
} elseif ($category_filter !== '') {
    // Filter by category only (server-side)
    $stmt = $conn->prepare('SELECT p.PostId, p.Title, p.Description, p.LikeCount, c.CategoryName, u.UserName, u.Rating, u.ProfilePicture, u.UserId,
        (SELECT COUNT(*) FROM postlikes WHERE PostId = p.PostId AND UserId = ?) as UserLiked
        FROM posts p 
        JOIN users u ON p.UserId = u.UserId 
        JOIN category c ON p.CategoryId = c.CategoryId 
        WHERE p.PostStatus = "active" AND c.CategoryName = ? 
        ORDER BY p.CreatedAt DESC');
    $stmt->bind_param('is', $currentUserId, $category_filter);
    $stmt->execute();
    $stmt->bind_result($postId, $title, $description, $likeCount, $categoryName, $userName, $rating, $profilePicture, $userId, $userLiked);
    while ($stmt->fetch()) {
        $posts[] = [
            'PostId' => $postId,
            'Title' => $title,
            'Description' => $description,
            'LikeCount' => $likeCount,
            'CategoryName' => $categoryName,
            'UserName' => $userName,
            'Rating' => $rating,
            'UserLiked' => $userLiked,
            'ProfilePicture' => $profilePicture,
            'UserId' => $userId
        ];
    }
    $stmt->close();
} else {
    $stmt = $conn->prepare('SELECT p.PostId, p.Title, p.Description, p.LikeCount, c.CategoryName, u.UserName, u.Rating, u.ProfilePicture, u.UserId,
        (SELECT COUNT(*) FROM postlikes WHERE PostId = p.PostId AND UserId = ?) as UserLiked
        FROM posts p 
        JOIN users u ON p.UserId = u.UserId 
        JOIN category c ON p.CategoryId = c.CategoryId 
        WHERE p.PostStatus = "active" 
        ORDER BY p.CreatedAt DESC LIMIT ? OFFSET ?');
    $stmt->bind_param('iii', $currentUserId, $limit, $offset);
    $stmt->execute();
    $stmt->bind_result($postId, $title, $description, $likeCount, $categoryName, $userName, $rating, $profilePicture, $userId, $userLiked);
    while ($stmt->fetch()) {
        $posts[] = [
            'PostId' => $postId,
            'Title' => $title,
            'Description' => $description,
            'LikeCount' => $likeCount,
            'CategoryName' => $categoryName,
            'UserName' => $userName,
            'Rating' => $rating,
            'UserLiked' => $userLiked,
            'ProfilePicture' => $profilePicture,
            'UserId' => $userId
        ];
    }
    $stmt->close();
}

// For AJAX search requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    ob_clean(); // Clear any output buffers

    if (count($posts) > 0) {
        foreach ($posts as $post) {
            $rating = round((float) $post['Rating']);
            $starsHtml = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);

            $isLiked = $post['UserLiked'] > 0 ? 'liked' : '';
            $likeCount = (int) $post['LikeCount'];
            $userId = htmlspecialchars((string)($post['UserId'] ?? ''));
            $profilePic = !empty($post['ProfilePicture']) ? htmlspecialchars($post['ProfilePicture']) : '../../assets/images/Default_pfp.svg';
            $CategoryName = htmlspecialchars((string)($post['CategoryName'] ?? ''));
            $UserName = htmlspecialchars((string)($post['UserName'] ?? ''));
            $Desc = htmlspecialchars((string)($post['Description'] ?? ''));
            $Title = htmlspecialchars((string)($post['Title'] ?? ''));

            echo '<div class="post-card" data-post-id="' . htmlspecialchars($post['PostId']) . '" data-category="' . $CategoryName . '">';
            echo '<div class="post-header">';
            echo '<a href="../../dashboard/profile/profile.php?id=' . $userId . '" class="profile-pic-link">';
            echo '<img src="' . $profilePic . '" alt="Profile Picture" class="profile-pic">';
            echo '</a>';
            echo '<div class="profile-info">';
            echo '<h3><a href="../../dashboard/profile/profile.php?id=' . $userId . '" class="profile-username-link">' . $UserName . '</a></h3>';
            echo '<div class="stars">';
            echo $starsHtml;
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '<p class="post-field">' . $Title . '</p>';
            echo '<p class="post-description">' . htmlspecialchars(substr($Desc, 0, 100)) . '...</p>';
            echo '<div class="skills">';
            echo '<span>' . htmlspecialchars((string)($post['CategoryName'] ?? '')) . '</span>';
            echo '</div>';
            echo '<div class="post-actions">';
            echo '<button class="see-details-btn" onclick="window.location.href=\'postdetails.php?Postid=' . htmlspecialchars($post['PostId']) . '\'">See Details</button>';
            echo '<button class="like-btn ' . $isLiked . '" data-likes="' . $likeCount . '">';
            echo '<svg viewBox="0 0 24 24">';
            echo '<path d="M12 21s-7.5-4.9-9.3-7.1C1.2 11.9 2.3 7.5 6.3 6.1 8.1 5.5 10 6.1 11 7.6c1-1.5 2.9-2.1 4.7-1.5 4 1.4 5.1 5.8 3.6 7.8C19.5 16.1 12 21 12 21z"></path>';
            echo '</svg>';
            if ($likeCount > 0) {
                echo '<span class="like-count">' . $likeCount . '</span>';
            }
            echo '</button>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        // No results for search — return recommendations-style empty state
        echo '<div class="no-results-message"><p>No posts found. Try adjusting your search or browsing categories.</p></div>';
    }
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Posts</title>
    <link rel="stylesheet" href="../../assets/css/posts.css">
    <link rel="icon" href="../../assets/icons/favicon_io/favicon.ico" type="image/x-icon">
    <style>
        .category-card.hidden {
            display: none;
        }

        .category-scroll {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            scroll-behavior: smooth;
            padding-bottom: 10px;
            scrollbar-width: auto;
            scrollbar-color: var(--clr-lighter) transparent;
        }

        .show-more-categories-btn {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            min-height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            min-width: 250px;
        }

        .show-more-categories-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .show-more-categories-btn.hidden {
            display: none;
        }

        .show-more-section {
            display: flex;
            justify-content: center;
            padding: 2rem;
            margin-top: 2rem;
        }

        .show-all-btn {
            padding: 12px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            display: block;
        }

        .show-all-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        /* Star rating styles */
        .stars {
            color: #ffc107;
            font-size: 16px;
            letter-spacing: 2px;
        }

        .stars .full-star {
            color: #ffc107;
        }

        .stars .half-star {
            color: #ffc107;
        }

        .stars .empty-star {
            color: #ddd;
        }

        .profile-pic-link {
            display: inline-block;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
        }

        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            transition: opacity 0.2s, transform 0.2s;
        }

        .profile-pic:hover {
            transform: scale(1.1);
            opacity: 0.8;
        }


        .profile-username-link {
            text-decoration: none;
            color: inherit;
            transition: color 0.2s ease;
        }

        .profile-username-link:hover {
            color: #ffae00;
        }
    </style>
</head>

<body>
    <?php include '../../components/header_t.php'; ?>
    <?php include '../../components/sidebar.html'; ?>

    <main class="main-content">
        <div class="search-container">
            <input type="text" id="search-bar" placeholder="Search for skills or services..." class="search-input">
            <button class="search-btn">
                <img src="../../assets/icons/search.svg" alt="">
            </button>
        </div>

        <button class="create-post-btn"
            onclick="window.location.href='addpost.php'">
            <img src="../../assets/icons/plus.svg" alt="" class="plus-icon">
            <span>create new post</span>
        </button>

        <!-- Browse Categories Section -->
        <section class="browse-category">
            <h2>Browse Categories</h2>
            <div class="category-scroll">

                <?php $cat = 'Technology & Programming'; ?>
                <a class="category-card<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="../../assets/icons/laptop-open-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Design & Creative'; ?>
                <a class="category-card<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="../../assets/icons/color-wheel-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Photography & Video'; ?>
                <a class="category-card<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="../../assets/icons/camera-white-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Writing & Content'; ?>
                <a class="category-card<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="../../assets/icons/author-writer-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Music & Performance'; ?>
                <a class="category-card<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="../../assets/icons/guitar-color-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Cooking & Culinary'; ?>
                <a class="category-card<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="../../assets/icons/hot_cooking_icon_134855.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <button class="show-more-categories-btn" id="showMoreCategoriesBtn">Show More Categories</button>

                <?php $cat = 'Health & Fitness'; ?>
                <a class="category-card hidden more-categories<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="../../assets/icons/yoga-meditation-girl-clipart.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Languages'; ?>
                <a class="category-card hidden more-categories<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="../../assets/icons/online-community-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Automotive & Mechanics'; ?>
                <a class="category-card hidden more-categories<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="../../assets/icons/car-service-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Gardening & Nature'; ?>
                <a class="category-card hidden more-categories<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="../../assets/icons/pine-trees-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Business & Finance'; ?>
                <a class="category-card hidden more-categories<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="../../assets/icons/briefcase-emoji-clipart-original.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Arts & Crafts'; ?>
                <a class="category-card hidden more-categories<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="../../assets/icons/paint-palette-clipart.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <button class="show-more-categories-btn hidden" id="showMoreCategoriesBtn2">Show More
                    Categories</button>

                <?php $cat = 'Home Improvement'; ?>
                <a class="category-card hidden more-categories-2<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="../../assets/icons/service-provider-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Cleaning & Organization'; ?>
                <a class="category-card hidden more-categories-2<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="../../assets/icons/cleaning.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Science & Education'; ?>
                <a class="category-card hidden more-categories-2<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="../../assets/icons/interesting-facts-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Fashion & Beauty'; ?>
                <a class="category-card hidden more-categories-2<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="../../assets/icons/woman-dress-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

            </div>
        </section>

        <section class="browse-posts">
            <h2 id="posts-title"><?php echo $category_filter !== '' ? htmlspecialchars($category_filter) : 'Browse All Posts'; ?></h2>

            <!-- Load more posts (pagination) / Back to all (when category filter active, from posts.php only) -->
            <div class="show-more-section" id="show-all-section">
                <?php if ($category_filter !== ''): ?>
                    <a href="posts.php" class="show-all-btn back-to-all-btn" id="back-to-all-btn">Show All Posts</a>
                <?php elseif (count($posts) >= 12): ?>
                    <button class="show-all-btn" id="show-all-btn">Show All Posts</button>
                <?php endif; ?>
            </div>

            <div class="posts-grid" id="posts-grid">
                <?php if (count($posts) > 0): ?>
                    <?php foreach ($posts as $post):
                    
                        $rating = round((float) ($post['Rating'] ?? 0));
                        $isLiked = $post['UserLiked'] > 0 ? 'liked' : '';
                        $likeCount = (int) $post['LikeCount'];
                        $userId = htmlspecialchars((string)($post['UserId'] ?? ''));
                        $profilePic = !empty($post['ProfilePicture']) ? htmlspecialchars($post['ProfilePicture']) : '../../assets/images/Default_pfp.svg';
                        $CategoryName = htmlspecialchars((string)($post['CategoryName'] ?? ''));
                        $UserName = htmlspecialchars((string)($post['UserName'] ?? ''));
                        $Desc = htmlspecialchars((string)($post['Description'] ?? ''));
                        $Title = htmlspecialchars((string)($post['Title'] ?? ''));
                    ?>
                        <div class="post-card" data-post-id="<?php echo htmlspecialchars($post['PostId']); ?>"
                            data-category="<?php echo $CategoryName; ?>">
                            <div class="post-header">
                                <a href="../../dashboard/profile/profile.php?id=<?php echo $userId; ?>" class="profile-pic-link">
                                    <img src="<?php echo $profilePic; ?>"
                                        alt="Profile Picture" class="profile-pic">
                                </a>
                                <div class="profile-info">
                                    <h3><a href="../../dashboard/profile/profile.php?id=<?php echo $userId; ?>" class="profile-username-link"><?php echo $UserName; ?></a></h3>
                                    <div class="stars">
                                        <?php
                                        echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <p class="post-field"><?php echo $Title; ?></p>
                            <p class="post-description">
                                <?php echo htmlspecialchars(substr($Desc, 0, 100)); ?>...
                            </p>

                            <div class="skills">
                                <span><?php echo $CategoryName; ?></span>
                            </div>
                            <div class="post-actions">
                                <a href="postdetails.php?Postid=<?php echo htmlspecialchars($post['PostId']); ?>" class="see-details-btn">See Details</a>
                                <button class="like-btn <?php echo $isLiked; ?>" data-likes="<?php echo $likeCount; ?>">
                                    <svg viewBox="0 0 24 24">
                                        <path
                                            d="M12 21s-7.5-4.9-9.3-7.1C1.2 11.9 2.3 7.5 6.3 6.1 8.1 5.5 10 6.1 11 7.6c1-1.5 2.9-2.1 4.7-1.5 4 1.4 5.1 5.8 3.6 7.8C19.5 16.1 12 21 12 21z">
                                        </path>
                                    </svg>
                                    <?php if ($likeCount > 0): ?>
                                        <span class="like-count"><?php echo $likeCount; ?></span>
                                    <?php endif; ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-results-message">
                        <p>No posts found. Try adjusting your search or browsing categories.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Show All Posts Button at Bottom -->
            <!-- REMOVED - Using only top button -->
        </section>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="../../assets/js/posts.js"></script>

    <script>
        var currentOffset = 12;
        var hasMorePosts = <?php echo count($posts) >= 12 ? 'true' : 'false'; ?>;
        var originalPostsHtml = null; // Store original posts for reset
        var isSearchActive = false;

        $(document).ready(function() {
            // Store the original posts HTML on page load
            originalPostsHtml = $('#posts-grid').html();

            // Show More Posts button with AJAX pagination (only when not in search mode)
            $('#show-all-btn, #show-all-btn-bottom').on('click', function() {
                // If search is active, reset to show all original posts
                if (isSearchActive) {
                    $('#posts-grid').html(originalPostsHtml);
                    $('#posts-title').text('Browse All Posts');
                    $('#search-bar').val('');
                    isSearchActive = false;
                    currentOffset = 12;
                    // Show the button again for pagination if there are more posts
                    if (hasMorePosts) {
                        $('#show-all-btn, #show-all-btn-bottom').show();
                    }
                    return;
                }

                // Normal pagination - load more posts
                $.ajax({
                    type: 'GET',
                    url: 'posts.php',
                    data: {
                        offset: currentOffset
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.trim() !== '' && response.includes('post-card')) {
                            $('#posts-grid').append(response);
                            // Update original posts to include the newly loaded ones
                            originalPostsHtml = $('#posts-grid').html();
                            currentOffset += 12;
                        } else {
                            $('#show-all-btn, #show-all-btn-bottom').hide();
                        }
                    },
                    error: function() {
                        console.log('Error loading more posts');
                    }
                });
            });

            // Show More Categories functionality
            $('#showMoreCategoriesBtn').on('click', function(e) {
                e.preventDefault();
                $('.more-categories').css('display', 'block');
                $(this).css('display', 'none');
                $('#showMoreCategoriesBtn2').css('display', 'flex');
            });

            $('#showMoreCategoriesBtn2').on('click', function(e) {
                e.preventDefault();
                $('.more-categories-2').css('display', 'block');
                $(this).css('display', 'none');
            });

            // Search functionality — show results without recommendation title
            $('#search-bar').on('keyup', function() {
                var searchQuery = $(this).val().trim();

                if (searchQuery.length > 0) {
                    $.ajax({
                        type: 'GET',
                        url: 'posts.php',
                        data: {
                            search: searchQuery
                        },
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            $('#posts-grid').html(response);
                            $('#posts-title').text('Search Results for "' + searchQuery + '"');
                            $('#back-to-all-btn').hide();
                            isSearchActive = true;
                            // Change button text to indicate reset functionality
                            $('#show-all-btn').text('Show All Posts').show();
                        },
                        error: function() {
                            console.log('Search error');
                        }
                    });
                } else {
                    // Empty search - reset to original posts
                    if (isSearchActive) {
                        $('#posts-grid').html(originalPostsHtml);
                        $('#posts-title').text('Browse All Posts');
                        isSearchActive = false;
                        if (hasMorePosts) {
                            $('#show-all-btn').show();
                        }
                    }
                }
            });

            $('.search-btn').on('click', function() {
                var searchQuery = $('#search-bar').val();
                if (searchQuery.length > 0) {
                    $('#search-bar').keyup();
                }
            });

            // Like button functionality
            $(document).on('click', '.like-btn', function(e) {
                e.preventDefault();

                var $likeBtn = $(this);

                // Prevent multiple clicks while request is pending
                if ($likeBtn.hasClass('loading')) {
                    return;
                }

                var $postCard = $likeBtn.closest('.post-card');
                var postId = $postCard.data('post-id');
                var isLiked = $likeBtn.hasClass('liked');
                var newLikedState = !isLiked;

                // Mark as loading
                $likeBtn.addClass('loading');

                $.ajax({
                    type: 'POST',
                    url: 'like_post.php',
                    data: {
                        postId: postId,
                        liked: newLikedState ? 1 : 0
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        console.log('Server response:', response);
                        var data = JSON.parse(response);
                        console.log('Parsed data:', data);

                        if (data.success) {
                            // Update button appearance based on the server's action response
                            if (data.action === 'liked') {
                                $likeBtn.addClass('liked');
                            } else if (data.action === 'unliked') {
                                $likeBtn.removeClass('liked');
                            }

                            var $likeCount = $likeBtn.find('.like-count');

                            if (data.likeCount > 0) {
                                if ($likeCount.length === 0) {
                                    $likeBtn.append('<span class="like-count">' + data.likeCount + '</span>');
                                } else {
                                    $likeCount.text(data.likeCount);
                                }
                            } else {
                                $likeCount.remove();
                            }

                            $likeBtn.attr('data-likes', data.likeCount);
                            console.log('Like updated successfully. Action:', data.action);
                        } else {
                            console.log('Error from server:', data.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX error:', error, xhr.status, xhr.responseText);
                    },
                    complete: function() {
                        // Remove loading flag
                        $likeBtn.removeClass('loading');
                    }
                });
            });
        });
    </script>
</body>

</html>