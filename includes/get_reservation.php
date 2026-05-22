<?php
require_once __DIR__ . '/init.php';

ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Session expired.']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$res_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($res_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID invalid.']);
    exit;
}

try {
    $sql = "SELECT r.id, r.check_in, r.check_out, r.status, 
                   a.name AS hotel_name, a.city, a.image_path AS image, a.price
            FROM reservations r
            JOIN accomodations a ON r.accommodation_id = a.id
            WHERE r.id = ? AND r.user_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([$res_id, $user_id]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($trip) {
        $date1 = new DateTime($trip['check_in']);
        $date2 = new DateTime($trip['check_out']);
        $nights = $date1->diff($date2)->days;
        if ($nights == 0) $nights = 1;
        
        $trip['total_price'] = $nights * $trip['price'];

        echo json_encode(['success' => true, 'trip' => $trip]);
    } else {
        echo json_encode(['success' => true, 'message' => 'Reservation not found.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again later.']);
}