<?php
// Include your database connection
include '../includes/config.php';

// Check if the request_id and remark are passed via POST
if (isset($_POST['request_id']) && isset($_POST['remark'])) {
    $request_id = $_POST['request_id'];
    $remark = $_POST['remark'];

    // Update the request status to 'rejected' and store the remark
    $query = "UPDATE employer_requests SET status = 'rejected', remark = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $remark, $request_id);

    if ($stmt->execute()) {
        // If rejection is successful, send the remark to display it to the user
        echo json_encode([
            'status' => 'success',
            'remark' => $remark // Send the remark back to the frontend
        ]);
    } else {
        // If something goes wrong, send an error response
        echo json_encode(['status' => 'error']);
    }
} else {
    // If the parameters are not set, send an error response
    echo json_encode(['status' => 'error']);
}
?>
