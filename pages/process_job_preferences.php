<?php
session_start();
include '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure the user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Get form data
        $work_type = $_POST['work_type'];
        $job_location = $_POST['job_location'];
        $employment_type = $_POST['employment_type'];
        $preferred_positions = isset($_POST['preferred_positions']) ? $_POST['preferred_positions'] : []; // Fetch selected positions

        // Check if job preferences already exist for the user
        $check_query = "SELECT id FROM job_preferences WHERE user_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing job preferences
            $update_query = "UPDATE job_preferences 
                             SET work_type = ?, job_location = ?, employment_type = ? 
                             WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sssi", $work_type, $job_location, $employment_type, $user_id);
            $update_stmt->execute();

            // Get the existing job preference ID
            $job_preference_id = $conn->insert_id;

            // Delete existing positions for this user
            $delete_positions_query = "DELETE FROM job_preferences_positions WHERE job_preference_id = ?";
            $delete_positions_stmt = $conn->prepare($delete_positions_query);
            $delete_positions_stmt->bind_param("i", $job_preference_id);
            $delete_positions_stmt->execute();
            
        } else {
            // Insert new job preferences
            $insert_query = "INSERT INTO job_preferences (user_id, work_type, job_location, employment_type) 
                             VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("isss", $user_id, $work_type, $job_location, $employment_type);
            $insert_stmt->execute();

            // Get the inserted job preference ID
            $job_preference_id = $conn->insert_id;
        }

        // Insert preferred positions if provided
        if (!empty($preferred_positions)) {
            foreach ($preferred_positions as $position_id) {
                $insert_position_query = "INSERT INTO job_preferences_positions (job_preference_id, position_id) 
                                           VALUES (?, ?)";
                $position_stmt = $conn->prepare($insert_position_query);
                $position_stmt->bind_param("ii", $job_preference_id, $position_id);
                $position_stmt->execute();
            }
        }

        // Redirect to profile or show success message
        $_SESSION['success_message'] = "Job preferences saved successfully!";
        header("Location: profile.php");
        exit();
    } else {
        echo "User is not logged in!";
    }
}
?>
