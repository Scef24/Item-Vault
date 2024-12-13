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
    WHERE i.status = 'processing' AND cl.status = 'pending'
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
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

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: slideIn 0.5s ease;
        }

        .alert-success {
            background-color: #e3f2fd;
            color: #1565c0;
            border-left: 4px solid #1565c0;
        }

        .alert-danger {
            background-color: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }

        .item-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            padding: 10px;
        }

        .item-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .item-card h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #1a73e8;
        }

        .item-card p {
            margin: 8px 0;
            color: #666;
            font-size: 14px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .btn-approve {
            background-color: #28a745;
        }

        .btn-approve:hover {
            background-color: #218838;
        }

        .btn-reject {
            background-color: #dc3545;
        }

        .btn-reject:hover {
            background-color: #c82333;
        }

        .no-items {
            grid-column: 1/-1;
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 12px;
            color: #666;
            font-size: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

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

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            background-color: #e3f2fd;
            color: #1565c0;
            margin-bottom: 10px;
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

            .item-grid {
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
            <h1>Manage Claims</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <div class="item-grid">
                <?php if (empty($items)): ?>
                    <p class="no-items">No pending claims to process.</p>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <div class="item-card">
                            <?php if (!empty($item['image'])): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($item['image']); ?>" 
                                     class="item-image" alt="Item image">
                            <?php endif; ?>
                            
                            <div class="status-badge">Processing</div>
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p><strong>Type:</strong> <?php echo ucfirst($item['type']); ?></p>
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category_name']); ?></p>
                            <p><strong>Owner:</strong> <?php echo htmlspecialchars($item['owner_username']); ?></p>
                            <p><strong>Claimed by:</strong> <?php echo htmlspecialchars($item['claimer_username']); ?></p>
                            <p><strong>Claim Date:</strong> <?php echo date('M d, Y', strtotime($item['claim_date'])); ?></p>
                            
                            <div class="action-buttons">
                                <form method="POST" style="display: flex; gap: 10px; width: 100%;">
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
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
