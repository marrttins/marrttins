<?php
// Database Configuration

// Detect if running on Vercel or using remote DB
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'marrthings');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    // Enable SSL if we are not on localhost (Remote DBs like TiDB require this)
    if (DB_HOST !== 'localhost') {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Create database if not exists (Only for local development)
    if (DB_HOST === 'localhost') {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        $pdo->exec("USE " . DB_NAME);
    }
    
} catch(PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

// Session Security
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin Configuration
define('ADMIN_EMAIL', 'marrthings@gmail.com'); // Update this to your real email

// Fetch categories from database
try {
    $cat_stmt = $pdo->query("SELECT name FROM service_categories ORDER BY name ASC");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categories = []; // Fallback
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function checkLogin() {
    if (!isLoggedIn()) {
        header("Location: login");
        exit;
    }
}

/**
 * Sends an email to the admin
 */
function sendAdminEmail($subject, $message) {
    $to = ADMIN_EMAIL;
    $headers = "From: Marrthings <noreply@marrthings.com>\r\n";
    $headers .= "Reply-To: noreply@marrthings.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    $htmlMessage = "
    <html>
    <head>
        <title>{$subject}</title>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
            <h2 style='color: #1a56db;'>New Notification from Marrthings</h2>
            <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
            {$message}
            <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
            <p style='font-size: 0.8rem; color: #777;'>This is an automated message from your website contact form.</p>
        </div>
    </body>
    </html>
    ";
    
    return @mail($to, $subject, $htmlMessage, $headers);
}
?>
