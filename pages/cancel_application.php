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
    $_SESSION['error_message'] = "Invalid Job ID.";
    header("Location: job.php?id=$job_id");
    exit();
}

// Check if the user has a pending application for this job
$checkQuery = "SELECT id FROM applications WHERE user_id = ? AND job_id = ? AND status = 'pending'";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("ii", $user_id, $job_id);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();
$application_id = $application['id'] ?? null;

if (!$application_id) {
    $_SESSION['error_message'] = "No pending application found for this job.";
    header("Location: job.php?id=$job_id");
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Ensure 'canceled' status is valid (Run this query once manually in phpMyAdmin)
    // ALTER TABLE applications MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected', 'canceled') NOT NULL;

    // Delete associated positions in application_positions table
    $deletePositionsQuery = "DELETE FROM application_positions WHERE application_id = ?";
    $stmt = $conn->prepare($deletePositionsQuery);
    $stmt->bind_param("i", $application_id);
    $stmt->execute();

    // Mark the application as "canceled" and store the cancellation timestamp
    $updateApplicationQuery = "UPDATE applications SET status = 'canceled', canceled_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($updateApplicationQuery);
    $stmt->bind_param("i", $application_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    $_SESSION['success_message'] = "Application canceled successfully! You can reapply after 10 minutes.";
    header("Location: job.php?id=$job_id&status=success");
    exit();
    
} catch (mysqli_sql_exception $exception) {
    // Rollback transaction in case of error
    $conn->rollback();
    $_SESSION['error_message'] = "Error canceling application. Please try again later.";
    header("Location: job.php?id=$job_id&status=error");
    exit();
    
}
?>
