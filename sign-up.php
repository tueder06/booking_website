<?php
require_once 'includes/init.php';

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';
    $first_name = trim($_POST['first-name'] ?? '');
    $last_name = trim($_POST['last-name'] ?? '');
    $birthday = empty($_POST['birthdate']) ? null : $_POST['birthdate'];
    $phone = trim($_POST['phone'] ?? '');
    $country = $_POST['country'] ?? '';
    $city = $_POST['city'] ?? '';
    $bio = trim($_POST['bio'] ?? '');
    $role = 'tourist';

    if ($password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
    } elseif (!empty($email) && !empty($password) && !empty($first_name) && !empty($last_name)) {
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        if ($conn instanceof PDO) {
            $sql = "INSERT INTO users (email, password, first_name, last_name, birthday, phone_number, country, city, preferences, role) 
                    VALUES (:email, :password, :first_name, :last_name, :birthday, :phone, :country, :city, :preferences, :role)";
            
            try {
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':email'      => $email,
                    ':password'   => $hashed_password,
                    ':first_name' => $first_name,
                    ':last_name'  => $last_name,
                    ':birthday'   => $birthday,
                    ':phone'      => $phone,
                    ':country'    => $country,
                    ':city'       => $city,
                    ':preferences' => $bio,
                    ':role'       => $role
                ]);
                
                $success_msg = "Account created successfully! You can now log in.";
                $_POST = []; 
                
            } catch (PDOException $e) {
                if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                    $error_msg = "This email address is already registered.";
                } else {
                    $error_msg = "Database error: " . $e->getMessage();
                }
            } finally {
                $stmt = null;
            }
        } 
        elseif ($conn instanceof mysqli) {
            $sql = "INSERT INTO users (email, password, first_name, last_name, birthday, phone_number, country, city, preferences, role) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssssssssss", $email, $hashed_password, $first_name, $last_name, $birthday, $phone, $country, $city, $bio, $role);
                
                try {
                    $stmt->execute();
                    $success_msg = "Account created successfully! You can now log in.";
                    $_POST = [];
                } catch (mysqli_sql_exception $e) {
                    if ($e->getCode() === 1062) {
                        $error_msg = "This email address is already registered.";
                    } else {
                        $error_msg = "Database error: " . $e->getMessage();
                    }
                }
                $stmt->close();
            } else {
                $error_msg = "Database error: " . $conn->error;
            }
        }

    } else {
        $error_msg = "Please fill in all required fields.";
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

            <button type="submit" class="btn-block">Sign Up</button>
        </form>
        
        <p class="auth-link">Already have an account? <a href="login.php">Log in</a></p>
    </div>

</body>
</html>