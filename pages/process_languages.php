<?php
include '../includes/config.php'; // Include DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Get form data
        $languages = $_POST['languages'];

        // Insert or update languages in the database
        $stmt = $pdo->prepare("INSERT INTO languages (user_id, languages) VALUES (?, ?) ON DUPLICATE KEY UPDATE languages=?");
        $stmt->execute([$user_id, $languages, $languages]);

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
