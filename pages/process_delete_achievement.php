<?php
session_start();
include '../includes/config.php'; // Include DB connection

// Check if user is logged in and the achievement_id is passed
if (isset($_SESSION['user_id']) && isset($_POST['achievement_id'])) {
    $user_id = $_SESSION['user_id'];
    $achievement_id = $_POST['achievement_id'];

    // Prepare and execute a query to get the proof file path before deleting the achievement
    $query = "SELECT proof_file FROM achievements WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $achievement_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the achievement and the associated proof file
        $achievement = $result->fetch_assoc();
        $proof_file = $achievement['proof_file'];

        // If a proof file exists and is not empty, delete it from the server
        if (!empty($proof_file) && file_exists($proof_file)) {
            unlink($proof_file); // Delete the file
        }
    }

    // Prepare and execute the delete query to remove the achievement from the database
    $query = "DELETE FROM achievements WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $achievement_id, $user_id);

    if ($stmt->execute()) {
        // Success: Achievement deleted
        $_SESSION['success_message'] = "Achievement deleted successfully!";
    } else {
        // Error: Something went wrong
        $_SESSION['error_message'] = "Error deleting achievement.";
    }

    // Close the statement
    $stmt->close();

    // Redirect to profile page
    header("Location: profile.php");
    exit();
} else {
    // If user is not logged in or achievement_id is not set
    echo "Invalid request!";
}
?>
