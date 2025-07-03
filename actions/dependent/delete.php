<?php
session_start();
require_once '../../config/config.php';
require_once '../../helpers/auth_helper.php';

requireAuth();

if (isset($_GET['dependentId'])) {
    $dependentId = $_GET['dependentId'];
    
    try {
        // Check if user has permission to delete this dependent
        if (!hasRole('admin')) {
            $checkStmt = $pdo->prepare('SELECT UserIDFK FROM Dependents WHERE DependentID = ?');
            $checkStmt->execute([$dependentId]);
            $dependent = $checkStmt->fetch();
            
            if (!$dependent || $dependent['UserIDFK'] != $_SESSION['UserID']) {
                $_SESSION['error_message'] = "You don't have permission to delete this dependent.";
                header("Location: ../../pages/dependents.php");
                exit;
            }
        }
        
        // Check if dependent has associated devices
        $deviceCheckStmt = $pdo->prepare('SELECT COUNT(*) FROM EVA WHERE DependentID = ?');
        $deviceCheckStmt->execute([$dependentId]);
        $deviceCount = $deviceCheckStmt->fetchColumn();
        
        if ($deviceCount > 0) {
            $_SESSION['error_message'] = "Cannot delete dependent. Please remove associated devices first.";
        } else {
            $stmt = $pdo->prepare('DELETE FROM Dependents WHERE DependentID = ?');
            
            if ($stmt->execute([$dependentId])) {
                $_SESSION['success_message'] = "Dependent deleted successfully.";
            } else {
                $_SESSION['error_message'] = "Error: Unable to delete dependent.";
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
    
    header("Location: ../../pages/dependents.php");
    exit;
}
?>