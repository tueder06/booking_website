<?php
require_once __DIR__ . '/init_logs.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '', 
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    require_once __DIR__ . '/pdo_conn.php';
    require_once __DIR__ . '/remember_me.php';

    if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
        $user_data = validateRememberMe($conn);

        if ($user_data) {
            session_regenerate_id(true); 
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['first_name'] = $user_data['first_name'];
            $_SESSION['role'] = $user_data['role'];
        } else {
            clearRememberMe($conn);
        }
    }
} catch (Exception $e) {
    exit("An error occured. Please try again later.");
}
?>