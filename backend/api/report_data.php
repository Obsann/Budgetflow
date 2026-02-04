<?php
// backend/api/report_data.php
require_once '../includes/auth_check.php';
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

try {
    // 1. Fetch Actual Spending (Transactions)
    $sql = "SELECT c.id as cat_id, c.name as category_name, SUM(t.amount) as total_amount 
            FROM transactions t 
            JOIN categories c ON t.category_id = c.id 
            WHERE t.user_id = ? 
            GROUP BY c.id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $spending_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Fetch Planned Allocation (Active & Removed)
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

    // 3. Merge Data
    $report_data = [];
    $cat_names = [];
    $grand_total = 0;

    foreach ($spending_data as $row) {
        $cat_id = $row['cat_id'];
        if (!isset($report_data[$cat_id])) {
            $report_data[$cat_id] = ['cat_name' => $row['category_name'], 'spent' => 0, 'planned' => 0, 'removed' => 0];
        }
        $report_data[$cat_id]['spent'] += $row['total_amount'];
        $grand_total += $row['total_amount'];
    }

    foreach ($allocation_data as $row) {
        $cat_id = $row['cat_id'];
        if (!isset($report_data[$cat_id])) {
            $report_data[$cat_id] = ['cat_name' => $row['category_name'], 'spent' => 0, 'planned' => 0, 'removed' => 0];
        }
        $report_data[$cat_id]['planned'] += $row['active_planned'];
        $report_data[$cat_id]['removed'] += $row['removed_planned'];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'report' => array_values($report_data), // Convert to array list
            'grand_total' => $grand_total
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
