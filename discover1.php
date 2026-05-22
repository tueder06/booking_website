<?php
require_once __DIR__ . '/includes/upload_acmd.php';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$k_items = 5; 
$offset = ($page - 1) * $k_items;

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

$db_locations_filtered = [];
$totalRecords = 0;

if (empty($errors)) {
    try {
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
        
        if ($conn instanceof PDO) {
            $stmt_count = $conn->prepare($sql_count);
            $stmt_count->execute($params);
            $totalRecords = (int)$stmt_count->fetchColumn();
        }

        $sql_data = "SELECT id, name, image_path AS image, rating, city, distance, room_type AS roomType, price" . $sql_base . " ORDER BY id LIMIT ? OFFSET ?";
        
        if ($conn instanceof PDO) {
            $stmt_data = $conn->prepare($sql_data);
            
            $param_index = 1;
            foreach ($params as $value) {
                $stmt_data->bindValue($param_index++, $value);
            }
            
            $stmt_data->bindValue($param_index++, $k_items, PDO::PARAM_INT);
            $stmt_data->bindValue($param_index, $offset, PDO::PARAM_INT);
            
            $stmt_data->execute();
            $db_locations_filtered = $stmt_data->fetchAll(PDO::FETCH_ASSOC);
        } 
    } catch (Exception $e) {
        $system_error = "Server error. Could not load data. Please try again later.";
    }
}

$totalPages = max(1, ceil($totalRecords / $k_items));

function getRatingText($rating) {
    if ($rating >= 9) return "Excellent $rating";
    if ($rating >= 8) return "Very Good $rating";
    if ($rating >= 7) return "Good $rating";
    if ($rating >= 6) return "Pleasant $rating";
    return "Unpleasant $rating";
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/discover.png" type="image/png">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/discover.css">
    <script>
        const discoverLocations = <?= $json_locations ?>;
    </script>
    <script type="module" src="js/bag.js"></script>
    <script type="module" src="js/make-reservation.js"></script>
    
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
                    
                    <form class="filter-form" id="filter-form" action="discover1.php" method="GET" novalidate>
                        
                        <div class="filter-group">
                            <label for="search"><b>Destination:</b></label>
                            <input type="text" id="search" name="search" placeholder="Enter a destination" maxlength="30" 
                                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                            
                            <div id="search-error" class="error-text" style="<?= empty($errors['search']) ? 'display: none;' : 'display: block;' ?>">
                                <?= htmlspecialchars($errors['search'] ?? '') ?>
                            </div>
                            <div id="autocomplete-list" class="autocomplete-items"></div>
                        </div>
                        
                        <div class="filter-row">
                            <div class="filter-group">
                                <label for="check-in"><b>Check-in:</b></label>
                                <input type="date" id="check-in" name="check-in" 
                                       value="<?= htmlspecialchars($_GET['check-in'] ?? '') ?>">
                                <div id="checkin-error" class="error-text" style="<?= empty($errors['checkin']) ? 'display: none;' : 'display: block;' ?>">
                                    <?= htmlspecialchars($errors['checkin'] ?? '') ?>
                                </div>
                            </div>
                            <div class="filter-group">
                                <label for="check-out"><b>Check-out:</b></label>
                                <input type="date" id="check-out" name="check-out" 
                                       value="<?= htmlspecialchars($_GET['check-out'] ?? '') ?>">
                                <div id="checkout-error" class="error-text" style="<?= empty($errors['checkout']) ? 'display: none;' : 'display: block;' ?>">
                                    <?= htmlspecialchars($errors['checkout'] ?? '') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="error-text" style="<?= empty($errors['dates']) ? 'display: none;' : 'display: block;' ?>">
                            <?= htmlspecialchars($errors['dates'] ?? '') ?>
                        </div>

                        <input type="submit" value="Search" name="sidebar_search" class="btn-search">

                        <fieldset>
                            <legend>Price (0 - 2000 RON)</legend>
                            <input type="range" id="price-slider" name="price-slider" min="0" max="2000" step="5" 
                                   value="<?= htmlspecialchars($_GET['price-slider'] ?? '1000') ?>"
                                   oninput="document.getElementById('price').value = this.value">
                            <input type="number" id="price" name="price" 
                                   value="<?= htmlspecialchars($_GET['price-slider'] ?? '1000') ?>" readonly>
                            <div id="price-error" class="error-text" style="<?= empty($errors['price']) ? 'display: none;' : 'display: block;' ?>">
                                <?= htmlspecialchars($errors['price'] ?? '') ?>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>Property type</legend>
                            <div class="scroll-box" id="property-container">
                                <?php $ptypes = $_GET['property_type'] ?? []; ?>
                                <label><input type="checkbox" id="hotel" name="property_type[]" value="Hotel" <?= in_array('Hotel', $ptypes) ? 'checked' : '' ?>> Hotel</label>
                                <label><input type="checkbox" id="apartment" name="property_type[]" value="Apartment" <?= in_array('Apartment', $ptypes) ? 'checked' : '' ?>> Apartment</label>
                                <label><input type="checkbox" id="villa" name="property_type[]" value="Villa" <?= in_array('Villa', $ptypes) ? 'checked' : '' ?>> Villa</label>
                                <label><input type="checkbox" id="guesthouse" name="property_type[]" value="Guest House" <?= in_array('Guest House', $ptypes) ? 'checked' : '' ?>> Guest House</label>
                                <label><input type="checkbox" id="chalet" name="property_type[]" value="Chalet" <?= in_array('Chalet', $ptypes) ? 'checked' : '' ?>> Chalet</label>
                                <label><input type="checkbox" id="mobilehome" name="property_type[]" value="Mobile Home" <?= in_array('Mobile Home', $ptypes) ? 'checked' : '' ?>> Mobile Home</label>
                                <label><input type="checkbox" id="hostel" name="property_type[]" value="Hostel" <?= in_array('Hostel', $ptypes) ? 'checked' : '' ?>> Hostel</label>
                                <label><input type="checkbox" id="camping" name="property_type[]" value="Camping" <?= in_array('Camping', $ptypes) ? 'checked' : '' ?>> Camping</label>
                            </div>
                        </fieldset>

                        <fieldset id="meal-container">
                            <legend>Meal plan</legend>
                            <?php $cat = $_GET['catering'] ?? ''; ?>
                            <label><input type="radio" name="catering" value="Self Catering" <?= $cat === 'Self Catering' ? 'checked' : '' ?>> Self Catering</label>
                            <label><input type="radio" name="catering" value="Breakfast Included" <?= $cat === 'Breakfast Included' ? 'checked' : '' ?>> Breakfast Included</label>
                        </fieldset>

                        <fieldset>
                            <legend>Guest Rating</legend>
                            <?php $rat = $_GET['min_rating'] ?? ''; ?>
                            <label><input type="radio" name="min_rating" value="9" <?= $rat === '9' ? 'checked' : '' ?>> Excellent: 9+</label>
                            <label><input type="radio" name="min_rating" value="8" <?= $rat === '8' ? 'checked' : '' ?>> Very Good: 8+</label>
                            <label><input type="radio" name="min_rating" value="7" <?= $rat === '7' ? 'checked' : '' ?>> Good: 7+</label>
                            <label><input type="radio" name="min_rating" value="6" <?= $rat === '6' ? 'checked' : '' ?>> Pleasant: 6+</label>
                        </fieldset>

                        <fieldset>
                            <legend>Property Stars</legend>
                            <?php $star = $_GET['property-star'] ?? '0'; ?>
                            <select id="property-star" name="property-star">
                                <option value="0" <?= $star === '0' ? 'selected' : '' ?>>Any stars</option>
                                <option value="5" <?= $star === '5' ? 'selected' : '' ?>>5 Stars</option>
                                <option value="4" <?= $star === '4' ? 'selected' : '' ?>>4 Stars</option>
                                <option value="3" <?= $star === '3' ? 'selected' : '' ?>>3 Stars</option>
                                <option value="2" <?= $star === '2' ? 'selected' : '' ?>>2 Stars</option>
                                <option value="1" <?= $star === '1' ? 'selected' : '' ?>>1 Star</option>
                            </select>
                        </fieldset>

                        <fieldset>
                            <legend>Facilities</legend>
                            <?php $facs = $_GET['facilities'] ?? []; ?>
                            <select id="facilities" name="facilities[]" size="3" multiple>
                                <option value="wifi" <?= in_array('wifi', $facs) ? 'selected' : '' ?>>Free WiFi</option>
                                <option value="parking" <?= in_array('parking', $facs) ? 'selected' : '' ?>>Parking</option>
                                <option value="pool" <?= in_array('pool', $facs) ? 'selected' : '' ?>>Swimming Pool</option>
                                <option value="gym" <?= in_array('gym', $facs) ? 'selected' : '' ?>>Fitness Center</option>
                                <option value="spa" <?= in_array('spa', $facs) ? 'selected' : '' ?>>Spa</option>
                                <option value="restaurant" <?= in_array('restaurant', $facs) ? 'selected' : '' ?>>Restaurant</option>
                                <option value="bar" <?= in_array('bar', $facs) ? 'selected' : '' ?>>Bar</option>
                                <option value="pet-friendly" <?= in_array('pet-friendly', $facs) ? 'selected' : '' ?>>Pet Friendly</option>
                            </select>
                        </fieldset>

                        <input type="hidden" id="csrf-token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    </form>
                </div>
            </aside>

            <section class="found-locations">
                <?php if (!empty($system_error)): ?>
                    <div id="locations-error-container" style="color: red; background: #ffe6e6; padding: 15px; margin-bottom:15px; border: 1px solid red; border-radius: 5px;">
                        <?= htmlspecialchars($system_error) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors) && empty($system_error)): ?>
                    <div id="locations-error-container" style="color: red; margin-bottom:15px;">
                        Please correct the filters applied in the sidebar.
                    </div>
                <?php endif; ?>

                <table class="results-table" id="discover-results-table">
                    <?php if (empty($db_locations_filtered)): ?>
                        <tr><td colspan="2" style="text-align:center; padding: 20px;">No properties found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($db_locations_filtered as $loc): ?>
                            <tr class="result-card">
                                <td class="result-image-col">
                                    <img src="<?= htmlspecialchars($loc['image']) ?>" alt="<?= htmlspecialchars($loc['name']) ?>">
                                </td>
                                <td class="result-info-col">
                                    <div class="info-top">
                                        <h3 class="card-title"><?= htmlspecialchars($loc['name']) ?></h3>
                                        <span class="rating-badge"><?= getRatingText($loc['rating']) ?></span>
                                        <p class="location-text">
                                            <strong><?= htmlspecialchars($loc['city']) ?></strong> • <?= htmlspecialchars($loc['distance']) ?> km from center
                                        </p>
                                        <p class="room-type"><?= htmlspecialchars($loc['roomType']) ?></p>
                                    </div>
                                    <div class="card-actions">
                                        <div class="price-display">
                                            <span class="price-val"><?= htmlspecialchars($loc['price']) ?> RON</span>
                                            <span class="price-currency"> / night</span>
                                        </div>
                                        <button class="add-to-bag-btn" data-id="<?= $loc['id'] ?>">+ Add to Bag</button>
                                        <button class="book-now-btn" data-id="<?= $loc['id'] ?>">Book Now</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>

                <div class="pagination-controls">
                    <?php
                    $queryParams = $_GET;
                    
                    if ($page > 1 && $totalRecords > 0) {
                        $queryParams['page'] = $page - 1;
                        $prevUrl = '?' . http_build_query($queryParams);
                        echo "<a href='$prevUrl' class='btn' style='text-decoration: none;'>Previous</a>";
                    } else {
                        echo "<button class='btn' disabled>Previous</button>";
                    }

                    echo "<span id='page-indicator'>Page $page of $totalPages</span>";

                    if ($page < $totalPages && $totalRecords > 0) {
                        $queryParams['page'] = $page + 1;
                        $nextUrl = '?' . http_build_query($queryParams);
                        echo "<a href='$nextUrl' class='btn' style='text-decoration: none;'>Next</a>";
                    } else {
                        echo "<button class='btn' disabled>Next</button>";
                    }
                    ?>
                </div>
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