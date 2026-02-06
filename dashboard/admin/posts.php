<?php
/**
 * Admin Posts Moderation Page
 * Manage and moderate user posts
 */

require_once __DIR__ . '/auth_admin.php';

// Handle post actions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $postId = intval($_POST['post_id']);
    $action = $_POST['action'];

    if ($action === 'disable') {
        $stmt = $conn->prepare('UPDATE Posts SET PostStatus = "disabled" WHERE PostId = ?');
        $stmt->bind_param('i', $postId);
        if ($stmt->execute()) {
            $message = 'Post has been disabled.';
        } else {
            $error = 'Failed to disable post.';
        }
    } elseif ($action === 'enable') {
        $stmt = $conn->prepare('UPDATE Posts SET PostStatus = "active" WHERE PostId = ?');
        $stmt->bind_param('i', $postId);
        if ($stmt->execute()) {
            $message = 'Post has been enabled.';
        } else {
            $error = 'Failed to enable post.';
        }
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare('DELETE FROM posts WHERE PostId = ?');
        $stmt->bind_param('i', $postId);
        if ($stmt->execute()) {
            $message = 'Post has been deleted.';
        } else {
            $error = 'Failed to delete post.';
        }
    }
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = 'SELECT p.PostId, p.UserId, p.Title, p.PostStatus, p.CreatedAt, p.LikeCount, u.UserName FROM posts p JOIN users u ON p.UserId = u.UserId WHERE 1=1';
$params = [];
$types = '';

if ($filter === 'active') {
    $query .= " AND p.PostStatus = 'active'";
} elseif ($filter === 'disabled') {
    $query .= " AND p.PostStatus = 'disabled'";
}

if ($search !== '') {
    $query .= ' AND (p.Title LIKE ? OR u.UserName LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $params = [$searchTerm, $searchTerm];
    $types = 'ss';
}

$query .= ' ORDER BY p.CreatedAt DESC LIMIT 50';

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$posts = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts Moderation - Admin</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .management-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 16px;
        }

        .search-box {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }

        .filter-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }

        .filter-tab {
            padding: 8px 16px;
            border: 2px solid #e2e8f0;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .filter-tab:hover {
            border-color: #6366f1;
        }

        .filter-tab.active {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .table-responsive {
            overflow-x: auto;
        }

        .post-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-disabled {
            background: #fee2e2;
            color: #991b1b;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .post-title {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h1 class="admin-title">Admin Panel</h1>
                <p class="admin-user">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
            </div>

            <nav class="sidebar-nav">
                <a href="index.php" class="nav-item">
                    <span class="icon">📊</span>
                    <span class="label">Dashboard</span>
                </a>
                <a href="users.php" class="nav-item">
                    <span class="icon">👥</span>
                    <span class="label">Users Management</span>
                </a>
                <a href="posts.php" class="nav-item active">
                    <span class="icon">📝</span>
                    <span class="label">Posts Moderation</span>
                </a>
                <a href="events.php" class="nav-item">
                    <span class="icon">📅</span>
                    <span class="label">Events Management</span>
                </a>
                <a href="categories.php" class="nav-item">
                    <span class="icon">🏷️</span>
                    <span class="label">Categories & Skills</span>
                </a>
                <a href="exchanges.php" class="nav-item">
                    <span class="icon">🔄</span>
                    <span class="label">Exchanges</span>
                </a>
                <a href="transactions.php" class="nav-item">
                    <span class="icon">💳</span>
                    <span class="label">Transactions</span>
                </a>
                <a href="logout.php" class="nav-item logout">
                    <span class="icon">🚪</span>
                    <span class="label">Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <div class="header-title">
                    <h1>Posts Moderation</h1>
                    <p>Review and moderate user posts</p>
                </div>
            </header>

            <!-- Messages -->
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Search and Filter -->
            <div class="management-header">
                <form class="search-box" method="GET" action="">
                    <input type="text" name="search" placeholder="Search post title or username..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                </form>
            </div>

            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <a href="posts.php?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">All Posts</a>
                <a href="posts.php?filter=active" class="filter-tab <?php echo $filter === 'active' ? 'active' : ''; ?>">Active</a>
                <a href="posts.php?filter=disabled" class="filter-tab <?php echo $filter === 'disabled' ? 'active' : ''; ?>">Disabled</a>
            </div>

            <!-- Posts Table -->
            <div class="table-container">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Username</th>
                                <th>Created</th>
                                <th>Likes</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($post = $posts->fetch_assoc()): ?>
                                <tr>
                                    <td><span class="post-title"><strong><?php echo htmlspecialchars($post['Title']); ?></strong></span></td>
                                    <td><?php echo htmlspecialchars($post['UserName']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($post['CreatedAt'])); ?></td>
                                    <td><?php echo $post['LikeCount']; ?> ❤️</td>
                                    <td>
                                        <span class="post-status status-<?php echo strtolower($post['PostStatus']); ?>">
                                            <?php echo ucfirst($post['PostStatus']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="post_id" value="<?php echo $post['PostId']; ?>">
                                            <div class="action-buttons">
                                                <?php if ($post['PostStatus'] === 'active'): ?>
                                                    <button type="submit" name="action" value="disable" class="btn btn-warning btn-sm" onclick="return confirm('Disable this post?')">Disable</button>
                                                <?php else: ?>
                                                    <button type="submit" name="action" value="enable" class="btn btn-success btn-sm" onclick="return confirm('Enable this post?')">Enable</button>
                                                <?php endif; ?>
                                                <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete this post permanently?')">Delete</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
