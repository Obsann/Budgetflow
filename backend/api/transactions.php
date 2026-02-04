<?php
// backend/api/transactions.php
require_once '../includes/middleware.php';
// Middleware: starts session, sets headers, includes db, functions

$user_id = require_auth(); // Require authentication

$method = $_SERVER['REQUEST_METHOD'];

// GET: List transactions (No CSRF needed for read-only)
if ($method === 'GET') {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    
    $search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
    $category_id = isset($_GET['category_id']) ? clean_input($_GET['category_id']) : '';
    $start_date = isset($_GET['start_date']) ? clean_input($_GET['start_date']) : '';
    $end_date = isset($_GET['end_date']) ? clean_input($_GET['end_date']) : '';
    
    $sql = "
        SELECT t.*, c.name as category_name 
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ?
    ";
    
    $params = [$user_id];
    if ($search) {
        $sql .= " AND (t.description LIKE ? OR c.name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($category_id) {
        $sql .= " AND t.category_id = ?";
        $params[] = $category_id;
    }
    if ($start_date && validate_date($start_date)) {
        $sql .= " AND t.transaction_date >= ?";
        $params[] = $start_date;
    }
    if ($end_date && validate_date($end_date)) {
        $sql .= " AND t.transaction_date <= ?";
        $params[] = $end_date;
    }
    
    $sql .= " ORDER BY t.transaction_date DESC, t.id DESC LIMIT $limit";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $transactions]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
}

// POST: Create (CSRF Required)
elseif ($method === 'POST') {
    require_csrf();
    $data = json_decode(file_get_contents('php://input'), true);
    
    $amount = $data['amount'] ?? '';
    $description = clean_input($data['description'] ?? '');
    $category_id = (int)($data['category_id'] ?? 0);
    $date = clean_input($data['date'] ?? date('Y-m-d'));

    // Validation
    if (!validate_amount($amount)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Amount must be a positive number']);
        exit();
    }
    if (!validate_date($date)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid date format']);
        exit();
    }
    
    try {
        $sql = "INSERT INTO transactions (user_id, category_id, amount, description, transaction_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $category_id, $amount, $description, $date]);
        echo json_encode(['success' => true, 'message' => 'Transaction added']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
}

// DELETE (CSRF Required)
elseif ($method === 'DELETE') {
    require_csrf();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        echo json_encode(['success' => true, 'message' => 'Transaction deleted']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
}

// PUT: Update (CSRF Required)
elseif ($method === 'PUT') {
    require_csrf();
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = isset($data['id']) ? (int)$data['id'] : 0;
    $amount = $data['amount'] ?? '';
    $description = clean_input($data['description'] ?? '');
    $category_id = (int)($data['category_id'] ?? 0);
    $date = clean_input($data['date'] ?? date('Y-m-d'));

    // Validation
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
        exit();
    }
    if (!validate_amount($amount)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Amount must be a positive number']);
        exit();
    }

    try {
        $sql = "UPDATE transactions SET category_id = ?, amount = ?, description = ?, transaction_date = ? WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category_id, $amount, $description, $date, $id, $user_id]);
        echo json_encode(['success' => true, 'message' => 'Transaction updated']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
}
?>
