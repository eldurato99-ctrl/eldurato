<?php
// setup.php
header('Content-Type: text/plain');

$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'ecommerce';
$user = getenv('DB_USER') ?: 'ecommerce';
$pass = getenv('DB_PASSWORD') ?: 'strongpassword';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "✔ [DATABASE] '$dbname' checked/created.\n";
    
    $pdo->exec("USE `$dbname`");
    
    // 🛠️ FIX THE BUG: Purani conflicting tables ko drop kar rahe hain taaki naya column schema run ho sake
    echo "🧹 [CLEANUP] Purging existing structural relational tables for fresh setup...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("DROP TABLE IF EXISTS `order_items`;");
    $pdo->exec("DROP TABLE IF EXISTS `all_orders_list`;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "✔ [CLEANUP] Tables dropped successfully.\n\n";
    
    $migrationDir = __DIR__ . '/migrations/';
    
    if (is_dir($migrationDir)) {
        $files = scandir($migrationDir);
        
        // Alphabetic tracking rules ke liye files ko sort kar rahe hain
        sort($files);
        
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $filePath = $migrationDir . $file;
                $sql = include $filePath;
                
                if (!empty($sql)) {
                    $pdo->exec($sql);
                    echo "✔ [MIGRATION] Executed: $file\n";
                }
            }
        }
        echo "\n🚀 [SUCCESS] All migrations compiled successfully with 'tracking_status' schema!\n";
        
        // Agar kisi page ne auto-redirect kiya tha, to wapas wahan bhejo
        if (isset($_GET['return'])) {
            echo "Redirecting back to your page...";
            header("refresh:2;url=" . $_GET['return']);
            exit;
        }
    } else {
        echo "❌ Error: 'migrations/' folder missing!\n";
    }

} catch (PDOException $e) {
    die("❌ [MIGRATION CRASHED]: " . $e->getMessage());
}
?>
