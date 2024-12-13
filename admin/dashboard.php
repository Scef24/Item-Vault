<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';


check_login('admin');


$stmt = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM items WHERE type='lost') as total_lost,
    (SELECT COUNT(*) FROM items WHERE type='found') as total_found,
    (SELECT COUNT(*) FROM items WHERE status='claimed') as total_claimed
");
$stats = $stmt->fetch();


$stmt = $pdo->query("SELECT i.*, u.username FROM items i 
    JOIN users u ON i.user_id = u.id 
    ORDER BY i.date_posted DESC LIMIT 5");
$recent_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #1a73e8;
            color: white;
            padding: 20px;
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .recent-items {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .nav-link {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px 0;
        }
        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <nav>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="manage_users.php" class="nav-link">Manage Users</a>
                <a href="manage_items.php" class="nav-link">Manage Items</a>
                <a href="view_lost_items.php" class="nav-link">Lost Items</a>
                <a href="view_found_items.php" class="nav-link">Found Items</a>
                <a href="../logout.php" class="nav-link">Logout</a>
            </nav>
        </div>

        <div class="main-content">
            <h1>Dashboard</h1>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p><?php echo $stats['total_users']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Lost Items</h3>
                    <p><?php echo $stats['total_lost']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Found Items</h3>
                    <p><?php echo $stats['total_found']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Claimed Items</h3>
                    <p><?php echo $stats['total_claimed']; ?></p>
                </div>
            </div>

            <div class="recent-items">
                <h2>Recent Items</h2>
                <table width="100%">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Posted By</th>
                            <th>Status</th>
                            <th>Date Posted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_items as $item): ?>
                        <tr>
                            <td><?php echo ucfirst($item['type']); ?></td>
                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                            <td><?php echo htmlspecialchars($item['username']); ?></td>
                            <td><?php echo ucfirst($item['status']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($item['date_posted'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
