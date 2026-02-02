<?php
session_start();
require_once '../../DataBaseManagement/config.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../../auth/login.php");
    exit();
}

// For testing purposes, using a fixed user ID
// Replace this with actual session user ID when authentication is implemented
$currentUserId = 1; // $_SESSION['user_id'];

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
    $sql = 'SELECT p.PostId, p.Title, p.Description, p.LikeCount, c.CategoryName, u.UserName, u.Rating,
        (SELECT COUNT(*) FROM PostLikes WHERE PostId = p.PostId AND UserId = ?) as UserLiked
        FROM Posts p
        JOIN Users u ON p.UserId = u.UserId 
        JOIN Category c ON p.CategoryId = c.CategoryId 
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
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} elseif ($category_filter !== '') {
    // Filter by category only (server-side)
    $stmt = $conn->prepare('SELECT p.PostId, p.Title, p.Description, p.LikeCount, c.CategoryName, u.UserName, u.Rating,
        (SELECT COUNT(*) FROM PostLikes WHERE PostId = p.PostId AND UserId = ?) as UserLiked
        FROM Posts p 
        JOIN Users u ON p.UserId = u.UserId 
        JOIN Category c ON p.CategoryId = c.CategoryId 
        WHERE p.PostStatus = "active" AND c.CategoryName = ? 
        ORDER BY p.CreatedAt DESC');
    $stmt->bind_param('is', $currentUserId, $category_filter);
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $stmt = $conn->prepare('SELECT p.PostId, p.Title, p.Description, p.LikeCount, c.CategoryName, u.UserName, u.Rating,
        (SELECT COUNT(*) FROM PostLikes WHERE PostId = p.PostId AND UserId = ?) as UserLiked
        FROM Posts p 
        JOIN Users u ON p.UserId = u.UserId 
        JOIN Category c ON p.CategoryId = c.CategoryId 
        WHERE p.PostStatus = "active" 
        ORDER BY p.CreatedAt DESC LIMIT ? OFFSET ?');
    $stmt->bind_param('iii', $currentUserId, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);
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

            echo '<div class="post-card" data-post-id="' . htmlspecialchars($post['PostId']) . '" data-category="' . htmlspecialchars($post['CategoryName']) . '">';
            echo '<div class="post-header">';
            echo '<img src="/Skill-Service_exchanging_website-/assets/images/Default_pfp.svg" alt="Profile Picture" class="profile-pic">';
            echo '<div class="profile-info">';
            echo '<h3>' . htmlspecialchars($post['UserName']) . '</h3>';
            echo '<div class="stars">';
            echo $starsHtml;
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '<p class="post-field">' . htmlspecialchars($post['Title']) . '</p>';
            echo '<p class="post-description">' . htmlspecialchars(substr($post['Description'], 0, 100)) . '...</p>';
            echo '<div class="skills">';
            echo '<span>' . htmlspecialchars($post['CategoryName']) . '</span>';
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
    <link rel="stylesheet" href="/Skill-Service_exchanging_website-/assets/css/posts.css">
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
    </style>
</head>

<body>
    <?php include '../../components/header.html'; ?>
    <?php include '../../components/sidebar.html'; ?>

    <main class="main-content">
        <div class="search-container">
            <input type="text" id="search-bar" placeholder="Search for skills or services..." class="search-input">
            <button class="search-btn">
                <img src="/Skill-Service_exchanging_website-/assets/icons/search.svg" alt="">
            </button>
        </div>

        <button class="create-post-btn"
            onclick="window.location.href='/Skill-Service_exchanging_website-/dashboard/post/addpost.php'">
            <img src="/Skill-Service_exchanging_website-/assets/icons/plus.svg" alt="" class="plus-icon">
            <span>create new post</span>
        </button>

        <!-- Browse Categories Section -->
        <section class="browse-category">
            <h2>Browse Categories</h2>
            <div class="category-scroll">

                <?php $cat = 'Technology & Programming'; ?>
                <a class="category-card<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="/Skill-Service_exchanging_website-/assets/icons/laptop-open-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Design & Creative'; ?>
                <a class="category-card<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="/Skill-Service_exchanging_website-/assets/icons/color-wheel-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Photography & Video'; ?>
                <a class="category-card<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="/Skill-Service_exchanging_website-/assets/icons/camera-white-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Writing & Content'; ?>
                <a class="category-card<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="/Skill-Service_exchanging_website-/assets/icons/author-writer-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Music & Performance'; ?>
                <a class="category-card<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="/Skill-Service_exchanging_website-/assets/icons/guitar-color-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Cooking & Culinary'; ?>
                <a class="category-card<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="/Skill-Service_exchanging_website-/assets/icons/hot_cooking_icon_134855.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <button class="show-more-categories-btn" id="showMoreCategoriesBtn">Show More Categories</button>

                <?php $cat = 'Health & Fitness'; ?>
                <a class="category-card hidden more-categories<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="/Skill-Service_exchanging_website-/assets/icons/yoga-meditation-girl-clipart.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Languages'; ?>
                <a class="category-card hidden more-categories<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="/Skill-Service_exchanging_website-/assets/icons/online-community-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Automotive & Mechanics'; ?>
                <a class="category-card hidden more-categories<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="/Skill-Service_exchanging_website-/assets/icons/car-service-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Gardening & Nature'; ?>
                <a class="category-card hidden more-categories<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="/Skill-Service_exchanging_website-/assets/icons/pine-trees-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Business & Finance'; ?>
                <a class="category-card hidden more-categories<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="/Skill-Service_exchanging_website-/assets/icons/briefcase-emoji-clipart-original.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Arts & Crafts'; ?>
                <a class="category-card hidden more-categories<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="/Skill-Service_exchanging_website-/assets/icons/paint-palette-clipart.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <button class="show-more-categories-btn hidden" id="showMoreCategoriesBtn2">Show More
                    Categories</button>

                <?php $cat = 'Home Improvement'; ?>
                <a class="category-card hidden more-categories-2<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="/Skill-Service_exchanging_website-/assets/icons/service-provider-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Cleaning & Organization'; ?>
                <a class="category-card hidden more-categories-2<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="/Skill-Service_exchanging_website-/assets/icons/cleaning.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Science & Education'; ?>
                <a class="category-card hidden more-categories-2<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="/Skill-Service_exchanging_website-/assets/icons/interesting-facts-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </div>
                </a>

                <?php $cat = 'Fashion & Beauty'; ?>
                <a class="category-card hidden more-categories-2<?php echo ($category_filter === $cat) ? ' active' : ''; ?>" href="posts.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>">
                    <img src="/Skill-Service_exchanging_website-/assets/icons/woman-dress-icon.svg" alt="<?php echo htmlspecialchars($cat); ?>">
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
                        $rating = round((float) $post['Rating']);
                        $isLiked = $post['UserLiked'] > 0 ? 'liked' : '';
                        $likeCount = (int) $post['LikeCount'];
                        ?>
                        <div class="post-card" data-post-id="<?php echo htmlspecialchars($post['PostId']); ?>"
                            data-category="<?php echo htmlspecialchars($post['CategoryName']); ?>">
                            <div class="post-header">
                                <img src="/Skill-Service_exchanging_website-/assets/images/Default_pfp.svg"
                                    alt="Profile Picture" class="profile-pic">
                                <div class="profile-info">
                                    <h3><?php echo htmlspecialchars($post['UserName']); ?></h3>
                                    <div class="stars">
                                        <?php
                                        echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <p class="post-field"><?php echo htmlspecialchars($post['Title']); ?></p>
                            <p class="post-description">
                                <?php echo htmlspecialchars(substr($post['Description'], 0, 100)); ?>...
                            </p>

                            <div class="skills">
                                <span><?php echo htmlspecialchars($post['CategoryName']); ?></span>
                            </div>
                            <div class="post-actions">
                                <button class="see-details-btn"
                                    onclick="window.location.href='postdetails.php?Postid=<?php echo htmlspecialchars($post['PostId']); ?>'">See
                                    Details</button>
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
    <script src="/Skill-Service_exchanging_website-/assets/js/posts.js"></script>

    <script>
        var currentOffset = 12;
        var hasMorePosts = <?php echo count($posts) >= 12 ? 'true' : 'false'; ?>;

        $(document).ready(function () {
            // Show More Posts button with AJAX pagination
            $('#show-all-btn, #show-all-btn-bottom').on('click', function () {
                $.ajax({
                    type: 'GET',
                    url: 'posts.php',
                    data: { offset: currentOffset },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (response) {
                        if (response.trim() !== '' && response.includes('post-card')) {
                            $('#posts-grid').append(response);
                            currentOffset += 12;
                        } else {
                            $('#show-all-btn, #show-all-btn-bottom').hide();
                        }
                    },
                    error: function () {
                        console.log('Error loading more posts');
                    }
                });
            });

            // Show More Categories functionality
            $('#showMoreCategoriesBtn').on('click', function (e) {
                e.preventDefault();
                $('.more-categories').css('display', 'block');
                $(this).css('display', 'none');
                $('#showMoreCategoriesBtn2').css('display', 'flex');
            });

            $('#showMoreCategoriesBtn2').on('click', function (e) {
                e.preventDefault();
                $('.more-categories-2').css('display', 'block');
                $(this).css('display', 'none');
            });

            // Search functionality — show results without recommendation title
            $('#search-bar').on('keyup', function () {
                var searchQuery = $(this).val().trim();
                currentOffset = 12;

                if (searchQuery.length > 0) {
                    $.ajax({
                        type: 'GET',
                        url: 'posts.php',
                        data: { search: searchQuery },
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function (response) {
                            $('#posts-grid').html(response);
                            $('#posts-title').text('Browse All Posts');
                            $('#back-to-all-btn').hide();
                        },
                        error: function () {
                            console.log('Search error');
                        }
                    });
                } else {
                    location.reload();
                }
            });

            $('.search-btn').on('click', function () {
                var searchQuery = $('#search-bar').val();
                if (searchQuery.length > 0) {
                    $('#search-bar').keyup();
                }
            });

            // Like button functionality
            $(document).on('click', '.like-btn', function (e) {
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
                    success: function (response) {
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
                    error: function (xhr, status, error) {
                        console.log('AJAX error:', error, xhr.status, xhr.responseText);
                    },
                    complete: function () {
                        // Remove loading flag
                        $likeBtn.removeClass('loading');
                    }
                });
            });
        });
    </script>
</body>

</html>