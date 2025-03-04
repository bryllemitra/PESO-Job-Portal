<?php
include '../includes/config.php';  // This includes your database connection
include '../includes/header.php';

// Restrict access to logged-in users
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

// Get current page number from URL (default is page 1)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$notifications_per_page = 10;  // Number of notifications per page

// Calculate the offset for the SQL query (pagination logic)
$offset = ($page - 1) * $notifications_per_page;

// Initialize search and sort parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'created_at';
$sort_order = isset($_GET['sort_order']) ? ($_GET['sort_order'] === 'asc' ? 'ASC' : 'DESC') : 'DESC';
$filter_read_status = isset($_GET['filter_read_status']) ? $_GET['filter_read_status'] : ''; // New parameter for filtering read/unread

// Build the base query
$query_base = "
    SELECT n.*, CONCAT(u.first_name, ' ', IFNULL(u.middle_name, ''), ' ', u.last_name) AS applicant_name, 
           j.title AS job_name, a.status AS application_status, j.employer_id
    FROM notifications n
    JOIN users u ON n.sender_id = u.id
    JOIN jobs j ON n.job_id = j.id
    LEFT JOIN applications a ON n.job_id = a.job_id AND a.user_id = n.recipient_id
    WHERE n.recipient_id = {$_SESSION['user_id']}
";

// Add search condition dynamically
$search_filter = "";
if (!empty($search)) {
    $search_term = $conn->real_escape_string($search);
    $search_filter = " AND (
        j.title LIKE '%$search_term%' OR 
        j.description LIKE '%$search_term%' OR 
        CONCAT(u.first_name, ' ', IFNULL(u.middle_name, ''), ' ', u.last_name) LIKE '%$search_term%' OR 
        j.location LIKE '%$search_term%' OR 
        j.specific_location LIKE '%$search_term%' OR 
        u.barangay LIKE '%$search_term%' OR 
        u.username LIKE '%$search_term%'
    )";
}

// Add read/unread filter dynamically
$read_filter = "";
if ($filter_read_status === 'unread') {
    $read_filter = " AND n.is_read = 0"; // Filter for unread notifications
} elseif ($filter_read_status === 'read') {
    $read_filter = " AND n.is_read = 1"; // Filter for read notifications
}

// Add sorting
$query_order = " ORDER BY $sort_by $sort_order";

// Add pagination
$query_limit = " LIMIT $offset, $notifications_per_page";

// Full query for paginated notifications
$query = $query_base . $search_filter . $read_filter . $query_order . $query_limit;

// Execute the query
$result = $conn->query($query);
$notifications = $result->fetch_all(MYSQLI_ASSOC);

// Query to fetch total notifications for pagination
$total_query = "
    SELECT COUNT(*) AS total 
    FROM notifications n
    JOIN users u ON n.sender_id = u.id
    JOIN jobs j ON n.job_id = j.id
    LEFT JOIN applications a ON n.job_id = a.job_id AND a.user_id = n.recipient_id
    WHERE n.recipient_id = {$_SESSION['user_id']}
" . $search_filter . $read_filter;

$total_result = $conn->query($total_query);
$total_notifications = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_notifications / $notifications_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/JOB/assets/notification.css">
</head>
<body>
<div class="container">
    <h3 class="text-center">Notifications</h3><br><br>

<!-- Search and Sorting Form -->
<form method="GET" class="mb-3">
    <div class="row gx-2 gy-2 justify-content-center align-items-center">
        <!-- Search Bar -->
        <div class="col-lg-4 col-md-5 col-12">
            <input 
                type="text" 
                name="search" 
                class="form-control shadow-sm" 
                placeholder="Search..." 
                value="<?= htmlspecialchars($search) ?>" 
                style="border-radius: 30px; padding: 0.75rem 1.5rem; font-size: 0.9rem; height: 48px;" <!-- Standardized height -->
            
        </div>
        <!-- Sorting Dropdown -->
        <div class="col-lg-2 col-md-3 col-6">
            <select 
                name="sort_by" 
                class="form-select shadow-sm" 
                style="border-radius: 30px; padding: 0.75rem 1rem; font-size: 0.9rem; height: 48px;" <!-- Standardized height -->
            
                <option value="created_at" <?= $sort_by === 'created_at' ? 'selected' : '' ?>>Date</option>
                <option value="j.title" <?= $sort_by === 'j.title' ? 'selected' : '' ?>>Job Title</option>
                <option value="u.first_name" <?= $sort_by === 'u.first_name' ? 'selected' : '' ?>>First Name</option>
                <option value="u.last_name" <?= $sort_by === 'u.last_name' ? 'selected' : '' ?>>Last Name</option>
            </select>
        </div>
        <!-- Sort Order Dropdown -->
        <div class="col-lg-2 col-md-3 col-6">
            <select 
                name="sort_order" 
                class="form-select shadow-sm" 
                style="border-radius: 30px; padding: 0.75rem 1rem; font-size: 0.9rem; height: 48px;" <!-- Standardized height -->
            
                <option value="desc" <?= $sort_order === 'DESC' ? 'selected' : '' ?>>Descending</option>
                <option value="asc" <?= $sort_order === 'ASC' ? 'selected' : '' ?>>Ascending</option>
            </select>
        </div>
        <!-- Read/Unread Filter -->
        <div class="col-lg-2 col-md-3 col-6">
            <select 
                name="filter_read_status" 
                class="form-select shadow-sm" 
                style="border-radius: 30px; padding: 0.75rem 1rem; font-size: 0.9rem; height: 48px;" <!-- Standardized height -->
            
                <option value="" <?= empty($filter_read_status) ? 'selected' : '' ?>>All Notifications</option>
                <option value="unread" <?= $filter_read_status === 'unread' ? 'selected' : '' ?>>Unread</option>
                <option value="read" <?= $filter_read_status === 'read' ? 'selected' : '' ?>>Read</option>
            </select>
        </div>
        <!-- Submit Button -->
        <div class="col-lg-2 col-md-3 col-6 d-flex justify-content-center">
            <button 
                type="submit" 
                class="btn btn-primary w-100" 
                style="border-radius: 30px; padding: 0.6rem 1rem; font-size: 0.9rem; height: 48px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);" <!-- Standardized height -->
            
                Filter
            </button>
        </div>
    </div>
</form>

<?php if (empty($notifications)): ?>
    <div class="text-center py-4">
        <p class="text-muted">No new notifications.</p>
    </div>
<?php else: ?>
    <?php foreach ($notifications as $notif): ?>
        <div 
            class="notification-item card mb-3 <?php echo $notif['is_read'] == 0 ? 'unread' : ''; ?>" 
            data-id="<?php echo $notif['id']; ?>"
            style="border-radius: 15px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); transition: transform 0.2s ease-in-out;"
        >
            <div class="card-body d-flex justify-content-between align-items-start">
                <!-- Notification Content -->
                <div class="flex-grow-1">
                <?php if ($_SESSION['role'] == 'user'): ?>
                    <!-- Notification message for user -->
                    <p class="mb-1">
                        <strong>Your application for</strong> 
                        <strong class="text-primary"><?php echo htmlspecialchars($notif['job_name']); ?></strong>
                        <strong>has been <?php echo $notif['application_status'] == 'accepted' ? '<span class="text-success">accepted</span>' : '<span class="text-danger">rejected</span>'; ?>.</strong>
                    </p>
                <?php elseif ($_SESSION['role'] == 'admin'): ?>
                    <!-- Notification message for admin -->
                    <p class="mb-1">
                        <strong><?php echo htmlspecialchars($notif['applicant_name']); ?></strong> 
                        <strong>has applied for</strong> 
                        <strong class="text-primary"><?php echo htmlspecialchars($notif['job_name']); ?></strong>.
                    </p>
                <?php elseif ($_SESSION['role'] == 'employer' && $notif['employer_id'] == $_SESSION['user_id']): ?>
                    <!-- Notification message for employer when someone applies to their job -->
                    <p class="mb-1">
                        <strong><?php echo htmlspecialchars($notif['applicant_name']); ?></strong> 
                        <strong>has applied for your job:</strong> 
                        <strong class="text-primary"><?php echo htmlspecialchars($notif['job_name']); ?></strong>.
                    </p>
                <?php endif; ?>
                <small class="text-muted"><?php echo date('F j, Y, g:i a', strtotime($notif['created_at'])); ?></small>
            </div>

                <!-- Dropdown Actions -->
                <div>
                <div class="dropdown">
    <button 
        class="btn btn-sm btn-dark dropdown-toggle" 
        type="button" 
        id="dropdownMenuButton" 
        data-bs-toggle="dropdown" 
        aria-haspopup="true" 
        aria-expanded="false"
        style="border-radius: 30px; padding: 0.5rem 1rem;"
    >
        <!-- The button text can be customized here -->
        Options
    </button>
    <div 
        class="dropdown-menu dropdown-menu-end p-0" 
        aria-labelledby="dropdownMenuButton" 
        style="max-width: 95%; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);"
    >
        <button 
            class="dropdown-item mark-read d-flex align-items-center" 
            data-id="<?php echo $notif['id']; ?>"
            style="padding: 0.75rem 1rem;"
        >
            <i class="fas fa-check-circle text-success me-2"></i> Mark as Read
        </button>
        <button 
            class="dropdown-item delete-notif d-flex align-items-center" 
            data-id="<?php echo $notif['id']; ?>"
            style="padding: 0.75rem 1rem;"
        >
            <i class="fas fa-trash-alt text-danger me-2"></i> Delete
        </button>
        <?php if ($_SESSION['role'] == 'user'): ?>
            <!-- Remove "View Profile" option for users -->
            <a 
                class="dropdown-item d-flex align-items-center" 
                href="job.php?id=<?php echo $notif['job_id']; ?>" 
                style="padding: 0.75rem 1rem;"
            >
                <i class="fas fa-briefcase me-2"></i> View Job
            </a>
            <?php elseif ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'employer'): ?>
    <!-- Add "View Profile" option for both admins and employers -->
    <a 
        class="dropdown-item d-flex align-items-center" 
        href="
            <?php 
            // If the user is an admin, redirect to the applicant's profile
            if ($_SESSION['role'] == 'admin'): 
                echo 'profile.php?id=' . $notif['sender_id']; // Admin can view applicant's profile
            elseif ($_SESSION['role'] == 'employer'): 
                // Employers can view applicants' profiles based on job they posted (sender_id represents the applicant)
                echo 'profile.php?id=' . $notif['sender_id']; // Employer can view the applicant's profile who applied for their job
            endif; 
            ?>" 
        style="padding: 0.75rem 1rem;">
        <i class="fas fa-user me-2"></i> View Profile
    </a>
            <a 
                class="dropdown-item d-flex align-items-center" 
                href="job.php?id=<?php echo $notif['job_id']; ?>" 
                style="padding: 0.75rem 1rem;"
            >
                <i class="fas fa-briefcase me-2"></i> View Job
            </a>
        <?php endif; ?>
    </div>
</div>

</div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Pagination Controls -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <a class="page-link" href="#" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <a class="page-link" href="#" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
<script>
// Mark notification as read
$('.mark-read').click(function () {
    const notifId = $(this).data('id');
    $.ajax({
        url: '../includes/mark_read.php',
        method: 'POST',
        data: { id: notifId },
        success: function (response) {
            console.log(response);
            $(`.notification-item[data-id=${notifId}]`).removeClass('unread');
            location.reload(); // Refresh the page to show updated status
        },
        error: function () {
            alert('Failed to mark notification as read.');
        }
    });
});

// Delete notification
$('.delete-notif').click(function () {
    const notifId = $(this).data('id');
    $.ajax({
        url: '../includes/delete_notification.php',
        method: 'POST',
        data: { id: notifId },
        success: function (response) {
            console.log(response);
            $(`.notification-item[data-id=${notifId}]`).fadeOut(); // Fade out the notification
            location.reload(); // Refresh the page to show updated list
        },
        error: function () {
            alert('Failed to delete notification.');
        }
    });
});
</script>
</body>
</html>
