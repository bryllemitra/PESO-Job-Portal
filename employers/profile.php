<?php
include '../includes/header.php'; // This already includes session_start()
include '../includes/config.php'; // Include DB connection

// Restrict access: Show modal and redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    echo "
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <div class='modal fade show' id='errorModal' tabindex='-1' aria-labelledby='errorModalLabel' aria-hidden='false' style='display: block; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;'>
        <div class='modal-dialog modal-dialog-centered'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title' id='errorModalLabel'>Access Denied</h5>
                </div>
                <div class='modal-body'>
                    You do not have permission to access this page.
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-primary' onclick=\"window.location.href='/JOB/pages/index.php'\">OK</button>
                </div>
            </div>
        </div>
    </div>
    <div class='modal-backdrop fade show' style='position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9998;'></div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>
    ";
    exit();
}

// Now safe to use $_SESSION['user_id']
$user_role = $_SESSION['role'] ?? 'guest'; 

// Logic for Admin role: Redirect if no 'id' is provided
if ($user_role === 'admin') {
    if (!isset($_GET['id'])) {
        // Redirect admin to their own profile page
        echo "<script>window.location.href = '/JOB/admin/profile.php';</script>";
        exit();
    } else {
        // If 'id' is provided, show the admin the profile of the specified user
        $user_id = (int)$_GET['id'];
    }
} 

// Logic for Employer role: Show their own profile if no 'id' is provided
elseif ($user_role === 'employer') {
    if (!isset($_GET['id'])) {
        // Employer is allowed to see their own profile without an 'id'
        $user_id = $_SESSION['user_id']; 
    } else {
        // Employer cannot view other employers' profiles
        $requested_id = (int)$_GET['id'];
        if ($requested_id !== $_SESSION['user_id']) {
            echo "<script>alert('Access Denied: You can only view your own profile.'); window.location.href = '/JOB/employers/profile.php';</script>";
            exit();
        } else {
            $user_id = $_SESSION['user_id']; // View their own profile if 'id' matches
        }
    }
}

// Logic for User role: Redirect them to their own profile
elseif ($user_role === 'user') {
    echo "<script>window.location.href = '/JOB/pages/profile.php';</script>";
    exit();
} 

// Validate $user_id (make sure it's numeric)
if (!$user_id || !is_numeric($user_id)) {
    die("Invalid user ID.");
}

// Fetch user data from the database
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the user was found
if ($result->num_rows === 0) {
    die("User not found.");
}

// Fetch user details
$user = $result->fetch_assoc();





// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic'])) {
    if ($_FILES["profile_pic"]["size"] == 0) {
        // No file selected, just refresh the page
        echo "<script>window.location.href = 'profile.php?id=$user_id';</script>";
        exit();
    } else {
        $target_dir = "../uploads/profile_employer/"; // Ensure this directory exists and is writable
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
        $target_dir = "../uploads/company_docu/"; // Ensure this directory exists and is writable
        $fileType = strtolower(pathinfo($_FILES["resume"]["name"], PATHINFO_EXTENSION));

        // Allowed file types
        $allowed_types = ['pdf'];

        // Check if the file type is allowed
        if (!in_array($fileType, $allowed_types)) {
            echo "<div class='alert alert-danger'>Only PDF files are allowed.</div>";
        } elseif ($_FILES["resume"]["size"] > 5 * 1024 * 1024) { // Limit file size to 5MB
            echo "<div class='alert alert-danger'>File size must not exceed 5MB.</div>";
        } else {
            // Fetch the username from the database
            $query = "SELECT username FROM users WHERE id = ?";
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

                // If a previous resume exists, delete it
                if (!empty($user['resume_file']) && file_exists($user['resume_file'])) {
                    unlink($user['resume_file']);
                }

                // Move the uploaded file to the target directory
                if (move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {
                    // Save the new file path in the database
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
            } else {
                echo "<div class='alert alert-danger'>User not found.</div>";
            }
        }
    }
}

// Handle resume removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_resume'])) {
    // Check if a resume exists
    if (!empty($user['resume_file'])) {
        // Path to the resume file to be deleted from the server
        $resumeFilePath = $user['resume_file'];

        // Delete the file from the server
        if (file_exists($resumeFilePath)) {
            unlink($resumeFilePath);  // Deletes the file from the server
        }

        // Clear the resume_file column in the database (removes the reference)
        $update_query = "UPDATE users SET resume_file = NULL WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            // Refresh the page immediately after the resume is deleted
            echo "<script>window.location.href = 'profile.php?id=$user_id';</script>";
            exit();
        } else {
            // Error in database update, refresh the page anyway
            echo "<script>window.location.href = 'profile.php?id=$user_id';</script>";
            exit();
        }
    } else {
        // If no resume file exists, just refresh the page without an alert
        echo "<script>window.location.href = 'profile.php?id=$user_id';</script>";
        exit();
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


// Check if there's a session message
if (isset($_SESSION['message'])) {
    // Retrieve message details
    $message = $_SESSION['message'];
    $messageType = $message['type'];
    $messageText = $message['text'];

    // Unset the session message so it doesn't persist across page loads
    unset($_SESSION['message']);
}



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

    <!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.min.css" rel="stylesheet">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.min.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/JOB/assets/profile.css">
    <style>




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

.striped-border {
    border: 4px dashed #ddd;  /* Creates a dashed border */
    background-color: #f9f9f9; /* Optional: Light background color for no content state */
    padding: 20px; /* Padding to ensure the content doesn’t touch the border */
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
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="false">Documents</button>
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

    <!-- User Name Display (Clickable) -->
    <?php if ($_SESSION['user_id'] == $user['id']): ?>
        <h2>
            <a href="#" data-bs-toggle="modal" data-bs-target="#editUserNameModal" style="color: black; text-decoration: none;">
                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name'] . ' ' . $user['ext_name']); ?>
            </a>
        </h2>
    <?php else: ?>
        <!-- Display the name normally for users who are not the owner -->
        <h2>
            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name'] . ' ' . $user['ext_name']); ?>
        </h2>
    <?php endif; ?>
                                    <!-- Caption -->
                                    <p class="text-muted caption-text" id="bio-display">
                                        <?php echo !empty($user['caption']) ? htmlspecialchars($user['caption']) : 'No caption set'; ?>
                                    </p>
                                    <!-- Edit Bio Button -->
                                    <?php if ($isOwnProfile): ?>
                                        <button id="edit-bio-button" class="btn btn-outline-custom btn-sm mb-3"><i class="fas fa-edit"></i>Edit Bio</button>
                                    <?php endif; ?>
                                    <!-- Caption Update Form (Hidden Initially) -->
                                    <form action="profile.php?id=<?php echo $user_id; ?>" method="POST" id="bio-form" style="display:none;" class="mb-3">
                                        <textarea name="caption" class="form-control rounded-pill my-3" placeholder="Enter your caption or saying..."><?php echo htmlspecialchars($user['caption']); ?></textarea>
                                        <button type="submit" class="btn btn-primary rounded-pill me-2">Save</button>
                                        <button type="button" id="cancel-bio-button" class="btn btn-light rounded-pill">Cancel</button>
                                    </form>
                                </div>
                            </div>

<!-- Personal Information -->
<div class="profile-card p-4 mb-4 fade-in" style="border-radius: 15px;">
    <!-- Section Header with Toggle Icon on the Right -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="section-title resume-section text-primary" style="font-family: 'Roboto', sans-serif; font-weight: bold; color: #333;">Personal Information</h3>
        <button id="toggle-personal-info-section" class="btn btn-link text-secondary p-0" style="font-size: 1.2rem; background: none; border: none;">
            <i class="fas fa-chevron-up"></i>
        </button>
    </div>
    <div id="personal-info-section" style="background-color: #fff; border-radius: 12px; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08); padding: 20px; transition: transform 0.2s ease, box-shadow 0.2s ease;">
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-6">
                <p class="mb-3"><strong><i class="fas fa-envelope me-2" style="color: #17A2B8;"></i>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p class="mb-3"><strong><i class="fas fa-venus-mars me-2" style="color: #FFC107;"></i>Gender:</strong> <?php echo htmlspecialchars($user['gender']); ?></p>
                <p class="mb-3">
                    <strong><i class="fas fa-birthday-cake me-2" style="color: #28A745;"></i>Birth Date:</strong> 
                    <?php 
                    $birthDate = new DateTime($user['birth_date']);
                    echo htmlspecialchars($birthDate->format('F j, Y')); 
                    ?>
                </p>
                <p class="mb-3"><strong><i class="fas fa-hourglass-half me-2" style="color: #DC3545;"></i>Age:</strong> <?php echo htmlspecialchars($user['age']); ?></p>
                <p class="mb-3"><strong><i class="fas fa-ruler me-2" style="color: #6F42C1;"></i>Height:</strong> <?php echo round((float) htmlspecialchars($user['height'])); ?> cm</p>
                <p class="mb-3"><strong><i class="fas fa-weight me-2" style="color: #FD7E14;"></i>Weight:</strong> <?php echo round((float) htmlspecialchars($user['weight'])); ?> kg</p>


            </div>

            <!-- Right Column -->
            <div class="col-md-6">
                <p class="mb-3"><strong><i class="fas fa-ring me-2" style="color: #6F42C1;"></i>Civil Status:</strong> <?php echo htmlspecialchars($user['civil_status']); ?></p>
                <p class="mb-3"><strong><i class="fas fa-phone me-2" style="color: #FD7E14;"></i>Phone Number:</strong> <?php echo htmlspecialchars($user['phone_number']); ?></p>
                <p class="mb-3"><strong><i class="fas fa-map-marker-alt me-2" style="color: #6610F2;"></i>Address:</strong>
                    <?php
                    // Query to fetch the barangay name based on the barangay ID
                    $barangay_query = "SELECT name FROM barangay WHERE id = ?";
                    if ($stmt = $conn->prepare($barangay_query)) {
                        $stmt->bind_param("i", $user['barangay']);
                        $stmt->execute();
                        $stmt->bind_result($barangay_name);
                        $stmt->fetch();
                        $stmt->close();

                        // Display the address with the barangay name
                        echo htmlspecialchars($user['street_address']) . ', ' . htmlspecialchars($barangay_name) . ', ' . htmlspecialchars($user['city']);
                    }
                    ?>
                </p>
                <p class="mb-3"><strong><i class="fas fa-map-pin me-2" style="color: #6C757D;"></i>Zip Code:</strong> <?php echo htmlspecialchars($user['zip_code']); ?></p>
                <p class="mb-3"><strong><i class="fas fa-cross me-2" style="color: #007BFF;"></i>Religion:</strong> <?php echo htmlspecialchars($user['religion']); ?></p>
                <p class="mb-3"><strong><i class="fas fa-flag me-2" style="color: #17A2B8;"></i>Nationality:</strong> <?php echo htmlspecialchars($user['nationality']); ?></p>
            </div>
        </div>

        <!-- Edit Button -->
        <?php if ($_SESSION['user_id'] == $user['id']): ?>
            <div class="mt-4">
                <button class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#editPersonalInfoModal">
                    <i class="fas fa-edit me-2"></i> Edit Info
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>


                            <!-- Employer Information (Always visible, even if no data exists) -->

<!-- Company Name -->
<div class="profile-card p-4 mb-4 fade-in position-relative">
    <h3 class="section-title resume-section text-primary">Company Name</h3>
    <p class="no-data <?php echo empty($employer['company_name']) ? 'striped-border' : ''; ?>">
        <?php 
        // Ensure $employer is valid before checking its content
        if (isset($employer) && is_array($employer)) {
            echo isset($employer['company_name']) && !is_null($employer['company_name']) && !empty($employer['company_name']) 
                ? htmlspecialchars($employer['company_name']) 
                : 'No company name added yet.';
        } else {
            echo 'No company name added yet.';
        }
        ?>
    </p>
    <!-- Edit Button (always visible if it's the logged-in user) -->
    <?php if ($_SESSION['user_id'] == $user['id']): ?>
        <button class="btn btn-download-resume btn-sm position-absolute top-0 end-0 m-2" data-bs-toggle="modal" data-bs-target="#editCompanyNameModal">
            <i class="fas fa-edit"></i>
        </button>
    <?php endif; ?>
</div>

<!-- Company Description -->
<div class="profile-card p-4 mb-4 fade-in position-relative">
    <h3 class="section-title resume-section text-primary">Company Description</h3>
    <p class="no-data <?php echo empty($employer['company_description']) ? 'striped-border' : ''; ?>">
        <?php 
        if (isset($employer) && is_array($employer)) {
            echo isset($employer['company_description']) && !is_null($employer['company_description']) && !empty($employer['company_description']) 
                ? htmlspecialchars($employer['company_description']) 
                : 'No company description added yet.';
        } else {
            echo 'No company description added yet.';
        }
        ?>
    </p>
    <!-- Edit Button (always visible if it's the logged-in user) -->
    <?php if ($_SESSION['user_id'] == $user['id']): ?>
        <button class="btn btn-download-resume btn-sm position-absolute top-0 end-0 m-2" data-bs-toggle="modal" data-bs-target="#editCompanyDescriptionModal">
            <i class="fas fa-edit"></i>
        </button>
    <?php endif; ?>
</div>

<!-- Location -->
<div class="profile-card p-4 mb-4 fade-in position-relative">
    <h3 class="section-title resume-section text-primary">Location</h3>
    <p class="no-data <?php echo empty($employer['location']) ? 'striped-border' : ''; ?>">
        <?php 
        if (isset($employer) && is_array($employer)) {
            echo isset($employer['location']) && !is_null($employer['location']) && !empty($employer['location']) 
                ? htmlspecialchars($employer['location']) 
                : 'No location added yet.';
        } else {
            echo 'No location added yet.';
        }
        ?>
    </p>
    <!-- Edit Button (always visible if it's the logged-in user) -->
    <?php if ($_SESSION['user_id'] == $user['id']): ?>
        <button class="btn btn-download-resume btn-sm position-absolute top-0 end-0 m-2" data-bs-toggle="modal" data-bs-target="#editLocationModal">
            <i class="fas fa-edit"></i>
        </button>
    <?php endif; ?>
</div>

<!-- Company Website -->
<div class="profile-card p-4 mb-4 fade-in position-relative">
    <h3 class="section-title resume-section text-primary">Social Links</h3>
    <p class="no-data <?php echo empty($employer['company_website']) ? 'striped-border' : ''; ?>">
        <?php 
        if (isset($employer) && is_array($employer)) {
            if (isset($employer['company_website']) && !is_null($employer['company_website']) && !empty($employer['company_website'])) {
                echo '<a href="' . htmlspecialchars($employer['company_website']) . '" target="_blank" class="btn btn-view-resume btn-sm rounded-pill hover-link">
                    <i class="fas fa-globe"></i> Visit Website
                </a>';
            } else {
                echo 'No website added yet.';
            }
        } else {
            echo 'No website added yet.';
        }
        ?>
    </p>
    <!-- Edit Button (always visible if it's the logged-in user) -->
    <?php if ($_SESSION['user_id'] == $user['id']): ?>
        <button class="btn btn-download-resume btn-sm position-absolute top-0 end-0 m-2" data-bs-toggle="modal" data-bs-target="#editCompanyWebsiteModal">
            <i class="fas fa-edit"></i>
        </button>
    <?php endif; ?>
</div>



<!-- LinkedIn Profile -->
<div class="profile-card p-4 mb-4 fade-in position-relative">
    <h3 class="section-title resume-section text-primary">LinkedIn Profile</h3>
    <p class="no-data <?php echo empty($user['linkedin_profile']) ? 'striped-border' : ''; ?>">
        <?php if (!empty($user['linkedin_profile'])): ?>
            <a href="<?php echo htmlspecialchars($user['linkedin_profile']); ?>" target="_blank" class="btn btn-create-resume btn-sm rounded-pill hover-link">
                <i class="fab fa-linkedin"></i> View LinkedIn
            </a>
        <?php else: ?>
            No LinkedIn profile added yet.
        <?php endif; ?>
    </p>
    <!-- Edit Button for the User -->
    <?php if ($_SESSION['user_id'] == $user['id']): ?>
        <button class="btn btn-download-resume btn-sm position-absolute top-0 end-0 m-2" data-bs-toggle="modal" data-bs-target="#editLinkedInModal">
            <i class="fas fa-edit"></i>
        </button>
    <?php endif; ?>
</div>

<!-- Portfolio URL -->
<div class="profile-card p-4 mb-4 fade-in position-relative">
    <h3 class="section-title resume-section text-primary">Portfolio URL</h3>
    <p class="no-data <?php echo empty($user['portfolio_url']) ? 'striped-border' : ''; ?>">
        <?php if (!empty($user['portfolio_url'])): ?>
            <a href="<?php echo htmlspecialchars($user['portfolio_url']); ?>" target="_blank" class="btn btn-download-resume btn-sm rounded-pill hover-link">
                <i class="fas fa-globe"></i> Visit Portfolio
            </a>
        <?php else: ?>
            No portfolio added yet.
        <?php endif; ?>
    </p>
    <!-- Edit Button for the User -->
    <?php if ($_SESSION['user_id'] == $user['id']): ?>
        <button class="btn btn-download-resume btn-sm position-absolute top-0 end-0 m-2" data-bs-toggle="modal" data-bs-target="#editPortfolioModal">
            <i class="fas fa-edit"></i>
        </button>
    <?php endif; ?>
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
                <img src="../uploads/<?= htmlspecialchars($user['cover_photo'] ?? 'default_cover.jpg') ?>" alt="Cover Photo" id="fullSizedImage" class="img-fluid" style="max-height: 80vh;">
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
        <h4 class="text-center">
            <?php 
            // Check if the admin is viewing their own profile or another employer's profile
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                // If admin is viewing their own profile
                $viewedUserId = isset($_GET['id']) ? $_GET['id'] : $_SESSION['user_id']; // Profile being viewed
                
                // If the admin is viewing their own profile
                if ($viewedUserId == $_SESSION['user_id']) {
                    echo 'Pending Applications'; // Admin sees Pending Applications for their own profile
                } else {
                    echo 'Managing Jobs'; // Admin sees Managing Jobs for another employer
                }
            } else {
                echo 'Managing Jobs'; // Employers see Managing Jobs for their own profile
            }
            ?>
        </h4><br>
        <?php
        // Check if the current user is an admin or employer
        $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        $isEmployer = isset($_SESSION['role']) && $_SESSION['role'] === 'employer';
        
        // Determine the profile being viewed (either admin's or another employer's)
        $viewedUserId = isset($_GET['id']) ? $_GET['id'] : $_SESSION['user_id']; // The profile being viewed

        if ($isAdmin): 
            // Admin should see "Managing Jobs" for another employer's profile or "Pending Applications" for their own profile
            if ($viewedUserId == $_SESSION['user_id']) {
                // Admin viewing their own profile, show Pending Applications
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
            } else {
                // Admin viewing another employer's profile, show Managing Jobs
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
                    GROUP BY jobs.id"; // Jobs posted by the employer
            }
        elseif ($isEmployer):
            // Employer sees their own jobs (Managing Jobs)
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
                GROUP BY jobs.id"; // All jobs posted by the employer
        endif;

        // Execute query for admin or employer
        if ($isAdmin || $isEmployer): 
            $stmt = $conn->prepare($query_jobs);
            if ($isEmployer || ($isAdmin && $viewedUserId != $_SESSION['user_id'])) {
                // For the admin viewing another employer's profile, or employer viewing their own jobs
                $stmt->bind_param("i", $viewedUserId); // Employer's ID (viewed profile)
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





<!-- Documents Tab -->
<div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
        <div class="profile-cardop-4 mb-4">
        <h4 class="text-center mb-4 mt-4">Company Documents</h4>
<!-- Resume Section -->
<div class="profile-card p-4 mb-4 fade-in">
    <h3 class="section-title resume-section">Document</h3>
    <p class="no-data">
        <?php if (!empty($user['resume_file'])): ?>
            <!-- Download Resume Button -->
            <a href="<?php echo htmlspecialchars($user['resume_file']); ?>" 
                class="btn btn-download-resume me-2" 
                download>
                    <i class="fas fa-download"></i> Download 
            </a>

            <!-- View Resume Button -->
            <button onclick="viewResume('<?php echo htmlspecialchars($user['resume_file']); ?>')" class="btn btn-view-resume me-2">
                <i class="fas fa-eye"></i> View 
            </button>

            <!-- Remove Resume Button -->
            <?php if ($isOwnProfile): ?>
                <button id="remove-resume-button" class="btn btn-remove-resume" data-user-id="<?php echo $user_id; ?>">
                    <i class="fas fa-trash"></i> Remove 
                </button>
            <?php endif; ?>
        <?php else: ?>
            No document uploaded yet.
        <?php endif; ?>
    </p>
    
    <?php if ($isOwnProfile): ?>
        <form action="profile.php?id=<?php echo $user_id; ?>" method="POST" enctype="multipart/form-data" class="mt-3">
            <label for="resume" class="form-label fw-bold">Upload/Replace Document</label>
            <input type="file" name="resume" id="resume" class="form-control rounded-pill my-3" accept=".pdf">
            <small class="form-text text-muted">Only PDF files are allowed. Convert your document into PDF.</small><br><br> <!-- Added note here -->

            <!-- Upload/Replace Resume Button -->
            <button type="submit" class="btn btn-upload-resume me-2">
                <i class="fas fa-upload"></i> Upload/Replace Document
            </button>


        </form>
    <?php endif; ?>
</div>
        </div>
    </div>
</div>

<!-- Edit Profile Button -->
<?php if ($isOwnProfile): ?>
    <div id="edit-profile-button" style="margin-bottom: 30px;" class="text-center fade-in">
        <a href="dashboard.php" class="btn btn-custom rounded-pill px-4">
            <i class="fas fa-edit"></i> Employer Panel
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

<!-- Modal for Editing User Name -->
<div class="modal fade" id="editUserNameModal" tabindex="-1" aria-labelledby="editUserNameModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserNameModalLabel">Edit User Name</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/JOB/pages/update_user_name.php" method="POST">
                    <!-- First Name -->
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    
                    <!-- Middle Name -->
                    <div class="mb-3">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name']); ?>">
                    </div>
                    
                    <!-- Last Name -->
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                    
                    <!-- Extension Name (Optional) -->
                    <div class="mb-3">
                        <label for="ext_name" class="form-label">Extension Name (Optional)</label>
                        <input type="text" class="form-control" id="ext_name" name="ext_name" value="<?php echo htmlspecialchars($user['ext_name']); ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Editing Personal Information -->
<div class="modal fade" id="editPersonalInfoModal" tabindex="-1" aria-labelledby="editPersonalInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- Added modal-lg class to increase the width -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPersonalInfoModalLabel">Edit Personal Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="update_personal_info.php" method="POST">
                    <div class="row">
                        <!-- First row -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="Male" <?php echo ($user['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($user['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Non-Binary" <?php echo ($user['gender'] == 'Non-Binary') ? 'selected' : ''; ?>>Non-Binary</option>
                                <option value="LGBTQ+" <?php echo ($user['gender'] == 'LGBTQ+') ? 'selected' : ''; ?>>LGBTQ+</option>
                                <option value="Other" <?php echo ($user['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Second row -->
                        <div class="col-md-6 mb-3">
                            <label for="birth_date" class="form-label">Birth Date</label>
                            <input type="date" class="form-control" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($user['birth_date']); ?>" required>
                            <!-- Add a span to display the calculated age -->
                            <small id="ageDisplay" class="form-text text-muted"></small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="religion" class="form-label">Religion</label>
                            <input type="text" class="form-control" id="religion" name="religion" value="<?php echo htmlspecialchars($user['religion']); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <!-- Third row -->
                        <div class="col-md-6 mb-3">
                            <label for="weight" class="form-label">Weight (kg)</label>
                            <input type="number" class="form-control" id="weight" name="weight" value="<?php echo htmlspecialchars($user['weight']); ?>" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="height" class="form-label">Height (cm)</label>
                            <input type="number" class="form-control" id="height" name="height" value="<?php echo htmlspecialchars($user['height']); ?>" step="0.01" required>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Fourth row -->
                        <div class="col-md-6 mb-3">
                            <label for="nationality" class="form-label">Nationality</label>
                            <input type="text" class="form-control" id="nationality" name="nationality" value="<?php echo htmlspecialchars($user['nationality']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Fifth row -->
                        <div class="col-md-6 mb-3">
                            <label for="civil_status" class="form-label">Civil Status</label>
                            <select class="form-select" id="civil_status" name="civil_status" required>
                                <option value="Single" <?php echo ($user['civil_status'] == 'Single') ? 'selected' : ''; ?>>Single</option>
                                <option value="Married" <?php echo ($user['civil_status'] == 'Married') ? 'selected' : ''; ?>>Married</option>
                                <option value="Divorced" <?php echo ($user['civil_status'] == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                                <option value="Widowed" <?php echo ($user['civil_status'] == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="street_address" class="form-label">Street Address</label>
                            <input type="text" class="form-control" id="street_address" name="street_address" value="<?php echo htmlspecialchars($user['street_address']); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Sixth row -->
                        <div class="col-md-6 mb-3">
                            <label for="barangay" class="form-label">Barangay</label>
                            <select name="barangay" id="barangay" class="form-select" required>
                                <option value="">Select Barangay</option>
                                <?php
                                // Fetch barangays from the database
                                $query = "SELECT * FROM barangay";
                                $result = $conn->query($query);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        // Checking if the current barangay is the selected one (e.g., pre-populate if editing)
                                        $selected = ($row['id'] == $user['barangay']) ? 'selected' : '';
                                        echo "<option value='" . $row['id'] . "' $selected>" . htmlspecialchars($row['name']) . "</option>";
                                    }
                                } else {
                                    echo "<option value=''>No Barangays Available</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Seventh row -->
                        <div class="col-md-6 mb-3">
                            <label for="zip_code" class="form-label">Zip Code</label>
                            <input type="text" class="form-control" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($user['zip_code']); ?>" required>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Editing Company Name -->
<?php if (isset($user) && is_array($user) && $_SESSION['user_id'] == $user['id']): ?>
    <div class="modal fade" id="editCompanyNameModal" tabindex="-1" aria-labelledby="editCompanyNameModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCompanyNameModalLabel">Edit Company Name</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="update_employer_info.php" method="POST">
                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo isset($employer['company_name']) ? htmlspecialchars($employer['company_name']) : ''; ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Modal for Editing Company Description -->
<?php if (isset($user) && is_array($user) && $_SESSION['user_id'] == $user['id']): ?>
    <div class="modal fade" id="editCompanyDescriptionModal" tabindex="-1" aria-labelledby="editCompanyDescriptionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCompanyDescriptionModalLabel">Edit Company Description</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="update_employer_info.php" method="POST">
                        <div class="mb-3">
                            <label for="company_description" class="form-label">Company Description</label>
                            <textarea class="form-control" id="company_description" name="company_description" rows="4" required><?php echo isset($employer['company_description']) ? htmlspecialchars($employer['company_description']) : ''; ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Modal for Editing Location -->
<?php if (isset($user) && is_array($user) && $_SESSION['user_id'] == $user['id']): ?>
    <div class="modal fade" id="editLocationModal" tabindex="-1" aria-labelledby="editLocationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editLocationModalLabel">Edit Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="update_employer_info.php" method="POST">
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo isset($employer['location']) ? htmlspecialchars($employer['location']) : ''; ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Modal for Editing Company Website -->
<?php if (isset($user) && is_array($user) && $_SESSION['user_id'] == $user['id']): ?>
    <div class="modal fade" id="editCompanyWebsiteModal" tabindex="-1" aria-labelledby="editCompanyWebsiteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCompanyWebsiteModalLabel">Edit Company Website</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="update_employer_info.php" method="POST">
                        <div class="mb-3">
                            <label for="company_website" class="form-label">Company Website</label>
                            <input type="url" class="form-control" id="company_website" name="company_website" value="<?php echo isset($employer['company_website']) ? htmlspecialchars($employer['company_website']) : ''; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Modal for Editing LinkedIn Profile -->
<?php if (isset($user) && is_array($user) && $_SESSION['user_id'] == $user['id']): ?>
    <div class="modal fade" id="editLinkedInModal" tabindex="-1" aria-labelledby="editLinkedInModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editLinkedInModalLabel">Edit LinkedIn Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="update_employer_info.php" method="POST">
                        <div class="mb-3">
                            <label for="linkedin_profile" class="form-label">LinkedIn Profile URL</label>
                            <input type="url" class="form-control" id="linkedin_profile" name="linkedin_profile" value="<?php echo isset($user['linkedin_profile']) ? htmlspecialchars($user['linkedin_profile']) : ''; ?>" placeholder="https://linkedin.com/in/your-profile">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Modal for Editing Portfolio URL -->
<?php if (isset($user) && is_array($user) && $_SESSION['user_id'] == $user['id']): ?>
    <div class="modal fade" id="editPortfolioModal" tabindex="-1" aria-labelledby="editPortfolioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPortfolioModalLabel">Edit Portfolio URL</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="update_employer_info.php" method="POST">
                        <div class="mb-3">
                            <label for="portfolio_url" class="form-label">Portfolio URL</label>
                            <input type="url" class="form-control" id="portfolio_url" name="portfolio_url" value="<?php echo isset($employer['portfolio_url']) ? htmlspecialchars($employer['portfolio_url']) : ''; ?>" placeholder="https://your-portfolio.com">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>






<script src="https://unpkg.com/mammoth/mammoth.browser.min.js"></script>
<script>

function removeCoverPhoto() {
    // Show the confirmation modal
    const confirmationModal = new bootstrap.Modal(document.getElementById('removeCoverPhotoModal'));
    confirmationModal.show();

    // REMOVE COVER PHOTO
    document.getElementById('confirmRemoveCoverPhotoBtn').addEventListener('click', function() {
        // Send AJAX request to remove the cover photo
        fetch('/JOB/employers/update_cover_photo.php', {
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


// TOGGLE TO HIDE PERSONAL INFORMATION SECTION
document.addEventListener("DOMContentLoaded", function() {
    var personalInfoSection = document.getElementById("personal-info-section");
    var toggleButton = document.getElementById("toggle-personal-info-section");

    // Initially, personal information section is visible
    personalInfoSection.style.display = "block"; // Show by default
    toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Show up arrow when visible

    // Add event listener to toggle button
    toggleButton.addEventListener("click", function() {
        // Toggle visibility of the personal information section
        if (personalInfoSection.style.display === "none") {
            personalInfoSection.style.display = "block"; // Show the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>'; // Change icon to up arrow
        } else {
            personalInfoSection.style.display = "none"; // Hide the section
            toggleButton.innerHTML = '<i class="fas fa-chevron-down"></i>'; // Change icon to down arrow
        }
    });
});




    // Check if we have any messages to display from the session or custom PHP variables
    <?php if (isset($_SESSION['success_message']) || isset($_SESSION['error_message']) || (isset($messageType) && isset($messageText))): ?>
        <?php
            // Determine which message to show based on the session or custom variables
            if (isset($_SESSION['success_message'])) {
                $messageType = 'success';
                $messageText = $_SESSION['success_message'];
                unset($_SESSION['success_message']);
            } elseif (isset($_SESSION['error_message'])) {
                $messageType = 'error';
                $messageText = $_SESSION['error_message'];
                unset($_SESSION['error_message']);
            } elseif (isset($messageType) && isset($messageText)) {
                // Using the PHP variables for custom messages (from other scripts)
                // If messageType and messageText are set, use them
            } else {
                // If no message is set, don't show anything
                exit;
            }
        ?>

        // Display the SweetAlert based on the messageType and messageText
        Swal.fire({
            icon: '<?php echo $messageType; ?>',  // success or error
            title: '<?php echo $messageType === 'success' ? 'Success' : 'Error'; ?>',
            text: '<?php echo $messageText; ?>',
            confirmButtonText: 'OK'
        });

    <?php endif; ?>
</script>



</body>
</html>




