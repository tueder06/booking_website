<?php
require_once 'includes/init.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        $error_msg = "Unauthorized action. Please refresh the page.";
    } else {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm-password'] ?? '';
        $first_name = trim($_POST['first-name'] ?? '');
        $last_name = trim($_POST['last-name'] ?? '');
        $birthdate = $_POST['birthdate'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $role = 'tourist';

        $phone_regex = '/^\+?[0-9]{7,15}$/';

        if (!$email) {
            $error_msg = "Please enter a valid email address.";
        } else if (empty($first_name) || empty($last_name) || empty($birthdate) || 
                    empty($phone) || empty($country) || empty($city) || empty($bio)) {
            $error_msg = "All fields are required.";
        } elseif (strlen($password) < 8) {
            $error_msg = "Password must have at least 8 characters.";
        } elseif ($password !== $confirm_password) {
            $error_msg = "Passwords do not match.";
        } elseif (!preg_match($phone_regex, $phone)) {
            $error_msg = "Invalid phone number format.";
        } elseif (strlen($bio) < 20 || strlen($bio) > 300) {
            $error_msg = "Preferences must contain between 20 and 300 characters."; 
        } else {
            $date_of_birth = new DateTime($birthdate);
            $today = new DateTime();
            $age = $today->diff($date_of_birth)->y;

            if ($age < 18) {
                $error_msg = "You need to be at least 18 years old to register.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                try {
                    if ($conn instanceof PDO) {
                        $sql = "INSERT INTO users (email, password, first_name, last_name, birthday, phone_number, country, city, preferences, role) 
                                VALUES (:email, :password, :first_name, :last_name, :birthday, :phone, :country, :city, :preferences, :role)";
                        
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([
                            ':email'      => $email,
                            ':password'   => $hashed_password,
                            ':first_name' => $first_name,
                            ':last_name'  => $last_name,
                            ':birthday'   => $birthdate,
                            ':phone'      => $phone,
                            ':country'    => $country,
                            ':city'       => $city,
                            ':preferences' => $bio,
                            ':role'       => $role
                        ]);
                    } elseif ($conn instanceof mysqli) {
                        $sql = "INSERT INTO users (email, password, first_name, last_name, birthday, phone_number, country, city, preferences, role) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ssssssssss", $email, $hashed_password, $first_name, $last_name, $birthdate, $phone, $country, $city, $bio, $role);
                        $stmt->execute();
                        $stmt->close();
                    }

                    $success_msg = "Account created successfully. You can go back to login now.";
                    $_POST = []; 
                } catch (Exception $e) {
                    if (str_contains($e->getMessage(), '1062')) {
                        $error_msg = "Email address already used by someone else.";
                    } else {
                        error_log("Signup Error: " . $e->getMessage());
                        $error_msg = "An error occured. Plsease try again later.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/login.png" type="image/png">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/auth.css">
    <script type="module" src="js/account-forms.js"></script>
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script type="module" src="jquery/account-forms.js"></script> -->
    <title>Sign Up - Booking</title>
</head>
<body class="auth-page">
    
<div class="auth-card signup-card">
        <h1>Create an Account</h1>
        <p class="subtitle">Join us and start booking your dream vacations</p>

        <?php if ($success_msg): ?>
            <div class="success-message">
                <strong><?= htmlspecialchars($success_msg) ?></strong><br>
                <a href="login.php">Go to Login</a>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="error-message">
                <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" id="signup-form">
            <fieldset class="auth-fieldset">
                <legend>Account Information</legend>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    <div id="email-error" class="error-text"></div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" placeholder="Create password" required>
                        <div id="main-password-error" class="error-text"></div>
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">Confirm Password:</label>
                        <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm password" required>
                        <div id="confirm-password-error" class="error-text"></div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="auth-fieldset">
                <legend>Personal Information</legend>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first-name">First Name:</label>
                        <input type="text" id="first-name" name="first-name" placeholder="John" required>
                        <div id="first-name-error" class="error-text"></div>
                    </div>
                    <div class="form-group">
                        <label for="last-name">Last Name:</label>
                        <input type="text" id="last-name" name="last-name" placeholder="Doe" required>
                        <div id="last-name-error" class="error-text"></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="birthdate">Birthdate:</label>
                        <input type="date" id="birthdate" name="birthdate">
                        <div id="birthdate-error" class="error-text"></div>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="tel" id="phone" name="phone" placeholder="+40 700 000 000">
                        <div id="phone-error" class="error-text"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="country">Country:</label>
                        <select id="country" name="country">
                            <option value="">Select country</option>
                        </select>
                        <div id="country-error" class="error-text"></div>
                    </div>

                    <div class="form-group">
                        <label for="city">City:</label>
                        <select id="city" name="city" disabled>
                            <option value="">Select city</option>
                        </select>
                        <div id="city-error" class="error-text"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="bio">Travel Preferences:</label>
                    <textarea id="bio" name="bio" rows="3" placeholder="Tell us about your favorite travel destinations..."></textarea>
                    <div id="bio-error" class="error-text"></div>
                    <div id="char-count">0 / 300</div>
                </div>
            </fieldset>

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit" class="btn-block">Sign Up</button>
        </form>
        
        <p class="auth-link">Already have an account? <a href="login.php">Log in</a></p>
    </div>

</body>
</html>