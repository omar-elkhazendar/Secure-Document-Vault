<?php
session_start();
require_once 'classes/Database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'User ID is required']);
    exit();
}

$db = new Database();
$user_id = $_GET['id'];

try {
    // Get user details
    $user = $db->query(
        "SELECT u.*, r.role_name,
                (SELECT COUNT(*) FROM documents WHERE uploaded_by = u.id) as document_count,
                (SELECT MAX(created_at) FROM system_logs WHERE user_id = u.id) as last_activity
         FROM users u 
         LEFT JOIN roles r ON u.role_id = r.role_id 
         WHERE u.id = :user_id",
        ['user_id' => $user_id]
    )->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit();
    }

    // Get recent activity
    $recent_activity = $db->query(
        "SELECT action, details, created_at 
         FROM system_logs 
         WHERE user_id = :user_id 
         ORDER BY created_at DESC 
         LIMIT 5",
        ['user_id' => $user_id]
    )->fetchAll();

    // Format the response
    $response = [
        'username' => $user['username'],
        'email' => $user['email'],
        'role_name' => $user['role_name'],
        'document_count' => $user['document_count'],
        'last_activity' => $user['last_activity'] ? date('M d, Y H:i', strtotime($user['last_activity'])) : 'Never',
        'recent_activity' => array_map(function($activity) {
            return [
                'action' => $activity['action'],
                'details' => $activity['details'],
                'created_at' => date('M d, Y H:i', strtotime($activity['created_at']))
            ];
        }, $recent_activity)
    ];

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
} 