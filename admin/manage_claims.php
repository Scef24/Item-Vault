
<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

check_login('admin');


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $claim_id = $_POST['claim_id'];
    $action = $_POST['action'];
    $item_id = $_POST['item_id'];
    
    try {
   
        $stmt = $pdo->prepare("UPDATE claims SET status = ? WHERE id = ?");
        $stmt->execute([$action, $claim_id]);
        
    
        $item_status = ($action == 'approved') ? 'claimed' : 'approved';
        $stmt = $pdo->prepare("UPDATE items SET status = ? WHERE id = ?");
        $stmt->execute([$item_status, $item_id]);
        
        $_SESSION['success'] = "Claim " . ucfirst($action) . " successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error processing action: " . $e->getMessage();
    }
    
    header("Location: manage_claims.php");
    exit();
}

$stmt = $pdo->query("
    SELECT c.*, i.title, i.image_path, i.type, 
           u1.username as claimer_name,
           u2.username as owner_name
    FROM claims c 
    JOIN items i ON c.item_id = i.id 
    JOIN users u1 ON c.claimer_id = u1.id
    JOIN users u2 ON i.user_id = u2.id
    WHERE c.status = 'pending'
    ORDER BY c.claim_date DESC
");
$pending_claims = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Claims</title>
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
            <h1>Manage Claims</h1>
            
            <div class="claims-grid">
                <?php foreach ($pending_claims as $claim): ?>
                    <div class="claim-card">
                        <?php if (!empty($claim['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($claim['image_path']); ?>" class="item-image" alt="Item image">
                        <?php endif; ?>
                        
                        <div class="claim-details">
                            <h3><?php echo htmlspecialchars($claim['title']); ?></h3>
                            <p>Claimed by: <?php echo htmlspecialchars($claim['claimer_name']); ?></p>
                            <p>Owner: <?php echo htmlspecialchars($claim['owner_name']); ?></p>
                            <p>Claim Date: <?php echo date('M d, Y', strtotime($claim['claim_date'])); ?></p>
                            
                            <form method="POST" class="claim-actions">
                                <input type="hidden" name="claim_id" value="<?php echo $claim['id']; ?>">
                                <input type="hidden" name="item_id" value="<?php echo $claim['item_id']; ?>">
                                <button type="submit" name="action" value="approved" class="btn approve">Approve</button>
                                <button type="submit" name="action" value="rejected" class="btn reject">Reject</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>