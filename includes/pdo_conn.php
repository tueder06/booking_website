<?php
require_once __DIR__ . '/init_logs.php';

$config = parse_ini_file(__DIR__ . '/../config.ini');

if ($config === false) {
    error_log("Error: Cannot read from the configuration file.");
    exit("Server error.");
}

$dsn = $config['db_dsn'];
$username = $config['db_user'];
$password = $config['db_pass'];

$realRoot = dirname(__DIR__);
$dsn = str_replace('{{ROOT}}', $realRoot, $dsn);

try {
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    error_log("Error connecting using PDO: " . $e->getMessage());
    exit("Our travel agents are busy fixing the server. Please come back later.");
}
?>