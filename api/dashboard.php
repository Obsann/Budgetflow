<?php
// api/dashboard.php - API endpoint for dashboard data
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

// Handle different actions
$action = $_GET['action'] ?? 'get';

switch ($action) {
    case 'get':
        // Fetch Categories
        $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch Allocations (Only active ones)
        $stmt = $pdo->prepare("
            SELECT ba.*, c.name as category_name 
            FROM budget_allocations ba
            LEFT JOIN categories c ON ba.category_id = c.id
            WHERE ba.user_id = ? AND ba.is_deleted = 0 
            ORDER BY ba.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $allocations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate Total Allocation
        $stmt = $pdo->prepare("SELECT SUM(amount) FROM budget_allocations WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $total_allocation = $stmt->fetchColumn() ?: 0;
        
        // Fetch Total Income
        $stmt = $pdo->prepare("
            SELECT SUM(t.amount) 
            FROM transactions t 
            JOIN categories c ON t.category_id = c.id 
            WHERE t.user_id = ? AND c.name = 'Income'
        ");
        $stmt->execute([$user_id]);
        $total_income = $stmt->fetchColumn() ?: 0;
        
        $response['success'] = true;
        $response['data'] = [
            'categories' => $categories,
            'allocations' => $allocations,
            'total_allocation' => $total_allocation,
            'total_income' => $total_income
        ];
        break;
        
    case 'add':
        $input = json_decode(file_get_contents('php://input'), true);
        $name = clean_input($input['name'] ?? '');
        $amount = clean_input($input['amount'] ?? '');
        $category_id = clean_input($input['category_id'] ?? '');
        
        if (!empty($name) && is_numeric($amount)) {
            $cat_val = !empty($category_id) ? $category_id : null;
            $stmt = $pdo->prepare("INSERT INTO budget_allocations (user_id, name, amount, category_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $amount, $cat_val]);
            $response['success'] = true;
        } else {
            $response['error'] = 'Invalid input';
        }
        break;
        
    case 'toggle':
        $id = $_GET['id'] ?? '';
        if ($id) {
            $stmt = $pdo->prepare("UPDATE budget_allocations SET is_paid = NOT is_paid WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $response['success'] = true;
        }
        break;
        
    case 'delete':
        $id = $_GET['id'] ?? '';
        if ($id) {
            $stmt = $pdo->prepare("UPDATE budget_allocations SET is_deleted = 1 WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $response['success'] = true;
        }
        break;
}

echo json_encode($response);
?>
