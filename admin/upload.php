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

$db = new Database();
$role = new Role($db);

// Get current user's role
$user_role = $role->getUserRole($_SESSION['user_id']);
if (!$user_role || $user_role['role_name'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit();
}

// Handle document upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Please select a file to upload');
        }

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $share_with_all = isset($_POST['share_with_all']);
        
        if (empty($title)) {
            throw new Exception('Document title is required');
        }

        $document = new Document($db->getConnection(), $_SESSION['user_id']);
        $document_id = $document->uploadDocument($title, $description, $_FILES['document'], $_SESSION['user_id'], $share_with_all);
        
        $_SESSION['success'] = 'Document uploaded successfully' . ($share_with_all ? ' and shared with all users' : '');
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

header('Location: dashboard.php');
exit(); 