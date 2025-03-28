<?php
include '../includes/config.php';
include '../includes/header.php';
include '../includes/restrictions.php';
include('../includes/sidebar.php');

// Handle search and sort
$search = isset($_GET['search']) ? trim($_GET['search']) : ''; // Trim whitespace from search term
$sort_by = isset($_GET['sort_by']) && in_array($_GET['sort_by'], ['company_name', 'created_at']) ? $_GET['sort_by'] : 'created_at';
$order = ($sort_by == 'company_name') ? 'ASC' : 'DESC'; // Default sorting: alphabetical for company_name, or by created_at

// Pagination setup
$limit = 10; // Number of requests per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Current page (defaults to 1)
$offset = ($page - 1) * $limit; // Offset for the SQL query

// Prepare search parameters
$search_param = '%' . $search . '%'; // Add wildcards for partial matching

// Query employer requests with search and sort (searching company_name and username)
$query = "
    SELECT er.id, er.company_name, u.id AS user_id, u.username, er.created_at
    FROM employer_requests er
    JOIN users u ON er.user_id = u.id
    WHERE er.company_name LIKE ? OR u.username LIKE ?
    ORDER BY $sort_by $order
    LIMIT ?, ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param('ssii', $search_param, $search_param, $offset, $limit); // Bind parameters
$stmt->execute();
$requests_result = $stmt->get_result();

// Query to get the total number of requests for pagination
$total_query = "
    SELECT COUNT(*) AS total
    FROM employer_requests er
    JOIN users u ON er.user_id = u.id
    WHERE er.company_name LIKE ? OR u.username LIKE ?
";
$total_stmt = $conn->prepare($total_query);
$total_stmt->bind_param('ss', $search_param, $search_param); // Bind parameters
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_requests = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_requests / $limit); // Calculate total pages
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Requests</title>
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="/JOB/assets/employer_hiring.css">
    <style>
    body {
        background-color: #f1f3f5;
        }
    </style>
</head>
<body>



<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="header">
        <h1>Employer Role Request</h1>
    </div>

    <!-- Filters and Sorting -->
    <div class="filters">
        <form action="employer_hiring.php" method="GET" class="mb-4 row g-3 align-items-center">
            <!-- Search Box -->
            <div class="col-md-6">
                <input type="text" name="search" class="form-control rounded-pill shadow-sm expanded-input" 
                    placeholder="Search by company name or username" 
                    value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            </div>

            <!-- Sorting Dropdown -->
            <div class="col-md-3">
                <select name="sort_by" class="form-select rounded-pill shadow-sm expanded-select">
                    <option value="company_name" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'company_name') ? 'selected' : '' ?>>Company Name</option>
                    <option value="created_at" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'created_at') ? 'selected' : '' ?>>Latest Registered</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary rounded-pill shadow-sm expanded-button">Filter</button>
            </div>
        </form>
    </div>

<!-- Employer Requests List -->
<div class="user-list">
    <!-- Table Header -->
    <div class="user-header">
        <div>Company Name</div>
        <div>Employer Username</div>
        <div>Requested At</div>
        <div>Actions</div>
    </div>

    <!-- Employer Request Items -->
    <?php while ($request = $requests_result->fetch_assoc()): ?>
        <div class="user-item">
            <div><?= htmlspecialchars($request['company_name']) ?></div>
            <div class="username">
                <a href="../pages/profile.php?id=<?= $request['user_id'] ?>" class="text-decoration-none text-primary fw-semibold">
                    <?= htmlspecialchars($request['username']) ?>
                </a>
            </div>
            <div><?= htmlspecialchars($request['created_at']) ?></div>
            <div class="actions">
                <!-- View Details Button - Redirect to employer_approval.php -->
                <button onclick="location.href='employer_approval.php?id=<?= $request['id'] ?>'" class="btn btn-primary">
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

</body>
</html>
