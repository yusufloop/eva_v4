<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

requireAuth();

if (isset($_GET['dependentId'])) {
    $dependentId = $_GET['dependentId'];
    
    try {
        $pdo = getDatabase();
        
        // Check if user has permission to delete this dependent
        if (!hasRole('admin')) {
            $checkStmt = $pdo->prepare('SELECT UserIDFK FROM Dependents WHERE DependentID = ?');
            $checkStmt->execute([$dependentId]);
            $dependent = $checkStmt->fetch();
            
            if (!$dependent || $dependent['UserIDFK'] != getCurrentUserId()) {
                throw new Exception("You don't have permission to delete this dependent.");
            }
        }
        
        // Check if dependent has associated devices
        $deviceCheckStmt = $pdo->prepare('SELECT COUNT(*) FROM EVA WHERE DependentIDFK = ?');
        $deviceCheckStmt->execute([$dependentId]);
        $deviceCount = $deviceCheckStmt->fetchColumn();
        
        if ($deviceCount > 0) {
            throw new Exception("Cannot delete dependent. Please remove associated devices first.");
        }
        
        // Delete dependent
        $stmt = $pdo->prepare('DELETE FROM Dependents WHERE DependentID = ?');
        
        if ($stmt->execute([$dependentId])) {
            $_SESSION['success_message'] = "Dependent deleted successfully.";
        } else {
            throw new Exception("Unable to delete dependent.");
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    // Redirect based on user role
    if (hasRole('admin')) {
        header("Location: ../../pages/dependents.php");
    } else {
        header("Location: ../../pages/my_dependents.php");
    }
    exit;
}

$_SESSION['error_message'] = "Invalid request.";
header("Location: ../../pages/dependents.php");
exit;
?>