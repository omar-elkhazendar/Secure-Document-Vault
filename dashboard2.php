<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: okta.php'); // redirect to login if not logged in
    exit;
}

$user = $_SESSION['user'];

// Connect to database to get login history
$mysqli = new mysqli('localhost', 'root', '', 'auth_system');
if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// Create login_history table if it doesn't exist
$createTableQuery = "CREATE TABLE IF NOT EXISTS login_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    login_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    auth_method VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45),
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if (!$mysqli->query($createTableQuery)) {
    die("Error creating table: " . $mysqli->error);
}

// Insert current login into history
$userId = $user['id'];
$authMethod = $user['auth_method'];
$ipAddress = $_SERVER['REMOTE_ADDR'];

$insertQuery = "INSERT INTO login_history (user_id, auth_method, ip_address) 
                VALUES (?, ?, ?)";
$stmt = $mysqli->prepare($insertQuery);
$stmt->bind_param("iss", $userId, $authMethod, $ipAddress);
$stmt->execute();

// Get login history
$query = "SELECT * FROM login_history WHERE user_id = $userId ORDER BY login_time DESC LIMIT 5";
$loginHistory = $mysqli->query($query);

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Dashboard - Welcome <?= htmlspecialchars($user['name']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
  /* Reset & base */
  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: url('https://images.unsplash.com/photo-1557683316-973673baf926?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80') no-repeat center center fixed;
    background-size: cover;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #333;
    padding: 20px;
    position: relative;
  }

  body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.9), rgba(188, 209, 245, 0.9));
    backdrop-filter: blur(5px);
    z-index: 0;
  }

  .dashboard-container {
    position: relative;
    z-index: 1;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(15px);
    border-radius: 30px;
    padding: 40px;
    width: 100%;
    max-width: 800px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    animation: fadeInScale 0.8s ease forwards;
    border: 1px solid rgba(255, 255, 255, 0.2);
  }

  .welcome-section {
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 2px solid rgba(106, 17, 203, 0.1);
    position: relative;
  }

  .welcome-section::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 2px;
    background: linear-gradient(90deg, #6a11cb, #2575fc);
  }

  .welcome-section h1 {
    color: #2c3e50;
    font-size: 2.8rem;
    font-weight: 800;
    margin-bottom: 15px;
    animation: slideDown 0.8s ease forwards;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
  }

  .user-info {
    background: rgba(248, 249, 250, 0.9);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    animation: slideUp 0.8s ease forwards;
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
  }

  .info-item {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px;
    border-radius: 15px;
    background: rgba(255, 255, 255, 0.5);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .info-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  }

  .info-item:last-child {
    margin-bottom: 0;
  }

  .info-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    color: white;
    font-size: 1.2rem;
    box-shadow: 0 5px 15px rgba(106, 17, 203, 0.3);
    transition: transform 0.3s ease;
  }

  .info-item:hover .info-icon {
    transform: scale(1.1) rotate(5deg);
  }

  .info-content {
    flex: 1;
  }

  .info-label {
    font-weight: 700;
    color: #6a11cb;
    margin-bottom: 8px;
    font-size: 1.1rem;
    letter-spacing: 0.5px;
  }

  .info-value {
    color: #2c3e50;
    font-size: 1.2rem;
    font-weight: 500;
  }

  .auth-badge {
    display: inline-flex;
    align-items: center;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    color: white;
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    box-shadow: 0 4px 15px rgba(106, 17, 203, 0.2);
  }

  .auth-badge i {
    margin-right: 8px;
  }

  .activity-section {
    background: rgba(248, 249, 250, 0.9);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    animation: slideUp 0.8s ease forwards;
    border: 1px solid rgba(255, 255, 255, 0.2);
  }

  .activity-section h3 {
    color: #2c3e50;
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid rgba(106, 17, 203, 0.1);
  }

  .activity-item {
    display: flex;
    align-items: center;
    padding: 15px;
    margin-bottom: 10px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 15px;
    transition: transform 0.3s ease;
  }

  .activity-item:hover {
    transform: translateX(5px);
  }

  .activity-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: white;
  }

  .activity-details {
    flex: 1;
  }

  .activity-time {
    font-size: 0.9rem;
    color: #6c757d;
  }

  .activity-method {
    font-weight: 600;
    color: #2c3e50;
  }

  .action-buttons {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 30px;
  }

  .btn-custom {
    padding: 15px 35px;
    border-radius: 15px;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .btn-primary {
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    border: none;
  }

  .btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(106, 17, 203, 0.4);
  }

  .btn-danger {
    background: linear-gradient(135deg, #ff416c, #ff4b2b);
    border: none;
  }

  .btn-danger:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(255, 65, 108, 0.4);
  }

  /* Animations */
  @keyframes fadeInScale {
    0% {
      opacity: 0;
      transform: scale(0.9) translateY(20px);
    }
    100% {
      opacity: 1;
      transform: scale(1) translateY(0);
    }
  }

  @keyframes slideDown {
    0% {
      opacity: 0;
      transform: translateY(-30px);
    }
    100% {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes slideUp {
    0% {
      opacity: 0;
      transform: translateY(30px);
    }
    100% {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @media (max-width: 768px) {
    .dashboard-container {
      padding: 25px;
      margin: 15px;
    }
    
    .welcome-section h1 {
      font-size: 2.2rem;
    }
    
    .action-buttons {
      flex-direction: column;
    }
    
    .btn-custom {
      width: 100%;
      justify-content: center;
      padding: 12px 25px;
    }

    .info-item {
      padding: 12px;
    }

    .info-icon {
      width: 40px;
      height: 40px;
      font-size: 1rem;
    }
  }
</style>
</head>
<body>

<div class="dashboard-container">
  <div class="welcome-section">
    <h1>Welcome, <?= htmlspecialchars($user['name']) ?>!</h1>
  </div>

  <div class="user-info">
    <div class="info-item">
      <div class="info-icon">
        <i class="fas fa-envelope"></i>
      </div>
      <div class="info-content">
        <div class="info-label">Email Address</div>
        <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
      </div>
    </div>

    <div class="info-item">
      <div class="info-icon">
        <i class="fas fa-shield-alt"></i>
      </div>
      <div class="info-content">
        <div class="info-label">Authentication Method</div>
        <div class="info-value">
          <span class="auth-badge">
            <i class="fas <?= $user['auth_method'] === 'okta' ? 'fa-user-shield' : 'fa-user' ?>"></i>
            <?= ucfirst($user['auth_method']) ?> Authentication
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="activity-section">
    <h3><i class="fas fa-history me-2"></i>Recent Login Activity</h3>
    <?php if ($loginHistory && $loginHistory->num_rows > 0): ?>
      <?php while ($login = $loginHistory->fetch_assoc()): ?>
        <div class="activity-item">
          <div class="activity-icon">
            <i class="fas fa-sign-in-alt"></i>
          </div>
          <div class="activity-details">
            <div class="activity-time">
              <?= date('F j, Y g:i A', strtotime($login['login_time'])) ?>
            </div>
            <div class="activity-method">
              <?= ucfirst($login['auth_method'] ?? 'manual') ?> Login
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="activity-item">
        <div class="activity-icon">
          <i class="fas fa-info-circle"></i>
        </div>
        <div class="activity-details">
          <div class="activity-method">No login history available</div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div class="action-buttons">
    <form method="GET" action="okta.php" style="margin: 0;">
      <button type="submit" name="action" value="logout" class="btn btn-danger btn-custom">
        <i class="fas fa-sign-out-alt"></i>
        Logout
      </button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
