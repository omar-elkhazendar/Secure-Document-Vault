<?php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Document.php';
require_once '../classes/Role.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$role = new Role($database);

// Verify admin role
$user_role = $role->getUserRole($_SESSION['user_id']);
if (!$user_role || $user_role['role_name'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit();
}

// Check if document ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'Document ID is required';
    header('Location: dashboard.php');
    exit();
}

$document_id = $_GET['id'];
$document = new Document($db, $_SESSION['user_id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        
        if (empty($title)) {
            throw new Exception('Title is required');
        }

        // Update document details
        $stmt = $db->prepare("
            UPDATE documents 
            SET title = ?, description = ? 
            WHERE document_id = ?
        ");
        
        if ($stmt->execute([$title, $description, $document_id])) {
            $_SESSION['success'] = 'Document updated successfully';
            header('Location: dashboard.php');
            exit();
        } else {
            throw new Exception('Failed to update document');
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Get document details
try {
    $stmt = $db->prepare("
        SELECT d.*, u.username as uploaded_by_name 
        FROM documents d 
        LEFT JOIN users u ON d.uploaded_by = u.id 
        WHERE d.document_id = ?
    ");
    $stmt->execute([$document_id]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        throw new Exception('Document not found');
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Document - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Edit Document</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" 
                                       value="<?php echo htmlspecialchars($doc['title']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3"><?php 
                                    echo htmlspecialchars($doc['description']); 
                                ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Current File</label>
                                <p class="form-control-static">
                                    <?php echo htmlspecialchars($doc['file_path']); ?>
                                    <br>
                                    <small class="text-muted">
                                        Uploaded by: <?php echo htmlspecialchars($doc['uploaded_by_name']); ?>
                                    </small>
                                </p>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Update Document</button>
                                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 