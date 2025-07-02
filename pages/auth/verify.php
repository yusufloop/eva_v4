<?php 
require 'actions/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Status</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css?family=Montserrat:400,800');
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f6f5f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        .message {
            font-size: 1.2rem;
            margin: 20px 0;
        }
        .success {
            color: #28a745;
        }
        .error {
            color: #dc3545;
        }
        .icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        a {
            text-decoration: none;
            color: #007bff;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        if (isset($_GET['token'])) {
            $token = $_GET['token'];

            $stmt = $pdo->prepare('SELECT * FROM Users WHERE Token = ?');
            $stmt->execute([$token]);
            $user = $stmt->fetch();

            if ($user) {
                // Update user to set is_verified = 1 and clear the token
                $stmt = $pdo->prepare('UPDATE Users SET IsVerified = 1, token = "" WHERE token = ?');
                $stmt->execute([$token]);
                ?>
                <div class="icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="message success">
                    Your email has been verified successfully!<br>
                    You can now <a href="index.php">login</a>.
                </div>
                <?php
            } else {
                ?>
                <div class="icon error">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="message error">
                    Invalid token! Please check the verification link or contact support.
                </div>
                <?php
            }
        } else {
            ?>
            <div class="icon error">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="message error">
                No token provided! Please check your email for the verification link.
            </div>
            <?php
        }
        ?>
    </div>
</body>
</html>
