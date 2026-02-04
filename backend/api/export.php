<?php
// backend/api/export.php
require_once '../includes/middleware.php';
// Middleware: starts session, sets headers, includes db, functions

$user_id = require_auth(); // Export is read-only, no CSRF

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

// Override JSON header for CSV
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
