<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';


check_login('student');


$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM items WHERE user_id = ? ORDER BY date_posted DESC LIMIT 5");
$stmt->execute([$user_id]);
$my_items = $stmt->fetchAll();


$stmt = $pdo->query("SELECT i.*, u.username FROM items i 
    JOIN users u ON i.user_id = u.id 
    WHERE i.status = 'approved'
    ORDER BY i.date_posted DESC LIMIT 5");
$recent_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
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
        .items-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .items-section {
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
        .btn {
            background-color: #1a73e8;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Student Panel</h2>
            <nav>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="post_lost_item.php" class="nav-link">Post Lost Item</a>
                <a href="post_found_item.php" class="nav-link">Post Found Item</a>
                <a href="my_items.php" class="nav-link">My Items</a>
                <a href="view_items.php" class="nav-link">Browse Items</a>
                <a href="my_claims.php" class = "nav-link">Claim Items</a>
                <a href="../logout.php" class="nav-link">Logout</a>
            </nav>
        </div>

        <div class="main-content">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            
            <div class="items-grid">
                <div class="items-section">
                    <h2>My Recent Items</h2>
                    <?php if (empty($my_items)): ?>
                        <p>You haven't posted any items yet.</p>
                    <?php else: ?>
                        <table width="100%">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Date Posted</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($my_items as $item): ?>
                                <tr>
                                    <td><?php echo ucfirst($item['type']); ?></td>
                                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                                    <td><?php echo ucfirst($item['status']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($item['date_posted'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                    <p><a href="my_items.php" class="btn">View All My Items</a></p>
                </div>

                <div class="items-section">
                    <h2>Recent Lost & Found Items</h2>
                    <?php if (empty($recent_items)): ?>
                        <p>No items available.</p>
                    <?php else: ?>
                        <table width="100%">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Posted By</th>
                                    <th>Date Posted</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_items as $item): ?>
                                <tr>
                                    <td><?php echo ucfirst($item['type']); ?></td>
                                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                                    <td><?php echo htmlspecialchars($item['username']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($item['date_posted'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                    <p><a href="view_items.php" class="btn">Browse All Items</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
