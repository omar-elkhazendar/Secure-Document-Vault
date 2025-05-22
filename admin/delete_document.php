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

$database = new Database();
$db = $database->getConnection();
$document = new Document($db, $_SESSION['user_id']);

// Verify admin role
$role = new Role($database);
$user_role = $role->getUserRole($_SESSION['user_id']);
if (!$user_role || $user_role['role_name'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit();
}

if (isset($_GET['id'])) {
    try {
        $db->beginTransaction();

        $document_id = $_GET['id'];

        // First, get the document information
        $stmt = $db->prepare("SELECT * FROM documents WHERE document_id = ?");
        $stmt->execute([$document_id]);
        $document_info = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($document_info) {
            // Delete the physical file if it exists
            $file_path = __DIR__ . '/../uploads/' . $document_info['file_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // First, delete all signatures for this document
            $stmt = $db->prepare("DELETE FROM document_signatures WHERE document_id = ?");
            if (!$stmt->execute([$document_id])) {
                throw new Exception("Failed to delete document signatures");
            }

            // Then delete document versions
            $stmt = $db->prepare("DELETE FROM document_versions WHERE document_id = ?");
            if (!$stmt->execute([$document_id])) {
                throw new Exception("Failed to delete document versions");
            }

            // Then delete document shares
            $stmt = $db->prepare("DELETE FROM document_shares WHERE document_id = ?");
            if (!$stmt->execute([$document_id])) {
                throw new Exception("Failed to delete document shares");
            }

            // Finally delete the document record
            $stmt = $db->prepare("DELETE FROM documents WHERE document_id = ?");
            if (!$stmt->execute([$document_id])) {
                throw new Exception("Failed to delete document record");
            }

            // Log the deletion
            $stmt = $db->prepare("
                INSERT INTO system_logs (user_id, action, details, ip_address) 
                VALUES (?, 'delete', ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                "Deleted document: " . $document_info['title'],
                $_SERVER['REMOTE_ADDR']
            ]);

            $db->commit();
            $_SESSION['success'] = 'Document deleted successfully';
        } else {
            throw new Exception('Document not found');
        }
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = 'Failed to delete document: ' . $e->getMessage();
        error_log("Document deletion error: " . $e->getMessage());
    }
} else {
    $_SESSION['error'] = 'Document ID not provided';
}

header('Location: dashboard.php');
exit(); 