<?php
/**
 * Admin Categories & Skills Management
 */

require_once __DIR__ . '/auth_admin.php';

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $categoryName = trim($_POST['category_name']);
        $categoryDesc = trim($_POST['category_desc']);

        if (empty($categoryName)) {
            $error = 'Category name is required.';
        } else {
            $stmt = $conn->prepare('INSERT INTO Category (CategoryName, CategoryDescription) VALUES (?, ?)');
            $stmt->bind_param('ss', $categoryName, $categoryDesc);
            if ($stmt->execute()) {
                $message = 'Category added successfully.';
            } else {
                $error = 'Failed to add category.';
            }
        }
    } elseif (isset($_POST['delete_category'])) {
        $categoryId = intval($_POST['category_id']);
        $stmt = $conn->prepare('DELETE FROM Category WHERE CategoryId = ?');
        $stmt->bind_param('i', $categoryId);
        if ($stmt->execute()) {
            $message = 'Category deleted successfully.';
        } else {
            $error = 'Cannot delete category with associated skills.';
        }
    }
}

// Get categories
$categories = $conn->query('SELECT * FROM Category ORDER BY CategoryName LIMIT 50');

// Get skills
$skills = $conn->query('SELECT s.*, c.CategoryName FROM Skills s JOIN Category c ON s.CategoryId = c.CategoryId ORDER BY s.SkillName LIMIT 50');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories & Skills - Admin</title>
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
            grid-template-columns: 1fr 1fr auto;
            gap: 12px;
            margin-bottom: 16px;
        }

        .form-input {
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }

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
                <a href="categories.php" class="nav-item active">🏷️ Categories & Skills</a>
                <a href="exchanges.php" class="nav-item">🔄 Exchanges</a>
                <a href="transactions.php" class="nav-item">💳 Transactions</a>
                <a href="logout.php" class="nav-item logout">🚪 Logout</a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-title">
                    <h1>Categories & Skills</h1>
                    <p>Manage platform categories and skills</p>
                </div>
            </header>

            <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <!-- Add Category Form -->
            <div class="form-section">
                <h3 class="section-title">Add New Category</h3>
                <form method="POST">
                    <div class="form-grid">
                        <input type="text" name="category_name" class="form-input" placeholder="Category Name" required>
                        <input type="text" name="category_desc" class="form-input" placeholder="Description">
                        <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>

            <!-- Categories List -->
            <div class="table-container">
                <h3 style="padding: 20px 20px 10px; font-size: 16px; font-weight: bold;">Categories</h3>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Post Count</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($cat['CategoryName']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($cat['CategoryDescription'] ?? 'N/A'); ?></td>
                                    <td><?php echo $cat['PostCount']; ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="category_id" value="<?php echo $cat['CategoryId']; ?>">
                                            <button type="submit" name="delete_category" class="btn btn-danger btn-sm" onclick="return confirm('Delete this category?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Skills List -->
            <div class="table-container" style="margin-top: 30px;">
                <h3 style="padding: 20px 20px 10px; font-size: 16px; font-weight: bold;">Skills</h3>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Skill Name</th>
                                <th>Category</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($skill = $skills->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($skill['SkillName']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($skill['CategoryName']); ?></td>
                                    <td><?php echo htmlspecialchars($skill['SkillDescription'] ?? 'N/A'); ?></td>
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
