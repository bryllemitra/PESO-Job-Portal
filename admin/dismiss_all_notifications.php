<?php
session_start();
include '../includes/config.php';
include '../includes/restrictions.php';

if ($_SESSION['role'] === 'admin') {
    $query = "UPDATE applications SET dismissed = 1 WHERE dismissed = 0";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->execute();
        echo "All notifications dismissed.";
    } else {
        echo "Error preparing statement.";
    }
} else {
    echo "Unauthorized access.";
}
?>