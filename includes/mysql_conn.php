<?php
require_once __DIR__ . '/init_logs.php';

$config = parse_ini_file(__DIR__ . '/../config.ini');

if ($config === false) {
    error_log("Error: Cannot read from the configuration file.");
    exit("Server error.");
}

$host = $config['db_host'];
$db_name = $config['db_name'];
$username = $config['db_user'];
$password = $config['db_pass'];

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    error_log("MySQLi Connection Failed: " . $conn->connect_error);
    http_response_code(500);
    exit("Our travel agents are busy fixing the server. Please come back later.");
}
$conn->set_charset("utf8mb4");
?>