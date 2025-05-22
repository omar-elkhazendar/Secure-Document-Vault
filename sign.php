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
$db_pdo = $db->getConnection(); // Get the actual PDO connection
$document = new Document($db, $_SESSION['user_id']); // Pass the PDO connection and user ID to Document
$role = new Role($db); // Role class might still expect the custom Database object, keep as is for now

$user_id = $_SESSION['user_id'];
$doc = null; // Initialize document variable

// Check if document ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'Document ID is required';
    header('Location: dashboard.php');
    exit();
}

$document_id = $_GET['id'];

// Handle document signing (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sign_document'])) {
    try {
        $signature_data = $_POST['signature_data'] ?? '';
        
        if (empty($signature_data)) {
            throw new Exception('Signature is required');
        }

        // The signDocument method in Document.php is likely for a different signing process (e.g., storing drawn signature).
        // We need to decide where to store this signature_data if it's different from the digital signature generated on upload.
        // For now, we'll call the existing signDocument method.
        $document->signDocument($document_id, $user_id, $signature_data);

        $_SESSION['success'] = 'Document signed successfully';
        header('Location: dashboard.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
         // On error, redirect back to sign page with ID to display the error
        header('Location: sign.php?id=' . $document_id);
        exit();
    }
}

// Get document details for display (GET request)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Fetch document details without triggering download
        $sql = "SELECT * FROM documents WHERE document_id = ?";
        $stmt = $db_pdo->prepare($sql);
        $stmt->execute([$document_id]);
        $doc = $stmt->fetch();

        if (!$doc) {
            throw new Exception('Document not found.');
        }

        // You might want to add an access check here similar to downloadDocument
        // to ensure the user is allowed to view this document for signing.

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: dashboard.php');
        exit();
    }
}

// If GET request failed or document not found, $doc will be null, handled above.
// If POST request failed, it redirects back to this page with error, and GET logic runs to fetch doc details.

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Document - Document Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
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
                        <a class="nav-link" href="dashboard.php">Back to Dashboard</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if ($doc): // Display document details only if $doc is loaded ?>
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Sign Document: <?php echo htmlspecialchars($doc['title']); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6>Document Details:</h6>
                            <p><strong>Title:</strong> <?php echo htmlspecialchars($doc['title']); ?></p>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($doc['description']); ?></p>
                            <p><strong>Type:</strong> <?php echo htmlspecialchars($doc['file_type']); ?></p>
                            <p><strong>Size:</strong> <?php echo number_format($doc['file_size'] / 1024, 2) . ' KB'; ?></p>
                        </div>

                        <form method="POST" action="">
                            <input type="hidden" name="document_id" value="<?php echo $document_id; ?>">
                            <div class="mb-4">
                                <label class="form-label">Your Signature</label>
                                <div class="border rounded p-3">
                                    <canvas id="signature-pad" class="signature-pad" width="600" height="200"></canvas>
                                </div>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-secondary" id="clear-signature">Clear</button>
                                </div>
                                <input type="hidden" name="signature_data" id="signature-data">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="sign_document" class="btn btn-primary">Sign Document</button>
                                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php else: // Handle case where document details could not be loaded ?>
             <p>Could not load document details.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('signature-pad');
            // Check if canvas element exists before initializing SignaturePad
            if (canvas) {
                const signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)'
                });

                // Handle form submission
                document.querySelector('form').addEventListener('submit', function(e) {
                    if (signaturePad.isEmpty()) {
                        e.preventDefault();
                        alert('Please provide your signature');
                        return;
                    }
                    document.getElementById('signature-data').value = signaturePad.toDataURL();
                });

                // Handle clear button
                const clearButton = document.getElementById('clear-signature');
                 if (clearButton) {
                    clearButton.addEventListener('click', function() {
                        signaturePad.clear();
                    });
                 }

                // Adjust canvas size
                function resizeCanvas() {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);
                    signaturePad.clear();
                }

                window.addEventListener('resize', resizeCanvas);
                resizeCanvas();
            }
        });
    </script>
</body>
</html> 