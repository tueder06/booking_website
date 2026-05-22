<?php
require_once __DIR__ . '/init.php';

ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired.']);
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

$res_id = isset($data['id']) ? (int)$data['id'] : 0;
$user_id = (int)$_SESSION['user_id'];
$check_in = $data['check_in'] ?? '';
$check_out = $data['check_out'] ?? '';
$is_cancelled = isset($data['cancel']) ? (bool)$data['cancel'] : false;
$status = $data['new_status'] ?? '';

if (empty($check_in) || empty($check_out)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Both Check-in and Check-out dates are required.']);
    exit;
}
if ($status !== "Confirmed" && $status !== "Cancelled") {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status.']);
    exit;
}

$today = new DateTime('today');
$check_in_date = new DateTime($check_in ?? '');
$check_out_date = new DateTime($check_out ?? '');
if ($check_out_date <= $check_in_date) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Check-out date must be after Check-in date.']);
    exit;
}
if ($check_in_date < $today) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cannot make a reservation in the past.']);
    exit;
}

try {
    if ($is_cancelled) {
        $sql = "UPDATE reservations SET status = ?, check_in = ?, check_out = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$status, $check_in, $check_out, $res_id, $user_id]);
    } else {
        $sql = "UPDATE reservations SET check_in = ?, check_out = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$check_in, $check_out, $res_id, $user_id]);
    }

    echo json_encode(['success' => true, 'message' => 'Changes have been made successfully.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again later.']);
}