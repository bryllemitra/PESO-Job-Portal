<?php
session_start();
include '../includes/config.php'; // Include DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id']; // Get user ID from session

        // Get reference ID to delete
        $reference_id = $_POST['reference_id'];

        // Delete reference from the `references` table
        $query = "DELETE FROM `references` WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $reference_id, $user_id);
        $stmt->execute();

        // Set success message in session
        $_SESSION['success_message'] = "Reference deleted successfully!";

        // Redirect to profile page
        header("Location: profile.php");
        exit();
    } else {
        echo "User is not logged in!";
    }
}
?>
