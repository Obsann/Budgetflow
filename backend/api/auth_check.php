<?php
// backend/api/auth_check.php
require_once '../includes/middleware.php';
// Note: middleware.php starts session, includes functions.php (which has generate_csrf_token)

$authenticated = isset($_SESSION['user_id']);
$response = ['authenticated' => $authenticated];

if ($authenticated) {
    $response['csrf_token'] = generate_csrf_token();
}

echo json_encode($response);
?>
