<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $allowed_roles = ['student', 'faculty'];
    $role = $_POST['role'];
    
    if (in_array($role, $allowed_roles)) {
        $_SESSION['selected_role'] = $role;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'Invalid role selected'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
}
?>