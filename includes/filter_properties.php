<?php
require_once __DIR__ . '/init.php';

$errors = [];

$val_checkin = $_GET['check-in'] ?? '';
$val_checkout = $_GET['check-out'] ?? '';
$val_price = isset($_GET['price-slider']) ? (int)$_GET['price-slider'] : null;
$price = isset($_GET['price']) ? (int)$_GET['price'] : null;

if (!empty($val_checkin) && !empty($val_checkout)) {
    if (strtotime($val_checkout) < strtotime($val_checkin)) {
        $errors['checkout'] = "Check-out cannot be before Check-in.";
    }
    if (strtotime($val_checkin) < strtotime('today')) {
        $errors['checkin'] = "Check-in cannot be in the past.";
    }
} else if (!empty($val_checkin) || !empty($val_checkout)) {
    $errors['dates'] = "Please select both Check-in and Check-out dates.";
}

if ($val_price !== null) {
    if ($val_price < 0 || $val_price > 2000) {
        $errors['price'] = "Invalid price range (it must be in [0,2000]).";
    }
}
if ($price !== null) {
    if ($price < 0 || $price > 2000) {
        $errors['price'] = "Invalid price range (it must be in [0,2000]).";
    }
}

header('Content-Type: application/json; charset=utf-8');
// header('Content-Type: application/xml; charset=utf-8');

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please correct the filters applied.',
        'errors' => $errors
    ]);
    exit;
}
// if (!empty($errors)) {
//     http_response_code(400);
//     header('Content-Type: application/xml; charset=utf-8');

//     $xmlDoc = new DOMDocument('1.0', 'UTF-8');
//     $xmlDoc->formatOutput = true;

//     $root = $xmlDoc->createElement('response');
//     $xmlDoc->appendChild($root);

//     $root->appendChild($xmlDoc->createElement('success', 'false'));
//     $root->appendChild($xmlDoc->createElement('message', 'Please correct the filters applied.'));

//     $errorsNode = $xmlDoc->createElement('errors');
//     foreach ($errors as $field => $message) {
//         $errNode = $xmlDoc->createElement('error', htmlspecialchars($message));
//         $errNode->setAttribute('field', $field);
//         $errorsNode->appendChild($errNode);
//     }
//     $root->appendChild($errorsNode);

//     echo $xmlDoc->saveXML();
//     exit;
// }

try {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $k = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 3;
    $offset = ($page - 1) * $k;

    $sql_base = " FROM accomodations WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($_GET['search'])) {
        $sql_base .= " AND (city LIKE ?)";
        $types .= "s";
        $params[] = '%' . $_GET['search'] . '%';
    }

    if (!empty($_GET['price-slider'])) {
        $sql_base .= " AND price <= ?";
        $types .= "i";
        $params[] = $_GET['price-slider'];
    }

    if (!empty($_GET['property-star']) && $_GET['property-star'] > 0) {
        $sql_base .= " AND stars = ?";
        $types .= "i";
        $params[] = $_GET['property-star'];
    }

    if (!empty($_GET['catering'])) {
        $sql_base .= " AND meal_plan = ?";
        $types .= "s";
        $params[] = $_GET['catering'];
    }

    if (!empty($_GET['min_rating'])) {
        $sql_base .= " AND rating >= ?";
        $types .= "d";
        $params[] = $_GET['min_rating'];
    }

    if (!empty($_GET['property_type']) && is_array($_GET['property_type'])) {
        $placeholders = implode(',', array_fill(0, count($_GET['property_type']), '?'));
        $sql_base .= " AND property_type IN ($placeholders)";
        foreach ($_GET['property_type'] as $prop_type) {
            $types .= "s";
            $params[] = $prop_type;
        }
    }

    if (!empty($_GET['facilities']) && is_array($_GET['facilities'])) {
        foreach ($_GET['facilities'] as $fac) {
            $sql_base .= " AND facilities_string LIKE ?";
            $types .= "s";
            $params[] = '%' . $fac . '%';
        }
    }

    $sql_count = "SELECT COUNT(*)" . $sql_base;
    $totalRecords = 0;
    
    if ($conn instanceof PDO) {
        $stmt_count = $conn->prepare($sql_count);
        $stmt_count->execute($params);
        $totalRecords = (int)$stmt_count->fetchColumn();
    }

    $sql_data = "SELECT id, name, image_path AS image, rating, city, distance, room_type AS roomType, price" . $sql_base . " ORDER BY id LIMIT ? OFFSET ?";
    $db_locations_filtered = [];

    if ($conn instanceof PDO) {
        $stmt_data = $conn->prepare($sql_data);
        

        $param_index = 1;
        foreach ($params as $value) {
            $stmt_data->bindValue($param_index++, $value);
        }
        
        $stmt_data->bindValue($param_index++, $k, PDO::PARAM_INT);
        $stmt_data->bindValue($param_index, $offset, PDO::PARAM_INT);
        
        $stmt_data->execute();
        $db_locations_filtered = $stmt_data->fetchAll(PDO::FETCH_ASSOC);
    } 

    // $xmlDoc = new DOMDocument('1.0', 'UTF-8');
    // $xmlDoc->formatOutput = true;

    // $root = $xmlDoc->createElement('properties');
    // $xmlDoc->appendChild($root);

    // foreach ($db_locations_filtered as $loc) {
    //     $property = $xmlDoc->createElement('property');
        
    //     $property->appendChild($xmlDoc->createElement('id', $loc['id']));
    //     $property->appendChild($xmlDoc->createElement('name', htmlspecialchars($loc['name'])));
    //     $property->appendChild($xmlDoc->createElement('city', htmlspecialchars($loc['city'])));
    //     $property->appendChild($xmlDoc->createElement('distance', $loc['distance']));
    //     $property->appendChild($xmlDoc->createElement('rating', $loc['rating']));
    //     $property->appendChild($xmlDoc->createElement('roomType', htmlspecialchars($loc['roomType'])));
    //     $property->appendChild($xmlDoc->createElement('price', $loc['price']));
    //     $property->appendChild($xmlDoc->createElement('image', htmlspecialchars($loc['image'])));

    //     $root->appendChild($property);
    // }
    // $root->appendChild($xmlDoc->createElement('total', $totalRecords));
    // $root->appendChild($xmlDoc->createElement('success', 'true'));

    // echo $xmlDoc->saveXML();

    echo json_encode([
        'success' => true,
        'data' => $db_locations_filtered,
        'total' => $totalRecords
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again later.']);
    // $xmlDoc = new DOMDocument('1.0', 'UTF-8');
    // $response = $xmlDoc->createElement('response');
    
    // $response->appendChild($xmlDoc->createElement('success', 'false'));
    // $response->appendChild($xmlDoc->createElement('message', 'Server error. Please try again later.'));
    
    // $xmlDoc->appendChild($response);
    // echo $xmlDoc->saveXML();
}
?>