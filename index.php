<?php
session_start();
$error = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$login_error = isset($_SESSION['login_error_message']) ? $_SESSION['login_error_message'] : '';
$success = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Clear session after displaying
unset($_SESSION['error_message'], $_SESSION['login_error_message'], $_SESSION['success_message'], $_SESSION['email']); 

// If already logged in, redirect
if (isset($_SESSION['admin_username'])) {
    header("Location: /admin");
    exit();
} else if (isset($_SESSION['username'])) {
    header("Location: /dashboard");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EVA - Emergency Voice Alert Management Platform</title>
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
                <h1>Emergency Voice Alert<br>Management Platform</h1>
                
                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-wifi"></i>
                        <span>Real-Time Device Monitoring</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-phone-alt"></i>
                        <span>Emergency Response Coordination</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Reporting and Logging</span>
                    </div>
                </div>
            </div>
            
            <!-- Background cityscape overlay -->
            <div class="cityscape-overlay"></div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="right-panel">
            <div class="login-form-container">
                <!-- Toggle Buttons -->
                <div class="form-toggle">
                    <button class="toggle-btn active" id="loginToggle">Login</button>
                    <button class="toggle-btn" id="signupToggle">Sign Up</button>
                </div>

                <!-- Login Form -->
                <div class="form-wrapper" id="loginForm">
                    <form action="/actions/auth/login.php" method="POST" class="auth-form">
                        <h2>Welcome Back</h2>
                        <p class="form-subtitle">Sign in to your account</p>

                        <!-- Messages -->
                        <?php if (!empty($success)): ?>
                            <div class="message success">
                                <i class="fas fa-check-circle"></i>
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($login_error)): ?>
                            <div class="message error">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo htmlspecialchars($login_error); ?>
                            </div>
                        <?php endif; ?>

                        <div class="input-group">
                            <label for="login-email">Email</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" 
                                       id="login-email" 
                                       name="login-email" 
                                       placeholder="Your email" 
                                       value="<?php echo htmlspecialchars($email); ?>" 
                                       required>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="login-password">Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" 
                                       id="login-password" 
                                       name="login-password" 
                                       placeholder="Your password" 
                                       required>
                                <button type="button" class="password-toggle" onclick="togglePassword('login-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="remember-me">
                                <input type="checkbox" name="remember">
                                <span class="checkmark"></span>
                                Remember me
                            </label>
                            <a href="/auth/forgot-password" class="forgot-password">Forgot password?</a>
                        </div>

                        <button type="submit" class="submit-btn">
                            <span>Sign In</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>
                </div>

                <!-- Signup Form -->
                <div class="form-wrapper hidden" id="signupForm">
                    <form action="/actions/auth/register.php" method="POST" class="auth-form" onsubmit="return validateSignupForm()">
                        <h2>Create Account</h2>
                        <p class="form-subtitle">Join the EVA platform</p>

                        <!-- Messages for signup -->
                        <?php if (!empty($error)): ?>
                            <div class="message error">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <div class="input-group">
                            <label for="signup-email">Email</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" 
                                       id="signup-email" 
                                       name="email" 
                                       placeholder="Your email" 
                                       value="<?php echo htmlspecialchars($email); ?>" 
                                       required>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="signup-password">Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" 
                                       id="signup-password" 
                                       name="newPassword" 
                                       placeholder="Create password" 
                                       required>
                                <button type="button" class="password-toggle" onclick="togglePassword('signup-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="strength-bar">
                                    <div class="strength-fill"></div>
                                </div>
                                <span class="strength-text">Password strength</span>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="confirm-password">Confirm Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" 
                                       id="confirm-password" 
                                       name="confirmPassword" 
                                       placeholder="Confirm password" 
                                       required>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="password-requirements">
                            <p>Password must contain:</p>
                            <ul>
                                <li id="length-req"><i class="fas fa-times"></i> At least 6 characters</li>
                                <li id="uppercase-req"><i class="fas fa-times"></i> One uppercase letter</li>
                                <li id="number-req"><i class="fas fa-times"></i> One number</li>
                                <li id="special-req"><i class="fas fa-times"></i> One special character</li>
                            </ul>
                        </div>

                        <button type="submit" class="submit-btn">
                            <span>Create Account</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/modern-login.js"></script>
</body>
</html>