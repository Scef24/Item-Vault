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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <title>Lost Items</title>
    <style>
        /* Common CSS for all admin pages */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1a73e8 0%, #0d47a1 100%);
            color: white;
            padding: 25px;
            position: fixed;
            height: 100vh;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar h2 {
            font-size: 24px;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .nav-link {
            color: white;
            text-decoration: none;
            display: block;
            padding: 12px 15px;
            margin: 8px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link:hover {
            background-color: rgba(255,255,255,0.2);
            transform: translateX(5px);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 25px;
            color: #1a73e8;
        }

        /* Stats Grid (for dashboard) */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            color: #666;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .stat-card p {
            color: #1a73e8;
            font-size: 28px;
            font-weight: 600;
        }

        /* Table Styling */
        table {
            width: 100%;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            color: #666;
            font-weight: 500;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        /* Item Cards Grid */
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            padding: 10px;
        }

        .item-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .item-card:hover {
            transform: translateY(-5px);
        }

        .item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-pending { 
            background-color: #fff3cd;
            color: #856404;
        }

        .status-approved { 
            background-color: #d4edda;
            color: #155724;
        }

        .status-claimed { 
            background-color: #cce5ff;
            color: #004085;
        }

        .status-rejected { 
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: slideIn 0.5s ease;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        /* Buttons */
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-approve {
            background-color: #28a745;
            color: white;
        }

        .btn-reject {
            background-color: #dc3545;
            color: white;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        /* Animations */
        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .items-grid {
                grid-template-columns: 1fr;
            }

            .container {
                flex-direction: column;
            }
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
