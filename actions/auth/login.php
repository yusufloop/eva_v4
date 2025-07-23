<?php
session_start();
require_once '../../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email    = $_POST['login-email'];
    $password = $_POST['login-password'];

    // Check for user from database  
    $stmt = $pdo->prepare('SELECT user_id, password, is_admin FROM users WHERE email = ? AND is_verified = 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['UserID'] = $user['user_id']; // backward compatibility
        $_SESSION['Email'] = $email;
        
        if ($user['is_admin'] == 1) {
            $_SESSION['IsAdmin'] = 1;
            $_SESSION['admin_username'] = $email;
        }

        // Redirect to protected page
        header('Location: //pages/dashboard.php');
        
    } else {        
        $_SESSION['login_error_message'] = "Invalid credentials or email not verified.";
        header('Location: /index.php'); // Redirect back to index.php
        // exit();
    }
    exit();
}
?>
