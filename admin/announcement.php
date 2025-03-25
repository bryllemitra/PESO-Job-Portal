<?php
include '../includes/config.php'; // Include your database connection file
include '../includes/header.php';

// Fetch user role from session if available
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;

// Handle Delete Request (Once Confirmed)
if ($user_role === 'admin' && isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $query = "DELETE FROM announcements WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();

    // Redirect after deletion
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
    <title>Announcements - Zamboanga City PESO Job Portal</title>
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/JOB/assets/announcement.css">
    <style> 
/* Mobile Layout for Announcements Section */
@media (max-width: 900px) {
  .announcement-card {
    flex-direction: column; /* Stack content vertically */
    align-items: center; /* Center content */
    text-align: center; /* Center text */
  }

  .announcement-thumbnail {
    margin-bottom: 16px; /* Add space between the thumbnail and the text */
    margin-right: 0; /* Remove right margin */
  }

  .announcement-thumbnail img {
    width: 100%; /* Make the image responsive */
    max-width: 300px; /* Limit the size */
    height: auto;
  }

  .announcement-content {
    width: 100%; /* Ensure content takes full width */
  }

  .announcement-title {
    font-size: 1.4rem; /* Adjust title size */
  }

  .announcement-date {
    font-size: 0.9rem; /* Adjust date size */
  }

  .announcement-content p {
    font-size: 0.9rem; /* Adjust paragraph size */
  }

  /* Adjust Admin Action Buttons */
  .action-buttons {
    top: 10px;
    right: 10px;
  }
}

/* Hide the Thumbnail on Mobile (e.g., iPhone SE and similar) */
@media (max-width: 900px) {
  .announcement-thumbnail {
    display: none; /* Completely hide the thumbnail */
  }
}



    </style>
</head>
<body>
<br><h1 class="text-center mt-8 ">Latest Announcements</h1>
    
    <div class="container mt-6 text-center">
        <?php if ($user_role === 'admin'): ?>
            <button onclick="window.location.href='create_announcement.php'" class="py-2 px-4 bg-blue-900 hover:bg-blue-800 text-white font-semibold rounded-md shadow-md transition duration-300 ease-in-out">
                Create Announcement
            </button>
        <?php endif; ?>
    </div>

<!-- Announcements Section -->
<div class="container mx-auto py-8">
    

    <?php
    // Fetch announcements from the database
    $query = "SELECT * FROM announcements ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Thumbnail and URL setup
            $thumbnail = $row['thumbnail'] ? '../uploads/announcement_thumbnail/' . $row['thumbnail'] : '../uploads/default/PESO.png'; // Default image if no thumbnail
            $url_link = $row['url_link'] ? $row['url_link'] : '#'; // Fallback to '#' if no URL

            // Check if URL is present to make the announcement clickable
            $is_clickable = !empty($row['url_link']); // If URL is empty, set to false

            echo '
<div class="announcement-card p-6 mb-6 relative flex items-start border rounded-lg shadow-md bg-white hover:bg-gray-100 transition-all duration-300 ease-in-out">
    <!-- Announcement Link (Click to open URL) -->
    ' . ($is_clickable ? 
        '<a href="' . $url_link . '" target="_blank" class="flex items-start">' :
        '<div class="flex items-start">') . '

        <!-- Thumbnail Image on the Left -->
        <div class="announcement-thumbnail mr-6">
            <img src="' . $thumbnail . '" alt="Thumbnail" class="w-48 h-48 object-cover rounded-md">
        </div>

        <!-- Announcement Content on the Right -->
        <div class="announcement-content flex-1 flex-col">
            <h2 class="announcement-title text-xl font-bold text-[#1976d2] hover:underline mb-2">' . htmlspecialchars($row['title']) . '</h2>
            <p class="announcement-date text-sm text-gray-500 mb-2"><i class="fas fa-calendar-alt me-2"></i>' . date("F j, Y", strtotime($row['created_at'])) . '</p>
            <p class="announcement-content mt-2 text-gray-700">' . nl2br(htmlspecialchars($row['content'])) . '</p>
        </div>

    ' . ($is_clickable ? '</a>' : '</div>') . '

    <!-- Action Buttons for Admin -->
    ' . ($user_role === 'admin' ? '
    <div class="action-buttons absolute top-2 right-2">
        <button class="btn btn-outline-custom dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="edit_announcement.php?id=' . $row['id'] . '">Edit</a></li>
            <li><a class="dropdown-item delete-button btn-delete" href="#" data-id="' . $row['id'] . '" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete</a></li>
        </ul>
    </div>
    ' : '') . '
</div>';

        }
    } else {
        echo '<p class="text-center text-gray-600">No announcements available at the moment.</p>';
    }
    ?>
</div>


    <!-- Modal for Deleting Announcement -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this announcement? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <a style="background-color:#007bff; box-shadow:none;" href="#" id="deleteConfirmButton" class="btn btn-primary">Delete</a>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Wait for DOM to be ready before attaching events
        document.addEventListener('DOMContentLoaded', function () {
            const deleteButtons = document.querySelectorAll('.delete-button');
            const deleteConfirmButton = document.getElementById('deleteConfirmButton');
            let announcementId = null;

            // Assign the announcement ID when the delete button is clicked
            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function () {
                    announcementId = this.getAttribute('data-id');
                    // Update the confirmation button's href with the correct delete URL
                    deleteConfirmButton.setAttribute('href', 'announcement.php?delete_id=' + announcementId);
                });
            });
        });
    </script>

</body>
</html>

<?php include '../includes/footer.php'; ?>
