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
                d.DependentID, 
                d.Firstname, 
                d.Lastname, 
                d.Gender,
                d.DOB,
                d.Address, 
                d.PostalCode,
                d.MedicalCondition,
                d.UserIDFK,
                u.Email as UserEmail,
                COUNT(e.SerialNoFK) as DeviceCount
            FROM Dependents d 
            LEFT JOIN Users u ON d.UserIDFK = u.UserID 
            LEFT JOIN EVA e ON d.DependentID = e.DependentIDFK
            GROUP BY d.DependentID
            ORDER BY u.Email, d.Firstname, d.Lastname
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
                d.DependentID, 
                d.Firstname, 
                d.Lastname, 
                d.Gender,
                d.DOB,
                d.Address, 
                d.PostalCode,
                d.MedicalCondition,
                d.UserIDFK,
                COUNT(e.SerialNoFK) as DeviceCount
            FROM Dependents d 
            LEFT JOIN EVA e ON d.DependentID = e.DependentIDFK
            WHERE d.UserIDFK = ? 
            GROUP BY d.DependentID
            ORDER BY d.Firstname, d.Lastname
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
            SELECT d.*, u.Email as UserEmail 
            FROM Dependents d 
            LEFT JOIN Users u ON d.UserIDFK = u.UserID 
            WHERE d.DependentID = ?
        ';
        
        $params = [$dependentId];
        
        // If userId provided, restrict to user's dependents
        if ($userId !== null) {
            $sql .= ' AND d.UserIDFK = ?';
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