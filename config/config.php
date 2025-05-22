<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'auth_system');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// File upload settings
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'txt', 'html', 'htm', 'jpg', 'jpeg', 'png', 'gif']);

// Security settings
define('SESSION_LIFETIME', 24 * 60 * 60); // 24 hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 15 * 60); // 15 minutes
define('HMAC_SECRET_KEY', '5daa0b511b7831b501243bd3b4aa3f6996b88724445c101e3443a444e6766cfc');

// File paths
define('UPLOAD_DIR', 'uploads/documents/');
define('TEMP_DIR', 'uploads/temp/');

// Create required directories if they don't exist
$directories = [UPLOAD_DIR, TEMP_DIR];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}
?> 