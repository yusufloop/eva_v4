<?php
require 'config.php';

if (isset($_GET['userId'])) {
    $userId = $_GET['userId'];

    // Fetch dependents based on the User ID
    $stmt = $pdo->prepare("SELECT DependentID, Firstname, Lastname, Address FROM Dependents WHERE UserIDFK = ?");
    $stmt->execute([$userId]);
    $dependents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($dependents); // Return dependents as JSON
} else {
    echo json_encode([]); // Return empty array if no User ID provided
}
?>
