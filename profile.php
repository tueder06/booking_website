<?php
require_once 'includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_data = [];
$system_error = "";

try {
    $sql = "SELECT email, first_name, last_name, birthday, phone_number, country, city, preferences FROM users WHERE id = ?";

    if ($conn instanceof PDO) {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($conn instanceof mysqli) {
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user_data = $result->fetch_assoc();
            }
            $stmt->close();
        }
    }

    if (!$user_data) {
        session_destroy();
        header("Location: login.php");
        exit;
    }

    $email = htmlspecialchars($user_data['email'] ?? '');
    $firstname = htmlspecialchars($user_data['first_name'] ?? '');
    $lastname = htmlspecialchars($user_data['last_name'] ?? '');
    $birthdate = htmlspecialchars($user_data['birthday'] ?? '');
    $phone = htmlspecialchars($user_data['phone_number'] ?? '');
    $country = htmlspecialchars($user_data['country'] ?? '');
    $city = htmlspecialchars($user_data['city'] ?? '');
    $preferences = htmlspecialchars($user_data['preferences'] ?? '');

    $res_query = "SELECT r.id, a.name AS hotel_name, a.city, r.check_in, r.check_out, a.image_path as image, r.status
                    FROM reservations r
                    JOIN accomodations a ON r.accommodation_id = a.id
                    WHERE r.user_id = ? 
                    AND r.check_in > CURDATE()
                    ORDER BY r.check_in ASC";

    $res_stmt = $conn->prepare($res_query);
    $res_stmt->execute([$user_id]);
    $future_reservations = $res_stmt->fetchAll(PDO::FETCH_ASSOC);

    $initial_selected_id = !empty($future_reservations) ? $future_reservations[0]['id'] : '';

} catch (PDOException $e) {
    error_log("Database Error in profile.php: " . $e->getMessage());
    $system_error = "We're experiencing temporary difficulties loading all your profile details. Please try refreshing the page later.";
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/suitcase.png" type="image/png">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/profile.css">
    <script type="module" src="js/profile.js"></script>
    <script type="module" src="js/get-reservation.js"></script>
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script type="module" src="jquery/get-reservation.js"></script> -->
    <title>Profile</title>
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    <main class="dashboard-layout container">
        <?php if (!empty($system_error)): ?>
            <div style="background-color: #ffe6e6; color: #cc0000; padding: 15px; border-radius: 8px; border: 1px solid #cc0000; margin-bottom: 20px; font-weight: bold; text-align: center;">
                <?= htmlspecialchars($system_error) ?>
            </div>
        <?php endif; ?>

        <section class="profile-content">
            <form class="profile-form">
                
                <fieldset class="dashboard-card">
                    <legend><h2>Account Information</h2></legend>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <div class="input-row">
                            <input type="email" id="email" name="email" value="<?= $email ?>" readonly>
                            <button type="button" class="btn-icon" title="Edit Email"><img src="images/modify.png" alt="Edit" width="20"></button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password:</label>
                        <div class="input-row">
                            <input type="password" id="password" name="password" value="****************" readonly>
                            <button type="button" class="btn-icon" title="Edit Password"><img src="images/modify.png" alt="Edit" width="20"></button>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="dashboard-card">
                    <legend><h2>Personal Information</h2></legend>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="firstname">First Name:</label>
                            <div class="input-row">
                                <input type="text" id="firstname" name="firstname" value="<?= $firstname ?>" readonly>
                                <button type="button" class="btn-icon"><img src="images/modify.png" alt="Edit" width="20"></button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="lastname">Last Name:</label>
                            <div class="input-row">
                                <input type="text" id="lastname" name="lastname" value="<?= $lastname ?>" readonly>
                                <button type="button" class="btn-icon"><img src="images/modify.png" alt="Edit" width="20"></button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="birthdate">Birthdate:</label>
                            <div class="input-row">
                                <input type="date" id="birthdate" name="birthdate" value="<?= $birthdate ?>" readonly>
                                <button type="button" class="btn-icon"><img src="images/modify.png" alt="Edit" width="20"></button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone:</label>
                            <div class="input-row">
                                <input type="tel" id="phone" name="phone" value="<?= $phone ?>" readonly>
                                <button type="button" class="btn-icon"><img src="images/modify.png" alt="Edit" width="20"></button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="country">Country:</label>
                            <div class="input-row">
                                <select id="country" name="country" disabled>
                                    <?php if(!empty($country)): ?>
                                        <option value="<?= $country ?>" selected><?= $country ?></option>
                                    <?php else: ?>
                                        <option value="" selected>Not specified</option>
                                    <?php endif; ?>
                                </select>
                                <button type="button" class="btn-icon"><img src="images/modify.png" alt="Edit" width="20"></button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="city">City:</label>
                            <div class="input-row">
                                <select id="city" name="city" disabled>
                                    <?php if(!empty($city)): ?>
                                        <option value="<?= $city ?>" selected><?= $city ?></option>
                                    <?php else: ?>
                                        <option value="" selected>Not specified</option>
                                    <?php endif; ?>
                                </select>
                                <button type="button" class="btn-icon"><img src="images/modify.png" alt="Edit" width="20"></button>
                            </div>
                        </div>

                        <div class="form-group preferences-group">
                            <label for="preferences">Travel Preferences:</label>
                            <div class="input-row">
                                <textarea id="preferences" name="preferences" rows="3" readonly><?= $preferences ?></textarea>
                                <button type="button" class="btn-icon" title="Edit Preferences"><img src="images/modify.png" alt="Edit" width="20"></button>
                            </div>
                        </div>
                    </div>
                </fieldset>

            </form>
        </section>
        <section class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Bookings</h3>
                <p class="stat-value">14</p>
                <a href="#" class="stat-link">View Details &rarr;</a>
            </div>
            
            <div class="stat-card">
                <h3>Upcoming Trips</h3>
                <p class="stat-value"><?= count($future_reservations) ?></p>
            </div>
            
            <div class="stat-card accent">
                <h3>Loyalty Points</h3>
                <p class="stat-value">3,450</p>
            </div>
        </section>

        <section id="upcoming-trips" class="dashboard-trips-section">
            <h2>Your Next Adventures</h2>
            
            <?php if (empty($future_reservations)): ?>
                <div class="empty-trips-message">
                    <p>You have no upcoming trips. Time to plan a new adventure!</p>
                </div>
            <?php else: ?>
                
                <div class="reservation-selector-container">
                    <label for="reservation-selector">Select a trip to manage:</label>
                    <select id="reservation-selector" class="form-select">
                        <?php foreach ($future_reservations as $res): ?>
                            <?php 
                            $display_in = date('d M Y', strtotime($res['check_in']));
                            $display_out = date('d M Y', strtotime($res['check_out']));
                            $display_text = htmlspecialchars($res['hotel_name'] . " (" . $display_in . " - " . $display_out . ")");
                            ?>
                            <option value="<?= $res['id'] ?>">
                                <?= $display_text ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="reservation-alerts" class="alert-box"></div>

                <form id="manage-trip-form" class="trip-list">
                    <input type="hidden" id="csrf-token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" id="edit-res-id" value="<?= $initial_selected_id ?>">
                    
                    <article class="trip-card">
                        <div class="trip-image">
                            <img id="edit-hotel-img" src="" alt="Hotel">
                        </div>
                        
                        <div class="trip-details">
                            <div class="trip-header">
                                <div>
                                    <h3 id="edit-hotel-name">Loading...</h3>
                                    <p class="trip-location" id="edit-hotel-city">Loading...</p>
                                </div>
                                <span id="status" class="status-badge">Loading...</span>
                            </div>
                            
                            <div class="trip-dates">
                                <div class="date-box">
                                    <span class="date-label">Check-in</span>
                                    <input type="date" id="edit-checkin" class="form-input-date" required>
                                </div>
                                
                                <div class="date-box">
                                    <span class="date-label">Check-out</span>
                                    <input type="date" id="edit-checkout" class="form-input-date" required>
                                </div>
                                
                                <div class="date-box checkbox-wrapper">
                                    <input type="checkbox" id="edit-toggle-box">
                                    <label id="edit-toggle-label" for="edit-toggle-box">Cancel Booking</label>
                                </div>
                            </div>
                            
                            <div class="trip-actions">
                                <span id="edit-total-price" class="total-price-tag">Total: 0.00 RON</span>
                                <button type="submit" id="btn-save-trip" class="btn-primary dynamic-save-btn" disabled>
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </article>
                </form>

            <?php endif; ?>
        </section>
    </main>
    <?php require_once 'includes/footer.html'; ?>
</body>
</html>