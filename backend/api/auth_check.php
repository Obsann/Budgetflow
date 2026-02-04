<?php
// backend/api/auth_check.php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Credentials: true');

$authenticated = isset($_SESSION['user_id']);
echo json_encode(['authenticated' => $authenticated]);
?>
