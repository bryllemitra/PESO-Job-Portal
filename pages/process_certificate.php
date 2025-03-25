<?php
session_start();
include '../includes/config.php'; // Include DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure the user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id']; // Get user ID from session

        // Get form data
        $certificate_id = isset($_POST['certificate_id']) ? $_POST['certificate_id'] : null; // For editing
        $certificate_name = isset($_POST['certificate_name']) ? $_POST['certificate_name'] : ''; 
        $issuing_organization = isset($_POST['issuing_organization']) ? $_POST['issuing_organization'] : ''; 
        $issue_date = isset($_POST['issue_date']) ? $_POST['issue_date'] : '';

        // Handle file upload for certificate_file (if exists)
        $certificate_file = null;
        if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] == UPLOAD_ERR_OK) {
            // Check if the uploaded file is valid (for example, file type, file size, etc.)
            $target_dir = "../uploads/certificates/"; // Directory to store the file
            $file_type = strtolower(pathinfo($_FILES["certificate_file"]["name"], PATHINFO_EXTENSION));

            // Fetch the username from the database to use it as the filename
            $query = "SELECT username FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $sanitized_username = preg_replace("/[^a-zA-Z0-9-_]/", "_", $user['username']); // Sanitize username

            // Generate a unique filename based on username and current timestamp
            $timestamp = time(); // Use current timestamp to ensure uniqueness
            $file_name = $sanitized_username . "_" . $timestamp . "." . $file_type; // Example: john_doe_1618472523.jpg
            $target_file = $target_dir . $file_name;

            // Check if file type is valid (image, PDF, DOCX)
            if (in_array($file_type, ["jpg", "jpeg", "png", "gif", "pdf", "docx"])) {
                // If editing, delete the old file if it exists
                if ($certificate_id) {
                    $query = "SELECT certificate_file FROM certificates WHERE id = ? AND user_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ii", $certificate_id, $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $certificate = $result->fetch_assoc();
                    if (!empty($certificate['certificate_file']) && file_exists($certificate['certificate_file'])) {
                        unlink($certificate['certificate_file']); // Delete old file
                    }
                }

                // Move the uploaded file to the target directory
                if (move_uploaded_file($_FILES["certificate_file"]["tmp_name"], $target_file)) {
                    $certificate_file = $target_file; // Store file path in the database
                } else {
                    $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
                }
            } else {
                $_SESSION['error_message'] = "Only image, PDF, and DOCX files are allowed for certificates.";
            }
        }

        // Check if it's an edit (certificate_id is provided)
        if ($certificate_id) {
            // Update existing certificate record, including the file if uploaded
            if ($certificate_file) {
                $query = "UPDATE certificates 
                          SET certificate_name = ?, issuing_organization = ?, issue_date = ?, certificate_file = ? 
                          WHERE id = ? AND user_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssisi", $certificate_name, $issuing_organization, $issue_date, $certificate_file, $certificate_id, $user_id);
            } else {
                $query = "UPDATE certificates 
                          SET certificate_name = ?, issuing_organization = ?, issue_date = ? 
                          WHERE id = ? AND user_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssii", $certificate_name, $issuing_organization, $issue_date, $certificate_id, $user_id);
            }
            $stmt->execute();

            $_SESSION['success_message'] = "Certificate updated successfully!";
        } else {
            // Insert a new certificate record, including the file if uploaded
            if ($certificate_file) {
                $query = "INSERT INTO certificates (user_id, certificate_name, issuing_organization, issue_date, certificate_file)
                          VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("issss", $user_id, $certificate_name, $issuing_organization, $issue_date, $certificate_file);
            } else {
                $query = "INSERT INTO certificates (user_id, certificate_name, issuing_organization, issue_date)
                          VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("isss", $user_id, $certificate_name, $issuing_organization, $issue_date);
            }
            $stmt->execute();

            $_SESSION['success_message'] = "Certificate added successfully!";
        }

        // Close the statement
        $stmt->close();

        // Redirect to the profile page
        header("Location: profile.php");
        exit();
    } else {
        echo "User is not logged in!";
    }
}
?>
