<?php
session_start(); // Start the session
include '../includes/config.php'; // Include your DB connection

// Get the JSON input from the request
$data = json_decode(file_get_contents('php://input'), true);

// Extract data
$skillId = $data['skill_id'];
$proficiency = $data['proficiency'];
$userId = $_SESSION['user_id']; // Get the logged-in user's ID

// Prepare and execute the query to add the skill
$stmt = $conn->prepare("INSERT INTO skills (user_id, skill_id, proficiency) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $userId, $skillId, $proficiency);
$success = $stmt->execute();

// Return a success/failure response
echo json_encode(['success' => $success]);

// Close the statement and connection
$stmt->close();
$conn->close();
?>