<?php
session_start();
include '../includes/config.php';

if ($_SESSION['role'] === 'admin') {
    // Admin-specific query: Fetch notifications for the admin (job applications)
    $query = "SELECT a.id, u.first_name, u.last_name, j.title, a.applied_at, j.id AS job_id, a.is_read 
              FROM applications a
              JOIN users u ON a.user_id = u.id
              JOIN jobs j ON a.job_id = j.id
              WHERE a.dismissed = 0"; // Only fetch non-dismissed notifications
    $result = mysqli_query($conn, $query);
    $notifications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Ensure applied_at is valid and not NULL
        $appliedAt = strtotime($row['applied_at']);
        if ($appliedAt === false) {
            $appliedAtFormatted = "undefined"; // Fallback for invalid timestamps
        } else {
            $appliedAtFormatted = date("M d, Y h:i A", $appliedAt); // Format timestamp if valid
        }
        $notifications[] = [
            'id' => $row['id'],
            'message' => "{$row['first_name']} {$row['last_name']} applied for '{$row['title']}'",
            'applied_at' => $appliedAtFormatted,
            'url' => "../admin/view_applicants.php?job_id={$row['job_id']}",
            'is_read' => (bool)$row['is_read']
        ];
    }
    echo json_encode($notifications);
} elseif ($_SESSION['role'] === 'user') {
    // User-specific query: Fetch notifications for the logged-in user (application status)
    $userId = $_SESSION['user_id'];  // Get the user ID from the session

    $query = "SELECT a.id, j.title, a.applied_at, a.status, a.is_read
              FROM applications a
              JOIN jobs j ON a.job_id = j.id
              WHERE a.user_id = ? AND a.dismissed = 0"; // Fetch notifications for the logged-in user

    // Prepare and execute the query
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('i', $userId); // Bind the user ID to the query
        $stmt->execute();
        $result = $stmt->get_result();

        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            // Ensure applied_at is valid and not NULL
            $appliedAt = strtotime($row['applied_at']);
            if ($appliedAt === false) {
                $appliedAtFormatted = "undefined"; // Fallback for invalid timestamps
            } else {
                $appliedAtFormatted = date("M d, Y h:i A", $appliedAt); // Format timestamp if valid
            }

            // Determine the status message based on application status
            switch ($row['status']) {
                case 'accepted':
                    $statusMessage = "Your application for '{$row['title']}' has been accepted!";
                    break;
                case 'rejected':
                    $statusMessage = "Your application for '{$row['title']}' has been rejected.";
                    break;
                case 'pending':
                    $statusMessage = "Your application for '{$row['title']}' is still pending.";
                    break;
                default:
                    $statusMessage = "Application status for '{$row['title']}' is unknown.";
                    break;
            }

            // Add notification to the array
            $notifications[] = [
                'id' => $row['id'],
                'message' => $statusMessage,
                'applied_at' => $appliedAtFormatted,
                'url' => "../user/job_application_details.php?app_id={$row['id']}",  // Link to job application details
                'is_read' => (bool)$row['is_read']
            ];
        }

        // Return the notifications as JSON
        echo json_encode($notifications);
    } else {
        // If the query preparation fails, return an empty array
        echo json_encode([]);
    }
}
?>
