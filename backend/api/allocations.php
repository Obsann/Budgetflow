<?php
// backend/api/allocations.php
require_once '../includes/middleware.php'; 
// Middleware: starts session, sets headers, includes db, functions

$user_id = require_auth(); // Require authentication
require_csrf(); // Verify CSRF for POST/PUT/DELETE

$method = $_SERVER['REQUEST_METHOD'];

// POST: Add Allocation
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = clean_input($data['name'] ?? '');
    $amount = $data['amount'] ?? '';
    $category_id = clean_input($data['category_id'] ?? '');
    
    // Validation
    if (empty($name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Name is required']);
        exit();
    }
    if (!validate_amount($amount)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Amount must be a positive number']);
        exit();
    }
    
    $cat_val = !empty($category_id) ? $category_id : null;

    try {
        $stmt = $pdo->prepare("INSERT INTO budget_allocations (user_id, name, amount, category_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $name, $amount, $cat_val]);
        echo json_encode(['success' => true, 'message' => 'Allocation added']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
}

// PUT: Toggle Paid Status
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);

    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID required']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE budget_allocations SET is_paid = NOT is_paid WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        echo json_encode(['success' => true, 'message' => 'Status toggled']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
}

// DELETE: Soft Delete
elseif ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);

    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID required']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE budget_allocations SET is_deleted = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        echo json_encode(['success' => true, 'message' => 'Allocation removed']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
}
?>
