<?php
include '../includes/config.php';
include '../includes/header.php';
include('../includes/sidebar_employer.php');

// Check if the employer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    echo "<script>window.location.href = '../pages/index.php';</script>";
    exit();
}

// Get the logged-in employer's ID
$employer_id = $_SESSION['user_id'];

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : ''; // Trim whitespace from search term

// Handle sorting
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'applied_at_desc'; // Default sorting by applied date (newest first)
$sort_mapping = [
    'name_asc' => ['column' => 'u.first_name', 'order' => 'ASC'],
    'name_desc' => ['column' => 'u.first_name', 'order' => 'DESC'],
    'email_asc' => ['column' => 'u.email', 'order' => 'ASC'],
    'email_desc' => ['column' => 'u.email', 'order' => 'DESC'],
    'job_title_asc' => ['column' => 'j.title', 'order' => 'ASC'],
    'job_title_desc' => ['column' => 'j.title', 'order' => 'DESC'],
    'applied_at_asc' => ['column' => 'a.applied_at', 'order' => 'ASC'],
    'applied_at_desc' => ['column' => 'a.applied_at', 'order' => 'DESC'],
];

// Validate the sort parameter
if (!array_key_exists($sort_by, $sort_mapping)) {
    $sort_by = 'applied_at_desc'; // Default to applied_at_desc if invalid sort is passed
}

// Extract column and order from the mapping
$sort_column = $sort_mapping[$sort_by]['column'];
$sort_order = $sort_mapping[$sort_by]['order'];

// Pagination setup
$limit = 10; // Number of applicants per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Current page (defaults to 1)
$offset = ($page - 1) * $limit; // Offset for the SQL query

// Prepare search parameters
$search_param = '%' . $search . '%'; // Add wildcards for partial matching

// Query applicants for the employer's jobs
$applicant_query = "
    SELECT 
        u.id AS user_id, 
        u.first_name, 
        u.last_name, 
        u.email, 
        j.title AS job_title, 
        a.applied_at 
    FROM applications a
    JOIN users u ON a.user_id = u.id
    JOIN jobs j ON a.job_id = j.id
    WHERE j.employer_id = ? 
    AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR j.title LIKE ?)
    ORDER BY $sort_column $sort_order
    LIMIT ?, ?
";
$stmt = $conn->prepare($applicant_query);
$stmt->bind_param('issssii', $employer_id, $search_param, $search_param, $search_param, $search_param, $offset, $limit); // Bind parameters
$stmt->execute();
$applicant_result = $stmt->get_result();

// Query to get the total number of applicants for pagination
$total_query = "
    SELECT COUNT(*) AS total 
    FROM applications a
    JOIN users u ON a.user_id = u.id
    JOIN jobs j ON a.job_id = j.id
    WHERE j.employer_id = ? 
    AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR j.title LIKE ?)
";
$total_stmt = $conn->prepare($total_query);
$total_stmt->bind_param('issss', $employer_id, $search_param, $search_param, $search_param, $search_param); // Bind parameters
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_applicants = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_applicants / $limit); // Calculate total pages
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicants</title>
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="/JOB/assets/user_list_employer.css">
    <style>
        body {
            background-color: #f1f3f5 !important;
        }
    </style>
</head>
<body>



<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="header">
        <h1>Applicants</h1>
    </div>

<!-- Filters and Sorting -->
<div class="filters">
    <form action="user_list.php" method="GET" class="mb-4 row g-3 align-items-center">
        <!-- Search Box -->
        <div class="col-md-6">
            <input type="text" name="search" class="form-control rounded-pill shadow-sm expanded-input" 
                   placeholder="Search by name, email, or job title" 
                   value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        </div>

        <!-- Sorting Dropdown -->
        <div class="col-md-3">
            <select name="sort_by" class="form-select rounded-pill shadow-sm expanded-select">
                <option value="name_asc" <?= $sort_by === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                <option value="name_desc" <?= $sort_by === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                <option value="email_asc" <?= $sort_by === 'email_asc' ? 'selected' : '' ?>>Email (A-Z)</option>
                <option value="email_desc" <?= $sort_by === 'email_desc' ? 'selected' : '' ?>>Email (Z-A)</option>
                <option value="job_title_asc" <?= $sort_by === 'job_title_asc' ? 'selected' : '' ?>>Job Title (A-Z)</option>
                <option value="job_title_desc" <?= $sort_by === 'job_title_desc' ? 'selected' : '' ?>>Job Title (Z-A)</option>
                <option value="applied_at_asc" <?= $sort_by === 'applied_at_asc' ? 'selected' : '' ?>>Applied At (Oldest First)</option>
                <option value="applied_at_desc" <?= $sort_by === 'applied_at_desc' ? 'selected' : '' ?>>Applied At (Newest First)</option>
            </select>
        </div>

        <!-- Submit Button -->
        <div class="col-md-auto">
            <button type="submit" class="btn btn-primary rounded-pill shadow-sm expanded-button">Filter</button>
        </div>
    </form>
</div>

    <!-- Applicant List -->
    <div class="user-list">
        <!-- Table Header -->
        <div class="user-header">
            <div>Name</div>
            <div>Email</div>
            <div>Job Applied To</div>
            <div>Applied At</div>
            <div>Actions</div>
        </div>
        <!-- Applicant Items -->
        <?php while ($applicant = $applicant_result->fetch_assoc()): ?>
            <div class="user-item">
                <div><?= htmlspecialchars($applicant['first_name'] . ' ' . $applicant['last_name']) ?></div>
                <div><?= htmlspecialchars($applicant['email']) ?></div>
                <div><?= htmlspecialchars($applicant['job_title']) ?></div>
                <div><?= htmlspecialchars($applicant['applied_at']) ?></div>
                <div class="actions">
                    <!-- View Profile Button -->
                    <button onclick="location.href='/JOB/pages/profile.php?id=<?= $applicant['user_id'] ?>'" class="btn btn-primary">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <button onclick="location.href='?page=<?= max($page - 1, 1) ?>&search=<?= htmlspecialchars($search) ?>&sort_by=<?= htmlspecialchars($sort_by) ?>'" <?= $page <= 1 ? 'disabled' : '' ?>>
            <i class="fas fa-chevron-left"></i>
        </button>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <button onclick="location.href='?page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>&sort_by=<?= htmlspecialchars($sort_by) ?>'" <?= $i === $page ? 'class="active"' : '' ?>>
                <?= $i ?>
            </button>
        <?php endfor; ?>
        <button onclick="location.href='?page=<?= min($page + 1, $total_pages) ?>&search=<?= htmlspecialchars($search) ?>&sort_by=<?= htmlspecialchars($sort_by) ?>'" <?= $page >= $total_pages ? 'disabled' : '' ?>>
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>


<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="confirmDeleteUser">Delete</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>


<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');

    // Toggle visibility for mobile
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle('visible'); // Toggle visibility for dropdown
    } else {
        // For larger screens, slide the sidebar in/out
        sidebar.classList.toggle('hidden');
        mainContent.classList.toggle('hidden');
    }
}

// Get modal element and confirm delete button
const deleteUserModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
const confirmDeleteUser = document.getElementById('confirmDeleteUser');

// Handle Delete User button click
document.querySelectorAll('.btn-delete').forEach(button => {
    button.addEventListener('click', function () {
        const userId = this.getAttribute('data-user-id');
        // Store the user ID for later use in the modal
        confirmDeleteUser.setAttribute('data-user-id', userId);
    });
});

// Confirm deletion in the modal
confirmDeleteUser.addEventListener('click', function () {
    const userId = this.getAttribute('data-user-id');
    location.href = `delete_user.php?id=${userId}`;  // Perform the deletion by navigating to the delete URL
});

</script>

</body>
</html>
