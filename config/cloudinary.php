<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Cloudinary\Cloudinary;
use Dotenv\Dotenv;

try {
    // Ye check karega ki root me .env file hai ya nahi, aur use load karega
    if (file_exists(__DIR__ . '/../.env')) {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
    }
} catch (Exception $e) {
    // Agar environment variables manually set hain toh fallback
}

$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'] ?? getenv('CLOUDINARY_CLOUD_NAME'),
        'api_key'    => $_ENV['CLOUDINARY_API_KEY'] ?? getenv('CLOUDINARY_API_KEY'),
        'api_secret' => $_ENV['CLOUDINARY_API_SECRET'] ?? getenv('CLOUDINARY_API_SECRET'),
    ],
    'url' => [
        'secure' => true
    ]
]);