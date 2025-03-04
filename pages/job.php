<?php
include '../includes/config.php';
include '../includes/header.php';

// Validate Job ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger text-center'>Invalid Job ID.</div>";
    exit();
}

$id = $_GET['id'];

// Fetch job details with categories, positions, specific location, and employer_id
$stmt = $conn->prepare("
    SELECT 
        j.id AS job_id,      -- Include job_id to identify the job
        j.title, 
        j.description, 
        j.responsibilities, 
        j.requirements, 
        j.preferred_qualifications, 
        j.specific_location,  
        GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') AS categories,  
        GROUP_CONCAT(DISTINCT p.position_name SEPARATOR ', ') AS positions,  
        j.location, 
        j.created_at, 
        j.photo, 
        j.thumbnail,
        j.employer_id -- Include employer_id to identify the employer who posted the job
    FROM jobs j
    LEFT JOIN job_categories jc ON j.id = jc.job_id
    LEFT JOIN categories c ON jc.category_id = c.id
    LEFT JOIN job_positions_jobs jp ON j.id = jp.job_id
    LEFT JOIN job_positions p ON jp.position_id = p.id
    WHERE j.id = ?
    GROUP BY j.id
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();

if (!$job) {
    echo "<div class='alert alert-danger text-center'>Job not found.</div>";
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;

// Count total applicants
$count_query = "SELECT COUNT(*) AS total_applicants FROM applications WHERE job_id = ?";
$stmt = $conn->prepare($count_query);
$stmt->bind_param("i", $id);
$stmt->execute();
$count_result = $stmt->get_result();
$count_data = $count_result->fetch_assoc();
$total_applicants = $count_data['total_applicants'] ?? 0;

// Check if the user has already applied
$user_applied = false;
if ($user_id) {
    $apply_check_query = "SELECT id, status FROM applications WHERE job_id = ? AND user_id = ?";
    $stmt = $conn->prepare($apply_check_query);
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $apply_result = $stmt->get_result();
    $apply_data = $apply_result->fetch_assoc();
    $user_applied = $apply_result->num_rows > 0;
    $application_status = $apply_data['status'] ?? null;
}

// Fetch user's resume file path
$resume_file = null;
$has_resume = false;
if ($user_id) {
    $resume_query = "SELECT resume_file FROM users WHERE id = ?";
    $stmt = $conn->prepare($resume_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $resume_result = $stmt->get_result();
    $user_data = $resume_result->fetch_assoc();
    $resume_file = $user_data['resume_file'];
    $has_resume = !empty($resume_file);
}

// Fetch the applicant's remark (if exists)
$remarkQuery = "SELECT remark FROM applications WHERE user_id = ? AND job_id = ?";
$stmt = $conn->prepare($remarkQuery);
$stmt->bind_param("ii", $user_id, $id);
$stmt->execute();
$remarkResult = $stmt->get_result();
$remark = $remarkResult->fetch_assoc();

// Fetch applied positions
$applied_positions = [];
if ($user_applied) {
    $applied_positions_query = "
        SELECT jp.position_name 
        FROM application_positions ap
        JOIN job_positions jp ON ap.position_id = jp.id
        JOIN applications a ON ap.application_id = a.id
        WHERE a.job_id = ? AND a.user_id = ?
    ";
    $stmt = $conn->prepare($applied_positions_query);
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $applied_positions[] = $row['position_name'];
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Description</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/JOB/assets/job.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center landscape-layout">
            <!-- Image Column -->
            <div class="col-md-6 image-column text-center">
                <?php if (!empty($job['photo']) && file_exists('../' . $job['photo'])): ?>
                    <img src="../<?= htmlspecialchars($job['photo']) ?>" alt="Job Image" class="img-fluid img-fluid-large rounded">
                <?php else: ?>
                    <div class="text-muted">No Image Available</div>
                <?php endif; ?>
            </div>

<!-- Details Column -->
<div class="col-md-6 details-column">
    <div class="card card-futuristic shadow-lg border-0 h-100">
        <div class="card-body p-5 scrollable-container">
            <!-- Job Title -->
            <h2 class="card-title text-center mb-4 text-futuristic"><?= htmlspecialchars($job['title']) ?></h2>

            <!-- Job Overview -->
            <div class="job-overview mb-4 text-center">
                <p class="text-muted">
                    <i class="fas fa-briefcase me-2"></i>
                    <strong>Category:</strong> <?= htmlspecialchars($job['categories'] ?? 'Not specified') ?>
                </p>
                <p class="text-muted">
                    <i class="fas fa-user-tie me-2"></i>
                    <strong>Position:</strong> <?= htmlspecialchars($job['positions'] ?? 'Not specified') ?>
                </p>
                <p class="text-muted">
        <i class="fas fa-map-marker-alt me-2"></i>
        <strong>Location:</strong> 
        <?php
        // Display Specific Location if available
        if (!empty($job['specific_location'])) {
            echo htmlspecialchars($job['specific_location']) . ', ';
        }
        // Display Barangay Location
        echo htmlspecialchars($job['location'] ?? 'Not specified');
        ?>
    </p>
                <p class="text-muted">
                    <i class="fas fa-calendar-alt me-2"></i>
                    <strong>Date Posted:</strong> <?= date("F j, Y", strtotime($job['created_at'])) ?>
                </p>
            </div>
            <hr class="divider-futuristic">


                    <!-- Job Description -->
                    <div class="job-description">
                        <h5 class="section-title text-futuristic">Job Description</h5>
                        <p class="card-text"><?= nl2br(htmlspecialchars($job['description'])) ?></p>
                    </div>
                    <hr class="divider-futuristic">
                    <!-- Responsibilities -->
                    <div class="job-responsibilities">
                        <h5 class="section-title text-futuristic">Responsibilities</h5>
                        <p class="card-text"><?= nl2br(htmlspecialchars($job['responsibilities'])) ?></p>
                    </div>
                    <hr class="divider-futuristic">
                    <!-- Requirements -->
                    <div class="job-requirements">
                        <h5 class="section-title text-futuristic">Requirements</h5>
                        <p class="card-text"><?= nl2br(htmlspecialchars($job['requirements'])) ?></p>
                    </div>
                    <hr class="divider-futuristic">
                    <!-- Preferred Qualifications -->
                    <div class="job-preferred-qualifications">
                        <h5 class="section-title text-futuristic">Preferred Qualifications</h5>
                        <p class="card-text"><?= nl2br(htmlspecialchars($job['preferred_qualifications'])) ?></p>
                    </div>
                        <hr class="divider-futuristic">

                        <!-- Show Positions Applied for (Only If User Applied) -->
<?php if ($user_applied && !empty($applied_positions)): ?>
    <div class="alert alert-info">
        <strong>Applied for:</strong> <?= implode(", ", $applied_positions) ?>
    </div>
<?php endif; ?>

<!-- Display the Admin's Remark (If Any and Only for the Applicant) -->
<?php if ($user_applied && !empty($remark['remark'])): ?>
    <div class="alert <?= ($application_status === 'accepted') ? 'alert-success' : ($application_status === 'rejected' ? 'alert-danger' : 'alert-info') ?>">
        <strong>Employer's Remark:</strong> <?= htmlspecialchars($remark['remark']) ?>
    </div>
<?php endif; ?>



<!-- Action Buttons -->
<div class="text-center mt-4">
    <?php if ($user_role === 'admin'): ?>
        <!-- Admin View - Can see all applicants for any job -->
        <p><strong>Applicants:</strong> <?= $total_applicants ?></p>
        <a href="../admin/view_applicants.php?job_id=<?= $id ?>" class="btn btn-futuristic-primary btn-action">
            <i class="fas fa-users me-2"></i> View Applicants
        </a>
    <?php elseif ($user_id): ?>
        <!-- Employer View - Only for the job they posted -->
        <?php if ($user_id === $job['employer_id']): ?>
            <p><strong>Applicants:</strong> <?= $total_applicants ?></p>
            <a href="../admin/view_applicants.php?job_id=<?= $id ?>" class="btn btn-futuristic-primary btn-action">
                <i class="fas fa-users me-2"></i> Manage Applicants
            </a>
        <?php elseif (!$user_applied): ?>
            <!-- User View - If not applied yet, show apply form -->
            <form action="apply.php" method="POST" enctype="multipart/form-data" class="d-inline" id="applyForm">
                <input type="hidden" name="job_id" value="<?= $id ?>">

                <!-- Position Selection -->
                <div class="mb-3">
                    <label class="form-label text-futuristic"><strong>Select Positions:</strong></label>
                    <select name="position_ids[]" id="position_ids" class="form-select form-futuristic mb-2" multiple>
                        <?php
                        $position_query = "SELECT jp.id, jp.position_name 
                                            FROM job_positions_jobs jpj
                                            JOIN job_positions jp ON jpj.position_id = jp.id
                                            WHERE jpj.job_id = ?";
                        $stmt = $conn->prepare($position_query);
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $positions_result = $stmt->get_result();
                        while ($row = $positions_result->fetch_assoc()):
                        ?>
                            <option value="<?= $row['id'] ?>"><?= $row['position_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Resume Selection -->
                <div class="mb-3">
                    <label class="form-label text-futuristic"><strong>Select Resume:</strong></label>
                    <select name="resume_option" id="resume_option" class="form-select form-futuristic mb-2">
                        <?php if ($has_resume): ?>
                            <option value="existing">Attach from Profile</option>
                        <?php else: ?>
                            <option value="" disabled>No resume available in profile</option>
                        <?php endif; ?>
                        <option value="new" <?= !$has_resume ? 'selected' : '' ?>>Upload New Resume</option>
                    </select>
                </div>
                
                <!-- File Upload Field -->
                <div id="resume_upload_field" class="mb-3" style="display: <?= !$has_resume ? 'block' : 'none' ?>;">
                    <label for="resume" class="form-label text-futuristic">Upload Resume</label>
                    <input type="file" name="resume" id="resume" class="form-control form-futuristic" accept=".pdf,.doc,.docx">
                </div>

                <!-- Submit Button -->
                <button type="submit" id="applyButton" class="btn btn-futuristic-success btn-action" disabled>
                    <i class="fas fa-paper-plane me-2"></i> Apply Now
                </button>
            </form>

        <?php else: ?>
            <!-- If user has already applied, show status and cancellation option -->
            <?php
            // Set the timezone to Manila
            date_default_timezone_set('Asia/Manila');

            // Fetch application status
            $app_status_query = "SELECT status, canceled_at FROM applications WHERE job_id = ? AND user_id = ?";
            $stmt = $conn->prepare($app_status_query);
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $status_result = $stmt->get_result();
            $application = $status_result->fetch_assoc();
            $application_status = $application['status'] ?? '';
            $canceled_at = $application['canceled_at'] ?? null;

            // Restrict reapplying for 10 minutes
            $can_reapply = true;
            if ($canceled_at) {
                // Convert canceled_at to a timestamp in the Manila timezone
                $canceled_time = strtotime($canceled_at); // This will now respect the Manila timezone
                $current_time = time(); // Current time in the Manila timezone
                $time_diff = $current_time - $canceled_time; // Time difference in seconds

                if ($time_diff < 600) { // 600 seconds = 10 minutes
                    $can_reapply = false;
                    $remaining_time = 600 - $time_diff; // Remaining time in seconds
                    $remaining_minutes = ceil($remaining_time / 60); // Convert to minutes and round up

                    echo "<div class='alert alert-warning text-center'>
                            You can reapply in <strong>{$remaining_minutes} minutes</strong>.
                          </div>";
                } else {
                    // Delete the application record after 10 minutes
                    $delete_query = "DELETE FROM applications WHERE job_id = ? AND user_id = ?";
                    $delete_stmt = $conn->prepare($delete_query);
                    $delete_stmt->bind_param("ii", $id, $user_id);
                    $delete_stmt->execute();

                    // Refresh the application status
                    $application_status = NULL;
                    $canceled_at = NULL;
                    $can_reapply = true;
                }
            }

            if ($application_status === 'pending'): ?>
                <p class="text-muted">
                    <i class="fas fa-info-circle me-1"></i> Note: You can only cancel your application if it is still <strong>pending</strong>.
                </p>

                <!-- Cancel Application Button with Confirmation Modal -->
                <button type="button" class="btn btn-danger btn-action" data-bs-toggle="modal" data-bs-target="#cancelApplicationModal">
                    <i class="fas fa-times me-2"></i> Cancel Application
                </button>

                <!-- Cancel Application Modal -->
                <div class="modal fade" id="cancelApplicationModal" tabindex="-1" aria-labelledby="cancelApplicationModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="cancelApplicationModalLabel">Confirm Cancellation</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to cancel your application? You will not be able to reapply for this job for the next 10 minutes.
                            </div>
                            <div class="modal-footer">
                                <form action="cancel_application.php" method="POST" class="d-inline">
                                    <input type="hidden" name="job_id" value="<?= $id ?>">
                                    <button style="background-color:#007bff; box-shadow:none;" type="submit" class="btn btn-primary">Yes, Cancel Application</button>
                                </form>
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-secondary btn-action" disabled>
                    <i class="fas fa-check me-2"></i> Applied (<?= ucfirst($application_status) ?>)
                </button>
            <?php endif; ?>
        <?php endif; ?>
    <?php else: ?>
        <p>
            <a href="login.php" class="btn btn-outline-primary btn-action">
                <i class="fas fa-sign-in-alt me-2"></i> Login to Apply
            </a>
        </p>
    <?php endif; ?>
</div>





                        <!-- Back Button -->
                        <div class="text-center mt-4">
                        <button type="button" class="btn btn-futuristic-back" onclick="goBackOrCancel()">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left-dashed">
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M5 12h6m3 0h1.5m3 0h.5" />
        <path d="M5 12l6 6" />
        <path d="M5 12l6 -6" />
    </svg>
    Back
</button>

                        </div>
                    
              
          
    
   


                        <script src="/JOB/assets/script/main.js"></script>

                        <script>
function goBackOrCancel() {
    // Check the referrer to determine the source of the request
    if (document.referrer.includes('browse.php')) {
        // If coming from browse.php, redirect to browse.php
        window.location.href = '/JOB/pages/browse.php';
    } else if (document.referrer.includes('profile.php')) {
        // If coming from profile.php, check if the admin is viewing another user's profile
        const urlParams = new URLSearchParams(window.location.search);
        const isAdminViewingOtherProfile = urlParams.get('viewing') === 'user';

        if (isAdminViewingOtherProfile) {
            // If the admin is viewing another user's profile, go back to the admin's own profile
            window.location.href = '/JOB/pages/profile.php';
        } else {
            // If the logged-in user is viewing their own profile, stay on profile.php
            window.history.back();
        }
    } else {
        // Default fallback if neither condition is met, redirect to browse.php
        window.location.href = '/JOB/pages/browse.php';
    }
}



document.addEventListener('DOMContentLoaded', function () {
    const resumeOptionSelect = document.getElementById('resume_option');
    const resumeUploadField = document.getElementById('resume_upload_field');
    const resumeFileInput = document.getElementById('resume');
    const applyButton = document.getElementById('applyButton');
    const positionSelect = document.getElementById('position_ids');

    // Function to check if the form is valid
    function validateForm() {
        const selectedOption = resumeOptionSelect.value;
        const fileUploaded = resumeFileInput.files.length > 0;
        const positionsSelected = positionSelect.selectedOptions.length > 0;

        // Enable apply button if resume is valid and at least one position is selected
        if (
            ((selectedOption === 'existing' && <?= $has_resume ? 'true' : 'false' ?>) || 
            (selectedOption === 'new' && fileUploaded)) && positionsSelected
        ) {
            applyButton.disabled = false; // Enable button
        } else {
            applyButton.disabled = true; // Disable button
        }
    }

    // Event listeners for changes
    resumeOptionSelect.addEventListener('change', function () {
        if (this.value === 'new') {
            resumeUploadField.style.display = 'block';
        } else {
            resumeUploadField.style.display = 'none';
        }
        validateForm();
    });
    resumeFileInput.addEventListener('change', validateForm);
    positionSelect.addEventListener('change', validateForm);

    // Initial validation
    validateForm();
});

</script>


