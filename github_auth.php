<?php
session_start();
require_once 'config/config.php';

// GitHub OAuth configuration
$client_id = GITHUB_CLIENT_ID;
$client_secret = GITHUB_CLIENT_SECRET;
$redirect_uri = GITHUB_REDIRECT_URI;

// Generate random state parameter
$state = bin2hex(random_bytes(16));
$_SESSION['github_state'] = $state;

// Build GitHub OAuth URL
$github_url = "https://github.com/login/oauth/authorize?" . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'state' => $state,
    'scope' => 'user:email'
]);

// Redirect to GitHub
header('Location: ' . $github_url);
exit();
?> 