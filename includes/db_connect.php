<?php
require_once 'config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    
    function initializeCategories($pdo) {
      
        $stmt = $pdo->query("SELECT COUNT(*) FROM item_categories");
        $count = $stmt->fetchColumn();

        if ($count == 0) {
           
            $categories = [
                'Phone',
                'Accessories',
                'Tumbler',
                'Laptop',
                'Umbrella',
                'ID',
                'Others'
            ];

         
            $stmt = $pdo->prepare("INSERT INTO item_categories (name) VALUES (:name)");

          
            foreach ($categories as $category) {
                $stmt->execute(['name' => $category]);
            }
        }
    }

    // Call the initialization function
    initializeCategories($pdo);

} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
