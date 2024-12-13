<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

check_login('student');


$stmt = $pdo->query("SELECT * FROM item_categories ORDER BY name");
$categories = $stmt->fetchAll();


$where = ["i.status = 'pending' "];
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
    $where[] = "(i.title LIKE ? OR i.description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where[] = "c.name = ?";
    $params[] = $_GET['category'];
}

$query = "SELECT i.*, c.name as category_name, u.username 
          FROM items i 
          JOIN item_categories c ON i.category_id = c.id 
          JOIN users u ON i.user_id = u.id
          WHERE " . implode(' AND ', $where) . " 
          ORDER BY i.date_posted DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Items</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f2f5;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        .search-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .search-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
        }
        .category-tags {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .category-tag {
            padding: 8px 16px;
            background-color: #1a73e8;
            color: white;
            border-radius: 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .category-tag:hover {
            background-color: #1557b0;
        }
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }
        .item-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .item-details {
            padding: 15px;
        }
        .item-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .item-info {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
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
        .btn:hover {
            background-color: #1557b0;
        }
        .no-image {
            height: 200px;
            background-color: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
        }
        .claimed-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #ff4444;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }
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
        .claimed-badge {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 10px;
        }
        
        form {
            margin-top: 10px;
        }
        
        button.btn {
            background-color: #28a745;
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button.btn:hover {
            background-color: #218838;
        }
        .search-form {
            display: flex;
            flex: 1;
            gap: 10px;
            margin-right: 10px;
        }
        
        .search-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .category-tag {
            text-decoration: none;
            padding: 8px 16px;
            background-color: #1a73e8;
            color: white;
            border-radius: 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .category-tag.active {
            background-color: #1557b0;
            font-weight: bold;
        }
        
        .category-tag:hover {
            background-color: #1557b0;
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
            <div class="search-bar">
                <form action="" method="GET" class="search-form">
                    <input type="text" 
                           name="search" 
                           class="search-input" 
                           placeholder="Search for items not yet claimed" 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="btn">Search</button>
                </form>
                <a href="post_lost_item.php" class="btn">Lost item +</a>
            </div>
            
            <div class="category-tags">
                <a href="view_items.php" class="category-tag <?php echo !isset($_GET['category']) ? 'active' : ''; ?>">
                    All
                </a>
                <?php foreach ($categories as $category): ?>
                    <a href="?category=<?php echo urlencode($category['name']); ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" 
                       class="category-tag <?php echo (isset($_GET['category']) && $_GET['category'] === $category['name']) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <div class="items-grid">
                <?php if (empty($items)): ?>
                    <p style="grid-column: 1/-1; text-align: center; padding: 20px;">No items found.</p>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <div class="item-card">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                     class="item-image" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php else: ?>
                                <div class="no-image">No Image Available</div>
                            <?php endif; ?>
                            
                            <div class="item-details">
                                <h3 class="item-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                                <p class="item-info">Category: <?php echo htmlspecialchars($item['category_name']); ?></p>
                                <p class="item-info">Posted by: <?php echo htmlspecialchars($item['username']); ?></p>
                                <p class="item-info">Type: <?php echo ucfirst($item['type']); ?></p>
                                <p class="item-info">Date: <?php echo date('M d, Y', strtotime($item['date_posted'])); ?></p>
                                
                                <?php if ($item['status'] === 'claimed'): ?>
                                    <div class="claimed-badge">CLAIMED</div>
                                <?php elseif ($item['status'] === 'pending'): ?>
                                    <form action="claim_item.php" method="POST">
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <?php echo "Debug - Item ID: " . $item['id']; ?>
                                        <button type="submit" class="btn">Claim Item</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
