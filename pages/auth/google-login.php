<?php
// pages\auth\google-login.php
require_once __DIR__ . '/../../vendor/autoload.php';
$envPath = realpath(__DIR__ . '/../../');
if (file_exists($envPath . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($envPath);
    $dotenv->load();
} else {
    die(".env file not found at: " . $envPath);
}
$client = new Google\Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID'] ?? '');
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI'] ?? '');
$client->addScope('email');
$client->addScope('profile');
header('Location: ' . $client->createAuthUrl());
exit;