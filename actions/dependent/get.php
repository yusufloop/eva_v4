<?php
session_start();
require_once '../../config/config.php';
require_once '../../helpers/auth_helper.php';

requireAuth();

if (isset($_GET['dependentId'])) {
    $dependentId = $_GET['dependentId'];
    
    try {
        if (hasRole('admin')) {
            // Admin can view any dependent
            $stmt = $pdo->prepare('SELECT d.*, u.Email as UserEmail FROM Dependents d LEFT JOIN Users u ON d.UserIDFK = u.UserID WHERE d.DependentID = ?');
            $stmt->execute([$dependentId]);
        } else {
            // Regular user can only view their own dependents
            $stmt = $pdo->prepare('SELECT d.*, u.Email as UserEmail FROM Dependents d LEFT JOIN Users u ON d.UserIDFK = u.UserID WHERE d.DependentID = ? AND d.UserIDFK = ?');
            $stmt->execute([$dependentId, $_SESSION['UserID']]);
        }
        
        $dependent = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dependent) {
            echo json_encode($dependent);
        } else {
            echo json_encode(['error' => 'Dependent not found or access denied']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} elseif (isset($_GET['userId'])) {
    // Get dependents by user ID (for dropdowns, etc.)
    $userId = $_GET['userId'];
    
    try {
        $stmt = $pdo->prepare("SELECT DependentID, Firstname, Lastname, Address FROM Dependents WHERE UserIDFK = ?");
        $stmt->execute([$userId]);
        $dependents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($dependents);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'No ID provided']);
}
?>