<?php
session_start(); // Start the session

require 'config.php';
// require '../vendor/autoload.php'; // Load Composer's autoloader

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    $token = $_POST['token'];

    // Password Validation
    if (strlen($newPassword) < 6 || !preg_match("/[A-Z]/", $newPassword) || !preg_match("/\d/", $newPassword) || !preg_match("/[@$!%*?&]/", $newPassword)) {
        $_SESSION['error_message'] = "Password must be at least 6 characters long, contain at least one uppercase letter, one number, and one special character.";
        header('Location: ../reset_password.php?token=' . $token);
        exit();
    }

    // Confirm Password Validation
    if ($newPassword !== $confirmPassword) {
        $_SESSION['error_message'] = "Passwords do not match.";
        header('Location: ../reset_password.php?token=' . $token);
        exit();
    }

    // // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);//md5($newPassword);

    // // Find the user with the given token
    $stmt = $pdo->prepare('SELECT * FROM Users WHERE Token = ?');
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // Update user's password and clear reset token
        $stmt = $pdo->prepare('UPDATE Users SET Password = ?, Token = "" WHERE UserId = ?');
        $stmt->execute([$hashedPassword, $user['UserId']]);

        $_SESSION['success_message'] = 'Password has been reset successfully. You may now log in with your new password.';
        header('Location: ../index.php');
    } else {
        $_SESSION['error_message'] = 'Invalid or expired token.';
        header('Location: ../index.php');
    }
    exit();
}
?>
