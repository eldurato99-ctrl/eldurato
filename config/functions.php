<?php
// 1. सबसे पहले ग्लोबल कॉन्फ़िगरेशन फ़ाइल को लोड करें
require_once __DIR__ . '/config.php';

// 2. लोकलहोस्ट और लाइव सर्वर दोनों के लिए सेशन को सिंक करें
if (session_status() === PHP_SESSION_NONE) {
    // कुकी पाथ को रूट पर सेट करें
    ini_set('session.cookie_path', '/');
    
    // अगर आप लोकलहोस्ट पर काम कर रहे हैं तो डोमेन को सख्त न करें
    if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
        ini_set('session.cookie_domain', '');
    }
    
    // सेशन लाइफ को बढ़ाएं (24 घंटे के लिए)
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