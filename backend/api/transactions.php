<?php
// backend/api/transactions.php
require_once '../includes/auth_check.php';
// We need to suppress the redirect in auth_check if it's an API call, 
// or manually handle it. For now, assume auth_check.php might need editing if it redirects.
// If auth_check.php redirects, this script will exit early with a 302.
// Client (fetch) might follow redirect or get opaque response.
// Better to ensure auth_check doesn't redirect for API.
// For now, let's assume valid session or we modify auth_check.

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// GET: List transactions or specific one
if ($method === 'GET') {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    
    $search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
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
    
    $sql .= " ORDER BY t.transaction_date DESC, t.id DESC LIMIT $limit";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $transactions]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// POST: Create
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $amount = clean_input($data['amount'] ?? '');
    $description = clean_input($data['description'] ?? '');
    $category_id = clean_input($data['category_id'] ?? '');
    $date = clean_input($data['date'] ?? date('Y-m-d'));

    if (empty($amount) || !is_numeric($amount)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid amount']);
        exit();
    }
    
    try {
        $sql = "INSERT INTO transactions (user_id, category_id, amount, description, transaction_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $category_id, $amount, $description, $date]);
        echo json_encode(['success' => true, 'message' => 'Transaction added']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// DELETE
elseif ($method === 'DELETE') {
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
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// PUT: Update
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = isset($data['id']) ? (int)$data['id'] : 0;
    $amount = clean_input($data['amount'] ?? '');
    $description = clean_input($data['description'] ?? '');
    $category_id = clean_input($data['category_id'] ?? '');
    $date = clean_input($data['date'] ?? date('Y-m-d'));

    if ($id <= 0 || empty($amount) || !is_numeric($amount)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit();
    }

    try {
        $sql = "UPDATE transactions SET category_id = ?, amount = ?, description = ?, transaction_date = ? WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category_id, $amount, $description, $date, $id, $user_id]);
        echo json_encode(['success' => true, 'message' => 'Transaction updated']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
