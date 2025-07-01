<?php
require 'config.php'; // Include your database connection here
session_start();

if (isset($_GET['serialNo'])) {
    $serialNo = $_GET['serialNo'];

    try {
            // Prepare and execute the delete statement
        $stmt = $pdo->prepare('DELETE FROM EVA WHERE SerialNoFK = ?');
        $stmt->execute([$serialNo]);

        // If delete was successful
        if ($stmt->rowCount()) {
            $_SESSION['device_message'] = 'Device successfully deleted.';
        } else {
            $_SESSION['device_message'] = 'Error: Device could not be deleted. Please try again.';
        }

    } catch (PDOException $e) {
        $_SESSION['device_message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $_SESSION['device_message'] = 'Error: Serial number is missing.';
}

// Redirect back to dashboard.php
header('Location: ../dashboard.php');
exit();
?>
