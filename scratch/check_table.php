<?php
require_once 'mat/config.php';
try {
    $stmt = $pdo->query("DESCRIBE service_categories");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($columns);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
