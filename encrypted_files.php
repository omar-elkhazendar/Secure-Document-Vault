<?php
session_start();
require_once 'config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's role
$stmt = $db->prepare("SELECT role_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userRole = $stmt->fetchColumn();

// Get files from files_with_signatures table
$stmt = $db->prepare("
    SELECT fs.*, u.username as uploaded_by_username
    FROM files_with_signatures fs
    JOIN users u ON fs.user_id = u.id
    WHERE fs.user_id = ? OR ? IN (
        SELECT user_id FROM users WHERE role_id = 1
    )
    ORDER BY fs.upload_date DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encrypted Files</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Encrypted Files</h1>
        
        <!-- Upload Form -->
        <div class="upload-section">
            <h2>Upload New File</h2>
            <form id="uploadForm" enctype="multipart/form-data">
                <input type="file" name="file" required>
                <button type="submit">Upload & Encrypt</button>
            </form>
            <div id="uploadStatus"></div>
        </div>
        
        <!-- Files List -->
        <div class="files-section">
            <h2>Your Files</h2>
            <table>
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th>Uploaded By</th>
                        <th>Upload Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $file): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($file['filename']); ?></td>
                        <td><?php echo htmlspecialchars($file['uploaded_by_username']); ?></td>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($file['upload_date'])); ?></td>
                        <td>
                            <a href="download_encrypted.php?file_id=<?php echo $file['id']; ?>" 
                               class="btn btn-primary">Download (Verification Only)</a>
                            <a href="edit_encrypted.php?file_id=<?php echo $file['id']; ?>" 
                               class="btn btn-secondary">Edit</a>
                            <a href="delete_encrypted.php?file_id=<?php echo $file['id']; ?>" 
                               class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this file?');">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const statusDiv = document.getElementById('uploadStatus');
        
        statusDiv.textContent = 'Uploading...';
        
        fetch('upload_encrypted.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusDiv.textContent = data.message;
                setTimeout(() => location.reload(), 1000);
            } else {
                statusDiv.textContent = 'Error: ' + data.message;
            }
        })
        .catch(error => {
            statusDiv.textContent = 'Error: ' + error.message;
        });
    });
    </script>
</body>
</html> 