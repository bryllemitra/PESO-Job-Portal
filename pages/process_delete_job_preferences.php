<?php
session_start();
include '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure the user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $job_preference_id = $_POST['job_preference_id'];

        // Check if the job preference belongs to the logged-in user
        $check_query = "SELECT user_id FROM job_preferences WHERE id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $job_preference_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $job_preference = $result->fetch_assoc();

        if ($job_preference && $job_preference['user_id'] == $user_id) {
            // Delete preferred positions associated with this job preference
            $delete_positions_query = "DELETE FROM job_preferences_positions WHERE job_preference_id = ?";
            $delete_positions_stmt = $conn->prepare($delete_positions_query);
            $delete_positions_stmt->bind_param("i", $job_preference_id);
            $delete_positions_stmt->execute();

            // Delete the job preferences itself
            $delete_query = "DELETE FROM job_preferences WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param("i", $job_preference_id);
            $delete_stmt->execute();

            $_SESSION['success_message'] = "Job preferences deleted successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $_SESSION['error_message'] = "You are not authorized to delete these preferences.";
            header("Location: profile.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "You must be logged in to delete job preferences.";
        header("Location: profile.php");
        exit();
    }
}
?>
