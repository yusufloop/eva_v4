<?php
// Device Management Functions

require_once __DIR__ . '/../config/config.php';

function getAllCallHistories() {
    $pdo = getDatabase(); 
    try {
        $stmt = $pdo->prepare('
            SELECT 
                ch.RecordID, 
                ch.SerialNoFK, 
                ch.Datetime, 
                ch.Number,
                ch.Direction,
                ch.Status,
                ch.Duration,
                d.Address, 
                d.Firstname,
                d.Lastname,
                d.Gender,
                d.DOB,
                d.MedicalCondition,
                u.Email as UserEmail,
                e.EmergencyNo1,
                e.EmergencyNo2,
                e.RegisteredDate,
                e.DeviceStatus,
                e.LastOnline
            FROM Call_Histories ch 
            LEFT JOIN EVA e ON ch.SerialNoFK = e.SerialNoFK
            LEFT JOIN Dependents d ON e.DependentIDFK = d.DependentID
            LEFT JOIN Users u ON e.UserIDFK = u.UserID 
            ORDER BY ch.Datetime DESC
        ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get All Call Histories Error: " . $e->getMessage());
        return [];
    }
}

// Alternative function to get call histories for a specific user
function getUserCallHistories($userId) {
    $pdo = getDatabase(); 
    try {
        $stmt = $pdo->prepare('
            SELECT 
                ch.RecordID, 
                ch.SerialNoFK, 
                ch.Datetime, 
                ch.Number,
                ch.Direction,
                ch.Status,
                ch.Duration,
                d.Address, 
                d.Firstname,
                d.Lastname,
                d.Gender,
                d.DOB,
                d.MedicalCondition,
                u.Email as UserEmail,
                e.EmergencyNo1,
                e.EmergencyNo2,
                e.RegisteredDate,
                e.DeviceStatus,
                e.LastOnline
            FROM Call_Histories ch 
            INNER JOIN EVA e ON ch.SerialNoFK = e.SerialNoFK
            INNER JOIN Dependents d ON e.DependentIDFK = d.DependentID
            INNER JOIN Users u ON e.UserIDFK = u.UserID 
            WHERE u.UserID = ?
            ORDER BY ch.Datetime DESC
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get User Call Histories Error: " . $e->getMessage());
        return [];
    }
}

// Function to get call histories with filters
function getFilteredCallHistories($filters = []) {
    $pdo = getDatabase(); 
    try {
        $baseQuery = '
            SELECT 
                ch.RecordID, 
                ch.SerialNoFK, 
                ch.Datetime, 
                ch.Number,
                ch.Direction,
                ch.Status,
                ch.Duration,
                d.Address, 
                d.Firstname,
                d.Lastname,
                d.Gender,
                d.DOB,
                d.MedicalCondition,
                u.Email as UserEmail,
                e.EmergencyNo1,
                e.EmergencyNo2,
                e.RegisteredDate,
                e.DeviceStatus,
                e.LastOnline
            FROM Call_Histories ch 
            LEFT JOIN EVA e ON ch.SerialNoFK = e.SerialNoFK
            LEFT JOIN Dependents d ON e.DependentIDFK = d.DependentID
            LEFT JOIN Users u ON e.UserIDFK = u.UserID 
        ';
        
        $whereConditions = [];
        $params = [];
        
        // Add filters
        if (!empty($filters['status'])) {
            $whereConditions[] = 'ch.Status = ?';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['direction'])) {
            $whereConditions[] = 'ch.Direction = ?';
            $params[] = $filters['direction'];
        }
        
        if (!empty($filters['user_id'])) {
            $whereConditions[] = 'u.UserID = ?';
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['serial_no'])) {
            $whereConditions[] = 'ch.SerialNoFK = ?';
            $params[] = $filters['serial_no'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = 'DATE(ch.Datetime) >= ?';
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = 'DATE(ch.Datetime) <= ?';
            $params[] = $filters['date_to'];
        }
        
        // Add WHERE clause if there are conditions
        if (!empty($whereConditions)) {
            $baseQuery .= ' WHERE ' . implode(' AND ', $whereConditions);
        }
        
        $baseQuery .= ' ORDER BY ch.Datetime DESC';
        
        // Add limit if specified
        if (!empty($filters['limit'])) {
            $baseQuery .= ' LIMIT ?';
            $params[] = $filters['limit'];
        }
        
        $stmt = $pdo->prepare($baseQuery);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get Filtered Call Histories Error: " . $e->getMessage());
        return [];
    }
}

// Function to get call history statistics
function getCallHistoryStats($userId = null) {
    $pdo = getDatabase(); 
    try {
        $baseQuery = '
            SELECT 
                COUNT(*) as total_calls,
                SUM(CASE WHEN ch.Status = "Unanswered" THEN 1 ELSE 0 END) as unanswered_calls,
                SUM(CASE WHEN ch.Status = "Active" THEN 1 ELSE 0 END) as active_calls,
                SUM(CASE WHEN ch.Status = "Resolved" THEN 1 ELSE 0 END) as resolved_calls,
                SUM(CASE WHEN ch.Direction = "Incoming" THEN 1 ELSE 0 END) as incoming_calls,
                SUM(CASE WHEN ch.Direction = "Outgoing" THEN 1 ELSE 0 END) as outgoing_calls
            FROM Call_Histories ch 
            LEFT JOIN EVA e ON ch.SerialNoFK = e.SerialNoFK
            LEFT JOIN Users u ON e.UserIDFK = u.UserID 
        ';
        
        $params = [];
        if ($userId !== null) {
            $baseQuery .= ' WHERE u.UserID = ?';
            $params[] = $userId;
        }
        
        $stmt = $pdo->prepare($baseQuery);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get Call History Stats Error: " . $e->getMessage());
        return [
            'total_calls' => 0,
            'unanswered_calls' => 0,
            'active_calls' => 0,
            'resolved_calls' => 0,
            'incoming_calls' => 0,
            'outgoing_calls' => 0
        ];
    }
}

// Function to get call history by record ID
function getCallHistoryById($recordId) {
    $pdo = getDatabase(); 
    try {
        $stmt = $pdo->prepare('
            SELECT 
                ch.RecordID, 
                ch.SerialNoFK, 
                ch.Datetime, 
                ch.Number,
                ch.Direction,
                ch.Status,
                ch.Duration,
                d.Address, 
                d.Firstname,
                d.Lastname,
                d.Gender,
                d.DOB,
                d.MedicalCondition,
                u.Email as UserEmail,
                e.EmergencyNo1,
                e.EmergencyNo2,
                e.RegisteredDate,
                e.DeviceStatus,
                e.LastOnline
            FROM Call_Histories ch 
            LEFT JOIN EVA e ON ch.SerialNoFK = e.SerialNoFK
            LEFT JOIN Dependents d ON e.DependentIDFK = d.DependentID
            LEFT JOIN Users u ON e.UserIDFK = u.UserID 
            WHERE ch.RecordID = ?
        ');
        $stmt->execute([$recordId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get Call History By ID Error: " . $e->getMessage());
        return null;
    }
}

// Function to update call status
function updateCallStatus($recordId, $newStatus) {
    $pdo = getDatabase(); 
    try {
        $stmt = $pdo->prepare('UPDATE Call_Histories SET Status = ? WHERE RecordID = ?');
        $result = $stmt->execute([$newStatus, $recordId]);
        return $result;
    } catch (Exception $e) {
        error_log("Update Call Status Error: " . $e->getMessage());
        return false;
    }
}

// Function to delete call history record
function deleteCallHistory($recordId) {
    $pdo = getDatabase(); 
    try {
        $stmt = $pdo->prepare('DELETE FROM Call_Histories WHERE RecordID = ?');
        $result = $stmt->execute([$recordId]);
        return $result;
    } catch (Exception $e) {
        error_log("Delete Call History Error: " . $e->getMessage());
        return false;
    }
}
?>