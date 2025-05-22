<?php
session_start();
require_once 'classes/Document.php';
require_once 'classes/Role.php';
require_once 'classes/Database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$pdo = $db->getConnection();
$document = new Document($pdo);
$role = new Role($db);

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    try {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $document->uploadDocument($title, $description, $_FILES['document'], $_SESSION['user_id']);
        $success = "Document uploaded successfully!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle document deletion
if (isset($_POST['delete_document'])) {
    try {
        $document->deleteDocument($_POST['document_id'], $_SESSION['user_id']);
        $success = "Document deleted successfully!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get user's documents
$sql = "SELECT d.*, u.username as uploaded_by_name 
        FROM documents d 
        LEFT JOIN users u ON d.uploaded_by = u.id 
        WHERE d.uploaded_by = ? 
        ORDER BY d.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$userDocuments = $stmt->fetchAll();

// Get shared documents
$sql = "SELECT d.*, u.username as uploaded_by_name 
        FROM documents d 
        JOIN document_shares ds ON d.document_id = ds.document_id 
        LEFT JOIN users u ON d.uploaded_by = u.id 
        WHERE ds.user_id = ? 
        ORDER BY d.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$sharedDocuments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Document Management</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Upload Document Form -->
        <div class="card">
            <h2>Upload New Document</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="document">Select File:</label>
                    <input type="file" id="document" name="document" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Upload Document</button>
            </form>
        </div>

        <!-- My Documents -->
        <div class="card">
            <h2>My Documents</h2>
            <?php if (empty($userDocuments)): ?>
                <p>No documents uploaded yet.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Uploaded By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userDocuments as $doc): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($doc['title']); ?></td>
                                <td><?php echo htmlspecialchars($doc['description']); ?></td>
                                <td><?php echo htmlspecialchars($doc['uploaded_by_name']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($doc['created_at'])); ?></td>
                                <td>
                                    <a href="download.php?id=<?php echo $doc['document_id']; ?>" class="btn btn-sm btn-primary">Download</a>
                                    <form action="" method="POST" style="display: inline;">
                                        <input type="hidden" name="document_id" value="<?php echo $doc['document_id']; ?>">
                                        <button type="submit" name="delete_document" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this document?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Shared Documents -->
        <div class="card">
            <h2>Shared Documents</h2>
            <?php if (empty($sharedDocuments)): ?>
                <p>No shared documents available.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Shared By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sharedDocuments as $doc): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($doc['title']); ?></td>
                                <td><?php echo htmlspecialchars($doc['description']); ?></td>
                                <td><?php echo htmlspecialchars($doc['uploaded_by_name']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($doc['created_at'])); ?></td>
                                <td>
                                    <a href="download.php?id=<?php echo $doc['document_id']; ?>" class="btn btn-sm btn-primary">Download</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 