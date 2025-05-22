<?php
require_once 'classes/Session.php';
require_once 'classes/User.php';
require_once 'classes/GithubAuth.php';
require_once 'classes/IpHelper.php';
require_once 'classes/Role.php';
require_once 'classes/Database.php';

$session = new Session();
$error = '';
$success = '';

if (isset($_GET['message']) && $_GET['message'] === 'logged_out') {
    $success = "You have been successfully logged out.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = new User();
    $result = $user->login($_POST['email'], $_POST['password']);
    
    if ($result['success']) {
        // Create session
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        
        // Log successful login
        $user->logLogin();
        
        // Get user role
        $db = new Database();
        $role = new Role($db);
        $userRole = $role->getUserRole($user->id);
        
        // Check if user is pending approval
        if ($userRole['status'] === 'pending') {
            $error = "Your account is pending admin approval. Please wait for approval to access your account.";
        } else {
            // Check if MFA is enabled
            if ($userRole['mfa_enabled']) {
                // Store user ID in session for MFA verification
                $_SESSION['mfa_user_id'] = $user->id;
                header('Location: mfa_verify.php');
                exit();
            } else {
                // Create session and redirect based on role
                if ($session->createSession($user->id)) {
                    if ($userRole['role_name'] === 'admin') {
                        header('Location: admin/dashboard.php');
                    } else {
                        header('Location: dashboard.php');
                    }
                    exit();
                } else {
                    $error = "Failed to create session. Please try again.";
                }
            }
        }
    } else {
        // Display the error message from the login result
        $error = $result['message'];
    }
}

$githubAuth = new GithubAuth();
$githubUrl = $githubAuth->getAuthUrl();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SecureAuth</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body class="fade-in">
    <!-- Floating Particles -->
    <div class="particles" id="particles"></div>

    <div class="container">
        <div class="auth-container slide-in">
            <div class="logo-container">
                <img src="images/logo.svg" alt="Document Management System Logo" class="logo">
            </div>

            <div class="auth-header">
                <h2>Welcome Back</h2>
                <p>Please sign in to continue</p>
            </div>

            <?php if (isset($error) && !empty($error)): ?>
                <div class="alert alert-danger shake" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div class="social-login">
                <a href="<?php echo $githubUrl; ?>" class="social-btn github-btn" title="Continue with GitHub">
                    <i class="fab fa-github"></i>
                </a>
                <a href="google_auth.php" class="social-btn google-btn" title="Continue with Google">
                    <i class="fab fa-google"></i>
                </a>
                <a href="okta.php" class="social-btn okta-btn" title="Continue with Okta">
                    <i class="fab fa-okta"></i>
                </a>
            </div>

            <div class="divider">
                <span>OR</span>
            </div>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-2"></i>Email
                    </label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Password
                    </label>
                    <div class="password-field">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="signup.php">Sign up</a></p>
                <a href="#" class="text-muted">Forgot password?</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Create floating particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                // Random size between 5 and 20 pixels
                const size = Math.random() * 15 + 5;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                
                // Random position
                particle.style.left = `${Math.random() * 100}%`;
                particle.style.top = `${Math.random() * 100}%`;
                
                // Random animation duration between 15 and 30 seconds
                particle.style.animationDuration = `${Math.random() * 15 + 15}s`;
                
                // Random delay
                particle.style.animationDelay = `${Math.random() * 5}s`;
                
                particlesContainer.appendChild(particle);
            }
        }

        // Initialize particles
        createParticles();

        // Password visibility toggle
        const passwordField = document.getElementById('password');
        const toggleButton = document.querySelector('.password-toggle');
        const toggleIcon = toggleButton.querySelector('i');

        toggleButton.addEventListener('click', function() {
            const type = passwordField.getAttribute('type');
            if (type === 'password') {
                passwordField.setAttribute('type', 'text');
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.setAttribute('type', 'password');
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });

        // Form validation with animations
        const emailField = document.getElementById('email');
        const form = document.querySelector('form');

        emailField.addEventListener('input', function(e) {
            const email = e.target.value;
            emailField.classList.remove('is-valid', 'is-invalid');
            
            if (email.length > 0) {
                if (email.includes('@') && email.includes('.')) {
                    emailField.classList.add('is-valid');
                } else {
                    emailField.classList.add('is-invalid');
                }
            }
        });

        passwordField.addEventListener('input', function(e) {
            const password = e.target.value;
            passwordField.classList.remove('is-valid', 'is-invalid');
            
            if (password.length > 0) {
                if (password.length >= 8) {
                    passwordField.classList.add('is-valid');
                } else {
                    passwordField.classList.add('is-invalid');
                }
            }
        });

        // Add hover effect to social buttons
        document.querySelectorAll('.social-btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px) scale(1.1)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Add shake animation to error messages
        document.querySelectorAll('.alert-danger').forEach(alert => {
            alert.classList.add('shake');
        });
    </script>
</body>
</html> 