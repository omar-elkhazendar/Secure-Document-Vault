<?php
session_start();
require_once '../classes/Database.php';
require_once '../classes/User.php';
require_once '../classes/Role.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$role = new Role($database);

// Verify admin role
$user_role = $role->getUserRole($_SESSION['user_id']);
if (!$user_role || $user_role['role_name'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit();
}

// Check if user ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'User ID is required';
    header('Location: dashboard.php');
    exit();
}

$user_id = $_GET['id'];
$user = new User();

try {
    // Start transaction
    $db->beginTransaction();

    // Delete user's documents first
    $stmt = $db->prepare("DELETE FROM documents WHERE uploaded_by = ?");
    $stmt->execute([$user_id]);

    // Delete user's document shares
    $stmt = $db->prepare("DELETE FROM document_shares WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Delete user's document signatures
    $stmt = $db->prepare("DELETE FROM document_signatures WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Delete user's keys
    $stmt = $db->prepare("DELETE FROM user_keys WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Delete user's system logs
    $stmt = $db->prepare("DELETE FROM system_logs WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Delete the user
    if ($user->delete($user_id)) {
        // Log the deletion
        $stmt = $db->prepare("
            INSERT INTO system_logs (user_id, action, details, ip_address) 
            VALUES (?, 'delete_user', ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            "Deleted user ID: " . $user_id,
            $_SERVER['REMOTE_ADDR']
        ]);

        $db->commit();
        $_SESSION['success'] = 'User deleted successfully';
    } else {
        throw new Exception('Failed to delete user');
    }
} catch (Exception $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $_SESSION['error'] = $e->getMessage();
}

header('Location: dashboard.php');
exit(); 