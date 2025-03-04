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
    AND j.status != 'pending'  -- Exclude pending jobs
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
    AND j.status != 'pending'  -- Exclude pending jobs
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
    AND j.status != 'pending'  -- Exclude pending jobs
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
    // Query to fetch only the employer's own jobs
    $query_my_jobs = "SELECT * FROM jobs WHERE employer_id = ?";
    $stmt = $conn->prepare($query_my_jobs);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
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
        $target_dir = "../uploads/covers/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Ensure directory exists
        }

        $file_name = uniqid() . '_' . basename($_FILES["cover_photo"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image type
        if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES["cover_photo"]["tmp_name"], $target_file)) {
                // Store in database
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
    $delete_query = "DELETE FROM browse";
    if ($conn->query($delete_query)) {
        $_SESSION['success'] = "Cover photo removed successfully.";
        echo '<script>window.location.href="browse.php";</script>';
        exit();
    } else {
        $_SESSION['error'] = "Failed to delete cover photo.";
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
    border-radius: 30px;
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
            <i class="fas fa-camera"></i> <?php echo $user_role === 'admin' ? 'Edit Cover' : 'View Cover'; ?>
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






        <!-- Search and Filter Form (Original Layout & Styling Preserved) -->
        <form action="" method="get" class="row gx-2 gy-2 justify-content-center">
        <!-- Hidden Input for Active Tab -->
        <input type="hidden" name="tab" value="<?= htmlspecialchars($active_tab) ?>">
            <!-- Search Bar -->
            <div class="col-lg-2 col-md-3 col-6">
                <input type="text" name="search" class="form-control form-control-m rounded-pill" placeholder="Search..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            </div>
            <!-- Category Dropdown -->
            <div class="col-lg-2 col-md-3 col-6">
                <select name="category" class="form-select form-select-m rounded-pill">
                    <option value="">Category</option>
                    <?php while ($category = $category_result->fetch_assoc()): ?>
                        <option value="<?= $category['id'] ?>" <?= isset($_GET['category']) && $_GET['category'] == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <!-- Position Dropdown -->
            <div class="col-lg-2 col-md-3 col-6">
                <select name="position" class="form-select form-select-m rounded-pill">
                    <option value="">Position</option>
                    <?php while ($position = $position_result->fetch_assoc()): ?>
                        <option value="<?= $position['id'] ?>" <?= isset($_GET['position']) && $_GET['position'] == $position['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($position['position_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <!-- Location Dropdown -->
            <div class="col-lg-2 col-md-3 col-6">
                <select name="location" class="form-select form-select-m rounded-pill">
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

            <!-- Submit Button -->
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-customs btn-lg w-100 rounded-pill">Filter</button>
            </div>
        </form>

<!-- Admin and Employer Buttons: Post a New Job -->
<?php if ($user_role === 'admin' || $user_role === 'employer'): ?>
    <div class="text-center mt-4">
        <a href="../admin/post_job.php" class="btn btn-outline-custom btn-lg rounded-pill">Post a New Job</a>
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
                <a class="hero-nav-link <?= $active_tab === 'saved' ? 'active' : '' ?>" href="?tab=saved">Saved Jobs</a>
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


<!-- Job Listings -->
<div class="album py-5 bg-light">
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
                                    <div class="save-flag" data-job-id="<?= $row['id'] ?>">
                                        <i class="fas fa-flag"></i>
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

    <!-- New Applicant Indicator for Admin -->
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <?php
        // Query to count new applicants (pending applications) for this job
        $new_applicants_query = "
            SELECT COUNT(*) AS count 
            FROM applications 
            WHERE job_id = {$row['id']} AND status = 'pending'
        ";
        $new_applicants_result = $conn->query($new_applicants_query);
        $new_applicants_count = $new_applicants_result->fetch_assoc()['count'];
        if ($new_applicants_count > 0): ?>
            <span class="badge bg-primary">New Applicant(s): <?= $new_applicants_count ?></span>
        <?php endif; ?>
    <?php endif; ?>

       <!-- New Applicant Indicator for Employer -->
<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'employer'): ?>
    <?php
    // Query to count new applicants (pending applications) for this employer's job
    $new_applicants_query = "
        SELECT COUNT(*) AS count 
        FROM applications 
        WHERE job_id = {$row['id']} 
        AND status = 'pending' 
        AND job_id IN (SELECT id FROM jobs WHERE employer_id = ?)
    ";
    $new_applicants_stmt = $conn->prepare($new_applicants_query);
    $new_applicants_stmt->bind_param("i", $_SESSION['user_id']); // Ensure the query is filtered by employer's job
    $new_applicants_stmt->execute();
    $new_applicants_result = $new_applicants_stmt->get_result();
    $new_applicants_count = $new_applicants_result->fetch_assoc()['count'];

    if ($new_applicants_count > 0): ?>
        <span class="badge bg-primary">
            New Applicant(s): <?= $new_applicants_count ?>
        </span>
    <?php endif; ?>
<?php endif; ?>

    <!-- Action Buttons -->
    <div class="mt-auto">
        <a href="job.php?id=<?= $row['id'] ?>&mark_as_read=true" class="btn btn-view-job">View Job</a>
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
                    <input type="file" class="form-control" name="cover_photo" id="coverPhotoInput" required>
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
