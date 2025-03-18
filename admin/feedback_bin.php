<?php
include '../includes/config.php';
include '../includes/header.php';
include '../includes/restrictions.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/index.php");
    exit();
}

// Handle the restore request
if (isset($_GET['restore_id'])) {
    $restore_id = $_GET['restore_id'];
    // Update the status of the feedback to 'active'
    $query = "UPDATE contacts SET status = 'active' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $restore_id);
    if ($stmt->execute()) {
        // Trigger success modal for restore
        echo "<script>
            window.onload = function() {
                var myModal = new bootstrap.Modal(document.getElementById('restoreSuccessModal'));
                myModal.show();
            }
        </script>";
    } else {
        echo "<script>alert('Error restoring feedback: " . $stmt->error . "');</script>";
    }
}

// Handle the delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    // Delete the feedback from the database
    $query = "DELETE FROM contacts WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        // Trigger success modal for deletion
        echo "<script>
            window.onload = function() {
                var myModal = new bootstrap.Modal(document.getElementById('deleteSuccessModal'));
                myModal.show();
            }
        </script>";
    } else {
        echo "<script>alert('Error deleting feedback: " . $stmt->error . "');</script>";
    }
}

// Get search and sorting parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'created_at';
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$feedbacks_per_page = 20;

// Build the SQL query for filtering and sorting
$query = "SELECT * FROM contacts WHERE status = 'deleted'";
$count_query = "SELECT COUNT(*) AS total FROM contacts WHERE status = 'deleted'";

// Add search condition if there's a search query
if ($search) {
    $search_param = "%" . $conn->real_escape_string($search) . "%";
    $query .= " AND (name LIKE ? OR subject LIKE ? OR email LIKE ?)";
    $count_query .= " AND (name LIKE ? OR subject LIKE ? OR email LIKE ?)";
}

// Add sorting condition
$query .= " ORDER BY " . ($sort_by === 'subject' ? 'subject' : 'created_at') . " DESC";

// Add pagination limit
$offset = ($current_page - 1) * $feedbacks_per_page;
$query .= " LIMIT ? OFFSET ?";

// Execute the count query
$stmt_count = $conn->prepare($count_query);
if ($search) {
    $stmt_count->bind_param("sss", $search_param, $search_param, $search_param);
}
$stmt_count->execute();
$total_result = $stmt_count->get_result()->fetch_assoc();
$total_feedbacks = $total_result['total'];
$total_pages = ceil($total_feedbacks / $feedbacks_per_page);

// Execute the main query
$stmt = $conn->prepare($query);
if ($search) {
    $stmt->bind_param("sssi", $search_param, $search_param, $search_param, $feedbacks_per_page, $offset);
} else {
    $stmt->bind_param("ii", $feedbacks_per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

// Check if there are results
if ($result === false) {
    echo "Error executing query: " . $conn->error;
    exit();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Bin - Admin Dashboard</title>
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="/JOB/assets/feedback_bin.css">
    <style>
        .form-control, .form-select, .btn {
    height: 45px; /* Consistent height */
    
}

.expanded-input, .expanded-select, .expanded-button {
    width: 100%; /* Make the input, select, and button take up full column width */
}

.expanded-button {
    width: auto; /* Allow the button to adjust dynamically based on content */
}

    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div>
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="admin.php" ><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="user_list.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="employer_hiring.php"><i class="fas fa-person-circle-question"></i> Employer Request</a></li>
            <li><a href="job_list.php"><i class="fas fa-briefcase"></i> Job List</a></li>
            <li><a href="job_approval.php "><i class="fas fa-clipboard-check"></i> Job Approvals</a></li>
            <li><a href="feedback_bin.php" class="active"><i class="fas fa-trash-alt"></i> Feedback Bin</a></li>
            
        </ul>
    </div>
    <div class="toggle-btn" onclick="toggleSidebar()">
        <i class="fas fa-angle-right"></i>
    </div>
</div>

<!-- Main Content -->
<div class="main-content" id="mainContent">
        <!-- Header -->
        <div class="header">
            <h1>Feedback Bin</h1>
        </div>

<!-- Filters and Sorting -->
<div class="filters">
    <form action="feedback_bin.php" method="GET" class="mb-4 row g-3 align-items-center">
        <!-- Search Box -->
        <div class="col-md-6 col-12">
            <input type="text" name="search" class="form-control rounded-pill shadow-sm expanded-input"
                   placeholder="Search by name or subject"
                   value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        </div>

        <!-- Sorting Dropdown -->
        <div class="col-md-3 col-12">
            <select name="sort_by" class="form-select rounded-pill expanded-select w-100">
                <option value="created_at" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'created_at') ? 'selected' : '' ?>>Latest Deleted</option>
                <option value="subject" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'subject') ? 'selected' : '' ?>>By Subject</option>
            </select>
        </div>

        <!-- Submit Button -->
        <div class="col-md-auto col-12">
            <button type="submit" class="btn btn-primary rounded-pill shadow-sm expanded-button">Filter</button>
        </div>
    </form>
</div>


        <!-- Feedback List -->
<div class="user-list">
    <!-- Feedback Header -->
    <div class="user-header">
        <div>ID</div>
        <div>Name</div>
        <div>Email</div>
        <div>Subject</div>
        <div>Message</div>
        <div>Deleted At</div>
        <div>Actions</div>
    </div>

    <!-- Feedback Items -->
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="user-item">
                <div><?= htmlspecialchars($row['id']) ?></div>
                <div><?= htmlspecialchars($row['name']) ?></div>
                <div><?= htmlspecialchars($row['email']) ?></div>
                <div><?= htmlspecialchars($row['subject']) ?></div>
                <div><?= htmlspecialchars($row['message']) ?></div>
                <div><?= htmlspecialchars($row['created_at']) ?></div>
                <div class="actions">
                    <!-- Restore Button -->
                    <form action="feedback_bin.php" method="GET" class="d-inline">
                        <button type="submit" name="restore_id" value="<?= $row['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-undo"></i>
                        </button>
                    </form>
                    <!-- Delete Button -->
                    <button class="btn-delete btn btn-danger" data-feedback-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-feedback">No deleted feedback found.</div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<div class="pagination">
    <?php
    // Previous Page Arrow
    if ($current_page > 1) {
        echo "<a href='?page=" . ($current_page - 1) . "&search=" . urlencode($search) . "&sort_by=" . urlencode($sort_by) . "'><</a>";
    } else {
        echo "<span class='disabled'><</span>"; // Disabled if on the first page
    }

    // Generate pagination links
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i === $current_page) ? 'active' : '';
        echo "<a href='?page=$i&search=" . urlencode($search) . "&sort_by=" . urlencode($sort_by) . "' class='$active'>$i</a>";
    }

    // Next Page Arrow
    if ($current_page < $total_pages) {
        echo "<a href='?page=" . ($current_page + 1) . "&search=" . urlencode($search) . "&sort_by=" . urlencode($sort_by) . "'>></a>";
    } else {
        echo "<span class='disabled'>></span>"; // Disabled if on the last page
    }
    ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this feedback? The applicant will be able to submit feedback again after deletion.
            </div>
            <div class="modal-footer">
                <a id="confirmDelete" href="#" class="btn btn-primary">Delete</a>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal for Restore -->
<div class="modal fade" id="restoreSuccessModal" tabindex="-1" aria-labelledby="restoreSuccessModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="restoreSuccessModalLabel">Success</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        The feedback has been successfully restored. You can view it on the Feedback page.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Success Modal for Deletion -->
<div class="modal fade" id="deleteSuccessModal" tabindex="-1" aria-labelledby="deleteSuccessModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteSuccessModalLabel">Success</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        The feedback has been successfully deleted. The applicant will be able to submit feedback again.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



<script>
    // Handle restoring feedback
    function restoreFeedback(feedbackId) {
        // Trigger success modal for restore
        var myModal = new bootstrap.Modal(document.getElementById('restoreSuccessModal'));
        myModal.show();
    }

    // Set up the delete confirmation
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const feedbackId = this.getAttribute('data-feedback-id');
            const confirmDeleteLink = document.getElementById('confirmDelete');
            // Set the confirmation link with the delete ID
            confirmDeleteLink.href = 'feedback_bin.php?delete_id=' + feedbackId;
        });
    });

    // Optional: If you still want the confirmation modal for deletion, you can use this:
    function confirmDeletion(feedbackId) {
        if (confirm('Are you sure you want to delete this feedback? The applicant can send feedback again upon deletion.')) {
            window.location.href = 'feedback_bin.php?delete_id=' + feedbackId;
        }
    }
</script>

</body>
</html>
