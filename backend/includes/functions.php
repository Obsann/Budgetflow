<?php
// includes/functions.php

/**
 * Sanitize input data
 */
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Redirect to a specific page
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Display specific error message if it exists in the errors array
 */
function display_error($errors, $field) {
    if (isset($errors[$field])) {
        return '<span class="error-msg">' . $errors[$field] . '</span>';
    }
    return '';
}
/**
 * Generate CSRF Token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Validate Email
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate Amount
 */
function validate_amount($amount) {
    return is_numeric($amount) && $amount >= 0;
}

/**
 * Validate Date
 */
function validate_date($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}
?>
