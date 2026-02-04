<?php
// backend/includes/middleware.php

// 1. Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// 2. Set Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // In production, restrict this
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

// Handle Preflight Options request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Middleware: Check Authentication
 */
function require_auth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit();
    }
    return $_SESSION['user_id'];
}

/**
 * Middleware: Verify CSRF for mutating requests
 */
function require_csrf() {
    $method = $_SERVER['REQUEST_METHOD'];
    if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
        $headers = apache_request_headers();
        $token = isset($headers['X-CSRF-Token']) ? $headers['X-CSRF-Token'] : 
                 (isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '');
        
        // Fallback for non-Apache servers if apache_request_headers is missing
        if (empty($token) && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        if (!verify_csrf_token($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF Token']);
            exit();
        }
    }
}
?>
