<?php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Document.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Get document ID from URL
$document_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($document_id <= 0) {
    die('Invalid document ID');
}

try {
    // Initialize database and document handler
    $db = new Database();
    $document = new Document($db);
    
    // Download document
    $document->downloadDocument($document_id, $_SESSION['user_id']);
} catch (Exception $e) {
    // Log the error
    error_log("Document download error: " . $e->getMessage());
    die('Error downloading document: ' . $e->getMessage());
} 