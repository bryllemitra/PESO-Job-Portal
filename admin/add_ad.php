<?php
include '../includes/config.php';
include '../includes/header.php';
include '../includes/restrictions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user input to prevent XSS
    $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
    $link_url = filter_var($_POST['link_url'], FILTER_SANITIZE_URL);

    // Handle file upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image_file'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB limit

        // Validate file type and size
        if (!in_array($file['type'], $allowed_types)) {
            echo "Invalid file type. Only JPG, PNG, and GIF files are allowed.";
            exit;
        }
        if ($file['size'] > $max_size) {
            echo "File size exceeds the maximum limit of 2MB.";
            exit;
        }

        // Generate a unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('ad_', true) . '.' . $file_extension;

        // Move the uploaded file to the uploads folder
        $upload_path = __DIR__ . '/../uploads/ads_thumbnail/' . $new_filename;
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            echo "Failed to upload the file.";
            exit;
        }
    } else {
        echo "No file uploaded or an error occurred during upload.";
        exit;
    }

    // Insert into the database using prepared statements
    $query = "INSERT INTO ads (title, description, image_file, link_url, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssss', $title, $description, $new_filename, $link_url);

    if ($stmt->execute()) {
        echo "Advertisement added successfully!";
    } else {
        echo "Error adding ad: " . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8');
    }
}
?>
