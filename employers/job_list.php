<?php
include '../includes/config.php';
include '../includes/header.php';

// Check if the employer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    // Redirect to login page or show an error message
    header('Location: ../login.php');
    exit();
}

// Get the logged-in employer's ID
$employer_id = $_SESSION['user_id'];

// Pagination logic
$limit = 15;  // Number of jobs per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

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

// Handle sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at_desc'; // Default sorting by creation date in descending order

// Define allowed sorting options to avoid SQL injection
$sort_mapping = [
    'created_at_asc' => ['column' => 'created_at', 'order' => 'ASC'],
    'created_at_desc' => ['column' => 'created_at', 'order' => 'DESC'],
    'title_asc' => ['column' => 'title', 'order' => 'ASC'],
    'title_desc' => ['column' => 'title', 'order' => 'DESC'],
    'total_applicants_asc' => ['column' => 'total_applicants', 'order' => 'ASC'],
    'total_applicants_desc' => ['column' => 'total_applicants', 'order' => 'DESC'],
];

// Validate the sort parameter
if (!array_key_exists($sort, $sort_mapping)) {
    $sort = 'created_at_desc'; // Default to created_at_desc if invalid sort is passed
}

// Extract column and order from the mapping
$sort_column = $sort_mapping[$sort]['column'];
$sort_order = $sort_mapping[$sort]['order'];

// Build the query for all jobs with joins to position and category tables
$query_all_jobs = "
    SELECT DISTINCT j.*, COUNT(a.id) AS total_applicants
    FROM jobs j
    LEFT JOIN applications a ON j.id = a.job_id
    LEFT JOIN job_categories jc ON j.id = jc.job_id
    LEFT JOIN job_positions_jobs jp ON j.id = jp.job_id
    WHERE j.employer_id = $employer_id
    $search_filter
    $category_filter
    $position_filter
    $location_filter
    GROUP BY j.id
    ORDER BY $sort_column $sort_order
    LIMIT $limit OFFSET $offset
";

// Execute the query
$result = $conn->query($query_all_jobs);

// Count total jobs for pagination
$count_query = "
    SELECT COUNT(DISTINCT j.id) AS total_jobs
    FROM jobs j
    LEFT JOIN job_categories jc ON j.id = jc.job_id
    LEFT JOIN job_positions_jobs jp ON j.id = jp.job_id
    WHERE j.employer_id = $employer_id
    $search_filter
    $category_filter
    $position_filter
    $location_filter
";
$count_result = $conn->query($count_query);
$count_data = $count_result->fetch_assoc();
$total_jobs = $count_data['total_jobs'];
$total_pages = ceil($total_jobs / $limit);

// Fetch categories for the dropdown
$category_query = "SELECT * FROM categories ORDER BY name ASC";
$category_result = $conn->query($category_query);

// Fetch positions for the position dropdown
$position_query = "SELECT * FROM job_positions ORDER BY position_name ASC";
$position_result = $conn->query($position_query);

// Fetch barangay names for the location dropdown
$barangay_query = "SELECT name FROM barangay ORDER BY name ASC";
$barangay_result = $conn->query($barangay_query);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job List - Admin Dashboard</title>
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="/JOB/assets/job_list.css">
    <style>


        /* Full width for small screens (e.g., mobile devices) */
        @media (max-width: 575.98px) {
            .filter-button {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div>
        <h2>Employer Panel</h2>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="job_list.php" class="active"><i class="fas fa-briefcase"></i> My Jobs</a></li>
            <li><a href="user_list.php"><i class="fas fa-users"></i> Applicants</a></li>
        </ul>
    </div>
    <div class="toggle-btn" onclick="toggleSidebar()">
        <i class="fas fa-angle-right"></i>
    </div>
</div>

 <!-- Main Content -->
<main>
    <h3>Posted Jobs</h3><br>

    <!-- Search and Filter Form -->
    <form action="" method="get" class="row gx-2 gy-2 justify-content-center">
        <!-- Search Bar -->
        <div class="col-lg-2 col-md-3 col-6">
            <input type="text" name="search" class="form-control form-control-m shadow-sm rounded-pill" placeholder="Search..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        </div>
        <!-- Category Dropdown -->
        <div class="col-lg-2 col-md-3 col-6">
            <select name="category" class="form-select form-select-m shadow-sm rounded-pill">
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
            <select name="position" class="form-select form-select-m shadow-sm rounded-pill">
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
            <select name="location" class="form-select form-select-m shadow-sm rounded-pill">
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
        <!-- Combined Sorting Dropdown -->
        <div class="col-lg-2 col-md-3 col-6">
            <select name="sort" class="form-select form-select-m shadow-sm rounded-pill">
                <option value="created_at_desc" <?= $sort === 'created_at_desc' ? 'selected' : '' ?>>Sort by Date (Newest First)</option>
                <option value="created_at_asc" <?= $sort === 'created_at_asc' ? 'selected' : '' ?>>Sort by Date (Oldest First)</option>
                <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Sort by Title (A-Z)</option>
                <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : '' ?>>Sort by Title (Z-A)</option>
                <option value="total_applicants_asc" <?= $sort === 'total_applicants_asc' ? 'selected' : '' ?>>Sort by Applicants (Lowest First)</option>
                <option value="total_applicants_desc" <?= $sort === 'total_applicants_desc' ? 'selected' : '' ?>>Sort by Applicants (Highest First)</option>
            </select>
        </div>
        <!-- Submit Button -->
        <div class="col-md-2">
            <button type="submit" style="height: 100%;" class="btn btn-primary btn-lg rounded-pill filter-button">Filter</button>
        </div>
    </form><br>
    
    <!-- Job List -->
    <div class="job-list">
        <!-- Table Header -->
        <div class="job-header">
            <div>Job Title</div>
            <div>Description</div>
            <div>Applicants</div>
            <div>Date</div>
            <div>Actions</div>
        </div>

        <!-- Job Items -->
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="job-item">
                <div class="title"><?= htmlspecialchars($row['title']) ?></div>
                <div class="description"><?= htmlspecialchars($row['description']) ?></div>
                <div class="applicants" onclick="location.href='view_applicants.php?job_id=<?= $row['id'] ?>'">
                    👤 <?= $row['total_applicants'] ?> Applicants
                </div>
                <div class="date"><?= htmlspecialchars(date('Y-m-d H:i', strtotime($row['created_at']))) ?></div>
                <div class="actions">
                    <button onclick="location.href='edit_job.php?id=<?= $row['id'] ?>&source=job_list'"><i class="fas fa-edit"></i></button>
                    <button data-bs-toggle="modal" data-bs-target="#deleteJobModal" class="btn-delete" data-job-id="<?= $row['id'] ?>"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <button onclick="location.href='?page=<?= $page - 1 ?>&search=<?= $search ?>&category=<?= $_GET['category'] ?? '' ?>&position=<?= $_GET['position'] ?? '' ?>&location=<?= $_GET['location'] ?? '' ?>'" <?= $page <= 1 ? 'disabled' : '' ?>> <i class="fas fa-chevron-left"></i> </button>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <button onclick="location.href='?page=<?= $i ?>&search=<?= $search ?>&category=<?= $_GET['category'] ?? '' ?>&position=<?= $_GET['position'] ?? '' ?>&location=<?= $_GET['location'] ?? '' ?>'" <?= $i === $page ? 'class="active"' : '' ?>><?= $i ?></button>
        <?php endfor; ?>
        <button onclick="location.href='?page=<?= $page + 1 ?>&search=<?= $search ?>&category=<?= $_GET['category'] ?? '' ?>&position=<?= $_GET['position'] ?? '' ?>&location=<?= $_GET['location'] ?? '' ?>'" <?= $page >= $total_pages ? 'disabled' : '' ?>> <i class="fas fa-chevron-right"></i> </button>
    </div>
</main>

<!-- Delete Job Modal -->
<div class="modal fade" id="deleteJobModal" tabindex="-1" aria-labelledby="deleteJobModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteJobModalLabel">Delete Job</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this job? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="confirmDeleteJob">Delete</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>


<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        sidebar.classList.toggle('hidden');
        mainContent.classList.toggle('hidden');
    }

    // Get modal element and confirm delete button
const deleteJobModal = new bootstrap.Modal(document.getElementById('deleteJobModal'));
const confirmDeleteJob = document.getElementById('confirmDeleteJob');

// Handle Delete Job button click
document.querySelectorAll('.btn-delete').forEach(button => {
    button.addEventListener('click', function () {
        const jobId = this.getAttribute('data-job-id');
        // Store the job ID for later use in the modal
        confirmDeleteJob.setAttribute('data-job-id', jobId);
    });
});

// Confirm deletion in the modal
confirmDeleteJob.addEventListener('click', function () {
    const jobId = this.getAttribute('data-job-id');
    location.href = `delete_job.php?id=${jobId}`;  // Perform the deletion by navigating to the delete URL
});

</script>

</body>
</html>