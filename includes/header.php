<?php
session_start(); // Start the session at the very top
include '../includes/config.php'; // Include DB connection
// Get the current page URL for highlighting active links
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Job Portal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/heroicons@1.0.6/dist/outline.css" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/JOB/assets/header.css">
    <style>
        .pagination-container-custom {
    display: flex;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically (optional) */
    margin-top: 10px; /* Add spacing above the pagination */
}

.pagination-custom {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
}

.pagination-custom .page-item {
    margin: 0 5px; /* Add spacing between pagination items */
}

.pagination-custom .page-link {
    padding: 2px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
    color: #007bff;
}

.pagination-custom .page-item.active .page-link {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
    text-decoration: none;
}

.pagination-custom .page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: #fff;
    border-color: #dee2e6;
}
        
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg custom-header shadow-sm fixed-top custom-header-container">
        <div class="container-fluid px-4">
            <!-- Left Side: Brand + Navigation Links -->
            <div class="d-flex align-items-center">
                <!-- Brand -->
                <a class="navbar-brand d-flex align-items-center" href="../pages/index.php">
                    <img src="/JOB/uploads/OFFICIAL.png" alt="PESO Logo" style="width: 60px; height: auto; margin-right: 10px;">
                    <span class="brand-text fw-bold text-uppercase">PESO</span>
                </a>
                <!-- Toggler Button for Main Navigation -->
                <button class="navbar-toggler border-0 me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fas fa-bars text-white"></i>
                </button>
                <!-- Collapsible Navigation Links -->
                <div class="collapse navbar-collapse justify-content-start" id="navbarNav">
                    <ul class="navbar-nav align-items-center">
                    <li class="nav-item">
    <a class="nav-link <?php echo ($current_page === 'index.php') ? 'custom-active' : ''; ?>" href="../pages/index.php">Home</a>
</li>
<li class="nav-item">
    <a class="nav-link <?php echo ($current_page === 'browse.php') ? 'custom-active' : ''; ?>" href="../pages/browse.php">Browse</a>
</li>
<li class="nav-item">
    <a class="nav-link <?php echo ($current_page === 'announcement.php') ? 'custom-active' : ''; ?>" href="../admin/announcement.php">Announcement</a>
</li>
<li class="nav-item">
    <a class="nav-link <?php echo ($current_page === 'about.php') ? 'custom-active' : ''; ?>" href="../pages/about.php">About Us</a>
</li>
<li class="nav-item">
    <a class="nav-link <?php echo ($current_page === 'contact.php') ? 'custom-active' : ''; ?>" href="../pages/contact.php">Contact</a>
</li>

<?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user'): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo ($current_page === 'employer_requests.php') ? 'custom-active' : ''; ?>" href="../pages/employer_requests.php">Hire Now</a>
    </li>
<?php endif; ?>


                    </ul>
                </div>
            </div>
            <!-- Right Side: Notification, Message, User Icon -->
            <div class="d-flex align-items-center">
                <!-- Toggler Button for Right-Side Navigation -->
                <button class="navbar-toggler border-0 d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#rightNav" aria-controls="rightNav" aria-expanded="false" aria-label="Toggle right navigation">
                    <i class="fas fa-user-circle text-white" style="font-size: 1.5em;"></i>
                </button>
                <div class="collapse navbar-collapse justify-content-end d-lg-flex" id="rightNav">
                    <ul class="navbar-nav align-items-center">
                        <?php if (isset($_SESSION['username'])): ?>
                            <!-- Message Icon (only for admins) -->
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <li class="nav-item position-relative">
                                    <a href="../admin/view_message.php" class="nav-link d-flex align-items-center" id="message-link">
                                        <i class="fas fa-envelope message-icon"></i>
                                        <span id="unread-count" class="message-count badge bg-danger rounded-circle"></span>
                                    </a>
                                </li>
                            <?php endif; ?>

<!-- Bell Dropdown for Notifications -->
<li class="nav-item dropdown position-relative">
<a class="nav-link d-flex align-items-center" href="#" id="notification-link" role="button" data-bs-toggle="dropdown" data-bs-auto-close="false" aria-expanded="false">
    <i class="fas fa-bell notification-icon"></i>
    <span id="notification-count" class="notification-count badge bg-danger rounded-circle" style="display: none;"></span>
</a>
    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 notification-scroll animated-dropdown" id="notification-dropdown">
        <li>
            <div class="dropdown-header text-center fw-bold">Notifications</div>
        </li>
        <li>
            <hr class="dropdown-divider">
        </li>
        <li id="notification-list">
            <!-- Notifications will be dynamically inserted here -->
            <div id="notification-items">
                <div class="dropdown-item text-center py-3">Loading...</div>
            </div>
        </li>
        <!-- Pagination controls -->
        <li>
            <div class="pagination-container-custom">
                <ul class="pagination-custom justify-content-center" id="pagination-custom"></ul>
            </div>
        </li>
        <!-- View All Notifications Button -->
        <li class="dropdown-item text-center">
            <a href="../pages/notification.php" class="btn header-btn  rounded-pill w-100">View All Notifications</a>
        </li>
        <!-- Dismiss All Button -->
        <li class="dropdown-item text-center">
            <button id="dismiss-all-notifications" class="btn header-btn  rounded-pill w-100">
                Mark All As Read
            </button>
        </li>
    </ul>
</li>




<!-- User Dropdown Menu -->
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-user-circle me-2" style="font-size: 1.5em;"></i>
        <?= htmlspecialchars($_SESSION['username']) ?>
    </a>
    <ul style="background: linear-gradient(120deg, #1e3c72 0%, #2a5298 100%);" class="dropdown-menu dropdown-menu-end">
        <!-- Conditional profile link based on role -->
        <li>
            <a class="dropdown-item text-white" href="<?= ($_SESSION['role'] === 'admin') ? '../admin/profile.php' : ($_SESSION['role'] === 'employer' ? '../employers/profile.php' : '../pages/profile.php') ?>">
                <i class="fas fa-user me-2"></i> Profile
            </a>
        </li>

        <!-- Dashboard link for Admin -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <li><a class="dropdown-item text-white" href="../admin/admin.php"><i class="fas fa-tachometer-alt me-2"></i> Admin Panel</a></li>
        <?php endif; ?>

        <!-- Dashboard link for Employer -->
        <?php if ($_SESSION['role'] === 'employer'): ?>
            <li><a class="dropdown-item text-white" href="../employers/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
        <?php endif; ?>

        <li><hr class="dropdown-divider"></li>

        <!-- Logout link -->
        <li>
            <a class="dropdown-item text-danger" href="../logout.php">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </li>
    </ul>
</li>


                        <?php else: ?>
                            <!-- Login Button (for users who are not logged in) -->
                            <li class="nav-item">
                                <a class="nav-link btn btn-primary btn-sm rounded-pill text-white px-3" href="../pages/login.php">Login</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    
    
    

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    
    <script>
<?php if ($_SESSION['role'] === 'admin'): ?>

// Function to fetch unread message count via AJAX (Admin only)
function fetchUnreadCount() {
    $.ajax({
         url: '../admin/get_unread_count.php',  // Admin-specific URL
        method: 'GET',
         success: function(response) {
        const count = parseInt(response.trim());
        const unreadCountElement = $('#unread-count');
        if (count > 0) {
                    unreadCountElement.text(count).show(); // Show the badge with the count
                } else {
                    unreadCountElement.hide(); // Hide the badge if no unread messages
                }
            },
            error: function() {
                console.error('Error fetching unread message count.');
            }
        });
    }

// Fetch unread count every 5 seconds
setInterval(fetchUnreadCount, 5000);
fetchUnreadCount(); // Initial fetch on page load

<?php endif; ?>

$(document).ready(function() {
    let currentPage = 1;
    const notificationsPerPage = 10;

    // Variable to track the unread notification count
    let unreadCount = 0;

    // Fetch notifications when the bell icon is clicked
    $('#notification-bell').on('click', function() {
        fetchNotifications(currentPage);
    });

   // Function to fetch notifications with pagination
function fetchNotifications(page) {
    $.ajax({
        url: '../includes/fetch_notifications.php',
        method: 'GET',
        data: { page: page, limit: notificationsPerPage },
        success: function(data) {
            try {
                let notifications = JSON.parse(data);

                // Handle potential errors from the backend
                if (notifications.error) {
                    console.error("Error fetching notifications:", notifications.error);
                    $('#notification-items').html('<div class="dropdown-item text-center py-3">Failed to load notifications.</div>');
                    return;
                }

                // Clear current notifications and pagination
                $('#notification-items').empty();
                $('#pagination-custom').empty();

                // Display notifications or a "no notifications" message
                if (notifications.notifications.length === 0) {
                    $('#notification-items').append('<div class="dropdown-item text-center py-3">No new notifications</div>');
                } else {
                    notifications.notifications.forEach(function(notif) {
                        // Determine the redirect URL based on the user's role and job status
                        let redirectUrl = '';
                        const role = "<?php echo $_SESSION['role']; ?>"; // Pass the user's role from PHP

                        if (role === 'admin') {
                            // Admin: Redirect to job approval page for job approval requests
                            if (notif.message && notif.message.includes('requested approval')) {
                                redirectUrl = `../admin/job_approval.php?job_id=${notif.job_id || ''}`;
                            } else {
                                // Default redirect for other admin notifications
                                redirectUrl = `../admin/view_applicants.php?job_id=${notif.job_id || ''}`;
                            }
                        } else if (role === 'employer') {
                            // Employer: Redirect based on job approval status
                            if (notif.message && notif.message.includes('has been approved')) {
                                redirectUrl = `../pages/job.php?id=${notif.job_id || ''}`;
                            } else if (notif.message && notif.message.includes('has been rejected')) {
                                redirectUrl = `../pages/job.php?id=${notif.job_id || ''}`;
                            } else {
                                // Default redirect for other employer notifications
                                redirectUrl = `../admin/view_applicants.php?job_id=${notif.job_id || ''}`;
                            }
                        } else {
                            // Default redirect for users (non-admin, non-employer)
                            redirectUrl = `../pages/job.php?id=${notif.job_id || ''}`;
                        }

                        // Construct the notification item
                        let notificationItem = `
                            <div style="text-decoration:none;" class="dropdown-item ${notif.is_read == 0 ? 'unread' : 'read'}" data-id="${notif.id}">
                                <div>
                                    <a href="${redirectUrl}" style="text-decoration:none;" class="notification-link" data-id="${notif.id}">
                                        ${notif.message || 'Notification details unavailable'} <br>
                                        <small>${notif.created_at || 'Unknown time'}</small>
                                    </a>
                                </div>
                                <div class="notification-actions">
                                    <i class="fas fa-check notification-action-icon mark-read" data-id="${notif.id}" title="Mark as Read"></i>
                                    <i class="fas fa-xmark notification-action-icon delete" data-id="${notif.id}" title="Delete"></i>
                                </div>
                            </div>
                        `;
                        $('#notification-items').append(notificationItem);
                    });
                

                        // Pagination logic
                        const totalPages = notifications.totalPages;

                        // Previous button
                        if (page > 1) {
                            $('#pagination-custom').append(`
                                <li class="page-item">
                                    <a class="page-link" href="#" data-page="${page - 1}" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            `);
                        } else {
                            $('#pagination-custom').append(`
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            `);
                        }

                        // Page numbers
                        for (let i = 1; i <= totalPages; i++) {
                            $('#pagination-custom').append(`
                                <li class="page-item ${i === page ? 'active' : ''}">
                                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                                </li>
                            `);
                        }

                        // Next button
                        if (page < totalPages) {
                            $('#pagination-custom').append(`
                                <li class="page-item">
                                    <a class="page-link" href="#" data-page="${page + 1}" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            `);
                        } else {
                            $('#pagination-custom').append(`
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            `);
                        }
                    }

                    // Update unread count badge
                    unreadCount = notifications.unreadCount || 0;
                    updateNotificationCount();
                } catch (error) {
                    console.error("Error parsing notifications data:", error);
                    $('#notification-items').html('<div class="dropdown-item text-center py-3">Failed to parse notifications.</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                $('#notification-items').html('<div class="dropdown-item text-center py-3">Failed to fetch notifications.</div>');
            }
        });
    }

    // Handle pagination clicks
    $(document).on('click', '#pagination-custom .page-link', function(e) {
        e.preventDefault(); // Prevent default link behavior
        const page = $(this).data('page'); // Get the page number from the clicked link
        currentPage = page; // Update the current page
        fetchNotifications(currentPage); // Fetch notifications for the selected page
    });

    // Function to update the unread count badge
    function updateNotificationCount() {
        const $badge = $('#notification-count');
        if (unreadCount > 0) {
            $badge.text(unreadCount).show();
        } else {
            $badge.hide();
        }
    }

    



    // Auto-mark as read for admin when clicking the link
    $(document).on('click', '.notification-link', function(e) {
        e.preventDefault(); // Prevent default link behavior
        let notif_id = $(this).data('id'); // Get the notification ID
        let href = $(this).attr('href'); // Get the target URL

        // Mark the notification as read
        $.ajax({
            url: '../includes/mark_read.php',
            method: 'POST',
            data: { id: notif_id },
            success: function(response) {
                console.log(response); // Log success or failure message
                let $notificationItem = $(`[data-id=${notif_id}]`);
                $notificationItem.removeClass('unread').addClass('read'); // Mark as read visually
                unreadCount--; // Decrement the unread count
                updateNotificationCount(); // Update the UI

                // Redirect to the target page after marking as read
                window.location.href = href;
            }
        });
    });

    // Mark notification as read manually
    $(document).on('click', '.mark-read', function() {
        let notif_id = $(this).data('id'); // Get the notification ID
        $.ajax({
            url: '../includes/mark_read.php',
            method: 'POST',
            data: { id: notif_id },
            success: function(response) {
                console.log(response); // Log success or failure message
                let $notificationItem = $(`[data-id=${notif_id}]`);
                $notificationItem.removeClass('unread').addClass('read'); // Mark as read visually
                unreadCount--; // Decrement the unread count
                updateNotificationCount(); // Update the UI
            }
        });
    });

    // Delete notification
    $(document).on('click', '.delete', function() {
        let notif_id = $(this).data('id'); // Get the notification ID
        $.ajax({
            url: '../includes/delete_notification.php',
            method: 'POST',
            data: { id: notif_id },
            success: function(response) {
                console.log(response); // Log success or failure message
                let $notificationItem = $(`[data-id=${notif_id}]`);
                if ($notificationItem.hasClass('unread')) {
                    unreadCount--; // Decrement the unread count if the notification was unread
                }
                $notificationItem.remove(); // Remove the notification item
                updateNotificationCount(); // Update the UI
            }
        });
    });

// Dismiss All Notifications
document.getElementById('dismiss-all-notifications').addEventListener('click', function () {
    fetch('../includes/dismiss_all_notifications.php', {
        method: 'GET',
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text(); // Optionally handle the response if needed
        })
        .then(() => {
            unreadCount = 0; // Reset the unread count
            updateNotificationCount(); // Update the UI
            location.reload(); // Reload the page to reflect changes
        })
        .catch(error => {
            console.error('Error dismissing notifications:', error);
        });
});

    // Initial fetch
    fetchNotifications(1);
});


$(document).click(function (event) {
    var $notificationDropdown = $("#notification-link");
    var $dropdownMenu = $notificationDropdown.next(".dropdown-menu");

    // Check if the clicked element is NOT inside the dropdown menu or the icon itself
    if (!$notificationDropdown.is(event.target) && !$dropdownMenu.is(event.target) && $dropdownMenu.has(event.target).length === 0) {
        $notificationDropdown.dropdown("hide"); // Close the dropdown
    }
});

// Prevent the bell icon from triggering the close event
$("#notification-link").click(function (event) {
    event.stopPropagation(); // Stops the click from bubbling up and triggering the document click event
});


</script>

</body>
</html>

