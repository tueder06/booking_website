<?php
require_once __DIR__ . '/init.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to make a reservation.']);
    exit;
}

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

$client_csrf = isset($data['csrf_token']) ? trim((string)$data['csrf_token']) : '';
$session_csrf = isset($_SESSION['csrf_token']) ? trim((string)$_SESSION['csrf_token']) : '';
if (empty($client_csrf) || !hash_equals($session_csrf, $client_csrf)) {
    http_response_code(403);
    echo json_encode([
        'success' => false, 
        'message' => 'Session expired or action could not be validated. Please retry.'
    ]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$acc_id = isset($data['accommodation_id']) ? (int)$data['accommodation_id'] : 0;
$check_in = trim($data['check_in'] ?? '');
$check_out = trim($data['check_out'] ?? '');

if ($acc_id <= 0 || empty($check_in) || empty($check_out)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please provide valid dates and a selected accommodation.']);
    exit;
}

$checkin_time = strtotime($check_in);
$checkout_time = strtotime($check_out);
$today_time = strtotime(date('Y-m-d'));

if ($checkout_time <= $checkin_time) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Check-out date must be strictly after the check-in date.']);
    exit;
}

if ($checkin_time < $today_time) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'You cannot make a reservation in the past.']);
    exit;
}

try {
    $check_acc_query = "SELECT id FROM accomodations WHERE id = ?";
    $stmt_check = $conn->prepare($check_acc_query);
    $stmt_check->execute([$acc_id]);
    
    if ($stmt_check->rowCount() === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'The selected accommodation does not exist.']);
        exit;
    }

    $insert_query = "INSERT INTO reservations (accommodation_id, user_id, check_in, check_out, status) 
                     VALUES (?, ?, ?, ?, 'Confirmed')";
              
    $stmt_insert = $conn->prepare($insert_query);
    $stmt_insert->execute([$acc_id, $user_id, $check_in, $check_out]);

    http_response_code(200);
    echo json_encode([
        'success' => true, 
        'message' => 'Your trip has been booked successfully!'
    ]);

} catch (PDOException $e) {
    error_log("Reservation Insert Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'A database error occurred while saving your reservation. Please try again later.'
    ]);
}