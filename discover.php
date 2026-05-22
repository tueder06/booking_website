<?php
require_once __DIR__ . '/includes/upload_acmd.php';
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
    <script type="module" src="js/booking-forms.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script type="module" src="jquery/locations.js"></script>
    <!-- <script type="module" src="js/locations.js"></script> -->
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
                    <form class="filter-form" id="filter-form" novalidate>
                        
                        <div class="filter-group">
                            <label for="search"><b>Destination:</b></label>
                            <input type="text" id="search" name="search" placeholder="Enter a destination" maxlength="30">
                            <div id="search-error" class="error-text" style="display: none; color: red;"></div>
                            <div id="autocomplete-list" class="autocomplete-items"></div>
                        </div>
                        
                        <div class="filter-row">
                            <div class="filter-group">
                                <label for="check-in"><b>Check-in:</b></label>
                                <input type="date" id="check-in" name="check-in">
                                <div id="checkin-error" class="error-text" style="display: none; color: red;"></div>
                            </div>
                            <div class="filter-group">
                                <label for="check-out"><b>Check-out:</b></label>
                                <input type="date" id="check-out" name="check-out">
                                <div id="checkout-error" class="error-text" style="display: none; color: red;"></div>
                            </div>
                        </div>

                        <input type="submit" value="Search" name="sidebar_search" class="btn-search">

                        <fieldset>
                            <legend>Price (0 - 2000 RON)</legend>
                            <input type="range" id="price-slider" name="price-slider" min="0" max="2000" step="5" value="1000">
                            <input type="number" id="price" name="price" value="1000" readonly>
                            <div id="price-error" class="error-text" style="display: none; color: red;"></div>
                        </fieldset>

                        <fieldset>
                            <legend>Property type</legend>
                            <div class="scroll-box" id="property-container">
                                <label><input type="checkbox" id="hotel" name="property_type[]" value="Hotel"> Hotel</label>
                                <label><input type="checkbox" id="apartment" name="property_type[]" value="Apartment"> Apartment</label>
                                <label><input type="checkbox" id="villa" name="property_type[]" value="Villa"> Villa</label>
                                <label><input type="checkbox" id="guesthouse" name="property_type[]" value="Guest House"> Guest House</label>
                                <label><input type="checkbox" id="chalet" name="property_type[]" value="Chalet"> Chalet</label>
                                <label><input type="checkbox" id="mobilehome" name="property_type[]" value="Mobile Home"> Mobile Home</label>
                                <label><input type="checkbox" id="hostel" name="property_type[]" value="Hostel"> Hostel</label>
                                <label><input type="checkbox" id="camping" name="property_type[]" value="Camping"> Camping</label>
                            </div>
                            <div id="property-error" class="error-text" style="display: none; color: red;"></div>
                        </fieldset>

                        <fieldset id="meal-container">
                            <legend>Meal plan</legend>
                            <label><input type="radio" name="catering" value="Self Catering"> Self Catering</label>
                            <label><input type="radio" name="catering" value="Breakfast Included"> Breakfast Included</label>
                            <div id="meal-error" class="error-text" style="display: none; color: red;"></div> 
                        </fieldset>

                        <fieldset>
                            <legend>Guest Rating</legend>
                            <label><input type="radio" name="min_rating" value="9"> Excellent: 9+</label>
                            <label><input type="radio" name="min_rating" value="8"> Very Good: 8+</label>
                            <label><input type="radio" name="min_rating" value="7"> Good: 7+</label>
                            <label><input type="radio" name="min_rating" value="6"> Pleasant: 6+</label>
                        </fieldset>

                        <fieldset>
                            <legend>Property Stars</legend>
                            <select id="property-star" name="property-star">
                                <option value="0">Any stars</option>
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </fieldset>

                        <fieldset>
                            <legend>Facilities</legend>
                            <select id="facilities" name="facilities[]" size="3" multiple>
                                <option value="wifi" name="facilities[]">Free WiFi</option>
                                <option value="parking" name="facilities[]">Parking</option>
                                <option value="pool" name="facilities[]">Swimming Pool</option>
                                <option value="gym" name="facilities[]">Fitness Center</option>
                                <option value="spa" name="facilities[]">Spa</option>
                                <option value="restaurant" name="facilities[]">Restaurant</option>
                                <option value="bar" name="facilities[]">Bar</option>
                                <option value="pet-friendly" name="facilities[]">Pet Friendly</option>
                            </select>
                            <div id="facilities-error" class="error-text" style="display: none; color: red;"></div>
                        </fieldset>

                        <input type="hidden" id="csrf-token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    </form>
                </div>
            </aside>

            <section class="found-locations">
                <div id="locations-error-container"></div>

                <table class="results-table" id="discover-results-table">
                    </table>

                <div class="pagination-controls">
                    <button id="btn-prev" class="btn" disabled>Previous</button>
                    <span id="page-indicator">Page 1</span>
                    <button id="btn-next" class="btn">Next</button>
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