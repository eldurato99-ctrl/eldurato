<?php
// config/database.php
$host = "localhost";
$dbname = "belt";
$user = "root";
$pass = "";

try {
    // 1. Database se connect karne ki koshish karein
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 2. Ek quick check karein ki kya 'users' table exist karta hai?
    // Agar database hai par tables nahi bane, to yeh catch block mein bhej dega
    $pdo->query("SELECT 1 FROM `users` LIMIT 1");

} catch(PDOException $e){
    
    // Agar Database missing ho (1049) YA Table missing ho (42S02 / 1146)
    if ($e->getCode() == 1049 || $e->getCode() == '42S02' || strpos($e->getMessage(), "not found") !== false) {
        
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $currentUrl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
        // Setup runner par bhejein aur wapas aane ka raasta (return URL) set karein
        header("Location: " . $protocol . $_SERVER['HTTP_HOST'] . "/belt/setup.php?return=" . urlencode($currentUrl));
        exit;
    } else {
        die("Database Connection Error : " . $e->getMessage());
    }
}