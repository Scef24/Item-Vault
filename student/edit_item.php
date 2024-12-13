<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

check_login('student');

$item_id = isset($_GET['id']) ? $_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Fetch categories
$stmt = $pdo->query("SELECT * FROM item_categories ORDER BY name");
$categories = $stmt->fetchAll();

// Fetch item details
$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND user_id = ?");
$stmt->execute([$item_id, $user_id]);
$item = $stmt->fetch();

// Check if item exists and belongs to user
if (!$item) {
    $_SESSION['error'] = "Item not found or access denied.";
    header("Location: my_items.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $category_id = $_POST['category_id'];
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    
    $errors = [];
    
    // Validation
    if (empty($title)) $errors[] = "Title is required";
    if (empty($category_id)) $errors[] = "Category is required";
    
    // Handle new image upload if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($filetype), $allowed)) {
            $errors[] = "Only JPG, JPEG, and PNG files are allowed";
        } else {
            $upload_dir = '../uploads/';
            $unique_filename = time() . '_' . uniqid() . '.' . $filetype;
            $image_path = '../uploads/' . $unique_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $unique_filename)) {
                // Delete old image if exists
                if (!empty($item['image_path']) && file_exists($item['image_path'])) {
                    unlink($item['image_path']);
                }
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    } else {
        $image_path = $item['image_path']; // Keep existing image
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE items SET 
                title = ?, 
                category_id = ?, 
                description = ?, 
                location_found = ?,
                image_path = ?
                WHERE id = ? AND user_id = ?");
                
            $stmt->execute([
                $title, 
                $category_id, 
                $description, 
                $location,
                $image_path,
                $item_id,
                $user_id
            ]);
            
            $_SESSION['success'] = "Item updated successfully!";
            header("Location: my_items.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Error updating item: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
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
                <h1>Edit Item</h1>
                
                <?php if (!empty($errors)): ?>
                    <div class="error">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Item Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($item['title']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                    <?php echo ($category['id'] == $item['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4"><?php echo htmlspecialchars($item['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($item['location_found']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Current Image</label>
                        <?php if (!empty($item['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" style="max-width: 200px;">
                        <?php else: ?>
                            <p>No image uploaded</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Upload New Image (optional)</label>
                        <input type="file" name="image" accept="image/*">
                    </div>
                    
                    <button type="submit" class="submit-btn">Update Item</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
