<?php
/**
 * Admin Exchanges Management
 */

require_once __DIR__ . '/auth_admin.php';

// Get exchanges with filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = 'SELECT e.ExchangeId, e.Status, e.ProposedDate, e.ConfirmedDate, e.CompletedDate, 
                  u1.UserName as OfferedBy, u2.UserName as RequestedBy, p.Title as PostTitle
          FROM Exchanges e
          JOIN Users u1 ON e.OfferedByUserId = u1.UserId
          JOIN Users u2 ON e.RequestedByUserId = u2.UserId
          JOIN Posts p ON e.PostId = p.PostId WHERE 1=1';

$params = [];
$types = '';

if ($filter !== 'all') {
    $query .= ' AND e.Status = ?';
    $params[] = $filter;
    $types .= 's';
}

if ($search !== '') {
    $query .= ' AND (p.Title LIKE ? OR u1.UserName LIKE ? OR u2.UserName LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    $types .= 'sss';
}

$query .= ' ORDER BY e.CreatedAt DESC LIMIT 50';

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$exchanges = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exchanges Management - Admin</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            max-width: 400px;
        }

        .search-box input {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }

        .filter-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 8px 16px;
            border: 2px solid #e2e8f0;
            background: white;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-tab.active {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
        }

        .status-pending { color: #f59e0b; font-weight: 600; }
        .status-accepted { color: #10b981; font-weight: 600; }
        .status-rejected { color: #ef4444; font-weight: 600; }
        .status-completed { color: #3b82f6; font-weight: 600; }
        .status-cancelled { color: #6b7280; font-weight: 600; }

        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h1 class="admin-title">Admin Panel</h1>
                <p class="admin-user">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
            </div>

            <nav class="sidebar-nav">
                <a href="index.php" class="nav-item">📊 Dashboard</a>
                <a href="users.php" class="nav-item">👥 Users Management</a>
                <a href="posts.php" class="nav-item">📝 Posts Moderation</a>
                <a href="events.php" class="nav-item">📅 Events Management</a>
                <a href="categories.php" class="nav-item">🏷️ Categories & Skills</a>
                <a href="exchanges.php" class="nav-item active">🔄 Exchanges</a>
                <a href="transactions.php" class="nav-item">💳 Transactions</a>
                <a href="notifications.php" class="nav-item">🔔 Notifications</a>
                <a href="logout.php" class="nav-item logout">🚪 Logout</a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-title">
                    <h1>Exchanges Management</h1>
                    <p>Monitor and oversee skill exchanges</p>
                </div>
            </header>

            <form class="search-box" method="GET">
                <input type="text" name="search" placeholder="Search post or username..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
            </form>

            <div class="filter-tabs">
                <a href="exchanges.php?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
                <a href="exchanges.php?filter=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">Pending</a>
                <a href="exchanges.php?filter=accepted" class="filter-tab <?php echo $filter === 'accepted' ? 'active' : ''; ?>">Accepted</a>
                <a href="exchanges.php?filter=completed" class="filter-tab <?php echo $filter === 'completed' ? 'active' : ''; ?>">Completed</a>
                <a href="exchanges.php?filter=rejected" class="filter-tab <?php echo $filter === 'rejected' ? 'active' : ''; ?>">Rejected</a>
            </div>

            <div class="table-container">
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Post Title</th>
                                <th>Offered By</th>
                                <th>Requested By</th>
                                <th>Status</th>
                                <th>Dates</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($ex = $exchanges->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($ex['PostTitle']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($ex['OfferedBy']); ?></td>
                                    <td><?php echo htmlspecialchars($ex['RequestedBy']); ?></td>
                                    <td><span class="status-<?php echo strtolower($ex['Status']); ?>"><?php echo ucfirst($ex['Status']); ?></span></td>
                                    <td>
                                        <small>
                                            Proposed: <?php echo date('M d', strtotime($ex['ProposedDate'])); ?><br>
                                            <?php if ($ex['CompletedDate']): ?>
                                                Completed: <?php echo date('M d', strtotime($ex['CompletedDate'])); ?>
                                            <?php endif; ?>
                                        </small>
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
