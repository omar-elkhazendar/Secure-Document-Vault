<?php
require_once 'classes/Session.php';
require_once 'classes/User.php';
require_once 'classes/IpHelper.php';
require_once 'config.php';

$session = new Session();

// GitHub OAuth configuration
$client_id = "Ov23li2yAWHcJDlT9JBw";
$client_secret = "e59d4cacbce759074f5bd6a8703919a0650ac6c0";

if (isset($_GET['code']) && isset($_GET['state'])) {
    // Verify state
    if ($_GET['state'] !== $session->get('github_state')) {
        die('Invalid state parameter');
    }
    
    // Exchange code for access token
    $params = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $_GET['code']
    ];
    
    $ch = curl_init('https://github.com/login/oauth/access_token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (isset($data['access_token'])) {
        // Get user information
        $ch = curl_init('https://api.github.com/user');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: token ' . $data['access_token'],
            'User-Agent: PHP-App'
        ]);
        
        $user_data = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        // Get user email
        $ch = curl_init('https://api.github.com/user/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: token ' . $data['access_token'],
            'User-Agent: PHP-App'
        ]);
        
        $emails = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        // Find primary email
        $email = '';
        foreach ($emails as $email_data) {
            if ($email_data['primary']) {
                $email = $email_data['email'];
                break;
            }
        }
        
        // Create or update user
        $user = new User();
        $user->username = $user_data['login'];
        $user->email = $email;
        $user->auth_method = 'github';
        
        if ($user->emailExists()) {
            // Update existing user
            $user->updateFromGitHub($user_data['id']);
        } else {
            // Create new user
            $user->createFromGitHub($user_data['id']);
        }
        
        // Log the user in
        $session->set('user_id', $user->id);
        $session->set('username', $user->username);
        $session->set('auth_method', 'github');
        
        // Log the login with real IP
        $ip = IpHelper::getClientIp();
        $user->logLogin($ip);
        
        // Redirect to dashboard
        header('Location: dashboard.php');
        exit();
    } else {
        // Handle error
        $error = isset($data['error_description']) ? $data['error_description'] : 'Unknown error occurred';
        header('Location: signup.php?error=' . urlencode($error));
        exit();
    }
} else {
    // Handle error - no code or state
    header('Location: signup.php?error=' . urlencode('Authentication failed'));
    exit();
}
?> 