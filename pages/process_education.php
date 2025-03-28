<?php
session_start();
include '../includes/config.php'; // Include DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure the user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id']; // Get user ID from session

        // Get form data and sanitize it
        $education_id = isset($_POST['education_id']) ? $_POST['education_id'] : null; // For editing
        $education_level = isset($_POST['education_level']) ? $_POST['education_level'] : ''; // Default to empty string if not set
        $course = isset($_POST['course']) ? $_POST['course'] : ''; // Default to empty string if not set
        $institution = isset($_POST['institution']) ? $_POST['institution'] : ''; // Default to empty string if not set
        $status = isset($_POST['status']) ? $_POST['status'] : ''; // Default to empty string if not set
        $completion_year = isset($_POST['completion_year']) ? $_POST['completion_year'] : 0; // Default to 0 if not set
        $expected_completion_date = isset($_POST['expected_completion_date']) ? $_POST['expected_completion_date'] : '0000-00-00'; // Default to '0000-00-00' if not set
        $course_highlights = isset($_POST['course_highlights']) ? $_POST['course_highlights'] : ''; // Default to empty string if not set
        $address = isset($_POST['address']) ? $_POST['address'] : ''; // Get address data

        // Validate required fields
        if (empty($education_level) || empty($institution)) {
            $_SESSION['error_message'] = "Education level and institution are required fields!";
            header("Location: profile.php");
            exit();
        }

        // For primary level, set fields to default values if they are not needed
        if ($education_level == 'primary') {
            $course = '';
            $course_highlights = '';
        }

        // Check if it's an edit (education_id is provided)
        if ($education_id) {
            // Update existing education record, including the address
            $query = "UPDATE education 
                      SET education_level = ?, course = ?, institution = ?, status = ?, completion_year = ?, expected_completion_date = ?, course_highlights = ?, address = ? 
                      WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            // Adjust bind_param string for 10 variables
            $stmt->bind_param("ssssssssii", $education_level, $course, $institution, $status, $completion_year, $expected_completion_date, $course_highlights, $address, $education_id, $user_id);

            // Execute the statement
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Education details updated successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to update education details!";
            }
        } else {
            // Insert a new education record, including the address
            $query = "INSERT INTO education (user_id, education_level, course, institution, status, completion_year, expected_completion_date, course_highlights, address)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            // Adjust bind_param string for 9 variables
            $stmt->bind_param("issssssss", $user_id, $education_level, $course, $institution, $status, $completion_year, $expected_completion_date, $course_highlights, $address);

            // Execute the statement
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Education details added successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to add education details!";
            }
        }

        // Close the statement
        $stmt->close();

        // Redirect to profile.php with a success or error message
        header("Location: profile.php");
        exit(); // Ensure no further code is executed after the redirect
    } else {
        echo "User is not logged in!";
    }
}
?>
