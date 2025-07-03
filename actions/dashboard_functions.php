<?php
require_once '../config/config.php';

/**
 * Get admin dashboard statistics
 */
function getAdminDashboardData() {
    $pdo = getDatabase();
    
    try {
        // Get device statistics
        $stmt = $pdo->prepare('
            SELECT 
                COUNT(*) as total_devices,
                SUM(CASE WHEN DeviceStatus = "active" THEN 1 ELSE 0 END) as online_devices,
                SUM(CASE WHEN DeviceStatus = "inactive" THEN 1 ELSE 0 END) as offline_devices
            FROM EVA
        ');
        $stmt->execute();
        $deviceStats = $stmt->fetch();
        
        // Get active emergencies
        $stmt = $pdo->prepare('
            SELECT COUNT(*) as active_emergencies 
            FROM Call_Histories 
            WHERE Status IN ("Unanswered", "Active") 
            AND DATE(Datetime) = CURDATE()
        ');
        $stmt->execute();
        $emergencyStats = $stmt->fetch();
        
        return [
            'total_devices' => $deviceStats['total_devices'] ?? 0,
            'online_devices' => $deviceStats['online_devices'] ?? 0,
            'offline_devices' => $deviceStats['offline_devices'] ?? 0,
            'active_emergencies' => $emergencyStats['active_emergencies'] ?? 0
        ];
        
    } catch (PDOException $e) {
        error_log("Admin dashboard data error: " . $e->getMessage());
        return [
            'total_devices' => 0,
            'online_devices' => 0,
            'offline_devices' => 0,
            'active_emergencies' => 0
        ];
    }
}

/**
 * Get user dashboard statistics
 */
function getUserDashboardData($userId) {
    $pdo = getDatabase();
    
    try {
        // Get user's device statistics
        $stmt = $pdo->prepare('
            SELECT 
                COUNT(*) as total_devices,
                SUM(CASE WHEN DeviceStatus = "active" THEN 1 ELSE 0 END) as online_devices,
                SUM(CASE WHEN DeviceStatus = "inactive" THEN 1 ELSE 0 END) as offline_devices
            FROM EVA 
            WHERE UserIDFK = ?
        ');
        $stmt->execute([$userId]);
        $deviceStats = $stmt->fetch();
        
        // Get user's active emergencies
        $stmt = $pdo->prepare('
            SELECT COUNT(*) as active_emergencies 
            FROM Call_Histories ch
            INNER JOIN EVA e ON ch.SerialNoFK = e.SerialNoFK
            WHERE e.UserIDFK = ? 
            AND ch.Status IN ("Unanswered", "Active")
            AND DATE(ch.Datetime) = CURDATE()
        ');
        $stmt->execute([$userId]);
        $emergencyStats = $stmt->fetch();
        
        return [
            'total_devices' => $deviceStats['total_devices'] ?? 0,
            'online_devices' => $deviceStats['online_devices'] ?? 0,
            'offline_devices' => $deviceStats['offline_devices'] ?? 0,
            'active_emergencies' => $emergencyStats['active_emergencies'] ?? 0
        ];
        
    } catch (PDOException $e) {
        error_log("User dashboard data error: " . $e->getMessage());
        return [
            'total_devices' => 0,
            'online_devices' => 0,
            'offline_devices' => 0,
            'active_emergencies' => 0
        ];
    }
}




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




?>