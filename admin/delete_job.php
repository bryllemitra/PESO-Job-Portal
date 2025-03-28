<?php
include '../includes/config.php';
include '../includes/header.php';

// Restrict access to admins and employers
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employer')) {
    die("Access Denied");
}

// Validate Job ID
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Job ID");
}

$id = $_GET['id'];

// Fetch the job details (thumbnail and photo paths) before deletion
$stmt = $conn->prepare("SELECT thumbnail, photo, employer_id FROM jobs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die("Job not found.");
}

$stmt->bind_result($thumbnail_path, $photo_path, $employer_id);
$stmt->fetch();
$stmt->close();

// If the user is an employer, check if they are the employer of this job
if ($_SESSION['role'] === 'employer') {
    $user_id = $_SESSION['user_id'];

    if ($employer_id !== $user_id) {
        die("You do not have permission to delete this job.");
    }
}

// Delete the thumbnail file if it exists
if ($thumbnail_path && file_exists("../" . $thumbnail_path)) {
    unlink("../" . $thumbnail_path);
}

// Delete the photo file if it exists
if ($photo_path && file_exists("../" . $photo_path)) {
    unlink("../" . $photo_path);
}

// Use a prepared statement for security and delete the job
$stmt = $conn->prepare("DELETE FROM jobs WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Set a session variable for success message
    $_SESSION['job_delete_message'] = 'Job deleted successfully!';
    $_SESSION['job_delete_redirect'] = 'job_list.php'; // Redirect to job_list.php after modal
} else {
    // Set a session variable for error message
    $_SESSION['job_delete_message'] = 'Error deleting job. Please try again.';
    $_SESSION['job_delete_redirect'] = 'job_list.php'; // Redirect to job_list.php after modal in case of failure
}

// Close the statement
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Deletion Status</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <!-- Success/Error Modal -->
    <div class="modal fade" id="successErrorModal" tabindex="-1" aria-labelledby="successErrorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successErrorModalLabel">Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="modalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="closeModalButton" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Check if there is a message in session
        var modalMessage = "<?php echo isset($_SESSION['job_delete_message']) ? $_SESSION['job_delete_message'] : ''; ?>";
        if (modalMessage) {
            // Show modal with the message
            document.getElementById('modalMessage').innerText = modalMessage;
            var myModal = new bootstrap.Modal(document.getElementById('successErrorModal'));
            myModal.show();

            // Clear the session after modal shows to prevent showing on next page load
            <?php unset($_SESSION['job_delete_message']); ?>
        }

        // Auto refresh after 1 second of showing the modal
        setTimeout(function() {
            window.location.reload(); // Reload the page after 1 second
        }, 1000);

        // Also refresh when clicking OK
        document.getElementById('closeModalButton').addEventListener('click', function() {
            window.location.reload(); // Reload the page when OK is clicked
        });
    });
    </script>

    <?php
    // JavaScript redirect using echo
    echo "<script>window.location.href = '" . $_SESSION['job_delete_redirect'] . "';</script>";
    ?>
</body>
</html>
