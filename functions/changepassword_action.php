<?php
require 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $UserID = $_SESSION['UserID'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Check if passwords match
    if ($newPassword !== $confirmPassword) {
        $_SESSION['message'] = "Passwords do not match.";
        header("Location: ../dashboard.php");
        exit;
    }

    $stmt = $pdo->prepare('SELECT Password FROM Users WHERE UserID = ?');
    $stmt->execute([$UserID]);
    $user = $stmt->fetch();
    if (password_verify($currentPassword, $user['Password'])) {
        $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update the password in the database
        try {
            $stmt = $pdo->prepare("UPDATE Users SET Password = ? WHERE UserID = ?");
            $stmt->execute([$hashedNewPassword, $UserID]);

            $_SESSION['message'] = "Password successfully updated.";
        } catch (PDOException $e) {
            $_SESSION['message'] = "Error updating password: " . $e->getMessage();
        }
    } else {
        $_SESSION['message'] = "Error: Current password is incorrect.";
    }
    // Redirect back to change password page
    header("Location: ../dashboard.php");
    exit;
}
?>
