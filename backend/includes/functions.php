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
?>
