<?php
session_start();
require_once 'config/config.php';
require_once 'classes/CryptoHandler.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    try {
        $file = $_FILES['file'];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed');
        }
        
        // Read file contents
        $fileData = file_get_contents($file['tmp_name']);
        
        // Initialize crypto handler
        $cryptoHandler = new CryptoHandler($db, $_SESSION['user_id']);
        
        // Encrypt file and get signature
        // The encryptFile method in CryptoHandler returns encrypted_data, encrypted_key, iv, file_hash, and signature
        $encryptionResult = $cryptoHandler->encryptFile($fileData);
        
        // Define upload directory and file path
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = uniqid() . '_' . basename($file['name']);
        $filePath = $uploadDir . $fileName;
        
        // Save encrypted file to filesystem
        if (file_put_contents($filePath, $encryptionResult['encrypted_data']) === false) {
            throw new Exception('Failed to save encrypted file.');
        }
        
        // Store file metadata and signatures in database
        $stmt = $db->prepare("
            INSERT INTO files_with_signatures 
            (user_id, filename, content_type, sha256_hash, encrypted_key, iv, digital_signature, file_path, upload_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        // Note: HMAC signature is not generated in current CryptoHandler, leaving it as NULL for now.
        // If HMAC is needed, you would need to add that logic.
        $hmacSignature = null; 
        
        $stmt->execute([
            $_SESSION['user_id'],
            $file['name'],
            $file['type'], // Using file type as content_type
            $encryptionResult['file_hash'],
            $encryptionResult['encrypted_key'], // Store encrypted_key
            $encryptionResult['iv'], // Store iv
            $encryptionResult['signature'],
            $filePath
        ]);
        
        $response['success'] = true;
        $response['message'] = 'File uploaded, encrypted, and signed successfully';
        
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
}

header('Content-Type: application/json');
echo json_encode($response); 