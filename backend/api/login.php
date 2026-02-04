<?php
// backend/api/login.php
require_once '../includes/middleware.php'; 
// Note: middleware starts session, sets headers, includes db.php and functions.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$username = clean_input($data['username'] ?? '');
$password = $data['password'] ?? ''; // Don't clean password, just verify

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => true, 
        'message' => 'Already logged in',
        'csrf_token' => generate_csrf_token() 
    ]);
    exit();
}

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Username and Password are required']);
    exit();
}

$ip_address = $_SERVER['REMOTE_ADDR'];
$block_time_minutes = 15;
$max_attempts = 5;

// 1. Check Rate Limit
$stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > (NOW() - INTERVAL ? MINUTE)");
$stmt->execute([$ip_address, $block_time_minutes]);
$attempts = $stmt->fetchColumn();

if ($attempts >= $max_attempts) {
    http_response_code(429); // Too Many Requests
    echo json_encode(['success' => false, 'error' => 'Too many failed attempts. Please try again in 15 minutes.']);
    exit();
}

// 2. Authenticate
try {
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Success: Clear Attempts
        $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
        $stmt->execute([$ip_address]);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'csrf_token' => generate_csrf_token() // Return token for frontend
        ]);
    } else {
        // Failure: Log Attempt
        $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address) VALUES (?)");
        $stmt->execute([$ip_address]);

        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
?>
