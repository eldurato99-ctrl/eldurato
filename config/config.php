<?php
// config/config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost/belt'); 
define('SITE_NAME', getenv('SITE_NAME') ?: 'ELDURATO');
define('ASSETS_URL', getenv('ASSETS_URL') ?: 'http://localhost/belt/assets');