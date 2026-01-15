<?php
// includes/db.php
// Database connection using PDO

$host = 'localhost';
$db   = 'budgetflow_db'; // Ensure this database exists or change to your local DB name
$user = 'root';        // Default XAMPP/WAMP user
$pass = '';            // Default XAMPP/WAMP password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // In a real app, log error and show user-friendly message
    // For development, we might want to see what's wrong
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
