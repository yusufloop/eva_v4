<?php
require 'config/config.php'; // Load all configurations

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    $token = generateToken();

    $_SESSION['email'] = $email;  // Store email in session

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWithMessage('../index.php', 'Invalid email format.', 'error');
    }

    // Password Validation
    if (!validatePassword($password)) {
        redirectWithMessage('../index.php', 
            'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters and include one uppercase letter, one number, and one special character.', 
            'error');
    }

    // Confirm password validation
    if ($password !== $confirmPassword) {
        redirectToAdminDashboard('Passwords do not match.');
        redirectWithMessage('../index.php', 'Passwords do not match.', 'error');
    }

    // Check if user exists
    $stmt = $pdo->prepare('SELECT * FROM Users WHERE Email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    if ($user && isset($_SESSION['admin_username'])) {
        $_SESSION['message'] = "Email already registered.";
        header('Location: ../admin_dashboard.php');
        exit();
    } else if ($user) {
        redirectWithMessage('../index.php', 'Email already registered.', 'error');
    } else {
        // Load email template
        $template = file_get_contents(EMAIL_TEMPLATE_PATH . 'validation_email.html');
        
        // Replace placeholders with dynamic data
        $verificationLink = BASE_URL . '/verify.php?token=' . $token;
        $template = str_replace('{{username}}', htmlspecialchars($email), $template);
        $template = str_replace('{{verification_link}}', htmlspecialchars($verificationLink), $template);

        // Send verification email
        $mail = new PHPMailer(true);

        try {
            // Server settings using config constants
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = SMTP_PORT;

            // Recipients
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($email, $email);

            // Insert user into database
            $stmt = $pdo->prepare('INSERT INTO Users (Email, Password, Token) VALUES (?, ?, ?)');
            $stmt->execute([$email, $password_hash, $token]);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email Address for ' . APP_NAME . ' Registration';
            $mail->Body    = $template;

            $mail->send();

            redirectToAdminDashboard('Registration successful!');
            redirectWithMessage('../index.php', 
                'Registration successful! Please check your email to verify your account.', 
                'success');

        } catch (Exception $e) {
            redirectToAdminDashboard("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            redirectWithMessage('../index.php', 
                "Message could not be sent. Mailer Error: {$mail->ErrorInfo}", 
                'error');
        }
    }
}
?>