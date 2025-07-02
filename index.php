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
<html>
<head>
    <title>EVA</title>
    <link rel="stylesheet" href="/assets/css/index.css" type="text/css">
</head>
<body>
    <div class="container <?php echo !empty($error) ? 'right-panel-active' : ''; ?>" id="container">
        <div class="form-container sign-up-container">
            <form id="signupForm" action="/actions/auth/register.php" method="POST" onsubmit="return validateForm();">
                <h1>Create Account</h1>
                <?php if (!empty($error)): ?>
                    <p id="error-message" style="color: red;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <input type="text" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required />
                <input type="password" class="password" id="newPassword" name="newPassword" placeholder="Password" required/>
                <input type="password" class="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required/>
                <div id="strong"><span></span></div>
                <div id="valid"></div>
                <small>Must be 6+ characters long and contain at least 1 upper case letter, 1 number, 1 special character</small>
                <button id="signupButton" type="submit">Sign Up</button>
                
                <button class="ghost" id="signIn">Sign In</button>
            </form>
        </div>
        <div class="form-container sign-in-container">
    <form id="loginForm" action="/actions/auth/login.php" method="POST">
        <h1>Log In</h1>
        
        <!-- Success/Error Messages -->
        <?php if (!empty($success)): ?>
            <div class="message success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($login_error)): ?>
            <div class="message error-message">
                <?php echo htmlspecialchars($login_error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Email Field -->
        <div class="input-group">
            <label for="login-email" class="input-label">Email</label>
            <input type="email" 
                   id="login-email" 
                   name="login-email" 
                   placeholder="Your Email" 
                   value="<?php echo htmlspecialchars($email); ?>" 
                   required/>
        </div>
        
        <!-- Password Field -->
        <div class="input-group">
            <label for="login-password" class="input-label">Password</label>
            <input type="password" 
                   id="login-password" 
                   name="login-password" 
                   placeholder="Your Password" 
                   required/>
        </div>
        
        <!-- Forgot Password Link -->
        <a href="/auth/forgot-password" class="forgot-password-link">
            Forgot your password?
        </a>
        
        <!-- Login Button -->
        <button type="submit" class="btn-primary" id="loginButton">
            Log In
        </button>
        
        <!-- Sign Up Link -->
        <div class="signup-section">
            <p class="signup-text">Don't have an account?</p>
            <a href="#" class="signup-link" id="signUp">Sign Up</a>
        </div>
    </form>
</div>


        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>Welcome Back!</h1>
                    <p>To keep connected with us please login with your personal info</p>
                    
                </div>
                <div class="overlay-panel overlay-right">
                    <h1>Voice Emergency Alert Management Platform</h1>
                    <p>Enter your personal details and start journey with us</p>
                    
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/index.js"></script>
    <script src="/vendor/jquery-3.6.0.min.js"></script>
    <script src="/assets/js/PasswordStrengthValidator.js"></script>
</body>
</html>