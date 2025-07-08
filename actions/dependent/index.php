<?php
/**
 * Dependent Actions Index
 * Main entry point for dependent-related actions
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

// Check authentication
requireAuth();

// Get current user ID and role
$userId = getCurrentUserId();
$isAdmin = hasRole('admin');

// Handle different dependent actions
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'create':
        require_once __DIR__ . '/create.php';
        break;
        
    case 'update':
        require_once __DIR__ . '/update.php';
        break;
        
    case 'delete':
        require_once __DIR__ . '/delete.php';
        break;
        
    case 'list':
        require_once __DIR__ . '/list.php';
        break;
        
    default:
        // If no action specified, handle based on request method
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require_once __DIR__ . '/list.php';
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Dependent action not found']);
        }
        break;
}