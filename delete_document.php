<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Role.php';
require_once 'classes/Document.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$role = new Role($db);
$document = new Document($db);

// Get current user's role
$user_role = $role->getUserRole($_SESSION['user_id']);
if (!$user_role || $user_role['role_name'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Check if document ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'No document specified';
    header('Location: admin/dashboard.php');
    exit();
}

$document_id = $_GET['id'];

try {
    // Get document details for logging
    $stmt = $db->query("SELECT * FROM documents WHERE document_id = ?", [$document_id]);
    $doc = $stmt->fetch();

    if (!$doc) {
        throw new Exception('Document not found');
    }

    // Delete the document
    $db->query("DELETE FROM documents WHERE document_id = ?", [$document_id]);

    // Log the deletion
    $db->query(
        "INSERT INTO system_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)",
        [
            $_SESSION['user_id'],
            'delete_document',
            "Deleted document: {$doc['title']} (ID: {$document_id})",
            $_SERVER['REMOTE_ADDR']
        ]
    );

    $_SESSION['success'] = 'Document deleted successfully';
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: admin/dashboard.php');
exit(); 