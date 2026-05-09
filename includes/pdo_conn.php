<?php
$config = parse_ini_file(__DIR__ . '/../config.ini');

if ($config === false) {
    die("Error: Cannot read from the configuration file.");
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

} catch(PDOException $e) {
    die("Error connecting using PDO: " . $e->getMessage());
}
?>