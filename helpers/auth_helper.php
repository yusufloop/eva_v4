<?php
// Authentication Helper Functions

/**
 * Check if user is logged in and redirect if not
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: ../../index.php');
        exit();
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['username']) || isset($_SESSION['Email']);
}

/**
 * Get current user role
 */
function getUserRole() {
    if (isset($_SESSION['Email'])):
        return 'admin';
    elseif (isset($_SESSION['Email'])):
        return 'user';
    else:
        return 'guest';
    endif;
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    $userRole = getUserRole();
    
    if ($role === 'user'):
        return $userRole === 'user' || $userRole === 'admin'; // Admin can access user pages
    elseif ($role === 'admin'):
        return $userRole === 'admin';
    else:
        return false;
    endif;
}

/**
 * Check if current user is admin
 */
function isAdmin() {
    return hasRole('admin');
}

/**
 * Check if current user is regular user
 */
function isUser() {
    return getUserRole() === 'user';
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
   
    return $_SESSION['UserID'] ?? null;
}

/**
 * Get current user data
 */
function getCurrentUserEmail() {
    
    return $_SESSION['Email'] ?? null;
}



/**
 * Login user and set session data
 */
function loginUser($user) {
    // Set common session data
    $_SESSION['UserID'] = $user['UserID'];
    $_SESSION['user_data'] = $user;
    
    // Check if user is admin
    if ($user['IsAdmin'] == 1 || $user['IsAdmin'] === "Yes"):
        // Admin login
        $_SESSION['admin_username'] = $user['Email'];
        $_SESSION['IsAdmin'] = $user['IsAdmin'];
        return 'admin';
    else:
        // Regular user login
        $_SESSION['username'] = $user['Email'];
        return 'user';
    endif;
}

/**
 * Logout user and clear session
 */
function logoutUser() {
    // Clear all session data
    unset($_SESSION['UserID']);
    unset($_SESSION['user_data']);
    unset($_SESSION['username']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['IsAdmin']);
    
    // Destroy session completely
    session_destroy();
    session_start(); // Restart for flash messages
}

/**
 * Redirect based on user role
 */
function redirectBasedOnRole() {
    if (hasRole('admin')):
        header("Location: /admin");
    elseif (hasRole('user')):
        header("Location: /dashboard");
    else:
        header("Location: /login");
    endif;
    exit();
}

/**
 * Redirect user if already logged in
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        redirectBasedOnRole();
    }
}

/**
 * Check if user can access admin features
 */
function canAccessAdmin() {
    return hasRole('admin');
}

/**
 * Check if user can manage other users
 */
function canManageUsers() {
    return hasRole('admin');
}

/**
 * Check if user can view all data (admin) or only own data
 */
function canViewAllData() {
    return hasRole('admin');
}

/**
 * Get accessible user ID (for data filtering)
 * Returns null for admin (can see all), UserID for regular users
 */
function getAccessibleUserId() {
    if (hasRole('admin')):
        return null; // Admin can see all data
    else:
        return getCurrentUserId();
    endif;
}

/**
 * Validate login credentials
 */
function validateLoginCredentials($email, $password) {
    // Basic validation
    if (empty($email) || empty($password)):
        throw new Exception('Please fill in all fields');
    endif;
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)):
        throw new Exception('Invalid email format');
    endif;
    
    return true;
}

/**
 * Authenticate user from database
 */
function authenticateUser($email, $password) {
    $pdo = getDatabase();
    
    try {
        // Check for user from database
        $stmt = $pdo->prepare('SELECT UserID, Email, Password, IsAdmin, IsVerified FROM Users WHERE Email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Check if user exists and password is correct
        if (!$user || !password_verify($password, $user['Password'])):
            throw new Exception('Invalid credentials');
        endif;
        
        // Check if email is verified
        if ($user['IsVerified'] == 0):
            throw new Exception('Please verify your email before logging in');
        endif;
        
        return $user;
        
    } catch (PDOException $e) {
        error_log("Authentication error: " . $e->getMessage());
        throw new Exception('System error. Please try again later');
    }
}

/**
 * Handle complete login process
 */
function handleLogin($email, $password) {
    // Validate input
    validateLoginCredentials($email, $password);
    
    // Authenticate user
    $user = authenticateUser($email, $password);
    
    // Login user (set session data)
    $userRole = loginUser($user);
    
    return [
        'success' => true,
        'user' => $user,
        'role' => $userRole
    ];
}

/**
 * Set flash message for authentication
 */
function setAuthMessage($message, $type = 'error') {
    if ($type === 'error'):
        $_SESSION['login_error_message'] = $message;
    else:
        $_SESSION['success_message'] = $message;
    endif;
}

/**
 * Display authentication messages
 */
function displayAuthMessages() {
    $error = $_SESSION['error_message'] ?? '';
    $login_error = $_SESSION['login_error_message'] ?? '';
    $success = $_SESSION['success_message'] ?? '';
    
    // Clear messages after getting them
    unset($_SESSION['error_message'], $_SESSION['login_error_message'], $_SESSION['success_message']);
    
    return [
        'error' => $error,
        'login_error' => $login_error,
        'success' => $success
    ];
}

/**
 * Get user's saved email (for forms)
 */
function getSavedEmail() {
    $email = $_SESSION['email'] ?? '';
    unset($_SESSION['email']);
    return $email;
}
?>