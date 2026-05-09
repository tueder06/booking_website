<?php
$config = parse_ini_file(__DIR__ . '/../config.ini');

if ($config === false) {
    die("Error: Cannot read from the configuration file.");
}

$host = $config['db_host'];
$db_name = $config['db_name'];
$username = $config['db_user'];
$password = $config['db_pass'];

$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    die("Error connecting using MySQLi: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>