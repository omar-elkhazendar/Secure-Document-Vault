<?php
require_once 'config/config.php';
require_once 'classes/Document.php';
require_once 'classes/CryptoHandler.php';
require_once 'classes/Role.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Please log in to download documents.");
}

$userId = $_SESSION['user_id'];
$documentId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($documentId <= 0) {
    die("Invalid document ID.");
}

try {
    // Initialize database connection
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );

    // Get document details with proper access control
    $stmt = $db->prepare("
        SELECT d.*, u.public_key 
        FROM documents d 
        LEFT JOIN user_keys u ON d.uploaded_by = u.user_id 
        LEFT JOIN document_shares ds ON d.document_id = ds.document_id AND ds.user_id = ?
        WHERE d.document_id = ? 
        AND (
            d.uploaded_by = ? 
            OR ds.user_id = ? 
            OR EXISTS (
                SELECT 1 
                FROM users u2 
                JOIN roles r ON u2.role_id = r.role_id 
                WHERE u2.id = ? 
                AND r.role_name = 'admin'
            )
        )
    ");
    $stmt->execute([$userId, $documentId, $userId, $userId, $userId]);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$document) {
        die("Document not found or you don't have permission to access it.");
    }

    // Initialize CryptoHandler
    $crypto = new CryptoHandler($db, $userId);

    // Get the file path and ensure it's absolute
    $filePath = $document['file_path'];
    if (!file_exists($filePath)) {
        // Try with absolute path
        $filePath = __DIR__ . '/' . $document['file_path'];
        if (!file_exists($filePath)) {
            die("Download failed: Document file not found on the filesystem. Path: " . $filePath);
        }
    }
    
    // Read the file
    $fileContent = file_get_contents($filePath);
    if ($fileContent === false) {
        die("Failed to read document file.");
    }

    // Verify document integrity if signatures exist
    if (!empty($document['sha256_hash'])) {
        $calculatedHash = hash('sha256', $fileContent);
        if ($calculatedHash !== $document['sha256_hash']) {
            die("Document integrity check failed. The file may have been tampered with.");
        }
    }

    if (!empty($document['hmac_signature'])) {
        $calculatedHmac = hash_hmac('sha256', $fileContent, HMAC_SECRET_KEY);
        if ($calculatedHmac !== $document['hmac_signature']) {
            die("Document HMAC verification failed. The file may have been tampered with.");
        }
    }

    if (!empty($document['digital_signature']) && !empty($document['public_key'])) {
        if (!$crypto->verifySignature($fileContent, $document['digital_signature'], $document['public_key'])) {
            die("Document signature verification failed. The file may have been tampered with.");
        }
    }

    // Log the download
    $logStmt = $db->prepare("
        INSERT INTO system_logs (user_id, action, details) 
        VALUES (?, 'download', ?)
    ");
    $logStmt->execute([$userId, "Downloaded document ID: " . $documentId]);

    // Get the original filename from the file path
    $originalFilename = basename($document['file_path']);
    
    // Set appropriate headers for file download
    if (!empty($document['file_type'])) {
        // Map common file types to their correct MIME types
        $mimeTypes = [
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];

        // Get file extension
        $extension = strtolower(pathinfo($document['file_path'], PATHINFO_EXTENSION));
        
        // Set the correct MIME type
        if (isset($mimeTypes[$extension])) {
            header('Content-Type: ' . $mimeTypes[$extension]);
        } else {
            header('Content-Type: ' . $document['file_type']);
        }
    } else {
        // If file type is not stored, try to determine it
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $fileContent);
        finfo_close($finfo);
        header('Content-Type: ' . $mimeType);
    }
    
    // Set content disposition based on file type
    $isViewable = in_array($document['file_type'], [
        'image/jpeg',
        'image/png',
        'image/gif',
        'text/plain',
        'text/html',
        'application/json'
    ]);
    
    // Always force download for Office documents
    $isOfficeDoc = in_array($extension, ['doc', 'docx', 'xls', 'xlsx']);
    
    if ($isViewable && !$isOfficeDoc) {
        header('Content-Disposition: inline; filename="' . $originalFilename . '"');
    } else {
        header('Content-Disposition: attachment; filename="' . $originalFilename . '"');
    }
    
    header('Content-Length: ' . strlen($fileContent));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Output the file
    echo $fileContent;
    exit;

} catch (Exception $e) {
    error_log("Download error: " . $e->getMessage());
    die("An error occurred while downloading the document. Please try again later.");
} 