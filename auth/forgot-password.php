<?php
session_start();
$error = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$success = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';

// Clear messages after displaying
unset($_SESSION['error_message'], $_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - EVA</title>
    <link rel="stylesheet" href="/assets/css/modern-login.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <!-- Left Panel - EVA Information -->
        <div class="left-panel">
            <div class="eva-branding">
                <div class="eva-logo">
                    <div class="logo-circle">
                        <span>EVA</span>
                    </div>
                </div>
                <h1>Password Recovery</h1>
                <p style="font-size: 18px; margin-top: 20px; opacity: 0.9;">
                    Don't worry, we'll help you get back into your account quickly and securely.
                </p>
            </div>
            <div class="cityscape-overlay"></div>
        </div>

        <!-- Right Panel - Forgot Password Form -->
        <div class="right-panel">
            <div class="login-form-container">
                <div class="form-wrapper">
                    <form action="/actions/auth/forget-password.php" method="POST" class="auth-form">
                        <h2>Forgot Password?</h2>
                        <p class="form-subtitle">Enter your email address and we'll send you a link to reset your password.</p>

                        <!-- Messages -->
                        <?php if (!empty($success)): ?>
                            <div class="message success">
                                <i class="fas fa-check-circle"></i>
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error)): ?>
                            <div class="message error">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <div class="input-group">
                            <label for="email">Email Address</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       placeholder="Enter your email address" 
                                       required>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn">
                            <span>Send Reset Link</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>

                        <div style="text-align: center; margin-top: 24px;">
                            <a href="/login" class="forgot-password">
                                <i class="fas fa-arrow-left"></i> Back to Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add loading state to form submission
        document.querySelector('form').addEventListener('submit', function() {
            const submitBtn = this.querySelector('.submit-btn');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });

        // Auto-hide success messages
        setTimeout(() => {
            const successMessages = document.querySelectorAll('.message.success');
            successMessages.forEach(message => {
                message.style.transition = 'opacity 0.5s ease';
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 500);
            });
        }, 8000);
    </script>
</body>
</html>