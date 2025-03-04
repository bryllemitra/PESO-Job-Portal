<?php
session_start();
include '../includes/config.php';

// Ensure the user is logged in and not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$job_id = $_POST['job_id'] ?? null;

// Validate job ID
if (!$job_id || !is_numeric($job_id)) {
    echo "<div class='alert alert-danger text-center'>Invalid Job ID.</div>";
    exit();
}

// Check if the user has already applied for this job
$checkQuery = "SELECT * FROM applications WHERE user_id = ? AND job_id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("ii", $user_id, $job_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<div class='alert alert-warning text-center'>You have already applied for this job.</div>";
    exit();
}

// Fetch user's resume file path (to validate "Attach from Profile" option)
$resume_query = "SELECT resume_file FROM users WHERE id = ?";
$stmt = $conn->prepare($resume_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resume_result = $stmt->get_result();
$user_data = $resume_result->fetch_assoc();
$has_resume = !empty($user_data['resume_file']);
$resume_file = $user_data['resume_file'];

// Handle resume attachment
$resume_attached = false;
if ($_POST['resume_option'] === 'existing') {
    // Use the existing resume file from the user's profile
    if (!$has_resume) {
        echo "<div class='alert alert-danger text-center'>No resume found in your profile. Please upload a new resume.</div>";
        exit();
    }
    $resume_attached = true;
    $resume_file_to_attach = $resume_file; // Use the existing resume file
} elseif ($_POST['resume_option'] === 'new' && isset($_FILES['resume']) && $_FILES['resume']['size'] > 0) {
    // Upload a new resume
    $target_dir = "../uploads/resumes/"; // Ensure this directory exists and is writable
    $target_file = $target_dir . basename($_FILES["resume"]["name"]);
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ['pdf', 'doc', 'docx'];

    // Validate file type
    if (!in_array($fileType, $allowed_types)) {
        echo "<div class='alert alert-danger text-center'>Only PDF, DOC, and DOCX files are allowed.</div>";
        exit();
    }

    // Validate file size (limit to 5MB)
    if ($_FILES["resume"]["size"] > 5 * 1024 * 1024) {
        echo "<div class='alert alert-danger text-center'>File size must not exceed 5MB.</div>";
        exit();
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {
        $resume_attached = true;
        $resume_file_to_attach = $target_file; // Use the newly uploaded resume file
    } else {
        echo "<div class='alert alert-danger text-center'>Sorry, there was an error uploading your file.</div>";
        exit();
    }
} else {
    echo "<div class='alert alert-danger text-center'>You must select a valid resume option.</div>";
    exit();
}

// Insert the application into the database
if ($resume_attached) {
    $insertQuery = "INSERT INTO applications (user_id, job_id, resume_file, applied_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("iis", $user_id, $job_id, $resume_file_to_attach);

    if ($stmt->execute()) {
        $application_id = $stmt->insert_id;  // Get the ID of the newly inserted application

        // Insert into application_positions table (optional step)
        if (isset($_POST['position_ids']) && is_array($_POST['position_ids'])) {
            foreach ($_POST['position_ids'] as $position_id) {
                if (is_numeric($position_id)) {
                    $stmt = $conn->prepare("INSERT INTO application_positions (application_id, position_id) VALUES (?, ?)");
                    $stmt->bind_param("ii", $application_id, $position_id);
                    $stmt->execute();
                    if ($stmt->error) {
                        die("Application Positions insert error: " . $stmt->error);
                    }
                    $stmt->close();
                }
            }
        }

        // Fetch employer ID (the person who posted the job) from the jobs table
        $job_query = "SELECT employer_id FROM jobs WHERE id = ?";
        $stmt = $conn->prepare($job_query);
        $stmt->bind_param("i", $job_id);
        $stmt->execute();
        $job_result = $stmt->get_result();
        $job_data = $job_result->fetch_assoc();
        $employer_id = $job_data['employer_id'];  // Get the employer's ID (the user who posted the job)

        // Check if employer exists in the users table
        $check_employer_query = "SELECT id FROM users WHERE id = ? LIMIT 1";
        $check_employer_stmt = $conn->prepare($check_employer_query);
        $check_employer_stmt->bind_param("i", $employer_id);
        $check_employer_stmt->execute();
        $check_employer_result = $check_employer_stmt->get_result();

        if ($check_employer_result->num_rows > 0) {
            // Insert notification for the employer
            $notif_message = "A new application has been submitted for your job: " . htmlspecialchars($job_id);
            $notif_query = "INSERT INTO notifications (recipient_id, sender_id, job_id, application_id, message, user_role) 
                            VALUES (?, ?, ?, ?, ?, 'employer')";
            $notif_stmt = $conn->prepare($notif_query);
            $notif_stmt->bind_param("iiiss", $employer_id, $user_id, $job_id, $application_id, $notif_message);
            $notif_stmt->execute();
        } else {
            // Log or handle case where employer does not exist
            error_log("Employer with ID $employer_id does not exist.");
        }

        // Fetch all admins (optional if needed, but keeping the logic for admins as well)
        $query = "SELECT id FROM users WHERE role = 'admin'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($admin = $result->fetch_assoc()) {
            $admin_id = $admin['id'];  // Get each admin's ID

            // Check if admin exists in the users table
            $check_admin_query = "SELECT id FROM users WHERE id = ? LIMIT 1";
            $check_admin_stmt = $conn->prepare($check_admin_query);
            $check_admin_stmt->bind_param("i", $admin_id);
            $check_admin_stmt->execute();
            $check_admin_result = $check_admin_stmt->get_result();

            if ($check_admin_result->num_rows > 0) {
                // Insert notification for each admin (admins are notified as per original logic)
                $notif_message_admin = "A new application has been submitted for the job: " . htmlspecialchars($job_id);
                $notif_query_admin = "INSERT INTO notifications (recipient_id, sender_id, job_id, application_id, message, user_role) 
                                      VALUES (?, ?, ?, ?, ?, 'admin')";
                $notif_stmt_admin = $conn->prepare($notif_query_admin);
                $notif_stmt_admin->bind_param("iiiss", $admin_id, $user_id, $job_id, $application_id, $notif_message_admin);
                $notif_stmt_admin->execute();
            } else {
                // Log or handle case where admin does not exist
                error_log("Admin with ID $admin_id does not exist.");
            }
        }

        // Success message and redirect
        echo "<div class='alert alert-success text-center'>Application submitted successfully!</div>";
        header("Location: job.php?id=$job_id&message=Application successful");
        exit();
    } else {
        echo "<div class='alert alert-danger text-center'>Error submitting application. Please try again later.</div>";
    }
}
?>
