<?php
require_once 'includes/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php");
    exit;
}

$error_msg = '';
if (isset($_SESSION['error_msg'])) {
    $error_msg = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}

function getPropertyById($conn, $property_id, $owner_id) {
    $sql = "SELECT * FROM accomodations WHERE id = ? AND owner_id = ?";
    
    if ($conn instanceof PDO) {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$property_id, $owner_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($conn instanceof mysqli) {
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $property_id, $owner_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $stmt->close();
            return $data;
        }
    }
    return false;
}

$action = $_GET['action'] ?? 'add';
$property_id = $_GET['id'] ?? null;

$name = '';
$city = '';
$distance = '';
$room_type = '';
$price = '';
$rating = '';
$property_type = '';
$stars = '';
$meal_plan = '';
$facilities_array = [];
$existing_image = '';

$page_title = "Add New Property";
$submit_btn_text = "Add Property";


if ($action === 'edit' && $property_id) {
    $page_title = "Edit Property";
    $submit_btn_text = "Update Property";

    $prop = getPropertyById($conn, $property_id, $_SESSION['user_id']);
    
    if ($prop) {
        $name = $prop['name'] ?? '';
        $city = $prop['city'] ?? '';
        $distance = $prop['distance'] ?? '';
        $room_type = $prop['room_type'] ?? '';
        $price = $prop['price'] ?? '';
        $rating = $prop['rating'] ?? '';
        $property_type = $prop['property_type'] ?? '';
        $stars = $prop['stars'] ?? '';
        $meal_plan = $prop['meal_plan'] ?? '';
        
        $facilities_raw = $prop['facilities_string'] ?? '';
        $facilities_array = !empty($facilities_raw) ? explode(',', $facilities_raw) : [];
        
        $existing_image = $prop['image_path'] ?? '';
    } else {
        header("Location: accomodations.php?error=unauthorized_or_not_found");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/property-form.css">
    <script type="module" src="js/property-form.js"></script>
    <title><?= $page_title ?> | Owner Dashboard</title>
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    
    <main class="dashboard-layout container">
        
        <div class="owner-header">
            <h2><?= $page_title ?></h2>
            <a href="accomodations.php" class="btn-outline">&larr; Back to Properties</a>
        </div>

        <section class="add-property-section">
            <div class="dashboard-card">
                <?php if ($error_msg): ?>
                    <div class="error-message">
                        <?= htmlspecialchars($error_msg) ?>
                    </div>
                <?php endif; ?>    

                <form action="includes/save_property.php" method="POST" enctype="multipart/form-data" class="property-form" novalidate>
                    <input type="hidden" name="action" value="<?= $action ?>">
                    <?php if($action === 'edit'): ?>
                        <input type="hidden" name="property_id" value="<?= $property_id ?>">
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="name">Property Name:</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>">
                            <div id="name-error" class="error-text"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="city">City:</label>
                            <select id="city" name="city" data-selected="<?= htmlspecialchars($city ?? '') ?>"></select>
                            <div id="city-error" class="error-text"></div>
                        </div>

                        <div class="form-group">
                            <label for="distance">Distance from center (km):</label>
                            <input type="number" id="distance" name="distance" value="<?= htmlspecialchars($distance ?? '') ?>" min="0" max="100" step="0.1">
                            <div id="distance-error" class="error-text"></div>
                        </div>

                        <div class="form-group">
                            <label for="room-type">Room Type:</label>
                            <select id="room-type" name="room_type">
                                <option value="">Select room type...</option>
                                <option value="Single Room" <?= ($room_type ?? '') === 'Single Room' ? 'selected' : '' ?>>Single Room</option>
                                <option value="Standard Double" <?= ($room_type ?? '') === 'Standard Double' ? 'selected' : '' ?>>Standard Double</option>
                                <option value="Deluxe Twin" <?= ($room_type ?? '') === 'Deluxe Twin' ? 'selected' : '' ?>>Deluxe Twin</option>
                                <option value="Family Suite" <?= ($room_type ?? '') === 'Family Suite' ? 'selected' : '' ?>>Family Suite</option>
                                <option value="Executive Studio" <?= ($room_type ?? '') === 'Executive Studio' ? 'selected' : '' ?>>Executive Studio</option>
                                <option value="Penthouse Apartment" <?= ($room_type ?? '') === 'Penthouse Apartment' ? 'selected' : '' ?>>Penthouse Apartment</option>
                            </select>
                            <div id="room-type-error" class="error-text"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price per night (RON):</label>
                            <input type="number" id="price" name="price" value="<?= htmlspecialchars($price ?? '') ?>" min="1">
                            <div id="price-error" class="error-text"></div>
                        </div>

                        <div class="form-group">
                            <label for="rating">Initial Rating (1-10):</label>
                            <input type="number" id="rating" name="rating" value="<?= htmlspecialchars($rating ?? '') ?>" min="1" max="10" step="0.1">
                            <div id="rating-error" class="error-text"></div>
                        </div>

                        <div class="form-group">
                            <label for="property-type">Property Type:</label>
                            <select id="property-type" name="property_type">
                                <option value="">Select type...</option>
                                <option value="Hotel" <?= ($property_type ?? '') === 'Hotel' ? 'selected' : '' ?>>Hotel</option>
                                <option value="Apartment" <?= ($property_type ?? '') === 'Apartment' ? 'selected' : '' ?>>Apartment</option>
                                <option value="Villa" <?= ($property_type ?? '') === 'Villa' ? 'selected' : '' ?>>Villa</option>
                                <option value="Guest House" <?= ($property_type ?? '') === 'Guest House' ? 'selected' : '' ?>>Guest House</option>
                                <option value="Chalet" <?= ($property_type ?? '') === 'Chalet' ? 'selected' : '' ?>>Chalet</option>
                                <option value="Mobile Home" <?= ($property_type ?? '') === 'Mobile Home' ? 'selected' : '' ?>>Mobile Home</option>
                                <option value="Hostel" <?= ($property_type ?? '') === 'Hostel' ? 'selected' : '' ?>>Hostel</option>
                                <option value="Camping" <?= ($property_type ?? '') === 'Camping' ? 'selected' : '' ?>>Camping</option>
                            </select>
                            <div id="property-type-error" class="error-text"></div>
                        </div>

                        <div class="form-group">
                            <label for="stars">Property Stars (1-5):</label>
                            <input type="number" id="stars" name="stars" value="<?= htmlspecialchars($stars ?? '') ?>" min="1" max="5">
                            <div id="stars-error" class="error-text"></div>
                        </div>

                        <div class="form-group">
                            <label>Meal Plan:</label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="meal_plan" id="meal-plan-self" value="Self Catering" <?= ($meal_plan ?? '') === 'Self Catering' ? 'checked' : '' ?>> Self Catering
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="meal_plan" id="meal-plan-breakfast" value="Breakfast Included" <?= ($meal_plan ?? '') === 'Breakfast Included' ? 'checked' : '' ?>> Breakfast Included
                                </label>
                            </div>
                            <div id="meal-plan-error" class="error-text"></div>
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="facilities">Facilities:</label>
                            <select id="facilities" name="facilities[]" size="4" multiple class="multiple-select">
                                <option value="wifi" <?= in_array('wifi', $facilities_array ?? []) ? 'selected' : '' ?>>Free WiFi</option>
                                <option value="parking" <?= in_array('parking', $facilities_array ?? []) ? 'selected' : '' ?>>Parking</option>
                                <option value="pool" <?= in_array('pool', $facilities_array ?? []) ? 'selected' : '' ?>>Swimming Pool</option>
                                <option value="gym" <?= in_array('gym', $facilities_array ?? []) ? 'selected' : '' ?>>Fitness Center</option>
                                <option value="spa" <?= in_array('spa', $facilities_array ?? []) ? 'selected' : '' ?>>Spa</option>
                                <option value="restaurant" <?= in_array('restaurant', $facilities_array ?? []) ? 'selected' : '' ?>>Restaurant</option>
                                <option value="bar" <?= in_array('bar', $facilities_array ?? []) ? 'selected' : '' ?>>Bar</option>
                                <option value="pet-friendly" <?= in_array('pet-friendly', $facilities_array ?? []) ? 'selected' : '' ?>>Pet Friendly</option>
                            </select>
                            <div id="facilities-error" class="error-text"></div>
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="property-image">Property Image:</label>
                            <?php if($action === 'edit' && !empty($existing_image)): ?>
                                <div class="image-preview-container">
                                    <p>Current Image:</p>
                                    <img src="<?= htmlspecialchars($existing_image) ?>" alt="Current Property Image">
                                </div>
                            <?php endif; ?>
                            <input type="file" id="property-image" name="property-image" accept="image/jpeg, image/png, image/webp" class="file-input">
                            <small>Max file size: 2MB. Formats: JPG, PNG, WEBP.</small>
                            <div id="property-image-error" class="error-text"></div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <button type="submit" name="submit-property" class="btn-primary">
                        <?= $submit_btn_text ?>
                    </button>
                </form>

            </div>
        </section>
        
    </main>
    
    <?php require_once 'includes/footer.html'; ?>
</body>
</html>