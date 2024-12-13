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
    <link rel="stylesheet" href="../assets/css/style.css">
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
