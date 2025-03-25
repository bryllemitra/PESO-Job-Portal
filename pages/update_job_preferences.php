<?php
session_start();
include '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure the user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $job_preference_id = $_POST['job_preference_id'];
        $work_type = $_POST['work_type'];
        $job_location = $_POST['job_location'];
        $employment_type = $_POST['employment_type'];
        $preferred_positions = isset($_POST['preferred_positions']) ? $_POST['preferred_positions'] : [];

        // Check if the job preference belongs to the logged-in user
        $check_query = "SELECT user_id FROM job_preferences WHERE id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $job_preference_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $job_preference = $result->fetch_assoc();

        if ($job_preference && $job_preference['user_id'] == $user_id) {
            // Update job preferences (work type, job location, and employment type)
            $update_query = "UPDATE job_preferences 
                             SET work_type = ?, job_location = ?, employment_type = ? 
                             WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sssi", $work_type, $job_location, $employment_type, $job_preference_id);
            $update_stmt->execute();

            // Update preferred positions (positions selected by the user)
            // First, delete existing positions for this job preference
            $delete_positions_query = "DELETE FROM job_preferences_positions WHERE job_preference_id = ?";
            $delete_positions_stmt = $conn->prepare($delete_positions_query);
            $delete_positions_stmt->bind_param("i", $job_preference_id);
            $delete_positions_stmt->execute();

            // Then insert the new preferred positions
            if (!empty($preferred_positions)) {
                foreach ($preferred_positions as $position_id) {
                    $insert_position_query = "INSERT INTO job_preferences_positions (job_preference_id, position_id) 
                                              VALUES (?, ?)";
                    $position_stmt = $conn->prepare($insert_position_query);
                    $position_stmt->bind_param("ii", $job_preference_id, $position_id);
                    $position_stmt->execute();
                }
            }

            $_SESSION['success_message'] = "Job preferences updated successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $_SESSION['error_message'] = "You are not authorized to update these preferences.";
            header("Location: profile.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "You must be logged in to update job preferences.";
        header("Location: profile.php");
        exit();
    }
}
?>
