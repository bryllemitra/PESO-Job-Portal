<?php
// Include database connection
include '../includes/config.php';

// Check if the request_id is passed via POST (for resubmitting the request)
if (isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];

    // First, delete the associated proof files
    $proof_files_query = "SELECT * FROM employer_request_proofs WHERE request_id = ?";
    $stmt_proof = $conn->prepare($proof_files_query);
    $stmt_proof->bind_param("i", $request_id);
    $stmt_proof->execute();
    $proof_result = $stmt_proof->get_result();

    // Delete the proof files from the uploads folder
    while ($proof = $proof_result->fetch_assoc()) {
        $file_path = $proof['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path); // Delete the file
        }
    }

    // Now, delete the request from the employer_requests table
    $delete_request_query = "DELETE FROM employer_requests WHERE id = ?";
    $stmt_delete = $conn->prepare($delete_request_query);
    $stmt_delete->bind_param("i", $request_id);

    if ($stmt_delete->execute()) {
        // Redirect back to the form page to allow the user to resubmit the request
        header('Location: employer_requests.php');
        exit();
    } else {
        // Error message if the deletion fails
        echo "Error deleting the request. Please try again later.";
    }
} else {
    // If request_id is not set, show an error
    echo "No request found to resubmit.";
}
?>
