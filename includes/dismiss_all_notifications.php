<?php
// Include the database connection
include 'config.php';

session_start();

// Ensure the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Update all notifications to be marked as read for the logged-in user
    $query = "UPDATE notifications SET is_read = 1 WHERE recipient_id = ? AND is_read = 0";  // Only mark unread notifications as read
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);  // Bind the user_id to the query
    $stmt->execute();
}
?>