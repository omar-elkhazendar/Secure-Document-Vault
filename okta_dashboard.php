<?php

// Include necessary files
require_once 'classes/Session.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'config.php';

$session = new Session();
$database = new Database();
$db = $database->getConnection();
$user = new User();

// Check if the user is logged in
if (!$session->isLoggedIn()) {
    // If not logged in, redirect to the login page or Okta initiation page
    header('Location: okta.php'); // Assuming okta.php handles the initial redirect to Auth0
    exit;
}

// Get user ID from session
$user_id = $session->get('user_id');

// Fetch user data from the database
$user_data = $user->getUserById($user_id);

// Check if user data was found
if (!$user_data) {
    // If user data not found (shouldn't happen if session exists), destroy session and redirect to login
    $session->destroy();
    header('Location: login.php');
    exit;
}

// User data is now in $user_data array
// You can display user information here

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Okta User Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($user_data['username']); ?>!</h1>
    <p>This is your personalized dashboard for Okta-signed users.</p>
    
    <h2>Your Information:</h2>
    <ul>
        <li>User ID: <?php echo htmlspecialchars($user_data['id']); ?></li>
        <li>Username: <?php echo htmlspecialchars($user_data['username']); ?></li>
        <li>Email: <?php echo htmlspecialchars($user_data['email']); ?></li>
        <li>Auth Method: <?php echo htmlspecialchars($user_data['auth_method']); ?></li>
        <!-- Add other user data fields you want to display -->
    </ul>

    <!-- You can add more content here, potentially displaying data related to the user from your database -->
    
    <p><a href="okta.php?action=logout">Logout</a></p> <!-- Assuming logout is handled by okta.php -->

</body>
</html> 