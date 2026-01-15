<?php
// api/auth_check.php - Check if user is authenticated
// Returns JSON response

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['authenticated' => false, 'username' => ''];

if (isset($_SESSION['user_id'])) {
    $response['authenticated'] = true;
    $response['username'] = $_SESSION['username'] ?? '';
}

echo json_encode($response);
?>
