<?php
require_once 'includes/init.php';

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $user_input = strtoupper(trim($_POST['captcha'] ?? ''));
    $actual_code = strtoupper($_SESSION['captcha_code'] ?? '');

    if ($user_input === $actual_code) {
        if (!empty($email) && !empty($password)) {
            $sql = "SELECT id, password, first_name, role FROM users WHERE email = ?";
            $user_data = null;

            if ($conn instanceof PDO) {
                try {
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$email]);
                    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    $error_msg = "Database error: " . $e->getMessage();
                }
            } 
            elseif ($conn instanceof mysqli) {
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    
                    $result = $stmt->get_result();
                    if ($result->num_rows === 1) {
                        $user_data = $result->fetch_assoc();
                    }
                    $stmt->close();
                } else {
                    $error_msg = "Database error: " . $conn->error;
                }
            }

            if ($user_data) {
                if (password_verify($password, $user_data['password'])) {
                    
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user_data['id'];
                    $_SESSION['first_name'] = $user_data['first_name'];
                    $_SESSION['role'] = $user_data['role'];
                    
                    if (isset($_POST['remember'])) {
                        createRememberMe($conn, $user_data['id']);
                    }

                    header("Location: index.php");
                    exit;

                } else {
                    $error_msg = "Invalid email or password.";
                }
            } else {
                if (empty($error_msg)) {
                    $error_msg = "Invalid email or password.";
                }
            }
        } else {
            $error_msg = "Please enter both email and password.";
        }
    } else {
        $error_msg = "CAPTCHA verification failed. Please try again.";
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
    <title>Login - Booking</title>
</head>
<body class="auth-page">
    <div class="auth-card">
        <h1>Welcome Back</h1>
        <p class="subtitle">Log in to manage your bookings</p>
        
        <?php if ($error_msg): ?>
            <div class="error-message">
                <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" id="login-form">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="login-email" name="email" placeholder="Enter your email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="login-password" name="password" placeholder="Enter your password" required>
            </div>
            
            <div class="form-group checkbox-group">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
            </div>

            <div class="form-group captcha-group">
                <img src="includes/captcha.php" id="login-captcha-image" alt="CAPTCHA Image">
                <input type="text" id="captcha" name="captcha" placeholder="Enter the code" required>
            </div>

            <button type="submit" class="btn-primary btn-block">Log in</button>
        </form>
        
        <p class="auth-link">Don't have an account? <a href="sign-up.php">Sign up</a></p>
    </div>
</body>
</html>