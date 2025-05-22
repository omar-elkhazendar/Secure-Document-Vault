<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Document.php';
require_once 'classes/User.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user = new User();
$document = new Document($db, $_SESSION['user_id']);

// Get user's documents
$documents = $document->getUserDocuments();

// Get user's shared documents
$shared_documents = $document->getSharedDocuments();

// Get user's account info
$account_info = $user->getAccountInfo();
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
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="text-center mb-4">
                    <h4><i class="bi bi-file-earmark"></i> Dashboard</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#documents">
                            <i class="bi bi-file-earmark"></i> My Documents
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#shared">
                            <i class="bi bi-share"></i> Shared with Me
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#profile">
                            <i class="bi bi-person"></i> Profile
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
            <div class="col-md-9 col-lg-10 main-content">
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

                <!-- Documents Section -->
                <div id="documents" class="table-container mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">My Documents</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="bi bi-upload"></i> Upload Document
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                                <?php foreach ($documents as $doc): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($doc['title']); ?></td>
                                        <td><?php echo htmlspecialchars($doc['description']); ?></td>
                                        <td><?php echo htmlspecialchars($doc['file_type']); ?></td>
                                        <td><?php echo number_format($doc['file_size'] / 1024, 2) . ' KB'; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($doc['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="download.php?id=<?php echo $doc['document_id']; ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <a href="edit_document.php?id=<?php echo $doc['document_id']; ?>" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger" 
                                                        onclick="deleteDocument(<?php echo $doc['document_id']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Shared Documents Section -->
                <div id="shared" class="table-container mb-4">
                    <h5 class="mb-3">Shared with Me</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Shared By</th>
                                    <th>Shared On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($shared_documents as $doc): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($doc['title']); ?></td>
                                        <td><?php echo htmlspecialchars($doc['description']); ?></td>
                                        <td><?php echo htmlspecialchars($doc['shared_by']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($doc['shared_at'])); ?></td>
                                        <td>
                                            <a href="download.php?id=<?php echo $doc['document_id']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Profile Section -->
                <div id="profile" class="table-container">
                    <h5 class="mb-3">Profile Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Account Details</h6>
                                    <p><strong>Username:</strong> <?php echo htmlspecialchars($account_info['username']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($account_info['email']); ?></p>
                                    <p><strong>Account Created:</strong> <?php echo date('M d, Y', strtotime($account_info['created_at'])); ?></p>
                                    <p><strong>Status:</strong> 
                                        <span class="badge bg-<?php echo $account_info['status'] === 'active' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($account_info['status']); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
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

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        function deleteDocument(documentId) {
            if (confirm('Are you sure you want to delete this document?')) {
                window.location.href = `delete_document.php?id=${documentId}`;
            }
        }
    </script>
</body>
</html> 