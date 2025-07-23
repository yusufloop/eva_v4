<?php
require_once __DIR__ . '/../../config/config.php';

/**
 * Get recent system activities
 */
function getRecentSystemActivities($limit = 10) {
    $pdo = getDatabase();
    
    try {
        $stmt = $pdo->prepare('
            SELECT 
                "emergency" as type,
                CONCAT("Emergency call from ", d.fullname) as title,
                CONCAT("Location: ", d.address) as description,
                ch.call_date as created_at
            FROM call_histories ch
            INNER JOIN eva_info e ON ch.eva_id = e.eva_id
            INNER JOIN dependants d ON e.dep_id = d.dep_id
            WHERE ch.status IN ("Unanswered", "Resolved")
            
            UNION ALL
            
            SELECT 
                "device" as type,
                CONCAT("New device registered: ", i.serial_no) as title,
                CONCAT("User: ", u.email) as description,
                e.reg_date as created_at
            FROM eva_info e
            INNER JOIN users u ON e.user_id = u.user_id
            INNER JOIN inventory i ON e.inventory_id = i.inventory_id
            WHERE DATE(e.reg_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            
            ORDER BY created_at DESC
            LIMIT ?
        ');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Get recent activities error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get user recent activities
 */
function getUserRecentActivities($userId, $limit = 5) {
    $pdo = getDatabase();
    
    try {
        $stmt = $pdo->prepare('
            SELECT 
                "emergency" as type,
                CONCAT("Emergency call from ", d.fullname) as title,
                CONCAT("Device: ", i.serial_no) as description,
                ch.call_date as created_at
            FROM call_histories ch
            INNER JOIN eva_info e ON ch.eva_id = e.eva_id
            INNER JOIN dependants d ON e.dep_id = d.dep_id
            INNER JOIN inventory i ON e.inventory_id = i.inventory_id
            WHERE e.user_id = ?
            
            UNION ALL
            
            SELECT 
                "device" as type,
                CONCAT("Device registered: ", i.serial_no) as title,
                CONCAT("Assigned to: ", d.fullname) as description,
                e.reg_date as created_at
            FROM eva_info e
            INNER JOIN dependants d ON e.dep_id = d.dep_id
            INNER JOIN inventory i ON e.inventory_id = i.inventory_id
            WHERE e.user_id = ? AND DATE(e.reg_date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            
            ORDER BY created_at DESC
            LIMIT ?
        ');
        $stmt->execute([$userId, $userId, $limit]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Get user activities error: " . $e->getMessage());
        return [];
    }
}

/**
 * Helper function to get activity icon
 */
function getActivityIcon($type) {
    $icons = [
        'emergency' => 'exclamation-triangle',
        'device' => 'mobile-alt',
        'user' => 'user',
        'system' => 'cog'
    ];
    return $icons[$type] ?? 'bell';
}

/**
 * Helper function to format time ago
 */
function formatTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    
    return date('M j, Y', strtotime($datetime));
}