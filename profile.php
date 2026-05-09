<?php
require_once 'includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_data = [];

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
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script type="module" src="jquery/profile.js"></script> -->
    <title>Profile</title>
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    <main class="dashboard-layout container">
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
                <a href="" class="stat-link">View Details &rarr;</a>
            </div>
            
            <div class="stat-card">
                <h3>Upcoming Trips</h3>
                <p class="stat-value">2</p>
            </div>
            
            <div class="stat-card accent">
                <h3>Loyalty Points</h3>
                <p class="stat-value">3,450</p>
            </div>
        </section>

        <section id="upcoming-trips" class="dashboard-trips-section">
            <h2>Your Next Adventures</h2>
            
            <div class="trip-list">
                <article class="trip-card">
                    <div class="trip-image">
                        <img src="https://cf.bstatic.com/xdata/images/hotel/max1024x768/830098191.jpg?k=0fa0f8de45ed0396c3c7492daa3d5ebdd8a4f10456bb211f54aa70fbdb9b575c&o=" alt="Constanta Apartment">
                    </div>
                    <div class="trip-details">
                        <div class="trip-header">
                            <div>
                                <h3>Seaside Serenity Apartments</h3>
                                <p class="trip-location">Constanța, Romania</p>
                            </div>
                            <span class="status-badge">Confirmed</span>
                        </div>
                        
                        <div class="trip-dates">
                            <div class="date-box">
                                <span class="date-label">Check-in</span>
                                <span class="date-value">15 Jul 2026</span>
                            </div>
                            <div class="date-box">
                                <span class="date-label">Check-out</span>
                                <span class="date-value">22 Jul 2026</span>
                            </div>
                            <div class="date-box">
                                <span class="date-label">Booking Ref</span>
                                <span class="date-value">#BK-789012</span>
                            </div>
                        </div>
                        
                        <div class="trip-actions">
                            <button class="btn-outline">Cancel Booking</button>
                            <button class="btn-primary">Manage Trip</button>
                        </div>
                    </div>
                </article>

                <article class="trip-card">
                    <div class="trip-image">
                        <img src="https://cf.bstatic.com/xdata/images/hotel/max1024x768/477203913.jpg?k=70150cc6d1e056b8e5795b7d1ead266542be64731b4cda55b3651c7641104fb6&o=" alt="Mountain Lodge">
                    </div>
                    <div class="trip-details">
                        <div class="trip-header">
                            <div>
                                <h3>Mountainview Retreat Lodge</h3>
                                <p class="trip-location">Șirnea, Romania</p>
                            </div>
                            <span class="status-badge">Confirmed</span>
                        </div>
                        
                        <div class="trip-dates">
                            <div class="date-box">
                                <span class="date-label">Check-in</span>
                                <span class="date-value">10 Oct 2026</span>
                            </div>
                            <div class="date-box">
                                <span class="date-label">Check-out</span>
                                <span class="date-value">14 Oct 2026</span>
                            </div>
                            <div class="date-box">
                                <span class="date-label">Booking Ref</span>
                                <span class="date-value">#BK-992341</span>
                            </div>
                        </div>
                        
                        <div class="trip-actions">
                            <button class="btn-outline">Cancel Booking</button>
                            <button class="btn-primary">Manage Trip</button>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </main>
    <?php require_once 'includes/footer.html'; ?>
</body>
</html>