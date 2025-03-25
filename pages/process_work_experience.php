<?php
session_start();
include '../includes/config.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Debugging: Print POST data
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Assuming the user's birthday is stored in the session or fetched from DB
        $user_birthday = $_SESSION['user_birthday']; // Replace with actual birthday data (e.g., '1990-05-15')

        $work_experience_id = isset($_POST['work_experience_id']) ? $_POST['work_experience_id'] : null;
        $job_title = isset($_POST['job_title']) ? trim($_POST['job_title']) : ''; 
        $company_name = isset($_POST['company_name']) ? trim($_POST['company_name']) : ''; 
        $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
        $end_date = isset($_POST['end_date']) && !empty($_POST['end_date']) ? $_POST['end_date'] : null; 
        $job_description = isset($_POST['job_description']) ? trim($_POST['job_description']) : ''; 
        $currently_working = isset($_POST['currently_working']) ? 1 : 0;

        // Additional fields for Employment Type, Job Location, and Work Type
        $employment_type = isset($_POST['employment_type']) ? $_POST['employment_type'] : ''; 
        $job_location = isset($_POST['job_location']) ? $_POST['job_location'] : ''; 
        $country = isset($_POST['country']) && !empty($_POST['country']) ? $_POST['country'] : null; 
        $work_type = isset($_POST['work_type']) ? $_POST['work_type'] : 'onsite';

        // Validate required fields
        if (empty($job_title) || empty($company_name) || empty($start_date) || empty($employment_type) || empty($job_location) || empty($work_type)) {
            $_SESSION['error_message'] = "Please fill in all required fields!";
            header("Location: profile.php");
            exit();
        }

        // Convert dates to timestamps for accurate comparison
        $start_date_timestamp = strtotime($start_date);
        $user_birthday_timestamp = strtotime($user_birthday);

        // Validate the start date (ensure it's not earlier than the user's birthday)
        if ($start_date_timestamp < $user_birthday_timestamp) {
            $_SESSION['error_message'] = "Start date cannot be earlier than your birthdate!";
            header("Location: profile.php");
            exit();
        }

        // If an end date is provided, validate it (ensure it's not earlier than start date)
        if ($end_date) {
            $end_date_timestamp = strtotime($end_date);
            if ($end_date_timestamp < $start_date_timestamp) {
                $_SESSION['error_message'] = "End date cannot be earlier than start date!";
                header("Location: profile.php");
                exit();
            }
        }

        // Validate employment_type, job_location, and work_type against allowed values
        $allowed_employment_types = ['fulltime', 'parttime', 'self-employed', 'freelance', 'contract', 'internship', 'apprenticeship', 'seasonal', 'home-based', 'domestic', 'temporary', 'volunteer'];
        $allowed_job_locations = ['local', 'overseas'];
        $allowed_work_types = ['remote', 'onsite', 'hybrid'];

        if (!in_array($employment_type, $allowed_employment_types)) {
            $_SESSION['error_message'] = "Invalid employment type!";
            header("Location: profile.php");
            exit();
        }

        if (!in_array($job_location, $allowed_job_locations)) {
            $_SESSION['error_message'] = "Invalid job location!";
            header("Location: profile.php");
            exit();
        }

        if (!in_array($work_type, $allowed_work_types)) {
            $_SESSION['error_message'] = "Invalid work type!";
            header("Location: profile.php");
            exit();
        }

        // Check if it's an edit (work_experience_id is provided)
        if ($work_experience_id) {
            $query = "UPDATE work_experience 
                      SET job_title = ?, company_name = ?, job_description = ?, start_date = ?, end_date = ?, currently_working = ?, 
                          employment_type = ?, job_location = ?, country = ?, work_type = ? 
                      WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssissssii", $job_title, $company_name, $job_description, $start_date, $end_date, $currently_working, 
                              $employment_type, $job_location, $country, $work_type, $work_experience_id, $user_id);
        } else {
            $query = "INSERT INTO work_experience (user_id, job_title, company_name, job_description, start_date, end_date, currently_working, 
                                                     employment_type, job_location, country, work_type)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isssssissss", $user_id, $job_title, $company_name, $job_description, $start_date, $end_date, $currently_working, 
                              $employment_type, $job_location, $country, $work_type);
        }

        // Execute the query
        if ($stmt->execute()) {
            $_SESSION['success_message'] = $work_experience_id ? "Work experience updated successfully!" : "Work experience added successfully!";
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();

        // Redirect to profile.php
        header("Location: profile.php");
        exit();
    } else {
        echo "User is not logged in!";
    }
}
?>