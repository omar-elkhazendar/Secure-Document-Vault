<?php
// Enable strict error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start secure session
session_start([
    'cookie_secure' => false, // Disable in localhost, enable in production
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true
]);
session_regenerate_id(true);

// Clear opcache if enabled
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// Load dependencies
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Role.php';
require_once __DIR__ . '/classes/Session.php';
require_once __DIR__ . '/config.php';

// Auth0 Configuration (replace with your actual values)
$clientId = 'G6mmeV2y8OvvqCl9kNdjATkPI0oZAmQT';
$clientSecret = 'IhTMZzuNsyE_cnzdiTTL3FZ-0eXCMU9fIXvMZWCBeaR3SUucs0ia0kd-bqyLz7V_';
$redirectUri = 'http://localhost/Data2/okta.php';
$auth0Domain = 'https://dev-ez350ewpzi47i0zf.us.auth0.com';

// Initialize services
$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$role = new Role($db);
$session = new Session();

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $session->destroy();
    header('Location: index.php');
    exit;
}

// Redirect if already logged in
if ($session->isLoggedIn()) {
    $userRole = $role->getUserRole($session->get('user_id'));
    $redirect = ($userRole['role_name'] === 'admin') 
        ? 'admin/dashboard.php' 
        : 'dashboard.php';
    header("Location: $redirect");
    exit;
}

// Handle Auth0 callback
if (isset($_GET['code'])) {
    // Validate state parameter
    if (!isset($_SESSION['okta_state']) || empty($_GET['state']) || $_GET['state'] !== $_SESSION['okta_state']) {
        error_log("State mismatch: Session=" . ($_SESSION['okta_state'] ?? 'null') . " vs GET=" . ($_GET['state'] ?? 'null'));
        header('Location: login.php?error=invalid_state');
        exit;
    }
    unset($_SESSION['okta_state']);

    // Exchange authorization code for tokens
    $tokenUrl = $auth0Domain . '/oauth/token';
    $tokenData = [
        'grant_type' => 'authorization_code',
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'code' => $_GET['code'],
        'redirect_uri' => $redirectUri
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($tokenData),
            'ignore_errors' => true
        ]
    ];

    $context = stream_context_create($options);
    $response = @file_get_contents($tokenUrl, false, $context);

    if ($response === false) {
        $error = error_get_last();
        error_log("Token exchange failed: " . print_r($error, true));
        header('Location: login.php?error=token_exchange_failed');
        exit;
    }

    $tokenData = json_decode($response, true);
    if (!isset($tokenData['access_token'])) {
        error_log("Invalid token response: " . $response);
        header('Location: login.php?error=invalid_token_response');
        exit;
    }

    // Get user info
    $userInfoUrl = $auth0Domain . '/userinfo';
    $options = [
        'http' => [
            'header' => "Authorization: Bearer " . $tokenData['access_token'] . "\r\n",
            'ignore_errors' => true
        ]
    ];
    $context = stream_context_create($options);
    $userInfoResponse = @file_get_contents($userInfoUrl, false, $context);

    if ($userInfoResponse === false) {
        error_log("Userinfo request failed");
        header('Location: login.php?error=userinfo_failed');
        exit;
    }

    $userInfo = json_decode($userInfoResponse, true);
    if (!isset($userInfo['email'])) {
        error_log("No email in userinfo: " . print_r($userInfo, true));
        header('Location: login.php?error=no_email');
        exit;
    }

    // Prepare user data for User class
    $user_data = [
        'username' => $userInfo['name'] ?? $userInfo['email'],
        'email' => $userInfo['email'],
        'okta_id' => $userInfo['sub'] ?? null, // 'sub' is standard for subject/user ID in OIDC
        'auth_method' => 'okta', // Explicitly set auth method for new Okta users
        // Add other fields if available and needed, like profile picture, etc.
    ];

    // Find or create user
    $existingUser = $user->getUserByEmail($user_data['email']);
    $userId = null;

    if ($existingUser) {
        // Update existing user
        $userId = $existingUser['id'];
        $user->username = $user_data['username'];
        $user->email = $user_data['email'];
        if (!$user->updateFromOkta($user_data['okta_id'])) {
            error_log("Failed to update Okta user: " . $user_data['email']);
            header('Location: login.php?error=user_update_failed');
            exit;
        }
    } else {
        // Create new user
        $user->username = $user_data['username'];
        $user->email = $user_data['email'];
        if (!$user->createFromOkta($user_data['okta_id'])) {
            error_log("Failed to create Okta user: " . $user_data['email']);
            header('Location: login.php?error=user_creation_failed');
            exit;
        }
        $userId = $user->id;

        // Assign default role
        $defaultRoleId = $role->getRoleIdByName('user');
        if ($defaultRoleId && !$role->assignRoleToUser($userId, $defaultRoleId)) {
            error_log("Failed to assign default role to user: " . $userId);
        }
    }

    // Create session
    if ($userId && $session->createSession($userId)) {
        $session->set('user_id', $userId);
        $session->set('email', $user_data['email']);
        $session->set('username', $user_data['username']);
        $session->set('auth_method', $user_data['auth_method']);
        
        // Log the login
        $user->id = $userId;
        $user->logLogin(null, true);
        
        // Redirect to dashboard
        header('Location: dashboard.php');
        exit;
    } else {
        error_log("Session creation failed for user: " . $userId);
        header('Location: login.php?error=session_failed');
        exit;
    }
}

// Initiate Auth0 login
$_SESSION['okta_state'] = bin2hex(random_bytes(32));
$authorizeUrl = $auth0Domain . '/authorize?' . http_build_query([
    'client_id' => $clientId,
    'response_type' => 'code',
    'scope' => 'openid profile email',
    'redirect_uri' => $redirectUri,
    'state' => $_SESSION['okta_state'],
    'audience' => $auth0Domain . '/api/v2/',
    'prompt' => 'login'
]);

header('Location: ' . $authorizeUrl);
exit;
?>