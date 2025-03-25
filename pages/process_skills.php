<?php
session_start();
include '../includes/config.php'; // Include DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Get form data
        $skills = $_POST['skills'];

        // Insert or update skills in the database
        $stmt = $pdo->prepare("INSERT INTO skills (user_id, skills) VALUES (?, ?) ON DUPLICATE KEY UPDATE skills=?");
        $stmt->execute([$user_id, $skills, $skills]);

        // Redirect to profile page after saving
        header('Location: profile.php');
        exit;
    } else {
        // Redirect to login if not logged in
        header('Location: login.php');
        exit;
    }
}
?>
