<?php
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit-property'])) {
    header("Location: ../accomodations.php");
    exit;
}

function insertProperty($conn, $data) {
    $sql = "INSERT INTO accomodations (name, image_path, rating, city, distance, room_type, price, owner_id, property_type, stars, meal_plan, facilities_string) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    if ($conn instanceof PDO) {
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            $data['name'], $data['image_path'], $data['rating'], $data['city'], 
            $data['distance'], $data['room_type'], $data['price'], $data['owner_id'], 
            $data['property_type'], $data['stars'], $data['meal_plan'], $data['facilities']
        ]);
    } elseif ($conn instanceof mysqli) {
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssdsisiisiss", 
                $data['name'], $data['image_path'], $data['rating'], $data['city'], 
                $data['distance'], $data['room_type'], $data['price'], $data['owner_id'], 
                $data['property_type'], $data['stars'], $data['meal_plan'], $data['facilities']
            );
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        }
    }
    return false;
}

function updateProperty($conn, $data, $hasNewImage) {
    if ($hasNewImage) {
        $sql = "UPDATE accomodations 
                SET name=?, image_path=?, rating=?, city=?, distance=?, room_type=?, price=?, property_type=?, stars=?, meal_plan=?, facilities_string=? 
                WHERE id=? AND owner_id=?";
                
        if ($conn instanceof PDO) {
            $stmt = $conn->prepare($sql);
            return $stmt->execute([
                $data['name'], $data['image_path'], $data['rating'], $data['city'], 
                $data['distance'], $data['room_type'], $data['price'], $data['property_type'], 
                $data['stars'], $data['meal_plan'], $data['facilities'], $data['id'], $data['owner_id']
            ]);
        } elseif ($conn instanceof mysqli) {
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssdsdsisissii", 
                    $data['name'], $data['image_path'], $data['rating'], $data['city'], 
                    $data['distance'], $data['room_type'], $data['price'], $data['property_type'], 
                    $data['stars'], $data['meal_plan'], $data['facilities'], $data['id'], $data['owner_id']
                );
                $success = $stmt->execute();
                $stmt->close();
                return $success;
            }
        }
    } else {
        $sql = "UPDATE accomodations 
                SET name=?, rating=?, city=?, distance=?, room_type=?, price=?, property_type=?, stars=?, meal_plan=?, facilities_string=? 
                WHERE id=? AND owner_id=?";
                
        if ($conn instanceof PDO) {
            $stmt = $conn->prepare($sql);
            return $stmt->execute([
                $data['name'], $data['rating'], $data['city'], $data['distance'], 
                $data['room_type'], $data['price'], $data['property_type'], $data['stars'], 
                $data['meal_plan'], $data['facilities'], $data['id'], $data['owner_id']
            ]);
        } elseif ($conn instanceof mysqli) {
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sdsdsisissii", 
                    $data['name'], $data['rating'], $data['city'], $data['distance'], 
                    $data['room_type'], $data['price'], $data['property_type'], $data['stars'], 
                    $data['meal_plan'], $data['facilities'], $data['id'], $data['owner_id']
                );
                $success = $stmt->execute();
                $stmt->close();
                return $success;
            }
        }
    }
    return false;
}

$owner_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? 'add';
$property_id = $_POST['property_id'] ?? null;

$data = [
    'name' => trim(htmlspecialchars($_POST['name'] ?? '')),
    'city' => trim(htmlspecialchars($_POST['city'] ?? '')),
    'distance' => trim(htmlspecialchars($_POST['distance'] ?? '')),
    'room_type' => trim(htmlspecialchars($_POST['room_type'] ?? '')),
    'price' => (float)($_POST['price'] ?? 0),
    'rating' => (float)($_POST['rating'] ?? 0),
    'property_type' => trim(htmlspecialchars($_POST['property_type'] ?? '')),
    'stars' => (int)($_POST['stars'] ?? 0),
    'meal_plan' => trim(htmlspecialchars($_POST['meal_plan'] ?? '')),
    'owner_id' => $owner_id,
    'id' => $property_id
];

$facilities_raw = $_POST['facilities'] ?? [];
$facilities_safe = array_map('htmlspecialchars', $facilities_raw);
$data['facilities'] = implode(',', $facilities_safe);

$data['image_path'] = null;
$hasNewImage = false;

if (isset($_FILES['property-image']) && $_FILES['property-image']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['property-image']['tmp_name'];
    $file_name = $_FILES['property-image']['name'];
    $file_size = $_FILES['property-image']['size'];
    $file_type = mime_content_type($file_tmp); 
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size = 2 * 1024 * 1024;
    
    if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $upload_dir = '../uploads/properties/'; 

        $new_filename = 'prop_' . uniqid() . '.' . $ext;
        $dest_path = $upload_dir . $new_filename;
        $db_save_path = 'uploads/properties/' . $new_filename;

        if (move_uploaded_file($file_tmp, $dest_path)) {
            $data['image_path'] = $db_save_path;
            $hasNewImage = true;
        } else {
            $_SESSION['error_msg'] = "Could not move file to $upload_dir. Check folder permissions.";
            header("Location: ../property_form.php?action=" . $action . ($property_id ? "&id=" . $property_id : ""));
            exit;
        }
    } else {
        $_SESSION['error_msg'] = "Invalid file format or file too large. Max 2MB, JPG/PNG/WEBP only.";
        header("Location: ../property_form.php?action=" . $action . ($property_id ? "&id=" . $property_id : ""));
        exit;
    }
}

try {
    if ($action === 'add') {
        if (!$data['image_path']) {
            $data['image_path'] = __DIR__ . '/../images/placeholder-home.webp';
        }
        $success = insertProperty($conn, $data);
        
    } elseif ($action === 'edit' && $property_id) {
        $old_image_path = null;
        $sql_get_img = "SELECT image_path FROM accomodations WHERE id = ? AND owner_id = ?";
        if ($conn instanceof PDO) {
            $stmt = $conn->prepare($sql_get_img);
            $stmt->execute([$data['id'], $data['owner_id']]);
            $old_image_path = $stmt->fetchColumn();
        } elseif ($conn instanceof mysqli) {
            if ($stmt = $conn->prepare($sql_get_img)) {
                $stmt->bind_param("ii", $data['id'], $data['owner_id']);
                $stmt->execute();
                $stmt->bind_result($old_image_path);
                $stmt->fetch();
                $stmt->close();
            }
        }

        $success = updateProperty($conn, $data, $hasNewImage);
    }
    
    if (isset($success) && $success) {
        if ($action === 'edit') {
            if ($hasNewImage && !empty($old_image_path)) {
                $absolute_old_path = dirname(__DIR__) . '/' . $old_image_path;
                
                if (file_exists($absolute_old_path)) {
                    unlink($absolute_old_path);
                }
            }
        }

        header("Location: ../accomodations.php?success=1");
        exit;
    } else {
        $_SESSION['error_msg'] = "Error saving property to the database. Please try again.";
        header("Location: ../property_form.php?action=" . $action . ($property_id ? "&id=" . $property_id : ""));
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error_msg'] = "Database error: " . $e->getMessage();
    header("Location: ../property_form.php?action=" . $action . ($property_id ? "&id=" . $property_id : ""));
    exit;
}
?>