<?php
// backend/api/export.php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$user_id = $_SESSION['user_id'];

// Fetch transactions
$stmt = $pdo->prepare("
    SELECT t.transaction_date, c.name as category, t.description, t.amount 
    FROM transactions t 
    LEFT JOIN categories c ON t.category_id = c.id 
    WHERE t.user_id = ? 
    ORDER BY t.transaction_date DESC
");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Headers for download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="budgetflow_transactions_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Date', 'Category', 'Description', 'Amount (ETB)']);

foreach ($transactions as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>
