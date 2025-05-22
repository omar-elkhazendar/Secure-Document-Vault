<?php
require_once 'classes/User.php';
require_once 'classes/Session.php';
require_once 'classes/IpHelper.php';
require_once 'classes/MFA.php';

$session = new Session();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = new User();
    $user->username = $_POST['username'];
    $user->email = $_POST['email'];
    $user->password = $_POST['password'];
    $user->auth_method = 'manual';
    $user->status = 'pending'; // Set initial status as pending

    // Validate password
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $user->password)) {
        $error = "Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character";
    } else if ($user->emailExists()) {
        $error = "Email already exists";
    } else if ($user->usernameExists()) {
        $error = "Username already exists";
    } else {
        // Create the user first with pending status
        if ($user->create()) {
            // Get the user ID after creation
            $userId = $user->id;
            
            // Initialize MFA
            $mfa = new MFA();
            $mfaResult = $mfa->createSecret($userId);
            
            if ($mfaResult) {
                // Store MFA data in session for setup
                $_SESSION['mfa_setup_user_id'] = $user->id;
                $_SESSION['mfa_secret'] = $mfaResult['secret'];
                $_SESSION['mfa_backup_codes'] = $mfaResult['backup_codes'];
                
                // Redirect to MFA setup
                header('Location: mfa_setup.php');
                exit();
            } else {
                // If MFA setup fails, delete the pending user
                $user->delete($userId);
                $error = "Failed to setup MFA. Please try again.";
            }
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - SecureAuth</title>
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
                <h2>Create Account</h2>
                <p>Join our secure platform today</p>
            </div>

            <?php if (isset($error) && !empty($error)): ?>
                <div class="alert alert-danger shake" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="social-login">
                <a href="github_auth.php" class="social-btn github-btn" title="Continue with GitHub">
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

            <form method="POST" action="signup.php" class="needs-validation" novalidate>
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user me-2"></i>Username
                    </label>
                    <input type="text" class="form-control" id="username" name="username" required>
                    <div class="invalid-feedback">Please choose a username.</div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-2"></i>Email
                    </label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="invalid-feedback">Please enter a valid email address.</div>
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
                    <div class="password-strength mt-2">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="strength-text">Password strength: <span>None</span></small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Confirm Password
                    </label>
                    <div class="password-field">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback">Passwords do not match.</div>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                    <label class="form-check-label" for="terms">
                        I agree to the <a href="#" class="text-primary">Terms and Conditions</a>
                    </label>
                    <div class="invalid-feedback">You must agree to the terms and conditions.</div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign in</a></p>
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
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Password strength meter
        const passwordInput = document.getElementById('password');
        const progressBar = document.querySelector('.progress-bar');
        const strengthText = document.querySelector('.strength-text span');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            let feedback = '';

            // Length check
            if (password.length >= 8) strength += 25;
            
            // Uppercase check
            if (/[A-Z]/.test(password)) strength += 25;
            
            // Lowercase check
            if (/[a-z]/.test(password)) strength += 25;
            
            // Numbers and special characters check
            if (/[0-9!@#$%^&*]/.test(password)) strength += 25;

            // Update progress bar
            progressBar.style.width = `${strength}%`;
            
            // Update strength text
            if (strength === 0) feedback = 'None';
            else if (strength <= 25) feedback = 'Weak';
            else if (strength <= 50) feedback = 'Fair';
            else if (strength <= 75) feedback = 'Good';
            else feedback = 'Strong';

            strengthText.textContent = feedback;

            // Update progress bar color
            if (strength <= 25) progressBar.className = 'progress-bar bg-danger';
            else if (strength <= 50) progressBar.className = 'progress-bar bg-warning';
            else if (strength <= 75) progressBar.className = 'progress-bar bg-info';
            else progressBar.className = 'progress-bar bg-success';
        });

        // Password confirmation validation
        const confirmPassword = document.getElementById('confirm_password');
        confirmPassword.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        // Form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
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