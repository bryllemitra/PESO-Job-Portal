<?php
session_start();
include '../includes/config.php'; // Include DB connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $work_experience_id = $_GET['id'];

    // Query to get work experience details
    $query = "SELECT * FROM work_experience WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param('ii', $work_experience_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $work_experience = $result->fetch_assoc();
            echo json_encode($work_experience); // Return data as JSON
        } else {
            echo json_encode(['error' => 'Work experience not found.']);
        }
        
        $stmt->close();
    }
}
?>
