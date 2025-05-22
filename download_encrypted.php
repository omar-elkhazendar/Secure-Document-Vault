<?php
session_start();
require_once 'config/config.php';
require_once 'classes/CryptoHandler.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['file_id'])) {
    die('No file specified');
}

try {
    // Get file metadata from files_with_signatures table
    $stmt = $db->prepare("
        SELECT fs.*, u.id as uploaded_by_user_id
        FROM files_with_signatures fs
        JOIN users u ON fs.user_id = u.id
        WHERE fs.id = ? AND (fs.user_id = ? OR ? IN (
            SELECT user_id FROM users WHERE role_id = 1
        ))
    ");
    
    $stmt->execute([$_GET['file_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$file) {
        die('File not found or access denied');
    }
    
    // Get uploader's public key
    $stmt = $db->prepare("SELECT public_key FROM user_keys WHERE user_id = ?");
    $stmt->execute([$file['uploaded_by_user_id']]);
    $publicKey = $stmt->fetchColumn();
    
    if (!$publicKey) {
        die('Uploader's public key not found.');
    }
    
    // Read encrypted file data from filesystem
    $encryptedData = file_get_contents($file['file_path']);
    
    if ($encryptedData === false) {
        die('Failed to read encrypted file from filesystem.');
    }
    
    // Initialize crypto handler (we need the downloader's private key for decryption)
    $cryptoHandler = new CryptoHandler($db, $_SESSION['user_id']);
    
    // Decrypt file content using the stored encrypted_key and iv
    $decryptedData = $cryptoHandler->decryptFile(
        $encryptedData, 
        $file['encrypted_key'], // Retrieve encrypted_key from table
        $file['iv'] // Retrieve iv from table
    );
    
    if ($decryptedData === false) {
         die('File decryption failed.');
    }
    
    // Verify digital signature against the hash of the decrypted data
    $isValidSignature = $cryptoHandler->verifySignature(
        $decryptedData, // Hash of decrypted data will be generated inside verifySignature
        $file['digital_signature'],
        $publicKey
    );
    
    // Verify file integrity by comparing hash of decrypted data with stored hash
    $decryptedFileHash = hash('sha256', $decryptedData);
    $isHashMatching = ($decryptedFileHash === $file['sha256_hash']);
    
    if (!$isValidSignature || !$isHashMatching) {
        die('File verification failed - file may have been tampered with.');
    }
    
    // Set headers for download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file['filename'] . '"');
    header('Content-Length: ' . strlen($decryptedData));
    
    // Output decrypted file content
    echo $decryptedData;
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
} 