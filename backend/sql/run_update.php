<?php
// backend/sql/run_update.php
// Script to apply schema updates

require_once __DIR__ . '/../includes/db.php';

try {
    $sql = file_get_contents(__DIR__ . '/schema_update.sql');
    $pdo->exec($sql);
    echo "Schema update applied successfully (login_attempts table created).";
} catch (PDOException $e) {
    echo "Error applying schema update: " . $e->getMessage();
}
?>
