<?php
function generateTokens()
{
    $selector = bin2hex(random_bytes(16));
    $validator = bin2hex(random_bytes(32));

    return [$selector, $validator, $selector . ':' . $validator];
}

function parseToken(string $token)
{
    $parts = explode(':', $token);

    if ($parts && count($parts) == 2) {
        return [$parts[0], $parts[1]];
    }
    return null;
}

function insertUserToken($conn, $user_id, $expires_at, $selector, $hashed_validator) {
    $sql = "INSERT INTO user_tokens (user_id, selector, hashed_validator, expires_at) VALUES (?, ?, ?, ?)";
        
    if ($conn instanceof PDO) {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id, $selector, $hashed_validator, $expires_at]);
    } elseif ($conn instanceof mysqli) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $user_id, $selector, $hashed_validator, $expires_at);
        $stmt->execute();
        $stmt->close();
    }
}

function createRememberMe($conn, $user_id) {
    [$selector, $validator, $token] = generateTokens();

    $hashed_validator = password_hash($validator, PASSWORD_DEFAULT);
    $expire_time = time() + 86400 * 30;
    $expires_at = date('Y-m-d H:i:s', $expire_time);

    $sql = "INSERT INTO user_tokens (user_id, selector, hashed_validator, expires_at) VALUES (?, ?, ?, ?)";
    
    if ($conn instanceof PDO) {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id, $selector, $hashed_validator, $expires_at]);
    } elseif ($conn instanceof mysqli) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $user_id, $selector, $hashed_validator, $expires_at);
        $stmt->execute();
        $stmt->close();
    }

    setcookie('remember_me', $token, $expire_time, "/", "", false, true);
}

function validateRememberMe($conn) {
    if (!isset($_COOKIE['remember_me'])) return false;

    $tokens = parseToken($_COOKIE['remember_me']);
    if (!$tokens) return false;

    [$selector, $validator] = $tokens;
    $user_data = null;
    $current_time = date('Y-m-d H:i:s');

    $sql = "SELECT t.hashed_validator, u.id, u.first_name, u.role 
            FROM user_tokens t
            JOIN users u ON t.user_id = u.id
            WHERE t.selector = ? AND t.expires_at > ?";

    if ($conn instanceof PDO) {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$selector, $current_time]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($conn instanceof mysqli) {
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $selector, $current_time);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user_data = $result->fetch_assoc();
            }
            $stmt->close();
        }
    }

    if ($user_data && password_verify($validator, $user_data['hashed_validator'])) {
        return $user_data;
    }

    return false;
}

function clearRememberMe($conn) {
    if (isset($_COOKIE['remember_me'])) {
        $cookie_parts = parseToken($_COOKIE['remember_me']);
        if ($cookie_parts != null) {
            $selector = $cookie_parts[0];
            $sql = "DELETE FROM user_tokens WHERE selector = ?";
            
            if ($conn instanceof PDO) {
                $stmt = $conn->prepare($sql);
                $stmt->execute([$selector]);
            } elseif ($conn instanceof mysqli) {
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $selector);
                $stmt->execute();
                $stmt->close();
            }
        }

        setcookie('remember_me', '', time() - 3600, "/");
    }
}
?>