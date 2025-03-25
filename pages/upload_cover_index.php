<?php
session_start();
include '../includes/config.php';
include '../includes/restrictions.php';


$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;

if (!$user_role || $user_role !== 'admin') {
    die("Unauthorized access.");
}

// Handle Cover Photo Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // Handle Cover Photo Upload
    if ($_POST['action'] === 'upload' && !empty($_FILES['cover_photo']['name'])) {
        $target_dir = "../uploads/index_cover/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Ensure the folder exists
        }

        // First, check if there's an existing cover photo in the database
        $select_query = "SELECT cover_photo FROM homepage LIMIT 1";
        $result = $conn->query($select_query);
        $row = $result->fetch_assoc();

        // If a cover photo already exists, delete it
        if ($row) {
            $old_cover_photo = $row['cover_photo'];
            $old_file_path = $target_dir . $old_cover_photo;

            // Delete the old cover photo file from the server if it exists
            if (file_exists($old_file_path)) {
                unlink($old_file_path); // Delete the file from the server
            }

            // Also, delete the old cover photo entry from the database
            $delete_query = "DELETE FROM homepage";
            $conn->query($delete_query);
        }

        // Now proceed to upload the new cover photo
        $file_name = uniqid() . '_' . basename($_FILES["cover_photo"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image type
        if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES["cover_photo"]["tmp_name"], $target_file)) {
                // Store the new cover photo in the database
                $stmt = $conn->prepare("INSERT INTO homepage (cover_photo) VALUES (?)");
                $stmt->bind_param("s", $file_name);

                if ($stmt->execute()) {
                    $_SESSION['success'] = "Cover photo updated successfully.";
                    echo '<script>window.location.href="index.php";</script>';
                    exit();
                } else {
                    $_SESSION['error'] = "Database update failed.";
                }
            } else {
                $_SESSION['error'] = "File upload failed.";
            }
        } else {
            $_SESSION['error'] = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    }

    // Handle Cover Photo Deletion
    if ($_POST['action'] === 'remove') {
        // First, fetch the current cover photo file name from the database
        $select_query = "SELECT cover_photo FROM homepage LIMIT 1";
        $result = $conn->query($select_query);
        $row = $result->fetch_assoc();

        if ($row) {
            $cover_photo = $row['cover_photo'];
            $file_path = "../uploads/index_cover/" . $cover_photo;

            // Delete the cover photo file from the server if it exists
            if (file_exists($file_path)) {
                unlink($file_path); // Delete the file from the server
            }

            // Now, delete the cover photo entry from the database
            $delete_query = "DELETE FROM homepage";
            if ($conn->query($delete_query)) {
                $_SESSION['success'] = "Cover photo removed successfully.";
                echo '<script>window.location.href="index.php";</script>';
                exit();
            } else {
                $_SESSION['error'] = "Failed to delete cover photo from the database.";
            }
        } else {
            $_SESSION['error'] = "No cover photo found to remove.";
        }
    }
}


// Redirect back if something went wrong
echo '<script>window.location.href="index.php";</script>';
exit();
?>
