<?php
include '../includes/config.php';
include '../includes/header.php';

// Restrict access to admins and employers only
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employer')) {
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

// Get the job_id from the query string
$job_id = $_GET['job_id'] ?? null;
if (!$job_id) {
    echo "<div class='alert alert-danger text-center'>Invalid job ID.</div>";
    exit();
}

// Fetch job details and employer_id
$jobQuery = "SELECT title, employer_id FROM jobs WHERE id = ?";
$stmt = $conn->prepare($jobQuery);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$jobResult = $stmt->get_result();
$job = $jobResult->fetch_assoc();
if (!$job) {
    echo "<div class='alert alert-danger text-center'>Job not found.</div>";
    exit();
}

// Get the logged-in user's ID and role
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;

// Check if the user is the employer or admin
if ($user_id !== $job['employer_id'] && $user_role !== 'admin') {
    echo "<div class='alert alert-danger text-center'>You are not authorized to manage applicants for this job.</div>";
    exit();
}

// Handle Accept/Reject Actions with Remarks
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $user_id = $_POST['user_id'] ?? null;
    $job_id = $_POST['job_id'] ?? null;
    $remark = $_POST['remark'] ?? null; // Get remark from the form

    if ($action && $user_id && $job_id) {
        // Determine the status based on the action
        $status = ($action === 'accept') ? 'accepted' : 'rejected';

        // Update the application status, remark, and action_taken_by in the database
        $updateQuery = "UPDATE applications 
                        SET status = ?, remark = ?, action_taken_by = ? 
                        WHERE user_id = ? AND job_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssiii", $status, $remark, $_SESSION['user_id'], $user_id, $job_id);
        $stmt->execute();
        $stmt->close();

        // Fetch applicant details (name and job title)
        $userQuery = "SELECT first_name, last_name FROM users WHERE id = ?";
        $stmt = $conn->prepare($userQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $userResult = $stmt->get_result();
        $user = $userResult->fetch_assoc();

        // Fetch job title
        $jobQuery = "SELECT title FROM jobs WHERE id = ?";
        $stmt = $conn->prepare($jobQuery);
        $stmt->bind_param('i', $job_id);
        $stmt->execute();
        $jobResult = $stmt->get_result();
        $job = $jobResult->fetch_assoc();

        // Create a notification for the applicant
        $notification_message = "{$user['first_name']} {$user['last_name']} has been {$status} for the job: {$job['title']}.";
        $insertQuery = "INSERT INTO notifications (recipient_id, sender_id, message, job_id, created_at) 
                         VALUES (?, ?, ?, ?, NOW())";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param('iisi', $user_id, $_SESSION['user_id'], $notification_message, $job_id);
        $insertStmt->execute();
        
        // Use JavaScript to redirect instead of header()
        echo "<script>window.location.href = 'view_applicants.php?job_id=$job_id&status=success';</script>";
        exit(); // Always exit after a redirect
    }
}

// Fetch applicants for the job
$appQuery = "
    SELECT 
        a.id, 
        a.user_id, 
        a.job_id, 
        a.applied_at, 
        a.resume_file, 
        a.status, 
        a.remark, 
        a.action_taken_by, 
        u.first_name, 
        u.middle_name, 
        u.last_name, 
        u.username, 
        GROUP_CONCAT(DISTINCT jp.position_name SEPARATOR ', ') AS applied_positions  -- Fetch position names
    FROM applications a
    JOIN users u ON a.user_id = u.id
    LEFT JOIN application_positions ap ON a.id = ap.application_id
    LEFT JOIN job_positions jp ON ap.position_id = jp.id  -- Join job_positions table to get position names
    WHERE a.job_id = ?
    GROUP BY a.id
";

$stmt = $conn->prepare($appQuery);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$appResult = $stmt->get_result();

?>



<link rel="stylesheet" href="/JOB/assets/view_applicants.css">
<style>
        body {
            background-color: #f1f3f5;
        }
    </style>

    
<div class="container mt-5">
    <h2 class="text-center mb-4 text-futuristic">📄 Applicants for <span class="text-primary"><?= htmlspecialchars($job['title']) ?></span></h2>
    
    <!-- Search Bar and Sorting Controls -->
    <div class="d-flex justify-content-between align-items-center my-3">
        <div class="d-flex align-items-center">
            <input type="text" id="searchInput" class="form-control me-2" placeholder="Search by name/username..." onkeyup="filterApplicants()">
            <select id="sortSelect" class="form-select" onchange="sortApplicants()">
                <option value="position">Sort by Position</option>
                <option value="status">Sort by Status</option>
                <option value="date">Sort by Date Applied</option>
            </select>
        </div>
        <button onclick="goBack()" class="btn btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left-dashed">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12h6m3 0h1.5m3 0h.5" />
                <path d="M5 12l6 6" />
                <path d="M5 12l6 -6" />
            </svg> Back
        </button>
    </div>

    <?php if ($appResult->num_rows > 0): ?>
    <table class="table-futuristic" id="applicantsTable">
        <thead>
            <tr>
                <th>Applicant</th>
                <th>Applied For</th>
                <th>Resume</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $appResult->fetch_assoc()): 
            // Construct full name
            $fullName = trim($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name']);
            $profileLink = "../pages/profile.php?id=" . urlencode($row['user_id']);
            $resumeFile = $row['resume_file'];
            $status = $row['status'];

            // Safely check if applied_positions and applied_at exist
            $appliedPositions = !empty($row['applied_positions']) ? $row['applied_positions'] : 'No positions applied';
            $appliedAt = !empty($row['applied_at']) ? $row['applied_at'] : 'Unknown time';

            // Fetch the name of the person who took the action (if any)
            $actionTakenBy = '';
            if (!empty($row['action_taken_by'])) {
                $actionUserQuery = "SELECT first_name, last_name, role FROM users WHERE id = ?";
                $actionUserStmt = $conn->prepare($actionUserQuery);
                $actionUserStmt->bind_param('i', $row['action_taken_by']);
                $actionUserStmt->execute();
                $actionUserResult = $actionUserStmt->get_result();
                if ($actionUserResult->num_rows > 0) {
                    $actionUser = $actionUserResult->fetch_assoc();
                    $actionTakenBy = $actionUser['role'] === 'admin' 
                        ? 'Admin' 
                        : trim($actionUser['first_name'] . ' ' . $actionUser['last_name']);
                }
            }
        ?>
        <tr>
            <td>
                <div>
                    <a href="<?= htmlspecialchars($profileLink) ?>" class="fw-bold text-decoration-none text-futuristic-link">
                        <?= htmlspecialchars($fullName) ?>
                    </a>
                    <br>
                    <small>
                        <a href="<?= htmlspecialchars($profileLink) ?>" class="text-decoration-none text-muted">
                            @<?= htmlspecialchars($row['username']) ?>
                        </a>
                    </small>
                </div>
            </td>
            <td>
                <strong><?= htmlspecialchars($appliedPositions) ?></strong><br>
                <br>
                <small><?= date("F j, Y, g:i a", strtotime($appliedAt)) ?></small> <!-- Format the applied_at -->
            </td>
            <td>
                <?php if (!empty($resumeFile)): ?>
                    <?php
                    // Check the file extension
                    $fileExtension = pathinfo($resumeFile, PATHINFO_EXTENSION);
                    $fileUrl = htmlspecialchars($resumeFile);
                    ?>
                    <div id="resume-actions-<?= $row['user_id'] ?>" class="d-flex">
                        <button class="btn btn-light-futuristic me-2" onclick="viewResume('<?= $fileUrl ?>', '<?= $fileExtension ?>')">View</button>
                        <a href="<?= $fileUrl ?>" class="btn btn-light-download" download>
                            <i class="fas fa-download me-2"></i> Download
                        </a>
                    </div>
                <?php else: ?>
                    <span class="text-danger">No resume attached</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($status === 'pending'): ?>
                    <span class="badge bg-warning text-dark">Pending</span>
                <?php elseif ($status === 'accepted'): ?>
                    <span class="badge bg-success">Accepted</span>
                <?php elseif ($status === 'rejected'): ?>
                    <span class="badge bg-danger">Rejected</span>
                <?php elseif ($status === 'canceled'): ?>
                    <span class="badge bg-secondary">Canceled</span>
                    <br>
                    <small class="text-muted">
                        <?= !empty($row['canceled_at']) ? date("F j, Y, g:i a", strtotime($row['canceled_at'])) : '' ?>
                    </small>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($status === 'pending'): ?>
                    <!-- Accept Button -->
                    <button type="button" class="btn btn-light-check me-2" data-bs-toggle="modal" data-bs-target="#actionModal" data-action="accept" data-user-id="<?= $row['user_id'] ?>" data-job-id="<?= $job_id ?>">
                        accept
                    </button>

                    <!-- Reject Button -->
                    <button type="button" class="btn btn-light-cross" data-bs-toggle="modal" data-bs-target="#actionModal" data-action="reject" data-user-id="<?= $row['user_id'] ?>" data-job-id="<?= $job_id ?>">
                        reject
                    </button>
                <?php elseif ($status === 'canceled'): ?>
                    <span class="text-muted">Applicant canceled</span>
                <?php else: ?>
                    <span class="text-muted">
                        Action taken by <?= htmlspecialchars($actionTakenBy) ?>
                    </span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
        <div class="alert alert-warning text-center">❌ No applicants for this job yet.</div>
    <?php endif; ?>
</div>

<!-- Modal for Fullscreen Resume Preview -->
<div id="resume-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-light text-dark">
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

<!-- Modal -->
<div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="actionText"></p> <!-- Action text will be dynamically set (Accept/Reject) -->
                <textarea id="remarkText" class="form-control" placeholder="Enter remark or reason"></textarea>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="confirmActionBtn">Confirm</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                
            </div>
        </div>
    </div>
</div>



<!-- Include Mammoth.js -->
<script src="https://unpkg.com/mammoth/mammoth.browser.min.js"></script>
<script>
    // Fix Back Button Issue
    function goBack() {
    // Check if there's a valid referrer
    if (document.referrer && !document.referrer.includes("view_applicants.php")) {
        window.location.href = document.referrer; // Navigate to the referrer page
    } else {
        window.location.href = "../pages/index.php"; // Fallback URL
    }
}

    // Function to Open Resume in Modal
    function viewResume(fileUrl, fileExtension) {
        const modalBody = document.getElementById('resume-modal-body');
        modalBody.innerHTML = ''; // Clear previous content

        if (fileExtension.toLowerCase() === 'pdf') {
            // Embed PDF in an iframe
            modalBody.innerHTML = `<iframe src="${fileUrl}" width="100%" height="100%" style="border:none;"></iframe>`;
        } else if (fileExtension.toLowerCase() === 'docx') {
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

        // Show the modal
        const resumeModal = new bootstrap.Modal(document.getElementById('resume-modal'), {});
        resumeModal.show();
    }

    // When the modal is shown, set the action and job/user ID
document.addEventListener('DOMContentLoaded', function () {
    const actionModal = document.getElementById('actionModal');
    actionModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const action = button.getAttribute('data-action');
        const userId = button.getAttribute('data-user-id');
        const jobId = button.getAttribute('data-job-id');

        // Set the action text and store user/job IDs
        document.getElementById('actionText').textContent = (action === 'accept') ? 'Accept this application?' : 'Reject this application?';
        document.getElementById('confirmActionBtn').setAttribute('data-action', action);
        document.getElementById('confirmActionBtn').setAttribute('data-user-id', userId);
        document.getElementById('confirmActionBtn').setAttribute('data-job-id', jobId);
    });

    document.getElementById('confirmActionBtn').addEventListener('click', function () {
    const action = this.getAttribute('data-action');
    const userId = this.getAttribute('data-user-id');
    const jobId = this.getAttribute('data-job-id');
    let remark = document.getElementById('remarkText').value;

    // If the action is 'accept' and remark is empty, set the default remark
    if (action === 'accept' && !remark) {
        remark = "Your application has been accepted. Kindly await our call for next steps.";
    }

    // Create a form and submit it to process the action
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = ''; // Stay on the same page

    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = action;
    form.appendChild(actionInput);

    const userIdInput = document.createElement('input');
    userIdInput.type = 'hidden';
    userIdInput.name = 'user_id';
    userIdInput.value = userId;
    form.appendChild(userIdInput);

    const jobIdInput = document.createElement('input');
    jobIdInput.type = 'hidden';
    jobIdInput.name = 'job_id';
    jobIdInput.value = jobId;
    form.appendChild(jobIdInput);

    const remarkInput = document.createElement('input');
    remarkInput.type = 'hidden';
    remarkInput.name = 'remark';
    remarkInput.value = remark;
    form.appendChild(remarkInput);

    document.body.appendChild(form);
    form.submit();
});

});

const filterApplicants = () => {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#applicantsTable tbody tr');

        rows.forEach(row => {
            const name = row.querySelector('td:nth-child(1) a').innerText.toLowerCase();
            const username = row.querySelector('td:nth-child(1) small a').innerText.toLowerCase();
            if (name.includes(input) || username.includes(input)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    };

    const sortApplicants = () => {
        const sortBy = document.getElementById('sortSelect').value;
        const rows = Array.from(document.querySelectorAll('#applicantsTable tbody tr'));

        rows.sort((a, b) => {
            let valA, valB;

            switch (sortBy) {
                case 'position':
                    valA = a.querySelector('td:nth-child(2) strong').innerText.toLowerCase();
                    valB = b.querySelector('td:nth-child(2) strong').innerText.toLowerCase();
                    break;
                case 'status':
                    valA = a.querySelector('td:nth-child(4) span').innerText.toLowerCase();
                    valB = b.querySelector('td:nth-child(4) span').innerText.toLowerCase();
                    break;
                case 'date':
                    valA = new Date(a.querySelector('td:nth-child(2) small').innerText);
                    valB = new Date(b.querySelector('td:nth-child(2) small').innerText);
                    break;
            }

            return valA > valB ? 1 : -1;
        });

        const tbody = document.querySelector('#applicantsTable tbody');
        rows.forEach(row => tbody.appendChild(row));
    };

</script>

