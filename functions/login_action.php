<?php
require '../config/config.php'; // Load all configurations

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['login-email'] ?? '';
    $password = $_POST['login-password'] ?? '';

  
    if (empty($email) || empty($password)) {
        redirectWithMessage('../index.php', 'Please fill in all fields', 'login_error');
    }

   
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWithMessage('../index.php', 'Invalid email format', 'login_error');
    }

    try {
        // Check for user from database
        $stmt = $pdo->prepare('SELECT UserID, Email, Password, IsAdmin, IsVerified FROM Users WHERE Email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

       
        if ($user && password_verify($password, $user['Password'])) {
            
            // Check if email is verified (optional check)
            if ($user['IsVerified'] == 0) {
                redirectWithMessage('../index.php', 'Please verify your email before logging in', 'login_error');
            }

            // Set common session data
            $_SESSION['UserID'] = $user['UserID'];
            $_SESSION['user_data'] = $user; // Store all user data for easy access

            // Check if user is admin
            if ($user['IsAdmin'] == 1 || $user['IsAdmin'] === "Yes") {
                // Admin login
                $_SESSION['admin_username'] = $user['Email'];
                $_SESSION['IsAdmin'] = $user['IsAdmin'];
                
                redirectWithMessage('../admin_dashboard.php', 'Welcome back, Administrator!', 'success');
                
            } else {
                // Regular user login
                $_SESSION['username'] = $user['Email'];
                
                redirectWithMessage('../dashboard.php', 'Welcome back!', 'success');
            }
            
        } else {
            // Invalid credentials
            redirectWithMessage('../index.php', 'Invalid credentials or email not verified', 'login_error');
        }
        
    } catch (PDOException $e) {
        // Database error
        error_log("Login error: " . $e->getMessage());
        redirectWithMessage('../index.php', 'System error. Please try again later', 'login_error');
    }
}
?>
