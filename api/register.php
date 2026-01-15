<?php
// api/register.php - API endpoint for user registration
// Returns JSON response

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';
require_once '../includes/functions.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$response = ['success' => false, 'errors' => [], 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = clean_input($input['username'] ?? '');
    $password = $input['password'] ?? '';
    $confirm_password = $input['confirm_password'] ?? '';

    // Validation
    if (empty($username)) {
        $response['errors']['username'] = "Username is required";
    }

    if (empty($password)) {
        $response['errors']['password'] = "Password is required";
    }

    if ($password !== $confirm_password) {
        $response['errors']['confirm'] = "Passwords do not match";
    }

    // Check if username already exists
    if (empty($response['errors'])) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $response['errors']['username'] = "Username already taken";
        }
    }

    // Insert new user
    if (empty($response['errors'])) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hashed_password]);
            $response['success'] = true;
            $response['message'] = "Registration successful! You can now login.";
        } catch (PDOException $e) {
            $response['errors']['general'] = "Error: " . $e->getMessage();
        }
    }
} else {
    $response['errors']['general'] = "Invalid request method";
}

echo json_encode($response);
?>
