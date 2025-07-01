<?php
// Include the router class
require_once 'config/router.php';

// Initialize Router
$router = new SimpleRouter();

// ============================================
// PUBLIC ROUTES (No authentication required)
// ============================================
$router->addRoute('', 'pages/auth/index.php');                    // Home page (redirect to login if not authenticated)
$router->addRoute('login', 'pages/auth/index.php');               // Login page
$router->addRoute('register', 'pages/auth/index.php');            // Register page
$router->addRoute('forgot', 'pages/auth/forgot.php');             // Forgot password
$router->addRoute('reset', 'pages/auth/reset.php');               // Reset password
$router->addRoute('verify', 'verify.php');                        // Email verification
$router->addRoute('verify/success', 'pages/auth/verify_success.php'); // Success page
$router->addRoute('verify/error', 'pages/auth/verify_error.php');     // Error page
$router->addRoute('auth/verify', 'functions/auth_functions.php');     // Verification processing

// ============================================
// AUTHENTICATION ACTION ROUTES
// ============================================
$router->addRoute('auth/login', 'functions/auth_functions.php');      // Login processing
$router->addRoute('auth/register', 'functions/auth_functions.php');   // Register processing
$router->addRoute('auth/logout', 'functions/auth_functions.php');     // Logout processing
$router->addRoute('auth/forgot', 'functions/auth_functions.php');     // Forgot password processing
$router->addRoute('auth/reset', 'functions/auth_functions.php');      // Reset password processing

// ============================================
// USER ROUTES (Requires user or admin role)
// ============================================
$router->addRoute('dashboard', 'pages/dashboard.php', 'user');        // User dashboard
$router->addRoute('devices', 'pages/devices.php', 'user');            // My devices
$router->addRoute('family', 'pages/family.php', 'user');              // My family members
$router->addRoute('call-logs', 'pages/call_logs.php', 'user');        // My call history
$router->addRoute('alerts', 'pages/alerts.php', 'user');              // My alert history
$router->addRoute('settings', 'pages/settings.php', 'user');          // User settings
$router->addRoute('profile', 'pages/profile.php', 'user');            // User profile

// ============================================
// USER ACTION ROUTES (Form processing)
// ============================================
$router->addRoute('device/add', 'functions/device_functions.php', 'admin');        // Add device
$router->addRoute('device/edit/*', 'functions/device_functions.php', 'user');     // Edit device
$router->addRoute('device/delete/*', 'functions/device_functions.php', 'user');   // Delete device
$router->addRoute('device/view/*', 'functions/device_functions.php', 'user');     // View device details

$router->addRoute('family/add', 'functions/dependent_functions.php', 'user');     // Add family member
$router->addRoute('family/edit/*', 'functions/dependent_functions.php', 'user');  // Edit family member
$router->addRoute('family/delete/*', 'functions/dependent_functions.php', 'user'); // Delete family member

$router->addRoute('user/update', 'functions/user_functions.php', 'user');         // Update profile
$router->addRoute('user/change-password', 'functions/user_functions.php', 'user'); // Change password

// ============================================
// ADMIN ROUTES (Requires admin role)
// ============================================
$router->addRoute('admin', 'pages/dashboard.php', 'admin');
$router->addRoute('admin/dashboard', 'pages/dashboard.php', 'admin');

$router->addRoute('admin/devices', 'pages/devices.php', 'admin');          // All devices management
$router->addRoute('admin/users', 'pages/users.php', 'admin');              // User management
$router->addRoute('admin/family', 'pages/family.php', 'admin');            // All family members
$router->addRoute('admin/inventory', 'pages/inventory.php', 'admin');      // Device inventory
$router->addRoute('admin/call-logs', 'pages/call_logs.php', 'admin');      // All call logs
$router->addRoute('admin/alerts', 'pages/alerts.php', 'admin');            // All system activities
$router->addRoute('admin/settings', 'pages/settings.php', 'admin');        // System settings

// ============================================
// ADMIN ACTION ROUTES
// ============================================
$router->addRoute('admin/device/add', 'functions/device_functions.php', 'admin');        // Admin add device
$router->addRoute('admin/device/edit/*', 'functions/device_functions.php', 'admin');     // Admin edit device
$router->addRoute('admin/device/delete/*', 'functions/device_functions.php', 'admin');   // Admin delete device
$router->addRoute('admin/device/assign/*', 'functions/device_functions.php', 'admin');   // Admin assign device

$router->addRoute('admin/user/add', 'functions/user_functions.php', 'admin');            // Admin add user
$router->addRoute('admin/user/edit/*', 'functions/user_functions.php', 'admin');         // Admin edit user
$router->addRoute('admin/user/delete/*', 'functions/user_functions.php', 'admin');       // Admin delete user
$router->addRoute('admin/user/activate/*', 'functions/user_functions.php', 'admin');     // Admin activate user
$router->addRoute('admin/user/deactivate/*', 'functions/user_functions.php', 'admin');   // Admin deactivate user

$router->addRoute('admin/inventory/add', 'functions/inventory_functions.php', 'admin');     // Admin add inventory
$router->addRoute('admin/inventory/edit/*', 'functions/inventory_functions.php', 'admin'); // Admin edit inventory
$router->addRoute('admin/inventory/import', 'functions/inventory_functions.php', 'admin'); // Admin import CSV

$router->addRoute('admin/family/edit/*', 'functions/dependent_functions.php', 'admin');    // Admin edit family member
$router->addRoute('admin/family/delete/*', 'functions/dependent_functions.php', 'admin');  // Admin delete family member

// ============================================
// EXPORT ROUTES (Role-based access)
// ============================================
$router->addRoute('export/devices', 'functions/export_functions.php', 'user');        // Export user devices
$router->addRoute('export/call-logs', 'functions/export_functions.php', 'user');      // Export user call logs
$router->addRoute('admin/export/devices', 'functions/export_functions.php', 'admin'); // Export all devices
$router->addRoute('admin/export/users', 'functions/export_functions.php', 'admin');   // Export all users
$router->addRoute('admin/export/call-logs', 'functions/export_functions.php', 'admin'); // Export all call logs

// ============================================
// API ROUTES (Future use - leave empty for now)
// ============================================
// $router->addRoute('api/v1/devices', 'api/v1/devices.php', 'user');           // Device API
// $router->addRoute('api/v1/users', 'api/v1/users.php', 'admin');              // User API (admin only)
// $router->addRoute('api/v1/call-logs', 'api/v1/call_logs.php', 'user');       // Call logs API




// Return the configured router
return $router;
?>