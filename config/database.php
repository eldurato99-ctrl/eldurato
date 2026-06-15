<?php
// config/database.php

$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'belt';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // users table check
    $pdo->query("SELECT 1 FROM `users` LIMIT 1");

} catch(PDOException $e){

    // Database missing or table missing
    if (
        $e->getCode() == 1049 ||
        $e->getCode() == '42S02' ||
        strpos($e->getMessage(), "not found") !== false
    ) {

        $protocol = (!empty($_SERVER['HTTPS']) &&
                    $_SERVER['HTTPS'] !== 'off')
                    ? "https://"
                    : "http://";

        $currentUrl = $protocol .
                      $_SERVER['HTTP_HOST'] .
                      $_SERVER['REQUEST_URI'];

        header(
            "Location: " .
            $protocol .
            $_SERVER['HTTP_HOST'] .
            "/setup.php?return=" .
            urlencode($currentUrl)
        );

        exit;

    } else {
        die("Database Connection Error: " . $e->getMessage());
    }
}
