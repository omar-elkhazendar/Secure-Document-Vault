<?php
session_start();
require_once 'classes/Database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$db = new Database();

try {
    // Get all users with their details
    $users = $db->query(
        "SELECT u.username, u.email, r.role_name, 
                (SELECT COUNT(*) FROM documents WHERE uploaded_by = u.id) as document_count,
                (SELECT MAX(created_at) FROM system_logs WHERE user_id = u.id) as last_activity,
                u.created_at
         FROM users u 
         LEFT JOIN roles r ON u.role_id = r.role_id 
         ORDER BY u.created_at DESC"
    )->fetchAll();

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d') . '.csv"');

    // Create output stream
    $output = fopen('php://output', 'w');

    // Add UTF-8 BOM for proper Excel encoding
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Add CSV headers
    fputcsv($output, [
        'Username',
        'Email',
        'Role',
        'Documents',
        'Last Activity',
        'Created At'
    ]);

    // Add user data
    foreach ($users as $user) {
        fputcsv($output, [
            $user['username'],
            $user['email'],
            $user['role_name'] ?? 'No Role',
            $user['document_count'] ?? 0,
            $user['last_activity'] ? date('Y-m-d H:i:s', strtotime($user['last_activity'])) : 'Never',
            date('Y-m-d H:i:s', strtotime($user['created_at']))
        ]);
    }

    fclose($output);
} catch (Exception $e) {
    // Log the error
    error_log("Export error: " . $e->getMessage());
    
    // Redirect back with error message
    $_SESSION['error'] = 'Error exporting users: ' . $e->getMessage();
    header('Location: admin_dashboard.php');
    exit();
} 