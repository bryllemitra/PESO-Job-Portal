<?php
include '../includes/config.php';
include '../includes/header.php';
include '../includes/restrictions.php';
include('../includes/sidebar.php');


// Check if the admin is logged in
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Approve Job
if (isset($_GET['approve_id'])) {
    $job_id = $_GET['approve_id'];
    $update_stmt = $conn->prepare("UPDATE jobs SET status = 'approved' WHERE id = ?");
    $update_stmt->bind_param("i", $job_id);
    if ($update_stmt->execute()) {
        echo "<script>$('#approveSuccessModal').modal('show');</script>";
    } else {
        echo "<script>alert('Error approving job. Please try again.');</script>";
    }
}

// Reject Job with Remarks
if (isset($_GET['reject_id']) && isset($_GET['remarks'])) {
    $job_id = $_GET['reject_id'];
    $remarks = $_GET['remarks']; // Get remarks from query string
    $update_stmt = $conn->prepare("UPDATE jobs SET status = 'rejected', remarks = ? WHERE id = ?");
    $update_stmt->bind_param("si", $remarks, $job_id);
    if ($update_stmt->execute()) {
        echo "<script>$('#rejectSuccessModal').modal('show');</script>";  // Show success modal for rejection
    } else {
        echo "<script>alert('Error rejecting job. Please try again.');</script>";
    }
}

// Fetch pending jobs for approval
$query = "SELECT jobs.id, jobs.title, jobs.status, 
          CONCAT(users.first_name, ' ', users.middle_name, ' ', users.last_name) AS employer_name, 
          users.email AS employer_email, users.id AS employer_id
          FROM jobs 
          JOIN users ON jobs.employer_id = users.id 
          WHERE jobs.status = 'pending'";

$result = $conn->query($query);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Approvals - Admin Dashboard</title>
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/JOB/assets/job_approval.css">
    <style>
        /* Custom Styles */
        .job-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #ddd; }
        .job-actions { display: flex; gap: 30px; }

        .form-control, .form-select, .btn {
    height: 45px; /* Set consistent height for input, select, and button */
    
}

.expanded-input, .expanded-select, .expanded-button {
    width: 100%; /* Ensure inputs, select, and button take up full width of their containers */
}

.expanded-button {
    width: auto; /* Allow the button to adjust dynamically based on its content */
}

    </style>
</head>
<body>



<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="header">
        <h1>Job Approvals</h1>
    </div>

<!-- Filters and Sorting -->
<div class="filters">
    <form action="job_approval.php" method="GET" class="mb-4 row g-3 align-items-center">
        <!-- Search Box -->
        <div class="col-md-6 col-12">
            <input type="text" name="search" class="form-control rounded-pill shadow-sm expanded-input"
                   placeholder="Search by title or employer name"
                   value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        </div>
        <!-- Sorting Dropdown -->
        <div class="col-md-3 col-12">
            <select name="sort_by" class="form-select rounded-pill shadow-sm expanded-select w-100">
                <option value="created_at" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'created_at') ? 'selected' : '' ?>>Latest Jobs</option>
                <option value="title" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'title') ? 'selected' : '' ?>>By Job Title</option>
            </select>
        </div>
        <!-- Submit Button -->
        <div class="col-md-auto col-12">
            <button type="submit" class="btn btn-primary rounded-pill shadow-sm expanded-button">Filter</button>
        </div>
    </form>
</div>


<!-- Job List -->
<div class="user-list">
    <!-- Table Header -->
    <div class="user-header text-center">
        <div>Job Title</div>
        <div>Employer Name</div>
        <div>Employer Email</div>
        <div>Status</div>
        <div>Actions</div>
    </div>

    <!-- Job Items -->
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="user-item text-center">
                <!-- Job Title -->
                <div class="job-title">
                    <a href="../pages/job.php?id=<?= $row['id'] ?>" class="text-decoration-none text-primary fw-semibold">
                        <?= htmlspecialchars($row['title']) ?>
                    </a>
                </div>

                <!-- Employer Name -->
                <div class="employer-name">
                    <a href="../employers/profile.php?id=<?= $row['employer_id'] ?>" class="text-decoration-none text-primary fw-semibold">
                        <?= htmlspecialchars($row['employer_name']) ?>
                    </a>
                </div>

                <!-- Employer Email -->
                <div class="employer-email"><?= htmlspecialchars($row['employer_email']) ?></div>

                <!-- Status -->
                <div class="status"><?= ucfirst($row['status']) ?></div>

                <!-- Actions -->
                <div class="actions">
<!-- Approve Button -->
<?php if ($row['status'] == 'pending'): ?>
    <button class="btn btn-primary btn-sm approveBtn" data-job-id="<?= $row['id'] ?>">
        Approve
    </button>
<?php endif; ?>

<!-- Reject Button -->
<?php if ($row['status'] == 'pending'): ?>
    <button class="btn btn-light btn-sm rejectBtn" data-job-id="<?= $row['id'] ?>">
        Reject
    </button>
<?php endif; ?>

                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-feedback">
            <div colspan="5">No pending jobs to approve/reject.</div>
        </div>
    <?php endif; ?>
</div>



</div>

<!-- Modal for Rejecting with Remarks -->
<div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered"">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="actionText"></p> <!-- Action text will be dynamically set (Approve/Reject) -->
                <textarea id="remarkText" class="form-control" placeholder="Enter remark or reason" style="display: none;"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="confirmActionBtn">Confirm</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                
            </div>
        </div>
    </div>
</div>

<!-- Success Modal for Approving -->
<div class="modal fade" id="approveSuccessModal" tabindex="-1" aria-labelledby="approveSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveSuccessModalLabel">Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Job approved successfully!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal for Rejecting -->
<div class="modal fade" id="rejectSuccessModal" tabindex="-1" aria-labelledby="rejectSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectSuccessModalLabel">Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Job rejected successfully!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Modal elements
        const actionModal = new bootstrap.Modal(document.getElementById('actionModal'));
        const approveSuccessModal = new bootstrap.Modal(document.getElementById('approveSuccessModal'));
        const actionTextElement = document.getElementById('actionText');
        const remarkTextElement = document.getElementById('remarkText');
        const confirmActionBtn = document.getElementById('confirmActionBtn');
        
        let currentJobId = null;
        let currentAction = null;

        // Open modal for rejecting a job
        function openRejectModal(jobId) {
            currentJobId = jobId;
            currentAction = 'reject';

            actionTextElement.textContent = 'Are you sure you want to reject this job?';
            remarkTextElement.style.display = 'block'; // Show remarks field
            confirmActionBtn.classList.remove('btn-primary');
            confirmActionBtn.classList.add('btn-primary');
            confirmActionBtn.textContent = 'Reject';

            actionModal.show();
        }

        // Open modal for approving a job
        function openApproveModal(jobId) {
            currentJobId = jobId;
            currentAction = 'approve';

            actionTextElement.textContent = 'Are you sure you want to approve this job?';
            remarkTextElement.style.display = 'none'; // Hide remarks field
            confirmActionBtn.classList.remove('btn-danger');
            confirmActionBtn.classList.add('btn-primary');
            confirmActionBtn.textContent = 'Approve';

            actionModal.show();
        }

        // Handle the "Confirm" button click
        confirmActionBtn.addEventListener('click', function () {
            const remarks = remarkTextElement.value.trim();

            // For rejection, ensure remarks are provided
            if (currentAction === 'reject' && !remarks) {
                alert('Please provide a remark or reason for rejection.');
                return;
            }

            // Handle job action
            if (currentAction === 'approve') {
                window.location.href = `job_approval.php?approve_id=${currentJobId}`;
            } else if (currentAction === 'reject') {
                window.location.href = `job_approval.php?reject_id=${currentJobId}&remarks=${encodeURIComponent(remarks)}`;
            }

            // Close the modal
            actionModal.hide();
        });

        // Listen for the "Reject" button click
        document.querySelectorAll('.rejectBtn').forEach(button => {
            button.addEventListener('click', function (e) {
                const jobId = e.target.getAttribute('data-job-id');
                openRejectModal(jobId);
            });
        });

        // Listen for the "Approve" button click
        document.querySelectorAll('.approveBtn').forEach(button => {
            button.addEventListener('click', function (e) {
                const jobId = e.target.getAttribute('data-job-id');
                openApproveModal(jobId);
            });
        });
    });
</script>


</body>
</html>