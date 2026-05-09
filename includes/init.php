<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/pdo_conn.php';
require_once __DIR__ . '/remember_me.php';

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    
    $user_data = validateRememberMe($conn);

    if ($user_data) {
        $_SESSION['user_id'] = $user_data['id'];
        $_SESSION['first_name'] = $user_data['first_name'];
        $_SESSION['role'] = $user_data['role'];
    } else {
        clearRememberMe($conn);
    }
}
?>