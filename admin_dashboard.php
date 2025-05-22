<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Document.php';
require_once 'classes/Role.php';
require_once 'classes/User.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$user = new User();
$role = new Role($db);
$document = new Document($db);

// Get current user's role
$user_role = $role->getUserRole($_SESSION['user_id']);
if (!$user_role || $user_role['role_name'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_user':
                try {
                    // Validate required fields
                    $username = trim($_POST['username'] ?? '');
                    $email = trim($_POST['email'] ?? '');
                    $password = $_POST['password'] ?? '';
                    $role_id = $_POST['role_id'] ?? '';

                    if (empty($username) || empty($email) || empty($password) || empty($role_id)) {
                        throw new Exception('All fields are required');
                    }

                    // Validate email format
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception('Invalid email format');
                    }

                    // Check if email already exists
                    $existing_user = $db->query(
                        "SELECT id FROM users WHERE email = :email",
                        ['email' => $email]
                    )->fetch();

                    if ($existing_user) {
                        throw new Exception('Email already exists');
                    }

                    // Create user data array
                    $user_data = [
                        'username' => $username,
                        'email' => $email,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'role_id' => $role_id,
                        'created_at' => date('Y-m-d H:i:s')
                    ];

                    // Insert user
                    $db->query(
                        "INSERT INTO users (username, email, password, role_id, created_at) 
                         VALUES (:username, :email, :password, :role_id, :created_at)",
                        $user_data
                    );

                    // Log the action
                    $db->query(
                        "INSERT INTO system_logs (user_id, action, details, ip_address) 
                         VALUES (:user_id, :action, :details, :ip_address)",
                        [
                            'user_id' => $_SESSION['user_id'],
                            'action' => 'create_user',
                            'details' => "Created new user: $username",
                            'ip_address' => $_SERVER['REMOTE_ADDR']
                        ]
                    );

                    $_SESSION['success'] = 'User added successfully';
                } catch (Exception $e) {
                    $_SESSION['error'] = $e->getMessage();
                }
                break;

            case 'edit_user':
                try {
                    $user_id = $_POST['user_id'];
                    $username = trim($_POST['username']);
                    $email = trim($_POST['email']);
                    $role_id = $_POST['role_id'];

                    if (empty($username) || empty($email) || empty($role_id)) {
                        throw new Exception('All fields are required');
                    }

                    // Validate email format
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception('Invalid email format');
                    }

                    // Check if email exists for other users
                    $existing_user = $db->query(
                        "SELECT id FROM users WHERE email = :email AND id != :user_id",
                        ['email' => $email, 'user_id' => $user_id]
                    )->fetch();

                    if ($existing_user) {
                        throw new Exception('Email already exists');
                    }

                    // Start transaction
                    $db->beginTransaction();

                    try {
                        // Update user
                        $db->query(
                            "UPDATE users SET username = :username, email = :email, role_id = :role_id 
                             WHERE id = :user_id",
                            [
                                'username' => $username,
                                'email' => $email,
                                'role_id' => $role_id,
                                'user_id' => $user_id
                            ]
                        );

                        // Log the action
                        $db->query(
                            "INSERT INTO system_logs (user_id, action, details, ip_address) 
                             VALUES (:user_id, :action, :details, :ip_address)",
                            [
                                'user_id' => $_SESSION['user_id'],
                                'action' => 'edit_user',
                                'details' => "Updated user: $username",
                                'ip_address' => $_SERVER['REMOTE_ADDR']
                            ]
                        );

                        // Commit transaction
                        $db->commit();
                        $_SESSION['success'] = 'User updated successfully';
                    } catch (Exception $e) {
                        // Rollback transaction on error
                        $db->rollBack();
                        throw $e;
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = $e->getMessage();
                }
                break;

            case 'delete_user':
                try {
                    $user_id = $_POST['user_id'];
                    
                    // Check if user exists
                    $user = $db->query(
                        "SELECT username FROM users WHERE id = :user_id",
                        ['user_id' => $user_id]
                    )->fetch();

                    if (!$user) {
                        throw new Exception('User not found');
                    }

                    // Start transaction
                    $db->beginTransaction();

                    try {
                        // Delete user's documents first
                        $db->query(
                            "DELETE FROM documents WHERE uploaded_by = :user_id",
                            ['user_id' => $user_id]
                        );

                        // Delete user's logs
                        $db->query(
                            "DELETE FROM system_logs WHERE user_id = :user_id",
                            ['user_id' => $user_id]
                        );

                        // Delete user
                        $db->query(
                            "DELETE FROM users WHERE id = :user_id",
                            ['user_id' => $user_id]
                        );

                        // Log the action
                        $db->query(
                            "INSERT INTO system_logs (user_id, action, details, ip_address) 
                             VALUES (:user_id, :action, :details, :ip_address)",
                            [
                                'user_id' => $_SESSION['user_id'],
                                'action' => 'delete_user',
                                'details' => "Deleted user: {$user['username']}",
                                'ip_address' => $_SERVER['REMOTE_ADDR']
                            ]
                        );

                        // Commit transaction
                        $db->commit();
                        $_SESSION['success'] = 'User deleted successfully';
                        
                        // Redirect back to admin dashboard
                        header('Location: admin_dashboard.php');
                        exit();
                    } catch (Exception $e) {
                        // Rollback transaction on error
                        $db->rollBack();
                        throw $e;
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = $e->getMessage();
                    // Redirect back to admin dashboard even on error
                    header('Location: admin_dashboard.php');
                    exit();
                }
                break;

            case 'add_role':
                try {
                    $role->createRole($_POST['role_name']);
                    $_SESSION['success'] = 'Role added successfully';
                } catch (Exception $e) {
                    $_SESSION['error'] = $e->getMessage();
                }
                break;

            case 'delete_role':
                try {
                    $role->deleteRole($_POST['role_id']);
                    $_SESSION['success'] = 'Role deleted successfully';
                } catch (Exception $e) {
                    $_SESSION['error'] = $e->getMessage();
                }
                break;

            case 'toggle_user_status':
                try {
                    $user_id = $_POST['user_id'];
                    $new_status = $_POST['new_status'];
                    $db->query(
                        "UPDATE users SET status = :status WHERE id = :user_id",
                        ['status' => $new_status, 'user_id' => $user_id]
                    );
                    $_SESSION['success'] = 'User status updated successfully';
                } catch (Exception $e) {
                    $_SESSION['error'] = $e->getMessage();
                }
                break;
        }
    }
}

// Get all users with additional information
$users = $db->query(
    "SELECT u.*, r.role_name, 
            (SELECT COUNT(*) FROM documents WHERE uploaded_by = u.id) as document_count,
            (SELECT MAX(created_at) FROM system_logs WHERE user_id = u.id) as last_activity
     FROM users u 
     LEFT JOIN roles r ON u.role_id = r.role_id 
     ORDER BY u.created_at DESC"
)->fetchAll();

// Get all roles
$roles = $role->getAllRoles();

// Get system logs
$logs = $db->query(
    "SELECT l.*, u.username 
     FROM system_logs l 
     LEFT JOIN users u ON l.user_id = u.id 
     ORDER BY l.created_at DESC 
     LIMIT 100"
)->fetchAll();

// Get all documents
$documents = $db->query(
    "SELECT d.*, u.username as uploaded_by_name 
     FROM documents d 
     LEFT JOIN users u ON d.uploaded_by = u.id 
     ORDER BY d.created_at DESC"
)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Document Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --sidebar-bg: #4e73df;
            --sidebar-hover: #2e59d9;
            --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        /* Sidebar Styles */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--sidebar-bg) 10%, #224abe 100%);
            color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            position: fixed;
            width: inherit;
            max-width: inherit;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,.8);
            padding: 1rem;
            border-radius: 0.35rem;
            margin: 0.2rem 0.8rem;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,.1);
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background: rgba(255,255,255,.2);
            color: white;
            font-weight: 600;
        }

        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 1.5rem;
            text-align: center;
        }

        /* Card Styles */
        .card {
            border: none;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
            border-radius: 0.35rem;
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background: none;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-header h5 {
            margin: 0;
            font-weight: 700;
            color: var(--primary-color);
        }

        /* Table Styles */
        .table {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            color: var(--secondary-color);
            border-top: none;
        }

        .table td {
            vertical-align: middle;
            color: #5a5c69;
        }

        /* Button Styles */
        .btn {
            border-radius: 0.35rem;
            padding: 0.375rem 0.75rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
            transform: translateY(-2px);
        }

        .btn-icon {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.35rem;
            margin: 0 0.2rem;
        }

        /* Avatar Styles */
        .avatar-circle {
            width: 32px;
            height: 32px;
            background: linear-gradient(45deg, var(--primary-color), #224abe);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Badge Styles */
        .badge {
            padding: 0.5em 0.75em;
            font-weight: 600;
            border-radius: 0.35rem;
        }

        .badge.bg-info {
            background-color: var(--info-color) !important;
        }

        .badge.bg-secondary {
            background-color: var(--secondary-color) !important;
        }

        /* Modal Styles */
        .modal-content {
            border: none;
            border-radius: 0.35rem;
            box-shadow: var(--card-shadow);
        }

        .modal-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }

        .modal-title {
            color: var(--primary-color);
            font-weight: 700;
        }

        /* Form Styles */
        .form-control {
            border-radius: 0.35rem;
            padding: 0.375rem 0.75rem;
            border: 1px solid #d1d3e2;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .form-label {
            font-weight: 600;
            color: var(--secondary-color);
        }

        /* Alert Styles */
        .alert {
            border: none;
            border-radius: 0.35rem;
            box-shadow: var(--card-shadow);
        }

        /* Content Area */
        .content-area {
            padding: 2rem;
            margin-left: 16.666667%; /* Offset for fixed sidebar */
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                position: static;
                width: 100%;
            }
            .content-area {
                margin-left: 0;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="text-center py-4">
                    <h4><i class="bi bi-shield-lock-fill me-2"></i>Admin Panel</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#users">
                            <i class="bi bi-people-fill"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#roles">
                            <i class="bi bi-shield-lock-fill"></i> Roles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#documents">
                            <i class="bi bi-file-earmark-fill"></i> Documents
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#logs">
                            <i class="bi bi-journal-text"></i> System Logs
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 content-area">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Users Section -->
                <div id="users" class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>User Management</h5>
                        <div>
                            <button class="btn btn-outline-primary me-2" onclick="exportUsers()" data-bs-toggle="tooltip" title="Export Users">
                                <i class="bi bi-download"></i> Export
                            </button>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal" data-bs-toggle="tooltip" title="Add New User">
                                <i class="bi bi-plus-circle"></i> Add User
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="bi bi-person me-1"></i>Username</th>
                                        <th><i class="bi bi-envelope me-1"></i>Email</th>
                                        <th><i class="bi bi-shield me-1"></i>Role</th>
                                        <th><i class="bi bi-file-earmark me-1"></i>Documents</th>
                                        <th><i class="bi bi-clock me-1"></i>Last Activity</th>
                                        <th><i class="bi bi-gear me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-2">
                                                        <?php echo strtoupper(substr($u['username'], 0, 1)); ?>
                                                    </div>
                                                    <?php echo htmlspecialchars($u['username']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($u['role_name'] ?? 'No Role'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo $u['document_count'] ?? 0; ?> docs
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                if (isset($u['last_activity'])) {
                                                    $last_activity = new DateTime($u['last_activity']);
                                                    $now = new DateTime();
                                                    $interval = $last_activity->diff($now);
                                                    
                                                    if ($interval->d == 0) {
                                                        echo $interval->h . 'h ago';
                                                    } else if ($interval->d < 7) {
                                                        echo $interval->d . 'd ago';
                                                    } else {
                                                        echo $last_activity->format('M d, Y');
                                                    }
                                                } else {
                                                    echo 'Never';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-icon btn-primary" onclick="editUser(<?php echo htmlspecialchars(json_encode($u)); ?>)" data-bs-toggle="tooltip" title="Edit User">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </button>
                                                    <button class="btn btn-icon btn-info" onclick="viewUserDetails(<?php echo $u['id']; ?>)" data-bs-toggle="tooltip" title="View Details">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </button>
                                                    <button class="btn btn-icon btn-danger" onclick="deleteUser(<?php echo $u['id']; ?>)" data-bs-toggle="tooltip" title="Delete User">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Roles Section -->
                <div id="roles" class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Role Management</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                            <i class="bi bi-plus-circle"></i> Add Role
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Role Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roles as $r): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($r['role_name']); ?></td>
                                            <td>
                                                <button class="btn btn-icon btn-danger" onclick="deleteRole(<?php echo $r['role_id']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Documents Section -->
                <div id="documents" class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Document Management</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                            <i class="bi bi-upload"></i> Upload Document
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Uploaded By</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Uploaded</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documents as $doc): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($doc['title']); ?></td>
                                            <td><?php echo htmlspecialchars($doc['description']); ?></td>
                                            <td><?php echo htmlspecialchars($doc['uploaded_by_name']); ?></td>
                                            <td><?php echo htmlspecialchars($doc['file_type']); ?></td>
                                            <td><?php echo number_format($doc['file_size'] / 1024, 2) . ' KB'; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($doc['created_at'])); ?></td>
                                            <td>
                                                <a href="download.php?id=<?php echo $doc['document_id']; ?>" 
                                                   class="btn btn-icon btn-primary">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <button class="btn btn-icon btn-danger" 
                                                        onclick="deleteDocument(<?php echo $doc['document_id']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- System Logs Section -->
                <div id="logs" class="card">
                    <div class="card-header">
                        <h5 class="mb-0">System Logs</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Details</th>
                                        <th>IP Address</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></td>
                                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                                            <td><?php echo htmlspecialchars($log['details']); ?></td>
                                            <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                            <td><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" id="addUserForm" onsubmit="return validateAddUserForm()">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_user">
                        <div class="mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" required 
                                   pattern="[A-Za-z0-9_]{3,20}" 
                                   title="Username must be 3-20 characters long and can only contain letters, numbers, and underscores">
                            <div class="form-text">Username must be 3-20 characters long and can only contain letters, numbers, and underscores</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" required 
                                       pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" 
                                       title="Password must be at least 8 characters long and include uppercase, lowercase, and numbers">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword(this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Password must be at least 8 characters long and include uppercase, lowercase, and numbers</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-control" name="role_id" required>
                                <option value="">Select a role</option>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?php echo $r['role_id']; ?>">
                                        <?php echo htmlspecialchars($r['role_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Role Modal -->
    <div class="modal fade" id="addRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_role">
                        <div class="mb-3">
                            <label class="form-label">Role Name</label>
                            <input type="text" class="form-control" name="role_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload Document Modal -->
    <div class="modal fade" id="uploadDocumentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" action="upload.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Document File</label>
                            <input type="file" class="form-control" name="document" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" id="editUserForm" onsubmit="return validateEditUserForm()">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_user">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        <div class="mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" id="edit_username" required 
                                   pattern="[A-Za-z0-9_]{3,20}" 
                                   title="Username must be 3-20 characters long and can only contain letters, numbers, and underscores">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-control" name="role_id" id="edit_role_id" required>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?php echo $r['role_id']; ?>">
                                        <?php echo htmlspecialchars($r['role_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add View User Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="avatar-circle mx-auto" style="width: 80px; height: 80px; font-size: 2rem;" id="view_user_avatar">
                        </div>
                        <h4 class="mt-3" id="view_username"></h4>
                        <p class="text-muted" id="view_email"></p>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Role:</strong> <span id="view_role"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Documents:</strong> <span id="view_documents"></span></p>
                            <p><strong>Last Activity:</strong> <span id="view_last_activity"></span></p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h6>Recent Activity</h6>
                        <div class="table-responsive">
                            <table class="table table-sm" id="view_activity_table">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Details</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });

        function editUser(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_role_id').value = user.role_id;
            
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            modal.show();
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" value="${userId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteRole(roleId) {
            if (confirm('Are you sure you want to delete this role?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_role">
                    <input type="hidden" name="role_id" value="${roleId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteDocument(documentId) {
            if (confirm('Are you sure you want to delete this document?')) {
                window.location.href = `delete_document.php?id=${documentId}`;
            }
        }

        function togglePassword(button) {
            const input = button.previousElementSibling;
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        function validateAddUserForm() {
            const form = document.getElementById('addUserForm');
            const username = form.querySelector('input[name="username"]');
            const email = form.querySelector('input[name="email"]');
            const password = form.querySelector('input[name="password"]');
            const role = form.querySelector('select[name="role_id"]');

            // Reset previous validation states
            [username, email, password, role].forEach(field => {
                field.classList.remove('is-invalid');
            });

            let isValid = true;

            // Validate username
            if (!username.value.trim()) {
                username.classList.add('is-invalid');
                isValid = false;
            }

            // Validate email
            if (!email.value.trim() || !isValidEmail(email.value)) {
                email.classList.add('is-invalid');
                isValid = false;
            }

            // Validate password
            if (!password.value || !isValidPassword(password.value)) {
                password.classList.add('is-invalid');
                isValid = false;
            }

            // Validate role
            if (!role.value) {
                role.classList.add('is-invalid');
                isValid = false;
            }

            if (!isValid) {
                alert('Please fill in all required fields correctly');
            }

            return isValid;
        }

        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        function isValidPassword(password) {
            return /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/.test(password);
        }

        function toggleUserStatus(userId, isActive) {
            const newStatus = isActive ? 'active' : 'inactive';
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="toggle_user_status">
                <input type="hidden" name="user_id" value="${userId}">
                <input type="hidden" name="new_status" value="${newStatus}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        function viewUserDetails(userId) {
            // Fetch user details
            fetch(`get_user_details.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    // Update modal content
                    document.getElementById('view_user_avatar').textContent = data.username.charAt(0).toUpperCase();
                    document.getElementById('view_username').textContent = data.username;
                    document.getElementById('view_email').textContent = data.email;
                    document.getElementById('view_role').textContent = data.role_name;
                    document.getElementById('view_documents').textContent = data.document_count + ' documents';
                    document.getElementById('view_last_activity').textContent = data.last_activity;

                    // Update activity table
                    const tbody = document.getElementById('view_activity_table').querySelector('tbody');
                    tbody.innerHTML = '';
                    data.recent_activity.forEach(activity => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${activity.action}</td>
                                <td>${activity.details}</td>
                                <td>${activity.created_at}</td>
                            </tr>
                        `;
                    });

                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading user details');
                });
        }

        function validateEditUserForm() {
            const form = document.getElementById('editUserForm');
            const username = form.querySelector('input[name="username"]');
            const email = form.querySelector('input[name="email"]');
            const role = form.querySelector('select[name="role_id"]');

            // Reset previous validation states
            [username, email, role].forEach(field => {
                field.classList.remove('is-invalid');
            });

            let isValid = true;

            // Validate username
            if (!username.value.trim()) {
                username.classList.add('is-invalid');
                isValid = false;
            }

            // Validate email
            if (!email.value.trim() || !isValidEmail(email.value)) {
                email.classList.add('is-invalid');
                isValid = false;
            }

            // Validate role
            if (!role.value) {
                role.classList.add('is-invalid');
                isValid = false;
            }

            if (!isValid) {
                alert('Please fill in all required fields correctly');
            }

            return isValid;
        }

        function exportUsers() {
            window.location.href = 'export_users.php';
        }

        // Add tooltips to all buttons
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html> 