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
                CONCAT("Emergency call from ", d.Firstname, " ", d.Lastname) as title,
                CONCAT("Location: ", d.Address) as description,
                ch.Datetime as created_at
            FROM Call_Histories ch
            INNER JOIN EVA e ON ch.SerialNoFK = e.SerialNoFK
            INNER JOIN Dependents d ON e.DependentIDFK = d.DependentID
            WHERE ch.Status IN ("Unanswered", "Resolved")
            
            UNION ALL
            
            SELECT 
                "device" as type,
                CONCAT("New device registered: ", e.SerialNoFK) as title,
                CONCAT("User: ", u.Email) as description,
                e.RegisteredDate as created_at
            FROM EVA e
            INNER JOIN Users u ON e.UserIDFK = u.UserID
            WHERE DATE(e.RegisteredDate) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            
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
                CONCAT("Emergency call from ", d.Firstname, " ", d.Lastname) as title,
                CONCAT("Device: ", e.SerialNoFK) as description,
                ch.Datetime as created_at
            FROM Call_Histories ch
            INNER JOIN EVA e ON ch.SerialNoFK = e.SerialNoFK
            INNER JOIN Dependents d ON e.DependentIDFK = d.DependentID
            WHERE e.UserIDFK = ?
            
            UNION ALL
            
            SELECT 
                "device" as type,
                CONCAT("Device registered: ", e.SerialNoFK) as title,
                CONCAT("Assigned to: ", d.Firstname, " ", d.Lastname) as description,
                e.RegisteredDate as created_at
            FROM EVA e
            INNER JOIN Dependents d ON e.DependentIDFK = d.DependentID
            WHERE e.UserIDFK = ? AND DATE(e.RegisteredDate) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            
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