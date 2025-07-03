<?php
session_start();
require_once '../../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email    = $_POST['login-email'];
    $password = $_POST['login-password'];

    // Check for user from database
    $stmt = $pdo->prepare('SELECT UserID, Password, IsAdmin FROM Users WHERE Email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (password_verify($password, $user['Password']) && $user['IsAdmin'] === "Yes") {
        $_SESSION['Email'] = $email;
        $_SESSION['IsAdmin'] = $admin['IsAdmin'];
        $_SESSION['UserID'] = $user['UserID'];

        header('Location: /pages/dashboard.php');
        // exit();

    }
    if (password_verify($password, $user['Password'])) {
        // Set session variables
        $_SESSION['Email'] = $email;
        $_SESSION['UserID'] = $user['UserID'];

        // Redirect to protected page
        header('Location: /pages/dashboard.php');
        // exit();
        
    } else {        
        $_SESSION['login_error_message'] = "Invalid credentials or email not verified.";
        header('Location: /index.php'); // Redirect back to index.php
        // exit();
    }
    exit();
}
?>
