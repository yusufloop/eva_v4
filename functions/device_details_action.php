<?php

session_start();
require 'config.php';

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../index.php");
    exit();
}

$editMode = false;



function generateTable($pdo) {
    $serialNo = $_GET['serialNo'];

    $stmt = $pdo->prepare('SELECT * FROM Call_Histories WHERE SerialNoFK = ?');
    $stmt->execute(["$serialNo"]);
    $users = $stmt->fetchAll();

    $headers = ['RecordId', 'SerialNo', 'Datetime', 'Number', 'Direction', 'Status', 'Duration'];
    $userDatas = ['RecordID', 'SerialNoFK', 'Datetime', 'Number', 'Direction', 'Status', 'Duration'];
    // Create table headers from the $headers array
    echo '<thead><tr>';
    foreach ($headers as $header) {
        echo '<th>' . htmlspecialchars($header) . '</th>';
    }
    echo '</tr></thead>';
    if (!empty($users[0])) {
        echo '<tbody>';
        foreach ($users as $user) {
            echo '<tr>';
            foreach ($userDatas as $index => $userData) {
                $value = $user[$userData] ?? '';

                // Check if the data is from a specific column and format accordingly
                if ($userData === 'Datetime') {
                    // Remove any trailing "+32" or similar parts
                    $value = preg_replace('/\+\d+$/', '', $value); // "24-10-21,16:13:52"

                    // Convert to DateTime
                    $dateTime = DateTime::createFromFormat('d-m-y,H:i:s', $value);
                    if ($dateTime) {
                        $value = $dateTime->format('d/m/Y H:i:s');
                    }
                }

                echo '<td data-label="' . htmlspecialchars($headers[$index]) . '">' . htmlspecialchars($value) . '</td>';
            }

            echo '</tr>';
        }
        echo '</tbody>';
    } else {
        echo '<tr><td colspan="'.count($headers).'" style="text-align: center;">No data available.</td></tr>';
    }
}

?>

