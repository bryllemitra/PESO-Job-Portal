<?php
session_start();
include '../includes/config.php'; // Include DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id']; // Get user ID from session

        // Get form data
        $reference_id = $_POST['reference_id'];
        $reference_name = $_POST['reference_name'];
        $position = $_POST['position'];
        $workplace = $_POST['workplace'];
        $contact_number = $_POST['contact_number'];

        // Update reference data in the `references` table
        $query = "UPDATE `references` SET name = ?, position = ?, workplace = ?, contact_number = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssii", $reference_name, $position, $workplace, $contact_number, $reference_id, $user_id);
        $stmt->execute();

        // Set success message in session
        $_SESSION['success_message'] = "Reference updated successfully!";

        // Redirect to profile page
        header("Location: profile.php");
        exit();
    } else {
        echo "User is not logged in!";
    }
}
?>
