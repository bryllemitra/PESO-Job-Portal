<?php
session_start();
include '../includes/config.php'; // Include DB connection

// Check if the user is logged in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    // Sanitize and validate the incoming form data
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];  // Add last name
    $ext_name = $_POST['ext_name']; // Extension name is optional

    // Prepare the update query (Include last_name in the query)
    $sql = "UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, ext_name = ? WHERE id = ?";

    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind the parameters (the extension name can be NULL if it's empty)
        if (empty($ext_name)) {
            $ext_name = NULL; // If no extension name, set it to NULL
        }

        // Bind the parameters for the prepared statement
        $stmt->bind_param("ssssi", $first_name, $middle_name, $last_name, $ext_name, $_SESSION['user_id']);

        // Execute the query
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "User name updated successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to update user name.";
        }

        // Close the statement
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Database query error.";
    }

    // Close the connection
    $conn->close();

    // Redirect back to the profile page
    header("Location: profile.php");
    exit;
} else {
    // Redirect if the user is not logged in or the form is not submitted correctly
    header("Location: login.php");
    exit;
}
?>
