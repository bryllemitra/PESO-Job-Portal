<?php
include '../includes/config.php'; // Include DB connection

// Start the session and check if the user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Debugging: Log the request method
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

// Handle Cover Photo Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['cover_photo'])) {
    error_log("Handling cover photo upload...");

    if ($_FILES["cover_photo"]["size"] == 0) {
        echo "<div class='alert alert-danger'>Please select a file to upload.</div>";
    } else {
        $target_dir = "../uploads/cover_admin/"; // Ensure this directory exists and is writable
        $fileType = strtolower(pathinfo($_FILES["cover_photo"]["name"], PATHINFO_EXTENSION));

        // Fetch the current cover photo and username from the database
        $query = "SELECT cover_photo, username FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user data is fetched correctly
        if ($user = $result->fetch_assoc()) {
            // Now user is an associative array, and you can access its fields
            // Sanitize the username and ensure it's safe for use in a file name
            $sanitized_username = preg_replace("/[^a-zA-Z0-9-_]/", "_", $user['username']); // Replace unsafe characters with underscore
            $file_name = $sanitized_username . "." . $fileType; // Use username as the filename
            $target_file = $target_dir . $file_name;

            // Check if the uploaded file is a valid image
            if (getimagesize($_FILES["cover_photo"]["tmp_name"])) {
                // Allow only certain image formats
                if ($fileType == "jpg" || $fileType == "jpeg" || $fileType == "png" || $fileType == "gif") {
                    // If there is an existing cover photo, delete it from the server
                    if (!empty($user['cover_photo']) && file_exists($user['cover_photo'])) {
                        unlink($user['cover_photo']); // Delete the old cover photo file
                    }

                    // Proceed with uploading the new cover photo
                    if (move_uploaded_file($_FILES["cover_photo"]["tmp_name"], $target_file)) {
                        // Update the database with the new cover photo path
                        $update_query = "UPDATE users SET cover_photo = ? WHERE id = ?";
                        $stmt = $conn->prepare($update_query);
                        $stmt->bind_param("si", $target_file, $user_id);

                        if ($stmt->execute()) {
                            error_log("Cover photo updated successfully.");
                            header("Location: profile.php?id=$user_id"); // Redirect to profile page
                            exit();
                        } else {
                            echo "<div class='alert alert-danger'>Error updating cover photo in the database.</div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger'>Sorry, there was an error uploading your file.</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>Only JPG, JPEG, PNG, and GIF files are allowed.</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>File is not a valid image.</div>";
            }
        } else {
            // If the user is not found, log and return an error
            echo "<div class='alert alert-danger'>No user found with ID: $user_id</div>";
            exit();
        }
    }
}


// Handle Cover Photo Removal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if a cover photo exists
    $query = "SELECT cover_photo FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!empty($user['cover_photo'])) {
        // Clear the cover_photo column in the database
        $update_query = "UPDATE users SET cover_photo = NULL WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            // Optionally delete the file from the server
            if (file_exists($user['cover_photo'])) {
                unlink($user['cover_photo']); // Delete the old cover photo file
            }

            // Return success response
            echo json_encode(['success' => true, 'message' => 'Cover photo removed successfully.']);
            exit();
        } else {
            // Return error response
            echo json_encode(['success' => false, 'message' => 'Error removing cover photo from the database.']);
            exit();
        }
    } else {
        // Return error response if no cover photo exists
        echo json_encode(['success' => false, 'message' => 'No cover photo found to remove.']);
        exit();
    }
} else {
    // Return error response for invalid request method
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}
?>