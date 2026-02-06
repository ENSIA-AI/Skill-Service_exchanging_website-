<?php
/**
 * Admin Events Management Page
 * Manage and oversee user events
 */

require_once __DIR__ . '/auth_admin.php';

// Handle event actions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $eventId = intval($_POST['event_id']);
    $action = $_POST['action'];

    if ($action === 'cancel') {
        $stmt = $conn->prepare('UPDATE Events SET EventStatus = "cancelled" WHERE EventId = ?');
        $stmt->bind_param('i', $eventId);
        if ($stmt->execute()) {
            $message = 'Event has been cancelled.';
        } else {
            $error = 'Failed to cancel event.';
        }
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare('DELETE FROM Events WHERE EventId = ?');
        $stmt->bind_param('i', $eventId);
        if ($stmt->execute()) {
            $message = 'Event has been deleted.';
        } else {
            $error = 'Failed to delete event.';
        }
    }
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = 'SELECT e.EventId, e.EventTitle, e.OrganizerId, e.EventStatus, e.EventStartDate, e.MaxAttendees, e.CurrentAttendeesNumber, u.UserName FROM Events e JOIN Users u ON e.OrganizerId = u.UserId WHERE 1=1';
$params = [];
$types = '';

if ($filter === 'upcoming') {
    $query .= " AND e.EventStatus = 'upcoming'";
} elseif ($filter === 'ongoing') {
    $query .= " AND e.EventStatus = 'ongoing'";
} elseif ($filter === 'cancelled') {
    $query .= " AND e.EventStatus = 'cancelled'";
}

if ($search !== '') {
    $query .= ' AND (e.EventTitle LIKE ? OR u.UserName LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $params = [$searchTerm, $searchTerm];
    $types = 'ss';
}

$query .= ' ORDER BY e.EventStartDate DESC LIMIT 50';

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$events = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Management - Admin</title>
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

        .event-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-upcoming {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-ongoing {
            background: #d1fae5;
            color: #065f46;
        }

        .status-cancelled {
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

        .event-title {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .attendance {
            font-size: 13px;
            color: #64748b;
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
                <a href="posts.php" class="nav-item">
                    <span class="icon">📝</span>
                    <span class="label">Posts Moderation</span>
                </a>
                <a href="events.php" class="nav-item active">
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
                <a href="notifications.php" class="nav-item">
                    <span class="icon">🔔</span>
                    <span class="label">Notifications</span>
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
                    <h1>Events Management</h1>
                    <p>Oversee and manage user events</p>
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
                    <input type="text" name="search" placeholder="Search event title or organizer..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                </form>
            </div>

            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <a href="events.php?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">All Events</a>
                <a href="events.php?filter=upcoming" class="filter-tab <?php echo $filter === 'upcoming' ? 'active' : ''; ?>">Upcoming</a>
                <a href="events.php?filter=ongoing" class="filter-tab <?php echo $filter === 'ongoing' ? 'active' : ''; ?>">Ongoing</a>
                <a href="events.php?filter=cancelled" class="filter-tab <?php echo $filter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
            </div>

            <!-- Events Table -->
            <div class="table-container">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Organizer</th>
                                <th>Date</th>
                                <th>Attendance</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($event = $events->fetch_assoc()): ?>
                                <tr>
                                    <td><span class="event-title"><strong><?php echo htmlspecialchars($event['EventTitle']); ?></strong></span></td>
                                    <td><?php echo htmlspecialchars($event['UserName']); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($event['EventStartDate'])); ?></td>
                                    <td>
                                        <span class="attendance">
                                            <?php echo $event['CurrentAttendeesNumber']; ?>/<?php echo $event['MaxAttendees']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="event-status status-<?php echo strtolower($event['EventStatus']); ?>">
                                            <?php echo ucfirst($event['EventStatus']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="event_id" value="<?php echo $event['EventId']; ?>">
                                            <div class="action-buttons">
                                                <?php if ($event['EventStatus'] !== 'cancelled'): ?>
                                                    <button type="submit" name="action" value="cancel" class="btn btn-warning btn-sm" onclick="return confirm('Cancel this event?')">Cancel</button>
                                                <?php endif; ?>
                                                <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete this event permanently?')">Delete</button>
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
