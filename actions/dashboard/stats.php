<?php
require_once __DIR__ . '/../../config/config.php';

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
                SUM(CASE WHEN DeviceStatus = "Active" THEN 1 ELSE 0 END) as online_devices,
                SUM(CASE WHEN DeviceStatus = "Inactive" THEN 1 ELSE 0 END) as offline_devices
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
                SUM(CASE WHEN DeviceStatus = "Active" THEN 1 ELSE 0 END) as online_devices,
                SUM(CASE WHEN DeviceStatus = "Inactive" THEN 1 ELSE 0 END) as offline_devices
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