<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Document.php';
require_once 'classes/Role.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$db_pdo = $db->getConnection();
$document = new Document($db, $_SESSION['user_id']);
$role = new Role($db);

$user_id = $_SESSION['user_id'];
$user = $db->query("SELECT * FROM users WHERE id = ?", [$user_id])->fetch();

// Handle document upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_document'])) {
    try {
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Please select a file to upload');
        }

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        
        if (empty($title)) {
            throw new Exception('Document title is required');
        }

        $document_id = $document->uploadDocument($title, $description, $_FILES['document'], $user_id);
        $_SESSION['success'] = 'Document uploaded successfully';
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    header('Location: dashboard.php');
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    try {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($username) || empty($email)) {
            throw new Exception('Username and email are required');
        }

        // Check if email is already taken by another user
        $existing_user = $db->query(
            "SELECT id FROM users WHERE email = ? AND id != ?",
            [$email, $user_id]
        )->fetch();

        if ($existing_user) {
            throw new Exception('Email is already taken');
        }

        // Handle password update if provided and user has manual authentication
        if (!empty($current_password) && !empty($new_password)) {
            // Get user's auth method
            $user_auth = $db->query(
                "SELECT auth_method FROM users WHERE id = ?",
                [$user_id]
            )->fetch();

            if ($user_auth['auth_method'] !== 'manual') {
                throw new Exception('Password update is only available for manual authentication users');
            }

            // Verify current password
            $current_user = $db->query(
                "SELECT password FROM users WHERE id = ?",
                [$user_id]
            )->fetch();

            if (!password_verify($current_password, $current_user['password'])) {
                throw new Exception('Current password is incorrect');
            }

            // Verify new password confirmation
            if ($new_password !== $confirm_password) {
                throw new Exception('New passwords do not match');
            }

            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $db->query(
                "UPDATE users SET password = ? WHERE id = ?",
                [$hashed_password, $user_id]
            );
        }

        // Update user profile
        $db->query(
            "UPDATE users SET username = ?, email = ? WHERE id = ?",
            [$username, $email, $user_id]
        );

        $_SESSION['success'] = 'Profile updated successfully';
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    header('Location: dashboard.php');
    exit();
}

// Get user's documents
$user_documents = $db->query(
    "SELECT * FROM documents WHERE uploaded_by = ? ORDER BY created_at DESC",
    [$user_id]
)->fetchAll();

// Get shared documents with more details
$shared_documents = $db->query(
    "SELECT DISTINCT d.*, 
            (SELECT username FROM users WHERE id = d.uploaded_by) as uploaded_by_name,
            (SELECT username FROM users WHERE id = (
                SELECT user_id FROM document_shares 
                WHERE document_id = d.document_id 
                AND user_id = ? 
                LIMIT 1
            )) as shared_by_name
     FROM documents d 
     JOIN document_shares ds ON d.document_id = ds.document_id 
     WHERE ds.user_id = ? 
     ORDER BY d.created_at DESC",
    [$user_id, $user_id]
)->fetchAll();

// Handle document deletion
if (isset($_POST['delete_document'])) {
    try {
        $documentId = $_POST['document_id'] ?? 0;
        if ($documentId > 0) {
            $document->deleteDocument($documentId, $_SESSION['user_id']);
            $_SESSION['success'] = "Document deleted successfully!";
        } else {
            $_SESSION['error'] = "Invalid document ID.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Document Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Document Management System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
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

        <div class="row">
            <!-- Profile Section -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bi bi-person-circle me-2"></i>Profile</h5>
                    </div>
                    <div class="card-body">
                        <!-- Authentication Method Info -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3"><i class="bi bi-shield-lock me-2"></i>Authentication Method</h6>
                            <?php
                            $auth_method = $user['auth_method'] ?? 'manual';
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
                                <i class="bi <?php echo $auth_icon; ?> fs-4 <?php echo $auth_class; ?> me-2"></i>
                                <span class="fw-bold"><?php echo $auth_label; ?></span>
                            </div>
                            <small class="text-muted d-block mt-1">
                                You signed in using <?php echo strtolower($auth_label); ?> authentication
                            </small>
                        </div>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary"><i class="bi bi-pencil-square me-2"></i>Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Document Upload Section -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bi bi-cloud-upload me-2"></i>Upload Document</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="title" class="form-label">Document Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="document" class="form-label">Select File</label>
                                <input type="file" class="form-control" id="document" name="document" required>
                            </div>
                            <button type="submit" name="upload_document" class="btn btn-primary"><i class="bi bi-upload me-2"></i>Upload Document</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- User's Documents Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bi bi-file-earmark-text me-2"></i>My Documents</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($user_documents)): ?>
                            <p class="text-muted">No documents uploaded yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Description</th>
                                            <th>Type</th>
                                            <th>Size</th>
                                            <th>Uploaded</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($user_documents as $doc): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($doc['title']); ?></td>
                                                <td><?php echo htmlspecialchars($doc['description']); ?></td>
                                                <td><?php echo htmlspecialchars($doc['file_type']); ?></td>
                                                <td><?php echo number_format($doc['file_size'] / 1024, 2) . ' KB'; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($doc['created_at'])); ?></td>
                                                <td>
                                                    <a href="download.php?id=<?php echo $doc['document_id']; ?>" 
                                                       class="btn btn-sm btn-primary"><i class="bi bi-download"></i> Download</a>
                                                    <button class="btn btn-sm btn-info" onclick="shareDocument(<?php echo $doc['document_id']; ?>, '<?php echo htmlspecialchars($doc['title']); ?>')">
                                                        <i class="bi bi-share"></i> Share
                                                    </button>
                                                    <a href="sign.php?id=<?php echo $doc['document_id']; ?>" 
                                                       class="btn btn-sm btn-success"><i class="bi bi-pencil-square"></i> Sign</a>
                                                    <a href="edit_document.php?id=<?php echo $doc['document_id']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>
                                                    <form action="" method="POST" style="display: inline;">
                                                        <input type="hidden" name="document_id" value="<?php echo $doc['document_id']; ?>">
                                                        <button type="submit" name="delete_document" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this document?')">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shared Documents Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bi bi-person-share me-2"></i>Shared with Me</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($shared_documents)): ?>
                            <p class="text-muted">No documents shared with you.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Description</th>
                                            <th>Shared By</th>
                                            <th>Original Owner</th>
                                            <th>Shared Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($shared_documents as $doc): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($doc['title']); ?></td>
                                                <td><?php echo htmlspecialchars($doc['description']); ?></td>
                                                <td><?php echo htmlspecialchars($doc['shared_by_name']); ?></td>
                                                <td><?php echo htmlspecialchars($doc['uploaded_by_name']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($doc['created_at'])); ?></td>
                                                <td>
                                                    <a href="download.php?id=<?php echo $doc['document_id']; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="bi bi-download"></i> Download
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Share Document Modal -->
    <div class="modal fade" id="shareDocumentModal" tabindex="-1" aria-labelledby="shareDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shareDocumentModalLabel">Share Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="shareDocumentForm" method="POST" action="share_document.php" onsubmit="return validateShareForm()">
                    <div class="modal-body">
                        <input type="hidden" name="document_id" id="shareDocumentId">
                        <div class="mb-3">
                            <label for="shareEmail" class="form-label">Share with (Gmail Address)</label>
                            <input type="email" class="form-control" id="shareEmail" name="email" required 
                                   pattern="[a-zA-Z0-9._%+-]+@gmail\.com$"
                                   placeholder="Enter recipient's Gmail address">
                            <small class="text-muted">Only Gmail addresses are allowed. The recipient must be a registered user.</small>
                        </div>
                        <input type="hidden" name="permission_level" value="read">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Share Document</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function shareDocument(documentId, documentTitle) {
        document.getElementById('shareDocumentId').value = documentId;
        document.getElementById('shareDocumentModalLabel').textContent = 'Share Document: ' + documentTitle;
        document.getElementById('shareEmail').value = ''; // Clear previous email
        var shareModal = new bootstrap.Modal(document.getElementById('shareDocumentModal'));
        shareModal.show();
    }

    function validateShareForm() {
        const email = document.getElementById('shareEmail').value;
        if (!email.endsWith('@gmail.com')) {
            alert('Please enter a valid Gmail address');
            return false;
        }
        return true;
    }

    // Add form submission handler for share document
    document.getElementById('shareDocumentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('shareEmail').value;
        if (!email.endsWith('@gmail.com')) {
            alert('Please enter a valid Gmail address');
            return;
        }
        
        // Submit the form
        this.submit();
    });
    </script>
</body>
</html> 