<?php
require_once 'classes/Session.php';
require_once 'classes/MFA.php';
require_once 'classes/User.php';
require_once 'classes/Database.php';
require_once 'classes/Role.php';
require_once 'vendor/GoogleAuthenticator.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$session = new Session();
$error = '';

// Check if user is being redirected here after login (meaning MFA is required)
$userId = $session->get('mfa_user_id');
$username = $session->get('mfa_username');

if (!$userId) {
    // If no mfa_user_id in session, redirect to login page
    header("Location: login.php");
    exit();
}

$user = new User();
$userData = $user->getUserById($userId);

if (!$userData) {
    // If user not found, redirect to login page
    header("Location: login.php");
    exit();
}

$db = new Database();
$ga = new PHPGangsta_GoogleAuthenticator();

// Get user's MFA secret
$mfa_data = $db->query("SELECT secret_key FROM mfa_secrets WHERE user_id = ?", [$userId])->fetch();
$secret = $mfa_data['secret_key'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['mfa_code'];
    
    if ($ga->verifyCode($secret, $code, 2)) {
        // MFA verification successful
        
        // Check user status
        if ($userData['status'] === 'pending') {
            // If status is pending, show message and redirect to login
            $session->set('error_message', 'Your account is pending admin approval. Please wait for approval before logging in.');
            header("Location: login.php");
            exit();
        }
        
        // Create user session
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        
        // Get user role
        $role = new Role($db);
        $userRole = $role->getUserRole($userId);
        
        // Redirect based on role
        if ($userRole['role_name'] === 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid verification code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFA Verification - SecureAuth</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="fade-in">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mt-5">
                    <div class="card-header">
                        <h3 class="text-center">MFA Verification</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <p class="text-center mb-4">
                            Please enter the verification code from your authenticator app.
                        </p>

                        <form method="POST" action="mfa_verify.php">
                            <div class="mb-3">
                                <label for="mfa_code" class="form-label">Verification Code</label>
                                <input type="text" class="form-control" id="mfa_code" name="mfa_code" 
                                       pattern="[0-9]*" inputmode="numeric" maxlength="6" required
                                       autocomplete="off" autofocus>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Verify</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 