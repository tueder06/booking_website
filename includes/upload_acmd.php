<?php
require_once __DIR__ . '/init.php';

$sql = "SELECT 
            id, 
            name, 
            image_path AS image, 
            rating, 
            city, 
            distance, 
            room_type AS roomType, 
            price 
        FROM accomodations 
        ORDER BY id";

$db_locations = [];

if ($conn instanceof PDO) {
    $stmt = $conn->query($sql);
    $db_locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($conn instanceof mysqli) {
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $db_locations[] = $row;
    }
}

$json_locations = json_encode($db_locations);
?>