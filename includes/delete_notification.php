<?php
// Include the database connection
include 'config.php';

// Check if the ID is passed via POST
if (isset($_POST['id'])) {
    $notif_id = $_POST['id'];

    // Delete the notification
    $query = "DELETE FROM notifications WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $notif_id);  // Bind the notification ID as an integer
    $stmt->execute();

    // Check if the query was successful
    if ($stmt->affected_rows > 0) {
        echo 'Notification deleted.';
    } else {
        echo 'Error: Could not delete the notification.';
    }
}
?>
