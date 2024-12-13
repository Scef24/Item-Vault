<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

check_login('admin');

// Fetch all lost items with only the columns that have values
$stmt = $pdo->query("
    SELECT i.type, i.title, i.description, i.date_posted, i.date_item, 
           i.status, i.user_id, i.category_id, i.image, i.location_found,
           u.username, c.name as category_name 
    FROM items i 
    JOIN users u ON i.user_id = u.id 
    JOIN item_categories c ON i.category_id = c.id 
    WHERE i.type = 'lost'
    ORDER BY i.date_posted DESC
");
$lost_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Items</title>
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
      .nav-link {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px 0;
        }
        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
        }
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .item-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .item-image {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background-color: #ffd700; }
        .status-approved { background-color: #90EE90; }
        .status-claimed { background-color: #87CEEB; }
        .status-rejected { background-color: #FFB6C1; }
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
            <h1>Lost Items</h1>
            
            <div class="items-grid">
                <?php foreach ($lost_items as $item): ?>
                    <div class="item-card">
                        <?php if (!empty($item['image'])): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($item['image']); ?>" 
                                 class="item-image" alt="Item image">
                        <?php endif; ?>
                        
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                        
                        <?php if (!empty($item['category_name'])): ?>
                            <p>Category: <?php echo htmlspecialchars($item['category_name']); ?></p>
                        <?php endif; ?>
                        
                        <p>Posted by: <?php echo htmlspecialchars($item['username']); ?></p>
                        
                        <?php if (!empty($item['location_found'])): ?>
                            <p>Location: <?php echo htmlspecialchars($item['location_found']); ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($item['date_item'])): ?>
                            <p>Date Lost: <?php echo date('M d, Y', strtotime($item['date_item'])); ?></p>
                        <?php endif; ?>
                        
                        <p>Date Posted: <?php echo date('M d, Y', strtotime($item['date_posted'])); ?></p>
                        
                        <?php if (!empty($item['description'])): ?>
                            <p>Description: <?php echo htmlspecialchars($item['description']); ?></p>
                        <?php endif; ?>
                        
                        <p>Status: 
                            <span class="status-badge status-<?php echo htmlspecialchars($item['status']); ?>">
                                <?php echo ucfirst($item['status']); ?>
                            </span>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
