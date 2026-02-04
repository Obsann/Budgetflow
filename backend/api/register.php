<?php
// backend/api/register.php
require_once '../includes/middleware.php';
// Middleware: starts session, sets headers, includes db, functions
// Note: Register is a public endpoint, no auth required

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

$username = clean_input($data['username'] ?? '');
$password = $data['password'] ?? '';
$confirm_password = $data['confirm_password'] ?? '';

$errors = [];

// Validation
if (empty($username)) $errors[] = "Username is required";
if (strlen($username) < 3) $errors[] = "Username must be at least 3 characters";
if (empty($password)) $errors[] = "Password is required";
if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
if ($password !== $confirm_password) $errors[] = "Passwords do not match";

if (empty($errors)) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $errors[] = "Username already taken";
    }
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => implode(', ', $errors)]);
    exit();
}

try {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $hashed_password]);
    echo json_encode(['success' => true, 'message' => 'Registration successful']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
?>
