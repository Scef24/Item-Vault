
<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

check_login('student');

$user_id = $_SESSION['user_id'];

// Fetch user's claims
$stmt = $pdo->prepare("
    SELECT c.*, i.title, i.image_path, i.type, u.username as owner_name 
    FROM claims c 
    JOIN items i ON c.item_id = i.id 
    JOIN users u ON i.user_id = u.id 
    WHERE c.claimer_id = ?
    ORDER BY c.claim_date DESC
");
$stmt->execute([$user_id]);
$claims = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Claims</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<style>body {
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
        }</style>
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
            <h1>My Claims</h1>
            
            <div class="claims-grid">
                <?php foreach ($claims as $claim): ?>
                    <div class="claim-card">
                        <?php if (!empty($claim['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($claim['image_path']); ?>" class="item-image" alt="Item image">
                        <?php endif; ?>
                        
                        <div class="claim-details">
                            <h3><?php echo htmlspecialchars($claim['title']); ?></h3>
                            <p>Owner: <?php echo htmlspecialchars($claim['owner_name']); ?></p>
                            <p>Status: <span class="status-badge <?php echo $claim['status']; ?>">
                                <?php echo ucfirst($claim['status']); ?>
                            </span></p>
                            <p>Claim Date: <?php echo date('M d, Y', strtotime($claim['claim_date'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>