<?php
// export.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Fetch all transactions for the user
$stmt = $pdo->prepare("
    SELECT t.transaction_date, c.name as category, t.description, t.amount 
    FROM transactions t 
    JOIN categories c ON t.category_id = c.id 
    WHERE t.user_id = ? 
    ORDER BY t.transaction_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="budgetflow_transactions_' . date('Y-m-d') . '.csv"');

// Create file pointer connected to output stream
$output = fopen('php://output', 'w');

// Output column headings
fputcsv($output, ['Date', 'Category', 'Description', 'Amount (ETB)']);

// Output data rows
foreach ($transactions as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>
