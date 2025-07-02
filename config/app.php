<?php
// Application settings
define('APP_NAME', 'EVA System');
define('APP_VERSION', '3.0');

// Password requirements
define('MIN_PASSWORD_LENGTH', 6);

// Session settings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirectWithMessage($location, $message, $type = 'error') {
    $_SESSION[$type . '_message'] = $message;
    header('Location: ' . $location);
    exit();
}

function validatePassword($password) {
    return strlen($password) >= MIN_PASSWORD_LENGTH && 
           preg_match("/[A-Z]/", $password) && 
           preg_match("/\d/", $password) && 
           preg_match("/[@$!%*?&]/", $password);
}

function generateToken() {
    return bin2hex(random_bytes(50));
}
?>