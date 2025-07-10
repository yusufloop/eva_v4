<?php
session_start();
require_once '../../config/config.php';
require_once '../../helpers/auth_helper.php';

// Check authentication and admin role
requireAuth();
if (!hasRole('admin')) {
    echo json_encode(['error' => "You don't have permission to access inventory data."]);
    exit;
}

if (isset($_GET['serialNo'])) {
    $serialNo = $_GET['serialNo'];
    
    try {
        $pdo = getDatabase();
        
        // Get device from inventory
        $stmt = $pdo->prepare('SELECT * FROM Inventory WHERE SerialNo = ?');
        $stmt->execute([$serialNo]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($device) {
            echo json_encode($device);
        } else {
            echo json_encode(['error' => "Device not found."]);
        }
    } catch (PDOException $e) {
        error_log("Get inventory error: " . $e->getMessage());
        echo json_encode(['error' => "Database error: " . $e->getMessage()]);
    }
    
    exit;
}

echo json_encode(['error' => "Invalid request."]);
exit;