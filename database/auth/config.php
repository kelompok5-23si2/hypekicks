<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'hypekicks';

// Set default timezone for PHP
date_default_timezone_set('Asia/Jakarta'); // GMT+7

// Email configuration
$email_host = 'smtp.gmail.com';
$email_port = 587;
$email_username = 'hypekicks.indonesia@gmail.com';
$email_password = 'pxvz saqu znhs xbsd';
$email_from = 'HypeKicks Team <hypekicks.indonesia@gmail.com>';

// Website configuration
$site_url = 'http://localhost/HypeKicks';
$activation_url = $site_url . '/database/auth/activate.php';

// Security settings
$password_min_length = 8;
$session_lifetime = 3600; // 1 hour
$token_expiry = 3600; // 1 hour
?>
