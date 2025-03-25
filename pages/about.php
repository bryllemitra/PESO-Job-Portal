<?php
// Start output buffering to prevent headers already sent errors
ob_start();

include '../includes/config.php'; // Include your database connection file
include '../includes/header.php';

// Fetch user role from session if available
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$is_admin = ($user_role === 'admin'); // Check if the user is an admin

// Fetch data from the database using prepared statement
$query = "SELECT * FROM about WHERE id = 1"; // Assuming there's only one row for 'about'
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Provide default values if no data is found
    $about = [
        'id' => 1,
        'cover_photo' => '/JOB/uploads/default/COVER.jpg',
        'carousel_images' => '[]',
        'mission' => 'Default mission text.',
        'vision' => 'Default vision text.',
    ];
} else {
    $about = $result->fetch_assoc();
}

$query = "SELECT cover_photo FROM about WHERE id = 1";
$stmt = $conn->prepare($query);
$stmt->execute();
$cover_photo_result = $stmt->get_result();
$row = $cover_photo_result->fetch_assoc();
$cover_photo = isset($row['cover_photo']) ? $row['cover_photo'] : '/JOB/uploads/default/COVER.jpg';

// Handle form submissions for updating mission, vision, and uploading images
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($is_admin) { // Only allow admins to perform these actions
        
        // Update Mission
        if (isset($_POST['update_mission'])) {
            $mission = mysqli_real_escape_string($conn, $_POST['mission']); // Prevent XSS
            $updateQuery = "UPDATE about SET mission=? WHERE id=1";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("s", $mission);
            $stmt->execute();

            // Redirect using JavaScript
            echo '<script>window.location.href = "about.php";</script>';
            exit();
        }

        // Update Vision
        if (isset($_POST['update_vision'])) {
            $vision = mysqli_real_escape_string($conn, $_POST['vision']); // Prevent XSS
            $updateQuery = "UPDATE about SET vision=? WHERE id=1";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("s", $vision);
            $stmt->execute();

            // Redirect using JavaScript
            echo '<script>window.location.href = "about.php";</script>';
            exit();
        }

        // Update Hero Text
        if (isset($_POST['update_hero_text'])) {
            $hero_text = mysqli_real_escape_string($conn, $_POST['hero_text']); // Prevent XSS
            $updateQuery = "UPDATE about SET hero_text=? WHERE id=1";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("s", $hero_text);
            $stmt->execute();

            // Redirect using JavaScript
            echo '<script>window.location.href = "about.php";</script>';
            exit();
        }

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle Cover Photo Upload
if (isset($_FILES['cover_photo'])) {
    // Sanitize file input
    $target_dir = "../uploads/about_cover/";
    $file_name = basename($_FILES["cover_photo"]["name"]);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($imageFileType, $allowed_types)) {
        // First, fetch the current cover photo file path from the database
        $selectQuery = "SELECT cover_photo FROM about WHERE id = 1";
        $result = $conn->query($selectQuery);
        $row = $result->fetch_assoc();

        if ($row) {
            $current_cover_photo = $row['cover_photo'];
            $file_path = "../uploads/about_cover/" . basename($current_cover_photo);

            // If a previous cover photo exists, delete it
            if (file_exists($file_path)) {
                unlink($file_path); // Delete the old cover photo
            }
        }

        // Move the new uploaded cover photo to the target directory
        if (move_uploaded_file($_FILES["cover_photo"]["tmp_name"], $target_file)) {
            // Prevent SQL Injection by using prepared statements
            $updateQuery = "UPDATE about SET cover_photo=? WHERE id=1";
            $stmt = $conn->prepare($updateQuery);

            // Now, bind the variable correctly
            $stmt->bind_param("s", $file_name);  // Bind the variable properly

            $stmt->execute();

            // Redirect using header() instead of JavaScript
            header("Location: about.php");
            exit(); // Ensure the script stops execution after the redirect
        } else {
            // Handle error during file upload
            echo "<script>alert('Error uploading cover photo.');</script>";
        }
    } else {
        // Handle invalid file type
        echo "<script>alert('Invalid file type for cover photo.');</script>";
    }
}


// Handle Cover Photo Deletion
if (isset($_POST['delete_cover_photo'])) {
    // First, fetch the current cover photo file path from the database
    $selectQuery = "SELECT cover_photo FROM about WHERE id = 1";
    $result = $conn->query($selectQuery);
    $row = $result->fetch_assoc();

    if ($row) {
        $current_cover_photo = $row['cover_photo'];
        $file_path = "../uploads/about_cover/" . basename($current_cover_photo);

        // Delete the current cover photo file if it exists
        if (file_exists($file_path)) {
            unlink($file_path); // Delete the old cover photo
        }
    }

    // Update the database to set the cover photo to the default one
    $updateQuery = "UPDATE about SET cover_photo='/JOB/uploads/default/COVER.jpg' WHERE id=1";
    $stmt = $conn->prepare($updateQuery);
    $stmt->execute();

    // Redirect using JavaScript
    echo '<script>window.location.href = "about.php";</script>';
    exit();
}

    }
}
?>



    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>About Us - Zamboanga City PESO Job Portal</title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
        <!-- Font Awesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <!-- Custom CSS -->
        <link rel="stylesheet" href="/JOB/assets/about.css">
        <style>
            /* Ensure the hero section allows absolute positioning */
.hero-section {
    position: relative;
    background-size: cover;
    background-position: center;
    height: 50vh; /* Adjust as needed */
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

/* Button Wrapper (Top Right) */
.hero-section .position-absolute {
    z-index: 10; /* Ensure button is on top */
}

/* Button Styling */
.hero-section .btn-light {
    background-color: rgba(255, 255, 255, 0.85); /* Slight transparency */
    padding: 10px 20px;
    
    transition: 0.3s ease-in-out;
}

/* Hover Effect */
.hero-section .btn-light:hover {
    color:#4c6ef5;
    background-color: white;
    transform: translateY(-2px);
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
}

/* Adjust title, subtitle, and button size for small screens */
@media (max-width: 900px) {
  .hero-section {
    padding-top: 5px;
    background-size: cover;
    background-position: center;
  }

  /* Minimize the title */
  .hero-section h1 {
    font-size: 1.6rem; /* Reduced from default */
  }

  /* Minimize the subtitle */
  .hero-section p {
    font-size: 0.9rem; /* Slightly smaller */
  }

  /* Adjust the camera button for admins and users */
  .btn-light {
    font-size: 0.8rem;
    padding: 6px 10px; /* Smaller padding */
  }

  /* Center content properly */
  .hero-content {
    padding: 20px;
  }
}


        </style>
    </head>
    <body>
<!-- Hero Section -->
<div class="hero-section position-relative" style="background-image: url('../uploads/about_cover/<?php echo htmlspecialchars($about['cover_photo']); ?>'); background-size: cover; background-position: center;">
    <!-- Button Wrapper -->
    <div class="position-absolute top-0 end-0 p-3">
        <?php if ($is_admin): ?>
            <!-- Admin sees "Edit Cover" -->
            <button type="button" class="btn btn-light shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadCoverPhotoModal">
                <i class="fas fa-camera"></i>
            </button>
        <?php else: ?>
            <!-- Non-admins/guests see "View Cover" -->
            <button type="button" class="btn btn-light shadow-sm" data-bs-toggle="modal" data-bs-target="#viewPhotoModal">
                <i class="fas fa-camera"></i>
            </button>
        <?php endif; ?>
    </div>

    <!-- Hero Content -->
    <div class="hero-content text-center">
        <h1>About Us</h1>
        <?php if ($is_admin): ?>
            <!-- Clickable Paragraph for Admins -->
            <p id="editableParagraph" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#editHeroTextModal">
                <?php echo htmlspecialchars($about['hero_text'] ?? 'Empowering the community through employment opportunities.'); ?>
            </p>
        <?php else: ?>
            <!-- Static Paragraph for Non-Admins -->
            <p>
                <?php echo htmlspecialchars($about['hero_text'] ?? 'Empowering the community through employment opportunities.'); ?>
            </p>
        <?php endif; ?>
    </div>
</div>




        <!-- Mission and Vision Section -->
        <div class="container mission-vision mt-5">
            <div class="row">
                <div class="col-md-6 fade-in">
                    <div class="mission-vision-card">
                        <i class="fas fa-bullseye"></i>
                        <h3>Our Mission</h3>
                        <p><?php echo htmlspecialchars($about['mission']); ?></p>
                        <?php if ($is_admin): ?>
                            <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#editMissionModal">
                                Edit Mission
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6 fade-in">
                    <div class="mission-vision-card">
                        <i class="fas fa-eye"></i>
                        <h3>Our Vision</h3>
                        <p><?php echo htmlspecialchars($about['vision']); ?></p>
                        <?php if ($is_admin): ?>
                            <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#editVisionModal">
                                Edit Vision
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>



        <!-- Modal for Editing Hero Text -->
        <div class="modal fade" id="editHeroTextModal" tabindex="-1" aria-labelledby="editHeroTextModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editHeroTextModalLabel">Edit Hero Text</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <textarea class="form-control" name="hero_text" rows="3" required><?php echo htmlspecialchars($about['hero_text'] ?? 'Empowering the community through employment opportunities.'); ?></textarea>
                    </div>
                    <div class="modal-footer">
                        <button style="background-color:#007bff; box-shadow:none;" type="submit" name="update_hero_text" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        
                    </div>
                </form>
            </div>
        </div>
    </div>


<!-- Upload Cover Photo Modal (Only for Admins) -->
<?php if ($is_admin): ?>
<div class="modal fade" id="uploadCoverPhotoModal" tabindex="-1" aria-labelledby="uploadCoverPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" id="uploadCoverPhotoForm" >
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadCoverPhotoModalLabel">Upload Cover Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="file" class="form-control" name="cover_photo" id="coverPhotoInput" accept="image/*" required>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewPhotoModal">
                        View Photo
                    </button>
                    <div class="d-flex gap-2">
                        <button style="background-color:#007bff; box-shadow:none;" type="submit" class="btn btn-primary">Upload</button>
                        <button type="button" id="deleteCoverPhotoButton" class="btn btn-light">Remove</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>


<!-- View Photo Modal (For Everyone) -->
<div class="modal fade" id="viewPhotoModal" tabindex="-1" aria-labelledby="viewPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewPhotoModalLabel">Cover Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <!-- Full-Sized Image -->
                <img src="../uploads/about_cover/<?= htmlspecialchars($cover_photo) ?>" alt="Cover Photo" id="fullSizedImage" class="img-fluid" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>



    <!-- Delete Cover Photo Modal -->
<div class="modal fade" id="deleteCoverPhotoModal" tabindex="-1" aria-labelledby="deleteCoverPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCoverPhotoModalLabel">Delete Cover Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete your cover photo? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button style="background-color:#007bff; box-shadow:none;" type="button" class="btn btn-primary" id="confirmDeleteCoverPhoto">Delete</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                
            </div>
        </div>
    </div>
</div>


        <!-- Edit Mission Modal -->
        <div class="modal fade" id="editMissionModal" tabindex="-1" aria-labelledby="editMissionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editMissionModalLabel">Edit Mission</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <textarea class="form-control" name="mission" rows="5" required><?php echo htmlspecialchars($about['mission']); ?></textarea>
                        </div>
                        <div class="modal-footer">
                            
                            <button style="background-color:#007bff; box-shadow:none;" type="submit" name="update_mission" class="btn btn-primary">Save Changes</button>
                            <button type="button" class="btn light" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Vision Modal -->
        <div class="modal fade" id="editVisionModal" tabindex="-1" aria-labelledby="editVisionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editVisionModalLabel">Edit Vision</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <textarea class="form-control" name="vision" rows="5" required><?php echo htmlspecialchars($about['vision']); ?></textarea>
                        </div>
                        <div class="modal-footer">
                            
                            <button style="background-color:#007bff; box-shadow:none;"  type="submit" name="update_vision" class="btn btn-primary">Save Changes</button>
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        

        <!-- Bootstrap JS -->

        <script>

document.addEventListener('DOMContentLoaded', function () {
    const coverPhotoInput = document.getElementById('coverPhotoInput');
    const fullSizedImage = document.getElementById('fullSizedImage');

    // Set the initial image source to the current cover photo
    fullSizedImage.src = fullSizedImage.src || '../uploads/about_cover/<?= htmlspecialchars($cover_photo) ?>';

    // Update the image preview when a new file is selected
    coverPhotoInput.addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            if (!file.type.startsWith('image/')) {
                alert('Please select a valid image file.');
                coverPhotoInput.value = ''; // Clear the input
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                fullSizedImage.src = e.target.result; // Update the image source
            };
            reader.readAsDataURL(file);
        } else {
            // If no file is selected, revert to the current cover photo
            fullSizedImage.src = '../uploads/about_cover/<?= htmlspecialchars($cover_photo) ?>';
        }
    });
});


    // Handle "Delete Cover Photo" button click
    document.getElementById('deleteCoverPhotoButton').addEventListener('click', function () {
        // Show the modal when the delete button is clicked
        const modal = new bootstrap.Modal(document.getElementById('deleteCoverPhotoModal'));
        modal.show();
    });

    // Handle the confirmation to delete cover photo
    document.getElementById('confirmDeleteCoverPhoto').addEventListener('click', function () {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                window.location.reload(); // Reload the page after deletion
            }
        };
        xhr.send('delete_cover_photo=1');

        // Close the modal after deletion
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteCoverPhotoModal'));
        modal.hide();
    });

    // Check if the user is an admin (this should match your PHP logic)
    const isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;
</script>

    </body>
    </html>

    <?php include '../includes/footer.php'; ?>

    <?php
    // End output buffering
    ob_end_flush();