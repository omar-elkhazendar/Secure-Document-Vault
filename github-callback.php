<?php
require_once 'classes/Session.php';
require_once 'classes/GithubAuth.php';
require_once 'classes/Database.php';
require_once 'classes/Role.php';
require_once 'classes/User.php';

$session = new Session();

if (isset($_GET['code'])) {
    $githubAuth = new GithubAuth();
    $user = $githubAuth->handleCallback($_GET['code']);
    
    if ($user) {
        // Get database connection and Role object
        $database = new Database();
        $db = $database->getConnection();
        $role = new Role($db);

        // Assign 'user' role to the user
        $user_role_id = $role->getRoleIdByName('user');
        if ($user_role_id) {
            // Assuming assignRoleToUser handles assigning the role correctly
            $role->assignRoleToUser($user->id, $user_role_id);
        }

        // Create session in database
        if ($session->createSession($user->id)) {
            $session->set('username', $user->username);
            $session->set('auth_method', 'github');  // Set the auth method
            // Set the role ID in session as well for easier access
            $session->set('role_id', $user_role_id); 
            
            // Get the real IP address
            $ip_address = '';
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip_address = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip_address = $_SERVER['REMOTE_ADDR'];
            }
            
            // Log the login
            if ($user->logLogin($ip_address, true)) {
                header("Location: dashboard.php");
                exit();
            } else {
                error_log("Failed to log login for user ID: " . $user->id);
                header("Location: login.php?error=login_log_failed");
                exit();
            }
        } else {
            error_log("Failed to create session for user ID: " . $user->id);
            header("Location: login.php?error=session_creation_failed");
            exit();
        }
    } else {
        error_log("GitHub authentication failed - No user data returned");
        header("Location: login.php?error=github_auth_failed");
        exit();
    }
} else if (isset($_GET['error'])) {
    error_log("GitHub OAuth Error: " . $_GET['error']);
    if (isset($_GET['error_description'])) {
        error_log("Error Description: " . $_GET['error_description']);
    }
    header("Location: login.php?error=github_auth_error");
    exit();
}

// If we get here, something went wrong
error_log("GitHub callback reached without code or error parameter");
header("Location: login.php?error=github_auth_failed");
exit();
?> 