<?php
session_start(); // Start the session
include '../includes/config.php'; // Include your DB connection

// Get the JSON input from the request
$data = json_decode(file_get_contents('php://input'), true);

// Extract data
$languageId = $data['language_id'];
$fluency = $data['fluency'];
$userId = $_SESSION['user_id']; // Get the logged-in user's ID

// Prepare and execute the query to add the language
$stmt = $conn->prepare("INSERT INTO languages (user_id, language_name, fluency) 
                        SELECT ?, language_name, ? FROM languages_list WHERE id = ?");
$stmt->bind_param("isi", $userId, $fluency, $languageId);
$success = $stmt->execute();

// Return a success/failure response
echo json_encode(['success' => $success]);

// Close the statement and connection
$stmt->close();
$conn->close();
?>
