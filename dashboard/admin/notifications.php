<?php
// Session is already started by auth_admin.php, so no need to start it here
require_once '../../DataBaseManagement/config.php';
require_once './auth_admin.php';

// Get filter parameters
$filterType = isset($_GET['type']) ? $_GET['type'] : '';
$filterSection = isset($_GET['section']) ? $_GET['section'] : '';
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';

// Build base query
$query = "SELECT NotificationId, RecipientId, SenderId, NotificationType, 
                 NotificationSection, Title, Message, IsRead, CreatedAt
          FROM UserNotifications
          WHERE 1=1";

$params = [];
$types = "";

// Apply filters
if (!empty($filterType)) {
    $query .= " AND NotificationType = ?";
    $params[] = $filterType;
    $types .= "s";
}

if (!empty($filterSection)) {
    $query .= " AND NotificationSection = ?";
    $params[] = $filterSection;
    $types .= "s";
}

if (!empty($filterStatus)) {
    if ($filterStatus === 'read') {
        $query .= " AND IsRead = 'yes'";
    } elseif ($filterStatus === 'unread') {
        $query .= " AND IsRead = 'no'";
    }
}

$query .= " ORDER BY CreatedAt DESC LIMIT 1000";

$notifications = [];
$error = '';

try {
    // If no filters, use simple query instead of prepared statement
    if (empty($params)) {
        $result = $conn->query($query);
        if (!$result) {
            throw new Exception('Query failed: ' . $conn->error);
        }
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
    } else {
        // Use prepared statement when we have parameters
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param($types, ...$params);
        
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        $stmt->close();
    }
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

// Get statistics
$stats = [
    'total' => 0,
    'unread' => 0,
    'bookings' => 0,
    'acceptances' => 0
];

try {
    $statsResult = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN IsRead = 'no' THEN 1 ELSE 0 END) as unread,
        SUM(CASE WHEN NotificationType = 'booking' THEN 1 ELSE 0 END) as bookings,
        SUM(CASE WHEN NotificationType = 'accepted' THEN 1 ELSE 0 END) as acceptances
        FROM UserNotifications");
    
    if ($statsResult) {
        $stats = $statsResult->fetch_assoc();
    }
} catch (Exception $e) {
    // Stats optional
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications Management - Admin</title>
    <link rel="icon" href="../../assets/icons/favicon_io/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="admin.css">
    <style>
        /* Stat Cards - Small compact section */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #6366f1;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-card h3 {
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin: 0 0 8px 0;
        }

        .stat-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        /* Filters section */
        .filters {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .filter-group {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .filter-group label {
            font-size: 11px;
            font-weight: 600;
            color: #333;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            color: #333;
            font-size: 12px;
            cursor: pointer;
        }

        .filters button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            background: #6366f1;
            color: white;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .filters button:hover {
            background: #4f46e5;
        }

        .filters button.reset {
            background: #e0e0e0;
            color: #333;
        }

        .filters button.reset:hover {
            background: #d0d0d0;
        }

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table thead {
            background: #f9f9f9;
            border-bottom: 1px solid #ddd;
        }

        .admin-table th {
            padding: 12px 15px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .admin-table tbody tr {
            border-bottom: 1px solid #f0f0f0;
        }

        .admin-table tbody tr:hover {
            background: #fafafa;
        }

        .admin-table td {
            padding: 12px 15px;
            color: #333;
            font-size: 12px;
        }

        /* Badges */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
        }

        .status-badge.unread {
            background: #ffcccc;
            color: #c62828;
        }

        .status-badge.read {
            background: #ccffcc;
            color: #2e7d32;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .badge-booking {
            background: #fff3e0;
            color: #e65100;
        }

        .badge-accepted {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-rejected {
            background: #ffebee;
            color: #c62828;
        }

        .badge-post {
            background: #f3e5f5;
            color: #6a1b9a;
        }

        .badge-event {
            background: #e3f2fd;
            color: #1565c0;
        }

        .badge-exchange {
            background: #fce4ec;
            color: #c2185b;
        }

        /* Action Buttons */
        .btn-action {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-read {
            background: #e3f2fd;
            color: #1565c0;
        }

        .btn-read:hover {
            background: #bbdefb;
        }

        .btn-accept {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .btn-accept:hover {
            background: #c8e6c9;
        }

        .btn-reject {
            background: #ffebee;
            color: #c62828;
        }

        .btn-reject:hover {
            background: #ffcdd2;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background: white;
            margin: 20% auto;
            padding: 25px;
            border-radius: 6px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }

        .modal-content h2 {
            margin-bottom: 12px;
            color: #333;
            font-size: 16px;
        }

        .modal-content p {
            color: #666;
            margin-bottom: 18px;
            line-height: 1.5;
            font-size: 13px;
        }

        .modal-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .btn-confirm {
            background: #4caf50;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
        }

        .btn-confirm:hover {
            background: #45a049;
        }

        .btn-cancel {
            background: #f44336;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
        }

        .btn-cancel:hover {
            background: #da190b;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h1 class="admin-title">Admin Panel</h1>
                <p class="admin-user">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></p>
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

                <a href="notifications.php" class="nav-item active">
                    <span class="icon">🔔</span>
                    <span class="label">Notifications</span>
                </a>

                <a href="logout.php" class="nav-item logout">
                    <span class="icon">🚪</span>
                    <span class="label">Logout</span>
                </a>
            </nav>
        </aside>

        <div class="admin-main">
            <header class="admin-header">
                <h1>Notification Management</h1>
                <p>Monitor and manage all system notifications</p>
            </header>

            <div class="admin-content">
                <!-- Error Message Display -->
                <?php if (!empty($error)): ?>
                    <div class="alert-error">
                        <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards SECTION -->
                <div class="stats-container">
                    <div class="stat-card">
                        <h3>Total Notifications</h3>
                        <div class="value"><?= $stats['total'] ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Unread</h3>
                        <div class="value"><?= $stats['unread'] ?? 0 ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Booking Requests</h3>
                        <div class="value"><?= $stats['bookings'] ?? 0 ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Acceptances</h3>
                        <div class="value"><?= $stats['acceptances'] ?? 0 ?></div>
                    </div>
                </div>

                <!-- Filters SECTION -->
                <div class="filters">
                    <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center; width: 100%;">
                        <div class="filter-group">
                            <label>Type</label>
                            <select name="type">
                                <option value="">All Types</option>
                                <option value="booking" <?= $filterType === 'booking' ? 'selected' : '' ?>>Booking</option>
                                <option value="accepted" <?= $filterType === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                                <option value="being_refused" <?= $filterType === 'being_refused' ? 'selected' : '' ?>>Rejected</option>
                                <option value="acceptedInEvent" <?= $filterType === 'acceptedInEvent' ? 'selected' : '' ?>>Event Accepted</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Section</label>
                            <select name="section">
                                <option value="">All Sections</option>
                                <option value="Exchange" <?= $filterSection === 'Exchange' ? 'selected' : '' ?>>Exchange</option>
                                <option value="events" <?= $filterSection === 'events' ? 'selected' : '' ?>>Events</option>
                                <option value="Reviews" <?= $filterSection === 'Reviews' ? 'selected' : '' ?>>Reviews</option>
                                <option value="credits" <?= $filterSection === 'credits' ? 'selected' : '' ?>>Credits</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Status</label>
                            <select name="status">
                                <option value="">All</option>
                                <option value="read" <?= $filterStatus === 'read' ? 'selected' : '' ?>>Read</option>
                                <option value="unread" <?= $filterStatus === 'unread' ? 'selected' : '' ?>>Unread</option>
                            </select>
                        </div>

                        <button type="submit">Filter</button>
                        <a href="notifications.php" style="text-decoration: none;"><button type="button" class="reset">Reset</button></a>
                    </form>
                </div>

                <!-- Notifications Table SECTION -->
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Title</th>
                                <th>Message</th>
                                <th>Type</th>
                                <th>Section</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($notifications)): ?>
                                <?php foreach ($notifications as $notif): ?>
                                    <tr>
                                        <td>
                                            <span class="status-badge <?= $notif['IsRead'] === 'yes' ? 'read' : 'unread' ?>">
                                                <?= $notif['IsRead'] === 'yes' ? '✓ Read' : '● Unread' ?>
                                            </span>
                                        </td>
                                        <td><strong><?= htmlspecialchars($notif['Title']) ?></strong></td>
                                        <td><?= htmlspecialchars(substr($notif['Message'], 0, 50)) ?><?= strlen($notif['Message']) > 50 ? '...' : '' ?></td>
                                        <td>
                                            <span class="badge badge-<?= str_replace('_', '-', strtolower($notif['NotificationType'])) ?>">
                                                <?= ucfirst(str_replace('_', ' ', $notif['NotificationType'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= strtolower($notif['NotificationSection']) ?>">
                                                <?= $notif['NotificationSection'] ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($notif['CreatedAt'])) ?></td>
                                        <td>
                                            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                                <?php if ($notif['IsRead'] === 'no'): ?>
                                                    <button class="btn-action btn-read" onclick="markAsRead(<?= $notif['NotificationId'] ?>)">Mark Read</button>
                                                <?php endif; ?>
                                                
                                                <?php if (in_array($notif['NotificationType'], ['booking', 'acceptedInEvent', 'RejectedFromEvent'])): ?>
                                                    <button class="btn-action btn-accept" onclick="handleNotification(<?= $notif['NotificationId'] ?>, 'accept')">Accept</button>
                                                    <button class="btn-action btn-reject" onclick="handleNotification(<?= $notif['NotificationId'] ?>, 'reject')">Reject</button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px; color: #999;">No notifications found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle">Confirm Action</h2>
            <p id="modalMessage"></p>
            <div class="modal-buttons">
                <button class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button class="btn-confirm" onclick="confirmAction()">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        let pendingAction = null;
        let pendingNotificationId = null;

        function markAsRead(notificationId) {
            fetch('../../api/notifications/mark_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    notificationId: notificationId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error marking notification as read');
            });
        }

        function handleNotification(notificationId, action) {
            pendingNotificationId = notificationId;
            pendingAction = action;

            const modal = document.getElementById('confirmModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');

            if (action === 'accept') {
                modalTitle.textContent = 'Accept Notification';
                modalMessage.textContent = 'This will accept the notification and send an acceptance message to the user. Are you sure?';
            } else {
                modalTitle.textContent = 'Reject Notification';
                modalMessage.textContent = 'This will reject the notification and send a rejection message to the user. Are you sure?';
            }

            modal.style.display = 'block';
        }

        function confirmAction() {
            const modal = document.getElementById('confirmModal');
            modal.style.display = 'none';

            fetch('notification_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    notificationId: pendingNotificationId,
                    action: pendingAction
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error processing notification');
            });
        }

        function closeModal() {
            document.getElementById('confirmModal').style.display = 'none';
            pendingAction = null;
            pendingNotificationId = null;
        }

        window.onclick = function(event) {
            const modal = document.getElementById('confirmModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
