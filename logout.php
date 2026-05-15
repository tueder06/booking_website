<?php
require_once 'includes/init.php';
require_once __DIR__ . '/includes/remember_me.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header("Location: index.php");
    exit;
}

if (isset($conn)) {
    clearRememberMe($conn);
}

$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $params["path"],
        'domain'   => $params["domain"],
        'secure'   => $params["secure"],
        'httponly' => $params["httponly"],
        'samesite' => $params["samesite"] ?? 'Strict'
    ]);
}

session_destroy();

header("Location: index.php");
exit;
?>