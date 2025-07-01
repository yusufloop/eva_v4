<?php


$result = $_SESSION['verification_result'] ?? null;
unset($_SESSION['verification_result']); // Clear after getting

// Default error message if no result data
$message = $result['message'] ?? 'Verification failed!';
$submessage = $result['submessage'] ?? 'Please try again or contact support.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Failed - EVA System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/verification.css">
</head>
<body>
    <div class="verification-container">
        <div class="verification-card">
            <div class="icon error">
                <i class="fas fa-times-circle"></i>
            </div>
            
            <h1 class="title">Verification Failed</h1>
            
            <div class="message error">
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
                    Back to Login
                </a>
                <a href="/auth/resend-verification" class="btn btn-secondary">
                    <i class="fas fa-envelope"></i>
                    Resend Verification
                </a>
            </div>
            
            <div class="troubleshooting">
                <h3>Need Help?</h3>
                <ul>
                    <li>Check your email for the latest verification link</li>
                    <li>Make sure you clicked the complete link from your email</li>
                    <li>Try registering again if the link has expired</li>
                    <li>Contact support if the problem persists</li>
                </ul>
            </div>
            
            <div class="footer">
                <p>EVA System Support</p>
            </div>
        </div>
    </div>
</body>
</html>