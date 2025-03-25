<?php
session_start();
include '../includes/config.php'; // Include DB connection

// Check if user is logged in and the certificate_id is passed
if (isset($_SESSION['user_id']) && isset($_POST['certificate_id'])) {
    $user_id = $_SESSION['user_id'];
    $certificate_id = $_POST['certificate_id'];

    // Prepare and execute a query to get the certificate file path before deleting
    $query = "SELECT certificate_file FROM certificates WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $certificate_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the certificate and the associated file path
        $certificate = $result->fetch_assoc();
        $certificate_file = $certificate['certificate_file'];

        // If a certificate file exists and is not empty, delete it from the server
        if (!empty($certificate_file) && file_exists($certificate_file)) {
            unlink($certificate_file); // Delete the file from the server
        }

        // Prepare and execute the delete query to remove the certificate from the database
        $query = "DELETE FROM certificates WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $certificate_id, $user_id);

        if ($stmt->execute()) {
            // Success: Certificate deleted
            $_SESSION['success_message'] = "Certificate deleted successfully!";
        } else {
            // Error: Something went wrong
            $_SESSION['error_message'] = "Error deleting certificate.";
        }

        // Close the statement
        $stmt->close();
    } else {
        // No certificate found
        $_SESSION['error_message'] = "Certificate not found or invalid request.";
    }

    // Redirect to the profile page after deletion
    header("Location: profile.php");
    exit();
} else {
    // If user is not logged in or certificate_id is not set
    echo "Invalid request!";
}
?>
