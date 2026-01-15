<?php
// api/logout.php - API endpoint for logout
// Returns JSON response

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

session_unset();
session_destroy();

echo json_encode(['success' => true]);
?>
