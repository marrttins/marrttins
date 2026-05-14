<?php
require_once 'mat/config.php';
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM projects");
    $count = $stmt->fetchColumn();
    echo "Total projects: " . $count . "\n";
    
    $stmt = $pdo->query("SELECT * FROM projects LIMIT 1");
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($project);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
