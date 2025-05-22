<?php
session_start();
require_once 'config/config.php';

// Google OAuth configuration
$client_id = GOOGLE_CLIENT_ID;
$client_secret = GOOGLE_CLIENT_SECRET;
$redirect_uri = GOOGLE_REDIRECT_URI;

// Generate random state parameter
$state = bin2hex(random_bytes(16));
$_SESSION['google_state'] = $state;

// Build Google OAuth URL
$google_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => 'email profile',
    'state' => $state,
    'access_type' => 'online',
    'prompt' => 'select_account'
]);

// Redirect to Google
header('Location: ' . $google_url);
exit();
?> 