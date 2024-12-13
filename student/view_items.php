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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <title>View Items</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #1a73e8 0%, #0d47a1 100%);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            display: block;
            padding: 12px 15px;
            margin: 8px 0;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        .btn {
            background: linear-gradient(45deg, #1a73e8, #0d47a1);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .item-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
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
        }

        .item-details {
            padding: 20px;
        }

        .item-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .item-info {
            color: #666;
            margin: 5px 0;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input[type="text"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        select:focus,
        textarea:focus {
            border-color: #1a73e8;
            outline: none;
        }

        .error {
            background: #ffe3e3;
            color: #d63031;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .search-bar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
        }

        .search-input {
            flex: 1;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 6px;
            font-size: 14px;
        }

        .category-tags {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .category-tag {
            padding: 8px 16px;
            background: linear-gradient(45deg, #1a73e8, #0d47a1);
            color: white;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .category-tag:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .category-tag.active {
            background: linear-gradient(45deg, #0d47a1, #1a73e8);
            font-weight: 600;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            animation: fadeIn 0.5s ease;
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
