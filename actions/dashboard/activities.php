<?php
require_once __DIR__ . '/../../config/config.php';

/**
 * Get recent system activities for admin
 */
function getRecentSystemActivities($limit = 10) {
    $pdo = getDatabase();
    
    try {
        $stmt = $pdo->prepare('
            SELECT 
                "emergency" as type,
                CONCAT("Emergency call from ", d.Firstname, " ", d.Lastname) as title,
                CONCAT("Location: ", d.Address) as description,
                ch.Datetime as created_at,
                ch.SerialNoFK as device_id
            FROM Call_Histories ch
            INNER JOIN EVA e ON ch.SerialNoFK = e.SerialNoFK
            INNER JOIN Dependents d ON e.DependentIDFK = d.DependentID
            WHERE ch.Status IN ("Unanswered", "Resolved")
            
            UNION ALL
            
            SELECT 
                "device" as type,
                CONCAT("New device registered: ", e.SerialNoFK) as title,
                CONCAT("User: ", u.Email) as description,
                e.RegisteredDate as created_at,
                e.SerialNoFK as device_id
            FROM EVA e
            INNER JOIN Users u ON e.UserIDFK = u.UserID
            WHERE DATE(e.RegisteredDate) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            
            UNION ALL
            
            SELECT 
                "user" as type,
                CONCAT("New user registered: ", u.Email) as title,
                "User account created" as description,
                u.CreatedAt as created_at,
                NULL as device_id
            FROM Users u
            WHERE DATE(u.CreatedAt) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            AND u.CreatedAt IS NOT NULL
            
            ORDER BY created_at DESC
            LIMIT ?
        ');
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
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
                ch.Datetime as created_at,
                ch.SerialNoFK as device_id
            FROM Call_Histories ch
            INNER JOIN EVA e ON ch.SerialNoFK = e.SerialNoFK
            INNER JOIN Dependents d ON e.DependentIDFK = d.DependentID
            WHERE e.UserIDFK = ?
            
            UNION ALL
            
            SELECT 
                "device" as type,
                CONCAT("Device registered: ", e.SerialNoFK) as title,
                CONCAT("Assigned to: ", d.Firstname, " ", d.Lastname) as description,
                e.RegisteredDate as created_at,
                e.SerialNoFK as device_id
            FROM EVA e
            INNER JOIN Dependents d ON e.DependentIDFK = d.DependentID
            WHERE e.UserIDFK = ? AND DATE(e.RegisteredDate) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            
            ORDER BY created_at DESC
            LIMIT ?
        ');
        $stmt->execute([$userId, $userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Get user activities error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get activity icon based on type
 */
function getActivityIcon($type) {
    $icons = [
        'emergency' => 'bi-exclamation-triangle',
        'device' => 'bi-phone',
        'user' => 'bi-person',
        'system' => 'bi-gear'
    ];
    return $icons[$type] ?? 'bi-bell';
}

/**
 * Format time ago
 */
function formatTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    
    return date('M j, Y', strtotime($datetime));
}

/**
 * Get activity color class based on type
 */
function getActivityColorClass($type) {
    $colors = [
        'emergency' => 'text-danger',
        'device' => 'text-primary',
        'user' => 'text-success',
        'system' => 'text-info'
    ];
    return $colors[$type] ?? 'text-secondary';
}

/**
 * Get recent alerts for dashboard
 */
function getRecentAlerts($userId = null, $limit = 5) {
    $pdo = getDatabase();
    
    try {
        $sql = '
            SELECT 
                ch.RecordID,
                ch.SerialNoFK,
                ch.Datetime,
                ch.Status,
                ch.Direction,
                d.Firstname,
                d.Lastname,
                d.Address,
                u.Email
            FROM Call_Histories ch
            INNER JOIN EVA e ON ch.SerialNoFK = e.SerialNoFK
            INNER JOIN Dependents d ON e.DependentIDFK = d.DependentID
            INNER JOIN Users u ON e.UserIDFK = u.UserID
        ';
        
        $params = [];
        if ($userId !== null) {
            $sql .= ' WHERE e.UserIDFK = ?';
            $params[] = $userId;
        }
        
        $sql .= ' ORDER BY ch.Datetime DESC LIMIT ?';
        $params[] = $limit;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Get recent alerts error: " . $e->getMessage());
        return [];
    }
}
?>