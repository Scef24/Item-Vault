<?php
require_once 'config.php';
require_once 'db_connect.php';


$admin_username = 'admin';
$admin_password = 'admin123';
$admin_email = 'admin@gmail.com';

try {
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin WHERE username = ?");
    $stmt->execute([$admin_username]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo "Admin account already exists!";
    } else {
    
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO admin (username, password, email) 
            VALUES (?, ?, ?)
        ");

        $result = $stmt->execute([
            $admin_username,
            $hashed_password,
            $admin_email
        ]);

        if ($result) {
            echo "Admin account created successfully!<br>";
            echo "Username: " . $admin_username . "<br>";
            echo "Password: " . $admin_password . "<br>";
            echo "Email: " . $admin_email . "<br>";
            echo "<br>Please delete this file after creating the admin account for security!";
        } else {
            echo "Failed to create admin account.";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
