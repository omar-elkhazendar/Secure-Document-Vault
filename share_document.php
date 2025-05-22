<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Document.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit();
}

// Get and validate input
$document_id = isset($_POST['document_id']) ? intval($_POST['document_id']) : 0;
$email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) : '';
$permission_level = 'read'; // Force read-only permission

// Validate Gmail address
if (!$document_id || !$email || !preg_match('/@gmail\.com$/', $email)) {
    $_SESSION['error'] = 'Please enter a valid Gmail address';
    header('Location: dashboard.php');
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $document = new Document($db, $_SESSION['user_id']);

    // Get user ID from email
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }

    // Share the document
    $stmt = $db->prepare("INSERT INTO document_shares (document_id, user_id, permission_level) VALUES (?, ?, ?)");
    if ($stmt->execute([$document_id, $user['id'], $permission_level])) {
        $_SESSION['success'] = 'Document shared successfully';
    } else {
        throw new Exception('Failed to share document');
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: dashboard.php');
exit(); 