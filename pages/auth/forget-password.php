<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - EVA Emergency Voice Alert</title>
    <link rel="stylesheet" href="/assets/css/eva-login.css">
    <link rel="stylesheet" href="/assets/css/forget-password.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
</head>
<body>
    <div class="forgot-password-container">
        <!-- Left Panel - EVA Branding -->
        <div class="forgot-password-left">
            <div class="cityscape-bg"></div>
            <div class="forgot-password-branding">
                <div class="eva-logo">
                    <div class="logo-circle">
                        <i class="fas fa-key"></i>
                    </div>
                </div>
                <h1>Password Recovery1</h1>
                <p>Secure account recovery for your Emergency Voice Alert Management Platform. We'll help you regain access quickly and safely.</p>
            </div>
        </div>

        <!-- Right Panel - Forgot Password Form -->
        <div class="forgot-password-right">
            <div class="forgot-password-form-container">
                <a href="/index.php" class="back-to-login">
                    <i class="fas fa-arrow-left"></i>
                    Back to Login
                </a>

                <div id="forgot-password-form" class="form-wrapper">
                    <h2>Forgot Password?</h2>
                    <p class="form-subtitle">Enter your email address and we'll send you a secure link to reset your password.</p>

                    <!-- Messages -->
                    <?php
                   
                    $error = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
                    $success = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
                    $email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
                    
                    // Clear messages after displaying
                    unset($_SESSION['error_message'], $_SESSION['success_message'], $_SESSION['email']);
                    ?>

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

                    <form action="/actions/auth/forget-password.php" method="POST" class="auth-form">
                        <div class="input-group">
                            <label for="email">Email Address</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       placeholder="Enter your registered email" 
                                       value="<?php echo htmlspecialchars($email); ?>"
                                       required>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn" id="submit-btn">
                            <span>Send Reset Link</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>

                <!-- Success State (Hidden by default) -->
                <div id="success-state" class="success-state">
                    <div class="success-icon">
                        <i class="fas fa-envelope-circle-check"></i>
                    </div>
                    <h3>Check Your Email</h3>
                    <p>We've sent a password reset link to your email address. Please check your inbox and follow the instructions to reset your password.</p>
                    <a href="index.php" class="submit-btn">
                        <span>Back to Login</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Enhanced form submission with loading state
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submit-btn');
            const span = submitBtn.querySelector('span');
            const icon = submitBtn.querySelector('i');
            
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            span.textContent = 'Sending...';
            icon.className = 'fas fa-spinner fa-spin';
            
            // Add visual feedback
            submitBtn.style.background = 'linear-gradient(135deg, #9ca3af, #6b7280)';
        });

        // Show success state if there's a success message
        <?php if (!empty($success)): ?>
        setTimeout(() => {
            document.getElementById('forgot-password-form').style.display = 'none';
            document.getElementById('success-state').classList.add('active');
        }, 2500);
        <?php endif; ?>

        // Auto-hide success messages after 10 seconds
        setTimeout(() => {
            const successMessages = document.querySelectorAll('.message.success');
            successMessages.forEach(message => {
                message.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                message.style.opacity = '0';
                message.style.transform = 'translateY(-10px)';
                setTimeout(() => message.remove(), 500);
            });
        }, 10000);

        // Enhanced focus management
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            if (emailInput && !emailInput.value) {
                setTimeout(() => emailInput.focus(), 100);
            }

            // Add floating label effect
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });

                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });

                // Check if input has value on load
                if (input.value) {
                    input.parentElement.classList.add('focused');
                }
            });

            // Add subtle animations
            const container = document.querySelector('.forgot-password-form-container');
            container.style.opacity = '0';
            container.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                container.style.transition = 'all 0.6s ease-out';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);
        });

        // Email validation enhancement
        document.getElementById('email').addEventListener('input', function() {
            const email = this.value;
            const submitBtn = document.getElementById('submit-btn');
            const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            
            if (email.length > 0) {
                if (isValid) {
                    this.style.borderColor = '#10b981';
                    submitBtn.style.opacity = '1';
                    submitBtn.disabled = false;
                } else {
                    this.style.borderColor = '#ef4444';
                    submitBtn.style.opacity = '0.7';
                    submitBtn.disabled = true;
                }
            } else {
                this.style.borderColor = '';
                submitBtn.style.opacity = '1';
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>