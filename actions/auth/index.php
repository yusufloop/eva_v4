<?php
/**
 * Authentication Actions Index
 * Centralized authentication handling
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

// Handle different authentication actions
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        require_once __DIR__ . '/login.php';
        break;
        
    case 'register':
        require_once __DIR__ . '/register.php';
        break;
        
    case 'logout':
        require_once __DIR__ . '/logout.php';
        break;
        
    case 'verify':
        require_once __DIR__ . '/verify.php';
        break;
        
    case 'forget_password':
        require_once __DIR__ . '/forget-password.php';
        break;
        
    case 'reset_password':
        require_once __DIR__ . '/reset-password.php';
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Authentication action not found']);
        break;
}