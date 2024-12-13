<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

check_login('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = $_POST['item_id'];
    $claim_id = $_POST['claim_id'];
    $action = $_POST['action'];
    
    try {
        if ($action === 'approve') {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE claims SET status = 'approved' WHERE id = ? AND status = 'pending'");
            $stmt->execute([$claim_id]);
            
            $stmt = $pdo->prepare("UPDATE items SET status = 'claimed' WHERE id = ?");
            $stmt->execute([$item_id]);
            
            $stmt = $pdo->prepare("UPDATE claims SET status = 'rejected' 
                                 WHERE item_id = ? AND id != ? AND status = 'pending'");
            $stmt->execute([$item_id, $claim_id]);
            
            $pdo->commit();
            $_SESSION['success'] = "Claim approved successfully!";
            
        } elseif ($action === 'reject') {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE claims SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$claim_id]);
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM claims WHERE item_id = ? AND status = 'pending'");
            $stmt->execute([$item_id]);
            $pending_claims = $stmt->fetchColumn();
            
            if ($pending_claims == 0) {
                $stmt = $pdo->prepare("UPDATE items SET status = 'pending' WHERE id = ?");
                $stmt->execute([$item_id]);
            }
            
            $pdo->commit();
            $_SESSION['success'] = "Claim rejected successfully!";
        }
    } catch (PDOException $e) {
        if (isset($pdo)) $pdo->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    header("Location: manage_items.php");
    exit();
}

$stmt = $pdo->query("
    SELECT i.*, 
           u.username as owner_username,
           c.name as category_name,
           cl.id as claim_id,
           cl.claimer_id,
           cl.claim_date,
           cl.status as claim_status,
           cu.username as claimer_username
    FROM items i 
    JOIN users u ON i.user_id = u.id 
    JOIN item_categories c ON i.category_id = c.id 
    JOIN claims cl ON i.id = cl.item_id
    JOIN users cu ON cl.claimer_id = cu.id
    WHERE i.status = '' AND cl.status = 'pending'
    ORDER BY cl.claim_date DESC
");
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Items</title>
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
        .item-grid {
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
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .btn-approve { background-color: #28a745; }
        .btn-reject { background-color: #dc3545; }
        .btn-delete { background-color: #6c757d; }
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
            <h1>Manage Claims</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <div class="item-grid">
                <?php foreach ($items as $item): ?>
                    <div class="item-card">
                        <?php if (!empty($item['image'])): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($item['image']); ?>" 
                                 class="item-image" alt="Item image">
                        <?php endif; ?>
                        
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p>Type: <?php echo ucfirst($item['type']); ?></p>
                        <p>Category: <?php echo htmlspecialchars($item['category_name']); ?></p>
                        <p>Owner: <?php echo htmlspecialchars($item['owner_username']); ?></p>
                        <p>Claimed by: <?php echo htmlspecialchars($item['claimer_username']); ?></p>
                        <p>Claim Date: <?php echo date('M d, Y', strtotime($item['claim_date'])); ?></p>
                        <p>Status: Processing</p>
                        
                        <div class="action-buttons">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="claim_id" value="<?php echo $item['claim_id']; ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-approve">
                                    Approve Claim
                                </button>
                                <button type="submit" name="action" value="reject" class="btn btn-reject">
                                    Reject Claim
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($items)): ?>
                    <p class="no-items">No pending claims to process.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
