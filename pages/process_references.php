<?php
session_start();
include '../includes/config.php'; // Include DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure the user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id']; // Get user ID from session

        // Get form data
        $reference_name = isset($_POST['reference_name']) ? $_POST['reference_name'] : '';
        $position = isset($_POST['position']) ? $_POST['position'] : '';
        $workplace = isset($_POST['workplace']) ? $_POST['workplace'] : '';
        $contact_number = isset($_POST['contact_number']) ? $_POST['contact_number'] : '';

        // Insert the reference data into the `references` table
        $query = "INSERT INTO `references` (user_id, name, position, workplace, contact_number) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issss", $user_id, $reference_name, $position, $workplace, $contact_number);
        $stmt->execute();

        // Set success message in session
        $_SESSION['success_message'] = "Reference added successfully!";

        // Close the statement
        $stmt->close();

        // Redirect to profile page
        header("Location: profile.php");
        exit(); // Ensure no further code is executed after the redirect
    } else {
        echo "User is not logged in!";
    }
}
?>
