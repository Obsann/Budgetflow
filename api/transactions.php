<?php
// api/transactions.php - API endpoint for transactions CRUD
// Returns JSON response

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'data' => [], 'error' => ''];

// Check authentication
if (!isset($_SESSION['user_id'])) {
    $response['error'] = 'Not authenticated';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        $limit = 10;
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        // Get total count
        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ?");
        $count_stmt->execute([$user_id]);
        $total_records = $count_stmt->fetchColumn();
        $total_pages = ceil($total_records / $limit);
        
        // Fetch transactions
        $stmt = $pdo->prepare("
            SELECT t.*, c.name as category_name 
            FROM transactions t 
            JOIN categories c ON t.category_id = c.id 
            WHERE t.user_id = ? 
            ORDER BY t.transaction_date DESC, t.id DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(3, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response['success'] = true;
        $response['data'] = [
            'transactions' => $transactions,
            'page' => $page,
            'total_pages' => $total_pages,
            'total_records' => $total_records
        ];
        break;
        
    case 'get':
        $id = $_GET['id'] ?? '';
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($transaction) {
                $response['success'] = true;
                $response['data'] = $transaction;
            } else {
                $response['error'] = 'Transaction not found';
            }
        }
        break;
        
    case 'categories':
        $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $response['success'] = true;
        $response['data'] = $categories;
        break;
        
    case 'create':
        $input = json_decode(file_get_contents('php://input'), true);
        $amount = clean_input($input['amount'] ?? '');
        $description = clean_input($input['description'] ?? '');
        $category_id = clean_input($input['category_id'] ?? '');
        $date = clean_input($input['transaction_date'] ?? '');
        
        $errors = [];
        if (empty($amount) || !is_numeric($amount) || $amount <= 0) $errors['amount'] = "Enter a valid amount";
        if (empty($category_id)) $errors['category_id'] = "Select a category";
        if (empty($date)) $errors['date'] = "Select a date";
        
        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, category_id, amount, description, transaction_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $category_id, $amount, $description, $date]);
            $response['success'] = true;
            $response['message'] = 'Transaction added successfully!';
        } else {
            $response['errors'] = $errors;
        }
        break;
        
    case 'update':
        $id = $_GET['id'] ?? '';
        $input = json_decode(file_get_contents('php://input'), true);
        $amount = clean_input($input['amount'] ?? '');
        $description = clean_input($input['description'] ?? '');
        $category_id = clean_input($input['category_id'] ?? '');
        $date = clean_input($input['transaction_date'] ?? '');
        
        $errors = [];
        if (empty($amount) || !is_numeric($amount) || $amount <= 0) $errors['amount'] = "Valid amount required";
        if (empty($category_id)) $errors['category_id'] = "Category required";
        if (empty($date)) $errors['date'] = "Date required";
        
        if (empty($errors) && $id) {
            $stmt = $pdo->prepare("UPDATE transactions SET category_id = ?, amount = ?, description = ?, transaction_date = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$category_id, $amount, $description, $date, $id, $user_id]);
            $response['success'] = true;
            $response['message'] = 'Transaction updated!';
        } else {
            $response['errors'] = $errors;
        }
        break;
        
    case 'delete':
        $id = $_GET['id'] ?? '';
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $response['success'] = true;
        }
        break;
        
    case 'search':
        $cat_id = $_GET['category_id'] ?? '';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        
        $sql = "SELECT t.*, c.name as category_name FROM transactions t JOIN categories c ON t.category_id = c.id WHERE t.user_id = :uid";
        $params = [':uid' => $user_id];
        
        if (!empty($cat_id)) { $sql .= " AND t.category_id = :cat_id"; $params[':cat_id'] = $cat_id; }
        if (!empty($start_date)) { $sql .= " AND t.transaction_date >= :start_date"; $params[':start_date'] = $start_date; }
        if (!empty($end_date)) { $sql .= " AND t.transaction_date <= :end_date"; $params[':end_date'] = $end_date; }
        
        $sql .= " ORDER BY t.transaction_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response['success'] = true;
        $response['data'] = $results;
        break;
}

echo json_encode($response);
?>
