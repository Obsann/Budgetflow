<?php
// includes/auth_check.php
// Include this at the top of any page that requires a user to be logged in

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
