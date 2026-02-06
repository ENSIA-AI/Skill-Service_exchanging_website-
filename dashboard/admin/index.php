<?php
/**
 * Admin Dashboard - Home Page
 * Shows overview statistics and management options
 */

require_once __DIR__ . '/auth_admin.php';

// Get statistics from database
$stats = [];

// Total users
$result = $conn->query('SELECT COUNT(*) as count FROM users');
$stats['total_users'] = $result->fetch_assoc()['count'];

// Active posts
$result = $conn->query("SELECT COUNT(*) as count FROM posts WHERE PostStatus = 'active'");
$stats['active_posts'] = $result->fetch_assoc()['count'];

// Active events
$result = $conn->query("SELECT COUNT(*) as count FROM events WHERE EventStatus IN ('upcoming', 'ongoing')");
$stats['active_events'] = $result->fetch_assoc()['count'];

// Pending exchanges
$result = $conn->query("SELECT COUNT(*) as count FROM Exchanges WHERE Status = 'pending'");
$stats['pending_exchanges'] = $result->fetch_assoc()['count'];

// Pending reports removed - Reports table no longer exists
$stats['pending_reports'] = 0;

// Total transactions
$result = $conn->query('SELECT COUNT(*) as count FROM CreditTransactions');
$stats['total_transactions'] = $result->fetch_assoc()['count'];

// Banned users
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE IsBanned = 'yes'");
$stats['banned_users'] = $result->fetch_assoc()['count'];

// Recent users (last 5)
$recent_users = $conn->query("
    SELECT UserId, UserName, Email, UserSince 
    FROM users 
    ORDER BY UserSince DESC 
    LIMIT 5
");

// Recent reports removed - Reports table no longer exists
$recent_reports = null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Skill Service Exchange</title>
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="admin.css">
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
                <a href="index.php" class="nav-item active">
                    <span class="icon">📊</span>
                    <span class="label">Dashboard</span>
                </a>
                <a href="users.php" class="nav-item">
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
                    <h1>Dashboard</h1>
                    <p>Welcome to the Admin Control Panel</p>
                </div>
                <div class="header-info">
                    <span class="timestamp">Last updated: <?php echo date('Y-m-d H:i:s'); ?></span>
                </div>
            </header>

            <!-- Statistics Cards -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon users">👥</div>
                        <div class="stat-content">
                            <p class="stat-label">Total Users</p>
                            <p class="stat-value"><?php echo $stats['total_users']; ?></p>
                            <a href="users.php" class="stat-link">View All →</a>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon posts">📝</div>
                        <div class="stat-content">
                            <p class="stat-label">Active Posts</p>
                            <p class="stat-value"><?php echo $stats['active_posts']; ?></p>
                            <a href="posts.php" class="stat-link">Moderate →</a>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon events">📅</div>
                        <div class="stat-content">
                            <p class="stat-label">Active Events</p>
                            <p class="stat-value"><?php echo $stats['active_events']; ?></p>
                            <a href="events.php" class="stat-link">Manage →</a>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon exchanges">🔄</div>
                        <div class="stat-content">
                            <p class="stat-label">Pending Exchanges</p>
                            <p class="stat-value"><?php echo $stats['pending_exchanges']; ?></p>
                            <a href="exchanges.php" class="stat-link">Review →</a>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon transactions">💳</div>
                        <div class="stat-content">
                            <p class="stat-label">Total Transactions</p>
                            <p class="stat-value"><?php echo $stats['total_transactions']; ?></p>
                            <a href="transactions.php" class="stat-link">View →</a>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon banned">🚫</div>
                        <div class="stat-content">
                            <p class="stat-label">Banned Users</p>
                            <p class="stat-value"><?php echo $stats['banned_users']; ?></p>
                            <a href="users.php?filter=banned" class="stat-link">View →</a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Recent Activity -->
            <section class="activity-section">
                <div class="activity-container">
                    <!-- Recent Users -->
                    <div class="activity-card">
                        <h3 class="activity-title">Recent Users</h3>
                        <div class="activity-list">
                            <?php while ($user = $recent_users->fetch_assoc()): ?>
                                <div class="activity-item">
                                    <div class="activity-info">
                                        <p class="activity-name"><?php echo htmlspecialchars($user['UserName']); ?></p>
                                        <p class="activity-date"><?php echo date('M d, Y', strtotime($user['UserSince'])); ?></p>
                                    </div>
                                    <a href="users.php?edit=<?php echo $user['UserId']; ?>" class="activity-action">Edit</a>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
