<?php
/**
 * Dashboard Actions Index
 * Main entry point for dashboard-related actions
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';
require_once __DIR__ . '/stats.php';
require_once __DIR__ . '/activities.php';

// Check authentication
requireAuth();

// Get current user ID and role
$userId = getCurrentUserId();
$isAdmin = hasRole('admin');

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get_stats':
            if ($isAdmin) {
                $stats = getAdminDashboardData();
            } else {
                $stats = getUserDashboardData($userId);
            }
            echo json_encode($stats);
            break;
            
        case 'get_activities':
            $limit = $_POST['limit'] ?? 10;
            
            if ($isAdmin) {
                $activities = getRecentSystemActivities($limit);
            } else {
                $activities = getUserRecentActivities($userId, $limit);
            }
            echo json_encode($activities);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
    exit;
}

// If no valid request method, return error
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);