<?php
session_start();
include '../includes/config.php'; // Include DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure the user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id']; // Get user ID from session

        // Get form data
        $certificate_id = $_POST['certificate_id']; // Get certificate_id from the hidden field
        $certificate_name = $_POST['certificate_name']; // The name of the certificate
        $issuing_organization = $_POST['issuing_organization']; // The organization that issued the certificate
        $issue_date = $_POST['issue_date']; // The date the certificate was issued

        // Initialize variable for the file
        $certificate_file = null;

        // Fetch the username associated with the user_id
        $query = "SELECT username FROM users WHERE id = ?"; // Assuming the table name is 'users'
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $username = null;
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $username = $row['username']; // Get the username
        }
        $stmt->close();

        // Handle file upload for certificate_file (if exists)
        if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] == UPLOAD_ERR_OK) {
            // Check if the uploaded file is valid (file type, file size, etc.)
            $target_dir = "../uploads/certificates/"; // Directory to store the file
            $file_type = strtolower(pathinfo($_FILES["certificate_file"]["name"], PATHINFO_EXTENSION));

            // Generate a unique filename based on username and timestamp (to avoid overwriting)
            $timestamp = time(); // Current timestamp to ensure the filename is unique
            $new_file_name = $username . "_" . $timestamp . '.' . $file_type; // Example: john_doe_1618472523.jpg
            $target_file = $target_dir . $new_file_name;

            // Check if file is an image or document
            if (in_array($file_type, ["jpg", "jpeg", "png", "gif", "pdf", "docx"])) {
                // If there's an existing certificate file, delete it
                $current_certificate_file = null;
                // Fetch current certificate file name from the database (if any)
                $query = "SELECT certificate_file FROM certificates WHERE id = ? AND user_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $certificate_id, $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $current_certificate_file = $row['certificate_file']; // Store current certificate file
                }
                $stmt->close();

                // If there's an existing certificate file, delete it
                if ($current_certificate_file && file_exists($current_certificate_file)) {
                    unlink($current_certificate_file); // Delete the old file
                }

                // Move uploaded file to target directory
                if (move_uploaded_file($_FILES["certificate_file"]["tmp_name"], $target_file)) {
                    $certificate_file = $target_file; // Store the new file path
                } else {
                    $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
                }
            } else {
                $_SESSION['error_message'] = "Only image and document files are allowed for certificates.";
            }
        }

        // Update certificate in the database
        if ($certificate_file) {
            // Update certificate record with certificate file
            $query = "UPDATE certificates SET certificate_name = ?, issuing_organization = ?, issue_date = ?, certificate_file = ? WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssii", $certificate_name, $issuing_organization, $issue_date, $certificate_file, $certificate_id, $user_id);
        } else {
            // Update certificate record without certificate file
            $query = "UPDATE certificates SET certificate_name = ?, issuing_organization = ?, issue_date = ? WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssii", $certificate_name, $issuing_organization, $issue_date, $certificate_id, $user_id);
        }

        if ($stmt->execute()) {
            // Set success message in session
            $_SESSION['success_message'] = "Certificate updated successfully!";
        } else {
            $_SESSION['error_message'] = "Error updating certificate.";
        }

        // Close the statement
        $stmt->close();

        // Redirect to profile page
        header("Location: profile.php");
        exit(); // Ensure no further code is executed after the redirect
    } else {
        echo "User is not logged in!";
    }
}
?>
