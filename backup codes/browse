<?php
include '../includes/config.php';
include '../includes/header.php';

// Fetch user role and user_id from session if available
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';

// Handle search input
$search_filter = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $conn->real_escape_string($_GET['search']);
    $search_filter = " AND j.title LIKE '%$search_term%'";
}

// Handle category selection
$category_filter = "";
if (isset($_GET['category']) && is_numeric($_GET['category'])) {
    $category_id = $_GET['category'];
    $category_filter = " AND (j.category_id = $category_id OR jc.category_id = $category_id)";
}

// Handle position selection
$position_filter = "";
if (isset($_GET['position']) && is_numeric($_GET['position'])) {
    $position_id = $_GET['position'];
    $position_filter = " AND (jp.position_id = $position_id)";
}

// Handle location selection
$location_filter = "";
if (isset($_GET['location']) && !empty($_GET['location'])) {
    $location = $conn->real_escape_string($_GET['location']);
    $location_filter = " AND j.location = '$location'";
}

// Build the query for all jobs with joins to position and category tables
$query_all_jobs = "
    SELECT DISTINCT j.* 
    FROM jobs j
    LEFT JOIN job_categories jc ON j.id = jc.job_id
    LEFT JOIN job_positions_jobs jp ON j.id = jp.job_id
    WHERE 1=1
    $search_filter
    $category_filter
    $position_filter
    $location_filter
    AND j.status = 'approved'  -- Only fetch approved jobs
    ORDER BY j.created_at DESC
";

// Build the query for saved jobs (Only for logged-in users)
if ($user_id) {
    $query_saved_jobs = "
    SELECT DISTINCT j.* 
    FROM saved_jobs sj
    JOIN jobs j ON sj.job_id = j.id
    LEFT JOIN job_categories jc ON j.id = jc.job_id
    LEFT JOIN job_positions_jobs jp ON j.id = jp.job_id
    WHERE sj.user_id = $user_id
    $search_filter
    $category_filter
    $position_filter
    $location_filter
    AND j.status = 'approved'  -- Only fetch approved jobs
    ORDER BY sj.saved_at DESC
";
} else {
    // If user is not logged in, show empty result
    $query_saved_jobs = "SELECT * FROM jobs WHERE 1 = 0"; // This query returns no jobs if not logged in
}

// Build the query for jobs applied by the user (Visible only to logged-in users)
if ($user_id) {
    $query_applied_jobs = "
    SELECT DISTINCT j.* 
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    LEFT JOIN job_categories jc ON j.id = jc.job_id
    LEFT JOIN job_positions_jobs jp ON j.id = jp.job_id
    WHERE a.user_id = $user_id
    $search_filter
    $category_filter
    $position_filter
    $location_filter
    AND j.status = 'approved'  -- Only fetch approved jobs
    ORDER BY a.applied_at DESC
";
} else {
    // If user is not logged in, show empty result
    $query_applied_jobs = "SELECT * FROM jobs WHERE 1 = 0";
}

// Build the query for jobs with applicants (Visible only to admins)
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $query_jobs_with_applicants = "
        SELECT DISTINCT j.* 
        FROM jobs j
        JOIN applications a ON j.id = a.job_id
        LEFT JOIN job_categories jc ON j.id = jc.job_id
        LEFT JOIN job_positions_jobs jp ON j.id = jp.job_id
        WHERE a.status = 'pending'  -- Ensure only jobs with pending applicants are shown
        $search_filter
        $category_filter
        $position_filter
        $location_filter
        GROUP BY j.id
        ORDER BY j.created_at ASC
    ";
} else {
    // If not admin, show empty result
    $query_jobs_with_applicants = "SELECT * FROM jobs WHERE 1 = 0";
}

// Handle search input for my_jobs (employer)
$search_filter_my_jobs = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $conn->real_escape_string($_GET['search']);
    $search_filter_my_jobs = " AND j.title LIKE '%$search_term%'";
}

// Handle category selection for my_jobs
$category_filter_my_jobs = "";
if (isset($_GET['category']) && is_numeric($_GET['category'])) {
    $category_id = $_GET['category'];
    $category_filter_my_jobs = " AND (j.category_id = $category_id OR jc.category_id = $category_id)";
}

// Handle position selection for my_jobs
$position_filter_my_jobs = "";
if (isset($_GET['position']) && is_numeric($_GET['position'])) {
    $position_id = $_GET['position'];
    $position_filter_my_jobs = " AND (jp.position_id = $position_id)";
}

// Handle location selection for my_jobs
$location_filter_my_jobs = "";
if (isset($_GET['location']) && !empty($_GET['location'])) {
    $location = $conn->real_escape_string($_GET['location']);
    $location_filter_my_jobs = " AND j.location = '$location'";
}

// Build the query for employer's own jobs with sorting and filtering
if (isset($_SESSION['role']) && $_SESSION['role'] === 'employer') {
    $query_my_jobs = "
        SELECT DISTINCT j.* 
        FROM jobs j
        LEFT JOIN job_categories jc ON j.id = jc.job_id
        LEFT JOIN job_positions_jobs jp ON j.id = jp.job_id
        WHERE j.employer_id = ? 
        $search_filter_my_jobs
        $category_filter_my_jobs
        $position_filter_my_jobs
        $location_filter_my_jobs
        ORDER BY j.created_at DESC  -- You can adjust this sorting as needed
    ";
    $stmt = $conn->prepare($query_my_jobs);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // If not logged in as an employer, show an empty result
    $result = $conn->query("SELECT * FROM jobs WHERE 1 = 0");
}

// Determine which tab is active
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'all'; // Default to 'all'

// Execute the appropriate query based on the active tab
if ($active_tab === 'saved' && $user_id) {
    $result = $conn->query($query_saved_jobs);
} elseif ($active_tab === 'applied' && $user_id) {
    $result = $conn->query($query_applied_jobs);
} elseif ($active_tab === 'applicants' && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $result = $conn->query($query_jobs_with_applicants);
} elseif ($active_tab === 'my_jobs' && isset($_SESSION['role']) && $_SESSION['role'] === 'employer') {
    // Query to fetch only the employer's own jobs with filters applied
    // The query is already executed above, and the result is stored in $result
} else {
    $result = $conn->query($query_all_jobs); // Default to all jobs
}

// Fetch categories for the dropdown
$category_query = "SELECT * FROM categories ORDER BY name ASC";
$category_result = $conn->query($category_query);

// Fetch positions for the position dropdown (assuming you have the `job_positions` table)
$position_query = "SELECT * FROM job_positions ORDER BY position_name ASC";
$position_result = $conn->query($position_query);

// Fetch barangay names for the location dropdown (Use a separate variable)
$barangay_query = "SELECT name FROM barangay ORDER BY name ASC";
$barangay_result = $conn->query($barangay_query);


// Fetch the latest cover photo from the 'browse' table
$cover_query = "SELECT cover_photo FROM browse ORDER BY id DESC LIMIT 1";
$cover_result = $conn->query($cover_query);
$cover_row = $cover_result->fetch_assoc();
$cover_photo = $cover_row ? $cover_row['cover_photo'] : '/JOB/uploads/default/COVER.jpg'; // Default if no cover photo is set

// Handle Cover Photo Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_cover'])) {
    if (!empty($_FILES['cover_photo']['name'])) {
        $target_dir = "../uploads/browse_cover/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Ensure directory exists
        }

        // First, check if there's an existing cover photo in the database
        $select_query = "SELECT cover_photo FROM browse LIMIT 1";
        $result = $conn->query($select_query);
        $row = $result->fetch_assoc();

        // If a cover photo already exists, delete it
        if ($row) {
            $old_cover_photo = $row['cover_photo'];
            $old_file_path = "../uploads/browse_cover/" . basename($old_cover_photo);

            // Delete the old cover photo file from the server if it exists
            if (file_exists($old_file_path)) {
                unlink($old_file_path); // Delete the file from the server
            }

            // Also, delete the old cover photo entry from the database
            $delete_query = "DELETE FROM browse";
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
                $stmt = $conn->prepare("INSERT INTO browse (cover_photo) VALUES (?)");
                $stmt->bind_param("s", $target_file);

                if ($stmt->execute()) {
                    $_SESSION['success'] = "Cover photo updated successfully.";
                    echo '<script>window.location.href="browse.php";</script>';
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
    } else {
        $_SESSION['error'] = "Please select a file to upload.";
    }
}


// Handle Cover Photo Deletion (Admin Only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_cover']) && $user_role === 'admin') {
    // First, fetch the current cover photo file path from the database
    $select_query = "SELECT cover_photo FROM browse LIMIT 1";
    $result = $conn->query($select_query);
    $row = $result->fetch_assoc();

    if ($row) {
        $cover_photo = $row['cover_photo'];
        $file_path = "../uploads/browse_cover/" . basename($cover_photo);

        // Delete the cover photo file from the server if it exists
        if (file_exists($file_path)) {
            unlink($file_path); // Delete the file from the server
        }

        // Now, delete the cover photo entry from the database
        $delete_query = "DELETE FROM browse";
        if ($conn->query($delete_query)) {
            $_SESSION['success'] = "Cover photo removed successfully.";
            echo '<script>window.location.href="browse.php";</script>';
            exit();
        } else {
            $_SESSION['error'] = "Failed to delete cover photo from the database.";
        }
    } else {
        $_SESSION['error'] = "No cover photo found to remove.";
    }
}


// Fetch user ID from session
$user_id = $_SESSION['user_id'] ?? null;

// Check if the user is accessing the "Jobs Applied" tab
if ($user_id && isset($_GET['tab']) && $_GET['tab'] === 'applied') {
    // Mark all accepted/rejected applications as viewed
    $mark_as_read_query = "
        UPDATE applications 
        SET user_viewed = 1 
        WHERE user_id = ? AND status IN ('accepted', 'rejected')
    ";
    $stmt = $conn->prepare($mark_as_read_query);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        error_log("Successfully marked all accepted/rejected applications as viewed for user_id: $user_id");
    } else {
        error_log("Error marking applications as viewed: " . $stmt->error);
    }
}
$browse_query = $conn->query("SELECT * FROM browse LIMIT 1");
$browse_data = $browse_query->fetch_assoc();

if ($browse_data) {
    $hero_title = $browse_data['hero_title'];
    $hero_subtitle = $browse_data['hero_subtitle'];
} else {
    // Set default values if no data is returned
    $hero_title = 'Discover Your Next Opportunity';
    $hero_subtitle = 'Search for the jobs that fit your expertise and apply with confidence.';
}

// Check if there is a login message to display
if (isset($_SESSION['login_message'])) {
    $message = $_SESSION['login_message'];
    unset($_SESSION['login_message']); // Clear the message after displaying it
} else {
    $message = '';
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/JOB/assets/browse.css">
    <style>
        /* Ensure hero section has relative positioning */
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
    z-index: 10; /* Ensure button is always clickable */
}

/* Button Styling */
.hero-section .btn-light {
    background-color: rgba(255, 255, 255, 0.85); /* Slight transparency */
    padding: 10px 20px;
    
    transition: 0.3s ease-in-out;
}

.modal {
    z-index: 1050 !important; /* Bootstrap default is 1050, ensuring it's above the hero section */
}

.modal-backdrop {
    z-index: 1040 !important; /* Ensure backdrop is behind the modal but above other elements */
}

.hero-section {
    position: relative;
    z-index: 1; /* Ensure hero section is below the modal */
}



.editable {
    cursor: pointer;
    border-bottom: none;
}

.editable:hover {
    color: rgba(255, 255, 255, 0.8);
}

.transparent-dropdown {
        background-color: transparent; /* Transparent background */
        color: #fff; /* White text color */
        border: 2px solid #fff; /* White border to make it look consistent with dark background */
    }

    /* Option Styling */
    .transparent-dropdown option {
        background-color: white; /* Dark background for options */
        color:dimgrey; /* White text for options */
    }

/* Adjust dropdown size and alignment for small screens */
@media (max-width: 900px) {
  .hero-section {
    padding-top: 20px; /* Prevent content from being hidden by the fixed header */
  }

  /* Slightly reduce the title and subtitle size */
  .hero-section h1 {
    font-size: 1.4rem; /* Reduced from 1.8rem */
  }

  .hero-section p {
    font-size: 0.8rem; /* Reduced from 1rem */
  }

  #search-input {
    font-size: 0.9rem;
    padding: 10px;
  }

  /* Adjust filter dropdowns to be smaller and stay horizontal */
  #filters-container {
    display: flex;
    justify-content: center;
    gap: 8px; /* Add spacing between the dropdowns */
    flex-wrap: nowrap; /* Prevent wrapping */
  }

  .filter-item select {
    font-size: 0.8rem;
    padding: 6px 10px;
    min-width: 100px; /* Ensure they remain aligned horizontally */
    border-radius: 15px; /* Adjust the roundness */
  }

  /* Optional: Limit the max-width if needed */
  .filter-item {
    flex: 1 1 auto;
    max-width: 150px;
  }

  /* Hide tabs */
  .hero-nav-tabs-wrapper {
    display: none !important;
    visibility: hidden !important;
  }

  /* Minimize the Post Job button */
  .btn-outline-customs {
    font-size: 0.8rem; /* Slightly smaller */
    padding: 8px 15px; /* Adjusted padding */
  }

  
}

/* Example of a simple media query for mobile */
@media (max-width: 768px) {
    #search-input::placeholder {
        content: "Type a keyword";
    }
}



/* MOBILE LIST VIEW */
@media (max-width: 900px) {
    /* Stack the job card content vertically */
    .job-card .row {
        flex-direction: column; /* Stack the image and job details */
    }

    /* Adjust job thumbnail to be on top and full width */
    .job-thumbnail {
        width: 100%;  /* Full width of the container */
        height: auto; /* Maintain aspect ratio */
        max-height: 300px; /* Set a max height for the thumbnail */
        object-fit: cover; /* Ensure the image doesn't stretch or distort */
        margin-bottom: 12px; /* Space below the thumbnail */
    }

    /* Job details (title, description, etc.) */
    .job-card .card-body {
        padding: 15px;
        text-align: center; /* Center align the text */
    }

    /* Job title */
    .job-card .job-title {
        font-size: 1.5rem; /* Slightly larger font for mobile */
        margin-bottom: 8px;
    }

    /* Job description */
    .job-card .card-description {
        font-size: 1rem;
        margin-bottom: 10px;
        display:none;
    }

    /* Adjust the View Details button */
    .job-card .btn-outline-primary {
        width: 100%; /* Make button span the full width */
        margin-top: 15px; /* Add some space above the button */
    }
}

/* Button Styling */
.btn-outline-primary {
    color: black; /* Text color */
    text-decoration: none; /* Removes underline */
    background: transparent; /* Keeps it transparent */
    border: 1px solid black; /* Adds a black border */
    transition: all 0.3s ease-in-out;
}

.btn-outline-primary:hover {
    background: transparent;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Soft shadow effect */
    border-color: #4c6ef5;
    color: #4c6ef5;
}

/* Active state styling */
.btn-outline-primary.active {
    background:transparent;
    color: #ff4500; /* Text color when active */
    border-color: #ff4500;
}

/* Media query to hide the toggle button on mobile devices */
@media (max-width: 768px) {
    .btn-group {
        display: none; /* Hides the toggle button group */
    }
}




    </style>
</head>
<body>


<!-- Hero Section with Background Image -->
<section class="hero-section position-relative text-center">
    <!-- Background Cover Image -->
    <div class="cover-image" style="background-image: url('<?= htmlspecialchars($cover_photo) ?>');"></div>

    <!-- Overlay for Readability -->
    <div class="overlay"></div>

    <!-- Cover Photo Button (Top-Right Corner) -->
    <div class="position-absolute top-0 end-0 p-3">
        <button type="button" class="btn btn-light shadow-sm" data-bs-toggle="modal" 
                data-bs-target="<?php echo $user_role === 'admin' ? '#uploadCoverPhotoModal' : '#viewPhotoModal'; ?>">
            <i class="fas fa-camera"></i> <?php echo $user_role === 'admin' ? '' : ''; ?>
        </button>
    </div>

    <!-- Content -->
    <div class="container position-relative z-2 py-5">
<!-- Clickable H1 (Only Admins Can Edit) -->
<?php if ($user_role === 'admin'): ?>
    <h1 class="fw-bold text-white editable" data-bs-toggle="modal" data-bs-target="#editTitleModal">
        <?= htmlspecialchars($hero_title) ?>
    </h1>
<?php else: ?>
    <h1 class="fw-bold text-white">
        <?= htmlspecialchars($hero_title) ?>
    </h1>
<?php endif; ?>

<!-- Clickable P (Only Admins Can Edit) -->
<?php if ($user_role === 'admin'): ?>
    <p class="lead text-light mb-4 editable" data-bs-toggle="modal" data-bs-target="#editSubtitleModal">
        <?= htmlspecialchars($hero_subtitle) ?>
    </p>
<?php else: ?>
    <p class="lead text-light mb-4">
        <?= htmlspecialchars($hero_subtitle) ?>
    </p>
<?php endif; ?>






<!-- Search and Filter Form -->
<form action="" method="get" class="row gx-2 gy-2 justify-content-center" id="search-form">
    <!-- Hidden Input for Active Tab -->
    <input type="hidden" name="tab" value="<?= htmlspecialchars($active_tab) ?>">

<!-- Search Bar and Toggle Button Container -->
<div class="col-lg-8 col-md-10 col-12 d-flex justify-content-center align-items-center position-relative">
<!-- Search Bar -->
<div class="flex-grow-1">
    <input type="text" name="search" class="form-control form-control-lg w-100 text-center" 
        placeholder="Looking for something specific? Try a job title or category..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" id="search-input">
</div>


    <!-- Toggle Button (SVG Icon) -->
    <button type="button" id="toggle-filter" style="border:none;" class="btn btn-outline-customz  position-absolute end-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-adjustments-plus">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M4 10a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
            <path d="M6 4v4" />
            <path d="M6 12v8" />
            <path d="M13.958 15.592a2 2 0 1 0 -1.958 2.408" />
            <path d="M12 4v10" />
            <path d="M12 18v2" />
            <path d="M16 7a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
            <path d="M18 4v1" />
            <path d="M18 9v3" />
            <path d="M16 19h6" />
            <path d="M19 16v6" />
        </svg>
    </button>
</div>


    <!-- Filters Container (Initially Hidden) -->
    <div id="filters-container" class="row gx-2 gy-2 mt-3 w-100 justify-content-center" style="display: none;">
        <!-- Category Dropdown -->
        <div class="col-lg-2 col-md-3 col-6 filter-item">
            <select name="category" class="form-select form-select-m rounded-pill transparent-dropdown" onchange="submitForm()">
                <option value="">Category</option>
                <?php while ($category = $category_result->fetch_assoc()): ?>
                    <option value="<?= $category['id'] ?>" <?= isset($_GET['category']) && $_GET['category'] == $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Position Dropdown -->
        <div class="col-lg-2 col-md-3 col-6 filter-item">
            <select name="position" class="form-select form-select-m rounded-pill transparent-dropdown" onchange="submitForm()">
                <option value="">Position</option>
                <?php while ($position = $position_result->fetch_assoc()): ?>
                    <option value="<?= $position['id'] ?>" <?= isset($_GET['position']) && $_GET['position'] == $position['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($position['position_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Location Dropdown -->
        <div class="col-lg-2 col-md-3 col-6 filter-item">
            <select name="location" class="form-select form-select-m rounded-pill transparent-dropdown" onchange="submitForm()">
                <option value="">Location</option>
                <?php if ($barangay_result->num_rows > 0): ?>
                    <?php while ($row = $barangay_result->fetch_assoc()): ?>
                        <?php $barangay_name = htmlspecialchars($row['name']); ?>
                        <option value="<?= $barangay_name ?>" <?= isset($_GET['location']) && $_GET['location'] == $barangay_name ? 'selected' : '' ?>>
                            <?= $barangay_name ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>
</form>

<!-- Admin and Employer Buttons: Post a New Job -->
<?php if ($user_role === 'admin' || $user_role === 'employer'): ?>
    <div class="text-center mt-4">
        <?php if ($user_role === 'admin'): ?>
            <!-- Redirects to admin's post job page -->
            <a href="../admin/post_job.php" class="btn btn-outline-customs btn-lg rounded-pill">Post a New Job</a>
        <?php elseif ($user_role === 'employer'): ?>
            <!-- Redirects to employer's post job page -->
            <a href="../employers/post_job.php" class="btn btn-outline-customs btn-lg rounded-pill">Post a New Job</a>
        <?php endif; ?>
    </div>
<?php endif; ?>



    </div>


<!-- Tab Navigation (Positioned inside the Hero Section) -->
<div class="hero-nav-tabs-wrapper position-absolute bottom-0 start-50 translate-middle-x mb-0">
    <ul class="nav hero-nav-tabs justify-content-center mb-0">
        <li class="hero-nav-item">
            <a class="hero-nav-link <?= $active_tab === 'all' ? 'active' : '' ?>" href="?tab=all">All Jobs</a>
        </li>
        <?php if ($user_id): ?>
            <li class="hero-nav-item">
                <a class="hero-nav-link <?= $active_tab === 'saved' ? 'active' : '' ?>" href="?tab=saved">Saved</a>
            </li>
            <!-- New Tab for Jobs Applied (Visible to Users Only) -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                <li class="hero-nav-item">
                    <a class="hero-nav-link <?= $active_tab === 'applied' ? 'active' : '' ?>" href="?tab=applied">
                        Applied
                        <?php
                        // Query to count unread jobs with responses (accepted/rejected)
                        $response_count_query = "
                            SELECT COUNT(*) AS count 
                            FROM applications 
                            WHERE user_id = $user_id AND status IN ('accepted', 'rejected') AND user_viewed = 0
                        ";
                        $response_count_result = $conn->query($response_count_query);
                        $response_count = $response_count_result->fetch_assoc()['count'];
                        if ($response_count > 0): ?>
                            <span class="badge bg-danger"><?= $response_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <!-- New Tab for Jobs with Applicants (Visible to Admins Only) -->
            <li class="hero-nav-item">
                <a class="hero-nav-link <?= $active_tab === 'applicants' ? 'active' : '' ?>" href="?tab=applicants">
                    New Applicants
                    <?php
                    // Query to count jobs with pending applicants
                    $pending_applicants_query = "
                        SELECT COUNT(DISTINCT j.id) AS count 
                        FROM jobs j
                        INNER JOIN applications a ON j.id = a.job_id
                        WHERE a.status = 'pending' -- Ensure only pending applications are counted
                    ";
                    $pending_applicants_result = $conn->query($pending_applicants_query);
                    $pending_applicants_count = $pending_applicants_result->fetch_assoc()['count'];
                    if ($pending_applicants_count > 0): ?>
                        <span class="badge bg-danger"><?= $pending_applicants_count ?></span>
                    <?php endif; ?>
                </a>
            </li>
        <?php endif; ?>
        
        <!-- New Tab for "My Jobs" (Visible to Employers Only) -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'employer'): ?>
            <li class="hero-nav-item">
                <a class="hero-nav-link <?= $active_tab === 'my_jobs' ? 'active' : '' ?>" href="?tab=my_jobs">
                    My Jobs
                    <?php
                    // Query to count the employer's jobs with pending applicants
                    $pending_applicants_query = "
                        SELECT COUNT(DISTINCT j.id) AS count 
                        FROM jobs j
                        INNER JOIN applications a ON j.id = a.job_id
                        WHERE j.employer_id = ? AND a.status = 'pending'
                    ";
                    $pending_applicants_stmt = $conn->prepare($pending_applicants_query);
                    $pending_applicants_stmt->bind_param("i", $_SESSION['user_id']);
                    $pending_applicants_stmt->execute();
                    $pending_applicants_result = $pending_applicants_stmt->get_result();
                    $pending_applicants_count = $pending_applicants_result->fetch_assoc()['count'];
                    if ($pending_applicants_count > 0): ?>
                        <span class="badge bg-danger"><?= $pending_applicants_count ?></span>
                    <?php endif; ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</div>

</section>

<!-- Toggle Button -->
<br>
<div class="container mb-4 d-flex justify-content-center">
    <div class="btn-group" role="group" aria-label="View Toggle">
        <button type="button" class="btn btn-outline-primary active" id="list-view-btn">
            <i class="fas fa-list"></i>
        </button>
        <button type="button" class="btn btn-outline-primary" id="grid-view-btn">
            <i class="fas fa-th-large"></i>
        </button>
    </div>
</div>



<!-- List View Container -->
<div id="list-view" class="album py-5 bg-light">
    <div class="container">
        <div class="row row-cols-1 g-4">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm job-card position-relative">
                            
                            <!-- Dropdown Button on Top Left Corner -->
                            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || ($row['employer_id'] == $_SESSION['user_id']))): ?>
                                <div class="dropdown position-absolute top-0 start-0 m-2">
                                    <button class="btn btn-light dropdown-toggle btn-minimal" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li><a class="dropdown-item" href="edit_job_browse.php?id=<?= $row['id'] ?>&source=browse">Edit</a></li>
                                        <li><button class="dropdown-item btn-delete" type="button" data-bs-toggle="modal" data-bs-target="#deleteModal" data-job-id="<?= $row['id'] ?>">Delete</button></li>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <div class="row g-0">
                                <!-- Thumbnail on the left -->
                                <div class="col-md-4 position-relative"> <!-- Use col-md-4 for larger screens -->
                                    <?php if (!empty($row['thumbnail'])): ?>
                                        <img src="../<?= htmlspecialchars($row['thumbnail']) ?>" class="job-thumbnail" alt="Job Thumbnail" style="object-fit: cover; height: 290px; width: 100%;"> <!-- Original size on desktop -->
                                    <?php else: ?>
                                        <div class="job-thumbnail-placeholder" style="height: 250px; width: 100%;">No Image</div>
                                    <?php endif; ?>
                                </div>

                                <!-- Job Details on the right -->
                                <div class="col">
                                    <div class="card-body d-flex flex-column">
                                        <!-- Job Title -->
                                        <h5 class="card-title job-title"><?= htmlspecialchars($row['title']) ?></h5>

                                        <!-- Created At -->
                                        <small class="text-muted"><?= time_elapsed_string($row['created_at']) ?></small><br>

                                        <!-- Job Description -->
                                        <p class="card-description"><?= htmlspecialchars(substr($row['description'], 0, 333)) ?>...</p>

                                        <!-- Categories -->
                                        <?php if (!empty($row['categories'])): ?>
                                            <p class="card-category"><i class="fas fa-briefcase me-2"></i><?= htmlspecialchars($row['categories']) ?></p>
                                        <?php endif; ?>

                                        <!-- Additional Job Info: Positions, Location -->
                                        <?php if (!empty($row['positions'])): ?>
                                            <p class="card-positions"><i class="fas fa-users me-2"></i><?= htmlspecialchars($row['positions']) ?></p>
                                        <?php endif; ?>
                                        <p class="card-location"><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($row['location']) ?></p>

                                        <!-- Job Approval Status or Response Indicators -->
                                        <?php if ($active_tab === 'applied' && isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                                            <?php
                                            // Query to check if the job has a response
                                            $job_response_query = "
                                                SELECT status, user_viewed 
                                                FROM applications 
                                                WHERE job_id = {$row['id']} AND user_id = $user_id
                                            ";
                                            $job_response_result = $conn->query($job_response_query);
                                            $job_response = $job_response_result->fetch_assoc();
                                            if ($job_response && in_array($job_response['status'], ['accepted', 'rejected'])): ?>
                                                <span class="badge <?= $job_response['status'] === 'accepted' ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= ucfirst($job_response['status']) ?>
                                                </span>
                                                <?php if ($job_response['user_viewed'] == 0): ?>
                                                    <span class="badge bg-primary">New</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>

<!-- New Applicant Indicator for Admin or Employer -->
<div class="mt-auto">
    <a href="job.php?id=<?= $row['id'] ?>&mark_as_read=true" class="btn btn-outline-primary btn-sm">
        View Details 
        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'employer')): ?>
            <?php
            // If the user is an admin or employer, show the "New" badge if applicable
            if ($_SESSION['role'] === 'admin') {
                // For admin, show the new applicants for all jobs
                $new_applicants_query = "
                    SELECT COUNT(*) AS count 
                    FROM applications 
                    WHERE job_id = {$row['id']} AND status = 'pending'
                ";
            } elseif ($_SESSION['role'] === 'employer') {
                // For employer, show the new applicants only for their own jobs
                $new_applicants_query = "
                    SELECT COUNT(*) AS count 
                    FROM applications 
                    WHERE job_id = {$row['id']} AND status = 'pending' 
                    AND job_id IN (SELECT id FROM jobs WHERE employer_id = ?)
                ";
            }

            // Execute the query to count new applicants
            if (isset($new_applicants_query)) {
                if ($_SESSION['role'] === 'employer') {
                    $new_applicants_stmt = $conn->prepare($new_applicants_query);
                    $new_applicants_stmt->bind_param("i", $_SESSION['user_id']);
                    $new_applicants_stmt->execute();
                    $new_applicants_result = $new_applicants_stmt->get_result();
                } else {
                    $new_applicants_result = $conn->query($new_applicants_query);
                }

                $new_applicants_count = $new_applicants_result->fetch_assoc()['count'];
            }
            ?>
            <?php if ($new_applicants_count > 0): ?>
                <span class="badge bg-primary ms-2"><?= $new_applicants_count ?> New</span>
            <?php endif; ?>
        <?php endif; ?>
    </a>
</div>


                                    </div>
                                </div>
                            </div>

                            <!-- Save Flag positioned at the top-right of the card -->
                            <?php if ($user_id): ?>
                                <div title="Save job" class="save-flag position-absolute top-0 end-0 m-2" data-job-id="<?= $row['id'] ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M18 7v14l-6 -4l-6 4v-14a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4z" />
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-muted fs-5">No jobs found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Reset the result pointer before using it in the grid view -->
<?php mysqli_data_seek($result, 0); ?>

<!-- Grid View Container -->
<div id="grid-view" class="album py-5 bg-light" style="display: none;">
    <div class="container">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm job-card">
                            <!-- Thumbnail -->
                            <div class="position-relative">
                                <?php if (!empty($row['thumbnail'])): ?>
                                    <img src="../<?= htmlspecialchars($row['thumbnail']) ?>" class="card-img-top job-thumbnail" alt="Job Thumbnail">
                                <?php else: ?>
                                    <div class="card-img-top placeholder-thumbnail">No Image</div>
                                <?php endif; ?>

                                <!-- Admin/Employer Dropdown (Edit & Delete buttons, visible for employer or admin) -->
                                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || ($row['employer_id'] == $_SESSION['user_id']))): ?>
                                    <div class="dropdown position-absolute top-0 start-0 m-2">
                                        <button class="btn btn-light dropdown-toggle btn-minimal" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <li><a class="dropdown-item" href="edit_job_browse.php?id=<?= $row['id'] ?>&source=browse">Edit</a></li>
                                            <li><button class="dropdown-item btn-delete" type="button" data-bs-toggle="modal" data-bs-target="#deleteModal" data-job-id="<?= $row['id'] ?>">Delete</button></li>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <!-- Save Flag -->
                                <?php if ($user_id): ?>
                                    <div title="Save job" class="save-flag" data-job-id="<?= $row['id'] ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-bookmark">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M18 7v14l-6 -4l-6 4v-14a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4z"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Card Body -->
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title job-title"><?= htmlspecialchars($row['title']) ?></h5>
                                <small class="text-muted"><?= time_elapsed_string($row['created_at']) ?></small><br>

                                <!-- Response Indicator for User -->
                                <?php if ($active_tab === 'applied' && isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                                    <?php
                                    // Query to check if the job has a response
                                    $job_response_query = "
                                        SELECT status, user_viewed 
                                        FROM applications 
                                        WHERE job_id = {$row['id']} AND user_id = $user_id
                                    ";
                                    $job_response_result = $conn->query($job_response_query);
                                    $job_response = $job_response_result->fetch_assoc();
                                    if ($job_response && in_array($job_response['status'], ['accepted', 'rejected'])): ?>
                                        <!-- Status Badge -->
                                        <span class="badge <?= $job_response['status'] === 'accepted' ? 'bg-success' : 'bg-danger' ?>">
                                            <?= ucfirst($job_response['status']) ?>
                                        </span>
                                        <!-- New Indicator -->
                                        <?php if ($job_response['user_viewed'] == 0): ?>
                                            <span class="badge bg-primary">New</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>

 <!-- New Applicant Indicator for Admin or Employer -->
<div class="mt-auto">
    <a href="job.php?id=<?= $row['id'] ?>&mark_as_read=true" class="btn btn-outline-primary btn-sm w-100">
        View Details
        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'employer')): ?>
            <?php
            // If the user is an admin or employer, show the "New" badge if applicable
            if ($_SESSION['role'] === 'admin') {
                // For admin, show the new applicants for all jobs
                $new_applicants_query = "
                    SELECT COUNT(*) AS count 
                    FROM applications 
                    WHERE job_id = {$row['id']} AND status = 'pending'
                ";
            } elseif ($_SESSION['role'] === 'employer') {
                // For employer, show the new applicants only for their own jobs
                $new_applicants_query = "
                    SELECT COUNT(*) AS count 
                    FROM applications 
                    WHERE job_id = {$row['id']} AND status = 'pending' 
                    AND job_id IN (SELECT id FROM jobs WHERE employer_id = ?)
                ";
            }

            // Execute the query to count new applicants
            if (isset($new_applicants_query)) {
                if ($_SESSION['role'] === 'employer') {
                    $new_applicants_stmt = $conn->prepare($new_applicants_query);
                    $new_applicants_stmt->bind_param("i", $_SESSION['user_id']);
                    $new_applicants_stmt->execute();
                    $new_applicants_result = $new_applicants_stmt->get_result();
                } else {
                    $new_applicants_result = $conn->query($new_applicants_query);
                }

                $new_applicants_count = $new_applicants_result->fetch_assoc()['count'];
            }
            ?>
            <?php if ($new_applicants_count > 0): ?>
                <span class="badge bg-primary ms-2"><?= $new_applicants_count ?> New</span>
            <?php endif; ?>
        <?php endif; ?>
    </a>
</div>


                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-muted fs-5">No jobs found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>






<!--FOR JOB LISTING/// Deletion Confirmation Modal/// FOR JOB LISTING -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this job listing? This action cannot be undone.
            </div>
            <div class="modal-footer">
            <a style="background-color:#007bff; box-shadow:none;" id="confirmDelete" href="#" class="btn btn-primary">Delete</a>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                
            </div>
        </div>
    </div>
</div>

<!-- Upload Cover Photo Modal -->
<div class="modal fade" id="uploadCoverPhotoModal" tabindex="-1" aria-labelledby="uploadCoverPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Upload Cover Form -->
            <form action="browse.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadCoverPhotoModalLabel">Upload Cover Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="file" class="form-control" name="cover_photo" id="coverPhotoInput" accept="image/*" required>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <?php if (!empty($cover_photo)): ?>
                        <!-- View Photo Button (Only visible if a cover photo exists) -->
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewPhotoModal">
                            View Photo
                        </button>
                        <div class="d-flex gap-2">
                            <button style="background-color:#007bff; box-shadow:none;" type="submit" class="btn btn-primary" name="upload_cover">Upload</button>
                            <!-- Trigger Delete Confirmation Modal -->
                            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">Remove</button>
                        </div>
                    <?php else: ?>
                        <!-- Save/Upload Button (Only visible if no cover photo exists) -->
                        <div class="d-flex justify-content-end w-100">
                            <button style="background-color:#007bff; box-shadow:none;" type="submit" class="btn btn-primary" name="upload_cover">Save</button>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Photo Modal -->
<div class="modal fade" id="viewPhotoModal" tabindex="-1" aria-labelledby="viewPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewPhotoModalLabel">Cover Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <!-- Full-Sized Image -->
                <img src="../uploads/<?= htmlspecialchars($cover_photo) ?>" alt="Cover Photo" id="fullSizedImage" class="img-fluid" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the cover photo? This action cannot be undone.
            </div>
            <div class="modal-footer">
            <form action="browse.php" method="POST">
                    <button style="background-color:#007bff; box-shadow:none;" type="submit" class="btn btn-primary" name="delete_cover">Remove</button>
                </form>
                <!-- Separate Delete Form -->
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                

            </div>
        </div>
    </div>
</div>


<!-- FOR TITLE AND SUBTITLE MODAL -->

<div class="modal fade" id="editTitleModal" tabindex="-1" aria-labelledby="editTitleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTitleModalLabel">Edit Title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="update_text_browse.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="field" value="hero_title">
                    <input type="text" name="value" class="form-control" value="<?= htmlspecialchars($hero_title) ?>" required>
                </div>
                <div class="modal-footer">
                    <button style="background-color: #007bff; box-shadow:none;" type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    
                </div>
            </form>
        </div>
    </div>
</div>



<div class="modal fade" id="editSubtitleModal" tabindex="-1" aria-labelledby="editSubtitleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSubtitleModalLabel">Edit Subtitle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="update_text_browse.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="field" value="hero_subtitle">
                    <textarea name="value" class="form-control" rows="3" required><?= htmlspecialchars($hero_subtitle) ?></textarea>
                </div>
                <div class="modal-footer">
                    <button style="background-color: #007bff; box-shadow:none;"  type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript for Saving Jobs -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const listViewBtn = document.getElementById('list-view-btn');
        const gridViewBtn = document.getElementById('grid-view-btn');
        const listView = document.getElementById('list-view');
        const gridView = document.getElementById('grid-view');

        // Default view (list view)
        listView.style.display = 'block';
        gridView.style.display = 'none';

        // Toggle to List View
        listViewBtn.addEventListener('click', function () {
            listView.style.display = 'block';
            gridView.style.display = 'none';
            listViewBtn.classList.add('active');
            gridViewBtn.classList.remove('active');
        });

        // Toggle to Grid View
        gridViewBtn.addEventListener('click', function () {
            listView.style.display = 'none';
            gridView.style.display = 'block';
            gridViewBtn.classList.add('active');
            listViewBtn.classList.remove('active');
        });
    });

    

document.addEventListener('DOMContentLoaded', function () {
    const coverPhotoInput = document.getElementById('coverPhotoInput');
    const fullSizedImage = document.getElementById('fullSizedImage');

    // Set the initial image source to the current cover photo
    fullSizedImage.src = fullSizedImage.src || '../uploads/<?= htmlspecialchars($cover_photo) ?>';

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
            fullSizedImage.src = '../uploads/<?= htmlspecialchars($cover_photo) ?>';
        }
    });
});
// When a delete button is clicked, set the job ID in the modal
const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const jobId = this.getAttribute('data-job-id');
            const confirmDeleteLink = document.getElementById('confirmDelete');
            confirmDeleteLink.href = 'delete_job_browse.php?id=' + jobId; // Set the delete URL with the correct job ID
        });
    });

document.querySelectorAll('.save-flag').forEach(flag => {
    const jobId = flag.dataset.jobId;

    // Check if the job is already saved
    fetch('../includes/check_saved.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ job_id: jobId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.is_saved) {
            flag.classList.add('saved');
        }
    });

    // Add click event to toggle save status
    flag.addEventListener('click', function () {
        fetch('../includes/toggle_save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ job_id: jobId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'saved') {
                flag.classList.add('saved');
            } else if (data.status === 'unsaved') {
                flag.classList.remove('saved');

                // If on the "Saved Jobs" tab, remove the job card from the DOM
                const activeTab = new URLSearchParams(window.location.search).get('tab');
                if (activeTab === 'saved') {
                    const jobCard = flag.closest('.col'); // Find the parent job card
                    if (jobCard) {
                        jobCard.remove(); // Remove the job card from the DOM
                    }
                }
            }
        });
    });
});



document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('toggle-filter');
    const filtersContainer = document.getElementById('filters-container');

    // Check if there's a stored state in localStorage
    const toggleState = localStorage.getItem('filters-toggled');

    // Default state should be hidden unless stored state says otherwise
    if (toggleState === 'true') {
        filtersContainer.style.display = 'flex'; // Show filters
    } else {
        filtersContainer.style.display = 'none'; // Hide filters (default)
    }

    // Add click event listener to toggle button
    toggleButton.addEventListener('click', function () {
        // Toggle the visibility of the filters container
        if (filtersContainer.style.display === 'none' || filtersContainer.style.display === '') {
            filtersContainer.style.display = 'flex';
            localStorage.setItem('filters-toggled', 'true'); // Store the state as open
        } else {
            filtersContainer.style.display = 'none';
            localStorage.setItem('filters-toggled', 'false'); // Store the state as closed
        }
    });
});

// Function to automatically submit the form when a filter is changed
function submitForm() {
    document.getElementById('search-form').submit();
}

// Debounce function to delay search input submission (to avoid instant refresh on every keystroke)
let debounceTimer;
document.getElementById('search-input').addEventListener('input', function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(function() {
        submitForm(); // Submit the form after 300ms delay
    }, 420);
});

// Call this function on logout or account change
function resetToggleState() {
    localStorage.removeItem('filters-toggled'); // Clear the stored toggle state
}

// Function to escape HTML entities
function escapeHtml(str) {
    return str.replace(/[&<>"/]/g, function (char) {
        switch (char) {
            case '&': return '&amp;';
            case '<': return '&lt;';
            case '>': return '&gt;';
            case '"': return '&quot;';
            
            case '/': return '&#x2F;';
        }
    });
}

// Get the message from the URL query parameter
const urlParams = new URLSearchParams(window.location.search);
const message = urlParams.get('message');

// Display SweetAlert2 notification if there is a message
if (message) {
    // Escape the message to avoid XSS
    const sanitizedMessage = escapeHtml(message);

    Swal.fire({
        title: "Successfully logged in!",
        text: sanitizedMessage,
        icon: "success", // You can remove this line if you don't want any icon
        showConfirmButton: true, // Show the close button
        confirmButtonText: "Close", // Customize the close button text
        timer: 5000, // Auto-close after 5 seconds
        timerProgressBar: true, // Show a progress bar
        showClass: {
            popup: 'swal2-noanimation', // Disable animation for the popup
            backdrop: 'swal2-noanimation' // Disable animation for the backdrop
        },
        hideClass: {
            popup: '', // No special class for hiding the popup
            backdrop: '' // No special class for hiding the backdrop
        }
    }).then(() => {
        // Remove the 'message' query parameter from the URL
        urlParams.delete('message');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, document.title, newUrl);
    });
}


// Function to change placeholder text
function updatePlaceholder() {
    const searchInput = document.getElementById('search-input');
    if (window.innerWidth <= 768) {  // For mobile or small screens
        searchInput.setAttribute('placeholder', 'Type a keyword (e.g. Animation)');
    } else {  // For larger screens
        searchInput.setAttribute('placeholder', 'Looking for something specific? Try a job title or category...');
    }
}

// Initial check when the page loads
updatePlaceholder();

// Update the placeholder on window resize
window.addEventListener('resize', updatePlaceholder);


</script>

<!-- Bootstrap JS (Optional) -->

</body>
</html>



<?php
// Helper Function: Time Elapsed String
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $string = [
        'y' => 'year',
        'm' => 'month',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second'
    ];

    foreach ($string as $key => &$value) {
        if ($diff->$key) {
            $value = $diff->$key . ' ' . $value . ($diff->$key > 1 ? 's' : '');
        } else {
            unset($string[$key]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>

<?php include '../includes/footer.php'; ?>
