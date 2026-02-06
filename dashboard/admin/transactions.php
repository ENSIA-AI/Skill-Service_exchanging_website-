<?php
/**
 * Admin Transactions Management
 */

require_once __DIR__ . '/auth_admin.php';

$message = '';
$error = '';

// Handle credit adjustment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adjust_credits'])) {
    $userId = intval($_POST['user_id']);
    $amount = intval($_POST['amount']);
    $description = trim($_POST['description']);

    if ($amount === 0) {
        $error = 'Amount must be non-zero.';
    } else {
        $conn->begin_transaction();
        try {
            // Get current balance
            $stmt = $conn->prepare('SELECT CreditBalance FROM users WHERE UserId = ?');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $newBalance = $result['CreditBalance'] + $amount;

            if ($newBalance < 0) {
                throw new Exception('Insufficient credits. Current balance: ' . $result['CreditBalance']);
            }

            // Update user credits
            $stmt = $conn->prepare('UPDATE Users SET CreditBalance = ? WHERE UserId = ?');
            $stmt->bind_param('ii', $newBalance, $userId);
            $stmt->execute();

            // Log transaction
            $type = $amount > 0 ? 'earned' : 'spent';
            $absAmount = abs($amount);
            $stmt = $conn->prepare('INSERT INTO CreditTransactions (UserId, TransactionType, Amount, BalanceAfter, Description) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('issis', $userId, $type, $absAmount, $newBalance, $description);
            $stmt->execute();

            $conn->commit();
            $message = 'Credits adjusted successfully. New balance: ' . $newBalance;
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get transactions
$query = 'SELECT t.TransactionId, t.UserId, u.UserName, t.TransactionType, t.Amount, t.BalanceAfter, t.Description, t.CreatedAt
          FROM CreditTransactions t
          JOIN Users u ON t.UserId = u.UserId WHERE 1=1';

$params = [];
$types = '';

if ($filter !== 'all') {
    $query .= ' AND t.TransactionType = ?';
    $params[] = $filter;
    $types .= 's';
}

if ($search !== '') {
    $query .= ' AND u.UserName LIKE ?';
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $types .= 's';
}

$query .= ' ORDER BY t.CreatedAt DESC LIMIT 100';

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$transactions = $stmt->get_result();

// Get users for credit adjustment form
$users_result = $conn->query('SELECT UserId, UserName, CreditBalance FROM Users ORDER BY UserName LIMIT 50');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Admin</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .form-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 12px;
        }

        .form-input, .form-select {
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
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
        }

        .filter-tab.active {
            background: #6366f1;
            color: white;
        }

        .type-earned { color: #10b981; font-weight: 600; }
        .type-spent { color: #ef4444; font-weight: 600; }

        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
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
                <a href="exchanges.php" class="nav-item">🔄 Exchanges</a>
                <a href="transactions.php" class="nav-item active">💳 Transactions</a>
                <a href="logout.php" class="nav-item logout">🚪 Logout</a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-title">
                    <h1>Transactions & Credits</h1>
                    <p>Manage user credits and credit transactions</p>
                </div>
            </header>

            <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <!-- Credit Adjustment Form -->
            <div class="form-section">
                <h3 class="section-title">Adjust User Credits</h3>
                <form method="POST">
                    <div class="form-grid">
                        <select name="user_id" class="form-select" required>
                            <option value="">Select User</option>
                            <?php 
                            $users_result = $conn->query('SELECT UserId, UserName, CreditBalance FROM users ORDER BY UserName LIMIT 50');
                            while ($u = $users_result->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $u['UserId']; ?>">
                                    <?php echo htmlspecialchars($u['UserName']); ?> (<?php echo $u['CreditBalance']; ?> credits)
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <input type="number" name="amount" class="form-input" placeholder="Amount (+/-)" required>
                        <input type="text" name="description" class="form-input" placeholder="Description (optional)">
                        <button type="submit" name="adjust_credits" class="btn btn-primary">Adjust</button>
                    </div>
                </form>
            </div>

            <!-- Transactions List -->
            <div class="filter-tabs">
                <a href="transactions.php?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
                <a href="transactions.php?filter=earned" class="filter-tab <?php echo $filter === 'earned' ? 'active' : ''; ?>">Earned</a>
                <a href="transactions.php?filter=spent" class="filter-tab <?php echo $filter === 'spent' ? 'active' : ''; ?>">Spent</a>
            </div>

            <div class="table-container">
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Balance After</th>
                                <th>Description</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($t = $transactions->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($t['UserName']); ?></strong></td>
                                    <td><span class="type-<?php echo $t['TransactionType']; ?>"><?php echo ucfirst($t['TransactionType']); ?></span></td>
                                    <td><?php echo $t['Amount']; ?> 💳</td>
                                    <td><?php echo $t['BalanceAfter']; ?> 💳</td>
                                    <td><?php echo htmlspecialchars($t['Description'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($t['CreatedAt'])); ?></td>
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
