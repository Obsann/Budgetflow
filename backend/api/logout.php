<?php
// backend/api/logout.php
require_once '../includes/middleware.php';
// Middleware: starts session, sets headers

session_unset();
session_destroy();

echo json_encode(['success' => true, 'message' => 'Logged out']);
?>
