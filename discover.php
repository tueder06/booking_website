<?php
require_once __DIR__ . '/includes/upload_acmd.php';

$is_submitted = isset($_GET['search']);

$val_search = $_GET['search'] ?? '';
$val_checkin = $_GET['check-in'] ?? '';
$val_checkout = $_GET['check-out'] ?? '';
$val_price = $_GET['price-slider'] ?? 1000;
$val_rating = $_GET['min_rating'] ?? '';
$val_stars = $_GET['property-star'] ?? '0';
$val_catering = $_GET['catering'] ?? '';
$val_types = isset($_GET['property_type']) ? (array)$_GET['property_type'] : [];
$val_facilities = isset($_GET['facilities']) ? (array)$_GET['facilities'] : [];

function checkPropertyType($type, $val_types, $is_submitted) {
    if (!$is_submitted) return 'checked';
    return in_array($type, $val_types) ? 'checked' : '';
}

$sql_filt = "SELECT id, name, image_path AS image, rating, city, distance, room_type AS roomType, price 
        FROM accomodations WHERE 1=1";

$params = [];

$types = "";
if (!empty($_GET['search'])) {
    $sql_filt .= " AND (city LIKE ?)";
    $types .= "s";
    $params[] = '%' . $_GET['search'] . '%';
}

if (!empty($_GET['price-slider'])) {
    $sql_filt .= " AND price <= ?";
    $types .= "i";
    $params[] = $_GET['price-slider'];
}

if (!empty($_GET['property-star']) && $_GET['property-star'] > 0) {
    $sql_filt .= " AND stars = ?";
    $types .= "i";
    $params[] = $_GET['property-star'];
}

if (!empty($_GET['catering'])) {
    $sql_filt .= " AND meal_plan = ?";
    $types .= "s";
    $params[] = $_GET['catering'];
}

if (!empty($_GET['min_rating'])) {
    $sql_filt .= " AND rating >= ?";
    $types .= "d";
    $params[] = $_GET['min_rating'];
}

if (!empty($_GET['property_type']) && is_array($_GET['property_type'])) {
    $placeholders = implode(',', array_fill(0, count($_GET['property_type']), '?'));
    $sql_filt .= " AND property_type IN ($placeholders)";
    foreach ($_GET['property_type'] as $prop_type) {
        $types .= "s";
        $params[] = $prop_type;
    }
}

if (!empty($_GET['facilities']) && is_array($_GET['facilities'])) {
    foreach ($_GET['facilities'] as $fac) {
        $sql_filt .= " AND facilities_string LIKE ?";
        $types .= "s";
        $params[] = '%' . $fac . '%';
    }
}

$sql_filt .= " ORDER BY id";

$db_locations_filtered = [];
if ($conn instanceof PDO) {
    $stmt = $conn->prepare($sql_filt);
    $stmt->execute($params);
    $db_locations_filtered = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($conn instanceof mysqli) {
    $db_locations_filtered = [];

    if ($stmt = $conn->prepare($sql_filt)) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $db_locations_filtered[] = $row;
        }
        $stmt->close();
    } else {}
}

$json_locations_filtered = json_encode($db_locations_filtered);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/discover.png" type="image/png">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/discover.css"> 
    <script type="module" src="js/booking-forms.js"></script>
    <script>
        const discoverLocations = <?= $json_locations ?>;
        const discoverLocationsFiltered = <?= $json_locations_filtered ?>;
    </script>
    <script type="module" src="js/locations.js"></script>
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script type="module" src="jquery/booking-forms.js"></script>
    <script type="module" src="jquery/locations.js"></script> -->
    <title>Discover</title>
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    <main class="main-content">
        <section class="hero-section">
            <div class="container">
                <h1>Discover new destinations</h1>
                <p>Explore the world with us! Find the best travel deals, tips, and guides to make your next trip unforgettable.</p>
            </div>
        </section>

        <div class="search-results-layout container">
            <aside class="filter-sidebar">
                <div class="filter-card">
                    <h2>Filter your search</h2>
                    <form class="filter-form" id="filter-form">
                        
                        <div class="filter-group">
                            <label for="search"><b>Destination:</b></label>
                            <input type="text" id="search" name="search" placeholder="Enter a destination" maxlength="30" value="<?= htmlspecialchars($val_search) ?>">
                            <div id="search-error" class="error-text"></div>
                            <div id="autocomplete-list" class="autocomplete-items"></div>
                        </div>
                        
                        <div class="filter-row">
                            <div class="filter-group">
                                <label for="check-in"><b>Check-in:</b></label>
                                <input type="date" id="check-in" name="check-in" value="<?= htmlspecialchars($val_checkin) ?>">
                                <div id="checkin-error" class="error-text"></div>
                            </div>
                            <div class="filter-group">
                                <label for="check-out"><b>Check-out:</b></label>
                                <input type="date" id="check-out" name="check-out" value="<?= htmlspecialchars($val_checkout) ?>">
                                <div id="checkout-error" class="error-text"></div>
                            </div>
                        </div>

                        <input type="submit" value="Search" class="btn-search">

                        <fieldset>
                            <legend>Price (0 - 2000 RON)</legend>
                            <input type="range" id="price-slider" name="price-slider" min="0" max="2000" step="5" value="<?= htmlspecialchars($val_price) ?>">
                            <input type="number" id="price" name="price" value="<?= htmlspecialchars($val_price) ?>" readonly>
                        </fieldset>

                        <fieldset>
                            <legend>Property type</legend>
                            <div class="scroll-box" id="property-container">
                                <label><input type="checkbox" id="hotel" name="property_type[]" value="Hotel" <?= checkPropertyType('Hotel', $val_types, $is_submitted) ?>> Hotel</label>
                                <label><input type="checkbox" id="apartment" name="property_type[]" value="Apartment" <?= checkPropertyType('Apartment', $val_types, $is_submitted) ?>> Apartment</label>
                                <label><input type="checkbox" id="villa" name="property_type[]" value="Villa" <?= checkPropertyType('Villa', $val_types, $is_submitted) ?>> Villa</label>
                                <label><input type="checkbox" id="guesthouse" name="property_type[]" value="Guest House" <?= checkPropertyType('Guest House', $val_types, $is_submitted) ?>> Guest House</label>
                                <label><input type="checkbox" id="chalet" name="property_type[]" value="Chalet" <?= checkPropertyType('Chalet', $val_types, $is_submitted) ?>> Chalet</label>
                                <label><input type="checkbox" id="mobilehome" name="property_type[]" value="Mobile Home" <?= checkPropertyType('Mobile Home', $val_types, $is_submitted) ?>> Mobile Home</label>
                                <label><input type="checkbox" id="hostel" name="property_type[]" value="Hostel" <?= checkPropertyType('Hostel', $val_types, $is_submitted) ?>> Hostel</label>
                                <label><input type="checkbox" id="camping" name="property_type[]" value="Camping" <?= checkPropertyType('Camping', $val_types, $is_submitted) ?>> Camping</label>
                            </div>
                            <div id="property-error" class="error-text"></div>
                        </fieldset>

                        <fieldset id="meal-container">
                            <legend>Meal plan</legend>
                            <label><input type="radio" name="catering" value="Self Catering" <?= $val_catering === 'Self Catering' ? 'checked' : '' ?>> Self Catering</label>
                            <label><input type="radio" name="catering" value="Breakfast Included" <?= $val_catering === 'Breakfast Included' ? 'checked' : '' ?>> Breakfast Included</label>
                            <div id="meal-error" class="error-text"></div> </fieldset>
                        </fieldset>

                        <fieldset>
                            <legend>Guest Rating</legend>
                            <label><input type="radio" name="min_rating" value="9" <?= $val_rating == '9' ? 'checked' : '' ?>> Excellent: 9+</label>
                            <label><input type="radio" name="min_rating" value="8" <?= $val_rating == '8' ? 'checked' : '' ?>> Very Good: 8+</label>
                            <label><input type="radio" name="min_rating" value="7" <?= $val_rating == '7' ? 'checked' : '' ?>> Good: 7+</label>
                            <label><input type="radio" name="min_rating" value="6" <?= $val_rating == '6' ? 'checked' : '' ?>> Pleasant: 6+</label>
                        </fieldset>

                        <fieldset>
                            <legend>Property Stars</legend>
                            <select id="property-star" name="property-star">
                                <option value="0" <?= $val_stars == '0' ? 'selected' : '' ?>>Any stars</option>
                                <option value="5" <?= $val_stars == '5' ? 'selected' : '' ?>>5 Stars</option>
                                <option value="4" <?= $val_stars == '4' ? 'selected' : '' ?>>4 Stars</option>
                                <option value="3" <?= $val_stars == '3' ? 'selected' : '' ?>>3 Stars</option>
                                <option value="2" <?= $val_stars == '2' ? 'selected' : '' ?>>2 Stars</option>
                                <option value="1" <?= $val_stars == '1' ? 'selected' : '' ?>>1 Star</option>
                            </select>
                        </fieldset>

                        <fieldset>
                            <legend>Facilities</legend>
                            <select id="facilities" name="facilities" size="3" multiple>
                                <option value="wifi" name="facilities[]" <?= in_array('wifi', $val_facilities) ? 'selected' : '' ?>>Free WiFi</option>
                                <option value="parking" name="facilities[]" <?= in_array('parking', $val_facilities) ? 'selected' : '' ?>>Parking</option>
                                <option value="pool" name="facilities[]" <?= in_array('pool', $val_facilities) ? 'selected' : '' ?>>Swimming Pool</option>
                                <option value="gym" name="facilities[]" <?= in_array('gym', $val_facilities) ? 'selected' : '' ?>>Fitness Center</option>
                                <option value="spa" name="facilities[]" <?= in_array('spa', $val_facilities) ? 'selected' : '' ?>>Spa</option>
                                <option value="restaurant" name="facilities[]" <?= in_array('restaurant', $val_facilities) ? 'selected' : '' ?>>Restaurant</option>
                                <option value="bar" name="facilities[]" <?= in_array('bar', $val_facilities) ? 'selected' : '' ?>>Bar</option>
                                <option value="pet-friendly" name="facilities[]" <?= in_array('pet-friendly', $val_facilities) ? 'selected' : '' ?>>Pet Friendly</option>
                            </select>
                            <div id="facilities-error" class="error-text"></div>
                        </fieldset>
                    </form>
                </div>
            </aside>

            <section class="found-locations">
                <table class="results-table" id="discover-results-table">
                    </table>
            </section>

            <button id="bag-toggle" class="bag-toggle-btn" aria-label="Open saved rooms">
                💼 Bag <span id="bag-badge" class="bag-badge">0</span>
            </button>

            <div id="bag-panel" class="bag-panel">
                <div class="bag-header">
                    <h3>My Travel Bag</h3>
                    <button id="close-bag" class="close-bag-btn">&times;</button>
                </div>
                
                <div id="bag-content" class="bag-content">
                    <p class="empty-msg">Your bag is empty. Start exploring!</p>
                </div>
            </div>
        </div>
    </main>
    <?php require_once 'includes/footer.html'; ?>
</body>
</html>