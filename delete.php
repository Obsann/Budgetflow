<?php
// delete.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Validate it's a number
    if (!filter_var($id, FILTER_VALIDATE_INT)) {
        header("Location: view_all.php");
        exit();
    }

    try {
        // Only allow deleting own transactions
        $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
    } catch (PDOException $e) {
        // Handle error silently or log it
    }
}

header("Location: view_all.php");
exit();
?>
