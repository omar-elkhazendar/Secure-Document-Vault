<?php
require_once 'classes/Session.php';
require_once 'classes/User.php';
require_once 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$session = new Session();

try {
    // Google OAuth configuration
    $client_id = "1051911702218-i5qspim20t473e6r52vk1a0e30eq4pol.apps.googleusercontent.com";
    $client_secret = "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";
    $redirect_uri = "http://localhost/Data2/google-callback.php";

    // If this is the initial request, redirect to Google
    if (!isset($_GET['code'])) {
        // Generate and store state parameter
        $state = bin2hex(random_bytes(16));
        $session->set('google_state', $state);
        
        $params = [
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];
        
        $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        header('Location: ' . $auth_url);
        exit();
    }

    // Handle errors
    if (isset($_GET['error'])) {
        throw new Exception("Google Error: " . $_GET['error_description'] ?? 'Unknown error');
    }

    header('Location: signup.php?error=' . urlencode('Invalid request'));
    exit();

} catch (Exception $e) {
    // Log the error
    error_log("Google Auth Error: " . $e->getMessage());
    
    // Redirect to signup with error message
    header('Location: signup.php?error=' . urlencode($e->getMessage()));
    exit();
}
?> 