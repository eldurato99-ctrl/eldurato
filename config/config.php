<?php
// config/config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


define('SITE_NAME', getenv('SITE_NAME') ?: 'ELDURATO');
define(
    'SITE_URL',
    rtrim(getenv('SITE_URL') ?: 'https://eldurato.com/belt', '/')
);

define(
    'ASSETS_URL',
    rtrim(getenv('ASSETS_URL') ?: SITE_URL . '/assets', '/')
);




