<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

check_login('student');


error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];
    $claimer_id = $_SESSION['user_id'];
    
    try {
        $pdo->beginTransaction();
        
        // Debug output
        echo "Item ID: " . $item_id . "<br>";
        echo "Claimer ID: " . $claimer_id . "<br>";
        
       
        $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND status = 'pending'");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();
        
 
        var_dump($item);
        
        if ($item && $item['user_id'] != $claimer_id) {
       
            $stmt = $pdo->prepare("
                INSERT INTO claims (item_id, claimer_id, claim_date, status) 
                VALUES (?, ?, NOW(), 'pending')
            ");
            
            $result = $stmt->execute([$item_id, $claimer_id]);
            
           
            echo "Claim insert result: ";
            var_dump($result);
            
            
            $stmt = $pdo->prepare("UPDATE items SET status = 'processing' WHERE id = ?");
            $result2 = $stmt->execute([$item_id]);
            
     
            echo "Item update result: ";
            var_dump($result2);
            
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
  
        echo "Error: " . $e->getMessage();
    }
    
    
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    

    header("refresh:5;url=view_items.php");
    exit();
}
