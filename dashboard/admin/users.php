<?php
/**
 * Admin Users Management Page
 * Manage user accounts, ban/unban users, edit user info
 */

require_once __DIR__ . '/auth_admin.php';

// Handle user actions
$message = '';
$error = '';

// Ban/Unban user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $userId = intval($_POST['user_id']);
    $action = $_POST['action'];

    if ($action === 'ban') {
        $stmt = $conn->prepare('UPDATE Users SET IsBanned = "yes" WHERE UserId = ?');
        $stmt->bind_param('i', $userId);
        if ($stmt->execute()) {
            $message = 'User has been banned successfully.';
        } else {
            $error = 'Failed to ban user.';
        }
    } elseif ($action === 'unban') {
        $stmt = $conn->prepare('UPDATE Users SET IsBanned = "no" WHERE UserId = ?');
        $stmt->bind_param('i', $userId);
        if ($stmt->execute()) {
            $message = 'User has been unbanned successfully.';
        } else {
            $error = 'Failed to unban user.';
        }
    } elseif ($action === 'make_admin') {
        $stmt = $conn->prepare('UPDATE Users SET IsAdmin = "yes" WHERE UserId = ?');
        $stmt->bind_param('i', $userId);
        if ($stmt->execute()) {
            $message = 'User is now an admin.';
        } else {
            $error = 'Failed to make user admin.';
        }
    } elseif ($action === 'remove_admin') {
        // Don't allow removing the last admin
        $adminCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE IsAdmin = 'yes'")->fetch_assoc()['count'];
        if ($adminCount > 1) {
            $stmt = $conn->prepare('UPDATE Users SET IsAdmin = "no" WHERE UserId = ?');
            $stmt->bind_param('i', $userId);
            if ($stmt->execute()) {
                $message = 'Admin privileges removed.';
            } else {
                $error = 'Failed to remove admin privileges.';
            }
        } else {
            $error = 'Cannot remove admin privileges from the last admin.';
        }
    }
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = 'SELECT UserId, UserName, Email, FullName, UserSince, Rating, RatingCount, CreditBalance, IsBanned, IsAdmin, ExchangeCount FROM users WHERE 1=1';
$params = [];
$types = '';

if ($filter === 'banned') {
    $query .= " AND IsBanned = 'yes'";
} elseif ($filter === 'admin') {
    $query .= " AND IsAdmin = 'yes'";
}

if ($search !== '') {
    $query .= ' AND (UserName LIKE ? OR Email LIKE ? OR FullName LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $params = [$searchTerm, $searchTerm, $searchTerm];
    $types = 'sss';
}

$query .= ' ORDER BY UserSince DESC LIMIT 50';

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Admin</title>
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

        .user-status {
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

        .status-banned {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-admin {
            background: #dbeafe;
            color: #1e40af;
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
                <a href="users.php" class="nav-item active">
                    <span class="icon">👥</span>
                    <span class="label">Users Management</span>
                </a>
                <a href="posts.php" class="nav-item">
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
                    <h1>Users Management</h1>
                    <p>Manage user accounts and permissions</p>
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
                    <input type="text" name="search" placeholder="Search username, email..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                </form>
            </div>

            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <a href="users.php?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">All Users</a>
                <a href="users.php?filter=banned" class="filter-tab <?php echo $filter === 'banned' ? 'active' : ''; ?>">Banned Users</a>
                <a href="users.php?filter=admin" class="filter-tab <?php echo $filter === 'admin' ? 'active' : ''; ?>">Admins</a>
            </div>

            <!-- Users Table -->
            <div class="table-container">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Full Name</th>
                                <th>Joined</th>
                                <th>Rating</th>
                                <th>Credits</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($user['UserName']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['Email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['FullName']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($user['UserSince'])); ?></td>
                                    <td>
                                        ⭐ <?php echo number_format($user['Rating'], 2); ?>
                                        <span style="font-size: 12px; color: #64748b;">(<?php echo $user['RatingCount']; ?>)</span>
                                    </td>
                                    <td><?php echo $user['CreditBalance']; ?></td>
                                    <td>
                                        <?php if ($user['IsBanned'] === 'yes'): ?>
                                            <span class="user-status status-banned">Banned</span>
                                        <?php elseif ($user['IsAdmin'] === 'yes'): ?>
                                            <span class="user-status status-admin">Admin</span>
                                        <?php else: ?>
                                            <span class="user-status status-active">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['UserId']; ?>">
                                            <div class="action-buttons">
                                                <?php if ($user['IsBanned'] === 'no'): ?>
                                                    <button type="submit" name="action" value="ban" class="btn btn-danger btn-sm" onclick="return confirm('Ban this user?')">Ban</button>
                                                <?php else: ?>
                                                    <button type="submit" name="action" value="unban" class="btn btn-success btn-sm" onclick="return confirm('Unban this user?')">Unban</button>
                                                <?php endif; ?>

                                                <?php if ($user['IsAdmin'] === 'no'): ?>
                                                    <button type="submit" name="action" value="make_admin" class="btn btn-primary btn-sm" onclick="return confirm('Make this user admin?')">Make Admin</button>
                                                <?php else: ?>
                                                    <button type="submit" name="action" value="remove_admin" class="btn btn-warning btn-sm" onclick="return confirm('Remove admin privileges?')">Remove Admin</button>
                                                <?php endif; ?>
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
