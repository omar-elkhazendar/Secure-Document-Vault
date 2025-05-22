<?php
require_once 'classes/Session.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Role.php';
require_once 'classes/Document.php';

session_start();

$session = new Session();
$database = new Database();
$db = $database->getConnection();
$user = new User();
$role = new Role($db);

// Check if user is logged in
if (!$session->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $session->get('user_id');
$user_role = $role->getUserRole($user_id);
$is_admin = ($user_role['role_name'] === 'admin');

// Get document ID from URL
$document_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$document_id) {
    header('Location: documents.php');
    exit();
}

// Get document details
$stmt = $db->prepare("SELECT * FROM documents WHERE document_id = ?");
$stmt->execute([$document_id]);
$document = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$document) {
    header('Location: documents.php');
    exit();
}

// Check if user has permission to edit
$can_edit = false;
if ($is_admin) {
    $can_edit = true;
} else {
    // Check if user owns the document
    if ($document['uploaded_by'] == $user_id) {
        $can_edit = true;
    } else {
        // Check if user has write permission through document_shares
        $share_stmt = $db->prepare("SELECT permission_level FROM document_shares WHERE document_id = ? AND user_id = ?");
        $share_stmt->execute([$document_id, $user_id]);
        $share = $share_stmt->fetch(PDO::FETCH_ASSOC);
        if ($share && ($share['permission_level'] === 'write' || $share['permission_level'] === 'admin')) {
            $can_edit = true;
        }
    }
}

if (!$can_edit) {
    header('Location: documents.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    
    if (empty($title)) {
        $error = "Title is required";
    } else {
        try {
            $db->beginTransaction();
            
            // Create a new version entry
            $version_stmt = $db->prepare("INSERT INTO document_versions (document_id, version_number, file_path, changes_description, created_by) 
                                        SELECT ?, COALESCE(MAX(version_number), 0) + 1, file_path, ?, ? 
                                        FROM document_versions WHERE document_id = ?");
            $version_stmt->execute([$document_id, "Document updated", $user_id, $document_id]);
            
            // Handle file upload if a new file is provided
            $new_file_path = $document['file_path'];
            $new_sha256_hash = $document['sha256_hash'];
            $new_hmac_signature = $document['hmac_signature'];
            $new_digital_signature = $document['digital_signature'];
            $file_type = $document['file_type'];
            $file_size = $document['file_size'];
            if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] == 0) {
                $file = $_FILES['document_file'];
                $file_name = basename($file['name']);
                $file_type = $file['type'];
                $file_size = $file['size'];
                // Generate unique filename
                $new_file_name = uniqid() . '_' . $file_name;
                $upload_dir = 'uploads/documents/';
                $new_file_path = $upload_dir . $new_file_name;
                if (move_uploaded_file($file['tmp_name'], $new_file_path)) {
                    // Delete old file if it exists
                    if (file_exists($document['file_path'])) {
                        unlink($document['file_path']);
                    }
                    // Recalculate hash, HMAC, and digital signature
                    require_once 'classes/CryptoHandler.php';
                    require_once __DIR__ . '/config/hmac_config.php';
                    $fileContent = file_get_contents($new_file_path);
                    $new_sha256_hash = hash('sha256', $fileContent);
                    
                    // Use the original uploader's ID for digital signature regeneration
                    $original_uploader_id = $document['uploaded_by'];
                    $cryptoHandler = new CryptoHandler($db, $original_uploader_id);
                    $privateKeyResource = $cryptoHandler->getPrivateKeyResource();

                    if (!$privateKeyResource) {
                         // Handle error if original uploader's key is missing
                         throw new Exception('Original uploader\'s private key not found or could not be loaded for signing the updated document.');
                    }

                    $digitalSignature = '';
                    if (!openssl_sign($new_sha256_hash, $digitalSignature, $privateKeyResource, OPENSSL_ALGO_SHA256)) {
                        throw new Exception('Failed to generate digital signature for the updated document.');
                    }
                    $new_digital_signature = base64_encode($digitalSignature);
                    
                    // HMAC signature uses the shared secret key, so the current user's ID is not needed here
                    $new_hmac_signature = hash_hmac('sha256', $fileContent, HMAC_SECRET_KEY);
                } else {
                    throw new Exception("Failed to upload file");
                }
            }
            
            // Update document
            $update_stmt = $db->prepare("UPDATE documents SET 
                title = ?, 
                description = ?, 
                file_path = ?,
                file_type = ?,
                file_size = ?,
                sha256_hash = ?,
                hmac_signature = ?,
                digital_signature = ?,
                updated_at = CURRENT_TIMESTAMP 
                WHERE document_id = ?");
                
            $update_stmt->execute([
                $title,
                $description,
                $new_file_path,
                $file_type,
                $file_size,
                $new_sha256_hash,
                $new_hmac_signature,
                $new_digital_signature,
                $document_id
            ]);
            
            $db->commit();
            $success = "Document updated successfully";
            
            // Refresh document data
            $stmt = $db->prepare("SELECT * FROM documents WHERE document_id = ?");
            $stmt->execute([$document_id]);
            $document = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Error updating document: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Document - SecureAuth</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="fade-in">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mt-5">
                    <div class="card-header">
                        <h3 class="text-center">Edit Document</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($document['title']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($document['description']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="document_file" class="form-label">Replace File (Optional)</label>
                                <input type="file" class="form-control" id="document_file" name="document_file">
                                <small class="text-muted">Current file: <?php echo htmlspecialchars(basename($document['file_path'])); ?></small>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 