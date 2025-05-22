<?php
// GitHub OAuth credentials
define('GITHUB_CLIENT_ID', 'YOUR_GITHUB_CLIENT_ID');
define('GITHUB_CLIENT_SECRET', 'YOUR_GITHUB_CLIENT_SECRET');

// Google OAuth credentials
define('GOOGLE_CLIENT_ID', '1051911702218-bg2r4lcjo8pjndvrqcu89r5d3hfcot8i.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost/Data2/google-callback.php');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'secure_auth');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?> 