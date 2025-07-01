<?php



$result = $_SESSION['verification_result'] ?? null;
unset($_SESSION['verification_result']); // Clear after getting

// If no result data, redirect to login
if (!$result || !$result['success']) {
    header('Location: /login');
    exit();
}

$message = $result['message'] ?? 'Email verified successfully!';
$submessage = $result['submessage'] ?? 'You can now login to your account.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified - EVA System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/verification.css">
</head>
<body>
    <div class="verification-container">
        <div class="verification-card">
            <div class="icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1 class="title">Email Verified!</h1>
            
            <div class="message success">
                <?= htmlspecialchars($message) ?>
            </div>
            
            <?php if ($submessage): ?>
                <div class="submessage">
                    <?= htmlspecialchars($submessage) ?>
                </div>
            <?php endif; ?>
            
            <div class="actions">
                <a href="/login" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Go to Login
                </a>
                <a href="/dashboard" class="btn btn-secondary">
                    <i class="fas fa-tachometer-alt"></i>
                    Go to Dashboard
                </a>
            </div>
            
            <div class="footer">
                <p>Welcome to EVA System!</p>
            </div>
        </div>
    </div>
    
    <!-- Auto redirect after 5 seconds -->
    <script>
        setTimeout(function() {
            window.location.href = '/login';
        }, 5000);
    </script>
</body>
</html>