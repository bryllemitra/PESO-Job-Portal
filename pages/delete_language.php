<?php
session_start(); // Start the session
include '../includes/config.php'; // Include your DB connection

// Get the JSON input from the request
$data = json_decode(file_get_contents('php://input'), true);

// Extract data
$languageId = $data['language_id'];
$userId = $_SESSION['user_id']; // Get the logged-in user's ID

// Prepare and execute the query to delete the language
$stmt = $conn->prepare("DELETE FROM languages WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $languageId, $userId);
$success = $stmt->execute();

// Return a success/failure response
echo json_encode(['success' => $success]);

// Close the statement and connection
$stmt->close();
$conn->close();
?>
