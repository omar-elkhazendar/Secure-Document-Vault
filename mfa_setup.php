<?php
session_start();
require_once 'classes/Database.php';
require_once 'vendor/GoogleAuthenticator.php';

// Only allow access if coming from signup
if (!isset($_SESSION['mfa_setup_user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$ga = new PHPGangsta_GoogleAuthenticator();

// Get user data from signup
$user_id = $_SESSION['mfa_setup_user_id'];
$user = $db->query("SELECT * FROM users WHERE id = ?", [$user_id])->fetch();

// Get MFA secret from mfa_secrets table
$mfa_data = $db->query("SELECT secret_key FROM mfa_secrets WHERE user_id = ?", [$user_id])->fetch();
$secret = $mfa_data['secret_key'];

// Handle MFA verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_mfa'])) {
    $code = $_POST['mfa_code'];
    
    if ($ga->verifyCode($secret, $code, 2)) { // 2 is the time window in seconds
        // Update user status to pending and enable MFA
        $db->query("UPDATE users SET mfa_enabled = 1, status = 'pending' WHERE id = ?", [$user_id]);
        
        // Clear temporary MFA setup session data
        unset($_SESSION['mfa_secret']);
        unset($_SESSION['mfa_backup_codes']);
        unset($_SESSION['mfa_setup_user_id']);
        
        // Set success message for login page
        $_SESSION['success'] = 'MFA has been successfully enabled! Please wait for admin approval to access your account.';
        header('Location: login.php');
        exit();
    } else {
        $error = 'Invalid verification code. Please try again.';
    }
}

// Generate QR code URL
$qrCodeUrl = $ga->getQRCodeGoogleUrl($user['email'], $secret, 'Document Management System');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Two-Factor Authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .mfa-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }
        .mfa-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .qr-code {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin: 20px 0;
            display: inline-block;
        }
        .step {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #0d6efd;
        }
        .step-number {
            background: #0d6efd;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px;
            font-size: 1.1em;
            text-align: center;
            letter-spacing: 4px;
        }
        .btn-primary {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 500;
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
        .alert i {
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="container mfa-container">
        <div class="mfa-card">
            <h2 class="text-center mb-4">
                <i class="bi bi-shield-lock me-2"></i>
                Setup Two-Factor Authentication
            </h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="step">
                <div class="step-number">1</div>
                <strong>Download Google Authenticator</strong>
                <p class="mb-0">Install Google Authenticator on your mobile device from the App Store or Google Play Store.</p>
            </div>

            <div class="step">
                <div class="step-number">2</div>
                <strong>Scan QR Code</strong>
                <p class="mb-0">Open Google Authenticator and scan this QR code:</p>
                <div class="text-center qr-code">
                    <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code" class="img-fluid">
                </div>
            </div>

            <div class="step">
                <div class="step-number">3</div>
                <strong>Enter Verification Code</strong>
                <p class="mb-0">Enter the 6-digit code from Google Authenticator to verify setup:</p>
                <form method="POST" class="mt-3">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" 
                               name="mfa_code" pattern="[0-9]{6}" maxlength="6" 
                               placeholder="Enter 6-digit code" required>
                        <button type="submit" name="verify_mfa" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Verify
                        </button>
                    </div>
                </form>
            </div>

            <div class="alert alert-info mt-4">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Note:</strong> Keep your backup codes in a safe place. You'll need them if you lose access to your authenticator app.
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-format the MFA code input
        document.querySelector('input[name="mfa_code"]').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
        });
    </script>
</body>
</html> 