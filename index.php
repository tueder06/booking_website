<?php
require_once __DIR__ . '/includes/upload_acmd.php';
?>

<!DOCTYPE html>
<html lang="ro"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/suitcase.png" type="image/png">
    <link rel="stylesheet" href="css/global.css" type="text/css">
    <link rel="stylesheet" href="css/index.css" type="text/css">
    <script type="module" src="js/carousel.js"></script>
    <script>
        const discoverLocations = <?= $json_locations ?>;
    </script>
    <script type="module" src="js/booking-forms.js"></script>
    <script type="module" src="js/locations.js"></script>
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script type="module" src="jquery/carousel.js"></script>
    <script type="module" src="jquery/booking-forms.js"></script>
    <script type="module" src="jquery/locations.js"></script> -->
    <title> Booking </title>
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    <main class="main-content">
        <section class="hero-section">
            <?php if(isset($_SESSION['user_id'])): ?>
                <h1>Welcome back, <?= htmlspecialchars($_SESSION['first_name'] ?? 'Traveler') ?>!</h1>
            <?php else: ?>
                <h1>Welcome to Booking</h1>
            <?php endif; ?>
            <p>Your one-stop destination for all your travel needs. Discover new places, book accommodations, and manage your profile with ease.</p>
        </section>

        <section class="search-section">
            <h2>Find Your Next Adventure</h2>
            <form id="home-form" action="discover.php">
                <div class="filter-group" style="position: relative;">
                    <label for="search"><b>Search for destinations:</b></label><br>
                    <input type="text" id="search" name="search" placeholder="Enter a destination" maxlength="30" size="30" autocomplete="off">
                    <div id="search-error" class="error-text"></div>
                    <div id="autocomplete-list" class="autocomplete-items"></div>
                </div>

                <label for="check-in"><b>Check-in date:</b></label>
                <input type="date" id="check-in" name="check-in">
                <div id="checkin-error" class="error-text"></div>

                <label for="check-out"><b>Check-out date:</b></label>
                <input type="date" id="check-out" name="check-out">
                <div id="checkout-error" class="error-text"></div>

                <br>
                <input type="submit" id="home-submit" value="Search" disabled>
            </form>
        </section>

        <section class="featured-section">
            <h2>Featured Destinations</h2>
            <div class="carousel-flex-container"> 
                <button id="prev-slide" class="carousel-control">&#10094;</button>
                <div class="carousel-wrapper">
                    <div id="carousel-content" class="carousel-content"></div>
                </div>
                <button id="next-slide" class="carousel-control">&#10095;</button>
            </div>
        </section>

        <section class="benefits-section">
            <h2>Why Book With Us?</h2>
            <ol class="benefits-list">
                <li>
                    <h3><strong>Best Prices Guaranteed</strong></h3>
                    <p>We offer <span>competitive rates</span> with no hidden fees.</p>
                </li>
                <li>
                    <h3><strong>24/7 Customer Support</strong></h3>
                    <p>Our team is <span>always here</span> to help you.</p>
                </li>
                <li>
                    <h3><strong>Secure Booking</strong></h3>
                    <p>Your personal information is <span>protected with encryption</span>.</p>
                </li>
                <li>
                    <h3><strong>Easy Cancellation</strong></h3>
                    <p>Cancel your booking <span>hassle-free, anytime</span>.</p>
                </li>
            </ol>
        </section>

        <section class="offers-section">
            <h2>Special Offers</h2>
            <table class="offers-table">
                <tr>
                    <td rowspan="3"><strong>Promotions</strong></td>
                    <td>
                        <h3>Early Bird Discount - <strong>20% Off</strong></h3>
                        <h4>Book <span>3 months</span> in advance</h4>
                        <p>Get exclusive discounts when you plan ahead.</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h3>Group Booking Offer - <strong>15% Off</strong></h3>
                        <h4>Valid for <span>4 or more</span> rooms</h4>
                        <p>Perfect for family trips and group vacations.</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h3>Loyalty Rewards - <strong>Triple Points</strong></h3>
                        <h4><strong>Expires <span>March 31, 2026</span></strong></h4>
                        <p>Earn rewards faster and unlock premium benefits.</p>
                    </td>
                </tr>
            </table>
        </section>

        <section class="reviews-section">
            <h2>What Our Guests Say</h2>
            <h3>Guest Reviews</h3>
            <ol type="I" start="4" class="reviews-list">
                <li>
                    <h4>Amazing experience!</h4>
                    <p>"The booking process was smooth and the accommodation exceeded my expectations."</p>
                    <h5>John Doe</h5>
                    <h6>Verified Guest - February 2026</h6>
                </li>
                <li>
                    <h4>Recommended!</h4>
                    <p>"Great customer service and competitive prices. I'll definitely book with them again."</p>
                    <h5>Dani Hrusca</h5>
                    <h6>Verified Guest - January 2026</h6>
                </li>
                <li>
                    <h4>Perfect Stay!</h4>
                    <p>"Found the perfect hotel in minutes. The app made everything incredibly convenient."</p>
                    <h5>Dan Balan</h5>
                    <h6>Verified Guest - February 2026</h6>
                </li>
            </ol>
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
    </main>
    <?php require_once 'includes/footer.html'; ?>
</body>
</html>