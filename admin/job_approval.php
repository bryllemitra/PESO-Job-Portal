<?php
include '../includes/config.php';
include '../includes/header.php';

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
        echo "<script>alert('Job approved successfully!'); window.location.href='job_approval.php';</script>";
    } else {
        echo "<script>alert('Error approving job. Please try again.');</script>";
    }
}

// Reject Job
if (isset($_GET['reject_id'])) {
    $job_id = $_GET['reject_id'];
    $update_stmt = $conn->prepare("UPDATE jobs SET status = 'rejected' WHERE id = ?");
    $update_stmt->bind_param("i", $job_id);
    if ($update_stmt->execute()) {
        echo "<script>alert('Job rejected successfully!'); window.location.href='job_approval.php';</script>";
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/JOB/assets/feedback_bin.css">
    <style>
        /* Custom Styles */
        .job-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #ddd; }
        .job-actions { display: flex; gap: 10px; }
    </style>
</head>
<body>

<!-- Sidebar (Admin Navigation) -->
<div class="sidebar" id="sidebar">
    <div>
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="admin.php" ><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="job_list.php"><i class="fas fa-briefcase"></i> Job List</a></li>
            <li><a href="user_list.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="feedback_bin.php"><i class="fas fa-trash-alt"></i> Feedback Bin</a></li>
            <li><a href="job_approval.php " class="active"><i class="fas fa-clipboard-check"></i> Job Approvals</a></li>
        </ul>
    </div>
</div>

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
                <input type="text" name="search" class="form-control rounded-pill shadow-sm"
                       placeholder="Search by title or employer name"
                       value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            </div>
            <!-- Sorting Dropdown -->
            <div class="col-md-3 col-12">
                <select name="sort_by" class="form-select rounded-pill shadow-sm w-100">
                    <option value="created_at" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'created_at') ? 'selected' : '' ?>>Latest Jobs</option>
                    <option value="title" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'title') ? 'selected' : '' ?>>By Job Title</option>
                </select>
            </div>
            <!-- Submit Button -->
            <div class="col-md-auto col-12">
                <button type="submit" class="btn btn-primary rounded-pill shadow-sm w-100">Filter</button>
            </div>
        </form>
    </div>

    <!-- Job List Table -->
    <div class="job-table">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Employer Name</th>
                    <th>Employer Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <!-- Make the job title clickable -->
                                <a href="../pages/job.php?id=<?= $row['id'] ?>" class="text-decoration-none text-primary fw-semibold">
                                    <?= htmlspecialchars($row['title']) ?>
                                </a>
                            </td>
                            <td>
                                <!-- Make the employer name clickable -->
                                <a href="../pages/profile.php?id=<?= $row['employer_id'] ?>" class="text-decoration-none text-primary fw-semibold">
                                    <?= htmlspecialchars($row['employer_name']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($row['employer_email']) ?></td>
                            <td><?= ucfirst($row['status']) ?></td>
                            <td class="job-actions">
                                <!-- Approve Button -->
                                <a href="job_approval.php?approve_id=<?= $row['id'] ?>" class="btn btn-success btn-sm">Approve</a>
                                <!-- Reject Button -->
                                <a href="job_approval.php?reject_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Reject</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No pending jobs to approve/reject.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- Bootstrap JS -->

</body>
</html>