<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

check_login('student');

$item_id = isset($_GET['id']) ? $_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Verify item exists and belongs to user
$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND user_id = ?");
$stmt->execute([$item_id, $user_id]);
$item = $stmt->fetch();

if (!$item) {
    $_SESSION['error'] = "Item not found or access denied.";
    header("Location: my_items.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Delete the image file if it exists
        if (!empty($item['image_path']) && file_exists($item['image_path'])) {
            unlink($item['image_path']);
        }
        
        // Delete the item from database
        $stmt = $pdo->prepare("DELETE FROM items WHERE id = ? AND user_id = ?");
        $stmt->execute([$item_id, $user_id]);
        
        $_SESSION['success'] = "Item deleted successfully!";
        header("Location: my_items.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting item: " . $e->getMessage();
        header("Location: my_items.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Item</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
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
            <div class="form-container">
                <h1>Delete Item</h1>
                
                <div class="delete-confirmation">
                    <p>Are you sure you want to delete this item?</p>
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    
                    <?php if (!empty($item['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($item['image_path']); ?>" style="max-width: 200px;">
                    <?php endif; ?>
                    
                    <form method="POST">
                        <input type="hidden" name="confirm_delete" value="1">
                        <div class="button-group">
                            <button type="submit" class="btn delete">Confirm Delete</button>
                            <a href="my_items.php" class="btn cancel">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
