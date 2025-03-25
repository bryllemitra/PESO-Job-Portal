<?php
include '../includes/header.php'; // This already includes session_start()
include '../includes/config.php'; // Include DB connection

// Restrict access to admins and employers only
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin')) {
    echo "
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <div class='modal fade show' id='errorModal' tabindex='-1' aria-labelledby='errorModalLabel' aria-hidden='false' style='display: block; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;'>
        <div class='modal-dialog modal-dialog-centered'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title' id='errorModalLabel'>Access Denied</h5>
                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <div class='modal-body'>
                    You do not have permission to access this page.
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-primary' id='redirectBtn'>OK</button>
                </div>
            </div>
        </div>
    </div>
    <div class='modal-backdrop fade show' style='position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9998;'></div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>
    <script type='text/javascript'>
        document.getElementById('redirectBtn').addEventListener('click', function() {
            window.location.href = '../index.php'; // Redirect to the home page when the button is clicked
        });
    </script>
    ";
    exit();
}

// Now safe to use $_SESSION['user_id']
$user_role = $_SESSION['role'] ?? 'guest'; 

// Determine user profile based on role
if ($user_role === 'admin') {
    $user_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];
} else {
    $user_id = $_SESSION['user_id']; 
}

// Validate $user_id
if (!$user_id || !is_numeric($user_id)) {
    die("Invalid user ID.");
}

// Fetch user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();





// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic'])) {
    if ($_FILES["profile_pic"]["size"] == 0) {
        // No file selected, just refresh the page
        echo "<script>window.location.href = 'profile.php?id=$user_id';</script>";
        exit();
    } else {
        $target_dir = "../uploads/profile_admin/"; // Ensure this directory exists and is writable
        $fileType = strtolower(pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION));

        // Fetch the current profile picture and username from the database
        $query = "SELECT uploaded_file, username FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user data is fetched correctly
        if ($user = $result->fetch_assoc()) {
            // Sanitize the username and ensure it's safe for use in a file name
            $sanitized_username = preg_replace("/[^a-zA-Z0-9-_]/", "_", $user['username']); // Replace unsafe characters with underscore
            $new_file_name = $sanitized_username . "." . $fileType; // Use username as the filename
            $target_file = $target_dir . $new_file_name;

            // Check if the uploaded file is a valid image
            if (getimagesize($_FILES["profile_pic"]["tmp_name"])) {
                // Check if there is already an existing profile picture
                if (!empty($user['uploaded_file']) && file_exists($user['uploaded_file'])) {
                    // Delete the old profile picture
                    unlink($user['uploaded_file']);
                }

                // Move the new file to the target directory
                if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                    // Update the database with the new file path
                    $update_query = "UPDATE users SET uploaded_file = ? WHERE id = ?";
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("si", $target_file, $user_id);

                    if ($stmt->execute()) {
                        // Successfully uploaded, refresh the page immediately
                        echo "<script>window.location.href = 'profile.php?id=$user_id';</script>";
                        exit();
                    } else {
                        // If there is an error updating the database, just refresh the page
                        echo "<script>window.location.href = 'profile.php?id=$user_id';</script>";
                        exit();
                    }
                } else {
                    // Error uploading the file, just refresh the page
                    echo "<script>window.location.href = 'profile.php?id=$user_id';</script>";
                    exit();
                }
            } else {
                // Invalid image, refresh the page
                echo "<script>window.location.href = 'profile.php?id=$user_id';</script>";
                exit();
            }
        } else {
            // If no user is found, log and return an error
            echo "<div class='alert alert-danger'>No user found with ID: $user_id</div>";
            exit();
        }
    }
}

// Handle profile picture removal when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_profile_pic'])) {
    // Check if a profile picture exists
    if (!empty($user['uploaded_file'])) {
        // Path to the file to be deleted from the server
        $filePath = $user['uploaded_file'];

        // Delete the file from the server
        if (file_exists($filePath)) {
            unlink($filePath); // Delete the file from the server
        }

        // Clear the uploaded_file column in the database (remove profile picture reference)
        $update_query = "UPDATE users SET uploaded_file = NULL WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            // Refresh the page immediately after successful removal
            echo "<script>window.location.href = 'profile.php?id=$user_id';</script>";
            exit();
        } else {
            // If there was an error removing from the database, you could log it or handle it, but we aren't showing an alert
            echo "<script>window.location.href = 'profile.php?id=$user_id';</script>";
            exit();
        }
    } else {
        // If there's no profile picture, just refresh the page without an alert
        echo "<script>window.location.href = 'profile.php?id=$user_id';</script>";
        exit();
    }
}




// Handle caption (bio) update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['caption'])) {
    $caption = trim($_POST['caption']);
    $update_caption_query = "UPDATE users SET caption = ? WHERE id = ?";
    $stmt = $conn->prepare($update_caption_query);
    $stmt->bind_param("si", $caption, $user_id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Caption updated successfully.</div>";
        // Using JavaScript to reload the page instead of header()
        echo "<script>window.location.href = 'profile.php?id=$user_id';</script>";
        exit(); // Make sure to stop further execution
    } else {
        echo "<div class='alert alert-danger'>Failed to update caption.</div>";
    }
}


// Handle profile updates for employers/admins
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Sanitize inputs for both user and employer-specific data
    $work_experience = trim($_POST['work_experience']);
    $skills = trim($_POST['skills']);
    $linkedin_profile = trim($_POST['linkedin_profile']);
    $portfolio_url = trim($_POST['portfolio_url']);

    // Employer-specific fields
    $company_name = trim($_POST['company_name']);
    $company_description = trim($_POST['company_description']);
    $company_website = trim($_POST['company_website']);
    $location = trim($_POST['location']);

    // Update query for the user's basic information
    $update_user_query = "UPDATE users 
                          SET work_experience = ?, 
                              skills = ?, 
                              linkedin_profile = ?, 
                              portfolio_url = ? 
                          WHERE id = ?";

    // Prepare and bind the statement for the users table
    $stmt_user = $conn->prepare($update_user_query);
    $stmt_user->bind_param("ssssi", $work_experience, $skills, $linkedin_profile, $portfolio_url, $user_id);

    // Update query for the employer-specific information
    $update_employer_query = "UPDATE employers 
                              SET company_name = ?, 
                                  company_description = ?, 
                                  company_website = ?, 
                                  location = ? 
                              WHERE user_id = ?";

    // Prepare and bind the statement for the employers table
    $stmt_employer = $conn->prepare($update_employer_query);
    $stmt_employer->bind_param("ssssi", $company_name, $company_description, $company_website, $location, $user_id);

    // Execute both queries
    $stmt_user_result = $stmt_user->execute();
    $stmt_employer_result = $stmt_employer->execute();

    // Check if both updates were successful
    if ($stmt_user_result && $stmt_employer_result) {
        echo "<div class='alert alert-success'>Profile updated successfully.</div>";
        header("Location: profile.php?id=$user_id");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Failed to update profile.</div>";
    }
}

// Fetch employer data (if user is an employer)
$employer_query = "SELECT * FROM employers WHERE user_id = ?";
$employer_stmt = $conn->prepare($employer_query);
$employer_stmt->bind_param("i", $user_id);
$employer_stmt->execute();
$employer_result = $employer_stmt->get_result();
$employer = $employer_result->fetch_assoc();

// Handle resume upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['resume'])) {
    if ($_FILES["resume"]["size"] == 0) {
        echo "<div class='alert alert-danger'>Please select a file to upload.</div>";
    } else {
        $target_dir = "../uploads/resumes/"; // Ensure this directory exists and is writable
        $target_file = $target_dir . basename($_FILES["resume"]["name"]);
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        // Allowed file types
        $allowed_types = ['pdf', 'doc', 'docx'];
        // Check if the file type is allowed
        if (!in_array($fileType, $allowed_types)) {
            echo "<div class='alert alert-danger'>Only PDF, DOC, and DOCX files are allowed.</div>";
        } elseif ($_FILES["resume"]["size"] > 5 * 1024 * 1024) { // Limit file size to 5MB
            echo "<div class='alert alert-danger'>File size must not exceed 5MB.</div>";
        } else {
            // If a previous resume exists, delete it
            if (!empty($user['resume_file']) && file_exists($user['resume_file'])) {
                unlink($user['resume_file']);
            }
            // Move the uploaded file to the target directory
            if (move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {
                // Save the file path in the database
                $update_query = "UPDATE users SET resume_file = ? WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("si", $target_file, $user_id);
                if ($stmt->execute()) {
                    echo "<div class='alert alert-success'>Resume uploaded successfully.</div>";
                    echo "<script>window.location.href = 'profile.php?id=$user_id';</script>";
                    exit();
                } else {
                    echo "<div class='alert alert-danger'>Error updating resume file in the database.</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Sorry, there was an error uploading your file.</div>";
            }
        }
    }
}

// Handle resume removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_resume'])) {
    // Check if a resume exists
    if (!empty($user['resume_file'])) {
        // Clear the resume_file column in the database (do not delete the actual file)
        $update_query = "UPDATE users SET resume_file = NULL WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Resume removed from profile successfully.</div>";
            echo "<script>window.location.href = 'profile.php?id=$user_id';</script>";
            exit();
        } else {
            echo "<div class='alert alert-danger'>Error removing resume from the database.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>No resume found to remove.</div>";
    }
}



// Fetch applied jobs for the user with status
$query_jobs = "
    SELECT jobs.title, categories.name AS category, jobs.location, jobs.id AS job_id, applications.status 
    FROM applications 
    JOIN jobs ON applications.job_id = jobs.id 
    JOIN job_categories ON jobs.id = job_categories.job_id 
    JOIN categories ON job_categories.category_id = categories.id
    WHERE applications.user_id = ?
";
$stmt = $conn->prepare($query_jobs);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_jobs = $stmt->get_result();

// Total Users Count (by Gender)
$total_users_query = "SELECT gender, COUNT(*) as total FROM users GROUP BY gender";
$total_users_result = mysqli_query($conn, $total_users_query);
$user_gender_data = [];
while ($row = mysqli_fetch_assoc($total_users_result)) {
    $user_gender_data[$row['gender']] = $row['total'];
}

// Total Jobs Count (by Status)
$total_jobs_query = "SELECT status, COUNT(*) as total FROM jobs GROUP BY status";
$total_jobs_result = mysqli_query($conn, $total_jobs_query);
$job_status_data = [];
while ($row = mysqli_fetch_assoc($total_jobs_result)) {
    $job_status_data[$row['status']] = $row['total'];
}

// Total Applicants Count (by Status)
$total_applicants_query = "SELECT status, COUNT(*) as total FROM applications GROUP BY status";
$total_applicants_result = mysqli_query($conn, $total_applicants_query);
$applicant_status_data = [];
while ($row = mysqli_fetch_assoc($total_applicants_result)) {
    $applicant_status_data[$row['status']] = $row['total'];
}

// Total Jobs Posted (With and Without Applicants)
$total_jobs_posted_query = "SELECT jobs.id, COUNT(applications.id) as applicants_count 
                            FROM jobs 
                            LEFT JOIN applications ON jobs.id = applications.job_id 
                            GROUP BY jobs.id";
$total_jobs_posted_result = mysqli_query($conn, $total_jobs_posted_query);

$jobs_with_applicants = 0;
$jobs_without_applicants = 0;

while ($row = mysqli_fetch_assoc($total_jobs_posted_result)) {
    if ($row['applicants_count'] > 0) {
        $jobs_with_applicants++;
    } else {
        $jobs_without_applicants++;
    }
}

// Store the total counts for output
$total_users = array_sum($user_gender_data);
$total_jobs = array_sum($job_status_data);
$total_applicants = array_sum($applicant_status_data);


// Fetch user data
$user_id = $_SESSION['user_id'];  // Assuming session contains the user id
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();


// Handle form submission to update name
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['first_name']) && isset($_POST['last_name'])) {
    $new_first_name = $_POST['first_name'];
    $new_last_name = $_POST['last_name'];

    // Update first and last name in the database
    $update_query = "UPDATE users SET first_name = ?, last_name = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param('ssi', $new_first_name, $new_last_name, $user_id);
    $update_stmt->execute();

    // Refresh the user data after update
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
}

// Fetch applicants per week for the current month
$current_year = date('Y');
$current_month = date('m');

// Generate a list of all weeks in the current month
$weeks_in_month = [];
$first_day_of_month = date("$current_year-$current_month-01");
$last_day_of_month = date("Y-m-t", strtotime($first_day_of_month));

$current_week_start = $first_day_of_month;
while ($current_week_start <= $last_day_of_month) {
    $current_week_end = date('Y-m-d', strtotime('+6 days', strtotime($current_week_start)));
    $weeks_in_month[] = [
        'week_start' => $current_week_start,
        'week_end' => $current_week_end,
        'week_number' => date('W', strtotime($current_week_start)) // Use ISO week number
    ];
    $current_week_start = date('Y-m-d', strtotime('+1 week', strtotime($current_week_start)));
}

// Fetch applicants per week for all job posts
$applicants_per_week_query = "
    SELECT 
        YEARWEEK(a.applied_at) AS week,
        COUNT(DISTINCT a.id) AS total_applicants
    FROM applications a
    WHERE YEAR(a.applied_at) = $current_year
    AND MONTH(a.applied_at) = $current_month
    GROUP BY YEARWEEK(a.applied_at)
    ORDER BY week ASC";
$applicants_per_week_result = $conn->query($applicants_per_week_query);
$applicants_per_week_data = $applicants_per_week_result->fetch_all(MYSQLI_ASSOC);

// Map the fetched data to the weeks in the current month
$applicants_per_week = [];
foreach ($weeks_in_month as $week) {
    $week_number = $week['week_number'];
    $found = false;
    foreach ($applicants_per_week_data as $data) {
        if ($data['week'] == $current_year . $week_number) {
            $applicants_per_week[] = [
                'week' => $week_number,
                'total_applicants' => $data['total_applicants']
            ];
            $found = true;
            break;
        }
    }
    if (!$found) {
        $applicants_per_week[] = [
            'week' => $week_number,
            'total_applicants' => 0
        ];
    }
}

// Convert the data to JSON for JavaScript
$applicants_per_week_json = json_encode($applicants_per_week);


?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/JOB/assets/profile.css">
    <style>
        .dashboard-container {
        padding: 40px;
    }

/* Cards (for Statistics) */
.card {
    background: rgba(255, 255, 255, 0.8); /* Light semi-transparent background */
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 255, 136, 0.1); /* Light glowing shadow */
    transition: all 0.3s ease-in-out;
    backdrop-filter: blur(8px); /* Slight frosted glass effect */
}

.card:hover {
    transform: translateY(-10px); /* Hover effect for cards */
    box-shadow: 0 10px 30px rgba(0, 255, 136, 0.3); /* Enhanced glow effect */
}

    .card-body {
        padding: 30px;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 500;
    }

    .card-statistics {
        font-size: 2rem;
        font-weight: 600;
        color: #333;
    }

    .card-text {
        color: #777;
    }

    .row {
        margin-top: 20px;
    }

    .col-md-4 {
        margin-bottom: 20px;
    }

    .col-md-6 {
        margin-bottom: 20px;
    }

    h1 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #333;
    }

    .shadow-sm {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
    }

    .custom-card {
        border-radius: 12px;
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease-in-out;
    }

    .custom-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 16px 32px rgba(0, 0, 0, 0.2);
    }

    .card-statistics {
        font-size: 3rem;
        font-weight: bold;
        margin-top: 10px;
    }

    .card-title {
        font-size: 1.2rem;
        font-weight: 600;
    }

    .card-text {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.8);
    }

    /* Glassmorphism Backgrounds */
.glass-bg {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    backdrop-filter: blur(15px);
    padding: 30px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
}

/* Button Styling */
.btn-outline-custom {
    color:#333;
    text-decoration: none; /* Removes underline */
    background: transparent; /* Keeps it transparent */
    border:1.5px solid #333; /* Adds a black border */
    transition: all 0.3s ease-in-out;
}

.btn-outline-custom:hover {
    background: transparent;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Soft shadow effect */
    border-color: #4c6ef5;
    color: #4c6ef5;
}

</style>
</head>
<body>
<?php $isOwnProfile = ($user_id == $_SESSION['user_id']); // Check if it's the user's own profile ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 fade-in">
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs custom-tabs" id="profileTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">Profile</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="applications-tab" data-bs-toggle="tab" data-bs-target="#applications" type="button" role="tab" aria-controls="applications" aria-selected="false">Management</button>
    </li>
</ul>

                <!-- Tab Content -->
                <div class="tab-content" id="profileTabsContent">
                    <!-- Profile Tab -->
                    <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        <!-- Profile Card -->
                        <div class="profile-card mt-4 p-4 mb-4">
            <!-- Cover Photo -->
            <div class="cover-photo-container position-relative" style="height: 340px; overflow: hidden;">
                <img src="<?php echo $user['cover_photo'] ? $user['cover_photo'] : '/JOB/uploads/default/COVER.jpg'; ?>" alt="Cover Photo" class="cover-photo w-100 h-100 object-fit-cover" style="object-position: center;">
<!-- Edit Cover Photo Button -->
<?php if ($user_id == $_SESSION['user_id']): ?>
    <!-- Edit Cover Photo Button (Only visible to the profile owner) -->
    <button type="button" class="btn btn-light position-absolute top-0 end-0 m-3" data-bs-toggle="modal" data-bs-target="#coverPhotoModal">
        <i class="fas fa-camera"></i> Edit Cover
    </button>
<?php elseif ($user_role === 'admin'): ?>
    <!-- View Cover Photo Button (Only visible to admins) -->
    <button type="button" class="btn btn-outline-secondary position-absolute top-0 end-0 m-3" data-bs-toggle="modal" data-bs-target="#viewPhotoModal">
        <i class="fas fa-camera"></i> View Cover
    </button>
<?php endif; ?>
            </div>

            <!-- Profile Picture -->
            <div class="text-center position-relative" style="margin-top: -100px;">
            <div class="profile-picture mb-3" data-bs-toggle="modal" data-bs-target="#profilePictureModal">
    <img src="<?php echo $user['uploaded_file'] ? $user['uploaded_file'] : '../uploads/default/default_profile.png'; ?>" alt="Profile Picture" class="rounded-circle shadow-sm" style="width: 200px; height: 200px; object-fit: cover; border: 4px solid #fff;">
</div>

                                <!-- User Name (Clickable) -->
                                <h2>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#nameModal" class="text-decoration-none text-dark fw-semibold">
                                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                    </a>
                                </h2>
                                <!-- Caption -->
                                <p class="text-muted caption-text" id="bio-display">
                                    <?php echo !empty($user['caption']) ? htmlspecialchars($user['caption']) : 'No caption set'; ?>
                                </p>
                                <!-- Edit Bio Button -->
                                <?php if ($isOwnProfile): ?>
                                    <button id="edit-bio-button" class="btn btn-outline-custom btn-sm mb-3">Edit Bio</button>
                                <?php endif; ?>
                                <!-- Caption Update Form (Hidden Initially) -->
                                <form action="profile.php?id=<?php echo $user_id; ?>" method="POST" id="bio-form" style="display:none;" class="mb-3">
                                    <textarea name="caption" class="form-control rounded-pill my-3" placeholder="Enter your caption or saying..."><?php echo htmlspecialchars($user['caption']); ?></textarea>
                                    <button type="submit" class="btn btn-primary rounded-pill me-2">Save</button>
                                    <button type="button" id="cancel-bio-button" class="btn btn-light rounded-pill">Cancel</button>
                                </form>
                            </div>
                        </div>

<!-- Dashboard Container -->
<div class="dashboard-container mt-5">
    <h1 class="text-center mb-4" style="font-family: 'Roboto', sans-serif; color: #4a90e2;">Dashboard</h1>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm rounded">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fas fa-user-check me-2"></i> Active Users</h5>
                    <h2 style="color:#4a90e2;" class="card-statistics text-center">
                        <?= array_sum($user_gender_data) ?>
                    </h2>
                    <p class="card-text ">Users in the system</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm rounded">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fas fa-briefcase me-2 text-dark"></i> Total Jobs</h5>
                    <h2 style="color:#4a90e2;" class="card-statistics text-center">
                        <?= array_sum($job_status_data) ?>
                    </h2>
                    <p class="card-text">Jobs posted in the system</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm rounded">
                <div class="card-body text-center">
                    <h5 class="card-title"><i class="fas fa-users me-2"></i> Total Applicants</h5>
                    <h2 style="color:#4a90e2;" class="card-statistics text-center">
                        <?= array_sum($applicant_status_data) ?>
                    </h2>
                    <p class="card-text">Applicants who have applied</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphs Section -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-lg rounded custom-card">
                <div class="card-body">
                    <h5 class="card-title text-center" style="font-size: 20px; font-weight: bold; color: #333;">Total Users</h5>
                    <canvas id="userGenderChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-lg rounded custom-card">
                <div class="card-body">
                    <h5 class="card-title text-center" style="font-size: 20px; font-weight: bold; color: #333;">Total Job Postings</h5>
                    <canvas id="totalJobPostedChart"></canvas>
                </div>
            </div>
        </div>

    </div>

    <div class="row mb-4">
        <div class="col-md-6">
        <div class="card shadow-lg rounded custom-card">
                <div class="card-body">
                    <h5 class="card-title text-center" style="font-size: 20px; font-weight: bold; color: #333;">Jobs Status</h5>
                    <canvas id="jobStatusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-lg rounded custom-card">
                <div class="card-body">
                    <h5 class="card-title text-center" style="font-size: 20px; font-weight: bold; color: #333;">Applicant Status</h5>
                    <canvas id="applicantStatusChart"></canvas>
                </div>
            </div>
        </div>

    </div>

<!-- Line Chart for Total Applicants per Week -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-lg border-0 rounded-3 chart-card" style="height: 500px;">
            <div class="card-header bg-transparent text-white">
                <i class="fas fa-chart-line me-2"></i> Total Applicants per Week
            </div>
            <div class="card-body" style="height: 90%;">
                <!-- Dynamic Label -->
                <h6 id="dynamicLabel" class="text-center mb-3"></h6>
                <canvas id="totalApplicationsChart" style="height: 90%; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>
</div>




<!-- Modal for Fullscreen Resume Preview -->
<div id="resume-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resume Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="resume-modal-body">
                <!-- Resume content will be loaded here -->
            </div>
        </div>
    </div>
</div>
                

<!-- Cover Photo Modal -->
<div class="modal fade" id="coverPhotoModal" tabindex="-1" aria-labelledby="coverPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="coverPhotoModalLabel">Edit Cover Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="update_cover_photo.php" method="POST" enctype="multipart/form-data">
                    <!-- File Input -->
                    <div class="mb-3">
                        <label for="cover-photo-upload" class="form-label fw-semibold">Upload New Cover Photo</label>
                        <input type="file" class="form-control" id="cover-photo-upload" name="cover_photo" accept="image/*" required>
                    </div>
                    <!-- Remove Cover Photo Option -->
                    <?php if ($user['cover_photo']): ?>
                        <div class="mb-3 d-flex gap-2 justify-content-between align-items-center">
                            <!-- View Photo Button -->
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewPhotoModal">
                                View Photo
                            </button>
                            <div class="d-flex gap-2">
                                <?php if ($user_id == $_SESSION['user_id'] || $user_role === 'admin'): ?>
                                    <!-- Save Button (Visible to profile owner and admin) -->
                                    <button type="submit" class="btn btn-primary">Save</button>
                                <?php endif; ?>
                                <?php if ($user_id == $_SESSION['user_id']): ?>
                                    <!-- Remove Button (Only visible to profile owner) -->
                                    <button type="button" class="btn btn-light" onclick="removeCoverPhoto()">Remove</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="mb-3 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
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
                <img src="../uploads/<?= htmlspecialchars($user['cover_photo'] ?? '/JOB/uploads/default/COVER.jpg') ?>" alt="Cover Photo" id="fullSizedImage" class="img-fluid" style="max-height: 80vh;">
         </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal for Removing Cover Photo (Bootstrap 5 Example) -->
<div class="modal fade" id="removeCoverPhotoModal" tabindex="-1" aria-labelledby="removeCoverPhotoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="removeCoverPhotoModalLabel">Confirm Removal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to remove your cover photo? This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="confirmRemoveCoverPhotoBtn">Remove</button>
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        
      </div>
    </div>
  </div>
</div>




<!-- Applications Tab -->
<div class="tab-pane fade" id="applications" role="tabpanel" aria-labelledby="applications-tab">
    <div class="container mt-4">
        <h4 class="text-center"><?php echo isset($_SESSION['role']) && $_SESSION['role'] === 'admin' ? 'With Pending Applications' : 'Managing Jobs'; ?></h4><Br>
        <?php
        // Check if the current user is an admin or employer
        $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        $isEmployer = isset($_SESSION['role']) && $_SESSION['role'] === 'employer';

        if ($isAdmin): 
            // Fetch jobs with pending applications for admin
            $query_jobs = "
                SELECT 
                    jobs.id AS job_id, 
                    jobs.title, 
                    categories.name AS category, 
                    jobs.location, 
                    jobs.created_at AS posted_date, 
                    COUNT(applications.id) AS pending_applicants
                FROM jobs
                LEFT JOIN applications ON jobs.id = applications.job_id AND applications.status = 'pending'
                LEFT JOIN job_categories ON jobs.id = job_categories.job_id
                LEFT JOIN categories ON job_categories.category_id = categories.id
                WHERE jobs.status != 'rejected' 
                GROUP BY jobs.id
                HAVING COUNT(applications.id) > 0"; // Only jobs with pending applications
        elseif ($isEmployer):
            // Fetch jobs posted by the employer, whether they have applicants or not
            $query_jobs = "
                SELECT 
                    jobs.id AS job_id, 
                    jobs.title, 
                    categories.name AS category, 
                    jobs.location, 
                    jobs.created_at AS posted_date, 
                    COUNT(applications.id) AS pending_applicants
                FROM jobs
                LEFT JOIN applications ON jobs.id = applications.job_id AND applications.status = 'pending'
                LEFT JOIN job_categories ON jobs.id = job_categories.job_id
                LEFT JOIN categories ON job_categories.category_id = categories.id
                WHERE jobs.employer_id = ?
                GROUP BY jobs.id"; // All jobs posted by this employer
        endif;

        // Execute query for admin or employer
        if ($isAdmin || $isEmployer): 
            $stmt = $conn->prepare($query_jobs);
            if ($isEmployer) {
                $stmt->bind_param("i", $_SESSION['user_id']);
            }
            $stmt->execute();
            $result_jobs = $stmt->get_result();

            // Display content based on conditions
            if ($result_jobs->num_rows > 0): ?>
                <div class="job-list">
                    <?php while ($job = $result_jobs->fetch_assoc()): ?>
                        <div class="job-card card mb-3 shadow-sm rounded">
                            <div class="card-body">
                                <!-- Job Header -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="text-primary"><?php echo htmlspecialchars($job['title']); ?></strong>
                                    <!-- Pending Applicants Count -->
                                    <span class="badge bg-warning text-dark">
                                        <?php echo $job['pending_applicants'] . ' Pending'; ?>
                                    </span>
                                </div>

                                <!-- Job Details -->
                                <div class="job-details mt-3">
                                    <p class="mb-1"><i class="fas fa-briefcase me-2"></i><?php echo htmlspecialchars($job['category']); ?></p>
                                    <p class="mb-1"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($job['location']); ?></p>
                                    <p class="mb-1"><i class="fas fa-calendar-alt me-2"></i>Posted on: <?php echo date('M d, Y', strtotime($job['posted_date'])); ?></p>
                                </div>

                                <!-- View Details Button -->
                                <div class="job-actions mt-3">
                                    <a href="/JOB/pages/job.php?id=<?php echo $job['job_id']; ?>" class="btn btn-primary btn-sm rounded-pill">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-muted"><?php echo $isAdmin ? "No pending applications for any job." : "You have not posted any jobs yet."; ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>






<!-- Edit Profile Button -->
<?php if ($isOwnProfile): ?>
    <div id="edit-profile-button" style="margin-bottom: 30px;" class="text-center fade-in">
        <a href="admin.php" class="btn btn-custom rounded-pill px-4">
            <i class="fas fa-tachometer-alt"></i> Admin Panel
        </a>
    </div>
<?php endif; ?>

                

<!-- Profile Picture Modal -->
<div class="modal fade" id="profilePictureModal" tabindex="-1" aria-labelledby="profilePictureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profilePictureModalLabel">Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Display the profile picture -->
                <div class="mb-3 text-center">
                    <?php
                    // Check if there's an uploaded profile picture, else use the default avatar
                    $default_avatar = '../uploads/default/default_profile.png';  // Path to the default avatar
                    if (!empty($user['uploaded_file'])) {
                        $default_avatar = $user['uploaded_file'];  // Use the uploaded profile picture if available
                    }
                    ?>
                    <img src="<?php echo $default_avatar; ?>" alt="Profile Picture" class="rounded-circle shadow-sm" style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #ddd;">
                </div>

                <!-- Conditional Buttons Based on User Role -->
                <?php if ($user_id == $_SESSION['user_id']): ?>
                    <!-- Upload Picture Form (Only visible to the profile owner) -->
                    <form action="profile.php?id=<?php echo $user_id; ?>" method="POST" enctype="multipart/form-data" class="mb-3">
                        <div class="mb-3">
                            <label for="profile_pic" class="form-label fw-bold">Upload New Profile Picture</label>
                            <input type="file" name="profile_pic" id="profile_pic" class="form-control rounded-pill" accept="image/*" required>
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill w-100"><i class="fas fa-upload"></i> Upload Picture</button>
                    </form>
                    <!-- Remove Picture Button (Only visible to the profile owner) -->
                    <?php if ($user['uploaded_file']): ?>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-light rounded-pill w-100" data-bs-toggle="modal" data-bs-target="#removeProfilePicModal">
                                <i class="fas fa-trash"></i> Remove Picture
                            </button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- View Profile Picture Button (Visible to everyone) -->
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill w-100" data-bs-toggle="modal" data-bs-target="#viewProfilePictureModal">
                        <i class="fas fa-eye"></i> View Profile Picture
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Profile Picture Removal Modal -->
<div class="modal fade" id="removeProfilePicModal" tabindex="-1" aria-labelledby="removeProfilePicModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeProfilePicModalLabel">Confirm Removal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to remove your profile picture? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <form action="profile.php?id=<?php echo $user_id; ?>" method="POST">
                    <input type="hidden" name="remove_profile_pic" value="1">
                    <button type="submit" class="btn btn-primary">Remove</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View Profile Picture Modal -->
<div class="modal fade" id="viewProfilePictureModal" tabindex="-1" aria-labelledby="viewProfilePictureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewProfilePictureModalLabel">Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <!-- Full-Sized Image -->
                <img src="<?php echo $default_avatar; ?>" alt="Profile Picture" id="fullSizedProfileImage" class="img-fluid" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>


<!-- Remove Resume Modal -->
<div class="modal fade" id="removeResumeModal" tabindex="-1" aria-labelledby="removeResumeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeResumeModalLabel">Confirm Remove Resume</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to remove your resume? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="confirmRemoveResume">Remove</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                
            </div>
        </div>
    </div>
</div>

<!-- Modal for Editing Name -->
<div class="modal fade" id="nameModal" tabindex="-1" aria-labelledby="nameModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nameModalLabel">Edit Name</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Name Edit Form -->
                <form action="profile.php?id=<?php echo $user_id; ?>" method="POST">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/mammoth/mammoth.browser.min.js"></script>
<script>

function removeCoverPhoto() {
    // Show the confirmation modal
    const confirmationModal = new bootstrap.Modal(document.getElementById('removeCoverPhotoModal'));
    confirmationModal.show();

    // REMOVE COVER PHOTO
    document.getElementById('confirmRemoveCoverPhotoBtn').addEventListener('click', function() {
        // Send AJAX request to remove the cover photo
        fetch('../admin/update_cover_photo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ user_id: <?php echo $user_id; ?> })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close both the confirmation modal and the cover photo modal
                confirmationModal.hide();

                // Optionally, you could close the "Cover Photo Modal" if it's open
                const coverPhotoModal = bootstrap.Modal.getInstance(document.getElementById('coverPhotoModal'));
                if (coverPhotoModal) {
                    coverPhotoModal.hide();
                }

                // Refresh the page to reflect changes
                location.reload();
            } else {
                alert(data.message); // Show error message
            }
        })
        .catch(error => console.error('Error:', error));
    });
}

// VIEW PROFILE

document.addEventListener('DOMContentLoaded', function () {
    const profilePicInput = document.getElementById('profile_pic');
    const fullSizedProfileImage = document.getElementById('fullSizedProfileImage');

    // Set the initial image source to the current profile picture
    fullSizedProfileImage.src = fullSizedProfileImage.src || '<?php echo $default_avatar; ?>';

    // Update the image preview when a new file is selected
    profilePicInput.addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            if (!file.type.startsWith('image/')) {
                alert('Please select a valid image file.');
                profilePicInput.value = ''; // Clear the input
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                fullSizedProfileImage.src = e.target.result; // Update the image source
            };
            reader.readAsDataURL(file);
        } else {
            // If no file is selected, revert to the current profile picture
            fullSizedProfileImage.src = '<?php echo $default_avatar; ?>';
        }
    });
});


// VIEW COVER PHOTO
document.addEventListener('DOMContentLoaded', function () {
    const coverPhotoInput = document.getElementById('cover-photo-upload');
    const fullSizedImage = document.getElementById('fullSizedImage');

    // Set the initial image source to the current cover photo
    fullSizedImage.src = fullSizedImage.src || '../uploads/<?= htmlspecialchars($user['cover_photo'] ?? "/JOB/uploads/default/COVER.jpg") ?>';

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
            fullSizedImage.src = '../uploads/<?= htmlspecialchars($user['cover_photo'] ?? "default_cover.jpg") ?>';
        }
    });
});




        // Add a data attribute to the body to track the active tab
        document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'default'; // Default to 'default' if no tab is specified
        document.body.setAttribute('data-tab', activeTab);
    });
    // Function to Open Resume in Modal
    function viewResume(fileUrl) {
        const modalBody = document.getElementById('resume-modal-body');
        modalBody.innerHTML = ''; // Clear previous content

        const fileExtension = fileUrl.split('.').pop().toLowerCase();

        if (fileExtension === 'pdf') {
            // Embed PDF in an iframe
            modalBody.innerHTML = `<iframe src="${fileUrl}" width="100%" height="100%" style="border:none;"></iframe>`;
        } else if (fileExtension === 'docx') {
            // Use Mammoth.js to render DOCX as HTML
            fetch(fileUrl)
                .then(response => response.arrayBuffer())
                .then(arrayBuffer => mammoth.convertToHtml({ arrayBuffer }))
                .then(result => {
                    modalBody.innerHTML = result.value;
                })
                .catch(error => {
                    modalBody.innerHTML = `<div class="alert alert-danger">Error loading DOCX file: ${error.message}</div>`;
                });
        } else {
            // Unsupported format
            modalBody.innerHTML = `<div class="alert alert-warning">Unsupported file format. Please download the file to view it.</div>`;
        }

        // Display extracted images
        fetch('get_extracted_images.php?user_id=<?= $user_id ?>')
            .then(response => response.json())
            .then(images => {
                if (images.length > 0) {
                    modalBody.innerHTML += '<h5>Extracted Images</h5><div class="row">';
                    images.forEach(image => {
                        modalBody.innerHTML += `<img src="${image}" class="col-md-6 img-thumbnail">`;
                    });
                    modalBody.innerHTML += '</div>';
                }
            });

        // Show the modal
        const resumeModal = new bootstrap.Modal(document.getElementById('resume-modal'), {});
        resumeModal.show();
    }

    // Toggle the bio text field and edit button visibility
    document.getElementById('edit-bio-button').addEventListener('click', function() {
        document.getElementById('bio-display').style.display = 'none';
        document.getElementById('bio-form').style.display = 'block';
        document.getElementById('edit-bio-button').style.display = 'none';
    });

    // Cancel the bio edit and revert to original state
    document.getElementById('cancel-bio-button').addEventListener('click', function() {
        document.getElementById('bio-display').style.display = 'block';
        document.getElementById('bio-form').style.display = 'none';
        document.getElementById('edit-bio-button').style.display = 'inline-block';
    });

    document.getElementById('remove-resume-button')?.addEventListener('click', function () {
    const userId = this.getAttribute('data-user-id');
    
    // Show the modal
    const removeResumeModal = new bootstrap.Modal(document.getElementById('removeResumeModal'));
    removeResumeModal.show();

    // Handle the confirmation button click inside the modal
    document.getElementById('confirmRemoveResume')?.addEventListener('click', function () {
        // Submit the form to remove the resume
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `profile.php?id=${userId}`;
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'remove_resume';
        input.value = '1';
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();

        // Close the modal after submitting the form
        removeResumeModal.hide();

        // Refresh the page after form submission
        setTimeout(function () {
            location.reload();
        }, 500); // Small delay to allow form submission to complete
    });




});


//CHARTS AND Graphs
  // Example chart setup
  var ctx1 = document.getElementById('userGenderChart').getContext('2d');
    var userGenderChart = new Chart(ctx1, {
        type: 'pie',
        data: {
            labels: ['Male', 'Female', 'Non-Binary', 'Other'],
            datasets: [{
                data: [
                    <?= isset($user_gender_data['Male']) ? $user_gender_data['Male'] : 0 ?>,
                    <?= isset($user_gender_data['Female']) ? $user_gender_data['Female'] : 0 ?>,
                    <?= isset($user_gender_data['Non-Binary']) ? $user_gender_data['Non-Binary'] : 0 ?>,
                    <?= isset($user_gender_data['Other']) ? $user_gender_data['Other'] : 0 ?>
                ],
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#ff6f61'],
            }]
        }
    });

    var ctx2 = document.getElementById('applicantStatusChart').getContext('2d');
    var applicantStatusChart = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: ['Pending', 'Accepted', 'Rejected'],
            datasets: [{
                label: 'Applicants Status',
                data: [
                    <?= isset($applicant_status_data['pending']) ? $applicant_status_data['pending'] : 0 ?>,
                    <?= isset($applicant_status_data['accepted']) ? $applicant_status_data['accepted'] : 0 ?>,
                    <?= isset($applicant_status_data['rejected']) ? $applicant_status_data['rejected'] : 0 ?>
                ],
                backgroundColor: ['#f6c23e', '#1cc88a', '#e74a3b'],
            }]
        }
    });

 // Bar chart for Job Status
 var ctx3 = document.getElementById('jobStatusChart').getContext('2d');
    var jobStatusChart = new Chart(ctx3, {
        type: 'bar', // Change this to 'bar' for a bar graph
        data: {
            labels: ['Pending', 'Approved', 'Rejected'], // Labels for the job statuses
            datasets: [{
                label: 'Job Status',
                data: [
                    <?= isset($job_status_data['pending']) ? $job_status_data['pending'] : 0 ?>, // Pending jobs count
                    <?= isset($job_status_data['approved']) ? $job_status_data['approved'] : 0 ?>, // Approved jobs count
                    <?= isset($job_status_data['rejected']) ? $job_status_data['rejected'] : 0 ?> // Rejected jobs count
                ],
                backgroundColor: [
                    '#f6c23e', // Color for Pending jobs
                    '#1cc88a', // Color for Approved jobs
                    '#e74a3b'  // Color for Rejected jobs
                ],
                borderColor: [
                    '#f6c23e', '#1cc88a', '#e74a3b' // Border colors for each bar
                ],
                borderWidth: 1 // Border width for the bars
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true // Ensures the Y-axis starts at 0
                }
            }
        }
    });

    var ctx4 = document.getElementById('totalJobPostedChart').getContext('2d');
    var totalJobPostedChart = new Chart(ctx4, {
        type: 'doughnut',
        data: {
            labels: ['With Applicants', 'Without Applicants'],
            datasets: [{
                data: [<?= $jobs_with_applicants ?>, <?= $jobs_without_applicants ?>],
                backgroundColor: ['#ff6f61', '#f6c23e'],
            }]
        }
    });

    // Existing chart configurations (for Total Users, Jobs, etc.)

// Parse the JSON data from PHP
const applicantsPerWeekData = <?php echo $applicants_per_week_json; ?>;

// Extract weeks and applicants data
const weeks = applicantsPerWeekData.map(item => `Week ${item.week}`);
const applicants = applicantsPerWeekData.map(item => item.total_applicants);

// Get the current date
const currentDate = new Date();
const currentMonth = currentDate.toLocaleString('default', { month: 'long' }); // Full month name (e.g., "March")
const currentDay = currentDate.getDate(); // Day of the month (e.g., 27)
const currentWeekOfYear = getWeekOfYear(currentDate); // Week of the year (e.g., 13)
const currentYear = currentDate.getFullYear(); // Current year (e.g., 2023)

// Function to get the week of the year
function getWeekOfYear(date) {
    const startOfYear = new Date(date.getFullYear(), 0, 1);
    const pastDaysOfYear = (date - startOfYear) / 86400000;
    return Math.ceil((pastDaysOfYear + startOfYear.getDay() + 1) / 7);
}

// Set the dynamic label in the HTML
const dynamicLabel = document.getElementById('dynamicLabel');
dynamicLabel.textContent = `Applicants for ${currentMonth} ${currentDay} (Week ${currentWeekOfYear}, ${currentYear})`;

// Render the chart
const ctx = document.getElementById('totalApplicationsChart').getContext('2d');
const myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: weeks,
        datasets: [{
            label: 'Total Applicants', // Generic label for the dataset
            data: applicants,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true, // Make the chart responsive
        maintainAspectRatio: false, // Allow the aspect ratio to change with container
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    title: (context) => `Week ${context[0].label.replace('Week ', '')}`, // Custom tooltip title
                    label: (context) => `Applicants: ${context.raw}` // Custom tooltip label
                }
            }
        }
    }
});
</script>


</body>
</html>




