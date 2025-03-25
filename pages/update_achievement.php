<?php
session_start();
include '../includes/config.php'; // Include DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure the user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id']; // Get user ID from session

        // Get form data
        $achievement_id = $_POST['achievement_id']; // Get achievement_id from the hidden field
        $award_name = $_POST['award_name'];
        $organization = $_POST['organization'];
        $award_date = $_POST['award_date'];

        // Initialize variables
        $proof_file = null;

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

        // Handle file upload for proof_file (if exists)
        if (isset($_FILES['proof_file']) && $_FILES['proof_file']['error'] == UPLOAD_ERR_OK) {
            // Check if the uploaded file is valid (file type, file size, etc.)
            $target_dir = "../uploads/achievements/"; // Directory to store the file
            $file_type = strtolower(pathinfo($_FILES["proof_file"]["name"], PATHINFO_EXTENSION));

            // Generate a unique filename based on username and timestamp (to avoid overwriting)
            $timestamp = time(); // Current timestamp to ensure the filename is unique
            $new_file_name = $username . "_" . $timestamp . '.' . $file_type; // Example: john_doe_1618472523.jpg
            $target_file = $target_dir . $new_file_name;

            // Check if file is an image or document
            if (in_array($file_type, ["jpg", "jpeg", "png", "gif", "pdf"])) {
                // If there's an existing proof file, delete it
                $current_proof_file = null;
                // Fetch current proof file name from the database (if any)
                $query = "SELECT proof_file FROM achievements WHERE id = ? AND user_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $achievement_id, $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $current_proof_file = $row['proof_file']; // Store current proof file
                }
                $stmt->close();

                // If there's an existing proof file, delete it
                if ($current_proof_file && file_exists($current_proof_file)) {
                    unlink($current_proof_file); // Delete the old file
                }

                // Move uploaded file to target directory
                if (move_uploaded_file($_FILES["proof_file"]["tmp_name"], $target_file)) {
                    $proof_file = $target_file; // Store the new file path
                } else {
                    $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
                }
            } else {
                $_SESSION['error_message'] = "Only image and document files are allowed for proof.";
            }
        }

        // Update achievement in the database
        if ($proof_file) {
            // Update achievement record with proof file
            $query = "UPDATE achievements SET award_name = ?, organization = ?, award_date = ?, proof_file = ? WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssii", $award_name, $organization, $award_date, $proof_file, $achievement_id, $user_id);
        } else {
            // Update achievement record without proof file
            $query = "UPDATE achievements SET award_name = ?, organization = ?, award_date = ? WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssii", $award_name, $organization, $award_date, $achievement_id, $user_id);
        }

        if ($stmt->execute()) {
            // Set success message in session
            $_SESSION['success_message'] = "Achievement updated successfully!";
        } else {
            $_SESSION['error_message'] = "Error updating achievement.";
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
