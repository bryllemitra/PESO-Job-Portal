<?php
session_start();
include '../includes/config.php'; // Include your database connection file

// Check if the user is logged in and handle based on role
if (!isset($_SESSION['username'])) {
    echo "0"; // Return 0 if no user is logged in
    exit;
}

// Admin role
if ($_SESSION['role'] === 'admin') {
    // Fetch unread message count for admin (contacts table)
    $query = "SELECT COUNT(*) AS unread_count FROM contacts WHERE is_read = 0";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
    echo $data['unread_count']; // Return unread count for admin
}
// User role
elseif ($_SESSION['role'] === 'user') {
    // Fetch unread notification count for user (applications table)
    $userId = $_SESSION['user_id']; // Get the logged-in user ID from the session

    $query = "SELECT COUNT(*) AS unread_count FROM applications WHERE user_id = ? AND is_read = 0";
    // Prepare and execute the query to fetch unread notifications for the user
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('i', $userId); // Bind the user ID to the query
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        echo $data['unread_count']; // Return unread count for user
    } else {
        echo "0"; // Return 0 if query fails
    }
}
?>
