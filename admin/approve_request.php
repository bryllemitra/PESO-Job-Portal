<?php
// Include the database connection
include '../includes/config.php';

// Check if the request ID is provided via POST
if (isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];

    // Update the employer_request status to 'approved'
    $query = "UPDATE employer_requests SET status = 'approved' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $request_id);

    if ($stmt->execute()) {
        // Successfully updated the status, now update the user role
        // Get the user_id related to the request
        $query_user = "SELECT user_id FROM employer_requests WHERE id = ?";
        $stmt_user = $conn->prepare($query_user);
        $stmt_user->bind_param('i', $request_id);
        $stmt_user->execute();
        $result = $stmt_user->get_result();
        $user = $result->fetch_assoc();
        $user_id = $user['user_id'];

        // Update the user's role to 'employer'
        $query_update_role = "UPDATE users SET role = 'employer' WHERE id = ?";
        $stmt_role = $conn->prepare($query_update_role);
        $stmt_role->bind_param('i', $user_id);

        if ($stmt_role->execute()) {
            // Send a success response with the user_id
            echo json_encode(['status' => 'success', 'user_id' => $user_id]);
        } else {
            // If role update fails, return an error
            echo json_encode(['status' => 'error', 'message' => 'Failed to update user role']);
        }
    } else {
        // If update fails, return an error
        echo json_encode(['status' => 'error']);
    }
} else {
    // If no request_id is passed, return an error
    echo json_encode(['status' => 'error']);
}
?>
