<?php
session_start();
include '../includes/config.php'; // Include DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure the user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id']; // Get user ID from session

        // Get form data
        $achievement_id = isset($_POST['achievement_id']) ? $_POST['achievement_id'] : null; // For editing
        $award_name = isset($_POST['award_name']) ? $_POST['award_name'] : ''; // Default to empty string if not set
        $organization = isset($_POST['organization']) ? $_POST['organization'] : ''; // Default to empty string if not set
        $award_date = isset($_POST['award_date']) ? $_POST['award_date'] : ''; // Default to empty string if not set

        // Handle file upload for proof_file (if exists)
        $proof_file = null;
        if (isset($_FILES['proof_file']) && $_FILES['proof_file']['error'] == UPLOAD_ERR_OK) {
            // Check if the uploaded file is valid (for example, file type, file size, etc.)
            $target_dir = "../uploads/achievements/"; // Directory to store the file
            $file_type = strtolower(pathinfo($_FILES["proof_file"]["name"], PATHINFO_EXTENSION));

            // Fetch the username from the database to use it as the filename
            $query = "SELECT username FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $sanitized_username = preg_replace("/[^a-zA-Z0-9-_]/", "_", $user['username']); // Sanitize username

            // Generate a unique filename based on username and current timestamp (or a random number)
            $timestamp = time(); // Use current timestamp to ensure uniqueness
            $file_name = $sanitized_username . "_" . $timestamp . "." . $file_type; // e.g., john_doe_1618472523.jpg
            $target_file = $target_dir . $file_name;

            // Check if file type is valid (allow only image, PDF, or DOCX files)
            if (in_array($file_type, ["jpg", "jpeg", "png", "gif", "pdf", "docx"])) {
                // If updating, delete the old file if it exists
                if ($achievement_id) {
                    $query = "SELECT proof_file FROM achievements WHERE id = ? AND user_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ii", $achievement_id, $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $achievement = $result->fetch_assoc();
                    if (!empty($achievement['proof_file']) && file_exists($achievement['proof_file'])) {
                        unlink($achievement['proof_file']); // Delete old file
                    }
                }

                // Move uploaded file to target directory
                if (move_uploaded_file($_FILES["proof_file"]["tmp_name"], $target_file)) {
                    $proof_file = $target_file; // Store the file path
                } else {
                    $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
                }
            } else {
                $_SESSION['error_message'] = "Only image, PDF, and DOCX files are allowed for proof.";
            }
        }

        // Check if it's an edit (achievement_id is provided)
        if ($achievement_id) {
            // Update existing achievement record, including the proof file if uploaded
            if ($proof_file) {
                $query = "UPDATE achievements 
                          SET award_name = ?, organization = ?, award_date = ?, proof_file = ? 
                          WHERE id = ? AND user_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssisi", $award_name, $organization, $award_date, $proof_file, $achievement_id, $user_id);
            } else {
                $query = "UPDATE achievements 
                          SET award_name = ?, organization = ?, award_date = ? 
                          WHERE id = ? AND user_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssii", $award_name, $organization, $award_date, $achievement_id, $user_id);
            }
            $stmt->execute();

            // Set success message in session
            $_SESSION['success_message'] = "Achievement updated successfully!";
        } else {
            // Insert a new achievement record, including the proof file if uploaded
            if ($proof_file) {
                $query = "INSERT INTO achievements (user_id, award_name, organization, award_date, proof_file)
                          VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("issss", $user_id, $award_name, $organization, $award_date, $proof_file);
            } else {
                $query = "INSERT INTO achievements (user_id, award_name, organization, award_date)
                          VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("isss", $user_id, $award_name, $organization, $award_date);
            }
            $stmt->execute();

            // Set success message in session
            $_SESSION['success_message'] = "Achievement added successfully!";
        }

        // Close the statement
        $stmt->close();

        // Redirect to profile.php
        header("Location: profile.php");
        exit(); // Ensure no further code is executed after the redirect
    } else {
        echo "User is not logged in!";
    }
}
?>
