<?php
include '../includes/config.php'; // Include your database connection file
include '../includes/header.php';
include '../includes/restrictions.php';

if (!isset($_GET['id'])) {
    echo "<script type='text/javascript'>
            window.location.href = 'announcement.php';
          </script>";
    exit;
}

$id = $_GET['id'];
$query = "SELECT * FROM announcements WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$announcement = $result->fetch_assoc();

if (!$announcement) {
    echo "<script type='text/javascript'>
            window.location.href = 'announcement.php';
          </script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $url_link = $_POST['url_link']; // URL Link for the announcement
    $thumbnail = $_FILES['thumbnail']; // Handling the uploaded thumbnail image

    // Handle thumbnail upload if there's a file
    if ($thumbnail['name']) {
        $upload_dir = '../uploads/';
        $thumbnail_name = time() . '-' . basename($thumbnail['name']);
        $thumbnail_path = $upload_dir . $thumbnail_name;
        
        if (move_uploaded_file($thumbnail['tmp_name'], $thumbnail_path)) {
            // Update in the database
            $query = "UPDATE announcements SET title = ?, content = ?, url_link = ?, thumbnail = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssi", $title, $content, $url_link, $thumbnail_name, $id);
            $stmt->execute();
        }
    } else {
        // If no file was uploaded, update without thumbnail
        $query = "UPDATE announcements SET title = ?, content = ?, url_link = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $title, $content, $url_link, $id);
        $stmt->execute();
    }

    echo "<script type='text/javascript'>
            window.location.href = 'announcement.php';
          </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Announcement - Zamboanga City PESO Job Portal</title>
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/JOB/assets/edit_announcement.css">
    <style>
        /* Custom Scrollbar for Textareas */
        textarea {
            resize: none; /* Disable manual resizing */
            overflow: hidden; /* Hide scrollbar until needed */
            min-height: 6rem; /* Set a minimum height for better usability */
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col items-center justify-center">

    <div class="max-w-xl w-full p-6 bg-white rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold text-center mb-6 text-[#1976d2]">Edit Announcement</h1>

        <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" id="title" name="title" class="mt-1 block w-full px-3 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]" value="<?= htmlspecialchars($announcement['title']) ?>" required>
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                <textarea id="content" name="content" rows="3" class="mt-1 block w-full px-3 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2] auto-expand" required><?= htmlspecialchars($announcement['content']) ?></textarea>
            </div>

            <!-- URL Link -->
            <div>
                <label for="url_link" class="block text-sm font-medium text-gray-700">URL Link (Optional)</label>
                <input type="url" id="url_link" name="url_link" class="mt-1 block w-full px-3 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]" value="<?= htmlspecialchars($announcement['url_link']) ?>">
            </div>

            <!-- Thumbnail -->
            <div>
                <label for="thumbnail" class="block text-sm font-medium text-gray-700">Thumbnail Image (Optional)</label>
                <input type="file" id="thumbnail" name="thumbnail" class="mt-1 block w-full px-3 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]">
                <?php if ($announcement['thumbnail']): ?>
                    <div class="mt-2">
                        <img src="../uploads/<?= htmlspecialchars($announcement['thumbnail']) ?>" alt="Current Thumbnail" class="w-24 h-24 object-cover rounded-md">
                    </div>
                <?php endif; ?>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-between">
                <button type="submit" class="w-full py-2 px-4 bg-blue-900 hover:bg-blue-800 text-white font-semibold rounded-md shadow-md transition duration-300 ease-in-out">
                    Update Announcement
                </button>
                <a href="announcement.php" class="ml-4 w-full py-2 px-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-md shadow-md transition duration-300 ease-in-out flex items-center justify-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        // Auto-expand textarea functionality
        document.querySelectorAll('.auto-expand').forEach(textarea => {
            // Set initial height based on content
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';

            // Add event listener for dynamic expansion
            textarea.addEventListener('input', function () {
                this.style.height = 'auto'; // Reset height to recalculate
                this.style.height = this.scrollHeight + 'px'; // Set height to fit content
            });
        });
    </script>

</body>
</html>
