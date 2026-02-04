<?php
// backend/api/dashboard.php
require_once '../includes/auth_check.php'; // Ensure this returns JSON error if not auth
// wait, auth_check.php redirects. We need to modify it or handle it.
// Actually, let's just check session here for now or update auth_check.php later.
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

$user_id = $_SESSION['user_id'];

// 1. Fetch Categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// 2. Fetch Allocations
$stmt = $pdo->prepare("
    SELECT ba.*, c.name as category_name 
    FROM budget_allocations ba
    LEFT JOIN categories c ON ba.category_id = c.id
    WHERE ba.user_id = ? AND ba.is_deleted = 0 
    ORDER BY ba.created_at DESC
");
$stmt->execute([$user_id]);
$allocations = $stmt->fetchAll();

// 3. Total Allocation
$stmt = $pdo->prepare("SELECT SUM(amount) FROM budget_allocations WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_allocation = $stmt->fetchColumn() ?: 0;

// 4. Total Income
$stmt = $pdo->prepare("
    SELECT SUM(t.amount) 
    FROM transactions t 
    JOIN categories c ON t.category_id = c.id 
    WHERE t.user_id = ? AND c.name = 'Income'
");
$stmt->execute([$user_id]);
$total_income = $stmt->fetchColumn() ?: 0;

// 5. Chart Data
$stmt = $pdo->prepare("
    SELECT c.name, SUM(t.amount) as total
    FROM transactions t
    JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = ? AND c.name != 'Income'
    GROUP BY c.name
");
$stmt->execute([$user_id]);
$chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'data' => [
        'income' => $total_income,
        'allocation_total' => $total_allocation,
        'allocations' => $allocations,
        'chart_data' => $chart_data,
        'categories' => $categories
    ]
]);
?>
