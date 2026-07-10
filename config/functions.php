<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    
    if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
        ini_set('session.cookie_domain', '');
    }
    
    ini_set('session.gc_maxlifetime', 86400);
    ini_set('session.cookie_lifetime', 86400);
    
    session_start();
}

// 3. एसेट पाथ हेल्पर फंक्शन
if (!function_exists('asset')) {
    function asset($path) {
        return SITE_URL . '/assets/' . ltrim($path, '/');
    }
}

// 4. यूआरएल रीडायरेक्शन और लिंकिंग हेल्पर
if (!function_exists('url')) {
    function url($path) {
        return SITE_URL . '/' . ltrim($path, '/');
    }
}

// 5. प्राइस फ़ॉर्मेटिंग हेल्पर
if (!function_exists('formatPrice')) {
    function formatPrice($amount) {
        return '₹' . number_format($amount, 2);
    }
}
?>