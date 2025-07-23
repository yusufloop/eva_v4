<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

/**
 * Get all dependents (admin only)
 */
function getAllDependents() {
    $pdo = getDatabase();
    
    try {
        $stmt = $pdo->prepare('
            SELECT 
                d.dep_id as DependentID, 
                d.fullname as Firstname, 
                "" as Lastname, 
                d.sex as Gender,
                d.dob as DOB,
                d.address as Address, 
                "" as PostalCode,
                d.med_condition as MedicalCondition,
                d.user_id as UserIDFK,
                u.email as UserEmail,
                COUNT(e.eva_id) as DeviceCount
            FROM dependants d 
            LEFT JOIN users u ON d.user_id = u.user_id 
            LEFT JOIN eva_info e ON d.dep_id = e.dep_id
            GROUP BY d.dep_id
            ORDER BY u.email, d.fullname
        ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get All Dependents Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get user's dependents
 */
function getUserDependents($userId) {
    $pdo = getDatabase();
    
    try {
        $stmt = $pdo->prepare('
            SELECT 
                d.dep_id as DependentID, 
                d.fullname as Firstname, 
                "" as Lastname, 
                d.sex as Gender,
                d.dob as DOB,
                d.address as Address, 
                "" as PostalCode,
                d.med_condition as MedicalCondition,
                d.user_id as UserIDFK,
                COUNT(e.eva_id) as DeviceCount
            FROM dependants d 
            LEFT JOIN eva_info e ON d.dep_id = e.dep_id
            WHERE d.user_id = ? 
            GROUP BY d.dep_id
            ORDER BY d.fullname
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get User Dependents Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get dependent by ID
 */
function getDependentById($dependentId, $userId = null) {
    $pdo = getDatabase();
    
    try {
        $sql = '
            SELECT d.*, u.email as UserEmail 
            FROM dependants d 
            LEFT JOIN users u ON d.user_id = u.user_id 
            WHERE d.dep_id = ?
        ';
        
        $params = [$dependentId];
        
        // If userId provided, restrict to user's dependents
        if ($userId !== null) {
            $sql .= ' AND d.user_id = ?';
            $params[] = $userId;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get Dependent By ID Error: " . $e->getMessage());
        return false;
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['dependentId'])) {
    requireAuth();
    
    $dependentId = $_GET['dependentId'];
    $userId = hasRole('admin') ? null : getCurrentUserId();
    
    $dependent = getDependentById($dependentId, $userId);
    
    if ($dependent) {
        echo json_encode($dependent);
    } else {
        echo json_encode(['error' => 'Dependent not found or access denied']);
    }
    exit;
}

// Handle user dependents request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['userId'])) {
    requireAuth();
    
    $userId = $_GET['userId'];
    
    // Check permissions
    if (!hasRole('admin') && $userId != getCurrentUserId()) {
        echo json_encode(['error' => 'Access denied']);
        exit;
    }
    
    $dependents = getUserDependents($userId);
    echo json_encode($dependents);
    exit;
}
?>