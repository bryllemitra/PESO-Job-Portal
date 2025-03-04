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
    if ($_POST['action'] === 'upload' && !empty($_FILES['cover_photo']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = uniqid() . '_' . basename($_FILES["cover_photo"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image type
        if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES["cover_photo"]["tmp_name"], $target_file)) {
                // Store in database
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
        $delete_query = "DELETE FROM homepage";
        if ($conn->query($delete_query)) {
            $_SESSION['success'] = "Cover photo removed successfully.";
            echo '<script>window.location.href="index.php";</script>';
            exit();
        } else {
            $_SESSION['error'] = "Failed to delete cover photo.";
        }
    }
}

// Redirect back if something went wrong
echo '<script>window.location.href="index.php";</script>';
exit();
?>
