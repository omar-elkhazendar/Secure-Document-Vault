<?php
require_once 'classes/Session.php';
require_once 'classes/User.php';
require_once 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$session = new Session();

try {
    // GitHub OAuth configuration
    $client_id ="Ov23li2yAWHcJDlT9JBw";
    $client_secret ="e59d4cacbce759074f5bd6a8703919a0650ac6c0";
    $redirect_uri = "http://localhost/Data2/github-callback.php";

    // If this is the initial request, redirect to GitHub
    if (!isset($_GET['code'])) {
        // Generate and store state parameter
        $state = bin2hex(random_bytes(16));
        $session->set('github_state', $state);
        
        $params = [
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'state' => $state,
            'scope' => 'user:email',
            'prompt' => 'consent'  // Force GitHub to always show the authorization screen
        ];
        
        $auth_url = 'https://github.com/login/oauth/authorize?' . http_build_query($params);
        header('Location: ' . $auth_url);
        exit();
    }

    // Handle errors
    if (isset($_GET['error'])) {
        throw new Exception("GitHub Error: " . $_GET['error_description'] ?? 'Unknown error');
    }

    header('Location: signup.php?error=' . urlencode('Invalid request'));
    exit();

} catch (Exception $e) {
    // Log the error
    error_log("GitHub Auth Error: " . $e->getMessage());
    
    // Redirect to signup with error message
    header('Location: signup.php?error=' . urlencode($e->getMessage()));
    exit();
}
?> 