<?php
// Device Management Functions

require_once __DIR__ . '/../config/config.php';

function getAllCallHistories() {
    $pdo = getDatabase(); 
    try {
        $stmt = $pdo->prepare('
            SELECT 
                ch.call_id as RecordID, 
                i.serial_no as SerialNoFK, 
                ch.call_date as Datetime, 
                ch.number as Number,
                ch.direction as Direction,
                ch.status as Status,
                ch.duration as Duration,
                d.address as Address, 
                d.fullname as Firstname,
                "" as Lastname,
                d.sex as Gender,
                d.dob as DOB,
                d.med_condition as MedicalCondition,
                u.email as UserEmail,
                e.family_contact1 as EmergencyNo1,
                e.family_contact2 as EmergencyNo2,
                e.reg_date as RegisteredDate,
                CASE WHEN i.is_registered = 1 THEN "Active" ELSE "Inactive" END as DeviceStatus,
                e.lastseen as LastOnline
            FROM call_histories ch 
            LEFT JOIN eva_info e ON ch.eva_id = e.eva_id
            LEFT JOIN dependants d ON e.dep_id = d.dep_id
            LEFT JOIN users u ON e.user_id = u.user_id 
            LEFT JOIN inventory i ON e.inventory_id = i.inventory_id
            ORDER BY ch.call_date DESC
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
                ch.call_id as RecordID, 
                i.serial_no as SerialNoFK, 
                ch.call_date as Datetime, 
                ch.number as Number,
                ch.direction as Direction,
                ch.status as Status,
                ch.duration as Duration,
                d.address as Address, 
                d.fullname as Firstname,
                "" as Lastname,
                d.sex as Gender,
                d.dob as DOB,
                d.med_condition as MedicalCondition,
                u.email as UserEmail,
                e.family_contact1 as EmergencyNo1,
                e.family_contact2 as EmergencyNo2,
                e.reg_date as RegisteredDate,
                CASE WHEN i.is_registered = 1 THEN "Active" ELSE "Inactive" END as DeviceStatus,
                e.lastseen as LastOnline
            FROM call_histories ch 
            INNER JOIN eva_info e ON ch.eva_id = e.eva_id
            INNER JOIN dependants d ON e.dep_id = d.dep_id
            INNER JOIN users u ON e.user_id = u.user_id 
            INNER JOIN inventory i ON e.inventory_id = i.inventory_id
            WHERE u.user_id = ?
            ORDER BY ch.call_date DESC
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
                ch.call_id as RecordID, 
                i.serial_no as SerialNoFK, 
                ch.call_date as Datetime, 
                ch.number as Number,
                ch.direction as Direction,
                ch.status as Status,
                ch.duration as Duration,
                d.address as Address, 
                d.fullname as Firstname,
                "" as Lastname,
                d.sex as Gender,
                d.dob as DOB,
                d.med_condition as MedicalCondition,
                u.email as UserEmail,
                e.family_contact1 as EmergencyNo1,
                e.family_contact2 as EmergencyNo2,
                e.reg_date as RegisteredDate,
                CASE WHEN i.is_registered = 1 THEN "Active" ELSE "Inactive" END as DeviceStatus,
                e.lastseen as LastOnline
            FROM call_histories ch 
            LEFT JOIN eva_info e ON ch.eva_id = e.eva_id
            LEFT JOIN dependants d ON e.dep_id = d.dep_id
            LEFT JOIN users u ON e.user_id = u.user_id 
            LEFT JOIN inventory i ON e.inventory_id = i.inventory_id
        ';
        
        $whereConditions = [];
        $params = [];
        
        // Add filters
        if (!empty($filters['status'])) {
            $whereConditions[] = 'ch.status = ?';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['direction'])) {
            $whereConditions[] = 'ch.direction = ?';
            $params[] = $filters['direction'];
        }
        
        if (!empty($filters['user_id'])) {
            $whereConditions[] = 'u.user_id = ?';
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['serial_no'])) {
            $whereConditions[] = 'i.serial_no = ?';
            $params[] = $filters['serial_no'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = 'DATE(ch.call_date) >= ?';
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = 'DATE(ch.call_date) <= ?';
            $params[] = $filters['date_to'];
        }
        
        // Add WHERE clause if there are conditions
        if (!empty($whereConditions)) {
            $baseQuery .= ' WHERE ' . implode(' AND ', $whereConditions);
        }
        
        $baseQuery .= ' ORDER BY ch.call_date DESC';
        
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
                SUM(CASE WHEN ch.status = "Unanswered" THEN 1 ELSE 0 END) as unanswered_calls,
                SUM(CASE WHEN ch.status = "Active" THEN 1 ELSE 0 END) as active_calls,
                SUM(CASE WHEN ch.status = "Resolved" THEN 1 ELSE 0 END) as resolved_calls,
                SUM(CASE WHEN ch.direction = "Incoming" THEN 1 ELSE 0 END) as incoming_calls,
                SUM(CASE WHEN ch.direction = "Outgoing" THEN 1 ELSE 0 END) as outgoing_calls
            FROM call_histories ch 
            LEFT JOIN eva_info e ON ch.eva_id = e.eva_id
            LEFT JOIN users u ON e.user_id = u.user_id 
        ';
        
        $params = [];
        if ($userId !== null) {
            $baseQuery .= ' WHERE u.user_id = ?';
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
                ch.call_id as RecordID, 
                i.serial_no as SerialNoFK, 
                ch.call_date as Datetime, 
                ch.number as Number,
                ch.direction as Direction,
                ch.status as Status,
                ch.duration as Duration,
                d.address as Address, 
                d.fullname as Firstname,
                "" as Lastname,
                d.sex as Gender,
                d.dob as DOB,
                d.med_condition as MedicalCondition,
                u.email as UserEmail,
                e.family_contact1 as EmergencyNo1,
                e.family_contact2 as EmergencyNo2,
                e.reg_date as RegisteredDate,
                CASE WHEN i.is_registered = 1 THEN "Active" ELSE "Inactive" END as DeviceStatus,
                e.lastseen as LastOnline
            FROM call_histories ch 
            LEFT JOIN eva_info e ON ch.eva_id = e.eva_id
            LEFT JOIN dependants d ON e.dep_id = d.dep_id
            LEFT JOIN users u ON e.user_id = u.user_id 
            LEFT JOIN inventory i ON e.inventory_id = i.inventory_id
            WHERE ch.call_id = ?
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
        $stmt = $pdo->prepare('UPDATE call_histories SET status = ? WHERE call_id = ?');
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
        $stmt = $pdo->prepare('DELETE FROM call_histories WHERE call_id = ?');
        $result = $stmt->execute([$recordId]);
        return $result;
    } catch (Exception $e) {
        error_log("Delete Call History Error: " . $e->getMessage());
        return false;
    }
}
?>