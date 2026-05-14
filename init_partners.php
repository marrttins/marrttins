<?php
require_once 'Mat/config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS partnerships (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        logo_url VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Partnerships table created successfully!";
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
?>
