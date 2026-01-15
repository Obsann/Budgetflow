<?php
// api/report.php - API endpoint for financial reports
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

// Fetch Actual Spending (Transactions)
$sql = "SELECT c.id as cat_id, c.name as category_name, SUM(t.amount) as total_amount 
        FROM transactions t 
        JOIN categories c ON t.category_id = c.id 
        WHERE t.user_id = ? 
        GROUP BY c.id";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$spending_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Planned Allocation (Active & Removed)
$sql = "SELECT c.id as cat_id, c.name as category_name, 
        SUM(CASE WHEN ba.is_deleted = 0 THEN ba.amount ELSE 0 END) as active_planned,
        SUM(CASE WHEN ba.is_deleted = 1 THEN ba.amount ELSE 0 END) as removed_planned
        FROM budget_allocations ba 
        JOIN categories c ON ba.category_id = c.id 
        WHERE ba.user_id = ?
        GROUP BY c.id";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$allocation_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Merge Data
$report_data = [];

// Process Spending
foreach ($spending_data as $row) {
    if (!isset($report_data[$row['cat_id']])) {
        $report_data[$row['cat_id']] = [
            'cat_id' => $row['cat_id'],
            'category_name' => $row['category_name'],
            'spent' => 0, 
            'planned' => 0, 
            'removed' => 0
        ];
    }
    $report_data[$row['cat_id']]['spent'] += $row['total_amount'];
}

// Process Allocations
foreach ($allocation_data as $row) {
    if (!isset($report_data[$row['cat_id']])) {
        $report_data[$row['cat_id']] = [
            'cat_id' => $row['cat_id'],
            'category_name' => $row['category_name'],
            'spent' => 0, 
            'planned' => 0, 
            'removed' => 0
        ];
    }
    $report_data[$row['cat_id']]['planned'] += $row['active_planned'];
    $report_data[$row['cat_id']]['removed'] += $row['removed_planned'];
}

// Calculate grand total
$grand_total = 0;
foreach ($spending_data as $row) {
    $grand_total += $row['total_amount'];
}

$response['success'] = true;
$response['data'] = [
    'report' => array_values($report_data),
    'grand_total' => $grand_total
];

echo json_encode($response);
?>
