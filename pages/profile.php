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
                    You must be logged in to view this page.
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-primary' onclick=\"window.location.href='login.php'\">OK</button>
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

// Check if the user is an Employer or Admin accessing their own profile
if ($user_role === 'employer') {
    // Redirect the employer to their own profile if they access pages/profile.php directly
    if (!isset($_GET['id'])) {
        echo "<script>window.location.href = '/JOB/employers/profile.php';</script>";
        exit();
    }

    // Employers can view applicants' profiles they have applied to
    if (isset($_GET['id'])) {
        $viewed_user_id = (int)$_GET['id'];
        // Check if the employer has the applicant applied to one of their jobs
        $query_check_applicant = "
            SELECT COUNT(*) AS applicant_count
            FROM applications
            WHERE job_id IN (SELECT id FROM jobs WHERE employer_id = ?) AND user_id = ?
        ";
        $stmt_check = $conn->prepare($query_check_applicant);
        $stmt_check->bind_param("ii", $_SESSION['user_id'], $viewed_user_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $check_data = $result_check->fetch_assoc();

        if ($check_data['applicant_count'] === 0) {
            // If the employer hasn't posted a job that the applicant has applied to, redirect or show error
            echo "<script>window.location.href = '/JOB/index.php';</script>";
            exit();
        } else {
            // Allow the employer to view the applicant's profile
            $user_id = $viewed_user_id;
        }
    }
} elseif ($user_role === 'admin') {
    // Redirect the admin to their own profile if they access pages/profile.php directly
    if (!isset($_GET['id'])) {
        echo "<script>window.location.href = '/JOB/admin/profile.php';</script>";
        exit();
    }

    // Admin can view any profile, no restrictions
    if (isset($_GET['id'])) {
        $user_id = (int)$_GET['id'];
    }
} else {
    // Default for user (applicant): They can only view their own profile
    $user_id = $_SESSION['user_id'];

    // Redirect to the user's own profile if 'id' is not their own ID
    if (isset($_GET['id']) && $_GET['id'] != $user_id) {
        echo "<script>window.location.href = '/JOB/pages/profile.php';</script>"; // Or any error page you prefer
        exit();
    }
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
        $target_dir = "../uploads/profile_user/"; // Ensure this directory exists and is writable
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


// Handle LinkedIn and Portfolio updates only (without work experience and skills)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Sanitize inputs for LinkedIn and Portfolio
    $linkedin_profile = trim($_POST['linkedin_profile']);
    $portfolio_url = trim($_POST['portfolio_url']);

    // Update query (no work experience or skills anymore)
    $update_query = "UPDATE users 
                     SET linkedin_profile = ?, 
                         portfolio_url = ? 
                     WHERE id = ?";
    
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi", $linkedin_profile, $portfolio_url, $user_id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Profile updated successfully.</div>";
        header("Location: profile.php?id=$user_id");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Failed to update profile.</div>";
    }
}


// Handle resume upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['resume'])) {
    if ($_FILES["resume"]["size"] == 0) {
        echo "<div class='alert alert-danger'>Please select a file to upload.</div>";
    } else {
        $target_dir = "../uploads/resumes/"; // Ensure this directory exists and is writable
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


// Query to get all education data for the user
$query = "SELECT * FROM education WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);  // "i" for integer
$stmt->execute();
$result = $stmt->get_result();

// Fetch all education records
$education = [];
while ($row = $result->fetch_assoc()) {
    $education[] = $row;
}

// Close the statement
$stmt->close();

// Fetch work experience data for the user
$query = "SELECT * FROM work_experience WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);  // "i" for integer (user_id is assumed to be an integer)
$stmt->execute();
$result = $stmt->get_result();

// Fetch all work experience records into an array
$work_experience = [];
while ($row = $result->fetch_assoc()) {
    $work_experience[] = $row;
}

// Close the statement
$stmt->close();

// Check if there's a session message
if (isset($_SESSION['message'])) {
    // Retrieve message details
    $message = $_SESSION['message'];
    $messageType = $message['type'];
    $messageText = $message['text'];

    // Unset the session message so it doesn't persist across page loads
    unset($_SESSION['message']);
}


// Check if there is a login message to display
if (isset($_SESSION['login_message'])) {
    $message = $_SESSION['login_message'];
    unset($_SESSION['login_message']); // Clear the message after displaying it
} else {
    $message = '';
}

// Fetch skills for the user with category name
$query_skills = "
    SELECT s.id, s.user_id, sl.skill_name, s.proficiency, c.name AS category_name 
    FROM skills s
    JOIN skill_list sl ON s.skill_id = sl.id
    JOIN categories c ON sl.category_id = c.id
    WHERE s.user_id = ?
";
$stmt = $conn->prepare($query_skills);
$stmt->bind_param("i", $user_id);  // "i" for integer
$stmt->execute();
$result_skills = $stmt->get_result();

// Fetch all skills records into an array
$skills = [];
while ($row = $result_skills->fetch_assoc()) {
    $skills[] = $row;
}

// Close the statement
$stmt->close();

// Fetch languages for the user with fluency level
$query_languages = "
    SELECT l.id, l.user_id, l.language_name, l.fluency
    FROM languages l
    WHERE l.user_id = ?
";
$stmt = $conn->prepare($query_languages);
$stmt->bind_param("i", $user_id);  // "i" for integer (user ID)
$stmt->execute();
$result_languages = $stmt->get_result();

// Fetch all language records into an array
$languages = [];
while ($row = $result_languages->fetch_assoc()) {
    $languages[] = $row;
}

// Close the statement
$stmt->close();

// Fetch user achievements
$achievement_query = "
    SELECT * FROM achievements
    WHERE user_id = ? 
    ORDER BY award_date DESC
";
$stmt = $conn->prepare($achievement_query);
$stmt->bind_param("i", $user_id); // Bind the user_id from the variable instead of session
$stmt->execute();
$achievement_result = $stmt->get_result();

// Fetch achievements into an array
$achievements = [];
if ($achievement_result->num_rows > 0) {
    while ($row = $achievement_result->fetch_assoc()) {
        $achievements[] = $row;
    }
} else {
    // No achievements found, you can log it or set an empty array
    error_log("No achievements found for user ID: " . $user_id);
    $achievements = [];
}

// Close the statement
$stmt->close();


// Fetch user certificates
$certificate_query = "
    SELECT * FROM certificates
    WHERE user_id = ? 
    ORDER BY issue_date DESC
";
$stmt = $conn->prepare($certificate_query);
$stmt->bind_param("i", $user_id); // Bind the user_id from the variable instead of session
$stmt->execute();
$certificate_result = $stmt->get_result();

// Fetch certificates into an array
$certificates = [];
if ($certificate_result->num_rows > 0) {
    while ($row = $certificate_result->fetch_assoc()) {
        $certificates[] = $row;
    }
} else {
    // No certificates found, log it or set an empty array
    error_log("No certificates found for user ID: " . $user_id);
    $certificates = [];
}

// Close the statement
$stmt->close();


// Fetch user references
$reference_query = "
    SELECT * FROM `references`
    WHERE user_id = ?
    ORDER BY created_at DESC
";
$stmt = $conn->prepare($reference_query);
$stmt->bind_param("i", $user_id); // Bind the user_id from the session
$stmt->execute();
$reference_result = $stmt->get_result();

// Fetch references into an array
$references = [];
if ($reference_result->num_rows > 0) {
    while ($row = $reference_result->fetch_assoc()) {
        $references[] = $row;
    }
} else {
    $references = [];
}

// Close the statement
$stmt->close();

// Fetch job preferences from the database, including preferred positions
$job_preferences_query = "
    SELECT jp.*, GROUP_CONCAT(p.position_name) AS preferred_positions
    FROM job_preferences jp
    LEFT JOIN job_preferences_positions jp_pos ON jp.id = jp_pos.job_preference_id
    LEFT JOIN job_positions p ON jp_pos.position_id = p.id
    WHERE jp.user_id = ?
    GROUP BY jp.id
    LIMIT 1
";
$stmt = $conn->prepare($job_preferences_query);
$stmt->bind_param("i", $user_id); // Bind the user_id from the session
$stmt->execute();
$job_preferences_result = $stmt->get_result();

// Fetch the job preferences data
$job_preferences = [];
if ($job_preferences_result->num_rows > 0) {
    $job_preferences = $job_preferences_result->fetch_assoc();
} else {
    // If no job preferences are found, set defaults or an empty array
    $job_preferences = [];
}

// Close the statement
$stmt->close();

// Handle preferred_positions
$preferred_positions_display = !empty($job_preferences['preferred_positions']) ? $job_preferences['preferred_positions'] : 'Not specified';





?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Job Portal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.min.css" rel="stylesheet">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.min.js"></script>
<link rel="stylesheet" href="/JOB/assets/profile.css">

<style>


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
        <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="false">Documents</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="applications-tab" data-bs-toggle="tab" data-bs-target="#applications" type="button" role="tab" aria-controls="applications" aria-selected="false">Applications</button>
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
                <img src="<?php echo $user['cover_photo'] ? $user['cover_photo'] : '../uploads/default/COVER.jpg'; ?>" alt="Cover Photo" class="cover-photo w-100 h-100 object-fit-cover" style="object-position: center;">
<!-- Edit Cover Photo Button -->
<?php if ($user_id == $_SESSION['user_id']): ?>
    <!-- Edit Cover Photo Button (Only visible to the profile owner) -->
    <button type="button" class="btn btn-light position-absolute top-0 end-0 m-3" data-bs-toggle="modal" data-bs-target="#coverPhotoModal">
        <i class="fas fa-camera"></i> Edit Cover
    </button>
<?php elseif ($user_role === 'admin' || $user_role === 'employer'):  ?>
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
            <i class="fas fa-chevron-up"></i> <!-- Initially showing the up arrow because the section is visible -->
        </button>
    </div>
    <div id="personal-info-section" style="background-color: #fff; border-radius: 12px; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08); padding: 20px; transition: transform 0.2s ease, box-shadow 0.2s ease;">
    <div class="row">
        <!-- Left Column -->
        <div class="col-md-6">
            <p class="mb-3"><strong><i class="fas fa-envelope me-2" style="color: #17A2B8;"></i>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p class="mb-3"><strong><i class="fas fa-venus-mars me-2" style="color: #FFC107;"></i>Gender:</strong> <?php echo htmlspecialchars($user['gender']); ?></p>
            <p class="mb-3"><strong><i class="fas fa-birthday-cake me-2" style="color: #28A745;"></i>Birth Date:</strong> <?php echo htmlspecialchars($user['birth_date']); ?></p>
            <p class="mb-3"><strong><i class="fas fa-hourglass-half me-2" style="color: #DC3545;"></i>Age:</strong> <?php echo htmlspecialchars($user['age']); ?></p>
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
                    $stmt->bind_param("i", $user['barangay']); // Bind the barangay ID from the user's data
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
        </div>
    </div>

    <!-- Edit Button -->
    <?php if ($_SESSION['user_id'] == $user['id']): ?>
        <div class="mt-4">
            <button class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#editPersonalInfoModal" >
                <i class="fas fa-edit me-2"></i> Edit Info
            </button>
        </div>
    <?php endif; ?>
</div>
</div>


<!-- Educational Background -->
<div class="profile-card p-4 mb-4 fade-in" style="border-radius: 15px;">
<div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="section-title resume-section text-primary" style="font-family: 'Roboto', sans-serif; font-weight: bold; color: #333;">Educational Background</h3>
        <button id="toggle-education-section" class="btn btn-link text-secondary p-0" style="font-size: 1.2rem; background: none; border: none;">
            <i class="fas fa-chevron-up"></i> <!-- Initially showing the up arrow because the section is visible -->
        </button>
    </div>
    
    <div id="education-section" class="<?php echo empty($education) ? 'striped-border' : ''; ?>" style="padding: 20px; border-radius: 10px;">
    <?php if (!empty($education)): ?>
        <?php foreach ($education as $edu): ?>
            <div class="education-item mb-4" style="background-color: #fff; border-radius: 12px; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08); padding: 20px; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                <!-- Header with Education Level -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted" style="font-size: 1.1rem; font-weight: 600; color: #333; position: relative; display: inline-block;">
                        <?php echo ucfirst(htmlspecialchars($edu['education_level'])); ?>
                        <span style="position: absolute; bottom: -4px; left: 0; width: 100%; height: 2px; background: linear-gradient(90deg, #007bff, #ff7e5f); border-radius: 2px;"></span>
                    </span>
                    <?php if ($_SESSION['user_id'] == $edu['user_id']): ?>
                        <!-- Edit and Delete Buttons -->
                        <div class="d-flex">
                            <a href="#" class="btn btn-sm btn-download me-2" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($edu)); ?>)">
                                <i class="fas fa-edit"></i> <!-- Edit Icon -->
                            </a>
                            <a href="#" style="border:none !important;" class="btn btn-sm btn-remove" onclick="openDeleteEduModal(<?php echo $edu['id']; ?>)">
                                <i class="fas fa-times"></i> <!-- Delete Icon -->
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Education Details -->
                <div class="mb-3">
                    <p><strong><i class="fas fa-school me-2" style="color: #17A2B8;"></i>Institution:</strong> 
                        <?php echo htmlspecialchars($edu['institution']); ?>
                    </p>
                </div>

                <!-- Course Details (for college, vocational, and graduate education levels) -->
                <?php if (in_array($edu['education_level'], ['college', 'graduate', 'vocational'])): ?>
                    <div class="mb-3">
                        <p><strong><i class="fas fa-book me-2" style="color: #FFC107;"></i>Course:</strong> 
                            <?php echo htmlspecialchars($edu['course']); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <p><strong><i class="fas fa-calendar-check me-2" style="color: #28A745;"></i>Status:</strong> 
                        <?php echo htmlspecialchars($edu['status']); ?>
                    </p>
                </div>

                <!-- Completion or Expected Completion Year -->
                <?php if ($edu['status'] == 'Completed'): ?>
                    <div class="mb-3">
                        <p><strong><i class="fas fa-calendar-alt me-2" style="color: #DC3545;"></i>Completion Year:</strong> 
                            <?php echo htmlspecialchars($edu['completion_year']); ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="mb-3">
                        <p><strong><i class="fas fa-calendar-alt me-2" style="color: #DC3545;"></i>Expected Completion:</strong> 
                            <?php echo htmlspecialchars($edu['expected_completion_date']); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Course Highlights (for college, vocational, and graduate education levels) -->
                <?php if (in_array($edu['education_level'], ['college', 'graduate', 'vocational']) && !empty($edu['course_highlights'])): ?>
                    <div class="mb-3">
                        <p><strong><i class="fas fa-trophy me-2" style="color: #6F42C1;"></i>Course Highlights:</strong> 
                            <?php echo htmlspecialchars($edu['course_highlights']); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <!-- Add Another Education Button (Visible only for own profile) -->
        <?php if ($_SESSION['user_id'] == $user_id): ?>
            <div class="mt-4 ">
                <button class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addEducationModal">
                    <i class="fas fa-plus me-2"></i> Add Education
                </button>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <!-- No Education Added Yet -->
        <div class="py-4" >
            <p class="mb-0" style="color: #666; font-style: italic;"
            >No education added yet.</p>
            <?php if ($_SESSION['user_id'] == $user_id): ?>
                <div class="mt-3">
                    <button class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addEducationModal">
                        <i class="fas fa-plus me-2"></i> Add Education
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</div>







<!-- Work Experience Section with Toggle Button -->
<div class="profile-card p-4 mb-4 fade-in" style="border-radius: 15px;">
    <!-- Section Header with Toggle Icon on the Right -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="section-title resume-section text-primary" style="font-family: 'Roboto', sans-serif; font-weight: bold; color: #333;">Work Experience</h3>
        <button id="toggle-work-experience" class="btn btn-link text-secondary p-0" style="font-size: 1.2rem; background: none; border: none;">
            <i class="fas fa-chevron-up"></i> <!-- Initially showing the up arrow because the section is visible -->
        </button>
    </div>
    <div id="work-experience-section" class="<?php echo empty($work_experience) ? 'striped-border' : ''; ?>" style="padding: 20px; border-radius: 10px;">
    <?php if (!empty($work_experience)): ?>
        <?php foreach ($work_experience as $work): ?>
            <div class="work-experience-item mb-4" style="background-color: #fff; border-radius: 12px; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08); padding: 20px; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                <!-- Header with Job Title -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted" style="font-size: 1.1rem; font-weight: 600; color: #333; position: relative; display: inline-block;">
    <?php echo ucfirst(htmlspecialchars($work['job_title'])); ?>
    <span style="position: absolute; bottom: -4px; left: 0; width: 100%; height: 2px; background: linear-gradient(90deg, #007bff, #ff7e5f); border-radius: 2px;"></span>
</span>
                    <?php if ($_SESSION['user_id'] == $work['user_id']): ?>
                        <!-- Edit and Delete Buttons -->
                        <div class="d-flex">
                            <a href="#" class="btn btn-sm btn-download me-2" onclick="openEditWorkModal(<?php echo htmlspecialchars(json_encode($work)); ?>)">
                                <i class="fas fa-edit"></i> <!-- Edit Icon -->
                            </a>
                            <a href="#" class="btn btn-sm btn-remove" onclick="openDeleteWorkExperienceModal(<?php echo $work['id']; ?>)">
                                <i class="fas fa-times"></i> <!-- Delete Icon -->
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Work Details -->
                <div class="mb-3">
                    <p><strong><i class="fas fa-building me-2" style="color: #17A2B8;"></i>Company:</strong> 
                        <?php echo htmlspecialchars($work['company_name']); ?>
                    </p>
                </div>

                <div class="mb-3">
                    <p><strong><i class="fas fa-briefcase me-2" style="color: #FFC107;"></i>Description:</strong> 
                        <?php echo htmlspecialchars($work['job_description']); ?>
                    </p>
                </div>

                <div class="mb-3">
                    <p><strong><i class="fas fa-cogs me-2" style="color: #28A745;"></i>Employment Type:</strong> 
                        <?php echo ucfirst(htmlspecialchars($work['employment_type'])); ?>
                    </p>
                </div>

                <div class="mb-3">
                    <p><strong><i class="fas fa-map-marker-alt me-2" style="color: #DC3545;"></i>Work Location:</strong> 
                        <?php echo ucfirst(htmlspecialchars($work['job_location'])); ?>
                    </p>
                </div>

                <!-- Country (Displayed only for overseas job locations) -->
                <?php if ($work['job_location'] == 'overseas'): ?>
                    <div class="mb-3">
                        <p><strong><i class="fas fa-globe me-2" style="color: #6F42C1;"></i>Country:</strong> 
                            <?php echo ucfirst(htmlspecialchars($work['country'] ?? 'N/A')); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <p><strong><i class="fas fa-suitcase-rolling me-2" style="color: #FD7E14;"></i>Work Type:</strong> 
                        <?php echo ucfirst(htmlspecialchars($work['work_type'])); ?>
                    </p>
                </div>

                <div class="mb-3">
                    <p><strong><i class="fas fa-calendar-alt me-2" style="color: #6610F2;"></i>Started:</strong> 
                        <?php echo htmlspecialchars($work['start_date']); ?>
                    </p>
                </div>

                <!-- End Date (Displayed only if provided) -->
                <?php if (!empty($work['end_date'])): ?>
                    <div class="mb-3">
                        <p><strong><i class="fas fa-calendar-alt me-2" style="color: #6610F2;"></i>End Date:</strong> 
                            <?php echo htmlspecialchars($work['end_date']); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <!-- Add Another Work Experience Button (Visible only for own profile) -->
        <?php if ($_SESSION['user_id'] == $user_id): ?>
            <div class="mt-4 ">
                <button class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addWorkExperienceModal">
                    <i class="fas fa-plus me-2"></i> Add Work
                </button>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <!-- No Work Experience Added Yet -->
        <div class=" py-4" ">
            <p class="mb-0" style="color: #666; font-style: italic;"
            >No work experience added yet.</p>
            <?php if ($_SESSION['user_id'] == $user_id): ?>
                <div class="mt-3">
                    <button class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addWorkExperienceModal">
                        <i class="fas fa-plus me-2"></i> Add Work
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</div>


<!-- Job Preferences Section -->
<div class="profile-card p-4 mb-4 fade-in" style="border-radius: 15px;">
    <!-- Section Header with Toggle Icon on the Right -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="section-title resume-section text-primary" style="font-family: 'Roboto', sans-serif; font-weight: bold; color: #333;">
            Job Preferences
        </h3>
        <button id="toggle-job-preferences-section" class="btn btn-link text-secondary p-0" style="font-size: 1.2rem; background: none; border: none;">
            <i class="fas fa-chevron-up"></i> <!-- Initially showing the up arrow because the section is visible -->
        </button>
    </div>
    <div id="job-preferences-section" class="<?php echo empty($job_preferences) ? 'striped-border' : ''; ?>" style="padding: 20px; border-radius: 10px;">
        <?php if (!empty($job_preferences)): ?>
            <div class="job-preferences-item mb-4" style="background-color: #fff; border-radius: 12px; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08); padding: 20px; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                <!-- Preferred Positions Title -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted" style="font-size: 1.1rem; font-weight: 600; color: #333; position: relative; display: inline-block;">
                        Preferred Occupation :
                        <span style="position: absolute; bottom: -4px; left: 0; width: 100%; height: 2px; background: linear-gradient(90deg, #007bff, #ff7e5f); border-radius: 2px;"></span>
                    </span>
                    <?php if ($_SESSION['user_id'] == $job_preferences['user_id']): ?>
                        <!-- Edit and Delete Buttons -->
                        <div class="d-flex">
                            <a href="#" class="btn btn-sm btn-download me-2" onclick="openEditJobPreferencesModal(<?php echo htmlspecialchars(json_encode($job_preferences)); ?>)">
                                <i class="fas fa-edit"></i> <!-- Edit Icon -->
                            </a>
                            <a href="#" style="border:none !important;" class="btn btn-sm btn-remove" onclick="openDeleteJobPreferencesModal(<?php echo $job_preferences['id']; ?>)">
                                <i class="fas fa-times"></i> <!-- Delete Icon -->
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Preferred Positions -->
                <div class="mb-3">
                    <p><strong><i class="fas fa-arrow-right me-2" style="color: #6F42C1;"></i></strong>
                        <?php 
                        // Fetch and display the associated positions for this job preference
                        if (!empty($preferred_positions_display)) {
                            // Add a space after each comma for the preferred positions
                            $preferred_positions_display = str_replace(',', ',  ', $preferred_positions_display);
                            echo htmlspecialchars($preferred_positions_display);
                        } else {
                            echo 'Not specified';
                        }
                        ?>
                    </p>
                </div>

                <!-- Work Type -->
                <div class="mb-3">
                    <p><strong><i class="fas fa-cogs me-2" style="color: #28A745;"></i>Work Type:</strong> 
                        <?php echo isset($job_preferences['work_type']) ? ucfirst(htmlspecialchars($job_preferences['work_type'])) : 'Not specified'; ?>
                    </p>
                </div>

                <!-- Job Location -->
                <div class="mb-3">
                    <p><strong><i class="fas fa-map-marker-alt me-2" style="color: #DC3545;"></i>Preferred Work Location:</strong> 
                        <?php echo isset($job_preferences['job_location']) ? ucfirst(htmlspecialchars($job_preferences['job_location'])) : 'Not specified'; ?>
                    </p>
                </div>

                <!-- Employment Type -->
                <div class="mb-3">
                    <p><strong><i class="fas fa-briefcase me-2" style="color: #FFC107;"></i>Employment Type:</strong> 
                        <?php echo isset($job_preferences['employment_type']) ? ucfirst(htmlspecialchars($job_preferences['employment_type'])) : 'Not specified'; ?>
                    </p>
                </div>

            </div>
        <?php else: ?>
            <!-- No Job Preferences Added Yet -->
            <div class="py-4">
                <p class="mb-0" style="color: #666; font-style: italic;">No job preferences added yet.</p>
                <?php if ($_SESSION['user_id'] == $user_id): ?>
                    <div class="mt-3">
                        <button class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addJobPreferencesModal">
                            <i class="fas fa-plus me-2"></i> Add Preferences
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>


    </div>
</div>




<!-- Achievements & Awards Section -->
<div class="profile-card p-4 mb-4 fade-in" style="border-radius: 15px;">
    <!-- Section Header with Toggle Icon on the Right -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="section-title resume-section text-primary" style="font-family: 'Roboto', sans-serif; font-weight: bold; color: #333;">
            Achievements & Awards
        </h3>
        <button id="toggle-achievements-section" class="btn btn-link text-secondary p-0" style="font-size: 1.2rem; background: none; border: none;">
            <i class="fas fa-chevron-up"></i> <!-- Initially showing the up arrow because the section is visible -->
        </button>
    </div>

    <div id="achievements-section" class="<?php echo empty($achievements) ? 'striped-border' : ''; ?>" style="padding: 20px; border-radius: 10px;">
    <?php if (!empty($achievements)): ?>
        <?php foreach ($achievements as $achievement): ?>
            <div class="achievement-item mb-4" style="background-color: #fff; border-radius: 12px; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08); padding: 20px; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                <!-- Header with Achievement Title -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted" style="font-size: 1.1rem; font-weight: 600; color: #333; position: relative; display: inline-block;">
    <?php echo ucfirst(htmlspecialchars($achievement['award_name'])); ?>
    <span style="position: absolute; bottom: -4px; left: 0; width: 100%; height: 2px; background: linear-gradient(90deg, #007bff, #ff7e5f); border-radius: 2px;"></span>
</span>
                    <?php if ($_SESSION['user_id'] == $achievement['user_id']): ?>
                        <!-- Edit and Delete Buttons -->
                        <div class="d-flex">
                            <a href="#" class="btn btn-sm btn-download me-2" 
                               onclick="openEditAchievementModal(<?php echo $achievement['id']; ?>, '<?php echo addslashes($achievement['award_name']); ?>', '<?php echo addslashes($achievement['organization']); ?>', '<?php echo $achievement['award_date']; ?>', '<?php echo addslashes($achievement['proof_file']); ?>')">
                                <i class="fas fa-edit"></i> <!-- Edit Icon -->
                            </a>

                            <a href="#" style="border:none !important;" class="btn btn-sm btn-remove" onclick="openDeleteAchievementModal(<?php echo $achievement['id']; ?>)">
                                <i class="fas fa-times"></i> <!-- Delete Icon -->
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Achievement Details -->
                <div class="mb-3">
                    <p><strong><i class="fas fa-trophy me-2" style="color: #FFC107;"></i>Awarded By:</strong> 
                        <?php echo htmlspecialchars($achievement['organization']); ?>
                    </p>
                </div>

                <div class="mb-3">
                    <p><strong><i class="fas fa-calendar-check me-2" style="color: #17A2B8;"></i>Award Date:</strong> 
                        <?php echo date('M d, Y', strtotime($achievement['award_date'])); ?>
                    </p>
                </div>

                <!-- Display the proof file if it exists -->
                <?php if (!empty($achievement['proof_file'])): ?>
                    <div class="mb-3">
                        <a href="<?php echo htmlspecialchars($achievement['proof_file']); ?>" target="_blank" class="btn btn-sm btn-download">
                            <i class="fas fa-file-alt me-2"></i> Attached File
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <!-- Add Another Achievement Button (Visible only for own profile) -->
        <?php if ($_SESSION['user_id'] == $user_id): ?>
            <div class="mt-4 ">
                <button class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addAchievementModal">
                    <i class="fas fa-plus me-2"></i> Add Achievement or award
                </button>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <!-- No Achievements Added Yet -->
        <div class=" py-4" >
            <p class="mb-0" style="color: #666; font-style: italic;">No achievements added yet.</p>
            <?php if ($_SESSION['user_id'] == $user_id): ?>
                <div class="mt-3">
                    <button class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addAchievementModal">
                        <i class="fas fa-plus me-2"></i> Add Achievement
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</div>

<!-- References Section -->
<div class="profile-card p-4 mb-4 fade-in" style="border-radius: 15px;">
    <!-- Section Header with Toggle Icon on the Right -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="section-title resume-section text-primary" style="font-family: 'Roboto', sans-serif; font-weight: bold; color: #333;">
            Character References
        </h3>
        <button id="toggle-references-section" class="btn btn-link text-secondary p-0" style="font-size: 1.2rem; background: none; border: none;">
            <i class="fas fa-chevron-up"></i> <!-- Initially showing the up arrow because the section is visible -->
        </button>
    </div>

    <div id="references-section" class="<?php echo empty($references) ? 'striped-border' : ''; ?>" style="padding: 20px; border-radius: 10px;">
        <?php if (!empty($references)): ?>
            <?php foreach ($references as $reference): ?>
                <div class="reference-item mb-4" style="background-color: #fff; border-radius: 12px; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08); padding: 20px; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted" style="font-size: 1.1rem; font-weight: 600; color: #333; position: relative; display: inline-block;">
    <?php echo ucfirst(htmlspecialchars($reference['name'])); ?>
    <span style="position: absolute; bottom: -4px; left: 0; width: 100%; height: 2px; background: linear-gradient(90deg, #007bff, #6f42c1); border-radius: 2px;"></span>
</span>
                        <?php if ($_SESSION['user_id'] == $reference['user_id']): ?>
                            <!-- Edit and Delete Buttons -->
                            <div class="d-flex">
                                <a href="#" class="btn btn-sm btn-download me-2" 
                                   onclick="editReference(<?php echo $reference['id']; ?>, '<?php echo addslashes($reference['name']); ?>', '<?php echo addslashes($reference['position']); ?>', '<?php echo addslashes($reference['workplace']); ?>', '<?php echo addslashes($reference['contact_number']); ?>')">
                                    <i class="fas fa-edit"></i> <!-- Edit Icon -->
                                </a>

                                <a href="#" class="btn btn-sm btn-remove" onclick="openDeleteReferenceModal(<?php echo $reference['id']; ?>)">
                                    <i class="fas fa-times"></i> <!-- Delete Icon -->
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="reference-details">
                        <p class="mb-2"><strong><i class="fas fa-user-tie me-2" style="color: #007bff;"></i>Position:</strong> <?php echo htmlspecialchars($reference['position']); ?></p>
                        <p class="mb-2"><strong><i class="fas fa-building me-2" style="color: #28a745;"></i>Workplace:</strong> <?php echo htmlspecialchars($reference['workplace']); ?></p>
                        <p><strong><i class="fas fa-phone-alt me-2" style="color: #fd7e14;"></i>Contact Number:</strong> <?php echo htmlspecialchars($reference['contact_number']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Add Another Reference Button (Visible only for own profile) -->
            <?php if ($_SESSION['user_id'] == $user_id): ?>
                <div class="mt-4">
                    <button class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addReferenceModal" >
                        <i class="fas fa-plus me-2"></i> Add Reference
                    </button>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- No References Added Yet -->
            <div class="py-4">
                <p class="mb-0" style="color: #666; font-style: italic;"
                >No references added yet.</p>
                <?php if ($_SESSION['user_id'] == $user_id): ?>
                    <div class="mt-3">
                        <button class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addReferenceModal" >
                            <i class="fas fa-plus me-2"></i> Add Reference
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>




<!-- Skills Section -->
<div class="profile-card p-4 mb-4 fade-in" style="border-radius: 15px;">
    <!-- Section Header with Toggle Icon on the Right -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="section-title resume-section text-primary" style="font-family: 'Roboto', sans-serif; font-weight: bold; color: #333;">
            Skills
        </h3>
        <button id="toggle-skills-section" class="btn btn-link text-secondary p-0" style="font-size: 1.5rem; background: none; border: none;">
            <i class="fas fa-chevron-up"></i> <!-- Initially showing the up arrow because the section is visible -->
        </button>
    </div>
    <div id="skills-section" class="<?php echo empty($skills) ? 'striped-border' : ''; ?>">
        <?php if (!empty($skills)): ?>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($skills as $skill): ?>
                    <div class="skill-item d-flex align-items-center p-2 rounded" 
                         style="background-color: #f8f9fa; border: 1px solid #ddd;" 
                         title="<?php echo htmlspecialchars($skill['category_name']); ?>"> <!-- Tooltip for category name -->
                        <span>
                            <?php echo htmlspecialchars($skill['skill_name']); ?> 
                            <small class="text-muted">(<?php echo htmlspecialchars($skill['proficiency']); ?>)</small>
                        </span>
                        <?php if ($_SESSION['user_id'] == $skill['user_id']): ?>
                            <button class="btn btn-sm btn-link text-danger ms-2" 
                                    onclick="openDeleteSkillModal(<?php echo $skill['id']; ?>, '<?php echo htmlspecialchars($skill['skill_name']); ?>', '<?php echo htmlspecialchars($skill['proficiency']); ?>')">
                                <i class="fas fa-times"></i> <!-- "x" button -->
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: #666; font-style: italic;"
            >No skills added yet.</p>
        <?php endif; ?>

        <!-- Add Skill Button (Visible only for own profile) -->
        <?php if ($_SESSION['user_id'] == $user_id): ?>
            <div class="mt-4">
                <button class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addSkillModal" >
                    <i class="fas fa-plus"></i> Add Skill
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Languages Section -->
<div class="profile-card p-4 mb-4 fade-in" style="border-radius: 15px;">
    <!-- Section Header with Toggle Icon on the Right -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="section-title resume-section text-primary" style="font-family: 'Roboto', sans-serif; font-weight: bold; color: #333;">
            Languages
        </h3>
        <button id="toggle-languages-section" class="btn btn-link text-secondary p-0" style="font-size: 1.5rem; background: none; border: none;">
            <i class="fas fa-chevron-up"></i> <!-- Initially showing the up arrow because the section is visible -->
        </button>
    </div>
    <div id="languages-section" class="<?php echo empty($languages) ? 'striped-border' : ''; ?>">
        <?php if (!empty($languages)): ?>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($languages as $language): ?>
                    <div class="language-item d-flex align-items-center p-2 rounded" 
                         style="background-color: #f8f9fa; border: 1px solid #ddd;" 
                         title="<?php echo htmlspecialchars($language['language_name']); ?>"> <!-- Tooltip for language -->
                        <span>
                            <?php echo htmlspecialchars($language['language_name']); ?> 
                            <small class="text-muted">(<?php echo htmlspecialchars($language['fluency']); ?>)</small>
                        </span>
                        <?php if ($_SESSION['user_id'] == $language['user_id']): ?>
                            <button class="btn btn-sm btn-link text-danger ms-2" 
                                    onclick="openDeleteLanguageModal(<?php echo $language['id']; ?>, '<?php echo htmlspecialchars($language['language_name']); ?>', '<?php echo htmlspecialchars($language['fluency']); ?>')">
                                <i class="fas fa-times"></i> <!-- "x" button -->
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: #666; font-style: italic;"
            >No languages added yet.</p>
        <?php endif; ?>

        <!-- Add Language Button (Visible only for own profile) -->
        <?php if ($_SESSION['user_id'] == $user_id): ?>
            <div class="mt-4">
                <button class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addLanguagesModal" >
                    <i class="fas fa-plus"></i> Add Language
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>






<!-- LinkedIn Profile -->
<div class="profile-card p-4 mb-4 fade-in position-relative">
    <h3 class="section-title resume-section text-primary ">LinkedIn Profile</h3>
    <p class="no-data <?php echo empty($user['linkedin_profile']) ? 'striped-border' : ''; ?>">
        <?php if (!empty($user['linkedin_profile'])): ?>
            <a href="<?php echo htmlspecialchars($user['linkedin_profile']); ?>" target="_blank" class="btn btn-create btn-sm rounded-pill hover-link">
                <i class="fab fa-linkedin"></i> View LinkedIn
            </a>
        <?php else: ?>
            No LinkedIn profile added yet.
        <?php endif; ?>
    </p>
    <!-- Edit Button for the User -->
    <?php if ($_SESSION['user_id'] == $user['id']): ?>
        <button class="btn btn-download btn-sm position-absolute top-0 end-0 m-2" data-bs-toggle="modal" data-bs-target="#editLinkedInModal">
            <i class="fas fa-edit"></i>
        </button>
    <?php endif; ?>
</div>

<!-- Portfolio URL -->
<div class="profile-card p-4 mb-4 fade-in position-relative">
    <h3 class="section-title resume-section text-primary ">Portfolio URL</h3>
    <p class="no-data <?php echo empty($user['portfolio_url']) ? 'striped-border' : ''; ?>">
        <?php if (!empty($user['portfolio_url'])): ?>
            <a href="<?php echo htmlspecialchars($user['portfolio_url']); ?>" target="_blank" class="btn btn-download btn-sm rounded-pill hover-link">
                <i class="fas fa-globe"></i> Visit Portfolio
            </a>
        <?php else: ?>
            No portfolio added yet.
        <?php endif; ?>
    </p>
    <!-- Edit Button for the User -->
    <?php if ($_SESSION['user_id'] == $user['id']): ?>
        <button class="btn btn-download btn-sm position-absolute top-0 end-0 m-2" data-bs-toggle="modal" data-bs-target="#editPortfolioModal">
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
                <img src="../uploads/<?= htmlspecialchars($user['cover_photo'] ?? '../uploads/default/COVER.jpg') ?>" alt="Cover Photo" id="fullSizedImage" class="img-fluid" style="max-height: 80vh;">
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
            // Check if the current user is an admin, employer, or applicant
            $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
            $isEmployer = isset($_SESSION['role']) && $_SESSION['role'] === 'employer';
            $isOwnProfile = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id;
            
            // Display title based on role
            if ($isAdmin) {
                echo 'All Job Applications';
            } elseif ($isEmployer) {
                echo 'Applications for Your Jobs';
            } else {
                echo 'My Applications'; // For applicants (users)
            }
            ?>
        </h4><br>
        <?php
        // Check if the current user is an admin, employer, or applicant
        if ($isAdmin || $isOwnProfile || $isEmployer): 
            // If the user is an admin or viewing their own profile (applicant), fetch the applications for the user
            if ($isAdmin || $isOwnProfile) {
                // Query for fetching applications for an applicant, excluding canceled ones
                $query_jobs = "
                    SELECT DISTINCT jobs.title, 
                        GROUP_CONCAT(categories.name SEPARATOR ', ') AS category, 
                        jobs.location, 
                        jobs.id AS job_id, 
                        applications.status, 
                        applications.applied_at, 
                        applications.resume_file, 
                        applications.status_updated_at 
                    FROM applications 
                    JOIN jobs ON applications.job_id = jobs.id 
                    JOIN job_categories ON jobs.id = job_categories.job_id 
                    JOIN categories ON job_categories.category_id = categories.id
                    WHERE applications.user_id = ? AND applications.status != 'canceled'
                    GROUP BY jobs.id
                ";
            } elseif ($isEmployer) {
                // Query for fetching applications made by the specific user for jobs posted by the employer, excluding canceled ones
                $query_jobs = "
                    SELECT DISTINCT jobs.title, 
                        GROUP_CONCAT(categories.name SEPARATOR ', ') AS category, 
                        jobs.location, 
                        jobs.id AS job_id, 
                        applications.status, 
                        applications.applied_at, 
                        applications.resume_file, 
                        applications.status_updated_at,
                        applications.user_id AS applicant_id 
                    FROM applications 
                    JOIN jobs ON applications.job_id = jobs.id 
                    JOIN job_categories ON jobs.id = job_categories.job_id 
                    JOIN categories ON job_categories.category_id = categories.id
                    WHERE jobs.employer_id = ? AND applications.user_id = ? AND applications.status != 'canceled'
                    GROUP BY jobs.id
                ";
            }

            // Prepare and execute the query
            $stmt = $conn->prepare($query_jobs);
            if ($isEmployer) {
                $stmt->bind_param("ii", $_SESSION['user_id'], $user_id); // Employer's ID and specific user's ID
            } else {
                $stmt->bind_param("i", $user_id); // Applicant's ID
            }
            $stmt->execute();
            $result_jobs = $stmt->get_result();
            
            // Display content based on conditions
            if ($result_jobs->num_rows > 0): ?>
                <div class="job-list">
                    <?php 
                    // Track jobs to avoid displaying the same job multiple times
                    $seen_jobs = [];
                    while ($job = $result_jobs->fetch_assoc()):
                        if (!in_array($job['job_id'], $seen_jobs)):
                            $seen_jobs[] = $job['job_id'];
                            ?>
                            <div class="job-card card mb-3 shadow-sm rounded">
                                <div class="card-body">
                                    <!-- Job Header -->
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="text-primary"><?php echo htmlspecialchars($job['title']); ?></strong>
                                        <!-- Display status badge -->
                                        <span class="badge 
                                            <?php if ($job['status'] === 'pending'): ?>bg-warning text-dark
                                            <?php elseif ($job['status'] === 'accepted'): ?>bg-success
                                            <?php elseif ($job['status'] === 'rejected'): ?>bg-danger
                                            <?php endif; ?>">
                                            <?php echo ucfirst($job['status']); ?>
                                        </span>
                                    </div>

                                    <!-- Job Details -->
                                    <div class="job-details mt-3">
                                        <p class="mb-1"><i class="fas fa-briefcase me-2"></i><?php echo htmlspecialchars($job['category']); ?></p>
                                        <p class="mb-1"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($job['location']); ?></p>
                                        <p class="mb-1"><i class="fas fa-clock me-2"></i>Applied on: <?php echo date('M d, Y', strtotime($job['applied_at'])); ?></p>
                                        <p class="mb-1"><i class="fas fa-file-alt me-2"></i>Resume: 
                                            <?php if (!empty($job['resume_file'])): ?>
                                                <a href="javascript:void(0);" onclick="viewResume('<?php echo htmlspecialchars($job['resume_file']); ?>')" class="text-info text-decoration-none me-2">
                                                    <i class="fas fa-eye me-1"></i> View Resume
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">No resume uploaded</span>
                                            <?php endif; ?>
                                        </p>
                                        <p class="mb-1"><i class="fas fa-calendar-check me-2"></i>Status Updated: <?php echo date('M d, Y', strtotime($job['status_updated_at'])); ?></p>
                                    </div>

                                    <!-- View Details Button -->
                                    <div class="job-actions mt-3">
                                        <a href="job.php?id=<?php echo $job['job_id']; ?>" class="btn btn-outline-custom btn-sm rounded-pill">
                                            View Job Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-muted"><?php echo $isAdmin ? "No job applications available." : ($isEmployer ? "You have no applicants for your job posted by this user." : "You have not applied for any jobs yet."); ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>





<!-- Documents Tab -->
<div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
        <div class="profile-cardop-4 mb-4">
        <h4 class="text-center mb-4 mt-4">My Documents</h4>

<!-- Certificates Section -->
<div class="profile-card p-4 mb-4 fade-in" style="border-radius: 15px;">
    <!-- Section Header with Toggle Icon on the Right -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="section-title resume-section text-primary" style="font-family: 'Roboto', sans-serif; font-weight: bold; color: #333;">
            Licences & Certifications
        </h3>
        <button id="toggle-certificates-section" class="btn btn-link text-secondary p-0" style="font-size: 1.5rem; background: none; border: none;">
            <i class="fas fa-chevron-up"></i> <!-- Initially showing the up arrow because the section is visible -->
        </button>
    </div>

    <div id="certificates-section" class="<?php echo empty($certificates) ? 'striped-border' : ''; ?>" style="padding: 20px; border-radius: 10px;">
    <?php if (!empty($certificates)): ?>
        <?php foreach ($certificates as $certificate): ?>
            <div class="certificate-item mb-4" style="background-color: #fff; border-radius: 12px; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08); padding: 20px; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                <!-- Header with Certificate Name -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted" style="font-size: 1.1rem; font-weight: 600; color: #444; position: relative; display: inline-block;">
                        <?php echo ucfirst(htmlspecialchars($certificate['certificate_name'])); ?>
                        <span style="position: absolute; bottom: -4px; left: 0; width: 100%; height: 2px; background: linear-gradient(90deg, #007bff, #6f42c1); border-radius: 2px;"></span>
                    </span>

                    <?php if ($_SESSION['user_id'] == $certificate['user_id']): ?>
                        <!-- Edit and Delete Buttons -->
                        <div class="d-flex">
                            <a href="#" class="btn btn-sm btn-download me-2" 
                               onclick="openEditCertificateModal(<?php echo $certificate['id']; ?>, '<?php echo addslashes($certificate['certificate_name']); ?>', '<?php echo addslashes($certificate['issuing_organization']); ?>', '<?php echo $certificate['issue_date']; ?>', '<?php echo addslashes($certificate['certificate_file']); ?>')">
                                <i class="fas fa-edit"></i> <!-- Edit Icon -->
                            </a>

                            <a href="#" class="btn btn-sm btn-remove" onclick="openDeleteCertificateModal(<?php echo $certificate['id']; ?>)">
                                <i class="fas fa-times"></i> <!-- Delete Icon -->
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Certificate Details -->
                <div class="mb-3">
                    <p><strong><i class="fas fa-university me-2" style="color: #17A2B8;"></i>Issued By:</strong> 
                        <?php echo htmlspecialchars($certificate['issuing_organization']); ?>
                    </p>
                </div>

                <div class="mb-3">
                    <p><strong><i class="fas fa-calendar-alt me-2" style="color: #28A745;"></i>Issue Date:</strong> 
                        <?php echo date('M d, Y', strtotime($certificate['issue_date'])); ?>
                    </p>
                </div>

                <!-- View Certificate Button -->
                <?php if (!empty($certificate['certificate_file'])): ?>
                    <div class="mb-3">
                        <a href="<?php echo htmlspecialchars($certificate['certificate_file']); ?>" target="_blank" class="btn btn-sm btn-download-resume">
                            <i class="fas fa-eye me-2"></i> View Certificate
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <!-- Add Certificate Button -->
        <?php if ($_SESSION['user_id'] == $user_id): ?>
            <div class="mt-4">
                <button class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addCertificateModal" >
                    <i class="fas fa-plus me-2"></i> Add Licence or Certification
                </button>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <!-- No Certificates Added Yet -->
        <div class=" py-4" >
            <p class="mb-0" style="color: #666;">No certificates added yet.</p>
            <?php if ($_SESSION['user_id'] == $user_id): ?>
                <div class="mt-3">
                    <button class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addCertificateModal" >
                        <i class="fas fa-plus me-2"></i> Add Licence or Certification
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</div>


<!-- Resume Section -->
<div class="profile-card p-4 mb-4 fade-in">
    <h3 class="section-title resume-section">Resume</h3>
    <p style="color: #666; font-style: italic;" class="<?php echo empty($user['resume_file']) ? 'striped-border' : ''; ?>">
        <?php if (!empty($user['resume_file'])): ?>
            <!-- Download Resume Button -->
            <a href="<?php echo htmlspecialchars($user['resume_file']); ?>" 
                class="btn btn-download-resume me-2" 
                download>
                    <i class="fas fa-download"></i> Download Resume
            </a>

            <!-- View Resume Button -->
            <button onclick="viewResume('<?php echo htmlspecialchars($user['resume_file']); ?>')" class="btn btn-view-resume me-2">
                <i class="fas fa-eye"></i> View Resume
            </button>

            <!-- Remove Resume Button -->
            <?php if ($isOwnProfile): ?>
                <button id="remove-resume-button" class="btn btn-remove-resume" data-user-id="<?php echo $user_id; ?>">
                    <i class="fas fa-trash"></i> Remove Resume
                </button>
            <?php endif; ?>
        <?php else: ?>
            No resume uploaded yet.
        <?php endif; ?>
    </p>
    
    <?php if ($isOwnProfile): ?>
        <form action="profile.php?id=<?php echo $user_id; ?>" method="POST" enctype="multipart/form-data" class="mt-3">
    <label for="resume" class="form-label fw-bold">Upload/Replace Resume</label>
    <input type="file" name="resume" id="resume" class="form-control rounded-pill my-3" accept=".pdf">

    <small class="form-text text-muted">Only PDF files are allowed. Convert your resume into PDF.</small><br><br> <!-- Added note here -->

    <!-- Upload/Replace Resume Button -->
    <button type="submit" class="btn btn-upload-resume me-2">
        <i class="fas fa-upload"></i> Upload/Replace Resume
    </button>

    <!-- Create Resume Button -->
    <a href="resume.php" class="btn btn-create-resume">
        <i class="fas fa-file-alt"></i> Create Resume
    </a>
    <a href="/JOB/forms/forms.php" class="btn btn-create-resume">
        <i class="fas fa-file-alt"></i> Create Application form
    </a>
</form>

    <?php endif; ?>
</div>
        </div>


    </div>
</div>




<!-- Edit Profile Button 
<?php if ($isOwnProfile): ?>
    <div id="edit-profile-button" style="margin-bottom: 30px;" class="text-center fade-in">
        <a href="browse.php" class="btn btn-custom rounded-pill px-4">
            <i class="fas fa-edit"></i> Check-out Latest Jobs
        </a>
    </div> 
 <?php endif; ?> -->

                

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
                <form action="update_user_name.php" method="POST">
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
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPersonalInfoModalLabel">Edit Personal Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="update_personal_info.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <!-- Set the email field to readonly so it's not editable -->
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly required>
                    </div>
                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="Male" <?php echo ($user['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($user['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Non-Binary" <?php echo ($user['gender'] == 'Non-Binary') ? 'selected' : ''; ?>>Non-Binary</option>
                            <option value="LGBTQ+" <?php echo ($user['gender'] == 'LGBTQ+') ? 'selected' : ''; ?>>LGBTQ+</option>
                            <option value="Other" <?php echo ($user['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
    <label for="birth_date" class="form-label">Birth Date</label>
    <input type="date" class="form-control" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($user['birth_date']); ?>" required>
    <!-- Add a span to display the calculated age -->
    <small id="ageDisplay" class="form-text text-muted"></small>
</div>

                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="civil_status" class="form-label">Civil Status</label>
                        <select class="form-select" id="civil_status" name="civil_status" required>
                            <option value="Single" <?php echo ($user['civil_status'] == 'Single') ? 'selected' : ''; ?>>Single</option>
                            <option value="Married" <?php echo ($user['civil_status'] == 'Married') ? 'selected' : ''; ?>>Married</option>
                            <option value="Divorced" <?php echo ($user['civil_status'] == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                            <option value="Widowed" <?php echo ($user['civil_status'] == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="street_address" class="form-label">Street Address</label>
                        <input type="text" class="form-control" id="street_address" name="street_address" value="<?php echo htmlspecialchars($user['street_address']); ?>" required>
                    </div>
                    <div class="mb-3">
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

                    <div class="mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="zip_code" class="form-label">Zip Code</label>
                        <input type="text" class="form-control" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($user['zip_code']); ?>" required>
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

<!-- Add Education Modal -->
<div class="modal fade" id="addEducationModal" tabindex="-1" aria-labelledby="addEducationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEducationModalLabel">Add Education</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <p class="text-muted mb-3 small">Please note that you don't need to fill out every education level. If you're adding just your college education, you can skip the primary and secondary education fields. Only fill out the relevant fields based on your selected education level.</p>


                <form method="POST" action="process_education.php">
                    <!-- Dropdown to choose Education Level (Primary, Secondary, College, Graduate, Vocational) -->
                    <div class="mb-3">
                        <label for="education_level" class="form-label fw-semibold">Education Level</label>
                        <select class="form-control" id="education_level" name="education_level">
                            <option value="primary">Primary</option>
                            <option value="secondary">Secondary</option>
                            <option value="college">College</option>
                            <option value="graduate">Graduate School (Masters, PhD, etc.)</option>
                            <option value="vocational">Vocational/Technical Education</option>
                        </select>
                    </div>

                    <!-- Course field (only for College) -->
                    <div class="mb-3" id="course_group" style="display:none;">
                        <label for="course" class="form-label fw-semibold">Course</label>
                        <input type="text" class="form-control" id="course" name="course" placeholder="Course">
                    </div>

                    <!-- Vocational Course (only for Vocational Education) -->
                    <div class="mb-3" id="vocational_group" style="display:none;">
                        <label for="vocational_course" class="form-label fw-semibold">Vocational Course</label>
                        <input type="text" class="form-control" id="vocational_course" name="vocational_course" placeholder="Vocational Course Name">
                    </div>

                    <!-- Graduate School Thesis (only for Graduate School) -->
                    <div class="mb-3" id="graduate_group" style="display:none;">
                        <label for="thesis_title" class="form-label fw-semibold">Thesis/Dissertation Title (If Applicable)</label>
                        <input type="text" class="form-control" id="thesis_title" name="thesis_title" placeholder="Thesis/Dissertation Title">
                    </div>

                    <!-- Institution -->
                    <div class="mb-3">
                        <label for="institution" class="form-label fw-semibold">Institution</label>
                        <input type="text" class="form-control" id="institution" name="institution" placeholder="Name of School">
                    </div>

                    <!-- Status (Completed or Not Completed) -->
                    <div class="mb-3">
                        <label for="status" class="form-label fw-semibold">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="Completed">Completed</option>
                            <option value="Not Completed">Not Completed</option>
                        </select>
                    </div>

                    <!-- Completion Year or Expected Completion -->
                    <div class="mb-3" id="completion_year_group">
                        <label for="completion_year" class="form-label fw-semibold">Completion Year</label>
                        <input type="number" class="form-control" id="completion_year" name="completion_year" placeholder="Year">
                    </div>

                    <div class="mb-3" id="expected_completion_group" style="display:none;">
                        <label for="expected_completion_date" class="form-label fw-semibold">Expected Completion Date</label>
                        <input type="date" class="form-control" id="expected_completion_date" name="expected_completion_date">
                    </div>

                    <!-- Course Highlights (only for College) -->
                    <div class="mb-3" id="course_highlights_group" style="display:none;">
                        <label for="course_highlights" class="form-label fw-semibold">Course Highlights</label>
                        <textarea class="form-control" id="course_highlights" name="course_highlights" rows="3" placeholder="Add activities, projects, awards or achievements during your study."></textarea>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Education</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>




<!-- Edit Education Modal -->
<div class="modal fade" id="editEducationModal" tabindex="-1" aria-labelledby="editEducationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEducationModalLabel">Edit Education</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <p class="text-muted mb-3 small">The education level is fixed and cannot be updated. Please edit the other details below. 
            If you want to change the education level, you will need to delete the existing entry and add a new one.</p>



                <form method="POST" action="process_education.php">
                    <!-- Hidden field to store education ID -->
                    <input type="hidden" id="edit_education_id" name="education_id">

                    <!-- Education Level (Read-Only) -->
                    <div class="mb-3">
                        <label for="edit_education_level" class="form-label fw-semibold">Education Level</label>
                        <input type="text" class="form-control" id="edit_education_level" name="education_level" readonly>
                    </div>

                    <!-- Course field (only for College) -->
                    <div class="mb-3" id="edit_course_group" style="display:none;">
                        <label for="edit_course" class="form-label fw-semibold">Course</label>
                        <input type="text" class="form-control" id="edit_course" name="course" placeholder="Course">
                    </div>

                    <!-- Vocational Course (only for Vocational Education) -->
                    <div class="mb-3" id="edit_vocational_group" style="display:none;">
                        <label for="edit_vocational_course" class="form-label fw-semibold">Vocational Course</label>
                        <input type="text" class="form-control" id="edit_vocational_course" name="vocational_course" placeholder="Vocational Course Name">
                    </div>

                    <!-- Graduate School Thesis (only for Graduate School) -->
                    <div class="mb-3" id="edit_graduate_group" style="display:none;">
                        <label for="edit_thesis_title" class="form-label fw-semibold">Thesis/Dissertation Title (If Applicable)</label>
                        <input type="text" class="form-control" id="edit_thesis_title" name="thesis_title" placeholder="Thesis/Dissertation Title">
                    </div>

                    <!-- Institution -->
                    <div class="mb-3">
                        <label for="edit_institution" class="form-label fw-semibold">Institution</label>
                        <input type="text" class="form-control" id="edit_institution" name="institution" placeholder="Name of School">
                    </div>

                    <!-- Status (Completed or Not Completed) -->
                    <div class="mb-3">
                        <label for="edit_status" class="form-label fw-semibold">Status</label>
                        <select class="form-control" id="edit_status" name="status">
                            <option value="Completed">Completed</option>
                            <option value="Not Completed">Not Completed</option>
                        </select>
                    </div>

                    <!-- Completion Year or Expected Completion -->
                    <div class="mb-3" id="edit_completion_year_group">
                        <label for="edit_completion_year" class="form-label fw-semibold">Completion Year</label>
                        <input type="number" class="form-control" id="edit_completion_year" name="completion_year" placeholder="Year">
                    </div>

                    <div class="mb-3" id="edit_expected_completion_group" style="display:none;">
                        <label for="edit_expected_completion_date" class="form-label fw-semibold">Expected Completion Date</label>
                        <input type="date" class="form-control" id="edit_expected_completion_date" name="expected_completion_date">
                    </div>

                    <!-- Course Highlights (only for College) -->
                    <div class="mb-3" id="edit_course_highlights_group" style="display:none;">
                        <label for="edit_course_highlights" class="form-label fw-semibold">Course Highlights</label>
                        <textarea class="form-control" id="edit_course_highlights" name="course_highlights" rows="3" placeholder="Add activities, projects, awards or achievements during your study."></textarea>
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



<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this education record? This action cannot be undone.
            </div>
            <div class="modal-footer">
            <a href="#" id="confirmDeleteButton" class="btn btn-primary">Remove</a>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                
            </div>
        </div>
    </div>
</div>

<!-- Add Work Experience Modal -->
<div class="modal fade" id="addWorkExperienceModal" tabindex="-1" aria-labelledby="addWorkExperienceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addWorkExperienceModalLabel">Add Work Experience</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="process_work_experience.php">
                    <!-- Job Title -->
                    <div class="mb-3">
                        <label for="job_title" class="form-label fw-semibold">Job Title</label>
                        <input type="text" class="form-control" id="job_title" name="job_title" placeholder="Job Title" required>
                    </div>

                    <!-- Company Name -->
                    <div class="mb-3">
                        <label for="company_name" class="form-label fw-semibold">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Company Name" required>
                    </div>



                    <!-- Job Description -->
                    <div class="mb-3">
                        <label for="job_description" class="form-label fw-semibold">Job Description</label>
                        <textarea class="form-control" id="job_description" name="job_description" rows="3" placeholder="Job Description" required></textarea>
                    </div>

                    <!-- Employment Type -->
                    <div class="mb-3">
                        <label for="employment_type" class="form-label fw-semibold">Employment Type</label>
                        <select class="form-control" id="employment_type" name="employment_type" required>
                            <option value="fulltime">Full-Time</option>
                            <option value="parttime">Part-Time</option>
                            <option value="self-employed">Self-Employed</option>
                            <option value="freelance">Freelance</option>
                            <option value="contract">Contract</option>
                            <option value="internship">Internship</option>
                            <option value="apprenticeship">Apprenticeship</option>
                            <option value="seasonal">Seasonal</option>
                            <option value="home-based">Home-Based</option>
                            <option value="domestic">Domestic</option>
                            <option value="temporary">Temporary</option>
                            <option value="volunteer">Volunteer</option>
                        </select>
                    </div>

                    <!-- Local or Overseas -->
                    <div class="mb-3">
                        <label for="job_location" class="form-label fw-semibold">Work Location</label>
                        <select class="form-control" id="job_location" name="job_location" required>
                            <option value="local">Local</option>
                            <option value="overseas">Overseas</option>
                        </select>
                    </div>

                    <!-- Country (only show if overseas) -->
                    <div class="mb-3" id="country-div" style="display:none;">
                        <label for="country" class="form-label fw-semibold">Country</label>
                        <input type="text" class="form-control" id="country" name="country" placeholder="Country">
                    </div>

                    <!-- Work Type -->
                    <div class="mb-3">
                        <label for="work_type" class="form-label fw-semibold">Work Type</label>
                        <select class="form-control" id="work_type" name="work_type">
                            <option value="remote">Remote</option>
                            <option value="onsite">On-Site</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>

                                        <!-- Start Date -->
                                        <div class="mb-3">
                        <label for="start_date" class="form-label fw-semibold">Started</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>

                    <!-- End Date -->
                    <div class="mb-3">
                        <label for="end_date" class="form-label fw-semibold">End date (Optional)</label>
                        <input type="date" class="form-control" id="end_date" name="end_date">
                    </div>

                    <!-- Currently Working Checkbox -->
                    <div class="mb-3">
                        <input type="checkbox" id="currently_working" name="currently_working">
                        <label for="currently_working" class="form-label">Currently Working Here</label>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Work Experience</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Edit Work Experience Modal -->
<div class="modal fade" id="editWorkExperienceModal" tabindex="-1" aria-labelledby="editWorkExperienceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editWorkExperienceModalLabel">Edit Work Experience</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="process_work_experience.php">
                    <input type="hidden" id="edit_work_experience_id" name="work_experience_id">

                    <!-- Job Title -->
                    <div class="mb-3">
                        <label for="edit_job_title" class="form-label fw-semibold">Job Title</label>
                        <input type="text" class="form-control" id="edit_job_title" name="job_title" required>
                    </div>

                    <!-- Company Name -->
                    <div class="mb-3">
                        <label for="edit_company_name" class="form-label fw-semibold">Company Name</label>
                        <input type="text" class="form-control" id="edit_company_name" name="company_name" required>
                    </div>

                                        <!-- Job Description -->
                                        <div class="mb-3">
                        <label for="edit_job_description" class="form-label fw-semibold">Job Description</label>
                        <textarea class="form-control" id="edit_job_description" name="job_description" rows="3" required></textarea>
                    </div>


                    <!-- Employment Type -->
                    <div class="mb-3">
                    <label for="edit_employment_type" class="form-label fw-semibold">Employment Type</label>
                        <select class="form-control" id="edit_employment_type" name="employment_type" required>
                            <option value="fulltime">Full-Time</option>
                            <option value="parttime">Part-Time</option>
                            <option value="self-employed">Self-Employed</option>
                            <option value="freelance">Freelance</option>
                            <option value="contract">Contract</option>
                            <option value="internship">Internship</option>
                            <option value="apprenticeship">Apprenticeship</option>
                            <option value="seasonal">Seasonal</option>
                            <option value="home-based">Home-Based</option>
                            <option value="domestic">Domestic</option>
                            <option value="temporary">Temporary</option>
                            <option value="volunteer">Volunteer</option>
                        </select>
                    </div>



                    <!-- Job Location -->
                    <div class="mb-3">
                        <label for="edit_job_location" class="form-label fw-semibold">Work Location</label>
                        <select class="form-control" id="edit_job_location" name="job_location" required>
                            <option value="local">Local</option>
                            <option value="overseas">Overseas</option>
                        </select>
                    </div>

                    <!-- Country (only show if overseas) -->
                    <div class="mb-3" id="edit_country_div" style="display:none;">
                        <label for="edit_country" class="form-label fw-semibold">Country</label>
                        <input type="text" class="form-control" id="edit_country" name="country" placeholder="Country">
                    </div>

                                                            <!-- Work Type -->
                                                            <div class="mb-3">
                        <label for="edit_work_type" class="form-label fw-semibold">Work Type</label>
                        <select class="form-control" id="edit_work_type" name="work_type">
                            <option value="remote">Remote</option>
                            <option value="onsite">On-Site</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>

                    <!-- Start Date -->
                    <div class="mb-3">
                        <label for="edit_start_date" class="form-label fw-semibold">Started</label>
                        <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                    </div>

                    <!-- End Date -->
                    <div class="mb-3">
                        <label for="edit_end_date" class="form-label fw-semibold">End date (Optional)</label>
                        <input type="date" class="form-control" id="edit_end_date" name="end_date">
                    </div>



                    <!-- Currently Working Checkbox -->
                    <div class="mb-3">
                        <input type="checkbox" id="edit_currently_working" name="currently_working">
                        <label for="edit_currently_working" class="form-label">Currently Working Here</label>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Delete Confirmation Modal for Work Experience -->
<div class="modal fade" id="deleteWorkExperienceModal" tabindex="-1" aria-labelledby="deleteWorkExperienceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteWorkExperienceModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this work experience record? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <a href="#" id="confirmDeleteWorkExperienceButton" class="btn btn-primary">Remove</a>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>


<!-- Add Skill Modal -->
<div class="modal fade" id="addSkillModal" tabindex="-1" aria-labelledby="addSkillModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSkillModalLabel">Add Skill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Search Bar -->
                <div class="mb-3">
                    <label for="skillSearch" class="form-label">Search for a skill:</label>
                    <input type="text" class="form-control" id="skillSearch" placeholder="Type a keyword (e.g. Arts, Marketing ..) " oninput="filterSkills()">
                    <div id="skillResults" class="mt-2"></div> <!-- Dropdown for search results -->
                </div>

                <!-- Proficiency Level (Optional) -->
                <div class="mb-3">
                    <label for="proficiencyLevel" class="form-label">Proficiency Level:</label>
                    <select class="form-select" id="proficiencyLevel">
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Advanced">Advanced</option>
                        <option value="Expert">Expert</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="addSkill()">Add Skill</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                
            </div>
        </div>
    </div>
</div>

<!-- Delete Skill Modal -->
<div class="modal fade" id="deleteSkillModal" tabindex="-1" aria-labelledby="deleteSkillModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSkillModalLabel">Delete Skill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this skill?</p>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="confirmDeleteSkillBtn">Remove</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                
            </div>
        </div>
    </div>
</div>


<!-- Add Language Modal -->
<div class="modal fade" id="addLanguagesModal" tabindex="-1" aria-labelledby="addLanguagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLanguagesModalLabel">Add Language</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Language Dropdown -->
                <div class="mb-3">
                    <label for="language" class="form-label">Select Language:</label>
                    <select class="form-select" id="language" name="language">
                        <option value="">Select a language</option>
                        <?php
                        // Fetch all languages from languages_list
                        $query = "SELECT * FROM languages_list ORDER BY language_name";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()):
                        ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['language_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Fluency Level Dropdown -->
                <div class="mb-3">
                    <label for="fluency" class="form-label">Fluency Level:</label>
                    <select class="form-select" id="fluency" name="fluency">
                        <option value="Basic">Basic</option>
                        <option value="Conversational">Conversational</option>
                        <option value="Fluent">Fluent</option>
                        <option value="Native">Native</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="addLanguage()">Add Language</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                
            </div>
        </div>
    </div>
</div>


<!-- Delete Language Modal -->
<div class="modal fade" id="deleteLanguageModal" tabindex="-1" aria-labelledby="deleteLanguageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteLanguageModalLabel">Delete Language</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this language?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="confirmDeleteLanguageBtn">Remove</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal for Editing LinkedIn Profile -->
<?php if ($_SESSION['user_id'] == $user['id']): ?>
    <div class="modal fade" id="editLinkedInModal" tabindex="-1" aria-labelledby="editLinkedInModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editLinkedInModalLabel">Edit LinkedIn Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="links.php" method="POST">
                        <div class="mb-3">
                            <label for="linkedin_profile" class="form-label">LinkedIn Profile URL</label>
                            <input type="url" class="form-control" id="linkedin_profile" name="linkedin_profile" value="<?php echo htmlspecialchars($user['linkedin_profile']); ?>" placeholder="https://linkedin.com/in/your-profile">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Modal for Editing Portfolio URL -->
<?php if ($_SESSION['user_id'] == $user['id']): ?>
    <div class="modal fade" id="editPortfolioModal" tabindex="-1" aria-labelledby="editPortfolioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPortfolioModalLabel">Edit Portfolio URL</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="links.php" method="POST">
                        <div class="mb-3">
                            <label for="portfolio_url" class="form-label">Portfolio URL</label>
                            <input type="url" class="form-control" id="portfolio_url" name="portfolio_url" 
                                   value="<?php echo isset($user['portfolio_url']) ? htmlspecialchars($user['portfolio_url']) : ''; ?>" 
                                   placeholder="https://your-portfolio.com">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Add Achievement Modal (with file upload option) -->
<?php if ($_SESSION['user_id'] == $user['id']): ?>
    <div class="modal fade" id="addAchievementModal" tabindex="-1" aria-labelledby="addAchievementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAchievementModalLabel">Add Achievement or Award</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="process_achievement.php" enctype="multipart/form-data">
                    <!-- Award Name -->
                    <div class="mb-3">
                        <label for="award_name" class="form-label fw-semibold">Award Name</label>
                        <input type="text" class="form-control" id="award_name" name="award_name" placeholder="Name of the Award" required>
                    </div>

                    <!-- Awarding Organization -->
                    <div class="mb-3">
                        <label for="organization" class="form-label fw-semibold">Awarding Organization</label>
                        <input type="text" class="form-control" id="organization" name="organization" placeholder="Name of the Organization" required>
                    </div>

                    <!-- Award Date -->
                    <div class="mb-3">
                        <label for="award_date" class="form-label fw-semibold">Award Date</label>
                        <input type="date" class="form-control" id="award_date" name="award_date" required>
                    </div>

                    <!-- Proof File (Optional) -->
                    <div class="mb-3">
                        <label for="proof_file" class="form-label fw-semibold">Attach Proof (Optional)</label>
                        <input type="file" class="form-control" id="proof_file" name="proof_file" accept=".jpg,.jpeg,.png,.gif,.pdf,">
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Achievement</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- EDIT Achievement Modal (with file upload option) -->
<div class="modal fade" id="editAchievementModal" tabindex="-1" aria-labelledby="editAchievementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAchievementModalLabel">Edit Achievement or Award</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="update_achievement.php" enctype="multipart/form-data">
                    <!-- Achievement ID (Hidden) -->
                    <input type="hidden" name="achievement_id" id="edit_achievement_id">

                    <!-- Award Name -->
                    <div class="mb-3">
                        <label for="edit_award_name" class="form-label fw-semibold">Award Name</label>
                        <input type="text" class="form-control" id="edit_award_name" name="award_name" placeholder="Name of the Award" required>
                    </div>

                    <!-- Awarding Organization -->
                    <div class="mb-3">
                        <label for="edit_organization" class="form-label fw-semibold">Awarding Organization</label>
                        <input type="text" class="form-control" id="edit_organization" name="organization" placeholder="Name of the Organization" required>
                    </div>

                    <!-- Award Date -->
                    <div class="mb-3">
                        <label for="edit_award_date" class="form-label fw-semibold">Award Date</label>
                        <input type="date" class="form-control" id="edit_award_date" name="award_date" required>
                    </div>

                    <!-- Proof File (Optional) -->
                    <div class="mb-3">
                        <label for="edit_proof_file" class="form-label fw-semibold">Attach Proof (Optional)</label>
                        <input type="file" class="form-control" id="edit_proof_file" name="proof_file" accept=".jpg,.jpeg,.png,.gif,.pdf,">
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Achievement</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>













<!-- Delete Achievement Modal -->
<div class="modal fade" id="deleteAchievementModal" tabindex="-1" aria-labelledby="deleteAchievementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAchievementModalLabel">Delete Achievement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this achievement?</p>
            </div>
            <div class="modal-footer">
                <form id="deleteAchievementForm" method="POST" action="process_delete_achievement.php">
                    <input type="hidden" name="achievement_id" id="delete_achievement_id">
                    <button type="submit" class="btn btn-primary">Delete</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Add Certificate Modal -->
<?php if ($_SESSION['user_id'] == $user['id']): ?>
    <div class="modal fade" id="addCertificateModal" tabindex="-1" aria-labelledby="addCertificateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCertificateModalLabel">Add Certificate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="process_certificate.php" enctype="multipart/form-data">
                        <!-- Certificate Name -->
                        <div class="mb-3">
                            <label for="certificate_name" class="form-label fw-semibold">Certificate Name</label>
                            <input type="text" class="form-control" id="certificate_name" name="certificate_name" placeholder="Name of the Certificate" required>
                        </div>

                        <!-- Issuing Organization -->
                        <div class="mb-3">
                            <label for="issuing_organization" class="form-label fw-semibold">Issuing Organization</label>
                            <input type="text" class="form-control" id="issuing_organization" name="issuing_organization" placeholder="Organization Name" required>
                        </div>

                        <!-- Issue Date -->
                        <div class="mb-3">
                            <label for="issue_date" class="form-label fw-semibold">Issue Date</label>
                            <input type="date" class="form-control" id="issue_date" name="issue_date" required>
                        </div>

                        <!-- Certificate File (Optional) -->
                        <div class="mb-3">
                            <label for="certificate_file" class="form-label fw-semibold">Attach Certificate (Optional)</label>
                            <input type="file" class="form-control" id="certificate_file" name="certificate_file" accept=".jpg,.jpeg,.png,.gif,.pdf,.docx">
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Certificate</button>
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<!-- Edit Certificate Modal -->
<div class="modal fade" id="editCertificateModal" tabindex="-1" aria-labelledby="editCertificateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCertificateModalLabel">Edit Certificate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="update_certificate.php" enctype="multipart/form-data">
                    <input type="hidden" name="certificate_id" id="certificate_id">

                    <!-- Certificate Name -->
                    <div class="mb-3">
                        <label for="certificate_name_edit" class="form-label fw-semibold">Certificate Name</label>
                        <input type="text" class="form-control" id="certificate_name_edit" name="certificate_name" placeholder="Name of the Certificate" required>
                    </div>

                    <!-- Issuing Organization -->
                    <div class="mb-3">
                        <label for="issuing_organization_edit" class="form-label fw-semibold">Issuing Organization</label>
                        <input type="text" class="form-control" id="issuing_organization_edit" name="issuing_organization" placeholder="Organization Name" required>
                    </div>

                    <!-- Issue Date -->
                    <div class="mb-3">
                        <label for="issue_date_edit" class="form-label fw-semibold">Issue Date</label>
                        <input type="date" class="form-control" id="issue_date_edit" name="issue_date" required>
                    </div>

                    <!-- Certificate File (Optional) -->
                    <div class="mb-3">
                        <label for="certificate_file_edit" class="form-label fw-semibold">Attach New Certificate (Optional)</label>
                        <input type="file" class="form-control" id="certificate_file_edit" name="certificate_file" accept=".jpg,.jpeg,.png,.gif,.pdf,.docx">
                    </div>

                    <!-- Link to View Existing Certificate (if exists) -->
                    <div id="file-link-container" class="mb-3"></div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Certificate</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<?php endif; ?>

<!-- Delete Certificate Modal -->
<div class="modal fade" id="deleteCertificateModal" tabindex="-1" aria-labelledby="deleteCertificateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCertificateModalLabel">Delete Certificate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this certificate?</p>
            </div>
            <div class="modal-footer">
                <form id="deleteCertificateForm" method="POST" action="process_delete_certificate.php">
                    <input type="hidden" name="certificate_id" id="delete_certificate_id">
                    <button type="submit" class="btn btn-primary">Delete</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>


<?php endif; ?>


<!-- Add Reference Modal -->
<?php if ($_SESSION['user_id'] == $user['id']): ?>
    <div class="modal fade" id="addReferenceModal" tabindex="-1" aria-labelledby="addReferenceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addReferenceModalLabel">Add Reference</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="process_references.php">
                        <div class="mb-3">
                            <label for="reference_name" class="form-label fw-semibold">Reference Name</label>
                            <input type="text" class="form-control" id="reference_name" name="reference_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="position" class="form-label fw-semibold">Position</label>
                            <input type="text" class="form-control" id="position" name="position" required>
                        </div>
                        <div class="mb-3">
                            <label for="workplace" class="form-label fw-semibold">Workplace</label>
                            <input type="text" class="form-control" id="workplace" name="workplace" required>
                        </div>
                        <div class="mb-3">
                            <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
                            <input type="text" class="form-control" id="contact_number" name="contact_number" required>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Reference</button>
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Reference Modal -->
<div class="modal fade" id="editReferenceModal" tabindex="-1" aria-labelledby="editReferenceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editReferenceModalLabel">Edit Reference</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="update_reference.php">
                    <input type="hidden" name="reference_id" id="reference_id">

                    <!-- Reference Name -->
                    <div class="mb-3">
                        <label for="reference_name_edit" class="form-label fw-semibold">Reference Name</label>
                        <input type="text" class="form-control" id="reference_name_edit" name="reference_name" required>
                    </div>

                    <!-- Position -->
                    <div class="mb-3">
                        <label for="position_edit" class="form-label fw-semibold">Position</label>
                        <input type="text" class="form-control" id="position_edit" name="position" required>
                    </div>

                    <!-- Workplace -->
                    <div class="mb-3">
                        <label for="workplace_edit" class="form-label fw-semibold">Workplace</label>
                        <input type="text" class="form-control" id="workplace_edit" name="workplace" required>
                    </div>

                    <!-- Contact Number -->
                    <div class="mb-3">
                        <label for="contact_number_edit" class="form-label fw-semibold">Contact Number</label>
                        <input type="text" class="form-control" id="contact_number_edit" name="contact_number" required>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Reference</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>




<!-- Delete Reference Modal -->
<div class="modal fade" id="deleteReferenceModal" tabindex="-1" aria-labelledby="deleteReferenceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteReferenceModalLabel">Delete Reference</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this reference?</p>
            </div>
            <div class="modal-footer">
                <form id="deleteReferenceForm" method="POST" action="process_delete_reference.php">
                    <input type="hidden" name="reference_id" id="delete_reference_id">
                    <button type="submit" class="btn btn-primary">Delete</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Add Job Preferences Modal -->
<div class="modal fade" id="addJobPreferencesModal" tabindex="-1" aria-labelledby="addJobPreferencesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addJobPreferencesModalLabel">Add Job Preferences</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="process_job_preferences.php">
                    <!-- Work Type -->
                    <div class="mb-3">
                        <label for="work_type" class="form-label fw-semibold">Work Type</label>
                        <select class="form-control" id="work_type" name="work_type" required>
                            <option value="onsite">On-Site</option>
                            <option value="remote">Remote</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>

                    <!-- Job Location -->
                    <div class="mb-3">
                        <label for="job_location" class="form-label fw-semibold">Job Location</label>
                        <select class="form-control" id="job_location" name="job_location" required>
                            <option value="local">Local</option>
                            <option value="overseas">Overseas</option>
                        </select>
                    </div>

                    <!-- Employment Type -->
                    <div class="mb-3">
                        <label for="employment_type" class="form-label fw-semibold">Employment Type</label>
                        <select class="form-control" id="employment_type" name="employment_type" required>
                            <option value="fulltime">Full-Time</option>
                            <option value="parttime">Part-Time</option>
                            <option value="self-employed">Self-Employed</option>
                            <option value="freelance">Freelance</option>
                            <option value="contract">Contract</option>
                            <option value="internship">Internship</option>
                            <option value="apprenticeship">Apprenticeship</option>
                            <option value="seasonal">Seasonal</option>
                            <option value="home-based">Home-Based</option>
                            <option value="domestic">Domestic</option>
                            <option value="temporary">Temporary</option>
                            <option value="volunteer">Volunteer</option>
                        </select>
                    </div>

                    <!-- Preferred Positions -->
                    <div class="mb-3">
                        <label for="preferred_positions" class="form-label fw-semibold">Preferred Positions</label>
                        <select class="form-control" id="preferred_positions" name="preferred_positions[]" multiple>
                            <?php
                                // Fetching available job positions from the database
                                $positions_query = "SELECT id, position_name FROM job_positions ORDER BY position_name ASC";
                                $positions_result = $conn->query($positions_query);
                                while ($position = $positions_result->fetch_assoc()) {
                                    echo '<option value="' . $position['id'] . '">' . htmlspecialchars($position['position_name']) . '</option>';
                                }
                            ?>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Preferences</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Edit Job Preferences Modal -->
<div class="modal fade" id="editJobPreferencesModal" tabindex="-1" aria-labelledby="editJobPreferencesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editJobPreferencesModalLabel">Edit Job Preferences</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="update_job_preferences.php">
                    <input type="hidden" id="edit_job_preference_id" name="job_preference_id">

                    <!-- Work Type -->
                    <div class="mb-3">
                        <label for="edit_work_type" class="form-label fw-semibold">Work Type</label>
                        <select class="form-control" id="edit_work_type" name="work_type" required>
                            <option value="onsite">On-Site</option>
                            <option value="remote">Remote</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>

                    <!-- Job Location -->
                    <div class="mb-3">
                        <label for="edit_job_location" class="form-label fw-semibold">Job Location</label>
                        <select class="form-control" id="edit_job_location" name="job_location" required>
                            <option value="local">Local</option>
                            <option value="overseas">Overseas</option>
                        </select>
                    </div>

                    <!-- Employment Type -->
                    <div class="mb-3">
                        <label for="edit_employment_type" class="form-label fw-semibold">Employment Type</label>
                        <select class="form-control" id="edit_employment_type" name="employment_type" required>
                            <option value="fulltime">Full-Time</option>
                            <option value="parttime">Part-Time</option>
                            <option value="self-employed">Self-Employed</option>
                            <option value="freelance">Freelance</option>
                            <option value="contract">Contract</option>
                            <option value="internship">Internship</option>
                            <option value="apprenticeship">Apprenticeship</option>
                            <option value="seasonal">Seasonal</option>
                            <option value="home-based">Home-Based</option>
                            <option value="domestic">Domestic</option>
                            <option value="temporary">Temporary</option>
                            <option value="volunteer">Volunteer</option>
                        </select>
                    </div>

                    <!-- Preferred Positions -->
                    <div class="mb-3">
                        <label for="edit_preferred_positions" class="form-label fw-semibold">Preferred Positions</label>
                        <select class="form-control" id="edit_preferred_positions" name="preferred_positions[]" multiple>
                            <?php
                                // Fetching available job positions from the database
                                $positions_query = "SELECT id, position_name FROM job_positions ORDER BY position_name ASC";
                                $positions_result = $conn->query($positions_query);

                                // Fetching user's current preferred positions (if any)
                                $user_preferred_positions_query = "
                                    SELECT jp_pos.position_id 
                                    FROM job_preferences jp
                                    JOIN job_preferences_positions jp_pos ON jp.id = jp_pos.job_preference_id
                                    WHERE jp.user_id = ? AND jp.id = ?
                                ";
                                $user_preferred_positions_stmt = $conn->prepare($user_preferred_positions_query);
                                $user_preferred_positions_stmt->bind_param("ii", $user_id, $job_preference_id);  // Assuming $user_id and $job_preference_id are available
                                $user_preferred_positions_stmt->execute();
                                $user_preferred_positions_result = $user_preferred_positions_stmt->get_result();
                                $user_preferred_positions = [];
                                while ($row = $user_preferred_positions_result->fetch_assoc()) {
                                    $user_preferred_positions[] = $row['position_id'];
                                }

                                // Display available positions and pre-select user's preferred ones
                                while ($position = $positions_result->fetch_assoc()) {
                                    $selected = in_array($position['id'], $user_preferred_positions) ? 'selected' : '';
                                    echo '<option value="' . $position['id'] . '" ' . $selected . '>' . htmlspecialchars($position['position_name']) . '</option>';
                                }
                            ?>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Preferences</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Delete Job Preferences Modal -->
<div class="modal fade" id="deleteJobPreferencesModal" tabindex="-1" aria-labelledby="deleteJobPreferencesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteJobPreferencesModalLabel">Delete Job Preferences</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete your job preferences? This action cannot be undone.</p>
                <form method="POST" action="process_delete_job_preferences.php">
                    <input type="hidden" id="delete_job_preference_id" name="job_preference_id">
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Delete</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>


<script src="https://unpkg.com/mammoth/mammoth.browser.min.js"></script>
<script src="/JOB/assets/script/profile_user.js"></script>

<script>

function removeCoverPhoto() {
    // Show the confirmation modal
    const confirmationModal = new bootstrap.Modal(document.getElementById('removeCoverPhotoModal'));
    confirmationModal.show();

    // REMOVE COVER PHOTO
    document.getElementById('confirmRemoveCoverPhotoBtn').addEventListener('click', function() {
        // Send AJAX request to remove the cover photo
        fetch('../pages/update_cover_photo.php', {
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
    fullSizedImage.src = fullSizedImage.src || '../uploads/<?= htmlspecialchars($user['cover_photo'] ?? '../uploads/default/COVER.jpg') ?>';

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
            fullSizedImage.src = '../uploads/<?= htmlspecialchars($user['cover_photo'] ?? '../uploads/default/COVER.jpg') ?>';
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






// SWEET ALERT MESSAGE
<?php if (isset($_SESSION['success_message'])): ?>
    Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?php echo $_SESSION['success_message']; ?>',
            showConfirmButton: true, // Show the confirm button
            confirmButtonText: 'Close', // Button text
            confirmButtonColor: '#3085d6', // Optional: Customize the button color
        }).then((result) => {
            if (result.isConfirmed) {
                // Optionally, you can redirect here if needed
                // window.location.href = 'profile.php'; // Uncomment if you want a redirect after clicking Okay
            }
        });

            // Clear the success message from the session
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>




        function openEditWorkModal(workExperience) {
        const modal = new bootstrap.Modal(document.getElementById('editWorkExperienceModal'));
        
        // Populate the fields with work experience data
        document.getElementById('edit_work_experience_id').value = workExperience.id;
        document.getElementById('edit_job_title').value = workExperience.job_title;
        document.getElementById('edit_company_name').value = workExperience.company_name;
        document.getElementById('edit_start_date').value = workExperience.start_date;
        document.getElementById('edit_end_date').value = workExperience.end_date;
        document.getElementById('edit_job_description').value = workExperience.job_description;
        document.getElementById('edit_currently_working').checked = workExperience.currently_working == 1;
        
        modal.show(); // Show the modal
    }

// Function to open the Delete Confirmation Modal for Work Experience
function openDeleteWorkExperienceModal(workExperienceId) {
    // Set the delete link with the work experience ID
    document.getElementById('confirmDeleteWorkExperienceButton').href = `delete_work_experience.php?id=${workExperienceId}`;

    // Open the modal using Bootstrap's modal API
    var myModal = new bootstrap.Modal(document.getElementById('deleteWorkExperienceModal'));
    myModal.show();
}

// Function to escape HTML entities
function escapeHtml(str) {
    return str.replace(/[&<>"'/]/g, function (char) {
        switch (char) {
            case '&': return '&amp;';
            case '<': return '&lt;';
            case '>': return '&gt;';
            case '"': return '&quot;';
            case "'": return '&#039;';
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




