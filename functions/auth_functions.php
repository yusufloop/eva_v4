<?php

require_once './helpers/auth_helper.php';
require_once './vendor/autoload.php';
require_once './config/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$action = $_GET['action'] ?? '';
$requestMethod = $_SERVER['REQUEST_METHOD'];

if (empty($action)) {
    $uri = $_SERVER['REQUEST_URI'];
    $parts = explode('/', trim($uri, '/'));
    $action = $parts[1] ?? '';
}


try {
    switch ($action) {
        case 'login':
            handleLoginAction();
            break;
        case 'register';
            handleRegisterAction();
            break;
        case 'logout':
            handleLogoutAction();
            break;
        case 'forgot':
            handleForgotPasswordAction();
            break;
        case 'reset':
            handleResetPasswordAction();
            break;
        case 'verify':
            handleEmailVerificationAction();
            break;
        default:
            header('Location: ../index.php');
            exit();
    }
} catch (Exception $e) {
    setAuthMessage($e->getMessage(), 'error');
    header('Location: /login');
    exit();
}

function handleLoginAction()
{
    $pdo = getDatabase();

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

                    redirectWithMessage('../dashboard.php', 'Welcome back, Administrator!', 'success');
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
}

function handleRegisterAction()
{
    $pdo = getDatabase();

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
            redirectWithMessage(
                '../index.php',
                'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters and include one uppercase letter, one number, and one special character.',
                'error'
            );
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
            header('Location: ../dashboard.php');
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
                redirectWithMessage(
                    '../index.php',
                    'Registration successful! Please check your email to verify your account.',
                    'success'
                );
            } catch (Exception $e) {
                redirectToAdminDashboard("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                redirectWithMessage(
                    '../index.php',
                    "Message could not be sent. Mailer Error: {$mail->ErrorInfo}",
                    'error'
                );
            }
        }
    }
}

function handleLogoutAction()
{
    
    session_destroy();
    header("Location: ../index.php");
    exit();
}

function handleForgotPasswordAction(){
    $pdo = getDatabase();

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
}

function handleResetPasswordAction(){
    $pdo = getDatabase();
    
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
}

function handleEmailVerificationAction(){
    $pdo = getDatabase();

    if (isset($_GET['token'])) {
        $token = $_GET['token'];

        try {
            // Find user with the provided token
            $stmt = $pdo->prepare('SELECT * FROM Users WHERE Token = ?');
            $stmt->execute([$token]);
            $user = $stmt->fetch();

            if ($user) {
                // Update user to set IsVerified = 1 and clear the token
                $stmt = $pdo->prepare('UPDATE Users SET IsVerified = 1, Token = "" WHERE Token = ?');
                $stmt->execute([$token]);

                // Set success message and redirect to success page
                $_SESSION['verification_result'] = [
                    'success' => true,
                    'message' => 'Your email has been verified successfully!',
                    'submessage' => 'You can now login to your account.'
                ];
                header('Location: /verify/success');
                exit();
            } else {
                // Invalid token
                $_SESSION['verification_result'] = [
                    'success' => false,
                    'message' => 'Invalid verification token!',
                    'submessage' => 'Please check the verification link or contact support.'
                ];
                header('Location: /verify/error');
                exit();
            }
        } catch (PDOException $e) {
            error_log("Email verification error: " . $e->getMessage());
            $_SESSION['verification_result'] = [
                'success' => false,
                'message' => 'An error occurred during verification.',
                'submessage' => 'Please try again or contact support.'
            ];
            header('Location: /verify/error');
            exit();
        }
    } else {
        // No token provided
        $_SESSION['verification_result'] = [
            'success' => false,
            'message' => 'No verification token provided!',
            'submessage' => 'Please check your email for the verification link.'
        ];
        header('Location: /verify/error');
        exit();
    }
}
