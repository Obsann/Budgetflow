<?php
// api/login.php - API endpoint for user authentication
// Returns JSON response

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';
require_once '../includes/functions.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$response = ['success' => false, 'error' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = clean_input($input['username'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($username) || empty($password)) {
        $response['error'] = "Please enter both username and password";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $response['success'] = true;
        } else {
            $response['error'] = "Invalid username or password";
        }
    }
} else {
    $response['error'] = "Invalid request method";
}

echo json_encode($response);
?>
