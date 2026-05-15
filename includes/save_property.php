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

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    $_SESSION['error_message'] = "Session expired or action could not be validated. Please retry.";
    header("Location: index.php"); 
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
    'name' => trim($_POST['name'] ?? ''),
    'city' => trim($_POST['city'] ?? ''),
    'distance' => trim($_POST['distance'] ?? ''),
    'room_type' => trim($_POST['room_type'] ?? ''),
    'price' => (int)($_POST['price'] ?? 0),
    'rating' => (float)($_POST['rating'] ?? 0),
    'property_type' => trim($_POST['property_type'] ?? ''),
    'stars' => (int)($_POST['stars'] ?? 0),
    'meal_plan' => trim($_POST['meal_plan'] ?? ''),
    'owner_id' => (int)$owner_id,
    'id' => (int)$property_id
];

$facilities_raw = $_POST['facilities'] ?? [];
$facilities_safe = array_map('htmlspecialchars', $facilities_raw);
$data['facilities'] = implode(',', $facilities_safe);

$data['image_path'] = null;
$hasNewImage = false;
$uploaded_image_path = null;

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

        $image_resource = false;
        switch ($file_type) {
            case 'image/jpeg':
                $image_resource = @imagecreatefromjpeg($file_tmp);
                break;
            case 'image/png':
                $image_resource = @imagecreatefrompng($file_tmp);
                break;
            case 'image/webp':
                $image_resource = @imagecreatefromwebp($file_tmp);
                break;
        }

        if ($image_resource !== false) {
            $save_success = false;
            
            switch ($file_type) {
                case 'image/jpeg':
                    $save_success = imagejpeg($image_resource, $dest_path, 85);
                    break;
                case 'image/png':
                    $save_success = imagepng($image_resource, $dest_path, 8);
                    break;
                case 'image/webp':
                    $save_success = imagewebp($image_resource, $dest_path, 85); 
                    break;
            }
            imagedestroy($image_resource);
            if ($save_success) {
                $data['image_path'] = $db_save_path;
                $uploaded_image_path = $dest_path;
                $hasNewImage = true;
            } else {
                $_SESSION['error_message'] = "Could not store file.";
                header("Location: ../property_form.php?action=" . $action . ($property_id ? "&id=" . $property_id : ""));
                exit;
            }
        } else {
            $_SESSION['error_message'] = "Uploaded file is either corrupted or contains malicious data.";
            header("Location: ../property_form.php?action=" . $action . ($property_id ? "&id=" . $property_id : ""));
            exit;
        }

    } else {
        $_SESSION['error_message'] = "Invalid file format or file too large. Max 2MB, JPG/PNG/WEBP only.";
        header("Location: ../property_form.php?action=" . $action . ($property_id ? "&id=" . $property_id : ""));
        exit;
    }
}

# compromised version
// if (isset($_FILES['property-image']) && $_FILES['property-image']['error'] === UPLOAD_ERR_OK) {
//     $file_tmp = $_FILES['property-image']['tmp_name'];
//     $file_name = $_FILES['property-image']['name']; 
    
//     $upload_dir = '../uploads/properties/'; 

//     $dest_path = $upload_dir . $file_name;
//     $db_save_path = 'uploads/properties/' . $file_name;

//     if (move_uploaded_file($file_tmp, $dest_path)) {
//         $data['image_path'] = $db_save_path;
//         $uploaded_image_path = $dest_path;
//         $hasNewImage = true;
//     } else {
//         $_SESSION['error_message'] = "Could not move file to $upload_dir. Check folder permissions.";
//         header("Location: ../property_form.php?action=" . $action . ($property_id ? "&id=" . $property_id : ""));
//         exit;
//     }
// }

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
        if ($hasNewImage && isset($uploaded_image_path)) {
            if (file_exists($uploaded_image_path)) {
                unlink($uploaded_image_path);
            }
        }
        $_SESSION['error_message'] = "Error saving property to the database. Please try again.";
        header("Location: ../property_form.php?action=" . $action . ($property_id ? "&id=" . $property_id : ""));
        exit;
    }
} catch (Exception $e) {
    error_log("DB Insert/Update Error: " . $e->getMessage());
    $_SESSION['error_message'] = "An error occurred while saving the property. Please try again.";
    header("Location: ../property_form.php?action=" . $action . ($property_id ? "&id=" . $property_id : ""));
    exit;
}
?>