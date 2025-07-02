<?php

session_start();
require '../../config/config.php';
require '../../vendor/autoload.php'; // Load Composer's autoloader


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = $_POST['email'];
    $password = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    $token    = bin2hex(random_bytes(50)); // Generate a unique token

    $_SESSION['email'] = $email;  // Store email in session

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectToAdminDashboard('Error invalid email format.');

        $_SESSION['error_message'] = "Invalid email format.";
        header('Location: ../../index.php'); // Redirect back to index.php
        exit();
    }

    // Password Validation
    if (strlen($password) < 6 || !preg_match("/[A-Z]/", $password) || !preg_match("/\d/", $password) || !preg_match("/[@$!%*?&]/", $password)) {

        $_SESSION['error_message'] = "Password must be at least 6 characters and include one uppercase letter, one number, and one special character.";
        header('Location: ../../index.php');
        exit();
    }

    // Confirm password validation
    if ($password !== $confirmPassword) {
        redirectToAdminDashboard('Passwords do not match.');

        $_SESSION['error_message'] = "Passwords do not match.";
        header('Location: ../../index.php');
        exit();
    }


    // Check if user exists
    $stmt = $pdo->prepare('SELECT * FROM Users WHERE Email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    $password_hash = password_hash($password, PASSWORD_BCRYPT); //md5($password);

    if ($user && isset($_SESSION['admin_username'])) {
        $_SESSION['message'] = "Email already registered.";
        header('Location: ../pages/dashboard.php');
        exit();
    } else if ($user) {
        $_SESSION['error_message'] = "Email already registered.";
        header('Location: ../../index.php');
        exit();
    } else {
        $template = file_get_contents('../../email_tamplate/validation_email.html');

        // Replace placeholders with dynamic data
        $verificationLink = 'http://' . $_SERVER['HTTP_HOST'] . '/eva/pages/auth/verify.php?token=' . $token;
        $template = str_replace('{{username}}', htmlspecialchars($email), $template);
        $template = str_replace('{{verification_link}}', htmlspecialchars($verificationLink), $template);

        // Send verification email
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = SMTP_PORT;

            // Recipients
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($email, $email);

            // Insert user into database
            $stmt = $pdo->prepare('INSERT INTO Users (Email, Password, Token) VALUES (?, ?, ?)');
            $stmt->execute([$email, $password_hash, $token]);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email Address for EVA Registration';
            $mail->Body    = $template;

            $mail->send();

            redirectToAdminDashboard('Registration successful!');

            $_SESSION['success_message'] = 'Registration successful! Please check your email to verify your account.';
            header('Location: ../../index.php');
        } catch (Exception $e) {
            redirectToAdminDashboard("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");

            $_SESSION['error_message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            header('Location: ../../index.php');
        }
        exit();
    }
}

function redirectToAdminDashboard($message)
{
    if (isset($_SESSION['admin_username'])) {
        $_SESSION['message'] = $message;
        header('Location: ../pages/dashboard.php');
        exit();
    }
}
