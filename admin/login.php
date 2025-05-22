<?php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Role.php';

$database = new Database();
$db = $database->getConnection();
$role = new Role($db);

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    $user_role = $role->getUserRole($_SESSION['user_id']);
    if ($user_role && $user_role['role_name'] === 'admin') {
        header('Location: dashboard.php');
        exit();
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        try {
            // Get user with admin role
            $stmt = $db->prepare(
                "SELECT u.* FROM users u 
                 JOIN roles r ON u.role_id = r.role_id 
                 WHERE u.email = ? AND r.role_name = 'admin'"
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Check if user is active
                if ($user['status'] !== 'active') {
                    $error = 'Your account is not active';
                } else {
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    
                    // Log the login
                    $stmt = $db->prepare(
                        "INSERT INTO system_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)"
                    );
                    $stmt->execute([
                        $user['id'],
                        'admin_login',
                        'Admin user logged in',
                        $_SERVER['REMOTE_ADDR']
                    ]);

                    header('Location: dashboard.php');
                    exit();
                }
            } else {
                $error = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'An error occurred during login';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Document Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .admin-login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .admin-login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .admin-login-header i {
            font-size: 3rem;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="admin-login-container">
            <div class="admin-login-header">
                <i class="bi bi-shield-lock"></i>
                <h2>Admin Login</h2>
                <p class="text-muted">Enter your admin credentials to continue</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="../login.php" class="text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Back to User Login
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 