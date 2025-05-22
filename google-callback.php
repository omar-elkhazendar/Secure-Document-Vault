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

    // Verify state parameter
    if (!isset($_GET['state']) || $_GET['state'] !== $session->get('google_state')) {
        throw new Exception('Invalid state parameter');
    }

    // Exchange authorization code for access token
    if (isset($_GET['code'])) {
        $token_url = 'https://oauth2.googleapis.com/token';
        $data = [
            'code' => $_GET['code'],
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init($token_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_POST, true);
        $response = curl_exec($ch);
        $token_data = json_decode($response, true);
        curl_close($ch);

        if (!isset($token_data['access_token'])) {
            throw new Exception('Failed to get access token');
        }

        // Get user info from Google
        $user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
        $ch = curl_init($user_info_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token_data['access_token']
        ]);
        $response = curl_exec($ch);
        $user_data = json_decode($response, true);
        curl_close($ch);

        if (!isset($user_data['email'])) {
            throw new Exception('Failed to get user data from Google');
        }

        // Create or update user in database
        $user = new User();
        $user_data = [
            'email' => $user_data['email'],
            'name' => $user_data['name'] ?? $user_data['email'],
            'google_id' => $user_data['id'],
            'profile_picture' => $user_data['picture'] ?? null
        ];

        // Check if user exists
        $existing_user = $user->getUserByEmail($user_data['email']);
        
        if ($existing_user) {
            // Update existing user
            $user->updateUser($existing_user['id'], $user_data);
            $user_id = $existing_user['id'];
        } else {
            // Create new user
            $user_id = $user->createUser($user_data);
        }

        // Set session
        $session->set('user_id', $user_id);
        $session->set('user_email', $user_data['email']);
        $session->set('user_name', $user_data['name']);

        // Redirect to dashboard
        header('Location: dashboard.php');
        exit();
    }

    throw new Exception('No authorization code received');

} catch (Exception $e) {
    // Log the error
    error_log("Google Callback Error: " . $e->getMessage());
    
    // Redirect to signup with error message
    header('Location: signup.php?error=' . urlencode($e->getMessage()));
    exit();
}
?> 