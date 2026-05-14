<?php
require_once 'mat/config.php';

$categories = [
    "Web Development",
    "Logistics",
    "Hotel/Apartments",
    "SEO & Growth",
    "Digital Marketing",
    "E-commerce",
    "Booking",
    "Church website",
    "Automation",
    "Business Consulting",
    "NGO",
    "Branding",
    "Specialized Solutions",
    "School/LMS",
    "Cooperative website",
    "Mobile App",
    "Banking & Fintech",
    "Real Estate",
    "Saas Application",
    "Travel/Tour",
    "Affliate website",
    "Marketplace",
];

function createSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text);
}

try {
    // Optional: Clear existing categories if you want a fresh start
    // $pdo->exec("TRUNCATE TABLE service_categories");

    $stmt = $pdo->prepare("INSERT INTO service_categories (name, slug, icon) VALUES (?, ?, ?)");
    
    foreach ($categories as $cat) {
        // Check if exists first
        $check = $pdo->prepare("SELECT id FROM service_categories WHERE name = ?");
        $check->execute([$cat]);
        if (!$check->fetch()) {
            $slug = createSlug($cat);
            $stmt->execute([$cat, $slug, 'fa-briefcase']); // Default icon
            echo "Inserted: $cat\n";
        } else {
            echo "Skipped (exists): $cat\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
