<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

check_login('student');

// Clear any existing messages first
unset($_SESSION['success']);
unset($_SESSION['error']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];
    $claimer_id = $_SESSION['user_id'];
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND status = 'pending'");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();
        
        if ($item && $item['user_id'] != $claimer_id) {
            $stmt = $pdo->prepare("
                INSERT INTO claims (item_id, claimer_id, claim_date, status) 
                VALUES (?, ?, NOW(), 'pending')
            ");
            $stmt->execute([$item_id, $claimer_id]);
            
            $stmt = $pdo->prepare("UPDATE items SET status = 'processing' WHERE id = ?");
            $stmt->execute([$item_id]);
            
            $pdo->commit();
            $_SESSION['success'] = "Claim request submitted successfully!";
        } else {
            $pdo->rollBack();
            $_SESSION['error'] = "You cannot claim this item. " . 
                               ($item ? "You can't claim your own item." : "Item not found or not available.");
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error processing claim: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Claim</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f2f5;
        }
        .notification {
            background: white;
            padding: 20px 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        .success {
            border-left: 4px solid #28a745;
        }
        .success .message {
            color: #28a745;
        }
        .error {
            border-left: 4px solid #dc3545;
        }
        .error .message {
            color: #dc3545;
        }
        .message {
            margin: 10px 0;
            font-size: 16px;
            font-weight: 500;
        }
        .redirect-text {
            color: #666;
            font-size: 14px;
            margin-top: 15px;
        }
        .loading {
            margin: 15px 0;
            width: 40px;
            height: 40px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 15px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script>
        setTimeout(function() {
            window.location.href = 'view_items.php';
        }, 5000);
    </script>
</head>
<body>
    <div class="notification <?php echo isset($_SESSION['error']) ? 'error' : 'success'; ?>">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="message"><?php echo $_SESSION['success']; ?></div>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="message"><?php echo $_SESSION['error']; ?></div>
        <?php endif; ?>
        <div class="loading"></div>
        <div class="redirect-text">Redirecting to items page in 5 seconds...</div>
    </div>
</body>
</html>
<?php
// Clear the messages after displaying them
unset($_SESSION['success']);
unset($_SESSION['error']);
?>
