<?php
session_start(); // Start the session

require 'config.php';
require '../vendor/autoload.php'; // Load Composer's autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = $_POST['email'];

    $token    = bin2hex(random_bytes(50)); // Generate a unique token

    // Check if user exists
    $stmt = $pdo->prepare('SELECT * FROM Users WHERE Email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $template = file_get_contents('../email_tamplate/password_reset.html');
        $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . '/eva/resetpassword.php?token=' . $token;

        // Replace placeholders in the template
        $template = str_replace('{{reset_link}}', $resetLink, $template);

        // Send verification email
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'yongahhock95@gmail.com';       // Replace with your Gmail address
            $mail->Password   = 'etrphuxvzfoivsiz';          // Replace with your Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  // Enable SSL encryption
            $mail->Port       = 465;

            // Recipients
            $mail->setFrom('yongahhock95@gmail.com', 'EVA Support - no reply');
            $mail->addAddress($email, $username);

            // Insert user into database
            $stmt = $pdo->prepare('UPDATE Users SET token = ? WHERE Email = ?');
            $stmt->execute([$token, $email]);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'EVA Password Reset Request';
            $mail->Body    = $template;

            $mail->send();
            $_SESSION['success_message'] = 'Password reset link has been sent to your email. Please check your inbox.';
            header('Location: ../index.php');
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            header('Location: ../index.php');

        }
        exit();
    } else {
        $_SESSION['error_message'] = "No account found with that email address.";
        header('Location: ../index.php');
        exit();
    }
}
?>