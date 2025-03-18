<?php
include '../includes/config.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get pagination variables from GET request
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5; // Default to 5 notifications per page
$offset = ($page - 1) * $limit; // Calculate the offset for pagination

try {
    // Prepare query to fetch the total number of notifications and unread count
    $count_query = "SELECT 
                        COUNT(*) AS total,
                        SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) AS unread_count
                    FROM notifications 
                    WHERE recipient_id = ?";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param('i', $user_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $counts = $count_result->fetch_assoc();
    $total_notifications = $counts['total'];
    $unread_count = $counts['unread_count'];
    $total_pages = ceil($total_notifications / $limit); // Calculate total pages

    // Prepare query to fetch notifications for the current page
    if ($role == 'admin') {
        // Admin view: List all applicants for all jobs
        $query = "SELECT n.id, n.job_id, CONCAT(u.first_name, ' ', u.last_name) AS applicant_name, j.title AS job_name, a.status AS application_status, n.is_read, n.created_at, 
                  CONCAT('Application from ', u.first_name, ' ', u.last_name, ' for job ', j.title) AS message
                  FROM notifications n
                  JOIN users u ON n.sender_id = u.id
                  JOIN jobs j ON n.job_id = j.id
                  LEFT JOIN applications a ON n.job_id = a.job_id AND a.user_id = n.recipient_id
                  WHERE n.recipient_id = ? ORDER BY n.created_at DESC LIMIT ?, ?";

    } elseif ($role == 'employer') {
        // Employer view: Notifications about applications for their own jobs
        $query = "SELECT n.id, n.job_id, CONCAT(u.first_name, ' ', u.last_name) AS applicant_name, j.title AS job_name, a.status AS application_status, n.is_read, n.created_at, 
                  CONCAT('Application from ', u.first_name, ' ', u.last_name, ' for job ', j.title) AS message
                  FROM notifications n
                  JOIN users u ON n.sender_id = u.id
                  JOIN jobs j ON n.job_id = j.id
                  LEFT JOIN applications a ON n.job_id = a.job_id AND a.user_id = n.recipient_id
                  WHERE n.recipient_id = ? AND j.employer_id = ? ORDER BY n.created_at DESC LIMIT ?, ?";

    } else {
        // User view: Notifications about their job applications
        $query = "SELECT n.id, n.job_id, j.title AS job_name, a.status AS application_status, n.is_read, n.created_at, 
                  CONCAT('Your application for ', j.title, ' has been ', a.status) AS message
                  FROM notifications n
                  JOIN jobs j ON n.job_id = j.id
                  LEFT JOIN applications a ON n.job_id = a.job_id AND a.user_id = n.recipient_id
                  WHERE n.recipient_id = ? ORDER BY n.created_at DESC LIMIT ?, ?";
    }

    $stmt = $conn->prepare($query);

    // Bind the parameters based on the role
    if ($role == 'admin') {
        $stmt->bind_param('iii', $user_id, $offset, $limit);
    
    } elseif ($role == 'employer') {
        $stmt->bind_param('iiii', $user_id, $user_id, $offset, $limit);
    
    } else {
        $stmt->bind_param('iii', $user_id, $offset, $limit);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }

    // Return notifications, total pages, and unread count
    echo json_encode([
        'notifications' => $notifications,
        'totalPages' => $total_pages,
        'unreadCount' => $unread_count
    ]);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
