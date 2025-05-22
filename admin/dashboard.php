<?php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Document.php';
require_once '../classes/Role.php';
require_once '../classes/User.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user = new User();
$role = new Role($database);
$document = new Document($db, $_SESSION['user_id']);

// Get current user's role
$user_role = $role->getUserRole($_SESSION['user_id']);
if (!$user_role || $user_role['role_name'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit();
}

// Get statistics
$total_users = $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
$total_documents = $db->query("SELECT COUNT(*) as count FROM documents")->fetch()['count'];
$total_roles = $db->query("SELECT COUNT(*) as count FROM roles")->fetch()['count'];
$recent_logs = $db->query("SELECT COUNT(*) as count FROM system_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetch()['count'];

// Handle user management actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_user':
                try {
                    $user_data = [
                        'username' => $_POST['username'],
                        'email' => $_POST['email'],
                        'password' => $_POST['password'],
                        'role_id' => $_POST['role_id']
                    ];
                    $user->username = $user_data['username'];
                    $user->email = $user_data['email'];
                    $user->password = $user_data['password'];
                    $user->auth_method = 'manual';
                    $user->status = 'active';
                    
                    if ($user->create()) {
                         // Update role if provided
                        if (!empty($user_data['role_id'])) {
                            $role->assignRoleToUser($user->id, $user_data['role_id']);
                        }
                       $_SESSION['success'] = 'User added successfully';
                    } else {
                       $_SESSION['error'] = 'Failed to add user.';
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = $e->getMessage();
                }
                break;

            case 'edit_user':
                try {
                    // Log received data for debugging
                    error_log("Edit User POST Data: " . print_r($_POST, true));

                    $user_data = [
                        'username' => $_POST['username'],
                        'email' => $_POST['email'],
                        'role_id' => $_POST['role_id']
                    ];

                    // Start transaction
                    $db->beginTransaction();

                    // Update user basic details (username and email)
                    if (!$user->updateUser($_POST['user_id'], $user_data)) {
                        throw new Exception('Failed to update user details');
                    }

                    // Update user role if role_id is provided
                    if (!empty($user_data['role_id'])) {
                        if (!$role->assignRoleToUser($_POST['user_id'], $user_data['role_id'])) {
                            throw new Exception('Failed to update user role');
                        }
                    }

                    // Log the update
                    $stmt = $db->prepare(
                        "INSERT INTO system_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)"
                    );
                    $stmt->execute([
                        $_SESSION['user_id'],
                        'edit_user',
                        "Updated user ID: {$_POST['user_id']}",
                        $_SERVER['REMOTE_ADDR']
                    ]);

                    // Commit transaction
                    $db->commit();
                    $_SESSION['success'] = 'User updated successfully.';
                    
                } catch (Exception $e) {
                    // Rollback transaction on error
                    if ($db->inTransaction()) {
                        $db->rollBack();
                    }
                    $_SESSION['error'] = $e->getMessage();
                }
                break;

            case 'delete_user':
                try {
                    // Start transaction
                    $db->beginTransaction();

                    $user_id = $_POST['user_id'];

                    // Delete user's documents first
                    $stmt = $db->prepare("DELETE FROM documents WHERE uploaded_by = ?");
                    $stmt->execute([$user_id]);

                    // Delete user's document shares
                    $stmt = $db->prepare("DELETE FROM document_shares WHERE user_id = ?");
                    $stmt->execute([$user_id]);

                    // Delete user's document signatures
                    $stmt = $db->prepare("DELETE FROM document_signatures WHERE user_id = ?");
                    $stmt->execute([$user_id]);

                    // Delete user's keys
                    $stmt = $db->prepare("DELETE FROM user_keys WHERE user_id = ?");
                    $stmt->execute([$user_id]);

                    // Delete user's system logs
                    $stmt = $db->prepare("DELETE FROM system_logs WHERE user_id = ?");
                    $stmt->execute([$user_id]);

                    // Delete the user
                    if ($user->delete($user_id)) {
                        // Log the deletion
                        $stmt = $db->prepare("
                            INSERT INTO system_logs (user_id, action, details, ip_address) 
                            VALUES (?, 'delete_user', ?, ?)
                        ");
                        $stmt->execute([
                            $_SESSION['user_id'],
                            "Deleted user ID: " . $user_id,
                            $_SERVER['REMOTE_ADDR']
                        ]);

                        $db->commit();
                        $_SESSION['success'] = 'User deleted successfully';
                    } else {
                        throw new Exception('Failed to delete user');
                    }
                } catch (Exception $e) {
                    // Rollback transaction on error
                    if ($db->inTransaction()) {
                        $db->rollBack();
                    }
                    $_SESSION['error'] = $e->getMessage();
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

            case 'activate_user':
                try {
                    if (isset($_POST['user_id'])) {
                        if ($user->activateAccount($_POST['user_id'])) {
                            $_SESSION['success'] = 'User activated successfully';
                        } else {
                            $_SESSION['error'] = 'Failed to activate user.';
                        }
                    } else {
                         $_SESSION['error'] = 'User ID not provided for activation.';
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = $e->getMessage();
                }
                break;
        }
        // Redirect after processing POST request to prevent form resubmission and display messages
        session_write_close(); // Explicitly save session data
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Debug: Display session messages before they are unset
// echo '<pre>'; print_r($_SESSION); echo '</pre>';

// Get all users with additional information
$users = $db->query(
    "SELECT u.*, r.role_name, 
            (SELECT COUNT(*) FROM documents WHERE uploaded_by = u.id) as document_count
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
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        :root {
            --primary-color: #2ecc71;
            --secondary-color: #27ae60;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --danger-color: #e74c3c;
            --warning-color: #f1c40f;
            --info-color: #3498db;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            min-height: 100vh;
            background: var(--dark-color);
            color: white;
            padding-top: 20px;
            position: fixed;
            width: 250px;
            transition: all 0.3s;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,.8);
            padding: 12px 20px;
            margin: 4px 0;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,.1);
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background: var(--primary-color);
            color: white;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            font-size: 1.1em;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }

        .stat-card {
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,.1);
            transition: all 0.3s;
            border: none;
            background: white;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,.15);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,.1);
            margin-bottom: 30px;
        }

        .table-container h5 {
            color: var(--dark-color);
            font-weight: 600;
            margin-bottom: 20px;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            color: var(--dark-color);
            border-top: none;
        }

        .table td {
            vertical-align: middle;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            background: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .modal-header .btn-close {
            color: white;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #dee2e6;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(46, 204, 113, 0.25);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                padding: 0;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="text-center mb-4">
                    <h4><i class="bi bi-shield-lock"></i> Admin Panel</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#dashboard">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#users">
                            <i class="bi bi-people"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#roles">
                            <i class="bi bi-shield-lock"></i> Roles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#documents">
                            <i class="bi bi-file-earmark"></i> Documents
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#logs">
                            <i class="bi bi-journal-text"></i> System Logs
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link text-danger" href="../logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <h3><?php echo $total_users; ?></h3>
                            <p class="mb-0 text-muted">Total Users</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-file-earmark"></i>
                            </div>
                            <h3><?php echo $total_documents; ?></h3>
                            <p class="mb-0 text-muted">Total Documents</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <h3><?php echo $total_roles; ?></h3>
                            <p class="mb-0 text-muted">Total Roles</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-activity"></i>
                            </div>
                            <h3><?php echo $recent_logs; ?></h3>
                            <p class="mb-0 text-muted">Recent Activities</p>
                        </div>
                    </div>
                </div>

                <!-- Users Section -->
                <div id="users" class="table-container mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">User Management</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
                            <i class="bi bi-person-plus"></i> Add User
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Auth Method</th>
                                    <th>Status</th>
                                    <th>Documents</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td><?php echo htmlspecialchars($u['role_name'] ?? 'No Role'); ?></td>
                                        <td>
                                            <?php
                                            $auth_method = $u['auth_method'] ?? 'manual';
                                            $auth_icon = '';
                                            $auth_class = '';
                                            
                                            switch($auth_method) {
                                                case 'github':
                                                    $auth_icon = 'bi-github';
                                                    $auth_class = 'text-dark';
                                                    $auth_label = 'GitHub';
                                                    break;
                                                case 'google':
                                                    $auth_icon = 'bi-google';
                                                    $auth_class = 'text-danger';
                                                    $auth_label = 'Google';
                                                    break;
                                                case 'okta':
                                                    $auth_icon = 'bi-shield-check';
                                                    $auth_class = 'text-primary';
                                                    $auth_label = 'Okta';
                                                    break;
                                                default:
                                                    $auth_icon = 'bi-person';
                                                    $auth_class = 'text-secondary';
                                                    $auth_label = 'Manual';
                                            }
                                            ?>
                                            <div class="d-flex align-items-center">
                                                <i class="bi <?php echo $auth_icon; ?> <?php echo $auth_class; ?> me-2"></i>
                                                <span><?php echo $auth_label; ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $u['status'] === 'active' ? 'success' : ($u['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                <?php echo ucfirst($u['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $u['document_count']; ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-primary" onclick="editUser(<?php echo htmlspecialchars(json_encode($u)); ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <?php if ($u['status'] === 'pending' && $u['auth_method'] === 'manual'): ?>
                                                    <form action="" method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="activate_user">
                                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="bi bi-check-circle"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <form action="" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                    <input type="hidden" name="action" value="delete_user">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Roles Section -->
                <div id="roles" class="table-container mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Role Management</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                            <i class="bi bi-plus-circle"></i> Add Role
                        </button>
                    </div>
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
                                            <button class="btn btn-sm btn-danger" onclick="deleteRole(<?php echo $r['role_id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Documents Section -->
                <div id="documents" class="table-container mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Document Management</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                            <i class="bi bi-file-earmark-plus"></i> Upload Document
                        </button>
                    </div>
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
                                            <a href="../download.php?id=<?php echo $doc['document_id']; ?>" class="btn btn-icon btn-primary">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <a href="../edit_document.php?id=<?php echo $doc['document_id']; ?>" class="btn btn-icon btn-warning">
                                                <i class="bi bi-pencil"></i>
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

                <!-- System Logs Section -->
                <div id="logs" class="table-container mb-4">
                    <h5 class="mb-3">System Logs</h5>
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
                                        <td><?php echo htmlspecialchars($log['action'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($log['details'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></td>
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

    <!-- Add/Edit User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="userForm" method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="userAction" value="add_user">
                        <input type="hidden" name="user_id" id="userId">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role_id" class="form-label">Role</label>
                            <select class="form-select" id="role_id" name="role_id" required>
                                <option value="">Select Role</option>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?php echo $r['role_id']; ?>"><?php echo htmlspecialchars($r['role_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete User Modal (Placeholder) -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this user?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteUserForm" method="POST" action="">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="user_id" id="deleteUserId">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Role Modal (Placeholder) -->
    <div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-labelledby="deleteRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteRoleModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this role?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteRoleForm" method="POST" action="">
                        <input type="hidden" name="action" value="delete_role">
                        <input type="hidden" name="role_id" id="deleteRoleId">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Role Modal -->
    <div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRoleModalLabel">Add Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_role">
                        <div class="mb-3">
                            <label for="role_name" class="form-label">Role Name</label>
                            <input type="text" class="form-control" id="role_name" name="role_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="shareWithAll" name="share_with_all">
                            <label class="form-check-label" for="shareWithAll">Share with all users</label>
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

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        // Function to open modal for adding a user
        function addUser() {
            document.getElementById('userModalLabel').textContent = 'Add User';
            document.getElementById('userAction').value = 'add_user';
            document.getElementById('userId').value = '';
            document.getElementById('username').value = '';
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
            document.getElementById('password').required = true;
            document.getElementById('role_id').value = '';
            
            // Reset form validation
            document.getElementById('userForm').reset();
            
            // Open the modal using Bootstrap's API
            var userModal = new bootstrap.Modal(document.getElementById('userModal'));
            userModal.show();
        }

        // Function to open modal for editing a user
        function editUser(user) {
            document.getElementById('userModalLabel').textContent = 'Edit User';
            document.getElementById('userAction').value = 'edit_user';
            document.getElementById('userId').value = user.id;
            document.getElementById('username').value = user.username;
            document.getElementById('email').value = user.email;
            document.getElementById('password').value = '';
            document.getElementById('password').required = false;
            document.getElementById('role_id').value = user.role_id || '';
            
            // Open the modal using Bootstrap's API
            var userModal = new bootstrap.Modal(document.getElementById('userModal'));
            userModal.show();
        }

        // Add form submission handler
        document.getElementById('userForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const action = document.getElementById('userAction').value;
            const userId = document.getElementById('userId').value;
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const roleId = document.getElementById('role_id').value;
            
            // Validate form
            if (!username || !email || !roleId) {
                alert('Please fill in all required fields');
                return;
            }
            
            if (action === 'add_user' && !password) {
                alert('Password is required for new users');
                return;
            }
            
            // Submit the form
            this.submit();
        });

        // Function to open modal for deleting a user
        function deleteUser(userId) {
            document.getElementById('deleteUserId').value = userId;
            var deleteUserModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
            deleteUserModal.show();
        }

        // Function to open modal for deleting a role
        function deleteRole(roleId) {
            document.getElementById('deleteRoleId').value = roleId;
            var deleteRoleModal = new bootstrap.Modal(document.getElementById('deleteRoleModal'));
            deleteRoleModal.show();
        }

        // Function to handle document deletion
        function deleteDocument(documentId) {
            if (confirm('Are you sure you want to delete this document?')) {
                window.location.href = `delete_document.php?id=${documentId}`;
            }
        }

        // Function to handle document download (for admin dashboard)
        function downloadAdminDocument(documentId) {
             window.location.href = `../download.php?id=${documentId}`;
        }

        // Hook up the Add User button to the addUser function
        document.querySelector('button[data-bs-target="#userModal"]').setAttribute('onclick', 'addUser()');
        document.querySelector('button[data-bs-target="#userModal"]').removeAttribute('data-bs-toggle');
        document.querySelector('button[data-bs-target="#userModal"]').removeAttribute('data-bs-target');
    </script>
</body>
</html> 