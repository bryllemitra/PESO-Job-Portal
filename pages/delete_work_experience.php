<?php
session_start();
include '../includes/config.php'; // Include DB connection

if (isset($_GET['id'])) {
    $workExperienceId = $_GET['id'];
    $user_id = $_SESSION['user_id']; // Ensure the user is logged in

    // Delete the work experience record
    $query = "DELETE FROM work_experience WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $workExperienceId, $user_id);
    $stmt->execute();

    // Check if the record was deleted
    if ($stmt->affected_rows > 0) {
        // Set success message in session
        $_SESSION['success_message'] = "Work experience record deleted successfully!";
    } else {
        // Set error message in session
        $_SESSION['error_message'] = "Failed to delete work experience record.";
    }

    // Close the statement
    $stmt->close();

    // Redirect back to the profile page
    header("Location: profile.php");
    exit();
} else {
    // Invalid request if ID is not present
    $_SESSION['error_message'] = "Invalid request. No work experience ID provided.";
    header("Location: profile.php");
    exit();
}
?>
